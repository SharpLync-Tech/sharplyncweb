{{-- 
  Page: customers/portal-standalone.blade.php
  Version: v0.1 (Safe Single-File Portal)
  Date: 13 Nov 2025
  Notes:
  - Completely isolated: all styles prefixed with .cp-*
  - Icons are hard-capped in CSS (18–22px). No “Hulk” images.
  - Desktop: logout in header (⏻). Mobile: floating logout FAB.
--}}

@php
  $firstName = optional(Auth::guard('customer')->user())->first_name ?? 'User';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>SharpLync Portal</title>

  <style>
    /* ====== SCOPE EVERYTHING ====== */
    .cp-root, .cp-root * { box-sizing: border-box; }
    .cp-root img { max-width: none !important; height: auto; } /* Prevent global img rules from stretching */

    /* ====== Base ====== */
    .cp-root {
      --navy: #0A2A4D;
      --blue: #104976;
      --teal: #2CBFAE;
      --ink: #0A2A4D;
      --card: #ffffff;
      --muted: #6b7a89;
      --shadow: 0 10px 30px rgba(0,0,0,.25);

      min-height: 100vh;
      background: linear-gradient(135deg, var(--navy) 0%, var(--blue) 40%, var(--teal) 100%);
      color: var(--ink);
      font-family: Poppins, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
    }

    /* ====== Glass Header (desktop) ====== */
    .cp-header {
      position: sticky; top: 0; z-index: 1000;
      display: flex; align-items: center; justify-content: space-between;
      padding: .75rem 1.25rem;
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      background: rgba(10, 42, 77, .55);
      border-bottom: 1px solid rgba(255,255,255,.08);
      color: #E9F4F3;
    }
    .cp-logo { display: flex; align-items: center; gap: .6rem; text-decoration: none; color: inherit; }
    .cp-logo img { height: 48px; width: auto; display: block; }
    .cp-welcome { font-weight: 600; }

    /* Header logout (desktop) */
    .cp-logout-inline form { margin: 0; }
    .cp-logout-inline button {
      display: inline-flex; align-items: center; justify-content: center;
      width: 34px; height: 34px; border-radius: 8px; cursor: pointer;
      border: 1px solid rgba(255,255,255,.28);
      background: rgba(255,255,255,.15);
      color: #fff; font-size: 16px; line-height: 1;
      transition: transform .12s ease, background .2s ease;
    }
    .cp-logout-inline button:hover { background: rgba(44,191,174,.45); transform: translateY(-1px); }

    /* ====== Page Header ====== */
    .cp-pagehead { text-align: center; color: #fff; padding: 1.25rem 1rem .5rem; }
    .cp-pagehead h2 { margin: 0; font-size: 1.25rem; text-shadow: 0 2px 4px rgba(0,0,0,.3); }

    /* ====== Main wrapper ====== */
    .cp-main { padding: 1.5rem 1rem 4rem; }

    /* ====== Card ====== */
    .cp-card {
      background: var(--card);
      border-radius: 16px;
      box-shadow: var(--shadow);
      max-width: 900px; margin: 0 auto; padding: 2rem;
    }

    /* ====== Tabs ====== */
    .cp-tabs { display: flex; flex-wrap: wrap; gap: .5rem; justify-content: center; margin-bottom: 1.25rem; }
    .cp-tabs button {
      display: inline-flex; align-items: center; gap: .5rem;
      background: #E8F2F1; border: 0; border-radius: 10px;
      padding: .6rem 1.1rem; font-weight: 600; color: var(--ink);
      cursor: pointer; transition: background .2s ease, box-shadow .2s ease;
    }
    .cp-tabs button:hover { background: #D4E9E7; }
    .cp-tabs button.cp-active {
      background: var(--ink); color: #fff;
      box-shadow: 0 0 12px rgba(44,191,174,.5);
    }
    /* Strict icon sizing: no more Hulks */
    .cp-tabs button img {
      width: 18px; height: 18px; object-fit: contain; display: inline-block;
      flex: 0 0 18px;
    }

    /* ====== Tab content ====== */
    .cp-pane { display: none; animation: cpFade .25s ease; }
    .cp-pane.cp-show { display: block; }
    .cp-pane h3 { margin: .25rem 0 .5rem; font-size: 1.25rem; }
    .cp-pane p { color: #555; line-height: 1.6; margin: 0 0 1rem; }

    /* Buttons */
    .cp-btn {
      display: inline-block; text-decoration: none; cursor: pointer;
      border: 0; border-radius: 10px; padding: .85rem 1.25rem;
      font-weight: 600; background: #104946; color: #fff;
      transition: background .2s ease, transform .1s ease;
    }
    .cp-btn:hover { background: var(--ink); transform: translateY(-1px); }

    /* Footer note in card */
    .cp-footnote { text-align: center; margin-top: 2rem; font-size: .9rem; }
    .cp-footnote .cp-hl { color: var(--teal); font-weight: 700; }

    /* ====== Floating Logout (mobile) ====== */
    .cp-logout-fab { position: fixed; right: 20px; bottom: 20px; z-index: 1100; display: none; }
    .cp-logout-fab button {
      width: 48px; height: 48px; border-radius: 50%; border: none;
      display: inline-flex; align-items: center; justify-content: center;
      background: rgba(10,42,77,.9); color: #fff; font-size: 18px; line-height: 1;
      box-shadow: 0 6px 18px rgba(0,0,0,.35);
      transition: transform .12s ease, background .2s ease;
    }
    .cp-logout-fab button:hover { transform: translateY(-1px); background: #0D375A; }

    /* Footer (site) */
    .cp-footer {
      background: var(--ink); color: #fff; text-align: center;
      padding: 1.25rem; font-size: .95rem; margin-top: 2rem;
    }
    .cp-footer .cp-hl { color: var(--teal); font-weight: 700; }

    /* ====== Responsive ====== */
    @media (max-width: 900px) {
      .cp-card { padding: 1.5rem; }
    }
    @media (max-width: 768px) {
      .cp-header { flex-direction: column; gap: .5rem; padding: 1rem; text-align: center; }
      .cp-logo img { height: 56px; }
      .cp-logout-inline { display: none; } /* hide desktop logout */
      .cp-logout-fab { display: block; }    /* show mobile FAB */
      .cp-tabs { flex-direction: column; align-items: center; }
      .cp-tabs button { width: 92%; justify-content: center; }
      .cp-tabs button img { width: 20px; height: 20px; }
    }
    @keyframes cpFade { from { opacity: 0 } to { opacity: 1 } }
  </style>
</head>
<body class="cp-root">

  <!-- Header -->
  <header class="cp-header">
    <a class="cp-logo" href="{{ route('customer.portal') }}">
      <img src="/images/sharplync-logo.png" alt="SharpLync">
    </a>

    <div style="display:flex; align-items:center; gap:.75rem;">
      <span class="cp-welcome">Welcome, {{ $firstName }}</span>

      <div class="cp-logout-inline">
        <form action="{{ route('customer.logout') }}" method="POST">
          @csrf
          <button type="submit" title="Log out">⏻</button>
        </form>
      </div>
    </div>
  </header>

  <!-- Page heading -->
  <div class="cp-pagehead"><h2>Account Portal</h2></div>

  <!-- Main -->
  <main class="cp-main">
    <div class="cp-card">

      <!-- Tabs -->
      <div class="cp-tabs" id="cpTabs">
        <button class="cp-active" data-cp-target="cp-details">
          <img src="/images/details.png" alt=""> Details
        </button>
        <button data-cp-target="cp-financial">
          <img src="/images/financial.png" alt=""> Financial
        </button>
        <button data-cp-target="cp-security">
          <img src="/images/security.png" alt=""> Security
        </button>
        <button data-cp-target="cp-documents">
          <img src="/images/documents.png" alt=""> Documents
        </button>
        <button data-cp-target="cp-support">
          <img src="/images/support.png" alt=""> Support
        </button>
      </div>

      <!-- Panes -->
      <section id="cp-details" class="cp-pane cp-show">
        <h3>Account Details v1</h3>
        <p>View and update your personal and company information.</p>
        <a href="{{ route('profile.edit') }}" class="cp-btn">Edit Profile</a>
      </section>

      <section id="cp-financial" class="cp-pane">
        <h3>Financial</h3>
        <p>Billing and payment history will appear here.</p>
      </section>

      <section id="cp-security" class="cp-pane">
        <h3>Security Settings</h3>
        <p>Manage 2FA, password, and account security preferences.</p>
      </section>

      <section id="cp-documents" class="cp-pane">
        <h3>Documents</h3>
        <p>Access invoices, quotes, and uploaded files here.</p>
      </section>

      <section id="cp-support" class="cp-pane">
        <h3>Support</h3>
        <p>Submit support tickets or chat with SharpLync support.</p>
      </section>

      <p class="cp-footnote">
        SharpLync – Old School Support, <span class="cp-hl">Modern Results</span>
      </p>
    </div>
  </main>

  <!-- Floating Logout (mobile) -->
  <form action="{{ route('customer.logout') }}" method="POST" class="cp-logout-fab">
    @csrf
    <button type="submit" title="Log out">⏻</button>
  </form>

  <!-- Site Footer -->
  <footer class="cp-footer">
    <p>© {{ date('Y') }} SharpLync Pty Ltd. All rights reserved.</p>
    <p>Old School Support, <span class="cp-hl">Modern Results</span></p>
  </footer>

  <script>
    // Tab logic (strictly within cp scope)
    (function () {
      const tabs = document.querySelectorAll('#cpTabs button');
      const panes = document.querySelectorAll('.cp-pane');

      tabs.forEach(btn => {
        btn.addEventListener('click', () => {
          const id = btn.getAttribute('data-cp-target');
          tabs.forEach(b => b.classList.remove('cp-active'));
          panes.forEach(p => p.classList.remove('cp-show'));
          btn.classList.add('cp-active');
          document.getElementById(id).classList.add('cp-show');
        });
      });
    })();
  </script>
</body>
</html>
