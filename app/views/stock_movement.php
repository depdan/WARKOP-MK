<?php
require __DIR__ . '/layout/header.php';
$selectedProductId = (int)($_GET['product_id'] ?? 0);
?>

<div class="ws-page-header">
  <div>
    <h1 class="ws-page-title">Restock Dapur &amp; Porsi Menu</h1>
    <p class="ws-page-sub">Tambah ketersediaan porsi menu warkop atau catat koreksi porsi dapur dengan aman.</p>
  </div>
  <a href="<?= BASE_URL ?>/?page=history" class="ws-btn ws-btn-secondary">Lihat Riwayat</a>
</div>

<div class="ws-dashboard-grid">
  <div class="ws-card" style="padding:22px;">
    <form method="post" id="wsSingleMovementForm">
      <input type="hidden" name="_csrf" value="<?= e(csrfToken()) ?>">
      <input type="hidden" name="type" id="wsSingleType" value="IN">

      <div style="margin-bottom:18px;">
        <label class="ws-form-label">Pilih Operasi Dapur</label>
        <div class="ws-method-grid" id="wsSingleTypeCards" style="margin-bottom:0;">
          <button type="button" class="ws-method-card is-active" data-type="IN" style="padding:13px 14px;">
            <span class="ws-method-dot" style="width:32px;height:32px;">+</span>
            <span>
              <span class="ws-method-label" style="display:block;font-size:13px;">Tambah Porsi</span>
              <span class="ws-method-sub" style="font-size:11.5px;">Restock dapur / siap jual</span>
            </span>
          </button>

          <button type="button" class="ws-method-card" data-type="OUT" style="padding:13px 14px;">
            <span class="ws-method-dot" style="width:32px;height:32px;">−</span>
            <span>
              <span class="ws-method-label" style="display:block;font-size:13px;">Kurangi Porsi</span>
              <span class="ws-method-sub" style="font-size:11.5px;">Rusak / konsumsi internal</span>
            </span>
          </button>
        </div>
      </div>

      <div style="margin-bottom:16px;">
        <label class="ws-form-label" for="wsProductSelect">Menu Warkop *</label>
        <select name="product_id" id="wsProductSelect" class="ws-select" required>
          <option value="">-- Pilih Menu --</option>
          <?php foreach ($products as $p):
            if ((int)$p['is_active'] !== 1) continue;
          ?>
            <option
              value="<?= (int)$p['id'] ?>"
              data-stock="<?= e((string)$p['stock']) ?>"
              data-unit="<?= e($p['unit']) ?>"
              data-min="<?= e((string)$p['min_stock']) ?>"
              data-name="<?= e($p['name']) ?>"
              <?= $selectedProductId === (int)$p['id'] ? 'selected' : '' ?>
            >
              <?= e($p['sku'] . ' — ' . $p['name'] . ' (Sisa: ' . formatQty($p['stock']) . ' ' . $p['unit'] . ')') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="margin-bottom:16px;">
        <label class="ws-form-label" for="wsSingleQty">Jumlah Porsi *</label>
        <input type="number" step="1" min="1" name="quantity" id="wsSingleQty" class="ws-input" placeholder="Contoh: 20" required>
      </div>

      <div style="margin-bottom:20px;">
        <label class="ws-form-label" for="wsSingleNote">Keterangan</label>
        <textarea name="note" id="wsSingleNote" class="ws-textarea" rows="3" placeholder="Contoh: Masak porsi sore / tambah stok mie"></textarea>
      </div>

      <button type="submit" class="ws-cta-full">Simpan Perubahan Porsi</button>
    </form>
  </div>

  <aside class="ws-invoice-panel">
    <div class="ws-invoice-head">
      <h3>Simulasi Porsi</h3>
      <span class="ws-pill ws-pill-blue">Aman &amp; Tercatat</span>
    </div>

    <div class="ws-summary-box" style="margin-top:0;">
      <div class="ws-summary-row">
        <span>Menu</span>
        <strong id="wsSimName">-</strong>
      </div>
      <div class="ws-summary-row">
        <span>Sisa Porsi</span>
        <strong id="wsSimBefore">-</strong>
      </div>
      <div class="ws-summary-row">
        <span>Perubahan</span>
        <strong id="wsSimDelta" style="color:#2563eb;">-</strong>
      </div>
      <div class="ws-summary-divider"></div>
      <div class="ws-summary-total">
        <span>Porsi Setelah Simpan</span>
        <span id="wsSimAfter" style="color:#2563eb;">-</span>
      </div>
    </div>

    <div style="font-size:12.5px;color:#6b7280;line-height:1.55;">
      <strong style="color:#1f2937;display:block;margin-bottom:4px;">Sinkronisasi Otomatis Kasir</strong>
      Penambahan atau pengurangan porsi langsung memperbarui menu yang tampil di layar kasir utama.
    </div>
  </aside>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
