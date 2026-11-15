<?php if (isLoggedIn()): ?>
    </main>
  </div>
</div>

<nav class="ws-bottom-nav ws-no-print" aria-label="Navigasi cepat mobile">
  <a href="<?= BASE_URL ?>/" class="ws-bottom-nav-item <?= in_array($currentPage, ['dashboard', '', 'checkout'], true) ? 'is-active' : '' ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
    <span>Kasir</span>
  </a>
  <a href="<?= BASE_URL ?>/?page=products" class="ws-bottom-nav-item <?= $currentPage === 'products' ? 'is-active' : '' ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/><line x1="6" x2="6" y1="2" y2="4"/><line x1="10" x2="10" y1="2" y2="4"/><line x1="14" x2="14" y1="2" y2="4"/></svg>
    <span>Menu</span>
    <?php if ($lowCount > 0): ?><span class="ws-bnav-dot"></span><?php endif; ?>
  </a>
  <a href="<?= BASE_URL ?>/?page=history" class="ws-bottom-nav-item <?= in_array($currentPage, ['history', 'receipt'], true) ? 'is-active' : '' ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M8 2v4"/><path d="M16 2v4"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/></svg>
    <span>Riwayat</span>
  </a>
  <a href="<?= BASE_URL ?>/?page=stock-movement" class="ws-bottom-nav-item <?= $currentPage === 'stock-movement' ? 'is-active' : '' ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 16V4m0 0L3 8m4-4 4 4"/><path d="M17 8v12m0 0 4-4m-4 4-4-4"/></svg>
    <span>Restock</span>
  </a>
  <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
  <a href="<?= BASE_URL ?>/?page=sales-report" class="ws-bottom-nav-item <?= $currentPage === 'sales-report' ? 'is-active' : '' ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
    <span>Laporan</span>
  </a>
  <?php endif; ?>
</nav>
<?php else: ?>
  </div>
</main>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/app.js?v=<?= @filemtime(__DIR__ . '/../../../public/assets/app.js') ?: 1 ?>"></script>
</body>
</html>
