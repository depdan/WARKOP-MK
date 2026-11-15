<?php require __DIR__ . '/layout/header.php'; ?>

<div class="ws-page-header">
  <div>
    <h1 class="ws-page-title">Daftar Menu WARKOP MK</h1>
    <p class="ws-page-sub">Kelola menu minuman, indomie, roti bakar, harga jual, dan porsi harian yang siap disajikan.</p>
  </div>
  <?php if (user()['role'] === 'admin'): ?>
  <button
    type="button"
    class="ws-btn ws-btn-primary"
    data-bs-toggle="modal"
    data-bs-target="#productModal"
    onclick="resetProductForm()"
  >
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
    Tambah Menu
  </button>
  <?php endif; ?>
</div>

<!-- Category / Status Filter Chips -->
<div class="ws-cat-grid" id="wsProductsFilterChips" style="margin-bottom:16px;">
  <button type="button" class="ws-cat-chip is-active" data-filter="all">
    <span class="ws-cat-icon"><?= categoryIconSvg('all') ?></span>
    <span class="ws-cat-label">Semua (<?= count($products) ?>)</span>
  </button>
  <?php foreach ($categories as $category): ?>
  <button type="button" class="ws-cat-chip" data-filter="cat-<?= (int)$category['id'] ?>">
    <span class="ws-cat-icon"><?= categoryIconSvg($category['name']) ?></span>
    <span class="ws-cat-label"><?= e($category['name']) ?></span>
  </button>
  <?php endforeach; ?>
  <button type="button" class="ws-cat-chip" data-filter="low">
    <span class="ws-cat-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
    </span>
    <span class="ws-cat-label">Porsi Menipis</span>
  </button>
</div>

<div class="ws-table-card">
  <div class="table-responsive">
    <table class="ws-table" id="wsProductsTable">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Menu</th>
          <th>Kategori</th>
          <th>Dapur / Asal</th>
          <th>Modal</th>
          <th>Harga Jual</th>
          <th>Sisa Porsi</th>
          <th>Status</th>
          <th style="text-align:right;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p):
          $isLow = (float)$p['stock'] <= (float)$p['min_stock'];
          $isActive = (int)$p['is_active'] === 1;
        ?>
        <tr
          data-cat="cat-<?= (int)($p['category_id'] ?? 0) ?>"
          data-low="<?= $isLow ? '1' : '0' ?>"
          data-search="<?= e(mb_strtolower($p['sku'] . ' ' . $p['name'] . ' ' . ($p['category_name'] ?? ''))) ?>"
        >
          <td><span class="ws-sku-code"><?= e($p['sku']) ?></span></td>
          <td>
            <div class="ws-prod-cell">
              <span class="ws-prod-mini"><?= productIllustrationSvg($p['name'], $p['category_name'] ?? '') ?></span>
              <div>
                <div style="font-weight:700;color:#1f2937;"><?= e($p['name']) ?></div>
                <div style="font-size:11.5px;color:#6b7280;">Satuan: <?= e($p['unit']) ?></div>
              </div>
            </div>
          </td>
          <td><?= e($p['category_name'] ?? '-') ?></td>
          <td><?= e($p['supplier_name'] ?? '-') ?></td>
          <td class="ws-nowrap" style="font-weight:600;"><?= formatRp($p['cost_price']) ?></td>
          <td class="ws-nowrap" style="font-weight:700;color:#2563eb;"><?= formatRp($p['sell_price']) ?></td>
          <td>
            <span class="ws-pill <?= $isLow ? 'ws-pill-warning' : 'ws-pill-success' ?>">
              <?= formatQty($p['stock']) ?> <?= e($p['unit']) ?>
            </span>
            <div style="font-size:11px;color:#9ca3af;margin-top:3px;">Min: <?= formatQty($p['min_stock']) ?></div>
          </td>
          <td>
            <?php if ($isActive): ?>
              <span class="ws-pill ws-pill-success">Tersedia</span>
            <?php else: ?>
              <span class="ws-pill ws-pill-muted">Nonaktif</span>
            <?php endif; ?>
          </td>
          <td style="text-align:right;">
            <?php if (user()['role'] === 'admin'): ?>
              <div style="display:inline-flex;gap:6px;align-items:center;">
                <?php if ($isActive): ?>
                  <a href="<?= BASE_URL ?>/?page=products&edit=<?= (int)$p['id'] ?>" class="ws-btn ws-btn-outline-blue" style="padding:6px 11px;font-size:12px;">Edit</a>
                  <form method="post" onsubmit="return confirm('Nonaktifkan menu ini dari daftar jual?')" style="margin:0;">
                    <input type="hidden" name="_csrf" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <button class="ws-btn ws-btn-danger-soft" style="padding:6px 11px;font-size:12px;">Hapus</button>
                  </form>
                <?php else: ?>
                  <form method="post" style="margin:0;">
                    <input type="hidden" name="_csrf" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="action" value="restore">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <button class="ws-btn ws-btn-secondary" style="padding:6px 11px;font-size:12px;">Aktifkan</button>
                  </form>
                <?php endif; ?>
              </div>
            <?php else: ?>
              <a href="<?= BASE_URL ?>/?page=stock-movement&product_id=<?= (int)$p['id'] ?>" class="ws-btn ws-btn-outline-blue" style="padding:6px 11px;font-size:12px;">Restock</a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$products): ?>
        <tr>
          <td colspan="9" style="text-align:center;color:#6b7280;padding:32px;">Menu tidak ditemukan.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal CRUD Menu -->
