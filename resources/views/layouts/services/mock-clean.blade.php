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

    /* HEADER */
    .mock-header {
        text-align: center;
        margin-bottom: 2.5rem;
    }

    .mock-header img {
        width: 70px;
        filter: drop-shadow(0 0 8px rgba(44,191,174,0.9));
        margin-bottom: 1rem;
    }

    .mock-header h2 {
        margin: 0;
        font-size: 1.7rem;
        font-weight: 600;
    }

    .mock-header p {
        margin-top: .3rem;
        opacity: 0.8;
    }

    .mock-divider {
        border: none;
        border-top: 1px solid rgba(255,255,255,0.18);
        margin: 2rem 0;
    }

    /* GRID */
    .mock-content {
        display: flex;
        gap: 2.5rem;
        align-items: flex-start;
    }

    .mock-image {
        flex: 0 0 420px;
    }

    .mock-image img {
        width: 100%;
        border-radius: 14px;
        object-fit: cover;
        box-shadow: 0 8px 25px rgba(0,0,0,0.35);
    }

    .mock-text {
        flex: 1;
        max-width: 650px;
    }

    .mock-text p {
        margin-bottom: 1.5rem;
        line-height: 1.55;
        opacity: 0.95;
    }

    .mock-text h4 {
        margin-bottom: .5rem;
        font-size: 1.2rem;
        font-weight: 600;
    }

    .mock-text ul {
        margin: 0;
        padding-left: 1.2rem;
    }

    .mock-text li {
        margin-bottom: .4rem;
    }

    /* MOBILE */
    @media (max-width: 768px) {
        .mock-content {
            flex-direction: column;
            text-align: center;
        }

        .mock-image {
            width: 100%;
        }

        .mock-text ul {
            text-align: left;
            margin: 0 auto;
            display: inline-block;
        }
    }

</style>
</head>

<body>

<div class="mock-wrapper">

    <div class="mock-header">
        <img src="/images/support.png" alt="">
        <h2>Remote Support</h2>
        <p>Instant help wherever you are.</p>
    </div>

    <hr class="mock-divider">

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
