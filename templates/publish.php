<?php
// Load values from local draft if editing one
$v = $editDraft ?? $_POST;
$draftId = $editDraft['draft_id'] ?? '';
?>
<div class="page-head fade">
  <h1><?= $draftId ? 'Editar borrador' : 'Nuevo episodio' ?></h1>
  <p>Publicando en <strong><?= htmlspecialchars($activePodcast) ?></strong></p>
</div>

<!-- LOCAL DRAFTS SIDEBAR LINK -->
<?php if (!empty($localDrafts) && !$draftId): ?>
<div class="drafts-bar fade">
  <span style="font-size:.78rem;color:var(--text3)">Tienes <?= count($localDrafts) ?> borrador<?= count($localDrafts)>1?'es':'' ?> local<?= count($localDrafts)>1?'es':'' ?>:</span>
  <?php foreach (array_slice($localDrafts, 0, 3) as $ld): ?>
  <a href="index.php?page=publish&podcast=<?= urlencode($activePodcast) ?>&draft_id=<?= urlencode($ld['draft_id']) ?>" class="draft-chip">
    <?= htmlspecialchars($ld['title'] ?: 'Sin titulo') ?>
  </a>
  <?php endforeach; ?>
  <?php if (count($localDrafts) > 3): ?>
  <a href="index.php?page=local_drafts&podcast=<?= urlencode($activePodcast) ?>" class="draft-chip">+<?= count($localDrafts)-3 ?> mas</a>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- MAIN PUBLISH FORM -->
