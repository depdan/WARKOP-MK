<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="ws-auth-card">
  <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;">
    <svg width="34" height="34" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <circle cx="16" cy="16" r="14" fill="#2563eb"/>
      <path d="M16 2A14 14 0 0 1 30 16H16V2Z" fill="#93c5fd"/>
      <circle cx="16" cy="16" r="5" fill="#FFFFFF"/>
    </svg>
    <span style="font-size:21px;font-weight:800;letter-spacing:-0.03em;color:#2563eb;">WARKOP <span style="color:#1f2937;">MK</span></span>
  </div>

  <h2 style="font-size:20px;font-weight:800;margin:0 0 4px;">Masuk ke Kasir Warkop</h2>
  <p style="font-size:13px;color:#6b7280;margin:0 0 22px;">Catat pesanan kopi, indomie, gorengan, terima bayar &amp; cetak struk pelanggan.</p>

  <form method="post" id="wsLoginForm">
    <input type="hidden" name="_csrf" value="<?= e(csrfToken()) ?>">

    <div style="margin-bottom:14px;">
      <label class="ws-form-label" for="loginEmail">Email</label>
      <input type="email" id="loginEmail" name="email" class="ws-input" placeholder="user@warkop.local" required autofocus>
    </div>

    <div style="margin-bottom:18px;">
      <label class="ws-form-label" for="loginPassword">Password</label>
      <input type="password" id="loginPassword" name="password" class="ws-input" placeholder="Masukkan password" required>
    </div>

    <button type="submit" class="ws-cta-full">Masuk ke Kasir</button>
  </form>

  <div style="margin-top:20px;padding-top:16px;border-top:1px dashed #e2e4ea;">
    <div style="font-size:11.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.04em;">Akun Demo Cepat</div>
    <div class="ws-demo-grid">
      <button type="button" class="ws-demo-btn" onclick="fillDemoLogin('user@warkop.local','password')">
        <div style="font-size:12px;font-weight:700;color:#1f2937;">Kasir Warkop</div>
        <div style="font-size:11px;color:#6b7280;">user@warkop.local</div>
      </button>
      <button type="button" class="ws-demo-btn" onclick="fillDemoLogin('admin@warkop.local','password')">
        <div style="font-size:12px;font-weight:700;color:#1f2937;">Owner / Admin</div>
        <div style="font-size:11px;color:#6b7280;">admin@warkop.local</div>
      </button>
    </div>
  </div>
</div>

<script>
function fillDemoLogin(email, pass) {
  document.getElementById('loginEmail').value = email;
  document.getElementById('loginPassword').value = pass;
}
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
