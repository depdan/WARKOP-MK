<?php
declare(strict_types=1);

function isLoggedIn(): bool {
    return isset($_SESSION['user']);
}

function user(): ?array {
    return $_SESSION['user'] ?? null;
}

function loginUser(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];
}

function logoutUser(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/?page=login');
        exit;
    }
}

function requireRole(string ...$roles): void {
    requireLogin();
    if (!in_array(user()['role'], $roles, true)) {
        http_response_code(403);
        exit('403 - Akses ditolak');
    }
}
