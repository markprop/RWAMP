<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Withdrawal Request Updated</title>
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
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #ffffff;
            padding: 25px;
            border-radius: 12px;
            margin: 30px 0;
        }
        .info-item {
            margin: 15px 0;
            font-size: 16px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            min-width: 140px;
        }
        .info-value {
            font-family: 'JetBrains Mono', monospace;
            font-weight: bold;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 5px 10px;
            border-radius: 4px;
        }
        .changes-box {
            background-color: #dbeafe;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .notice-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
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
            <img src="{{ asset('images/logo.png') }}" alt="RWAMP Logo">
            <h1 style="color: #ffffff; margin: 15px 0 0 0; font-size: 24px;">Withdrawal Request Updated</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <h2 style="color: #000; margin-top: 0;">Dear {{ $user->name }},</h2>
            <p>Your withdrawal request has been <strong>updated</strong> by the admin. Please review the changes below.</p>

            <div class="info-box">
                <h3 style="color: #ffffff; margin-top: 0; margin-bottom: 20px;">Updated Withdrawal Details</h3>
                <div class="info-item">
                    <span class="info-label">Request ID:</span>
                    <span class="info-value">#{{ $withdrawal->id }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Current Amount:</span>
                    <span class="info-value">{{ number_format($withdrawal->token_amount, 2) }} RWAMP</span>
                </div>
                @if($oldAmount != $newAmount)
                <div class="info-item">
                    <span class="info-label">Previous Amount:</span>
                    <span class="info-value">{{ number_format($oldAmount, 2) }} RWAMP</span>
                </div>
                @endif
                <div class="info-item">
                    <span class="info-label">Wallet Address:</span>
                    <span class="info-value" style="word-break: break-all;">{{ $withdrawal->wallet_address }}</span>
                </div>
            </div>

            @if(count($changes) > 0)
            <div class="changes-box">
                <p><strong>📝 Changes Made:</strong></p>
                <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                    @foreach($changes as $change)
                    <li style="margin: 5px 0;">{{ $change }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($amountDifference > 0)
            <div class="notice-box">
                <p><strong>💰 Token Adjustment:</strong> {{ number_format($amountDifference, 2) }} additional RWAMP tokens have been deducted from your balance to match the updated withdrawal amount.</p>
            </div>
            @elseif($amountDifference < 0)
            <div class="notice-box" style="background-color: #d1fae5; border-left-color: #10b981;">
                <p><strong>💰 Token Refund:</strong> {{ number_format(abs($amountDifference), 2) }} RWAMP tokens have been refunded to your balance due to the amount decrease.</p>
            </div>
            @endif

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('user.withdrawals') }}" class="btn">View Withdrawal Details</a>
            </div>

            <h3 style="color: #000; margin-top: 30px;">Important Notes:</h3>
            <ul style="color: #666; line-height: 1.8;">
                <li>Please verify all updated information is correct</li>
                <li>If the amount was increased, additional tokens have been deducted</li>
                <li>If the amount was decreased, tokens have been refunded to your balance</li>
                <li>Contact support if you notice any discrepancies</li>
            </ul>

            <p style="margin-top: 30px; color: #666; font-size: 14px;">
                If you have any questions or concerns about these changes, please don't hesitate to contact our support team.
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

