<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome to SharpLync</title>
</head>
<body style="margin:0;padding:0;overflow-x:hidden;background-color:#0A2A4D;font-family:'Inter','Open Sans',system-ui,sans-serif;">

  <!-- Hero Section -->
  <div style="
      position:relative;
      width:100vw;
      height:110vh;
      overflow:hidden;
      background-image:url('{{ asset('images/hero-bg.jpg') }}');
      background-size:cover;
      background-position:center;
      background-repeat:no-repeat;
      background-attachment:fixed;
      filter:brightness(1.1) contrast(1.05) saturate(1.05);
  ">

    <!-- SharpLync Logo (top-left floating) -->
    <img src="{{ asset('images/sharplync-logo.png') }}" 
         alt="SharpLync Logo" 
         style="
            position:absolute;
            top:70px;
            left:70px;
            height:85px;
            width:auto;
            z-index:3;
            filter:drop-shadow(0 0 8px rgba(0,0,0,0.5));
         ">

    <!-- Overlay with Text and Buttons -->
    <div style="
        position:absolute;
        bottom:0;
        left:0;
        width:100%;
        height:100%;
        background:rgba(10,42,77,0.25);
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:flex-end;
        padding-bottom:10vh;
        text-align:center;
        color:#fff;
        z-index:2;
    ">

        <!-- Headline -->
        <h1 style="
            font-size:clamp(2rem,4vw,3.5rem);
            font-weight:600;
            margin-bottom:1rem;
            text-shadow:0 3px 10px rgba(0,0,0,0.8);
            color:#ffffff;
        ">
            Your Personal Tech Link — Backed by Experience.
        </h1>

        <!-- Subheadline -->
        <p style="
            font-size:clamp(1rem,2vw,1.3rem);
            margin-bottom:2rem;
            color:#ffffff;
            text-shadow:0 2px 6px rgba(0,0,0,0.6);
            max-width:750px;
        ">
            Helping Australians stay connected, secure, and productive.
        </p>

        <!-- Buttons -->
        <div style="display:flex;gap:1rem;flex-wrap:wrap;justify-content:center;">
          <a href="{{ url('/support') }}" style="
              background-color:#2CBFAE;
              color:#fff;
              padding:12px 28px;
              border-radius:6px;
              font-weight:600;
              text-decoration:none;
              transition:all 0.3s ease;
          " onmouseover="this.style.backgroundColor='#25a997'"
            onmouseout="this.style.backgroundColor='#2CBFAE'">
            Get Support
          </a>

          <a href="{{ url('/services') }}" style="
              border:2px solid #fff;
              color:#fff;
              padding:12px 28px;
              border-radius:6px;
              font-weight:600;
              text-decoration:none;
              transition:all 0.3s ease;
          " onmouseover="this.style.backgroundColor='#fff';this.style.color='#0A2A4D';"
            onmouseout="this.style.backgroundColor='transparent';this.style.color='#fff';">
            Explore Services
          </a>
        </div>
    </div>
  </div>

  <!-- Wave Divider -->
  <div style="width:100%;overflow:hidden;line-height:0;position:relative;background-color:transparent;">
    <svg viewBox='0 0 1440 320' preserveAspectRatio='none' style='display:block;width:100%;height:120px;'>
      <path fill='#f8f9fb' fill-opacity='1'
            d='M0,256L48,224C96,192,192,128,288,122.7C384,117,480,171,576,202.7C672,235,768,245,864,234.7C960,224,1056,192,1152,186.7C1248,181,1344,203,1392,213.3L1440,224L1440,320L0,320Z'>
      </path>
    </svg>
  </div>

</body>
</html>