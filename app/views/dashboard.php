<?php
require __DIR__ . '/layout/header.php';
?>

<?php if (!empty($receiptOrder)): ?>
<div class="ws-receipt-banner">
  <div>
    <strong>Pesanan <?= e($receiptOrder['order_code']) ?> lunas!</strong>
    <span>Total <?= formatRp($receiptOrder['total_amount']) ?> (<?= e($receiptOrder['payment_method']) ?>)</span>
    <?php if ((float)$receiptOrder['change_amount'] > 0): ?>
      <span>· Kembalian <?= formatRp($receiptOrder['change_amount']) ?></span>
    <?php endif; ?>
  </div>
  <div style="display:flex;gap:8px;">
    <a href="<?= BASE_URL ?>/?page=receipt&id=<?= (int)$receiptOrder['id'] ?>" class="ws-btn ws-btn-primary" style="padding:7px 13px;font-size:12.5px;">Lihat &amp; Cetak Struk</a>
    <a href="<?= BASE_URL ?>/" class="ws-btn ws-btn-secondary" style="padding:7px 12px;font-size:12.5px;">Tutup</a>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($openBills)): ?>
<div class="ws-open-bills-bar">
  <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:700;color:#92400e;">
    <span class="ws-pill ws-pill-warning"><?= count($openBills) ?> Meja Belum Bayar</span>
  </div>
  <div class="ws-open-bills-list">
    <?php foreach ($openBills as $ob): ?>
      <button
        type="button"
        class="ws-open-bill-chip"
        data-bs-toggle="modal"
        data-bs-target="#payBillModal"
        data-order-id="<?= (int)$ob['id'] ?>"
        data-order-code="<?= e($ob['order_code']) ?>"
        data-table="<?= e($ob['table_no']) ?>"
        data-customer="<?= e($ob['customer_name']) ?>"
        data-total="<?= (float)$ob['total_amount'] ?>"
        onclick="openPayBillModal(this)"
      >
        <strong><?= e($ob['table_no']) ?></strong> · <?= formatRp($ob['total_amount']) ?> <span>(Bayar)</span>
      </button>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<div class="ws-dashboard-grid" id="wsDashboardRoot">
  <!-- CENTER POS MENU CATALOG -->
  <div>
    <div class="ws-section-head">
      <h2 class="ws-section-title">Menu WARKOP MK</h2>
      <div class="ws-kpi-inline">
        <span class="ws-kpi-chip">Menu: <strong><?= (int)$stats['products'] ?></strong></span>
        <span class="ws-kpi-chip">Order Hari Ini: <strong><?= (int)($orderStats['orders_today'] ?? 0) ?></strong></span>
        <span class="ws-kpi-chip">Omzet Hari Ini: <strong style="color:#2563eb;"><?= formatRp($orderStats['revenue_today'] ?? 0) ?></strong></span>
        <span class="ws-kpi-chip">Total Omzet: <strong><?= formatRp($orderStats['revenue_total'] ?? 0) ?></strong></span>
      </div>
    </div>

    <!-- Explore Menu Categories -->
    <div class="ws-cat-grid" id="wsCategoryChips">
      <button type="button" class="ws-cat-chip is-active" data-cat="all">
        <span class="ws-cat-icon"><?= categoryIconSvg('all') ?></span>
        <span class="ws-cat-label">Semua Menu</span>
      </button>

      <?php foreach ($categories as $cat): ?>
      <button type="button" class="ws-cat-chip" data-cat="cat-<?= (int)$cat['id'] ?>">
        <span class="ws-cat-icon"><?= categoryIconSvg($cat['name']) ?></span>
        <span class="ws-cat-label"><?= e($cat['name']) ?></span>
      </button>
      <?php endforeach; ?>
    </div>

    <!-- Underline Tabs -->
    <div class="ws-tabs" id="wsDashboardTabs">
      <button type="button" class="ws-tab-btn is-active" data-tab="all">Semua Menu</button>
      <button type="button" class="ws-tab-btn" data-tab="popular">Terlaris</button>
      <button type="button" class="ws-tab-btn" data-tab="low">Porsi Menipis (<?= count($lowStock) ?>)</button>
    </div>

    <!-- 3-Column Menu Cards -->
    <div class="ws-product-grid" id="wsProductGrid">
      <?php foreach ($activeProducts as $p):
        $pid = (int)$p['id'];
        $stockQty = (float)$p['stock'];
        $isOut = $stockQty <= 0;
        $isLow = $stockQty <= (float)$p['min_stock'];
        $isPopular = in_array($pid, $popularIds ?? [], true);
        $sellPrice = (float)$p['sell_price'];
        $svg = productIllustrationSvg($p['name'], $p['category_name'] ?? '');
      ?>
      <article
        class="ws-product-card <?= $isOut ? 'is-out' : '' ?>"
        data-id="<?= $pid ?>"
        data-name="<?= e($p['name']) ?>"
        data-sku="<?= e($p['sku']) ?>"
        data-cat="cat-<?= (int)($p['category_id'] ?? 0) ?>"
        data-low="<?= $isLow ? '1' : '0' ?>"
        data-popular="<?= $isPopular ? '1' : '0' ?>"
        data-price="<?= e((string)$sellPrice) ?>"
        data-stock="<?= e((string)$stockQty) ?>"
        data-unit="<?= e($p['unit']) ?>"
      >
        <div class="ws-product-media" data-action="add-card">
          <button type="button" class="ws-card-check" data-action="toggle-card" aria-label="Pilih <?= e($p['name']) ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          </button>
          <span class="ws-card-sku"><?= e($p['sku']) ?></span>
          <?= $svg ?>
        </div>

        <h3 class="ws-product-title" title="<?= e($p['name']) ?>"><?= e($p['name']) ?></h3>

        <div class="ws-product-meta">
          <div class="ws-price-group">
            <span class="ws-price-main"><?= formatRp($sellPrice) ?></span>
            <span class="ws-price-sub">/ <?= e($p['unit']) ?></span>
          </div>
          <span class="ws-stock-star <?= $isLow ? 'is-low' : '' ?>" title="Sisa porsi tersedia">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="<?= $isLow ? '#2563eb' : '#F59E0B' ?>"><path d="M12 2l2.9 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l7.1-1.01L12 2z"/></svg>
            <?= $isOut ? 'Habis' : formatQty($stockQty) . ' ' . e($p['unit']) ?>
          </span>
        </div>

        <div class="ws-product-actions">
          <?php if (user()['role'] === 'admin'): ?>
            <a href="<?= BASE_URL ?>/?page=products&edit=<?= $pid ?>" class="ws-card-btn ws-card-btn-ghost">Edit Menu</a>
          <?php else: ?>
            <a href="<?= BASE_URL ?>/?page=stock-movement&product_id=<?= $pid ?>" class="ws-card-btn ws-card-btn-ghost">Restock</a>
          <?php endif; ?>
          <button type="button" class="ws-card-btn ws-card-btn-accent" data-action="add-card" <?= $isOut ? 'disabled' : '' ?>>
            <?= $isOut ? 'Habis' : '+ Pesan' ?>
          </button>
        </div>

        <template class="ws-card-svg-tpl"><?= $svg ?></template>
      </article>
      <?php endforeach; ?>

      <?php if (!$activeProducts): ?>
      <div class="ws-card" style="grid-column:1 / -1;padding:34px;text-align:center;color:#6b7280;">
        Menu warkop yang Anda cari belum tersedia.
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- STICKY RIGHT PANEL (STRUK PESANAN KASIR / INVOICE) -->
  <aside class="ws-invoice-panel" id="wsInvoicePanel">
    <div class="ws-sheet-handle"></div>
    <div class="ws-invoice-head">
      <div style="display:flex;align-items:center;gap:8px;">
        <button type="button" class="ws-sheet-close-btn" id="wsCloseInvoiceSheet" aria-label="Tutup Struk">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
        <h3 style="margin:0;">Struk Pesanan</h3>
      </div>
      <button type="button" class="ws-clear-btn" id="wsClearSelection">Kosongkan</button>
    </div>

    <form method="post" action="<?= BASE_URL ?>/?page=checkout" id="wsPosCheckoutForm">
      <input type="hidden" name="_csrf" value="<?= e(csrfToken()) ?>">
      <input type="hidden" name="order_type" id="wsOrderType" value="Dine In">
      <input type="hidden" name="payment_method" id="wsPaymentMethod" value="Tunai">
      <input type="hidden" name="order_status" id="wsOrderStatus" value="PAID">

      <!-- Dine In / Take Away + Meja / Pelanggan -->
      <div class="ws-pos-meta-row">
        <div class="ws-order-type-switch" id="wsOrderTypeSwitch">
          <button type="button" class="ws-type-pill is-active" data-otype="Dine In">Dine In</button>
          <button type="button" class="ws-type-pill" data-otype="Take Away">Take Away</button>
        </div>
        <div class="ws-pos-inputs">
          <input type="text" name="table_no" id="wsTableInput" class="ws-mini-input" value="Meja 1" list="wsTableSuggestions" placeholder="Meja">
          <datalist id="wsTableSuggestions">
            <option value="Meja 1"><option value="Meja 2"><option value="Meja 3"><option value="Meja 4">
            <option value="Meja 5"><option value="Meja 6"><option value="Lesehan"><option value="Bungkus">
          </datalist>
          <input type="text" name="customer_name" class="ws-mini-input" placeholder="Nama pembeli (opsional)">
        </div>
      </div>

      <div class="ws-invoice-list" id="wsInvoiceList"></div>

      <div class="ws-summary-box">
        <h4 class="ws-summary-title">Ringkasan Pembayaran</h4>
        <div class="ws-summary-row">
          <span>Sub Total (<strong id="wsSumQty">0</strong> item)</span>
          <strong id="wsSumSubtotal">Rp 0</strong>
        </div>
        <div class="ws-summary-row">
          <span>Diskon (Rp)</span>
          <input type="number" name="discount" id="wsDiscountInput" class="ws-inline-num" min="0" step="500" value="0">
        </div>
        <div class="ws-summary-divider"></div>
        <div class="ws-summary-total">
          <span>Total Bayar</span>
          <span id="wsSumTotal">Rp 0</span>
        </div>
      </div>

      <h4 class="ws-method-title">Metode Pembayaran</h4>
      <div class="ws-method-grid" id="wsPaymentGrid">
        <button type="button" class="ws-method-card is-active" data-method="Tunai">
          <span class="ws-method-dot">Rp</span>
          <span>
            <span class="ws-method-label" style="display:block;">Tunai</span>
            <span class="ws-method-sub">Uang cash</span>
          </span>
        </button>

        <button type="button" class="ws-method-card" data-method="QRIS">
          <span class="ws-method-dot">QR</span>
          <span>
            <span class="ws-method-label" style="display:block;">QRIS</span>
            <span class="ws-method-sub">Scan cepat</span>
          </span>
        </button>

        <button type="button" class="ws-method-card" data-method="Transfer">
          <span class="ws-method-dot">TF</span>
          <span>
            <span class="ws-method-label" style="display:block;">Transfer</span>
            <span class="ws-method-sub">Bank / ATM</span>
          </span>
        </button>

        <button type="button" class="ws-method-card" data-method="E-Wallet">
          <span class="ws-method-dot">EW</span>
          <span>
            <span class="ws-method-label" style="display:block;">E-Wallet</span>
            <span class="ws-method-sub">GoPay / OVO</span>
          </span>
        </button>
      </div>

      <div id="wsCashBox" class="ws-cash-box">
        <div class="ws-summary-row" style="margin-bottom:6px;">
          <label for="wsCashPaidInput" style="font-weight:600;color:#1f2937;">Uang Diterima</label>
          <input type="number" name="cash_paid" id="wsCashPaidInput" class="ws-inline-num" min="0" step="1000" placeholder="Uang pas">
        </div>
        <div class="ws-quick-cash" id="wsQuickCashBtns">
          <button type="button" class="ws-qcash-btn" data-cash="exact">Pas</button>
          <button type="button" class="ws-qcash-btn" data-cash="10000">10rb</button>
          <button type="button" class="ws-qcash-btn" data-cash="20000">20rb</button>
          <button type="button" class="ws-qcash-btn" data-cash="50000">50rb</button>
          <button type="button" class="ws-qcash-btn" data-cash="100000">100rb</button>
        </div>
        <div class="ws-summary-row" style="margin-bottom:0;margin-top:7px;">
          <span>Kembalian</span>
          <strong id="wsCashChange" style="color:#059669;">Rp 0</strong>
        </div>
      </div>

      <input type="text" name="note" class="ws-note-input" placeholder="Catatan dapur: pedas, es sedikit, dll...">

      <div class="ws-pos-submit-grid">
        <button type="submit" class="ws-btn ws-btn-outline-blue" id="wsHoldBillBtn" onclick="document.getElementById('wsOrderStatus').value='OPEN'" style="height:42px;">
          Simpan Bill
        </button>
        <button type="submit" class="ws-cta-full" id="wsCheckoutSubmit" onclick="document.getElementById('wsOrderStatus').value='PAID'">
          Bayar Lunas
        </button>
      </div>
    </form>
  </aside>
  <div class="ws-invoice-backdrop" id="wsInvoiceBackdrop"></div>
