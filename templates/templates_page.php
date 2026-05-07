<div class="page-head fade">
  <h1>Plantillas</h1>
  <p>Plantillas de descripcion reutilizables</p>
</div>

<?php foreach ($templates as $i => $tpl): ?>
<div class="card fade" style="animation-delay:<?= $i*0.05 ?>s;margin-bottom:1rem">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem">
    <div style="font-weight:500;font-size:.95rem"><?= htmlspecialchars($tpl['name']) ?></div>
    <div style="display:flex;gap:.4rem">
      <button class="btn btn-ghost btn-sm" onclick="editTemplate(<?= htmlspecialchars(json_encode($tpl['id'])) ?>,<?= htmlspecialchars(json_encode($tpl['name'])) ?>,this)">Editar</button>
      <?php if($tpl['id'] !== 'default'): ?>
      <form method="POST" action="index.php?page=templates" style="margin:0">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="tpl_id" value="<?= htmlspecialchars($tpl['id']) ?>">
        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Eliminar plantilla?')">x</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
  <pre class="tpl-preview" id="preview_<?= htmlspecialchars($tpl['id']) ?>"><?= htmlspecialchars($tpl['description']) ?></pre>

  <!-- Inline edit form (hidden by default) -->
  <form method="POST" action="index.php?page=templates" class="tpl-edit-form" id="editform_<?= htmlspecialchars($tpl['id']) ?>" style="display:none;margin-top:.75rem">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="tpl_id" value="<?= htmlspecialchars($tpl['id']) ?>">
    <div class="field">
      <label>Nombre</label>
      <input type="text" name="tpl_name" value="<?= htmlspecialchars($tpl['name']) ?>" required>
    </div>
    <div class="field">
      <label>Contenido</label>
      <textarea name="tpl_body" rows="8" required><?= htmlspecialchars($tpl['description']) ?></textarea>
    </div>
    <div style="display:flex;gap:.5rem">
      <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
      <button type="button" class="btn btn-ghost btn-sm" onclick="cancelEdit('<?= htmlspecialchars($tpl['id']) ?>')">Cancelar</button>
    </div>
  </form>
</div>
<?php endforeach; ?>

<!-- Add new template -->
<div class="group-label" style="margin-top:1.5rem">Nueva plantilla</div>
<div style="background:var(--bg2);border:1px solid var(--line);border-radius:var(--r);padding:1.1rem" class="fade">
  <form method="POST" action="index.php?page=templates">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <input type="hidden" name="action" value="add">
    <div class="field">
      <label>Nombre de la plantilla</label>
      <input type="text" name="tpl_name" placeholder="Ej: Entrevista, Solo, Newsletter..." required>
    </div>
    <div class="field">
      <label>Contenido <span style="color:var(--text3);font-size:.72rem">(puedes usar Markdown basico)</span></label>
      <textarea name="tpl_body" rows="8" placeholder="En este episodio:&#10;&#10;- &#10;- &#10;&#10;---&#10;&#10;Sigueme en:&#10;- Web: " required></textarea>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Anadir plantilla</button>
  </form>
</div>

<script>
function editTemplate(id, name, btn) {
  document.getElementById('preview_' + id).style.display = 'none';
  document.getElementById('editform_' + id).style.display = 'block';
  btn.style.display = 'none';
}
function cancelEdit(id) {
  document.getElementById('preview_' + id).style.display = 'block';
  document.getElementById('editform_' + id).style.display = 'none';
  document.querySelector('#editform_' + id).previousElementSibling.querySelector('button').style.display = '';
}
</script>
