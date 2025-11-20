<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reseller Application Status</title>
    <style>
        body {
            font-family: 'Montserrat', 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .email-header {
            background: linear-gradient(to right, #000000, #FFD700);
            padding: 30px 20px;
            text-align: center;
        }
        .email-header img {
            max-width: 80px;
            height: auto;
            border-radius: 50%;
        }
        .email-body {
            padding: 40px 30px;
        }
        .status-box {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: #ffffff;
            padding: 25px;
            border-radius: 12px;
            margin: 30px 0;
            text-align: center;
        }
        .email-footer {
            background-color: #f9f9f9;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <img src="{{ asset('images/logo.jpeg') }}" alt="RWAMP Logo">
            <h1 style="color: #ffffff; margin: 15px 0 0 0; font-size: 24px;">Application Status Update</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <h2 style="color: #000; margin-top: 0;">Dear {{ $application->name }},</h2>
            <p>Thank you for your interest in becoming a RWAMP reseller.</p>

            <div class="status-box">
                <h3 style="color: #ffffff; margin-top: 0; margin-bottom: 10px;">Application Status: Rejected</h3>
                <p style="color: #ffffff; margin: 0;">Unfortunately, your reseller application has not been approved at this time.</p>
            </div>

            <p>We appreciate the time you took to submit your application. While we cannot proceed with your application at this moment, we encourage you to:</p>
            <ul>
                <li>Review our reseller requirements and criteria</li>
                <li>Consider reapplying in the future if your circumstances change</li>
                <li>Contact our support team if you have any questions</li>
            </ul>

            <p>If you have any questions about this decision, please don't hesitate to contact our support team.</p>

            <p>Thank you for your understanding.</p>

            <p style="margin-top: 30px;">
                Best regards,<br>
                <strong>The RWAMP Team</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} RWAMP. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