<form method="POST" action="index.php?page=publish&podcast=<?= urlencode($activePodcast) ?>" enctype="multipart/form-data" data-loading id="publishForm">
  <input type="hidden" name="podcast_handle" value="<?= htmlspecialchars($activePodcast) ?>">
  <input type="hidden" name="draft_id" value="<?= htmlspecialchars($draftId) ?>">

  <!-- AUDIO -->
  <div class="group-label">Audio</div>

  <div class="recorder fade" id="recorderBox">
    <canvas class="rec-canvas" id="recCanvas"></canvas>
    <div class="rec-timer" id="recTimer">00:00:00</div>
    <div class="rec-status" id="recStatus">Listo para grabar</div>
    <div class="rec-btns">
      <button type="button" class="btn btn-ghost btn-sm" id="btnRec">Grabar</button>
      <button type="button" class="btn btn-ghost btn-sm" id="btnPause" disabled>Pausar</button>
      <button type="button" class="btn btn-ghost btn-sm" id="btnStop" disabled>Detener</button>
      <button type="button" class="btn btn-ghost btn-sm" id="btnDiscard" style="display:none">Descartar</button>
    </div>
    <audio id="recPlayback" controls style="display:none"></audio>
    <input type="hidden" name="recorded_audio_data" id="recData">
    <input type="hidden" name="recorded_audio_name" id="recName">
    <input type="hidden" name="recorded_audio_mime" id="recMime">
  </div>

  <div class="or-line" style="margin:1rem 0">o sube un archivo</div>

  <div class="field">
    <div class="upload-zone">
      <input type="file" name="audio_file" accept="audio/*,.mp3,.ogg,.wav,.aac,.flac,.m4a,.opus">
      <div class="upload-icon">&#127911;</div>
      <div class="upload-main">Arrastra o selecciona un audio</div>
      <div class="upload-sub">MP3 - M4A - WAV - FLAC - max <?= round(MAX_AUDIO_SIZE/1024/1024) ?> MB</div>
      <div class="upload-name"></div>
    </div>
  </div>

  <div class="or-line">o usa una URL</div>

  <div class="field">
    <label for="audio_url">URL del audio</label>
    <input type="url" id="audio_url" name="audio_url" placeholder="https://example.com/audio.mp3" value="<?= htmlspecialchars($v['audio_url'] ?? '') ?>">
  </div>

  <div class="divider"></div>

  <!-- METADATA -->
  <div class="group-label">Informacion del episodio</div>

  <div class="field">
    <label for="ep-title">Titulo <span class="req">*</span></label>
    <input type="text" id="ep-title" name="title" required placeholder="Titulo del episodio" value="<?= htmlspecialchars($v['title'] ?? '') ?>">
  </div>

  <div class="field">
    <label for="ep-slug">Slug <span class="sub">(auto-generado)</span></label>
    <input type="text" id="ep-slug" name="slug" placeholder="titulo-del-episodio" value="<?= htmlspecialchars($v['slug'] ?? '') ?>">
  </div>

  <!-- DESCRIPTION with templates -->
  <div class="field">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.3rem">
      <label for="ep-desc" style="margin:0">Descripcion <span class="req">*</span></label>
      <?php if (!empty($templates)): ?>
      <div style="display:flex;align-items:center;gap:.4rem">
        <select id="tplPicker" style="padding:.2rem .6rem;font-size:.72rem;height:auto;background:var(--bg3);border-color:var(--line2);color:var(--text2)">
          <option value="">Usar plantilla...</option>
          <?php foreach ($templates as $tpl): ?>
          <option value="<?= htmlspecialchars(addslashes($tpl['description'])) ?>"><?= htmlspecialchars($tpl['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <a href="index.php?page=templates" class="btn btn-ghost btn-sm" style="padding:.2rem .5rem;font-size:.72rem">Editar</a>
      </div>
      <?php else: ?>
      <a href="index.php?page=templates" style="font-size:.72rem;color:var(--text3)">+ Crear plantilla</a>
      <?php endif; ?>
    </div>
    <textarea id="ep-desc" name="description" required placeholder="Notas del episodio, enlaces, creditos..."><?= htmlspecialchars($v['description'] ?? '') ?></textarea>
  </div>

  <div class="field-row">
    <div class="field">
      <label for="ep-season">Temporada</label>
      <input type="number" id="ep-season" name="season_number" min="1" placeholder="1" 
             value="<?= htmlspecialchars($v['season_number'] ?? $defaultSeason ?? '') ?>">
    </div>
    <div class="field">
      <label for="ep-num">Episodio <span class="sub">(auto según temporada)</span></label>
      <input type="number" id="ep-num" name="episode_number" min="0"
             placeholder="Auto"
             value="<?= htmlspecialchars($v['episode_number'] ?? $defaultEpisode ?? '') ?>">
    </div>
  </div>

  <div class="divider"></div>
  <div class="group-label">Opciones</div>

  <div class="field-row">
    <div class="field">
      <label for="ep-type">Tipo</label>
      <select id="ep-type" name="episode_type">
        <option value="full"    <?= ($v['episode_type']??'full')==='full'   ?'selected':'' ?>>Full</option>
        <option value="trailer" <?= ($v['episode_type']??'')==='trailer'    ?'selected':'' ?>>Trailer</option>
        <option value="bonus"   <?= ($v['episode_type']??'')==='bonus'      ?'selected':'' ?>>Bonus</option>
      </select>
    </div>
    <div class="field">
      <label for="ep-date">Publicacion</label>
      <input type="datetime-local" id="ep-date" name="published_at" value="<?= htmlspecialchars($v['published_at'] ?? date('Y-m-d\TH:i')) ?>">
    </div>
  </div>

  <div class="field">
    <label class="check-row">
      <input type="checkbox" name="explicit" <?= !empty($v['explicit'])?'checked':'' ?>>
      Contenido explicito
    </label>
  </div>

  <div class="field">
    <label>Portada del episodio <span class="sub">(opcional)</span></label>
    <div class="upload-zone">
      <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp">
      <div class="upload-icon">&#128444;</div>
      <div class="upload-main">Imagen de portada</div>
      <div class="upload-sub">JPG - PNG - min 1400x1400 px</div>
      <div class="upload-name"></div>
    </div>
  </div>

  <div class="divider"></div>

  <!-- PREVIEW SECTION -->
  <div class="group-label">Vista previa</div>
  <div class="preview-box fade" id="previewBox">
    <div class="preview-title" id="prevTitle">--</div>
    <div class="preview-meta" id="prevMeta">--</div>
    <div class="preview-desc" id="prevDesc"></div>
  </div>

  <div class="divider"></div>

  <!-- LUFS NORMALIZATION -->
  <div class="group-label">Normalización de audio (LUFS)</div>
  <div class="field">
    <p style="margin-top:0;margin-bottom:.5rem;font-size:.85rem;color:var(--text3)">
      Selecciona el nivel LUFS al que normalizar el audio. Valores comunes: podcast (-16), música (-14), voiceover (-24)
    </p>
    
    <div class="lufs-buttons" style="display:flex;gap:.5rem;margin-bottom:.8rem">
      <button type="button" class="btn btn-ghost btn-sm lufs-btn" data-lufs="-14">-14</button>
      <button type="button" class="btn btn-ghost btn-sm lufs-btn active" data-lufs="-16">-16</button>
      <button type="button" class="btn btn-ghost btn-sm lufs-btn" data-lufs="-18">-18</button>
      <button type="button" class="btn btn-ghost btn-sm lufs-btn" data-lufs="-24">-24</button>
    </div>
    
    <div style="display:flex;align-items:center;gap:.5rem">
      <label for="lufs_custom" style="font-size:.85rem">Otro valor:</label>
      <input type="number" id="lufs_custom" 
             name="lufs_custom" 
             min="-70" max="0" step="0.1"
             placeholder="-19.5"
             style="width:120px;padding:.3rem .5rem;font-size:.85rem">
      <span style="font-size:.75rem;color:var(--text3)">LUFS</span>
    </div>
  </div>

  <input type="hidden" name="lufs_level" id="lufs_level" value="-16">

  <div class="divider"></div>

  <!-- ACTIONS -->
  <div style="display:flex;gap:.6rem;flex-wrap:wrap">
    <button type="submit" class="btn btn-primary" style="flex:1">
      &#128640; Publicar en Castopod
    </button>
    <button type="button" class="btn btn-ghost" id="btnSaveDraft">
      &#128190; Guardar borrador
    </button>
    <a href="index.php?page=dashboard&podcast=<?= urlencode($activePodcast) ?>" class="btn btn-ghost">Cancelar</a>
  </div>
  <?php if ($draftId): ?>
  <p style="font-size:.75rem;color:var(--green);margin-top:.6rem">Editando borrador guardado el <?= htmlspecialchars($editDraft['saved_at'] ?? '') ?></p>
  <?php endif; ?>
</form>

<!-- SAVE DRAFT FORM (hidden, submitted by JS) -->
<form method="POST" action="index.php?page=save_draft&podcast=<?= urlencode($activePodcast) ?>" id="draftForm" style="display:none">
  <input type="hidden" name="draft_id" id="draftFormId" value="<?= htmlspecialchars($draftId) ?>">
  <input type="hidden" name="title"          id="df_title">
  <input type="hidden" name="description"    id="df_desc">
  <input type="hidden" name="slug"           id="df_slug">
  <input type="hidden" name="season_number"  id="df_season">
  <input type="hidden" name="episode_number" id="df_num">
  <input type="hidden" name="episode_type"   id="df_type">
  <input type="hidden" name="published_at"   id="df_date">
  <input type="hidden" name="explicit"       id="df_explicit">
  <input type="hidden" name="audio_url"      id="df_audiourl">
  <input type="hidden" name="lufs_level"     id="df_lufs" value="-16">
</form>

<script>
// Check for MediaRecorder support with more detail
const recorderBox = document.getElementById('recorderBox');
if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    recorderBox.innerHTML = 
        '<div style="color:var(--text3);padding:1rem;text-align:center">' +
        'getUserMedia no está soportado en este navegador.<br>' +
        'Asegúrate de usar HTTPS en producción.</div>';
} else if (!window.MediaRecorder) {
    recorderBox.innerHTML = 
        '<div style="color:var(--text3);padding:1rem;text-align:center">' +
        'MediaRecorder no está soportado en este navegador.<br>' +
        'Usa Firefox 25+, Chrome 47+, Edge 79+, o Safari 14.1+.</div>';
}

// Debug: Log browser info
console.log('Browser info:', {
    userAgent: navigator.userAgent,
    mediaDevices: !!navigator.mediaDevices,
    getUserMedia: !!navigator.mediaDevices?.getUserMedia,
    MediaRecorder: !!window.MediaRecorder,
    availableMimeTypes: (() => {
        const types = [
            'audio/webm;codecs=opus',
            'audio/webm',
            'audio/ogg;codecs=opus',
            'audio/ogg',
            'audio/mp4',
            'audio/mp4;codecs=mp4a'
        ];
        return types.filter(t => MediaRecorder.isTypeSupported(t));
    })()
});

// Test microphone access
async function testMicrophone() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ 
            audio: true 
        });
        const tracks = stream.getTracks();
        console.log('Microphone test successful, tracks:', tracks.length);
        tracks.forEach(track => track.stop());
        return true;
    } catch (e) {
        console.error('Microphone test failed:', e);
        return false;
    }
}

