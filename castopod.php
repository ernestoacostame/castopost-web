<?php
/**
 * Castopod REST API client
 *
 * Auth:     HTTP Basic Auth (restapi.basicAuth in Castopod .env)
 * Base URL: https://your-castopod.com/api/v1
 *
 * Flow to publish an episode:
 *   1. GET  /podcasts          → find numeric id from handle
 *   2. POST /episodes          → create episode (returns episode id)
 *   3. PUT  /episodes/{id}/publish → make it public
 */
class CastopodAPI {
    private string $baseUrl;
    private string $authHeader;   // "Basic base64(user:pass)"
    private array  $tmpFiles = [];

    public function __construct(string $instanceUrl, string $apiUser, string $apiPassword) {
        $this->baseUrl    = rtrim($instanceUrl, '/') . '/api/rest/v1';
        $this->authHeader = 'Basic ' . base64_encode($apiUser . ':' . $apiPassword);
    }

    public function __destruct() {
        foreach ($this->tmpFiles as $f) {
            if (file_exists($f)) unlink($f);
        }
    }

    // -------------------------------------------------------
    // Public API
    // -------------------------------------------------------

    /** Returns all podcasts as array. Each item has 'id', 'handle', 'title'. */
    public function getPodcasts(): array {
        return $this->request('GET', '/podcasts');
    }

    /** Get full podcast info by numeric ID. */
    public function getPodcastById(int $id): array {
        return $this->request('GET', "/podcasts/{$id}");
    }

    /** Resolve a handle like "my-podcast" to its numeric podcast id. */
    public function getPodcastIdByHandle(string $handle): int {
        $podcasts = $this->getPodcasts();
        foreach ($podcasts as $p) {
            if (($p['handle'] ?? '') === $handle) {
                return (int) $p['id'];
            }
        }
        throw new Exception(
            "No se encontró ningún podcast con el handle '{$handle}'. " .
            "Comprueba el valor de CASTOPOD_PODCAST_HANDLE en config.php. " .
            "Handles disponibles: " . implode(', ', array_column($podcasts, 'handle'))
        );
    }

    /** Publish an existing draft episode by ID. */
    public function publishDraft(int $episodeId, int $userId): array {
        $pubFields = [
            'publication_method' => 'now',
            'created_by'         => (string) $userId,
        ];
        return $this->request('POST', "/episodes/{$episodeId}/publish", [], $pubFields, false, true);
    }

    /** Returns only the most recent N episodes - fast single request for dashboard. */
    public function getRecentEpisodes(int $podcastId, int $limit = 20): array {
        $data = $this->request('GET', '/episodes/', [
            'podcastIds' => $podcastId,
            'limit'      => $limit,
            'order'      => 'newest',
        ]);
        $episodes = isset($data[0]) ? $data : ($data['data'] ?? $data);
        if (!is_array($episodes)) return [];
        foreach ($episodes as &$ep) {
            if (!isset($ep['episode_number']) && isset($ep['number'])) {
                $ep['episode_number'] = $ep['number'];
            }
        }
        return $episodes;
    }

    /** Returns ALL episodes for a podcast including drafts, fetching all pages. */
    public function getAllEpisodes(int $podcastId, int $pageSize = 100): array {
        $all    = [];
        $offset = 0;

        while (true) {
            $data = $this->request('GET', '/episodes/', [
                'podcastIds' => $podcastId,
                'limit'      => $pageSize,
                'offset'     => $offset,
                'order'      => 'newest',
            ]);

            $page = isset($data[0]) ? $data : ($data['data'] ?? $data);
            if (!is_array($page) || empty($page)) break;

            // Normalize field names
            foreach ($page as $ep) {
                if (!isset($ep['episode_number']) && isset($ep['number'])) {
                    $ep['episode_number'] = $ep['number'];
                }
                $all[] = $ep;
            }

            // If we got fewer than pageSize, we've reached the end
            if (count($page) < $pageSize) break;
            $offset += $pageSize;

            // Safety: stop after 20 pages (2000 episodes)
            if ($offset >= 2000) break;
        }

        return $all;
    }

