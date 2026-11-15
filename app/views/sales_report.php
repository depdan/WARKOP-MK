<?php
require __DIR__ . '/layout/header.php';

$dayNames = [
    'Sun' => 'Minggu',
    'Mon' => 'Senin',
    'Tue' => 'Selasa',
    'Wed' => 'Rabu',
    'Thu' => 'Kamis',
    'Fri' => 'Jumat',
    'Sat' => 'Sabtu',
];
$startVal = $_GET['start'] ?? '';
$endVal = $_GET['end'] ?? '';
$netTotal = (float)($summary['revenue_total'] ?? 0) - (float)($summary['expenses_total'] ?? 0);
?>

<div class="ws-page-header ws-no-print">
  <div>
    <h1 class="ws-page-title">Laporan Penjualan &amp; Pembukuan Admin</h1>
    <p class="ws-page-sub">Pencatatan kas masuk/keluar, kulakan, transaksi hari ini, laba menu, dan jejak audit kasir.</p>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <a href="<?= BASE_URL ?>/?page=sales-report&export=csv<?= $startVal ? '&start=' . urlencode($startVal) : '' ?><?= $endVal ? '&end=' . urlencode($endVal) : '' ?>" class="ws-btn ws-btn-outline-blue">Export CSV / Excel</a>
    <button type="button" onclick="window.print()" class="ws-btn ws-btn-secondary">Cetak Laporan</button>
    <a href="<?= BASE_URL ?>/" class="ws-btn ws-btn-primary">+ Transaksi Kasir</a>
  </div>
</div>

<!-- Date Filter Form -->
<form method="get" class="ws-filter-bar ws-no-print">
  <input type="hidden" name="page" value="sales-report">
  <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
    <label class="ws-form-label" style="margin:0;">Periode:</label>
    <input type="date" name="start" class="ws-mini-input" style="width:auto;" value="<?= e($startVal) ?>">
    <span style="color:#6b7280;font-size:12px;">s/d</span>
    <input type="date" name="end" class="ws-mini-input" style="width:auto;" value="<?= e($endVal) ?>">
    <button type="submit" class="ws-btn ws-btn-primary" style="padding:6px 13px;font-size:12.5px;">Terapkan</button>
    <?php if ($startVal || $endVal): ?>
      <a href="<?= BASE_URL ?>/?page=sales-report" class="ws-btn ws-btn-secondary" style="padding:6px 12px;font-size:12.5px;">Reset</a>
    <?php endif; ?>
  </div>
</form>

<!-- Admin Sales KPI Chips -->
<div class="ws-kpi-inline" style="margin-bottom:18px;gap:10px;">
  <span class="ws-kpi-chip" style="padding:9px 14px;font-size:13px;">
    Order Hari Ini: <strong style="color:#2563eb;"><?= (int)($summary['orders_today'] ?? 0) ?></strong>
  </span>
  <span class="ws-kpi-chip" style="padding:9px 14px;font-size:13px;">
    Omzet Hari Ini: <strong style="color:#059669;"><?= formatRp($summary['revenue_today'] ?? 0) ?></strong>
  </span>
  <span class="ws-kpi-chip" style="padding:9px 14px;font-size:13px;">
    Pengeluaran Hari Ini: <strong style="color:#dc2626;"><?= formatRp($summary['expenses_today'] ?? 0) ?></strong>
  </span>
  <span class="ws-kpi-chip" style="padding:9px 14px;font-size:13px;">
    Total Omzet (<?= (int)($summary['orders_total'] ?? 0) ?> nota): <strong style="color:#2563eb;"><?= formatRp($summary['revenue_total'] ?? 0) ?></strong>
  </span>
  <span class="ws-kpi-chip" style="padding:9px 14px;font-size:13px;">
    Kas Bersih: <strong style="color:#059669;"><?= formatRp($netTotal) ?></strong>
  </span>
</div>

