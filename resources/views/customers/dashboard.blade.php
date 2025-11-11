<section class="portal-hero">
  <h1>Welcome back, {{ Auth::guard('customer')->user()->first_name ?? 'User' }}!</h1>
  <p>Manage your SharpLync services and account details below.</p>
  <div class="shimmer-line"></div>
</section>

<section class="portal-container">
  <div class="portal-card">

    <div class="portal-tabs">
      <button class="active" data-tab="details">🧍 Details</button>
      <button data-tab="financial">💳 Financial</button>
      <button data-tab="security">🔐 Security</button>
      <button data-tab="documents">📄 Documents</button>
      <button data-tab="support">💬 Support</button>
    </div>

    <div class="portal-content active" id="details">
      <h3>Account Details</h3>
      <p>Manage your information and preferences.</p>
      <a href="{{ route('profile.edit') }}" class="btn-primary">Edit Profile</a>
    </div>

    <div class="portal-content" id="financial">
      <h3>Financial</h3>
      <p>Check invoices, update payment details, or view history.</p>
      <a href="#" class="btn-primary">View Billing</a>
    </div>

    <div class="portal-content" id="security">
      <h3>Security</h3>
      <p>Manage passwords, 2FA, and login history.</p>
      <a href="#" class="btn-primary">Manage Security</a>
    </div>

    <div class="portal-content" id="documents">
      <h3>Documents</h3>
      <p>Access your service documents and agreements.</p>
      <a href="#" class="btn-primary">View Documents</a>
    </div>

    <div class="portal-content" id="support">
      <h3>Support</h3>
      <p>Need help? Contact our team anytime.</p>
      <a href="#" class="btn-primary">Get Support</a>
    </div>

    <form action="{{ route('customer.logout') }}" method="POST" style="margin-top:2rem;">
      @csrf
      <button type="submit" class="btn-primary w-full">Log Out</button>
    </form>

    <p class="portal-note">SharpLync – Old School Support, <span class="highlight">Modern Results</span></p>

  </div>
</section>

<script>
document.querySelectorAll('.portal-tabs button').forEach(btn=>{
  btn.addEventListener('click',()=>{
    document.querySelectorAll('.portal-tabs button').forEach(b=>b.classList.remove('active'));
    document.querySelectorAll('.portal-content').forEach(c=>c.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(btn.dataset.tab).classList.add('active');
  });
});
</script>