<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SharpLync Admin | Sign in</title>
  <link href="{{ asset('css/admin/sharplync-admin.css') }}?v=1.0" rel="stylesheet">
  <style>
    /* Scoped tweaks just for this page */
    .login-wrap{
      min-height: 100vh; display:flex; align-items:center; justify-content:center;
      background: linear-gradient(135deg, #0A2A4D 0%, #104976 40%, #2CBFAE 100%);
      padding: 24px;
    }
    .login-card{
      width: 100%; max-width: 480px;
      background:#fff; border-radius: 14px;
      box-shadow: 0 10px 30px rgba(10,42,77,.25);
      padding: 28px;
    }
    .login-card h1{
      margin:0 0 4px 0; font-size: 22px; color:#0A2A4D;
      display:flex; align-items:center; gap:8px;
    }
    .login-card p{ color:#3a4a60; margin:0 0 18px 0; }
    .ms-btn{
      width:100%; display:flex; align-items:center; justify-content:center; gap:10px;
      border:0; border-radius:10px; padding:12px 16px; font-weight:700; cursor:pointer;
      background:#0A2A4D; color:#fff; transition:transform .15s ease, box-shadow .15s ease;
    }
    .ms-btn:hover{ transform: translateY(-1px); box-shadow:0 6px 16px rgba(10,42,77,.25); }
    .link-row{
      margin-top:14px; display:flex; justify-content:space-between; font-size:.95rem;
    }
    .link-row a{ color:#104976; text-decoration:none; }
    .link-row a:hover{ text-decoration:underline; }
    .as-badge{
      display:inline-flex; align-items:center; gap:6px; font-size:.9rem; color:#0A2A4D;
      background:#F0F6FA; border:1px solid #D7E6F2; padding:6px 10px; border-radius:8px;
    }
    .center { text-align:center; }
    .mt-2 { margin-top: 18px; }
  </style>
</head>
<body>
  <div class="login-wrap">
    <div class="login-card">
      <h1>⚡ SharpLync Admin</h1>
      <p class="center">Sign in securely with Microsoft 365 (Entra ID).</p>

      @if(session('admin_user'))
        <div class="as-badge">
          <span>Signed in as:</span>
          <strong>{{ session('admin_user.displayName') ?? session('admin_user')['displayName'] ?? 'SharpLync Admin' }}</strong>
        </div>
        <div class="mt-2">
          <a href="{{ url('/admin/dashboard') }}" class="btn btn-primary" style="display:inline-block;padding:10px 14px;border-radius:8px;color:#fff;background:#104976;text-decoration:none;">Go to Dashboard</a>
          <a href="{{ url('/admin/logout') }}" class="btn" style="display:inline-block;padding:10px 14px;border-radius:8px;margin-left:8px;color:#fff;background:#b40000;text-decoration:none;">Logout</a>
        </div>
      @else
        <form action="{{ url('/admin/login') }}" method="GET" class="mt-2">
          @csrf
          <button type="submit" class="ms-btn" aria-label="Sign in with Microsoft">
            {{-- Simple Microsoft "window" icon (SVG) --}}
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <rect x="3" y="3" width="8" height="8" fill="#F35325"/>
              <rect x="13" y="3" width="8" height="8" fill="#81BC06"/>
              <rect x="3" y="13" width="8" height="8" fill="#05A6F0"/>
              <rect x="13" y="13" width="8" height="8" fill="#FFBA08"/>
            </svg>
            Sign in with Microsoft 365
          </button>
        </form>
        <div class="link-row">
          <a href="{{ url('/') }}">← Back to website</a>
          <a href="{{ url('/admin/help') }}">Need help?</a>
        </div>
      @endif
    </div>
  </div>
</body>
</html>