<div class="ws-tabs ws-no-print" id="wsSalesTabs">
  <button type="button" class="ws-tab-btn is-active" data-pane="today">Transaksi Hari Ini (<?= count($todayOrders) ?>)</button>
  <button type="button" class="ws-tab-btn" data-pane="expenses">Pengeluaran &amp; Kulakan (<?= count($expenses) ?>)</button>
  <button type="button" class="ws-tab-btn" data-pane="daily">Ringkasan Harian (<?= count($dailyReport) ?>)</button>
  <button type="button" class="ws-tab-btn" data-pane="menu">Rekap Menu &amp; Profit (<?= count($menuReport) ?>)</button>
  <button type="button" class="ws-tab-btn" data-pane="audit">Log Audit (<?= count($auditLogs) ?>)</button>
</div>

<!-- PANE 1: TRANSAKSI HARI INI -->
<div id="wsPaneToday" class="ws-table-card">
  <div class="table-responsive">
    <table class="ws-table ws-sales-tbl">
      <thead>
        <tr>
          <th>No. Nota</th>
          <th>Waktu</th>
          <th>Pembeli / Meja</th>
          <th>Detail Menu</th>
          <th>Status</th>
          <th>Total</th>
          <th>Kasir</th>
          <th style="text-align:right;" class="ws-no-print">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($todayOrders as $o):
          $itemsStr = implode(', ', array_map(
              static fn(array $it): string => formatQty($it['quantity']) . 'x ' . $it['product_name'],
              $o['items'] ?? []
          ));
          $st = $o['status'] ?? 'PAID';
        ?>
        <tr data-search="<?= e(mb_strtolower($o['order_code'] . ' ' . $o['customer_name'] . ' ' . $o['table_no'] . ' ' . $itemsStr . ' ' . $o['cashier_name'])) ?>">
          <td><span class="ws-sku-code"><?= e($o['order_code']) ?></span></td>
          <td class="ws-nowrap" style="color:#6b7280;font-size:12.5px;"><?= e($o['created_at']) ?></td>
          <td>
            <div style="font-weight:700;color:#1f2937;"><?= e($o['customer_name']) ?></div>
            <div style="font-size:11.5px;color:#6b7280;"><?= e($o['table_no']) ?> · <?= e($o['order_type']) ?></div>
          </td>
          <td style="max-width:250px;font-size:12.5px;color:#374151;"><?= e($itemsStr !== '' ? $itemsStr : '-') ?></td>
          <td>
            <?php if ($st === 'OPEN'): ?>
              <span class="ws-pill ws-pill-warning">Belum Bayar</span>
            <?php elseif ($st === 'VOID'): ?>
              <span class="ws-pill ws-pill-muted">Batal (VOID)</span>
            <?php else: ?>
              <span class="ws-pill ws-pill-success">Lunas · <?= e($o['payment_method']) ?></span>
            <?php endif; ?>
          </td>
          <td class="ws-nowrap" style="font-weight:800;color:<?= $st === 'VOID' ? '#9ca3af' : '#2563eb' ?>;<?= $st === 'VOID' ? 'text-decoration:line-through;' : '' ?>"><?= formatRp($o['total_amount']) ?></td>
          <td class="ws-nowrap"><?= e($o['cashier_name']) ?></td>
          <td style="text-align:right;" class="ws-no-print">
            <div style="display:inline-flex;gap:6px;align-items:center;">
              <a href="<?= BASE_URL ?>/?page=receipt&id=<?= (int)$o['id'] ?>" class="ws-btn ws-btn-outline-blue" style="padding:6px 10px;font-size:12px;">Struk</a>
              <?php if ($st !== 'VOID'): ?>
                <form method="post" action="<?= BASE_URL ?>/?page=void-order" onsubmit="return confirm('Batalkan transaksi ini (VOID) dan kembalikan stok?')" style="margin:0;">
                  <input type="hidden" name="_csrf" value="<?= e(csrfToken()) ?>">
                  <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                  <button class="ws-btn ws-btn-danger-soft" style="padding:6px 10px;font-size:12px;">Void</button>
                </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$todayOrders): ?>
        <tr><td colspan="8" style="text-align:center;color:#6b7280;padding:30px;">Belum ada transaksi hari ini.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- PANE 2: PENGELUARAN & KULAKAN KAS -->
