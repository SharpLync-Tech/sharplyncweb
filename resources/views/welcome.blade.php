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

    <!-- Tagline + Button (to the right side) -->
    <div style="
        position:absolute;
        top:50%;
        right:10%;
        transform:translateY(-50%);
        text-align:left;
        color:#ffffff;
        z-index:3;
    ">

        <!-- Tagline -->
        <p style="
            font-size:clamp(1.2rem,2.2vw,1.6rem);
            font-weight:400;
            margin-bottom:2rem;
            text-shadow:0 2px 8px rgba(0,0,0,0.7);
            max-width:420px;
            line-height:1.5;
        ">
          Helping you stay connected, secure, and productive.
        </p>

        <!-- Glowing Power Button -->
        <a href="{{ url('/home') }}" 
           style="
              display:inline-block;
              background:linear-gradient(90deg, #2CBFAE, #1AA38F);
              color:#fff;
              font-weight:700;
              font-size:1.1rem;
              padding:18px 54px;
              border-radius:8px;
              text-decoration:none;
              letter-spacing:0.5px;
              box-shadow:0 0 15px rgba(44,191,174,0.6);
              transition:transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
           "
           onmouseover="this.style.transform='scale(1.08)';this.style.boxShadow='0 0 30px rgba(44,191,174,0.9)';this.style.background='linear-gradient(90deg, #32d9c5, #1CB39B)';"
           onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 0 15px rgba(44,191,174,0.6)';this.style.background='linear-gradient(90deg, #2CBFAE, #1AA38F)';">
           Explore SharpLync
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