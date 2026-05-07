<div class="page-head fade">
  <h1>Borradores</h1>
  <p><?= count($drafts) ?> sin publicar en <?= htmlspecialchars($activePodcast) ?></p>
</div>

<?php if (!empty($drafts)): ?>
<div class="ep-list fade">
  <?php foreach ($drafts as $i => $ep): ?>
  <div class="ep-item" style="animation-delay:<?= $i * 0.025 ?>s">
    <span class="ep-num"><?= htmlspecialchars($ep['number'] ?? '-') ?></span>
    <div class="ep-info">
      <div class="ep-title"><?= htmlspecialchars($ep['title'] ?? 'Sin titulo') ?></div>
      <div class="ep-meta">
        Creado <?= !empty($ep['created_at']) && strtotime($ep['created_at']) ? date('d/m/Y H:i', strtotime($ep['created_at'])) : '-' ?>
      </div>
    </div>
    <span class="ep-draft">borrador</span>
    <?php if (!empty($ep['id'])): ?>
    <form method="POST" action="index.php?page=publish_draft&podcast=<?= urlencode($activePodcast) ?>" style="margin:0">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" name="episode_id" value="<?= (int)$ep['id'] ?>">
      <button type="submit" class="btn btn-ghost btn-sm" onclick="return confirm('Publicar este borrador ahora?')">Publicar</button>
    </form>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>

<div style="margin-top:1rem;padding:.75rem 1rem;background:var(--bg2);border:1px solid var(--line);border-radius:var(--r);font-size:.8rem;color:var(--text3)">
  La API de Castopod no permite eliminar episodios remotamente. Para borrar un borrador ve al panel admin de Castopod directamente.
</div>

<?php else: ?>
<div class="empty fade">
  <div class="empty-icon">&#128196;</div>
  <p>No hay borradores pendientes.</p>
</div>
<?php endif; ?>