<div id="wsPaneExpenses" style="display:none;">
  <div class="ws-card ws-no-print" style="padding:18px;margin-bottom:16px;">
    <h4 style="font-size:14.5px;font-weight:700;margin:0 0 12px;">Catat Pengeluaran / Kulakan Warkop</h4>
    <form method="post" action="<?= BASE_URL ?>/?page=sales-report" class="row g-2 align-items-end">
      <input type="hidden" name="_csrf" value="<?= e(csrfToken()) ?>">
      <input type="hidden" name="action" value="add_expense">
      <div class="col-md-2">
        <label class="ws-form-label">Tanggal</label>
        <input type="date" name="expense_date" class="ws-input" value="<?= date('Y-m-d') ?>" required>
      </div>
      <div class="col-md-3">
        <label class="ws-form-label">Kategori</label>
        <select name="category" class="ws-select">
          <option value="Kulakan Stok">Kulakan Stok</option>
          <option value="Operasional">Operasional (Gas/Es/Listrik)</option>
          <option value="Gaji / Kasbon">Gaji / Kasbon</option>
          <option value="Lainnya">Lainnya</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="ws-form-label">Keterangan *</label>
        <input type="text" name="description" class="ws-input" placeholder="Contoh: Belanja kopi renceng & galon" required>
      </div>
      <div class="col-md-2">
        <label class="ws-form-label">Nominal (Rp) *</label>
        <input type="number" name="amount" class="ws-input" min="500" step="500" placeholder="50000" required>
      </div>
      <div class="col-md-1">
        <button type="submit" class="ws-btn ws-btn-primary" style="width:100%;height:39px;">Catat</button>
      </div>
    </form>
  </div>

  <div class="ws-table-card">
    <div class="table-responsive">
      <table class="ws-table ws-sales-tbl">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Kategori</th>
            <th>Keterangan</th>
            <th>Nominal</th>
            <th>Dicatat Oleh</th>
            <th style="text-align:right;" class="ws-no-print">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($expenses as $ex): ?>
          <tr data-search="<?= e(mb_strtolower($ex['expense_date'] . ' ' . $ex['category'] . ' ' . $ex['description'])) ?>">
            <td class="ws-nowrap" style="font-weight:700;"><?= e($ex['expense_date']) ?></td>
            <td><span class="ws-pill ws-pill-blue"><?= e($ex['category']) ?></span></td>
            <td><?= e($ex['description']) ?></td>
            <td class="ws-nowrap" style="font-weight:800;color:#dc2626;">-<?= formatRp($ex['amount']) ?></td>
            <td class="ws-nowrap"><?= e($ex['user_name']) ?></td>
            <td style="text-align:right;" class="ws-no-print">
              <form method="post" action="<?= BASE_URL ?>/?page=sales-report" onsubmit="return confirm('Hapus catatan pengeluaran ini?')" style="margin:0;">
                <input type="hidden" name="_csrf" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="action" value="delete_expense">
                <input type="hidden" name="id" value="<?= (int)$ex['id'] ?>">
                <button class="ws-btn ws-btn-danger-soft" style="padding:5px 10px;font-size:12px;">Hapus</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$expenses): ?>
          <tr><td colspan="6" style="text-align:center;color:#6b7280;padding:28px;">Belum ada pengeluaran tercatat.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- PANE 3: RINGKASAN HARIAN -->
