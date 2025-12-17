<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KYC Approved</title>
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
        .info-box {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            padding: 25px;
            border-radius: 12px;
            margin: 30px 0;
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
    </style>
</head>
<body>
<div class="email-container">
    <div class="email-header">
        <img src="{{ asset('images/logo.png') }}" alt="RWAMP Logo">
        <h1 style="color: #ffffff; margin: 15px 0 0 0; font-size: 24px;">KYC Approved</h1>
    </div>

    <div class="email-body">
        <h2 style="color: #000; margin-top: 0;">Good news, {{ $user->name }}!</h2>
        <p>Your KYC verification has been <strong>approved</strong>. You now have full access to all RWAMP features, including token purchases and withdrawals.</p>

        <div class="info-box">
            <p style="margin: 0;"><strong>ID Type:</strong> {{ strtoupper($user->kyc_id_type ?? '-') }}</p>
            <p style="margin: 8px 0 0 0;"><strong>ID Number:</strong> {{ $user->kyc_id_number ?? '-' }}</p>
            <p style="margin: 8px 0 0 0;"><strong>Approved At:</strong> {{ optional($user->kyc_approved_at)->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i') }}</p>
        </div>

        <p style="color: #666; font-size: 14px;">
            You can always review your KYC status and documents from your profile page.
        </p>

        <div style="text-align: center; margin-top: 25px;">
            <a href="{{ route('profile.show') }}" class="btn">View My Profile</a>
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

