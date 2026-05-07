<div class="page-head fade">
  <h1>Panel</h1>
</div>

<?php if ($podcastInfo): ?>
<div class="podcast-info-card fade">
  <?php if (!empty($podcastInfo['cover_url'])): ?>
  <img src="<?= htmlspecialchars($podcastInfo['cover_url']) ?>" class="podcast-cover" alt="cover">
  <?php endif; ?>
  <div class="podcast-info-body">
    <div class="podcast-info-title"><?= htmlspecialchars($podcastInfo['title'] ?? $activePodcast) ?></div>
    <div class="podcast-info-desc"><?= htmlspecialchars(strip_tags($podcastInfo['description_html'] ?? '')) ?></div>
    <div class="podcast-info-meta">
      <?php if (!empty($podcastInfo['feed_url'])): ?>
      <a href="<?= htmlspecialchars($podcastInfo['feed_url']) ?>" target="_blank" class="podcast-info-link">RSS</a>
      <?php endif; ?>
      <?php if (!empty($podcastInfo['language_code'])): ?>
      <span><?= strtoupper(htmlspecialchars($podcastInfo['language_code'])) ?></span>
      <?php endif; ?>
      <?php if (!empty($podcastInfo['type'])): ?>
      <span><?= htmlspecialchars($podcastInfo['type']) ?></span>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="stats fade">
  <div class="stat">
    <div class="stat-n">
      <a href="index.php?page=episodes&podcast=<?= urlencode($activePodcast) ?>"><?= count($episodes) ?>+</a>
    </div>
    <div class="stat-l">Publicados</div>
  </div>
  <div class="stat">
    <div class="stat-n">
      <a href="index.php?page=drafts&podcast=<?= urlencode($activePodcast) ?>" style="<?= !empty($drafts)?'color:var(--accent)':'' ?>"><?= count($drafts) ?></a>
    </div>
    <div class="stat-l">En Castopod</div>
  </div>
  <div class="stat">
    <div class="stat-n">
      <a href="index.php?page=local_drafts&podcast=<?= urlencode($activePodcast) ?>" style="<?= !empty($localDrafts)?'color:var(--green)':'' ?>"><?= count($localDrafts) ?></a>
    </div>
    <div class="stat-l">Borradores locales</div>
  </div>
  <div class="stat">
    <div class="stat-n" style="font-size:1.2rem;padding-top:.4rem">
      <?= $nextEpisodeNumber ?>
    </div>
    <div class="stat-l">Proximo ep.</div>
  </div>
</div>

<?php if (!empty($episodes)): $last = $episodes[0]; ?>
<div class="group-label fade">Ultimo publicado</div>
<div class="ep-list fade" style="background:var(--bg2);border:1px solid var(--line);border-radius:var(--r);padding:.25rem .85rem;margin-bottom:1.75rem">
  <div class="ep-item" style="border:none">
    <span class="ep-num"><?= htmlspecialchars($last['number'] ?? '-') ?></span>
    <div class="ep-info">
      <div class="ep-title"><?= htmlspecialchars($last['title'] ?? 'Sin titulo') ?></div>
      <?php if (!empty($last['published_at']) && ($ts = strtotime($last['published_at']))): ?>
      <div class="ep-meta"><?= date('d/m/Y H:i', $ts) ?></div>
      <?php endif; ?>
    </div>
    <?php if (!empty($last['audio_url'])): ?>
    <audio controls preload="none" style="height:32px;width:160px;flex-shrink:0">
      <source src="<?= htmlspecialchars($last['audio_url']) ?>">
    </audio>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($localDrafts)): ?>
<div class="group-label fade">Borradores locales</div>
<div class="ep-list fade" style="background:var(--bg2);border:1px solid var(--line);border-radius:var(--r);padding:.25rem .85rem;margin-bottom:1.75rem">
  <?php foreach (array_slice($localDrafts, 0, 3) as $ld):
    $draftUrl = 'index.php?page=publish&podcast=' . urlencode($activePodcast) . '&draft_id=' . urlencode($ld['draft_id']);
  ?>
  <a href="<?= $draftUrl ?>" class="ep-item" style="text-decoration:none;color:inherit">
    <div class="ep-info">
      <div class="ep-title" style="color:var(--green)"><?= htmlspecialchars($ld['title'] ?: 'Sin titulo') ?></div>
      <div class="ep-meta">Guardado <?= htmlspecialchars($ld['saved_at'] ?? '') ?><?php if(!empty($ld['episode_number'])): ?> &middot; Ep.<?= (int)$ld['episode_number'] ?><?php endif; ?></div>
    </div>
    <span class="ep-draft">retomar &rarr;</span>
  </a>
  <?php endforeach; ?>
  <?php if (count($localDrafts) > 3): ?>
  <div style="padding:.5rem 0;text-align:center">
    <a href="index.php?page=local_drafts&podcast=<?= urlencode($activePodcast) ?>" style="font-size:.78rem;color:var(--text3)">Ver todos (<?= count($localDrafts) ?>)</a>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php
// Tmp files
$tmpFiles = [];
if (is_dir(UPLOAD_TMP_DIR)) {
    foreach (glob(UPLOAD_TMP_DIR . '/*') as $f) {
        if (is_file($f)) $tmpFiles[] = ['name'=>basename($f),'size'=>round(filesize($f)/1024/1024,2),'age'=>round((time()-filemtime($f))/60)];
    }
}
?>
<?php if (!empty($tmpFiles)): ?>
<div class="group-label" style="margin-top:1.5rem">Temporales en /tmp</div>
<div style="background:var(--bg2);border:1px solid var(--line);border-radius:var(--r);padding:.25rem .85rem">
  <?php foreach ($tmpFiles as $tf): ?>
  <div class="tmp-item">
    <span class="tmp-name"><?= htmlspecialchars($tf['name']) ?></span>
    <span class="tmp-meta"><?= $tf['size'] ?> MB / <?= $tf['age'] ?> min</span>
    <form method="POST" action="index.php?page=dashboard" style="margin:0;display:inline">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" name="deltmp" value="<?= htmlspecialchars($tf['name']) ?>">
      <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Eliminar?')">x</button>
    </form>
  </div>
  <?php endforeach; ?>
  <div style="padding:.6rem 0;text-align:right">
    <form method="POST" action="index.php?page=dashboard" style="margin:0;display:inline">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" name="deltmpall" value="1">
      <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Eliminar todos?')">Limpiar todo</button>
    </form>
  </div>
</div>
<?php endif; ?>