<div class="modal fade" id="productModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title"><?= $editProduct ? 'Edit Menu Warkop' : 'Tambah Menu Warkop Baru' ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body" style="padding:20px;">
          <input type="hidden" name="_csrf" value="<?= e(csrfToken()) ?>">
          <input type="hidden" name="action" value="<?= $editProduct ? 'update' : 'create' ?>">
          <?php if ($editProduct): ?>
            <input type="hidden" name="id" value="<?= (int)$editProduct['id'] ?>">
          <?php endif; ?>

          <div class="row g-3">
            <div class="col-md-4">
              <label class="ws-form-label">Kode Menu *</label>
              <input type="text" name="sku" class="ws-input" required value="<?= e($editProduct['sku'] ?? '') ?>" placeholder="M-059">
            </div>

            <div class="col-md-8">
              <label class="ws-form-label">Nama Menu *</label>
              <input type="text" name="name" class="ws-input" required value="<?= e($editProduct['name'] ?? '') ?>" placeholder="Contoh: Kopi Tubruk Susu">
            </div>

            <div class="col-md-6">
              <label class="ws-form-label">Kategori Menu</label>
              <select name="category_id" class="ws-select">
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($categories as $category): ?>
                  <option value="<?= (int)$category['id'] ?>" <?= ((int)($editProduct['category_id'] ?? 0) === (int)$category['id']) ? 'selected' : '' ?>>
                    <?= e($category['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="ws-form-label">Sumber / Dapur</label>
              <select name="supplier_id" class="ws-select">
                <option value="">-- Pilih Sumber --</option>
                <?php foreach ($suppliers as $supplier): ?>
                  <option value="<?= (int)$supplier['id'] ?>" <?= ((int)($editProduct['supplier_id'] ?? 0) === (int)$supplier['id']) ? 'selected' : '' ?>>
                    <?= e($supplier['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-4">
              <label class="ws-form-label">Satuan *</label>
              <input type="text" name="unit" class="ws-input" required value="<?= e($editProduct['unit'] ?? 'porsi') ?>" placeholder="gelas / porsi">
            </div>

            <div class="col-md-4">
              <label class="ws-form-label">Modal / HPP</label>
              <input type="number" name="cost_price" class="ws-input" min="0" step="100" value="<?= e((string)($editProduct['cost_price'] ?? 0)) ?>">
            </div>

            <div class="col-md-4">
              <label class="ws-form-label">Harga Jual Menu</label>
              <input type="number" name="sell_price" class="ws-input" min="0" step="100" value="<?= e((string)($editProduct['sell_price'] ?? 0)) ?>">
            </div>

            <?php if (!$editProduct): ?>
            <div class="col-md-6">
              <label class="ws-form-label">Stok Porsi Awal</label>
              <input type="number" name="stock" class="ws-input" min="0" step="1" value="20">
              <div style="font-size:11.5px;color:#6b7280;margin-top:4px;">Setelah menu dibuat, penambahan porsi dilakukan lewat Restock Dapur.</div>
            </div>
            <?php endif; ?>

            <div class="col-md-<?= $editProduct ? '12' : '6' ?>">
              <label class="ws-form-label">Minimum Porsi</label>
              <input type="number" name="min_stock" class="ws-input" min="0" step="1" value="<?= e((string)($editProduct['min_stock'] ?? 5)) ?>">
            </div>

            <?php if ($editProduct): ?>
            <div class="col-md-6">
              <label class="ws-form-label">Status Jual</label>
              <select name="is_active" class="ws-select">
                <option value="1" <?= (int)$editProduct['is_active'] === 1 ? 'selected' : '' ?>>Tersedia</option>
                <option value="0" <?= (int)$editProduct['is_active'] === 0 ? 'selected' : '' ?>>Nonaktif</option>
              </select>
            </div>
            <div class="col-md-6">
              <div class="ws-summary-box" style="margin:0;">
                <div style="font-size:12px;color:#6b7280;">Sisa porsi saat ini</div>
                <div style="font-size:18px;font-weight:800;color:#2563eb;"><?= formatQty($editProduct['stock']) ?> <?= e($editProduct['unit']) ?></div>
                <div style="font-size:11.5px;color:#6b7280;margin-top:3px;">Gunakan menu <strong>Restock Dapur</strong> untuk tambah porsi.</div>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="ws-btn ws-btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="ws-btn ws-btn-primary"><?= $editProduct ? 'Simpan Menu' : 'Tambah Menu' ?></button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function resetProductForm() {
  const url = new URL(window.location.href);
  if (url.searchParams.has('edit')) {
    url.searchParams.delete('edit');
    window.history.replaceState({}, '', url);
    window.location.reload();
  }
}
</script>

<?php if ($editProduct): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const modal = new bootstrap.Modal(document.getElementById('productModal'));
  modal.show();
});
</script>
<?php endif; ?>

<?php require __DIR__ . '/layout/footer.php'; ?>