// Run test on page load
document.addEventListener('DOMContentLoaded', async () => {
    const hasMic = await testMicrophone();
    if (!hasMic) {
        console.warn('Microphone access may be blocked by browser');
    }
});

// RECORDER
(function(){
  let mr, chunks=[], stream, raf, secs=0, iv, paused=false, analyser, dataArr;
  const btnRec=document.getElementById('btnRec'), btnPause=document.getElementById('btnPause'),
        btnStop=document.getElementById('btnStop'), btnDisc=document.getElementById('btnDiscard'),
        timer=document.getElementById('recTimer'), status=document.getElementById('recStatus'),
        playback=document.getElementById('recPlayback'), recData=document.getElementById('recData'),
        recName=document.getElementById('recName'), canvas=document.getElementById('recCanvas'),
        ctx=canvas.getContext('2d');

  function fmt(s){ return [Math.floor(s/3600),Math.floor((s%3600)/60),s%60].map(n=>String(n).padStart(2,'0')).join(':'); }
  function draw(){
    if(!analyser) return; raf=requestAnimationFrame(draw);
    analyser.getByteTimeDomainData(dataArr);
    const W=canvas.width, H=canvas.height;
    ctx.clearRect(0,0,W,H);
    // Read CSS variable at draw time so theme changes reflect immediately
    ctx.strokeStyle = paused
      ? (window.getText3Color ? window.getText3Color() : '#55546a')
      : (window.getAccentColor ? window.getAccentColor() : '#c9b8ff');
    ctx.lineWidth=2; ctx.beginPath();
    const step=W/dataArr.length; let x=0;
    for(let i=0;i<dataArr.length;i++){
      const v=dataArr[i]/128.0;
      const y=v*(H/2);
      i===0?ctx.moveTo(x,y):ctx.lineTo(x,y);
      x+=step;
    }
    ctx.lineTo(W, H/2);
    ctx.stroke();
  }
  function resizeCanvas(){
    canvas.width  = canvas.offsetWidth  || canvas.parentElement.clientWidth || 300;
    canvas.height = 48;
  }
  window.addEventListener('resize', resizeCanvas);
  // Initial size after layout is ready
  requestAnimationFrame(resizeCanvas);

  btnRec.addEventListener('click',async()=>{
    try{ 
        // Try with specific constraints for Firefox compatibility
        const constraints = {
            audio: {
                channelCount: 1,
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl: true
            }
        };
        stream = await navigator.mediaDevices.getUserMedia(constraints); 
    }
    catch(e){ 
        status.textContent='Sin acceso al microfono: ' + e.message; 
        return; 
    }
    
    const aC = new AudioContext(); 
    analyser = aC.createAnalyser(); 
    analyser.fftSize = 2048;
    dataArr = new Uint8Array(analyser.frequencyBinCount);
    aC.createMediaStreamSource(stream).connect(analyser); 
    draw();
    
    chunks = []; 
    paused = false; 
    secs = 0;
    
    // Get available MIME types for the current browser
    const mimeTypes = [
        'audio/ogg;codecs=opus',
        'audio/ogg',
        'audio/webm;codecs=opus',
        'audio/webm',
        'audio/mp4',
        'audio/mp4;codecs=mp4a',
        ''
    ];
    
    let selectedMimeType = '';
    for (const mime of mimeTypes) {
        if (!mime || MediaRecorder.isTypeSupported(mime)) {
            selectedMimeType = mime;
            break;
        }
    }
    
    console.log('Selected MIME type:', selectedMimeType);
    
    // Try to create MediaRecorder with options
    let options = {};
    if (selectedMimeType) {
        options.mimeType = selectedMimeType;
    }
    
    // Add audio bits per second for better quality
    options.audioBitsPerSecond = 128000;
    
    try {
        mr = new MediaRecorder(stream, options);
    } catch (e) {
        console.warn('Failed to create MediaRecorder with options:', e);
        // Fallback to default constructor
        try {
            mr = new MediaRecorder(stream);
        } catch (e2) {
            status.textContent = 'Error al crear grabador: ' + e2.message;
            stream.getTracks().forEach(t => t.stop());
            return;
        }
    }
    
    // Handle MediaRecorder errors
    mr.onerror = function(event) {
        console.error('MediaRecorder error:', event.error);
        status.textContent = 'Error en grabación: ' + event.error;
        btnRec.disabled = false;
        btnPause.disabled = true;
        btnStop.disabled = true;
        clearInterval(iv);
        if (stream) {
            stream.getTracks().forEach(t => t.stop());
        }
    };
    
    mr.ondataavailable = e => {
        if (e.data && e.data.size > 0) {
            chunks.push(e.data);
        }
    };
    
    mr.onstop = () => {
        cancelAnimationFrame(raf); 
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        if (chunks.length === 0) {
            status.textContent = 'Error: No se capturó audio';
            return;
        }
        
        const blob = new Blob(chunks, { 
            type: selectedMimeType || mr.mimeType || 'audio/webm' 
        });
        
        console.log('Recording blob size:', blob.size, 'type:', blob.type);
        
        if (blob.size < 100) {
            status.textContent = 'Error: Grabación demasiado corta o vacía (' + blob.size + ' bytes). Intenta de nuevo.';
            return;
        }
        
        playback.src = URL.createObjectURL(blob); 
        playback.style.display = 'block';
        
        const r = new FileReader();
        r.onloadend = () => {
          // CORRECCIÓN: Limpiar el prefijo data:audio/xxx;base64,
          const fullResult = r.result;
          const base64Clean = fullResult.split(',')[1];

          // CORRECCIÓN: Usar getElementById para mayor precisión
          document.getElementById('recData').value = base64Clean;

          console.log('recData value length:', document.getElementById('recData').value.length);

          // Determine file extension based on MIME type
          let extension = 'webm';
        const mime = selectedMimeType || mr.mimeType || '';
        if (mime.includes('ogg')) {
          extension = 'ogg';
        } else if (mime.includes('mp4')) {
          extension = 'mp4';
        } else if (mime.includes('webm')) {
          extension = 'webm';
        }

        recName.value = 'grabacion-' + Date.now() + '.' + extension;
        status.textContent = 'Grabación completada (' + Math.round(blob.size / 1024) + ' KB)';
        };
        r.readAsDataURL(blob);
        // Store the real MIME type for server-side processing
        document.getElementById('recMime').value = blob.type;
        
        if (stream) {
            stream.getTracks().forEach(t => t.stop());
        }
    };
    
    // Start recording with timeslice of 1000ms (1 second)
    try {
        mr.start(1000); 
    } catch (e) {
        status.textContent = 'Error al iniciar grabación: ' + e.message;
        stream.getTracks().forEach(t => t.stop());
        return;
    }
    
    iv = setInterval(() => {
        if (!paused) {
            secs++;
            timer.textContent = fmt(secs);
        }
    }, 1000);
    
    timer.classList.add('on'); 
    status.textContent = 'Grabando...';
    btnRec.disabled = true; 
    btnPause.disabled = false; 
    btnStop.disabled = false; 
    btnDisc.style.display = 'none';
    playback.style.display = 'none'; 
    document.getElementsByName('recorded_audio_data')[0].value = '';
  });
  btnPause.addEventListener('click',()=>{
    if(!mr) return;
    
    try {
        if(paused){
            mr.resume();
            paused = false;
            timer.classList.add('on');
            status.textContent = 'Grabando...';
            btnPause.textContent = 'Pausar';
        } else {
            mr.pause();
            paused = true;
            timer.classList.remove('on');
            status.textContent = 'En pausa';
            btnPause.textContent = 'Reanudar';
        }
    } catch (e) {
        console.error('Pause/resume error:', e);
        status.textContent = 'Error: ' + e.message;
    }
  });
  btnStop.addEventListener('click',()=>{
    if(!mr)return; clearInterval(iv); timer.classList.remove('on'); status.textContent='Listo';
    mr.stop(); btnRec.disabled=false; btnPause.disabled=true; btnStop.disabled=true; btnDisc.style.display='inline-flex';
  });
  btnDisc.addEventListener('click',()=>{
    playback.style.display='none'; document.getElementsByName('recorded_audio_data')[0].value=''; recName.value=''; secs=0;
    timer.textContent='00:00:00'; status.textContent='Listo para grabar';
    btnDisc.style.display='none'; ctx.clearRect(0,0,canvas.width,canvas.height);
  });
})();

