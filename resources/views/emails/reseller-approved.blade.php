<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reseller Account Approved</title>
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
        .credentials-box {
            background: linear-gradient(135deg, #E30613 0%, #c0050f 100%);
            color: #ffffff;
            padding: 25px;
            border-radius: 12px;
            margin: 30px 0;
        }
        .credential-item {
            margin: 15px 0;
            font-size: 16px;
        }
        .credential-label {
            font-weight: bold;
            display: inline-block;
            min-width: 140px;
        }
        .credential-value {
            font-family: 'JetBrains Mono', monospace;
            font-weight: bold;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 5px 10px;
            border-radius: 4px;
        }
        .password-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
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
            padding: 14px 28px;
            background-color: #E30613;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin-top: 20px;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #c0050f;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <img src="{{ asset('images/logo.jpeg') }}" alt="RWAMP Logo">
            <h1 style="color: #ffffff; margin: 15px 0 0 0; font-size: 24px;">Reseller Account Approved</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <h2 style="color: #000; margin-top: 0;">Congratulations, {{ $user->name }}!</h2>
            <p>Your RWAMP reseller application has been <strong>approved</strong>. You can now access your reseller dashboard and start selling RWAMP tokens to your network.</p>

            <div class="credentials-box">
                <h3 style="color: #ffffff; margin-top: 0; margin-bottom: 20px;">Your Login Credentials</h3>
                <div class="credential-item">
                    <span class="credential-label">Email:</span>
                    <span class="credential-value">{{ $user->email }}</span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Temporary Password:</span>
                    <span class="credential-value">{{ $defaultPassword }}</span>
                </div>
            </div>

            <div class="password-box">
                <strong>🔒 Security Notice</strong>
                <p style="margin: 10px 0 0 0;">For your security, you <strong>must change your password</strong> immediately after your first login. The system will automatically prompt you to set a new password.</p>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $loginUrl }}" class="btn">Login to Your Dashboard</a>
            </div>

            <h3 style="color: #000; margin-top: 30px;">What's Next?</h3>
            <ul style="color: #666; line-height: 1.8;">
                <li>Log in using the credentials above</li>
                <li>Change your password when prompted</li>
                <li>Explore your reseller dashboard</li>
                <li>Start selling RWAMP tokens to your network</li>
                <li>Earn commissions on direct purchases</li>
            </ul>

            <p style="margin-top: 30px; color: #666; font-size: 14px;">
                If you have any questions or need assistance, please don't hesitate to contact our support team.
            </p>
        </div>

        <!-- Footer -->
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

