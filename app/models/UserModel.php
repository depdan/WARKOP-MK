<?php
declare(strict_types=1);

class UserModel {
    public static function all(): array {
        return db()->query("SELECT id,name,email,role,is_active,created_at FROM users ORDER BY created_at DESC")->fetchAll();
    }

    public static function create(string $name, string $email, string $password, string $role): void {
        $stmt = db()->prepare("INSERT INTO users(name,email,password,role) VALUES(?,?,?,?)");
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
    }

    public static function delete(int $id): void {
        db()->prepare("UPDATE users SET is_active=0 WHERE id=?")->execute([$id]);
    }
}
