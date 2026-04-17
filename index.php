<?php
session_start();
require_once 'config.php';
require_once 'auth.php';
require_once 'castopod.php';
require_once 'podcasts_store.php';
require_once 'drafts_store.php';
require_once 'templates_store.php';

$auth      = new Auth();
$castopod  = new CastopodAPI(CASTOPOD_URL, CASTOPOD_API_USER, CASTOPOD_API_PASSWORD);
$store     = new PodcastsStore(PODCASTS_FILE);
$draftSt   = new LocalDraftsStore(DRAFTS_FILE);
$templateSt= new TemplatesStore(TEMPLATES_FILE);

$page    = $_GET['page'] ?? 'login';
$error   = $success = '';

// ── Logout ────────────────────────────────────────────────
if ($page === 'logout') { $auth->logout(); header('Location: index.php'); exit; }

// ── Login ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $page === 'login') {
    if ($auth->login($_POST['password'] ?? '')) {
        header('Location: index.php?page=dashboard'); exit;
    }
    $error = 'Contrasena incorrecta.';
}

// ── Guard ─────────────────────────────────────────────────
if ($page !== 'login' && !$auth->isLoggedIn()) {
    header('Location: index.php?page=login'); exit;
}

// ── Active podcast ────────────────────────────────────────
$allPodcasts   = $store->all();
$activePodcast = $_GET['podcast'] ?? $_SESSION['active_podcast'] ?? CASTOPOD_PODCAST_HANDLE;
$_SESSION['active_podcast'] = $activePodcast;

// ── Podcast store actions ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $page === 'podcasts') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $name   = trim($_POST['name'] ?? '');
        $handle = trim($_POST['handle'] ?? '');
        if ($name && $handle) {
            try {
                $castopod->getPodcastIdByHandle($handle);
                $store->add($name, $handle);
                $success = "Podcast '{$name}' anadido.";
            } catch (Exception $e) { $error = $e->getMessage(); }
        } else { $error = 'Nombre y handle son obligatorios.'; }
    } elseif ($action === 'delete') {
        $store->remove($_POST['handle'] ?? '');
        $success = 'Podcast eliminado.';
    }
}

// ── Template actions ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $page === 'templates') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $name = trim($_POST['tpl_name'] ?? '');
        $body = $_POST['tpl_body'] ?? '';
        if ($name && $body) {
            $templateSt->add($name, $body);
            $success = "Plantilla '{$name}' guardada.";
        } else { $error = 'Nombre y contenido son obligatorios.'; }
    } elseif ($action === 'update') {
        $templateSt->update($_POST['tpl_id'], trim($_POST['tpl_name'] ?? ''), $_POST['tpl_body'] ?? '');
        $success = 'Plantilla actualizada.';
    } elseif ($action === 'delete') {
        $templateSt->delete($_POST['tpl_id'] ?? '');
        $success = 'Plantilla eliminada.';
    }
}

// ── Local draft actions ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $page === 'save_draft') {
    $fields = [
        'title'          => trim($_POST['title'] ?? ''),
        'description'    => trim($_POST['description'] ?? ''),
        'slug'           => trim($_POST['slug'] ?? ''),
        'season_number'  => $_POST['season_number']  ?: '',
        'episode_number' => $_POST['episode_number'] ?: '',
        'episode_type'   => $_POST['episode_type']   ?? 'full',
        'published_at'   => $_POST['published_at']   ?? '',
        'explicit'       => !empty($_POST['explicit']) ? '1' : '',
        'draft_id'       => $_POST['draft_id']       ?? '',
    ];
    $id = $draftSt->save_draft($activePodcast, $fields);
    $success = 'Borrador guardado.';
    header("Location: index.php?page=publish&podcast=" . urlencode($activePodcast) . "&draft_id={$id}");
    exit;
}

if (isset($_GET['delete_draft']) && $auth->isLoggedIn()) {
    $draftSt->delete_draft($activePodcast, $_GET['delete_draft']);
    header('Location: index.php?page=local_drafts&podcast=' . urlencode($activePodcast)); exit;
}

