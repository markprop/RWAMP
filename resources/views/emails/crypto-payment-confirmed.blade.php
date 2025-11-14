<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RWAMP Tokens Credited - Payment Confirmed</title>
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #E30613, #000000);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .success-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #E30613;
            margin: 20px 0;
        }
        .details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .value {
            color: #333;
        }
        .button {
            display: inline-block;
            background: #E30613;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="success-icon">✅</div>
        <h1>Payment Confirmed!</h1>
        <p>Your RWAMP tokens have been successfully credited to your account.</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $user->name }},</h2>
        
        <p>Great news! We've successfully processed your crypto payment and credited your RWAMP tokens.</p>
        
        <div class="amount">
            +{{ number_format($rwampTokens) }} RWAMP Tokens
        </div>
        
        <div class="details">
            <div class="detail-row">
                <span class="label">Transaction Hash:</span>
                <span class="value">{{ $txHash }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Network:</span>
                <span class="value">{{ $network }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Tokens Credited:</span>
                <span class="value">{{ number_format($rwampTokens) }} RWAMP</span>
            </div>
            <div class="detail-row">
                <span class="label">Date:</span>
                <span class="value">{{ now()->format('M d, Y H:i:s') }}</span>
            </div>
        </div>
        
        <p>Your tokens are now available in your account and ready to use for real estate investments.</p>
        
        <div style="text-align: center;">
            <a href="{{ url('/profile') }}" class="button">View My Account</a>
        </div>
        
        <div class="footer">
            <p>Thank you for choosing RWAMP!</p>
            <p>If you have any questions, please contact our support team.</p>
        </div>
    </div>
</body>
</html>
