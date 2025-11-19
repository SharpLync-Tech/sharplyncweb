<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Services Mock Clean</title>

<style>
    body {
        margin: 0;
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #0A2A4D 0%, #104976 40%, #2CBFAE 100%);
        color: white;
    }

    .mock-wrapper {
        max-width: 1200px;
        margin: 4rem auto;
        background: #0A2A4D;
        border-radius: 18px;
        padding: 3rem;
        box-shadow: 0 12px 32px rgba(0,0,0,0.35);
    }

    /* -------------------------------------------------------
       HEADER aligned to the RIGHT COLUMN ONLY
    ---------------------------------------------------------*/
    .mock-header {
        text-align: center;
        width: 100%;
        max-width: 600px;        /* keeps header aligned to text width */
        margin-left: calc(420px + 3rem); /* pushes header to align above text */
    }

    .mock-header img {
        width: 70px;
        /* stronger glow */
        filter:
            drop-shadow(0 0 4px rgba(44,191,174,1))
            drop-shadow(0 0 16px rgba(44,191,174,0.7));
        margin-bottom: 1rem;
    }

    .mock-header h2 {
        margin: 0;
        font-size: 1.7rem;
        font-weight: 600;
    }

    .mock-header p {
        margin-top: .3rem;
        opacity: 0.85;
        font-size: 0.95rem;
    }

    /* -------------------------------------------------------
       DIVIDER — matches width of text column only
    ---------------------------------------------------------*/
    .mock-divider {
        width: 600px;  /* matches .mock-header max-width */
        margin-left: calc(420px + 3rem);
        border: none;
        border-top: 1px solid rgba(255,255,255,0.18);
        margin-top: 2rem;
        margin-bottom: 2rem;
    }

    /* -------------------------------------------------------
       GRID LAYOUT
    ---------------------------------------------------------*/
    .mock-content {
        display: grid;
        grid-template-columns: 420px 1fr;
        gap: 3rem;
        align-items: flex-start;
    }

    .mock-image img {
        width: 100%;
        border-radius: 14px;
        object-fit: cover;
        box-shadow: 0 8px 25px rgba(0,0,0,0.35);
        margin-top: -13rem; /* keep your original offset */
    }

    .mock-text {
        max-width: 650px;
    }

    .mock-text p {
        margin-bottom: 1.5rem;
        line-height: 1.55;
        opacity: 0.95;
    }

    .mock-text h4 {
        /* more breathing room above and below */
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-size: 1.2rem;
        font-weight: 600;
        text-align: center;
    }

    .mock-text ul {
        margin: 0;
        padding-left: 0;          /* remove default indent */
        list-style: none;         /* remove default bullets */
    }

    .mock-text li {
        margin-bottom: .6rem;
        position: relative;
        padding-left: 1.4rem;     /* space for custom icon */
    }

    .mock-text li::before {
        content: "➜";             /* custom arrow bullet */
        position: absolute;
        left: 0;
        top: 0;
        color: #2CBFAE;           /* SharpLync teal */
        font-weight: 700;
    }

    /* -------------------------------------------------------
       MOBILE
    ---------------------------------------------------------*/
    @media (max-width: 768px) {

        .mock-wrapper {
            padding: 2rem;
        }

        .mock-header,
        .mock-divider {
            margin-left: 0;
            max-width: 100%;
            width: 100%;
        }

        .mock-content {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .mock-text ul {
            text-align: left;
            display: inline-block;
            margin: 0 auto;
        }

        .mock-image img {
            margin-top: 0;
        }
    }
</style>

</head>

<body>

<div class="mock-wrapper">

    <!-- HEADER ON TOP RIGHT -->
    <div class="mock-header">
        <img src="/images/support.png" alt="">
        <h2>Remote Support</h2>
        <p>Instant help wherever you are.</p>
    </div>

    <hr class="mock-divider">

    <!-- IMAGE LEFT, CONTENT RIGHT -->
    <div class="mock-content">

        <div class="mock-image">
            <img src="/images/remote_support.png" alt="">
        </div>

        <div class="mock-text">

            <p>
                Fast, friendly remote support to keep your people working — without waiting days 
                for someone to show up. Screensharing, quick fixes, and real humans on the other end.
            </p>

            <h4>Included Services</h4>
            <ul>
                <li>Remote troubleshooting & fixes</li>
                <li>Application and OS support</li>
                <li>Printer, email & access issues</li>
            </ul>
        </div>

    </div>

</div>

</body>
</html>
