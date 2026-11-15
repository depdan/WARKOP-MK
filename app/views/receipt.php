<?php require __DIR__ . '/layout/header.php'; ?>

<div class="ws-page-header ws-no-print">
  <div>
    <h1 class="ws-page-title">Struk Pembayaran</h1>
    <p class="ws-page-sub">Bukti transaksi kasir WARKOP MK — siap dicetak atau disimpan.</p>
  </div>
  <div style="display:flex;gap:8px;">
    <a href="<?= BASE_URL ?>/" class="ws-btn ws-btn-secondary">Kembali ke Kasir</a>
    <a href="<?= BASE_URL ?>/?page=history" class="ws-btn ws-btn-outline-blue">Riwayat Pesanan</a>
    <button type="button" onclick="window.print()" class="ws-btn ws-btn-primary">Cetak Struk</button>
  </div>
</div>

<div class="ws-receipt-paper">
  <div style="text-align:center;">
    <div style="display:inline-flex;align-items:center;gap:8px;margin-bottom:4px;">
      <svg width="26" height="26" viewBox="0 0 32 32" fill="none" aria-hidden="true">
        <circle cx="16" cy="16" r="14" fill="#2563eb"/>
        <path d="M16 2A14 14 0 0 1 30 16H16V2Z" fill="#93c5fd"/>
        <circle cx="16" cy="16" r="5" fill="#FFFFFF"/>
      </svg>
      <span style="font-size:19px;font-weight:800;letter-spacing:-0.03em;color:#2563eb;">WARKOP <span style="color:#1f2937;">MK</span></span>
    </div>
    <div style="font-size:12px;color:#6b7280;">Kopi Tubruk, Indomie &amp; Camilan Hangat</div>
    <div style="font-size:11.5px;color:#9ca3af;margin-top:2px;">Buka Setiap Hari · 07.00 - 23.00 WIB</div>
    <?php $st = $order['status'] ?? 'PAID'; ?>
    <div style="margin-top:8px;">
      <?php if ($st === 'OPEN'): ?>
        <span class="ws-pill ws-pill-warning">BELUM BAYAR (OPEN BILL)</span>
      <?php elseif ($st === 'VOID'): ?>
        <span class="ws-pill ws-pill-danger">DIBATALKAN (VOID)</span>
      <?php else: ?>
        <span class="ws-pill ws-pill-success">LUNAS</span>
      <?php endif; ?>
    </div>
  </div>

  <div class="ws-receipt-dashed"></div>

  <div style="display:flex;flex-direction:column;gap:4px;font-size:12.5px;">
    <div style="display:flex;justify-content:space-between;">
      <span style="color:#6b7280;">No. Nota</span>
      <strong><?= e($order['order_code']) ?></strong>
    </div>
    <div style="display:flex;justify-content:space-between;">
      <span style="color:#6b7280;">Tanggal</span>
      <span><?= e($order['created_at']) ?></span>
    </div>
    <div style="display:flex;justify-content:space-between;">
      <span style="color:#6b7280;">Pelanggan / Meja</span>
      <span><?= e($order['customer_name']) ?> · <?= e($order['table_no']) ?> (<?= e($order['order_type']) ?>)</span>
    </div>
    <div style="display:flex;justify-content:space-between;">
      <span style="color:#6b7280;">Kasir</span>
      <span><?= e($order['cashier_name']) ?></span>
    </div>
  </div>

  <div class="ws-receipt-dashed"></div>

  <div style="display:flex;flex-direction:column;gap:10px;">
    <?php foreach ($order['items'] as $it): ?>
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;font-size:13px;">
        <div>
          <div style="font-weight:700;color:#1f2937;"><?= e($it['product_name']) ?></div>
          <div style="font-size:11.5px;color:#6b7280;"><?= formatQty($it['quantity']) ?> x <?= formatRp($it['unit_price']) ?></div>
        </div>
        <strong style="white-space:nowrap;"><?= formatRp($it['subtotal']) ?></strong>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="ws-receipt-dashed"></div>

  <div style="display:flex;flex-direction:column;gap:5px;font-size:13px;">
    <div style="display:flex;justify-content:space-between;">
      <span style="color:#6b7280;">Sub Total</span>
      <span><?= formatRp($order['subtotal']) ?></span>
    </div>
    <?php if ((float)$order['discount'] > 0): ?>
    <div style="display:flex;justify-content:space-between;">
      <span style="color:#6b7280;">Diskon</span>
      <span style="color:#2563eb;">-<?= formatRp($order['discount']) ?></span>
    </div>
    <?php endif; ?>
    <div style="display:flex;justify-content:space-between;font-size:15.5px;font-weight:800;margin-top:4px;color:#1f2937;">
      <span>TOTAL BAYAR</span>
      <span style="color:#2563eb;"><?= formatRp($order['total_amount']) ?></span>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-top:4px;">
      <span style="color:#6b7280;">Bayar (<?= e($order['payment_method']) ?>)</span>
      <strong><?= formatRp($order['cash_paid']) ?></strong>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:12.5px;">
      <span style="color:#6b7280;">Kembalian</span>
      <strong style="color:#059669;"><?= formatRp($order['change_amount']) ?></strong>
    </div>
  </div>

  <?php if (!empty($order['note'])): ?>
  <div class="ws-receipt-dashed"></div>
  <div style="font-size:12px;color:#4b5563;">
    <strong>Catatan Dapur:</strong> <?= e($order['note']) ?>
  </div>
  <?php endif; ?>

  <div class="ws-receipt-dashed"></div>
  <div style="text-align:center;font-size:12px;color:#6b7280;">
    Terima kasih sudah nongkrong di <strong>WARKOP MK</strong>!<br>
    <span style="font-size:11px;color:#9ca3af;">Harga sudah termasuk senyum ramah kasir :)</span>
  </div>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>

