<?php
$flash = getFlash();
$currentPage = $_GET['page'] ?? 'dashboard';
$searchValue = trim($_GET['q'] ?? '');
$notifications = isLoggedIn() ? getHeaderNotifications() : [];
$lowCount = count($notifications);
$currentUser = user();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/app.css?v=<?= @filemtime(__DIR__ . '/../../../public/assets/app.css') ?: 1 ?>" rel="stylesheet">
</head>
<body>
<?php if (isLoggedIn()): ?>
<div class="ws-shell">
  <aside class="ws-sidebar" id="wsSidebar">
    <div>
      <div class="ws-sidebar-head">
        <a class="ws-brand" href="<?= BASE_URL ?>/">
          <svg class="ws-brand-mark" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <circle cx="16" cy="16" r="14" fill="#2563eb"/>
            <path d="M16 2A14 14 0 0 1 30 16H16V2Z" fill="#93c5fd"/>
            <circle cx="16" cy="16" r="5" fill="#FFFFFF"/>
          </svg>
          <span class="ws-brand-text">WARKOP <span>MK</span></span>
        </a>
        <button type="button" class="ws-sidebar-close" id="wsSidebarClose" aria-label="Tutup menu">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>

      <nav class="ws-nav" aria-label="Menu utama">
        <a class="ws-nav-item <?= ($currentPage === 'dashboard' || $currentPage === '' || $currentPage === 'checkout') ? 'is-active' : '' ?>" href="<?= BASE_URL ?>/">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
          <span>Kasir &amp; Menu</span>
        </a>

        <a class="ws-nav-item <?= $currentPage === 'products' ? 'is-active' : '' ?>" href="<?= BASE_URL ?>/?page=products">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/><line x1="6" x2="6" y1="2" y2="4"/><line x1="10" x2="10" y1="2" y2="4"/><line x1="14" x2="14" y1="2" y2="4"/></svg>
          <span>Daftar Menu</span>
          <?php if ($lowCount > 0): ?>
            <span class="ws-nav-badge"><?= $lowCount ?></span>
          <?php endif; ?>
        </a>

        <a class="ws-nav-item <?= $currentPage === 'stock-movement' ? 'is-active' : '' ?>" href="<?= BASE_URL ?>/?page=stock-movement">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M7 16V4m0 0L3 8m4-4 4 4"/><path d="M17 8v12m0 0 4-4m-4 4-4-4"/></svg>
          <span>Restock Dapur</span>
        </a>

        <a class="ws-nav-item <?= in_array($currentPage, ['history', 'receipt'], true) ? 'is-active' : '' ?>" href="<?= BASE_URL ?>/?page=history">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M8 2v4"/><path d="M16 2v4"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/></svg>
          <span>Riwayat Pesanan</span>
        </a>

        <?php if ($currentUser['role'] === 'admin'): ?>
        <a class="ws-nav-item <?= $currentPage === 'sales-report' ? 'is-active' : '' ?>" href="<?= BASE_URL ?>/?page=sales-report">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
          <span>Laporan Penjualan</span>
        </a>

        <a class="ws-nav-item <?= $currentPage === 'users' ? 'is-active' : '' ?>" href="<?= BASE_URL ?>/?page=users">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <span>Kelola Kasir</span>
        </a>
        <?php endif; ?>
      </nav>
    </div>

    <div class="ws-guide-card" id="wsGuideCard">
      <div class="ws-guide-visual">
        <svg viewBox="0 0 180 90" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%" aria-hidden="true">
          <rect x="18" y="16" width="54" height="62" rx="9" fill="#FFFFFF" stroke="#93c5fd" stroke-width="1.5"/>
          <rect x="25" y="25" width="40" height="18" rx="4" fill="#eff6ff"/>
          <circle cx="34" cy="34" r="5" fill="#2563eb"/>
          <rect x="25" y="49" width="28" height="5" rx="2.5" fill="#E5E7EB"/>
          <rect x="25" y="58" width="38" height="5" rx="2.5" fill="#F3F4F6"/>
          <rect x="86" y="12" width="76" height="66" rx="11" fill="#1F2937"/>
          <circle cx="124" cy="38" r="14" fill="#2563eb"/>
          <path d="M118 38h12M124 32v12" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/>
          <rect x="100" y="60" width="48" height="6" rx="3" fill="#4B5563"/>
        </svg>
      </div>
      <h4 class="ws-guide-title" id="wsGuideTitle">Cara order di kasir?</h4>
      <p class="ws-guide-desc" id="wsGuideDesc">Pilih menu kopi &amp; makanan di kartu, isi meja di kanan, lalu klik Bayar Sekarang.</p>
      <div class="ws-guide-controls">
        <div class="ws-guide-arrows">
          <button type="button" class="ws-circle-btn" id="wsGuidePrev" aria-label="Tips sebelumnya">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
          </button>
          <button type="button" class="ws-circle-btn is-primary" id="wsGuideNext" aria-label="Tips berikutnya">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
          </button>
        </div>
        <span class="ws-guide-step" id="wsGuideStep">1 / 3</span>
      </div>
    </div>
  </aside>
  <div class="ws-sidebar-backdrop" id="wsSidebarBackdrop"></div>

  <div class="ws-main-wrap">
    <header class="ws-topbar">
      <div class="ws-topbar-left">
        <button type="button" class="ws-mobile-toggle" id="wsSidebarToggle" aria-label="Buka menu">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
        </button>

        <form class="ws-search-form" method="get" action="<?= BASE_URL ?>/">
          <input type="hidden" name="page" value="<?= in_array($currentPage, ['products', 'dashboard'], true) ? e($currentPage) : 'dashboard' ?>">
          <div class="ws-search-input-wrap">
            <svg class="ws-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input
              type="search"
              name="q"
              id="wsGlobalSearch"
              class="ws-search-input"
              value="<?= e($searchValue) ?>"
              placeholder="Cari kopi, indomie, roti bakar..."
              autocomplete="off"
            >
          </div>
          <button type="submit" class="ws-btn-filter" id="wsFilterActionBtn">
            <span>Filter</span>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
          </button>
        </form>
      </div>

      <div class="ws-topbar-right">
        <button type="button" class="ws-icon-btn" id="wsNotifToggle" aria-label="Peringatan porsi menipis">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
          <?php if ($lowCount > 0): ?><span class="ws-notif-dot"></span><?php endif; ?>
        </button>

        <div class="ws-popover" id="wsNotifPopover">
          <div class="ws-popover-head">
            <span>Porsi Menu Menipis</span>
            <span class="ws-pill ws-pill-blue"><?= $lowCount ?> menu</span>
          </div>
          <?php if ($lowCount > 0): ?>
            <div style="display:flex;flex-direction:column;gap:8px;max-height:220px;overflow-y:auto;">
              <?php foreach ($notifications as $n): ?>
                <a href="<?= BASE_URL ?>/?page=stock-movement&product_id=<?= (int)$n['id'] ?>" style="display:flex;justify-content:space-between;align-items:center;padding:7px 9px;border-radius:8px;background:#eff6ff;border:1px solid #bfdbfe;">
                  <div>
                    <div style="font-size:12.5px;font-weight:700;color:#1f2937;"><?= e($n['name']) ?></div>
                    <div style="font-size:11px;color:#6b7280;">Min <?= formatQty($n['min_stock']) ?> <?= e($n['unit']) ?></div>
                  </div>
                  <span class="ws-pill ws-pill-warning"><?= formatQty($n['stock']) ?> <?= e($n['unit']) ?></span>
                </a>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div style="font-size:12.5px;color:#6b7280;padding:8px 0;">Semua porsi menu siap dijual.</div>
          <?php endif; ?>
        </div>

        <button type="button" class="ws-user-pill" id="wsUserToggle">
          <span class="ws-avatar"><?= e(userInitials($currentUser['name'])) ?></span>
          <span class="ws-user-name"><?= e($currentUser['name']) ?></span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
        </button>

        <div class="ws-popover" id="wsUserPopover" style="width:220px;">
          <div style="padding:4px 4px 10px;border-bottom:1px solid #ececf1;margin-bottom:8px;">
            <div style="font-size:13px;font-weight:700;"><?= e($currentUser['name']) ?></div>
            <div style="font-size:11.5px;color:#6b7280;"><?= e($currentUser['email']) ?></div>
            <span class="ws-pill ws-pill-blue" style="margin-top:6px;"><?= $currentUser['role'] === 'admin' ? 'OWNER' : 'KASIR' ?></span>
          </div>
          <a href="<?= BASE_URL ?>/?page=logout" class="ws-btn ws-btn-danger-soft" style="width:100%;">Keluar dari Kasir</a>
        </div>
      </div>
    </header>

    <main class="ws-content">
<?php else: ?>
<main class="ws-auth-wrap">
  <div style="width:100%;max-width:440px;">
<?php endif; ?>
    <?php if ($flash): ?>
      <div class="ws-flash ws-flash-<?= e($flash['type']) ?>" role="alert">
        <span><?= e($flash['message']) ?></span>
        <button type="button" class="btn-close" style="font-size:11px;" onclick="this.parentElement.remove()" aria-label="Tutup"></button>
      </div>
    <?php endif; ?>
