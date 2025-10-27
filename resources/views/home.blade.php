<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SharpLync | Home</title>
  <link rel="stylesheet" href="/public/css/app.css">
  <style>
    body {
      margin: 0;
      font-family: 'Comfortaa', sans-serif;
      background-color: #f8f9fa;
      color: #0A2A4D;
      padding-top: 70px; /* Offset for fixed header */
      padding-bottom: 60px; /* Offset for fixed footer */
    }

    header {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      background-color: #0A2A4D;
      color: white;
      text-align: center;
      padding: 20px 0;
      font-size: 1.4rem;
      font-weight: bold;
      z-index: 1000;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    footer {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      background-color: #0A2A4D;
      color: white;
      text-align: center;
      padding: 15px 10%;
      font-size: 0.9rem;
      box-shadow: 0 -2px 5px rgba(0,0,0,0.2);
    }

    /* Hero Section */
    .hero {
      height: 80vh;
      background: linear-gradient(rgba(10,42,77,0.6), rgba(10,42,77,0.6)), url('/storage/videos/hero-bg.jpg') center/cover no-repeat;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      color: white;
    }

    .hero h1 {
      font-size: 3rem;
      margin-bottom: 10px;
    }

    .hero p {
      font-size: 1.2rem;
      max-width: 600px;
    }

    .hero .buttons {
      margin-top: 25px;
    }

    .hero button {
      background-color: #104946;
      color: white;
      border: none;
      padding: 12px 28px;
      border-radius: 50px;
      cursor: pointer;
      margin: 5px;
      font-size: 1rem;
      transition: background 0.3s;
    }

    .hero button:hover {
      background-color: #0A2A4D;
    }

    /* Services Grid */
    .services {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      padding: 60px 10%;
      background-color: white;
    }

    .service-tile {
      background: #f0f4f7;
      border-radius: 16px;
      padding: 30px;
      text-align: center;
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .service-tile:hover {
      transform: translateY(-6px);
      box-shadow: 0 6px 15px rgba(0,0,0,0.1);
    }

    .service-tile i {
      font-size: 2.5rem;
      margin-bottom: 15px;
      color: #104946;
    }

    .service-tile h3 {
      margin-bottom: 10px;
      color: #0A2A4D;
    }

    .about {
      text-align: center;
      padding: 60px 10%;
      background-color: #f8f9fa;
    }

    .about p {
      max-width: 800px;
      margin: auto;
      line-height: 1.6;
    }

    .cta {
      background-color: #104946;
      color: white;
      text-align: center;
      padding: 60px 10%;
    }

    .cta button {
      background-color: white;
      color: #104946;
      border: none;
      padding: 12px 30px;
      border-radius: 50px;
      cursor: pointer;
      font-weight: bold;
    }
  </style>
</head>
<body>

  <header>
    SharpLync
  </header>

  <section class="hero">
    <h1>Welcome to SharpLync</h1>
    <p>Old School Support. Modern Results.</p>
    <div class="buttons">
      <button onclick="location.href='#services'">View Our Services</button>
      <button onclick="location.href='/safecheck'">Free Scam Check</button>
    </div>
  </section>

  <section id="services" class="services">
    <div class="service-tile">
      <i class="fa-solid fa-laptop"></i>
      <h3>IT Support & Cloud</h3>
      <p>Reliable solutions that just work, so you can focus on business.</p>
    </div>
    <div class="service-tile">
      <i class="fa-solid fa-robot"></i>
      <h3>AI & Automation</h3>
      <p>Smarter systems for a smoother workflow.</p>
    </div>
    <div class="service-tile">
      <i class="fa-solid fa-lock"></i>
      <h3>Security & Backup</h3>
      <p>Protecting your data, devices, and reputation from every angle.</p>
    </div>
    <div class="service-tile">
      <i class="fa-solid fa-chart-line"></i>
      <h3>Xero Integration</h3>
      <p>Connect your business tools seamlessly and securely.</p>
    </div>
    <div class="service-tile">
      <i class="fa-solid fa-network-wired"></i>
      <h3>Infrastructure Design</h3>
      <p>From Wi-Fi to full-scale networks, we build it right.</p>
    </div>
    <div class="service-tile">
      <i class="fa-solid fa-shield"></i>
      <h3>SharpLync SafeCheck</h3>
      <p>Free scam and phishing email checks, powered by AI.</p>
    </div>
  </section>

  <section class="about">
    <h2>Our Philosophy</h2>
    <p>We believe in keeping things personal. SharpLync blends dependable, old-school service with cutting-edge technology — so you get modern results, delivered with real human support.</p>
  </section>

  <section class="cta">
    <h2>Need Help or Want to Chat?</h2>
    <button onclick="location.href='/contact'">Book a Free Consult</button>
  </section>

  <footer>
    <p>&copy; 2025 SharpLync. All rights reserved.</p>
  </footer>

</body>
</html>
