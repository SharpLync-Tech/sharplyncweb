@extends('layouts.base')

@section('title', 'Registration Log | SharpLync Admin')

@section('content')
<section class="content-card fade-in" style="max-width: 950px; margin: 40px auto;">
  <h2>🪵 Registration Log Viewer</h2>
  <p style="color: #555;">View or filter the latest entries from <code>registration.log</code></p>

  @if(session('status'))
    <div style="background:#D1FAE5;color:#065F46;padding:10px;border-radius:6px;margin-bottom:15px;">
      {{ session('status') }}
    </div>
  @endif

  <!-- Search bar -->
  <input id="logSearch" type="text"
         placeholder="Search logs..."
         style="width:100%;padding:10px;border-radius:6px;border:1px solid #ccc;margin-bottom:15px;">

  <!-- Log display -->
  <div id="logContainer"
       style="background:#0A2A4D;color:#00FFB9;font-family:'Courier New',monospace;
              font-size:14px;line-height:1.4;padding:20px;border-radius:8px;
              overflow-y:auto;height:600px;white-space:pre-wrap;
              box-shadow:inset 0 0 10px rgba(0,0,0,0.5);">
    @forelse($logLines as $line)
      <div class="log-line">{{ $line }}</div>
    @empty
      <p>No log entries found.</p>
    @endforelse
  </div>

  <!-- Buttons -->
  <div style="margin-top:20px;display:flex;gap:10px;">
    <a href="{{ route('admin.registration.log') }}"
       style="background:#104946;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;">
       🔄 Refresh
    </a>

    <form method="POST" action="{{ route('admin.registration.log.clear') }}"
          onsubmit="return confirm('Are you sure you want to clear the registration log?');">
      @csrf
      <button type="submit"
              style="background:#B91C1C;color:#fff;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;">
        🧹 Clear Log
      </button>
    </form>
  </div>
</section>

<!-- 🔍 Search script -->
<script>
document.getElementById('logSearch').addEventListener('input', function() {
  const term = this.value.toLowerCase();
  document.querySelectorAll('#logContainer .log-line').forEach(line => {
    line.style.display = line.textContent.toLowerCase().includes(term) ? '' : 'none';
  });
});
</script>
@endsection