// ── Publish draft from Castopod ───────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $page === 'publish_draft') {
    try {
        $episodeId = (int)($_POST['episode_id'] ?? 0);
        if (!$episodeId) throw new Exception('ID de episodio invalido.');
        $castopod->publishDraft($episodeId, CASTOPOD_USER_ID);
        $success = 'Borrador publicado correctamente.';
        $page = 'drafts';
    } catch (Exception $e) { $error = $e->getMessage(); $page = 'drafts'; }
}

// ── Publish episode ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $page === 'publish') {
    try {
        $data = [
            'title'          => trim($_POST['title'] ?? ''),
            'description'    => trim($_POST['description'] ?? ''),
            'season_number'  => $_POST['season_number']  ?: null,
            'episode_number' => $_POST['episode_number'] ?: null,
            'type'           => $_POST['episode_type']   ?? 'full',
            'explicit'       => !empty($_POST['explicit']),
            'slug'           => trim($_POST['slug'] ?? ''),
            'published_at'   => $_POST['published_at']   ?? date('Y-m-d\TH:i:s'),
        ];
        if (empty($data['title'])) throw new Exception('El titulo es obligatorio.');

        $audioFile = null;
        if (!empty($_FILES['audio_file']['name'])) {
            $audioFile = $_FILES['audio_file'];
        } elseif (!empty($_POST['recorded_audio_data'])) {
            $base64  = preg_replace('/^data:[^;]+;base64,/', '', $_POST['recorded_audio_data']);
            $tmpPath = UPLOAD_TMP_DIR . '/rec_' . uniqid() . '.webm';
            if (!is_dir(UPLOAD_TMP_DIR)) mkdir(UPLOAD_TMP_DIR, 0700, true);
            file_put_contents($tmpPath, base64_decode($base64));
            $audioFile = [
                'tmp_name' => $tmpPath,
                'name'     => $_POST['recorded_audio_name'] ?? 'grabacion.webm',
                'type'     => 'audio/webm',
                'size'     => filesize($tmpPath),
                '_cleanup' => true,
            ];
        } elseif (empty($_POST['audio_url'])) {
            throw new Exception('Debes grabar, subir o enlazar un archivo de audio.');
        } else {
            $data['audio_url'] = $_POST['audio_url'];
        }

        $coverFile     = !empty($_FILES['cover_image']['name']) ? $_FILES['cover_image'] : null;
        $podcastHandle = !empty($_POST['podcast_handle']) ? trim($_POST['podcast_handle']) : CASTOPOD_PODCAST_HANDLE;

        $result = $castopod->publishEpisode($podcastHandle, CASTOPOD_USER_ID, $data, $audioFile, $coverFile);

        if (!empty($audioFile['_cleanup']) && file_exists($audioFile['tmp_name'])) {
            unlink($audioFile['tmp_name']);
        }

        // Delete local draft if it was used
        if (!empty($_POST['draft_id'])) {
            $draftSt->delete_draft($podcastHandle, $_POST['draft_id']);
        }

        $success = 'Episodio publicado correctamente.' . (CASTOPOD_DEBUG ? ' [debug] ' . json_encode($result) : '');

    } catch (Exception $e) {
        $error = $e->getMessage();
        if (!empty($audioFile['_cleanup']) && !empty($audioFile['tmp_name']) && file_exists($audioFile['tmp_name'])) {
            unlink($audioFile['tmp_name']);
        }
    }
}

// ── Tmp cleanup ───────────────────────────────────────────
if (isset($_GET['deltmp']) && $auth->isLoggedIn()) {
    $t = UPLOAD_TMP_DIR . '/' . basename($_GET['deltmp']);
    if (is_file($t)) unlink($t);
    header('Location: index.php?page=dashboard&podcast=' . urlencode($activePodcast)); exit;
}
if (isset($_GET['deltmpall']) && $auth->isLoggedIn()) {
    foreach (glob(UPLOAD_TMP_DIR . '/*') as $f) { if (is_file($f)) unlink($f); }
    header('Location: index.php?page=dashboard&podcast=' . urlencode($activePodcast)); exit;
}

// ── Load episodes/podcast info ────────────────────────────
$episodes = $drafts = [];
$podcastId   = null;
$podcastInfo = null;