// VALIDATE audio source
document.getElementById('publishForm').addEventListener('submit',function(e){
  const hasFile=document.querySelector('input[name="audio_file"]')?.files.length>0;
  const hasUrl=document.getElementById('audio_url')?.value.trim()!=='';
  const hasRec=document.getElementsByName('recorded_audio_data')[0]?.value!=='';
  if(!hasFile&&!hasUrl&&!hasRec){ e.preventDefault(); alert('Necesitas grabar, subir o enlazar un audio.'); }
});

// TEMPLATE PICKER
const tplPicker = document.getElementById('tplPicker');
if(tplPicker){
  tplPicker.addEventListener('change', function(){
    const val = this.value;
    if(val){ document.getElementById('ep-desc').value = val; this.value=''; }
  });
}

// LIVE PREVIEW
function updatePreview(){
  const title = document.getElementById('ep-title')?.value || '--';
  const num   = document.getElementById('ep-num')?.value;
  const season= document.getElementById('ep-season')?.value;
  const type  = document.getElementById('ep-type')?.value;
  const desc  = document.getElementById('ep-desc')?.value || '';
  const date  = document.getElementById('ep-date')?.value;

  document.getElementById('prevTitle').textContent = title;

  let meta = [];
  if(num) meta.push('Ep. ' + num);
  if(season) meta.push('T' + season);
  if(type && type !== 'full') meta.push(type);
  if(date) meta.push(new Date(date).toLocaleDateString('es-ES'));
  document.getElementById('prevMeta').textContent = meta.join(' · ') || '--';

  // Simple markdown-ish rendering
  const html = desc
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/\*\*(.+?)\*\*/g,'<strong>$1</strong>')
    .replace(/\*(.+?)\*/g,'<em>$1</em>')
    .replace(/^- (.+)/gm,'<li>$1</li>')
    .replace(/\n/g,'<br>');
  document.getElementById('prevDesc').innerHTML = html;
}

