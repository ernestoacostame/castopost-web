<?php
// Colors for podcast indicators
global $tabColors;
$tabColors = $tabColors ?? ['#2d6a4f','#1d3557','#6d2b3d','#4a3728','#2d4356','#4a235a','#1b4332','#7b3f00'];
$allNav = array_merge(
    [['name' => CASTOPOD_PODCAST_HANDLE, 'handle' => CASTOPOD_PODCAST_HANDLE, 'default' => true]],
    array_filter($allPodcasts, fn($p) => $p['handle'] !== CASTOPOD_PODCAST_HANDLE)
);
?>
<div class="page-head fade">
  <h1>Podcasts</h1>
  <p>Gestiona tus podcasts disponibles</p>
</div>

<div class="group-label fade">Podcasts configurados</div>
<div style="margin-bottom:1.5rem" class="fade">
<?php foreach ($allNav as $i => $pod):
  $color = $tabColors[$i % count($tabColors)];
?>
<div class="podcast-card">
  <span class="podcast-card-color" style="background:<?= $color ?>"></span>
  <div class="podcast-card-info">
    <div class="podcast-card-name"><?= htmlspecialchars($pod['name']) ?></div>
    <div class="podcast-card-handle">@<?= htmlspecialchars($pod['handle']) ?></div>
  </div>
  <?php if (!empty($pod['default'])): ?>
    <span class="podcast-card-badge">config.php</span>
  <?php else: ?>
    <form method="POST" action="index.php?page=podcasts" style="margin:0">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="handle" value="<?= htmlspecialchars($pod['handle']) ?>">
      <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Eliminar podcast de la lista?')">Eliminar</button>
    </form>
  <?php endif; ?>
</div>
<?php endforeach; ?>
</div>

<div class="group-label fade">Anadir podcast</div>
<div style="background:var(--bg2);border:1px solid var(--line);border-radius:var(--r);padding:1.1rem" class="fade">
  <form method="POST" action="index.php?page=podcasts">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <input type="hidden" name="action" value="add">
    <div class="field">
      <label>Nombre del podcast</label>
      <input type="text" name="name" placeholder="Mi podcast" required>
    </div>
    <div class="field">
      <label>Handle <span class="sub">(slug en Castopod, ej: mi-podcast)</span></label>
      <input type="text" name="handle" placeholder="mi-podcast" required>
    </div>
    <p style="font-size:.75rem;color:var(--text3);margin-bottom:.85rem">
      El handle se validara contra la API de Castopod antes de guardarse.
    </p>
    <button type="submit" class="btn btn-primary btn-sm">Anadir podcast</button>
  </form>
</div>

<div style="margin-top:1rem;padding:.75rem 1rem;background:var(--bg2);border:1px solid var(--line);border-radius:var(--r);font-size:.78rem;color:var(--text3)">
  Los podcasts se guardan en <span style="font-family:'Noto Sans Mono',monospace">podcasts.json</span> en el servidor, disponibles desde cualquier dispositivo.
</div>
