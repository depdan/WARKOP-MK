<?php
declare(strict_types=1);

class UserController {
    public static function index(): void {
        requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $action = $_POST['action'] ?? '';

            try {
                if ($action === 'create') {
                    UserModel::create(
                        trim($_POST['name']),
                        trim($_POST['email']),
                        $_POST['password'],
                        $_POST['role']
                    );
                    flash('success', 'User berhasil dibuat.');
                } elseif ($action === 'delete') {
                    UserModel::delete((int)$_POST['id']);
                    flash('success', 'User dinonaktifkan.');
                }
            } catch (Throwable $e) {
                flash('danger', $e->getMessage());
            }
            redirect(BASE_URL . '/?page=users');
        }

        $users = UserModel::all();
        require __DIR__ . '/../views/users.php';
    }
}