<div id="wsPaneDaily" class="ws-table-card" style="display:none;">
  <div class="table-responsive">
    <table class="ws-table ws-sales-tbl">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Hari</th>
          <th>Transaksi</th>
          <th>Item Terjual</th>
          <th>Omzet Masuk</th>
          <th>Pengeluaran</th>
          <th>Kas Bersih</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($dailyReport as $d):
          $ts = strtotime((string)$d['sale_date']);
          $hari = $dayNames[date('D', $ts)] ?? date('D', $ts);
          $isToday = $d['sale_date'] === date('Y-m-d');
        ?>
        <tr data-search="<?= e(mb_strtolower($d['sale_date'] . ' ' . $hari)) ?>">
          <td class="ws-nowrap" style="font-weight:700;color:#1f2937;">
            <?= e($d['sale_date']) ?>
            <?php if ($isToday): ?><span class="ws-pill ws-pill-success" style="margin-left:6px;">Hari Ini</span><?php endif; ?>
          </td>
          <td><?= e($hari) ?></td>
          <td><span class="ws-pill ws-pill-blue"><?= (int)$d['order_count'] ?> nota</span></td>
          <td class="ws-nowrap" style="font-weight:600;"><?= formatQty($d['qty_sold']) ?></td>
          <td class="ws-nowrap" style="font-weight:700;color:#2563eb;"><?= formatRp($d['revenue']) ?></td>
          <td class="ws-nowrap" style="color:#dc2626;font-weight:600;"><?= (float)$d['expense'] > 0 ? '-' . formatRp($d['expense']) : 'Rp 0' ?></td>
          <td class="ws-nowrap" style="font-weight:800;color:#059669;"><?= formatRp($d['net_cash']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- PANE 4: REKAP MENU & PROFIT -->
<div id="wsPaneMenu" class="ws-table-card" style="display:none;">
  <div class="table-responsive">
    <table class="ws-table ws-sales-tbl">
      <thead>
        <tr>
          <th>#</th>
          <th>Kode</th>
          <th>Nama Menu</th>
          <th>Terjual</th>
          <th>Transaksi</th>
          <th>Omzet</th>
          <th>Est. Modal</th>
          <th>Est. Laba</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($menuReport as $idx => $m): ?>
        <tr data-search="<?= e(mb_strtolower($m['sku'] . ' ' . $m['product_name'])) ?>">
          <td style="color:#9ca3af;font-weight:700;"><?= $idx + 1 ?></td>
          <td><span class="ws-sku-code"><?= e($m['sku']) ?></span></td>
          <td style="font-weight:700;color:#1f2937;"><?= e($m['product_name']) ?></td>
          <td class="ws-nowrap"><span class="ws-pill ws-pill-success"><?= formatQty($m['qty_sold']) ?></span></td>
          <td class="ws-nowrap"><?= (int)$m['tx_count'] ?>x</td>
          <td class="ws-nowrap" style="font-weight:700;color:#2563eb;"><?= formatRp($m['omzet']) ?></td>
          <td class="ws-nowrap" style="color:#6b7280;"><?= formatRp($m['cost_total']) ?></td>
          <td class="ws-nowrap" style="font-weight:800;color:#059669;"><?= formatRp($m['est_profit']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- PANE 5: LOG AUDIT AKTIVITAS -->
<div id="wsPaneAudit" class="ws-table-card" style="display:none;">
  <div class="table-responsive">
    <table class="ws-table ws-sales-tbl">
      <thead>
        <tr>
          <th>Waktu</th>
          <th>Petugas</th>
          <th>Aksi</th>
          <th>Tabel</th>
          <th>Ringkasan Data</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($auditLogs as $log): ?>
        <tr data-search="<?= e(mb_strtolower($log['user_name'] . ' ' . $log['action'] . ' ' . ($log['new_data'] ?? ''))) ?>">
          <td class="ws-nowrap" style="color:#6b7280;font-size:12.5px;"><?= e($log['created_at']) ?></td>
          <td class="ws-nowrap" style="font-weight:600;"><?= e($log['user_name']) ?></td>
          <td><span class="ws-pill ws-pill-blue"><?= e($log['action']) ?></span></td>
          <td><span class="ws-sku-code"><?= e($log['table_name'] ?? '-') ?></span></td>
          <td style="font-size:12px;color:#4b5563;max-width:360px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e((string)($log['new_data'] ?? $log['old_data'] ?? '')) ?>">
            <?= e((string)($log['new_data'] ?? $log['old_data'] ?? '-')) ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$auditLogs): ?>
        <tr><td colspan="5" style="text-align:center;color:#6b7280;padding:28px;">Belum ada aktivitas tercatat.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
