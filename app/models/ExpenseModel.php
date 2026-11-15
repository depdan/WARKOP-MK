<?php
declare(strict_types=1);

class ExpenseModel
{
    public static function all(?string $start = null, ?string $end = null): array
    {
        $sql = "SELECT e.*, u.name AS user_name
                FROM expenses e
                JOIN users u ON u.id = e.user_id
                WHERE 1=1";
        $params = [];
        if ($start) {
            $sql .= " AND e.expense_date >= ?";
            $params[] = $start;
        }
        if ($end) {
            $sql .= " AND e.expense_date <= ?";
            $params[] = $end;
        }
        $sql .= " ORDER BY e.expense_date DESC, e.id DESC LIMIT 300";

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create(array $data, int $userId): void
    {
        $date = trim((string)($data['expense_date'] ?? ''));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }
        $category = trim((string)($data['category'] ?? 'Operasional'));
        $desc = trim((string)($data['description'] ?? ''));
        $amount = (float)($data['amount'] ?? 0);

        if ($desc === '' || $amount <= 0) {
            throw new InvalidArgumentException('Keterangan dan nominal pengeluaran (> 0) wajib diisi.');
        }

        $pdo = db();
        $stmt = $pdo->prepare(
            "INSERT INTO expenses (expense_date, category, description, amount, user_id)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $date,
            mb_substr($category !== '' ? $category : 'Operasional', 0, 80),
            mb_substr($desc, 0, 255),
            round($amount, 2),
            $userId,
        ]);

        $id = (int)$pdo->lastInsertId();
        $audit = $pdo->prepare(
            "INSERT INTO audit_logs (user_id, action, table_name, record_id, old_data, new_data, ip_address)
             VALUES (?, 'EXPENSE_CREATE', 'expenses', ?, NULL, ?, ?)"
        );
        $audit->execute([
            $userId,
            $id,
            json_encode(['date' => $date, 'category' => $category, 'description' => $desc, 'amount' => $amount], JSON_UNESCAPED_UNICODE),
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    public static function delete(int $id, int $userId): void
    {
        $pdo = db();
        $oldStmt = $pdo->prepare("SELECT * FROM expenses WHERE id = ?");
        $oldStmt->execute([$id]);
        $old = $oldStmt->fetch();
        if (!$old) {
            throw new RuntimeException('Data pengeluaran tidak ditemukan.');
        }

        $del = $pdo->prepare("DELETE FROM expenses WHERE id = ?");
        $del->execute([$id]);

        $audit = $pdo->prepare(
            "INSERT INTO audit_logs (user_id, action, table_name, record_id, old_data, new_data, ip_address)
             VALUES (?, 'EXPENSE_DELETE', 'expenses', ?, ?, NULL, ?)"
        );
        $audit->execute([
            $userId,
            $id,
            json_encode($old, JSON_UNESCAPED_UNICODE),
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
}

