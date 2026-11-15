<?php require __DIR__ . '/layout/header.php'; ?>

<div class="ws-page-header">
  <div>
    <h1 class="ws-page-title">Manajemen User</h1>
    <p class="ws-page-sub">Kelola akun admin dan kasir yang memiliki akses ke sistem stok warkop.</p>
  </div>
</div>

<div class="ws-dashboard-grid ws-users-grid">
  <div class="ws-card" style="padding:20px;">
    <h3 style="font-size:15.5px;font-weight:700;margin:0 0 14px;">Tambah User Baru</h3>
    <form method="post">
      <input type="hidden" name="_csrf" value="<?= e(csrfToken()) ?>">
      <input type="hidden" name="action" value="create">

      <div style="margin-bottom:12px;">
        <label class="ws-form-label">Nama Lengkap</label>
        <input name="name" class="ws-input" placeholder="Contoh: Kasir Malam" required>
      </div>

      <div style="margin-bottom:12px;">
        <label class="ws-form-label">Email</label>
        <input type="email" name="email" class="ws-input" placeholder="nama@warkop.local" required>
      </div>

      <div style="margin-bottom:12px;">
        <label class="ws-form-label">Password</label>
        <input type="password" name="password" class="ws-input" minlength="6" placeholder="Minimal 6 karakter" required>
      </div>

      <div style="margin-bottom:16px;">
        <label class="ws-form-label">Role</label>
        <select name="role" class="ws-select">
          <option value="user">User / Kasir</option>
          <option value="admin">Admin</option>
        </select>
      </div>

      <button type="submit" class="ws-cta-full">Tambah User</button>
    </form>
  </div>

  <div class="ws-table-card">
    <div class="table-responsive">
      <table class="ws-table">
        <thead>
          <tr>
            <th>Pengguna</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th style="text-align:right;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                <span class="ws-avatar"><?= e(userInitials($u['name'])) ?></span>
                <strong><?= e($u['name']) ?></strong>
              </div>
            </td>
            <td style="color:#4b5563;"><?= e($u['email']) ?></td>
            <td>
              <span class="ws-pill <?= $u['role'] === 'admin' ? 'ws-pill-blue' : 'ws-pill-muted' ?>">
                <?= e(strtoupper($u['role'])) ?>
              </span>
            </td>
            <td>
              <span class="ws-pill <?= $u['is_active'] ? 'ws-pill-success' : 'ws-pill-muted' ?>">
                <?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?>
              </span>
            </td>
            <td style="text-align:right;">
              <?php if ((int)$u['id'] !== (int)user()['id'] && $u['is_active']): ?>
              <form method="post" onsubmit="return confirm('Nonaktifkan user ini?')" style="margin:0;display:inline;">
                <input type="hidden" name="_csrf" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <button class="ws-btn ws-btn-danger-soft" style="padding:6px 12px;font-size:12px;">Nonaktifkan</button>
              </form>
              <?php else: ?>
                <span style="font-size:12px;color:#9ca3af;"><?= (int)$u['id'] === (int)user()['id'] ? 'Akun Anda' : '-' ?></span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