['ep-title','ep-desc','ep-num','ep-season','ep-type','ep-date'].forEach(id => {
  const el = document.getElementById(id);
  if(el) el.addEventListener('input', updatePreview);
});
updatePreview();

// AUTOMATIC EPISODE NUMBER BY SEASON
(function(){
    // Convert PHP array to JavaScript object
    const episodesBySeason = <?= json_encode($episodesBySeason) ?>;
    
    const seasonInput = document.getElementById('ep-season');
    const episodeInput = document.getElementById('ep-num');
    
    // Track if episode number was manually modified
    let episodeManuallyChanged = false;
    
    // Store original episode number when page loads (if any)
    const originalEpisodeValue = episodeInput.value;
    
    // Function to update episode number based on season
    function updateEpisodeBySeason() {
        if (episodeManuallyChanged && episodeInput.value !== '') {
            // User manually changed the episode number, don't auto-update
            return;
        }
        
        const season = seasonInput.value;
        
        if (season === '' || season === null) {
            // No season selected
            if (originalEpisodeValue === '') {
                episodeInput.value = '';
            }
            return;
        }
        
        if (episodesBySeason[season]) {
            // Season exists, set to last episode + 1
            episodeInput.value = parseInt(episodesBySeason[season]) + 1;
        } else {
            // New season, start at 1
            episodeInput.value = 1;
        }
    }

    // Store initial season and episode values for tracking
    let initialSeasonValue = seasonInput.value;
    let initialEpisodeValue = episodeInput.value;

    // Listen for season changes
    seasonInput.addEventListener('change', function() {
        // Only mark as manually changed if user actually typed something different
        if (this.value !== initialSeasonValue) {
            episodeManuallyChanged = false; // Reset manual change when season changes
        }
        updateEpisodeBySeason();
    });

    seasonInput.addEventListener('input', function() {
        updateEpisodeBySeason();
    });
    
    // Listen for manual episode number changes
    episodeInput.addEventListener('input', function() {
        if (this.value !== '') {
            episodeManuallyChanged = true;
        }
    });
    
    // Also update when page loads if season is already set
    document.addEventListener('DOMContentLoaded', function() {
        // Check if we have initial values from PHP
        if (seasonInput.value !== '' && episodeInput.value !== '') {
            // We have values from PHP, don't mark as manually changed yet
            // The user hasn't interacted with the episode field
            initialSeasonValue = seasonInput.value;
            initialEpisodeValue = episodeInput.value;
        } else if (seasonInput.value !== '') {
            // Only season is set, update episode
            updateEpisodeBySeason();
        }
        
        // If we're editing a draft with a season but no episode number,
        // auto-fill the episode number
        if (seasonInput.value !== '' && episodeInput.value === '' && !episodeManuallyChanged) {
            updateEpisodeBySeason();
        }
    });
    
    // Reset manual change flag if user clears episode number
    episodeInput.addEventListener('change', function() {
        if (this.value === '') {
            episodeManuallyChanged = false;
        }
    });
})();

