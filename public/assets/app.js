(function () {
  'use strict';

  const formatRp = (num) => 'Rp ' + Math.round(num).toLocaleString('id-ID');

  // --------------------------------------------------------------------------
  // 1. SIDEBAR MOBILE DRAWER & TOPBAR POPOVERS
  // --------------------------------------------------------------------------
  const sidebar = document.getElementById('wsSidebar');
  const sidebarToggle = document.getElementById('wsSidebarToggle');
  const sidebarClose = document.getElementById('wsSidebarClose');
  const sidebarBackdrop = document.getElementById('wsSidebarBackdrop');

  function openSidebar() {
    if (sidebar) sidebar.classList.add('is-open');
    if (sidebarBackdrop) sidebarBackdrop.classList.add('is-open');
  }

  function closeSidebar() {
    if (sidebar) sidebar.classList.remove('is-open');
    if (sidebarBackdrop) sidebarBackdrop.classList.remove('is-open');
  }

  if (sidebar && sidebarToggle) {
    sidebarToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      if (sidebar.classList.contains('is-open')) closeSidebar();
      else openSidebar();
    });
  }

  if (sidebarClose) {
    sidebarClose.addEventListener('click', (e) => {
      e.stopPropagation();
      closeSidebar();
    });
  }

  if (sidebarBackdrop) {
    sidebarBackdrop.addEventListener('click', closeSidebar);
  }

  const notifToggle = document.getElementById('wsNotifToggle');
  const notifPopover = document.getElementById('wsNotifPopover');
  const userToggle = document.getElementById('wsUserToggle');
  const userPopover = document.getElementById('wsUserPopover');

  function closePopovers() {
    if (notifPopover) notifPopover.classList.remove('is-open');
    if (userPopover) userPopover.classList.remove('is-open');
  }

  if (notifToggle && notifPopover) {
    notifToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      const wasOpen = notifPopover.classList.contains('is-open');
      closePopovers();
      if (!wasOpen) notifPopover.classList.add('is-open');
    });
  }

  if (userToggle && userPopover) {
    userToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      const wasOpen = userPopover.classList.contains('is-open');
      closePopovers();
      if (!wasOpen) userPopover.classList.add('is-open');
    });
  }

  document.addEventListener('click', (e) => {
    if (sidebar && sidebar.classList.contains('is-open') && !sidebar.contains(e.target) && e.target !== sidebarToggle) {
      closeSidebar();
    }
    if (notifPopover && !notifPopover.contains(e.target) && e.target !== notifToggle) {
      notifPopover.classList.remove('is-open');
    }
    if (userPopover && !userPopover.contains(e.target) && e.target !== userToggle) {
      userPopover.classList.remove('is-open');
    }
  });

  // --------------------------------------------------------------------------
  // 2. SIDEBAR GUIDE CAROUSEL
  // --------------------------------------------------------------------------
  const guideSlides = [
    {
      title: 'Cara order di kasir?',
      desc: 'Pilih menu kopi & makanan di kartu, isi meja di kanan, lalu klik Bayar Sekarang.'
    },
    {
      title: 'Dine In atau Bungkus',
      desc: 'Atur tipe pesanan & nomor meja, lalu terima pembayaran Tunai, QRIS, Transfer, atau E-Wallet.'
    },
    {
      title: 'Struk & stok otomatis',
      desc: 'Setiap transaksi langsung mencetak struk pesanan dan memotong sisa porsi menu.'
    }
  ];
  let guideIdx = 0;
  const gTitle = document.getElementById('wsGuideTitle');
  const gDesc = document.getElementById('wsGuideDesc');
  const gStep = document.getElementById('wsGuideStep');
  const gPrev = document.getElementById('wsGuidePrev');
  const gNext = document.getElementById('wsGuideNext');

  function renderGuide() {
    if (!gTitle || !gDesc || !gStep) return;
    const item = guideSlides[guideIdx];
    gTitle.textContent = item.title;
    gDesc.textContent = item.desc;
    gStep.textContent = `${guideIdx + 1} / ${guideSlides.length}`;
  }

  if (gPrev && gNext) {
    gPrev.addEventListener('click', () => {
      guideIdx = (guideIdx - 1 + guideSlides.length) % guideSlides.length;
      renderGuide();
    });
    gNext.addEventListener('click', () => {
      guideIdx = (guideIdx + 1) % guideSlides.length;
      renderGuide();
    });
  }

  // --------------------------------------------------------------------------
  // 3. POS KASIR WARKOP: MENU FILTERING, CART & CHECKOUT SUMMARY
  // --------------------------------------------------------------------------
  const dashboardRoot = document.getElementById('wsDashboardRoot');
  const globalSearch = document.getElementById('wsGlobalSearch');

  if (dashboardRoot) {
    const cards = Array.from(document.querySelectorAll('.ws-product-card'));
    const invoiceList = document.getElementById('wsInvoiceList');
    const sumQty = document.getElementById('wsSumQty');
    const sumSubtotal = document.getElementById('wsSumSubtotal');
    const discountInput = document.getElementById('wsDiscountInput');
    const sumTotal = document.getElementById('wsSumTotal');
    const checkoutSubmit = document.getElementById('wsCheckoutSubmit');
    const clearBtn = document.getElementById('wsClearSelection');
    const catChips = Array.from(document.querySelectorAll('#wsCategoryChips .ws-cat-chip'));
    const tabBtns = Array.from(document.querySelectorAll('#wsDashboardTabs .ws-tab-btn'));
    const paymentCards = Array.from(document.querySelectorAll('#wsPaymentGrid .ws-method-card'));
    const paymentInput = document.getElementById('wsPaymentMethod');
    const cashBox = document.getElementById('wsCashBox');
    const cashPaidInput = document.getElementById('wsCashPaidInput');
    const cashChange = document.getElementById('wsCashChange');
    const orderTypeInput = document.getElementById('wsOrderType');
    const orderTypePills = Array.from(document.querySelectorAll('#wsOrderTypeSwitch .ws-type-pill'));
    const tableInput = document.getElementById('wsTableInput');

    let activeCat = 'all';
    let activeTab = 'all';
    let currentTotal = 0;
    const cartQty = new Map();

    // Shortcut '/' focuses search bar for rapid cashier lookup
    document.addEventListener('keydown', (e) => {
      if (e.key === '/' && globalSearch && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
        e.preventDefault();
        globalSearch.focus();
      }
    });

    function applyDashboardFilters() {
      const q = (globalSearch ? globalSearch.value : '').trim().toLowerCase();
      cards.forEach((card) => {
        const name = (card.dataset.name || '').toLowerCase();
        const sku = (card.dataset.sku || '').toLowerCase();
        const cat = card.dataset.cat || '';
        const isLow = card.dataset.low === '1';
        const isPopular = card.dataset.popular === '1';

        const matchCat = activeCat === 'all' || cat === activeCat;

        let matchTab = true;
        if (activeTab === 'low') matchTab = isLow;
        else if (activeTab === 'popular') matchTab = isPopular;

        const matchSearch = !q || name.includes(q) || sku.includes(q);
        card.style.display = matchCat && matchTab && matchSearch ? '' : 'none';
      });
    }

    function updateTotals(subtotal) {
      const disc = Math.min(Math.max(0, parseFloat((discountInput && discountInput.value) || '0') || 0), subtotal);
      currentTotal = Math.max(0, subtotal - disc);

      if (sumSubtotal) sumSubtotal.textContent = formatRp(subtotal);
      if (sumTotal) sumTotal.textContent = formatRp(currentTotal);

      if (cashChange && cashPaidInput) {
        const paid = parseFloat(cashPaidInput.value || '0') || 0;
        if (paid <= 0) {
          cashChange.textContent = 'Uang pas';
          cashChange.style.color = '#6b7280';
        } else if (paid < currentTotal) {
          cashChange.textContent = 'Kurang ' + formatRp(currentTotal - paid);
          cashChange.style.color = '#dc2626';
        } else {
          cashChange.textContent = formatRp(paid - currentTotal);
          cashChange.style.color = '#059669';
        }
      }
    }

    function renderInvoicePanel() {
      if (!invoiceList) return;
      invoiceList.innerHTML = '';

      let itemCount = 0;
      let totalQty = 0;
      let subtotal = 0;

      cards.forEach((card) => {
        const id = card.dataset.id;
        const maxStock = parseFloat(card.dataset.stock || '0');
        const actionBtn = card.querySelector('.ws-card-btn-accent');

        if (!cartQty.has(id) || maxStock <= 0) {
          cartQty.delete(id);
          card.classList.remove('is-selected');
          if (actionBtn && maxStock > 0) actionBtn.textContent = '+ Pesan';
          return;
        }

        card.classList.add('is-selected');
        if (actionBtn) actionBtn.textContent = 'Di Pesanan';

        let qty = cartQty.get(id) || 1;
        if (qty > maxStock) {
          qty = Math.floor(maxStock) > 0 ? Math.floor(maxStock) : maxStock;
          cartQty.set(id, qty);
        }

        const price = parseFloat(card.dataset.price || '0');
        const name = card.dataset.name || '';
        const lineValue = qty * price;

        itemCount++;
        totalQty += qty;
        subtotal += lineValue;

        const row = document.createElement('div');
        row.className = 'ws-invoice-item';

        const thumb = document.createElement('div');
        thumb.className = 'ws-invoice-thumb';
        const tpl = card.querySelector('.ws-card-svg-tpl');
        if (tpl) thumb.innerHTML = tpl.innerHTML;

        const info = document.createElement('div');
        info.className = 'ws-invoice-info';
        const titleEl = document.createElement('div');
        titleEl.className = 'ws-invoice-name';
        titleEl.textContent = name;
        const priceEl = document.createElement('div');
        priceEl.className = 'ws-invoice-price';
        priceEl.textContent = `${formatRp(lineValue)} · ${qty}x`;
        info.appendChild(titleEl);
        info.appendChild(priceEl);

        const stepper = document.createElement('div');
        stepper.className = 'ws-qty-stepper';

        const minusBtn = document.createElement('button');
        minusBtn.type = 'button';
        minusBtn.className = 'ws-qty-btn';
        minusBtn.textContent = '−';
        minusBtn.addEventListener('click', () => {
          const next = Math.max(0, +(qty - 1).toFixed(2));
          if (next <= 0) cartQty.delete(id);
          else cartQty.set(id, next);
          renderInvoicePanel();
        });

        const qtyInput = document.createElement('input');
        qtyInput.type = 'number';
        qtyInput.step = '1';
        qtyInput.min = '1';
        qtyInput.max = String(maxStock);
        qtyInput.name = `items[${id}]`;
        qtyInput.className = 'ws-qty-input';
        qtyInput.value = String(qty);
        qtyInput.addEventListener('change', () => {
          const parsed = parseFloat(qtyInput.value);
          if (!parsed || parsed <= 0) cartQty.delete(id);
          else cartQty.set(id, Math.min(parsed, maxStock));
          renderInvoicePanel();
        });

        const plusBtn = document.createElement('button');
        plusBtn.type = 'button';
        plusBtn.className = 'ws-qty-btn';
        plusBtn.textContent = '+';
        plusBtn.addEventListener('click', () => {
          if (qty < maxStock) {
            cartQty.set(id, Math.min(maxStock, +(qty + 1).toFixed(2)));
            renderInvoicePanel();
          }
        });

        stepper.appendChild(minusBtn);
        stepper.appendChild(qtyInput);
        stepper.appendChild(plusBtn);

        row.appendChild(thumb);
        row.appendChild(info);
        row.appendChild(stepper);
        invoiceList.appendChild(row);
      });

      if (itemCount === 0) {
        const empty = document.createElement('div');
        empty.className = 'ws-invoice-empty';
        empty.textContent = 'Keranjang masih kosong. Klik kartu menu warkop untuk mulai mencatat pesanan.';
        invoiceList.appendChild(empty);
      }

      if (sumQty) sumQty.textContent = String(Number(totalQty.toFixed(2)));
      updateTotals(subtotal);
      const holdBtn = document.getElementById('wsHoldBillBtn');
      if (checkoutSubmit) checkoutSubmit.disabled = itemCount === 0;
      if (holdBtn) holdBtn.disabled = itemCount === 0;

      const mobBar = document.getElementById('wsMobileCartBar');
      const mobBadge = document.getElementById('wsMobCartBadge');
      const mobQty = document.getElementById('wsMobCartQty');
      const mobTotal = document.getElementById('wsMobCartTotal');
      if (mobBar) {
        mobBar.classList.toggle('is-visible', itemCount > 0);
        if (mobBadge) mobBadge.textContent = String(Number(totalQty.toFixed(2)));
        if (mobQty) mobQty.textContent = `${Number(totalQty.toFixed(2))} menu dipilih`;
        if (mobTotal) mobTotal.textContent = formatRp(currentTotal);
      }
      if (itemCount === 0) {
        closeMobileInvoiceSheet();
      }
    }

    const mobGoBtn = document.getElementById('wsMobGoInvoice');
    const invoicePanel = document.getElementById('wsInvoicePanel');
    const closeSheetBtn = document.getElementById('wsCloseInvoiceSheet');
    const sheetBackdrop = document.getElementById('wsInvoiceBackdrop');

    function openMobileInvoiceSheet() {
      if (invoicePanel) invoicePanel.classList.add('is-mobile-open');
      if (sheetBackdrop) sheetBackdrop.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    }

    function closeMobileInvoiceSheet() {
      if (invoicePanel) invoicePanel.classList.remove('is-mobile-open');
      if (sheetBackdrop) sheetBackdrop.classList.remove('is-open');
      document.body.style.overflow = '';
    }

    if (mobGoBtn) {
      mobGoBtn.addEventListener('click', (e) => {
        e.preventDefault();
        openMobileInvoiceSheet();
      });
    }

    if (closeSheetBtn) {
      closeSheetBtn.addEventListener('click', (e) => {
        e.preventDefault();
        closeMobileInvoiceSheet();
      });
    }

    if (sheetBackdrop) {
      sheetBackdrop.addEventListener('click', closeMobileInvoiceSheet);
    }

    cards.forEach((card) => {
      card.querySelectorAll('[data-action="add-card"]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
          const maxStock = parseFloat(card.dataset.stock || '0');
          if (maxStock <= 0) return;
          const id = card.dataset.id;
          const curr = cartQty.get(id) || 0;
          if (curr < maxStock) {
            cartQty.set(id, Math.min(maxStock, curr + 1));
          }
          renderInvoicePanel();
        });
      });

      card.querySelectorAll('[data-action="toggle-card"]').forEach((trigger) => {
        trigger.addEventListener('click', (e) => {
          e.stopPropagation();
          const maxStock = parseFloat(card.dataset.stock || '0');
          if (maxStock <= 0) return;
          const id = card.dataset.id;
          if (cartQty.has(id)) cartQty.delete(id);
          else cartQty.set(id, 1);
          renderInvoicePanel();
        });
      });
    });

    // Quick cash nominal buttons (Pas, 10rb, 20rb, 50rb, 100rb)
    document.querySelectorAll('#wsQuickCashBtns .ws-qcash-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        if (!cashPaidInput) return;
        const val = btn.dataset.cash;
        cashPaidInput.value = val === 'exact' ? String(Math.round(currentTotal)) : val;
        renderInvoicePanel();
      });
    });

    if (clearBtn) {
      clearBtn.addEventListener('click', () => {
        cartQty.clear();
        renderInvoicePanel();
      });
    }

    catChips.forEach((chip) => {
      chip.addEventListener('click', () => {
        catChips.forEach((c) => c.classList.remove('is-active'));
        chip.classList.add('is-active');
        activeCat = chip.dataset.cat || 'all';
        applyDashboardFilters();
      });
    });

    tabBtns.forEach((btn) => {
      btn.addEventListener('click', () => {
        tabBtns.forEach((b) => b.classList.remove('is-active'));
        btn.classList.add('is-active');
        activeTab = btn.dataset.tab || 'all';
        applyDashboardFilters();
      });
    });

    paymentCards.forEach((btn) => {
      btn.addEventListener('click', () => {
        paymentCards.forEach((m) => m.classList.remove('is-active'));
        btn.classList.add('is-active');
        const method = btn.dataset.method || 'Tunai';
        if (paymentInput) paymentInput.value = method;
        if (cashBox) cashBox.style.display = method === 'Tunai' ? 'block' : 'none';
      });
    });

    orderTypePills.forEach((pill) => {
      pill.addEventListener('click', () => {
        orderTypePills.forEach((p) => p.classList.remove('is-active'));
        pill.classList.add('is-active');
        const otype = pill.dataset.otype || 'Dine In';
        if (orderTypeInput) orderTypeInput.value = otype;
        if (tableInput) {
          if (otype === 'Take Away' && tableInput.value.startsWith('Meja')) {
            tableInput.value = 'Bungkus';
          } else if (otype === 'Dine In' && tableInput.value === 'Bungkus') {
            tableInput.value = 'Meja 1';
          }
        }
      });
    });

    if (discountInput) discountInput.addEventListener('input', renderInvoicePanel);
    if (cashPaidInput) cashPaidInput.addEventListener('input', renderInvoicePanel);
    if (globalSearch) globalSearch.addEventListener('input', applyDashboardFilters);

    renderInvoicePanel();
    applyDashboardFilters();
  }

  // --------------------------------------------------------------------------
  // 4. PRODUCTS PAGE LIVE CATEGORY & SEARCH FILTER
  // --------------------------------------------------------------------------
  const productsTable = document.getElementById('wsProductsTable');
  const productChips = Array.from(document.querySelectorAll('#wsProductsFilterChips .ws-cat-chip'));
  if (productsTable) {
    let prodFilter = 'all';
    const rows = Array.from(productsTable.querySelectorAll('tbody tr[data-cat]'));

    function filterProductsTable() {
      const q = (globalSearch ? globalSearch.value : '').trim().toLowerCase();
      rows.forEach((row) => {
        const cat = row.dataset.cat || '';
        const isLow = row.dataset.low === '1';
        const text = row.dataset.search || '';
        let matchCat = true;
        if (prodFilter === 'low') matchCat = isLow;
        else if (prodFilter !== 'all') matchCat = cat === prodFilter;
        const matchSearch = !q || text.includes(q);
        row.style.display = matchCat && matchSearch ? '' : 'none';
      });
    }

    productChips.forEach((chip) => {
      chip.addEventListener('click', () => {
        productChips.forEach((c) => c.classList.remove('is-active'));
        chip.classList.add('is-active');
        prodFilter = chip.dataset.filter || 'all';
        filterProductsTable();
      });
    });

    if (globalSearch) {
      globalSearch.addEventListener('input', filterProductsTable);
    }
  }

  // --------------------------------------------------------------------------
  // 5. STOCK MOVEMENT LIVE SALDO SIMULATOR
  // --------------------------------------------------------------------------
  const singleTypeCards = Array.from(document.querySelectorAll('#wsSingleTypeCards .ws-method-card'));
  const singleTypeInput = document.getElementById('wsSingleType');
  const prodSelect = document.getElementById('wsProductSelect');
  const singleQtyInput = document.getElementById('wsSingleQty');

  function updateStockSimulation() {
    const simName = document.getElementById('wsSimName');
    const simBefore = document.getElementById('wsSimBefore');
    const simDelta = document.getElementById('wsSimDelta');
    const simAfter = document.getElementById('wsSimAfter');
    if (!prodSelect || !simName) return;

    const opt = prodSelect.options[prodSelect.selectedIndex];
    if (!opt || !opt.value) {
      simName.textContent = '-';
      simBefore.textContent = '-';
      simDelta.textContent = '-';
      simAfter.textContent = '-';
      return;
    }

    const stock = parseFloat(opt.dataset.stock || '0');
    const unit = opt.dataset.unit || '';
    const qty = parseFloat((singleQtyInput && singleQtyInput.value) || '0') || 0;
    const type = (singleTypeInput && singleTypeInput.value) || 'IN';
    const after = type === 'IN' ? stock + qty : stock - qty;

    simName.textContent = opt.dataset.name || '-';
    simBefore.textContent = `${stock} ${unit}`;
    simDelta.textContent = `${type === 'IN' ? '+' : '-'}${qty} ${unit}`;
    simDelta.style.color = type === 'IN' ? '#059669' : '#ec5b2a';
    simAfter.textContent = `${Number(after.toFixed(2))} ${unit}`;
    simAfter.style.color = after < 0 ? '#dc2626' : '#ec5b2a';
  }

  if (singleTypeCards.length && singleTypeInput) {
    singleTypeCards.forEach((btn) => {
      btn.addEventListener('click', () => {
        singleTypeCards.forEach((c) => c.classList.remove('is-active'));
        btn.classList.add('is-active');
        singleTypeInput.value = btn.dataset.type || 'IN';
        updateStockSimulation();
      });
    });
  }

  if (prodSelect) prodSelect.addEventListener('change', updateStockSimulation);
  if (singleQtyInput) singleQtyInput.addEventListener('input', updateStockSimulation);
  if (prodSelect) updateStockSimulation();

  // --------------------------------------------------------------------------
  // 6. HISTORY PAGE TABS (PESANAN KASIR & MUTASI STOK)
  // --------------------------------------------------------------------------
  const historyTabs = Array.from(document.querySelectorAll('#wsHistoryTabs .ws-tab-btn'));
  const ordersWrap = document.getElementById('wsOrdersSection');
  const movementsWrap = document.getElementById('wsMovementsSection');
  const ordersTable = document.getElementById('wsOrdersTable');
  const movementsTable = document.getElementById('wsHistoryTable');

  if (historyTabs.length && ordersWrap && movementsWrap) {
    let activePane = 'orders';

    function filterHistoryTables() {
      const q = (globalSearch ? globalSearch.value : '').trim().toLowerCase();
      [ordersTable, movementsTable].forEach((tbl) => {
        if (!tbl) return;
        tbl.querySelectorAll('tbody tr[data-search]').forEach((row) => {
          const match = !q || (row.dataset.search || '').includes(q);
          row.style.display = match ? '' : 'none';
        });
      });
    }

    historyTabs.forEach((tab) => {
      tab.addEventListener('click', () => {
        historyTabs.forEach((t) => t.classList.remove('is-active'));
        tab.classList.add('is-active');
        activePane = tab.dataset.pane || 'orders';
        ordersWrap.style.display = activePane === 'orders' ? '' : 'none';
        movementsWrap.style.display = activePane === 'movements' ? '' : 'none';
      });
    });

    if (globalSearch) globalSearch.addEventListener('input', filterHistoryTables);
  }

  // --------------------------------------------------------------------------
  // 7. ADMIN SALES REPORT TABS & SEARCH FILTER
  // --------------------------------------------------------------------------
  const salesTabs = Array.from(document.querySelectorAll('#wsSalesTabs .ws-tab-btn'));
  if (salesTabs.length) {
    const panes = {
      today: document.getElementById('wsPaneToday'),
      expenses: document.getElementById('wsPaneExpenses'),
      daily: document.getElementById('wsPaneDaily'),
      menu: document.getElementById('wsPaneMenu'),
      audit: document.getElementById('wsPaneAudit')
    };

    salesTabs.forEach((tab) => {
      tab.addEventListener('click', () => {
        salesTabs.forEach((t) => t.classList.remove('is-active'));
        tab.classList.add('is-active');
        const target = tab.dataset.pane || 'today';
        Object.keys(panes).forEach((k) => {
          if (panes[k]) panes[k].style.display = k === target ? '' : 'none';
        });
      });
    });

    if (globalSearch) {
      globalSearch.addEventListener('input', () => {
        const q = globalSearch.value.trim().toLowerCase();
        document.querySelectorAll('.ws-sales-tbl tbody tr[data-search]').forEach((row) => {
          row.style.display = !q || (row.dataset.search || '').includes(q) ? '' : 'none';
        });
      });
    }
  }
})();
