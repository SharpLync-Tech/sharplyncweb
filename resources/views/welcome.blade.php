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

    <!-- SharpLync Logo (top-left fixed position) -->
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

    <!-- Tagline (moved upward into the sky area) -->
    <p style="
        position:absolute;
        top:26%;
        right:10%;
        transform:translateY(-50%);
        color:#ffffff;
        font-size:clamp(1.2rem,2.2vw,1.6rem);
        font-weight:400;
        text-shadow:0 2px 8px rgba(0,0,0,0.7);
        z-index:3;
        max-width:420px;
        line-height:1.5;
        text-align:left;
    ">
      Helping you stay connected, secure, and productive.
    </p>

    <!-- Outlined Button (restored elegant style) -->
    <div style="
        position:absolute;
        bottom:10vh;
        left:50%;
        transform:translateX(-50%);
        text-align:center;
        z-index:3;
    ">
      <a href="{{ url('/home') }}" 
         style="
            display:inline-block;
            border:2px solid #ffffff;
            color:#ffffff;
            font-weight:700;
            font-size:1.1rem;
            padding:14px 46px;
            border-radius:6px;
            text-decoration:none;
            letter-spacing:0.5px;
            transition:all 0.3s ease;
            background:transparent;
         "
         onmouseover="this.style.backgroundColor='#ffffff';this.style.color='#0A2A4D';this.style.transform='scale(1.05)';"
         onmouseout="this.style.backgroundColor='transparent';this.style.color='#ffffff';this.style.transform='scale(1)';">
         Explore Services
      </a>
    </div>

    <!-- Subtle Overlay -->
    <div style="
        position:absolute;
        top:0;
        left:0;
        width:100%;
        height:100%;
        background:rgba(10,42,77,0.25);
        z-index:2;
    "></div>
  </div>

</body>
</html>