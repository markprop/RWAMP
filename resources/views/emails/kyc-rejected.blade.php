<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KYC Rejected</title>
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
        .reason-box {
            background-color: #fef2f2;
            border-left: 4px solid #dc2626;
            padding: 20px;
            margin: 25px 0;
            border-radius: 8px;
        }
        .email-footer {
            background-color: #f9f9f9;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #e0e0e0;
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
            font-size: 15px;
        }
        .btn:hover {
            background-color: #c0050f;
        }
        .reason-text {
            white-space: pre-line;
            font-size: 14px;
            color: #991b1b;
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="email-header">
        <img src="{{ asset('images/logo.png') }}" alt="RWAMP Logo">
        <h1 style="color: #ffffff; margin: 15px 0 0 0; font-size: 24px;">KYC Rejected</h1>
    </div>

    <div class="email-body">
        <h2 style="color: #000; margin-top: 0;">Hello {{ $user->name }},</h2>
        <p>We’ve reviewed your KYC submission, but unfortunately it could not be approved at this time.</p>

        <div class="reason-box">
            <p style="margin: 0 0 8px 0; font-weight: bold; color: #b91c1c;">Reason for Rejection:</p>
            <p class="reason-text">{{ $reason }}</p>
        </div>

        <p style="color: #666; font-size: 14px;">
            Please correct the above issue(s) and submit your KYC details again. Make sure the information on your ID
            matches the details you enter on RWAMP, and that all uploaded images are clear and readable.
        </p>

        <div style="text-align: center; margin-top: 25px;">
            <a href="{{ route('kyc.show') }}" class="btn">Resubmit KYC</a>
        </div>
    </div>

    <div class="email-footer">
        <p style="margin: 0 0 10px 0;">
            <strong>RWAMP</strong> – The Currency of Real Estate Investments
        </p>
        <p style="margin: 0; color: #999;">
            This is an automated message. Please do not reply to this email.
        </p>
    </div>
</div>
</body>
</html>