    /** Returns episodes for a podcast (by numeric id). */
    public function getEpisodesByPodcastId(int $podcastId): array {
        return $this->getAllEpisodes($podcastId);
    }

    /**
     * Full publish flow:
     *   1. Resolve handle -> podcast_id
     *   2. Convert audio to MP3/M4A if needed (API only accepts mp3, m4a)
     *   3. POST /episodes/  (creates draft)
     *   4. POST /episodes/{id}/publish
     */
    public function publishEpisode(
        string  $podcastHandle,
        int     $userId,
        array   $fields,
        ?array  $audioFile,
        ?array  $coverFile
    ): array {
        // Step 1 - resolve podcast id
        $podcastId = $this->getPodcastIdByHandle($podcastHandle);
        
        // Step 2 - obtener nivel LUFS (por defecto -16)
        $lufsLevel = -16.0;
        if (!empty($fields['lufs_level'])) {
            $lufsLevel = (float) $fields['lufs_level'];
        } elseif (!empty($_POST['lufs_level'])) {
            $lufsLevel = (float) $_POST['lufs_level'];
        }
        
        // Validar y luego convertir a MP3 con normalización LUFS
        if ($audioFile && !empty($audioFile['tmp_name'])) {
            $this->validateAudioFile($audioFile);
            $audioFile = $this->ensureMp3($audioFile, $lufsLevel);
        } elseif (empty($fields['audio_url'] ?? '')) {
            throw new Exception('Debes subir un archivo de audio o proporcionar una URL.');
        }

        // Step 3 - build multipart body
        // Field names must match EpisodeController::attemptCreate() validation rules exactly
        $body = [
            'podcast_id'  => $podcastId,
            'created_by'  => $userId,   // NOT user_id
            'updated_by'  => $userId,
            'title'       => $fields['title'],
            'slug'        => !empty($fields['slug']) ? $fields['slug'] : $this->slugify($fields['title']),
            'type'        => !empty($fields['type']) ? $fields['type'] : 'full',
        ];

        if (!empty($fields['description'])) {
            $body['description'] = $fields['description'];
        }
        if (!empty($fields['episode_number'])) {
            $body['episode_number'] = (int) $fields['episode_number'];
        }
        if (!empty($fields['season_number'])) {
            $body['season_number'] = (int) $fields['season_number'];
        }
        if (!empty($fields['explicit'])) {
            $body['parental_advisory'] = 'explicit';
        }

        if ($audioFile && !empty($audioFile['tmp_name'])) {
            $body['audio_file'] = new CURLFile(
                $audioFile['tmp_name'], 'audio/mpeg', $audioFile['name']
            );
        }

        if ($coverFile && !empty($coverFile['tmp_name'])) {
            $body['cover'] = new CURLFile(
                $coverFile['tmp_name'],
                $coverFile['type'] ?: 'image/jpeg',
                $coverFile['name']
            );
        }

        $created = $this->request('POST', '/episodes/', [], $body, true);

        if (empty($created['id'])) {
            throw new Exception('Castopod creo el episodio pero no devolvio su ID. Respuesta: ' . json_encode($created));
        }

        // Step 4 - publish the draft
        // attemptPublish uses getPost() which requires form-urlencoded, not JSON
        $episodeId = (int) $created['id'];
        $pubFields = [
            'publication_method' => 'now',
            'created_by'         => (string) $userId,
        ];

        // If a future date was selected, schedule instead
        if (!empty($fields['published_at'])) {
            $ts = strtotime($fields['published_at']);
            if ($ts && $ts > time() + 60) {
                $pubFields['publication_method']         = 'schedule';
                $pubFields['scheduled_publication_date'] = date('Y-m-d H:i', $ts);
            }
        }

        $this->request('POST', "/episodes/{$episodeId}/publish", [], $pubFields, false, true);

        return $created;
    }

