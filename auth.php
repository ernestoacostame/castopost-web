<?php
class Auth {
    private const SESSION_KEY = 'castopod_auth';

    public function login(string $password): bool {
        // Comparación timing-safe contra la contraseña configurada
        if (!hash_equals(APP_PASSWORD, $password)) {
            return false;
        }
        // Regenerar ID de sesión para evitar session fixation
        session_regenerate_id(true);
        $_SESSION[self::SESSION_KEY] = true;
        $_SESSION['login_time'] = time();
        return true;
    }

    public function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public function isLoggedIn(): bool {
        if (empty($_SESSION[self::SESSION_KEY])) return false;
        // Session expires after 8 hours
        if (time() - ($_SESSION['login_time'] ?? 0) > 28800) {
            $this->logout();
            return false;
        }
        return true;
    }
}
