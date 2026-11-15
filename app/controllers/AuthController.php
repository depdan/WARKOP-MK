<?php
declare(strict_types=1);

class AuthController {
    public static function login(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $stmt = db()->prepare("SELECT * FROM users WHERE email=? AND is_active=1 LIMIT 1");
            $stmt->execute([$email]);
            $u = $stmt->fetch();

            if ($u && password_verify($password, $u['password'])) {
                loginUser($u);
                flash('success', 'Login berhasil.');
                redirect(BASE_URL . '/');
            }

            flash('danger', 'Email atau password salah.');
        }

        require __DIR__ . '/../views/auth/login.php';
    }

    public static function logout(): void {
        logoutUser();
        redirect(BASE_URL . '/?page=login');
    }
}