    // -------------------------------------------------------
    // Audio processing: convert to MP3 + normalize to -16 LUFS
    // Uses two-pass EBU R128 loudnorm (same standard as Apple/Spotify)
    // Target: -16 LUFS integrated, -1.5 dBTP true peak, 11 LU range
    // Applied to ALL audio uploads, not just WebM recordings
    // -------------------------------------------------------
    private function ensureMp3(array $file, float $lufsLevel = -16.0): array {
        $debug = defined('CASTOPOD_DEBUG') && CASTOPOD_DEBUG;
        
        $ffmpeg = trim((string) shell_exec('which ffmpeg 2>/dev/null'));
        if (empty($ffmpeg)) {
            throw new Exception(
                'FFmpeg no está instalado. Instala con: sudo apt install ffmpeg'
            );
        }
        
        if (!is_dir(UPLOAD_TMP_DIR)) {
            mkdir(UPLOAD_TMP_DIR, 0750, true);
        }
        
        $input = $file['tmp_name'];
        if (!file_exists($input)) {
            throw new Exception('El archivo de audio no existe en la ubicación temporal: ' . $input);
        }
        if (filesize($input) === 0) {
            throw new Exception('El archivo de audio está vacío: ' . $input);
        }
        $normalizedName = preg_replace('/\.(webm|opus|ogg|mp3|m4a|wav|flac|aac)$/i', '.mp3', $file['name']);
        $out = UPLOAD_TMP_DIR . '/norm_' . uniqid() . '.mp3';
        
        // Validar nivel LUFS (entre -70 y 0)
        $lufsLevel = max(-70, min(0, $lufsLevel));
        $lufsStr = number_format($lufsLevel, 1, '.', '');
        if ($debug) error_log("[CastopodAPI] ensureMp3: input={$input}, LUFS={$lufsStr}");
        
        // Pass 1: medir estadísticas de loudness
        $measureFilter = "loudnorm=I={$lufsStr}:TP=-1.5:LRA=11:print_format=json";
        $measureCmd = sprintf(
            '%s -y -i %s -vn -af %s -f null /dev/null',
            escapeshellarg($ffmpeg),
            escapeshellarg($input),
            escapeshellarg($measureFilter)
        );
        exec($measureCmd . ' 2>&1', $measureLines, $measureExitCode);
        $measureOutput = implode("\n", $measureLines);
        if ($debug) error_log("[CastopodAPI] Pass 1 exit: {$measureExitCode}");
        if ($measureExitCode !== 0) {
            throw new Exception('FFmpeg falló al medir loudness. Log: ' . $measureOutput);
        }
        
        // Extraer JSON de la salida de FFmpeg
        $stats = null;
        if ($measureOutput && preg_match('/\{[^}]+\}/s', $measureOutput, $m)) {
            $stats = json_decode($m[0], true);
        }
        
        // Pass 2: aplicar normalización con valores medidos
        if ($stats &&
            isset($stats['input_i'], $stats['input_tp'],
                  $stats['input_lra'], $stats['input_thresh'],
                  $stats['target_offset'])) {
            // Normalización lineal de dos pasos - más precisa
            $filter = sprintf(
                'loudnorm=I=%s:TP=-1.5:LRA=11:measured_I=%s:measured_TP=%s:measured_LRA=%s:measured_thresh=%s:offset=%s:linear=true',
                $lufsStr,
                $stats['input_i'],
                $stats['input_tp'],
                $stats['input_lra'],
                $stats['input_thresh'],
                $stats['target_offset']
            );
        } else {
            // Fallback: un solo paso si falla la medición
            $filter = "loudnorm=I={$lufsStr}:TP=-1.5:LRA=11";
            if ($debug) error_log('[CastopodAPI] Using single-pass fallback filter');
        }
        
        $convertCmd = sprintf(
            '%s -y -i %s -vn -af %s -acodec libmp3lame -ab 192k -ar 44100 %s',
            escapeshellarg($ffmpeg),
            escapeshellarg($input),
            escapeshellarg($filter),
            escapeshellarg($out)
        );
        exec($convertCmd . ' 2>&1', $convertLines, $convertExitCode);
        $convertLog = implode("\n", $convertLines);
        if ($debug) error_log("[CastopodAPI] Pass 2 exit: {$convertExitCode}, size: " . (file_exists($out) ? filesize($out) : 'N/A'));
        
        if ($convertExitCode !== 0 || !file_exists($out) || filesize($out) < 1024) {
            throw new Exception(
                'FFmpeg no pudo procesar el audio.' . "\n" .
                'Código de salida: ' . $convertExitCode . "\n" .
                'Log completo de FFmpeg:' . "\n" . $convertLog
            );
        }
        
        $this->tmpFiles[] = $out;
        
        return [
            'tmp_name' => $out,
            'name'     => $normalizedName,
            'type'     => 'audio/mpeg',
            'size'     => filesize($out),
        ];
    }

