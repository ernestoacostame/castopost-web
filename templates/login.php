<div class="login-wrap">
  <div class="login-mark fade">Casto<span>POST</span></div>
  <p class="login-sub fade">Panel privado de publicacion</p>
  <div class="login-box fade">
    <form method="POST" action="index.php?page=login">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <div class="field">
        <label for="password">Contrasena</label>
        <input type="password" id="password" name="password" autofocus placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-primary btn-full" style="margin-top:.85rem">Entrar</button>
    </form>
  </div>
</div>
