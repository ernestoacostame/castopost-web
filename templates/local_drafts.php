<div class="page-head fade">
  <h1>Borradores locales</h1>
  <p><?= count($localDrafts) ?> guardados para <?= htmlspecialchars($activePodcast) ?></p>
</div>

<?php if (!empty($localDrafts)): ?>
<div class="ep-list fade">
  <?php foreach ($localDrafts as $i => $ld): ?>
  <div class="ep-item" style="animation-delay:<?= $i*0.025 ?>s">
    <div class="ep-info">
      <div class="ep-title"><?= htmlspecialchars($ld['title'] ?: 'Sin titulo') ?></div>
      <div class="ep-meta">
        Guardado <?= htmlspecialchars($ld['saved_at'] ?? '') ?>
        <?php if(!empty($ld['episode_number'])): ?> &middot; Ep.<?= (int)$ld['episode_number'] ?><?php endif; ?>
      </div>
    </div>
    <span class="ep-draft">local</span>
    <a href="index.php?page=publish&podcast=<?= urlencode($activePodcast) ?>&draft_id=<?= urlencode($ld['draft_id']) ?>" class="btn btn-ghost btn-sm">Editar</a>
    <form method="POST" action="index.php?page=delete_draft&podcast=<?= urlencode($activePodcast) ?>" style="margin:0;display:inline">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" name="draft_id" value="<?= htmlspecialchars($ld['draft_id']) ?>">
      <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Eliminar este borrador?')">x</button>
    </form>
  </div>
  <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty fade">
  <div class="empty-icon">&#128196;</div>
  <p>No tienes borradores locales guardados.</p>
  <a href="index.php?page=publish&podcast=<?= urlencode($activePodcast) ?>" class="btn btn-ghost btn-sm">Nuevo episodio</a>
</div>
<?php endif; ?>
