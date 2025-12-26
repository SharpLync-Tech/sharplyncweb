<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to SharpFleet</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2CBFAE; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .button { display: inline-block; background: #2CBFAE; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
        .features { margin: 20px 0; }
        .features ul { list-style: none; padding: 0; }
        .features li { padding: 8px 0; padding-left: 20px; position: relative; }
        .features li:before { content: "✓"; color: #2CBFAE; font-weight: bold; position: absolute; left: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to SharpFleet!</h1>
            <p>Your 30-day free trial has started</p>
        </div>

        <div class="content">
            <h2>Hi {{ $firstName }},</h2>

            <p>Welcome to SharpFleet! Your account has been successfully activated and your 30-day free trial is now active.</p>

            <p>Here's what you can do to get started:</p>

            <div class="features">
                <h3>🚗 Set up your fleet</h3>
                <ul>
                    <li>Add your vehicles to the system</li>
                    <li>Configure vehicle types and details</li>
                    <li>Set up maintenance schedules</li>
                </ul>

                <h3>👥 Manage your team</h3>
                <ul>
                    <li>Add drivers and staff members</li>
                    <li>Assign roles and permissions</li>
                    <li>Set up user profiles</li>
                </ul>

                <h3>📊 Start tracking</h3>
                <ul>
                    <li>Log trips and mileage</li>
                    <li>Record fuel consumption</li>
                    <li>Track maintenance and repairs</li>
                </ul>
            </div>

            <div style="text-align: center;">
                <a href="{{ url('/app/sharpfleet/admin') }}" class="button">Access Your Dashboard</a>
            </div>

            <p><strong>Need help getting started?</strong></p>
            <p>Check out our <a href="#">quick start guide</a> or contact our support team at <a href="mailto:support@sharplync.com.au">support@sharplync.com.au</a>.</p>

            <p>We're here to help you streamline your fleet operations and boost your business efficiency.</p>

            <p>Best regards,<br>The SharpFleet Team</p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} SharpFleet. All rights reserved.</p>
            <p>This email was sent to you because you registered for a SharpFleet account.</p>
        </div>
    </div>
</body>
</html>