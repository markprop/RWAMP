<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Email Verification Code') }}</title>
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
        .otp-code {
            background: linear-gradient(135deg, #E30613 0%, #c0050f 100%);
            color: #ffffff;
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 8px;
            text-align: center;
            padding: 20px;
            border-radius: 12px;
            margin: 30px 0;
            font-family: 'JetBrains Mono', monospace;
        }
        .email-footer {
            background-color: #f9f9f9;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #e0e0e0;
        }
        .warning-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #E30613;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <img src="{{ asset('images/logo.png') }}" alt="RWAMP Logo">
            <h1 style="color: #ffffff; margin: 15px 0 0 0; font-size: 24px;">{{ __('Email Verification') }}</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <h2 style="color: #000; margin-top: 0;">{{ __('Hello!') }}</h2>
            <p>{{ __('Thank you for registering with RWAMP. To complete your registration, please verify your email address using the code below:') }}</p>

            <div class="otp-code">
                {{ $formattedOtp }}
            </div>

            <p style="text-align: center; font-size: 14px; color: #666;">
                <strong>{{ __('This code expires in 10 minutes.') }}</strong>
            </p>

            <div class="warning-box">
                <strong>⚠️ {{ __('Security Notice') }}</strong>
                <p style="margin: 5px 0 0 0;">{{ __('Do not share this code with anyone. RWAMP will never ask for your verification code.') }}</p>
            </div>

            <p style="margin-top: 30px; color: #666; font-size: 14px;">
                {{ __('If you did not request this code, please ignore this email or contact our support team.') }}
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p style="margin: 0 0 10px 0;">
                <strong>RWAMP</strong> – {{ __('The Currency of Real Estate Investments') }}
            </p>
            <p style="margin: 0; color: #999;">
                {{ __('This is an automated message. Please do not reply to this email.') }}
            </p>
        </div>
    </div>
</body>
</html>

