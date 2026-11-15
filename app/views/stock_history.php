<?php require __DIR__ . '/layout/header.php'; ?>

<div class="ws-page-header">
  <div>
    <h1 class="ws-page-title">Riwayat Pesanan &amp; Kasir</h1>
    <p class="ws-page-sub">Daftar transaksi pesanan pelanggan WARKOP MK, tagihan meja aktif, dan log stok dapur.</p>
  </div>
  <a href="<?= BASE_URL ?>/" class="ws-btn ws-btn-primary">+ Buat Pesanan Baru</a>
</div>

<div class="ws-tabs" id="wsHistoryTabs">
  <button type="button" class="ws-tab-btn is-active" data-pane="orders">Pesanan Kasir (<?= count($orders) ?>)</button>
  <button type="button" class="ws-tab-btn" data-pane="movements">Log Porsi &amp; Restock (<?= count($movements) ?>)</button>
</div>

<div id="wsOrdersSection" class="ws-table-card">
  <div class="table-responsive">
    <table class="ws-table" id="wsOrdersTable">
      <thead>
        <tr>
          <th>No. Nota</th>
          <th>Waktu</th>
          <th>Meja / Pembeli</th>
          <th>Pesanan Menu</th>
          <th>Status &amp; Bayar</th>
          <th>Total</th>
          <th>Kasir</th>
          <th style="text-align:right;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o):
          $itemNames = array_map(
            static fn(array $it): string => formatQty($it['quantity']) . 'x ' . $it['product_name'],
            $o['items'] ?? []
          );
          $summaryText = implode(', ', $itemNames);
          $st = $o['status'] ?? 'PAID';
        ?>
        <tr data-search="<?= e(mb_strtolower($o['order_code'] . ' ' . $o['customer_name'] . ' ' . $o['table_no'] . ' ' . $o['payment_method'] . ' ' . $summaryText . ' ' . $o['cashier_name'])) ?>">
          <td><span class="ws-sku-code"><?= e($o['order_code']) ?></span></td>
          <td class="ws-nowrap" style="color:#6b7280;font-size:12.5px;"><?= e($o['created_at']) ?></td>
          <td>
            <div style="font-weight:700;color:#1f2937;"><?= e($o['customer_name']) ?></div>
            <div style="font-size:11.5px;color:#6b7280;"><?= e($o['table_no']) ?> · <?= e($o['order_type']) ?></div>
          </td>
          <td style="max-width:260px;">
            <div style="font-size:12.5px;color:#374151;line-height:1.4;"><?= e($summaryText !== '' ? $summaryText : '-') ?></div>
            <?php if (!empty($o['note'])): ?>
              <div style="font-size:11px;color:#9ca3af;">Catatan: <?= e($o['note']) ?></div>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($st === 'OPEN'): ?>
              <span class="ws-pill ws-pill-warning">Belum Bayar</span>
            <?php elseif ($st === 'VOID'): ?>
              <span class="ws-pill ws-pill-muted">Dibatalkan</span>
            <?php else: ?>
              <span class="ws-pill ws-pill-success">Lunas · <?= e($o['payment_method']) ?></span>
            <?php endif; ?>
          </td>
          <td class="ws-nowrap" style="font-weight:800;color:<?= $st === 'VOID' ? '#9ca3af' : '#2563eb' ?>;<?= $st === 'VOID' ? 'text-decoration:line-through;' : '' ?>"><?= formatRp($o['total_amount']) ?></td>
          <td>
            <span style="display:inline-flex;align-items:center;gap:6px;">
              <span class="ws-avatar" style="width:24px;height:24px;font-size:10px;"><?= e(userInitials($o['cashier_name'])) ?></span>
              <span class="ws-nowrap"><?= e($o['cashier_name']) ?></span>
            </span>
          </td>
          <td style="text-align:right;">
            <div style="display:inline-flex;gap:6px;align-items:center;">
              <?php if ($st === 'OPEN'): ?>
                <form method="post" action="<?= BASE_URL ?>/?page=pay-bill" style="margin:0;">
                  <input type="hidden" name="_csrf" value="<?= e(csrfToken()) ?>">
                  <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                  <input type="hidden" name="payment_method" value="Tunai">
                  <button class="ws-btn ws-btn-primary" style="padding:6px 11px;font-size:12px;">Lunasi Tunai</button>
                </form>
              <?php endif; ?>
              <a href="<?= BASE_URL ?>/?page=receipt&id=<?= (int)$o['id'] ?>" class="ws-btn ws-btn-outline-blue" style="padding:6px 11px;font-size:12px;">Struk</a>
              <?php if (user()['role'] === 'admin' && $st !== 'VOID'): ?>
                <form method="post" action="<?= BASE_URL ?>/?page=void-order" onsubmit="return confirm('Batalkan transaksi ini dan kembalikan stok menu?')" style="margin:0;">
                  <input type="hidden" name="_csrf" value="<?= e(csrfToken()) ?>">
                  <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                  <input type="hidden" name="redirect_to" value="history">
                  <button class="ws-btn ws-btn-danger-soft" style="padding:6px 10px;font-size:12px;">Void</button>
                </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$orders): ?>
        <tr>
          <td colspan="8" style="text-align:center;color:#6b7280;padding:32px;">Belum ada pesanan yang masuk.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div id="wsMovementsSection" class="ws-table-card" style="display:none;">
  <div class="table-responsive">
    <table class="ws-table" id="wsHistoryTable">
      <thead>
        <tr>
          <th>Waktu</th>
          <th>Kode</th>
          <th>Menu</th>
          <th>Jenis</th>
          <th>Qty</th>
          <th>Saldo</th>
          <th>Petugas</th>
          <th>Keterangan</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($movements as $m):
          $isIn = $m['type'] === 'IN';
        ?>
        <tr data-search="<?= e(mb_strtolower($m['sku'] . ' ' . $m['product_name'] . ' ' . $m['user_name'] . ' ' . $m['note'])) ?>">
          <td class="ws-nowrap" style="color:#6b7280;font-size:12.5px;"><?= e($m['created_at']) ?></td>
          <td><span class="ws-sku-code"><?= e($m['sku']) ?></span></td>
          <td style="font-weight:700;color:#1f2937;"><?= e($m['product_name']) ?></td>
          <td>
            <span class="ws-pill <?= $isIn ? 'ws-pill-success' : 'ws-pill-blue' ?>">
              <?= $isIn ? 'RESTOCK (IN)' : 'TERJUAL / KELUAR' ?>
            </span>
          </td>
          <td class="ws-nowrap" style="font-weight:800;color:<?= $isIn ? '#059669' : '#2563eb' ?>;">
            <?= $isIn ? '+' : '-' ?><?= formatQty($m['quantity']) ?>
          </td>
          <td class="ws-nowrap">
            <span style="color:#9ca3af;"><?= formatQty($m['stock_before']) ?></span>
            <span style="margin:0 4px;color:#9ca3af;">→</span>
            <strong><?= formatQty($m['stock_after']) ?></strong>
          </td>
          <td>
            <span style="display:inline-flex;align-items:center;gap:6px;">
              <span class="ws-avatar" style="width:24px;height:24px;font-size:10px;"><?= e(userInitials($m['user_name'])) ?></span>
              <span class="ws-nowrap"><?= e($m['user_name']) ?></span>
            </span>
          </td>
          <td style="color:#4b5563;"><?= e($m['note'] !== '' ? $m['note'] : '-') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$movements): ?>
        <tr>
          <td colspan="8" style="text-align:center;color:#6b7280;padding:32px;">Belum ada log pergerakan porsi dapur.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