    // -------------------------------------------------------
    // HTTP
    // -------------------------------------------------------
    private function request(
        string  $method,
        string  $endpoint,
        array   $query        = [],
        mixed   $body         = null,
        bool    $multipart    = false,
        bool    $formEncoded  = false
    ): array {
        $url = $this->baseUrl . $endpoint;
        if ($query) $url .= '?' . http_build_query($query);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $headers = [
            'Accept: application/json',
            'Authorization: ' . $this->authHeader,
        ];

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($body !== null) {
                if ($multipart) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                } elseif ($formEncoded) {
                    // application/x-www-form-urlencoded (what getPost() reads)
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));
                } else {
                    $headers[] = 'Content-Type: application/json';
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
                }
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, '');
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($body !== null) {
                $headers[] = 'Content-Type: application/json';
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $raw      = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            throw new Exception("Error de conexión: {$curlErr}");
        }

        $decoded = json_decode($raw, true);

        if (defined('CASTOPOD_DEBUG') && CASTOPOD_DEBUG) {
            error_log("[CastopodAPI] {$method} {$url} → HTTP {$httpCode}: " . substr($raw, 0, 500));
        }

        if ($httpCode >= 400) {
            $msg = $decoded['messages']['error']
                ?? $decoded['message']
                ?? $decoded['error']
                ?? substr($raw, 0, 400);
            throw new Exception("Castopod HTTP {$httpCode} [{$method} {$endpoint}]: {$msg}");
        }

        return $decoded ?? [];
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------
    private function slugify(string $text): string {
        $map = ['á'=>'a','à'=>'a','ä'=>'a','â'=>'a','ã'=>'a',
        'é'=>'e','è'=>'e','ë'=>'e','ê'=>'e',
        'í'=>'i','ì'=>'i','ï'=>'i','î'=>'i',
        'ó'=>'o','ò'=>'o','ö'=>'o','ô'=>'o','õ'=>'o',
        'ú'=>'u','ù'=>'u','ü'=>'u','û'=>'u','ñ'=>'n'];
        $text = mb_strtolower(strtr($text, $map), 'UTF-8');
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', trim($text));
        return substr($text, 0, 80) . '-' . time();
    }

    private function validateAudioFile(array $file): void {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['mp3','ogg','wav','aac','flac','m4a','mp4','opus'])) {
            throw new Exception("Formato de audio no soportado: .{$ext}");
        }
        if ($file['size'] > MAX_AUDIO_SIZE) {
            throw new Exception('El archivo supera el límite de ' . round(MAX_AUDIO_SIZE/1024/1024) . ' MB.');
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowedMime = ['audio/mpeg', 'audio/ogg', 'audio/wav', 'audio/x-wav', 'audio/aac', 'audio/flac', 'audio/mp4', 'audio/x-m4a', 'audio/opus', 'audio/webm'];
        if (!$mime || !in_array($mime, $allowedMime)) {
            throw new Exception("Tipo MIME de audio no reconocido: {$mime}.");
        }
    }
} // <--- Asegúrate de que este sea el ÚLTIMO cierre de llave del archivo