</div>

<!-- Sticky Mobile Cart Bar -->
<div class="ws-mobile-cart-bar" id="wsMobileCartBar">
  <div style="display:flex;align-items:center;gap:10px;">
    <div class="ws-mob-cart-badge">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
      <span id="wsMobCartBadge">0</span>
    </div>
    <div>
      <div style="font-size:11px;color:#d1d5db;line-height:1.2;" id="wsMobCartQty">0 menu</div>
      <div style="font-size:15px;font-weight:800;color:#ffffff;" id="wsMobCartTotal">Rp 0</div>
    </div>
  </div>
  <button type="button" class="ws-btn ws-btn-primary" id="wsMobGoInvoice" style="padding:8px 16px;font-size:13px;font-weight:700;">
    Bayar / Struk &rarr;
  </button>
</div>

<!-- Modal Lunasi Open Bill -->
<div class="modal fade" id="payBillModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="<?= BASE_URL ?>/?page=pay-bill">
        <div class="modal-header">
          <h5 class="modal-title">Lunasi Tagihan Meja</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" style="padding:20px;">
          <input type="hidden" name="_csrf" value="<?= e(csrfToken()) ?>">
          <input type="hidden" name="order_id" id="pbOrderId">
          <div class="ws-summary-box" style="margin-top:0;">
            <div class="ws-summary-row"><span>No. Nota</span><strong id="pbOrderCode">-</strong></div>
            <div class="ws-summary-row"><span>Meja / Pembeli</span><strong id="pbCustomer">-</strong></div>
            <div class="ws-summary-divider"></div>
            <div class="ws-summary-total"><span>Total Tagihan</span><span id="pbTotal" style="color:#2563eb;">Rp 0</span></div>
          </div>
          <div style="margin-bottom:12px;">
            <label class="ws-form-label" for="pbMethod">Metode Pembayaran</label>
            <select name="payment_method" id="pbMethod" class="ws-select">
              <option value="Tunai">Tunai</option>
              <option value="QRIS">QRIS</option>
              <option value="Transfer">Transfer</option>
              <option value="E-Wallet">E-Wallet</option>
            </select>
          </div>
          <div>
            <label class="ws-form-label" for="pbCashPaid">Uang Tunai Diterima (opsional, kosong = uang pas)</label>
            <input type="number" name="cash_paid" id="pbCashPaid" class="ws-input" min="0" step="1000" placeholder="Contoh: 50000">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="ws-btn ws-btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="ws-btn ws-btn-primary">Bayar &amp; Cetak Struk</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openPayBillModal(btn) {
  document.getElementById('pbOrderId').value = btn.dataset.orderId;
  document.getElementById('pbOrderCode').textContent = btn.dataset.orderCode;
  document.getElementById('pbCustomer').textContent = btn.dataset.table + ' · ' + btn.dataset.customer;
  const total = parseFloat(btn.dataset.total || '0');
  document.getElementById('pbTotal').textContent = 'Rp ' + Math.round(total).toLocaleString('id-ID');
  document.getElementById('pbCashPaid').value = '';
}
</script>

<?php require __DIR__ . '/layout/footer.php'; ?>