if ($auth->isLoggedIn() && in_array($page, ['dashboard', 'episodes', 'drafts', 'publish', 'local_drafts'])) {
    try {
        $podcastId   = $castopod->getPodcastIdByHandle($activePodcast);
        $podcastInfo = $castopod->getPodcastById($podcastId);

        if (in_array($page, ['dashboard'])) {
            $all = $castopod->getRecentEpisodes($podcastId, 5);
        } else {
            $all = $castopod->getAllEpisodes($podcastId);
        }

        foreach ($all as $ep) {
            // Castopod returns published_at as an object: {"date":"...","timezone_type":3,"timezone":"UTC"}
            // Normalize it to a plain string for consistent use in templates
            if (isset($ep['published_at']['date'])) {
                $ep['published_at'] = $ep['published_at']['date'];
            }
            if (isset($ep['created_at']['date'])) {
                $ep['created_at'] = $ep['created_at']['date'];
            }

            if (empty($ep['published_at'])) $drafts[] = $ep;
            else $episodes[] = $ep;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// ── Next episode number per season ───────────────────────────────────
$nextEpisodeNumber = 1;
$episodesBySeason = []; // Nuevo: para guardar el último episodio por temporada
$lastSeason = null;
$lastEpisode = null;

if ($page === 'publish' && $podcastId) {
    try {
        $recent = $castopod->getRecentEpisodes($podcastId, 20); // Aumentar para tener suficiente datos
        $nums = array_filter(array_map(fn($e) => $e['number'] ?? $e['episode_number'] ?? null, $recent));
        if ($nums) $nextEpisodeNumber = max($nums) + 1;
        
        // Calcular el último episodio por temporada
        foreach ($recent as $ep) {
            $season = $ep['season_number'] ?? null;
            $epNum = $ep['number'] ?? $ep['episode_number'] ?? null;
            if ($season !== null && $epNum !== null) {
                if (!isset($episodesBySeason[$season]) || $epNum > $episodesBySeason[$season]) {
                    $episodesBySeason[$season] = $epNum;
                }
                // Calcular la última temporada y episodio
                if ($lastSeason === null || $season > $lastSeason || 
                    ($season == $lastSeason && $epNum > $lastEpisode)) {
                    $lastSeason = $season;
                    $lastEpisode = $epNum;
                }
            }
        }
    } catch (Exception $e) { /* keep defaults */ }
} elseif (!empty($episodes)) {
    $nums = array_filter(array_map(fn($e) => $e['number'] ?? $e['episode_number'] ?? null, $episodes));
    if ($nums) $nextEpisodeNumber = max($nums) + 1;
    
    // Calcular el último episodio por temporada desde todos los episodios
    foreach ($episodes as $ep) {
        $season = $ep['season_number'] ?? null;
        $epNum = $ep['number'] ?? $ep['episode_number'] ?? null;
        if ($season !== null && $epNum !== null) {
            if (!isset($episodesBySeason[$season]) || $epNum > $episodesBySeason[$season]) {
                $episodesBySeason[$season] = $epNum;
            }
            // Calcular la última temporada y episodio
            if ($lastSeason === null || $season > $lastSeason || 
                ($season == $lastSeason && $epNum > $lastEpisode)) {
                $lastSeason = $season;
                $lastEpisode = $epNum;
            }
        }
    }
}

// Si encontramos una última temporada, calcular el próximo episodio
$defaultSeason = null;
$defaultEpisode = null;
if ($lastSeason !== null && $lastEpisode !== null) {
    $defaultSeason = $lastSeason;
    $defaultEpisode = $lastEpisode + 1;
}

// ── Load draft to edit if requested ──────────────────────
$editDraft = null;
if ($page === 'publish' && !empty($_GET['draft_id'])) {
    $editDraft = $draftSt->get_draft($activePodcast, $_GET['draft_id']);
}

// ── Local drafts for sidebar ──────────────────────────────
$localDrafts = $draftSt->get_drafts($activePodcast);
$templates   = $templateSt->all();

// ── Build nav podcast list ────────────────────────────────
$allPodcasts = $store->all();

// Pasar episodios por temporada y valores por defecto al template
$episodesBySeason = $episodesBySeason ?? [];
$defaultSeason = $defaultSeason ?? null;
$defaultEpisode = $defaultEpisode ?? null;

include 'templates/layout.php';