// LUFS SELECTION
(function(){
  const lufsButtons = document.querySelectorAll('.lufs-btn');
  const lufsCustom = document.getElementById('lufs_custom');
  const lufsHidden = document.getElementById('lufs_level');
  
  // Manejar clic en botones LUFS
  lufsButtons.forEach(btn => {
    btn.addEventListener('click', function() {
      // Remover clase 'active' de todos los botones
      lufsButtons.forEach(b => b.classList.remove('active'));
      
      // Añadir 'active' al botón clickeado
      this.classList.add('active');
      
      // Establecer valor en campo oculto
      const val = this.getAttribute('data-lufs');
      lufsHidden.value = val;
      lufsCustom.value = '';
    });
  });
  
  // Manejar entrada personalizada
  lufsCustom.addEventListener('input', function() {
    // Remover 'active' de todos los botones
    lufsButtons.forEach(b => b.classList.remove('active'));
    
    // Actualizar valor oculto si hay entrada
    if (this.value.trim() !== '') {
      lufsHidden.value = parseFloat(this.value).toFixed(1);
    }
  });
  
  // Resetear cuando se borra el campo personalizado
  lufsCustom.addEventListener('change', function() {
    if (this.value.trim() === '') {
      // Restaurar botón -16 como predeterminado
      const defaultBtn = document.querySelector('.lufs-btn[data-lufs="-16"]');
      if (defaultBtn) {
        defaultBtn.classList.add('active');
        lufsHidden.value = '-16';
      }
    }
  });
})();

// SAVE DRAFT
document.getElementById('btnSaveDraft')?.addEventListener('click', function(){
  document.getElementById('df_title').value   = document.getElementById('ep-title')?.value||'';
  document.getElementById('df_desc').value    = document.getElementById('ep-desc')?.value||'';
  document.getElementById('df_slug').value    = document.getElementById('ep-slug')?.value||'';
  document.getElementById('df_season').value  = document.getElementById('ep-season')?.value||'';
  document.getElementById('df_num').value     = document.getElementById('ep-num')?.value||'';
  document.getElementById('df_type').value    = document.getElementById('ep-type')?.value||'full';
  document.getElementById('df_date').value    = document.getElementById('ep-date')?.value||'';
  document.getElementById('df_explicit').value= document.querySelector('input[name="explicit"]')?.checked?'1':'';
  document.getElementById('df_audiourl').value= document.getElementById('audio_url')?.value||'';
  document.getElementById('df_lufs').value    = document.getElementById('lufs_level')?.value || '-16';
  document.getElementById('draftForm').submit();
});
</script>
