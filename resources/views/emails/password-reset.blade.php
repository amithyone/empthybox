<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - BiggestLogs</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/proxima-nova" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', 'Proxima Nova', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #e0e0e0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #2a2a3e;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(255, 71, 87, 0.3);
        }
        .header {
            background: linear-gradient(135deg, #ff4757 0%, #ffd32a 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            color: #1a1a2e;
            font-size: 32px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .content {
            padding: 40px 30px;
        }
        .content h2 {
            color: #ffd32a;
            margin: 0 0 20px 0;
            font-size: 24px;
        }
        .content p {
            line-height: 1.8;
            margin: 0 0 20px 0;
            color: #d0d0d0;
            font-size: 16px;
        }
        .code-container {
            text-align: center;
            margin: 40px 0;
        }
        .code {
            display: inline-block;
            background: linear-gradient(135deg, #ff4757 0%, #ffd32a 100%);
            color: #1a1a2e;
            padding: 25px 50px;
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 10px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(255, 71, 87, 0.4);
            font-family: 'Courier New', monospace;
        }
        .footer {
            background: #1a1a2e;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #ff4757;
        }
        .footer p {
            margin: 10px 0;
            color: #888;
            font-size: 14px;
        }
        .icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        .highlight {
            color: #ffd32a;
            font-weight: bold;
        }
        .warning {
            border-left: 3px solid #ff4757;
            padding-left: 15px;
            background: rgba(255, 71, 87, 0.1);
            padding: 15px;
            border-radius: 5px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">🔒</div>
            <h1>BiggestLogs</h1>
        </div>
        
        <div class="content">
            <h2>Password Reset Request</h2>
            
            <p>Hello {{ $user->name }},</p>
            
            <p>We received a request to reset your password for your <span class="highlight">BiggestLogs</span> account.</p>
            
            <p>Use the code below to reset your password:</p>
            
            <div class="code-container">
                <div class="code">{{ $resetCode }}</div>
            </div>
            
            <p style="text-align: center; color: #888; font-size: 14px;">
                This code will expire in <strong class="highlight">60 minutes</strong>.
            </p>
            
            <div class="warning">
                <strong>Important Security Notice:</strong><br>
                If you didn't request this password reset, please ignore this email. Your account remains secure.
            </div>
        </div>
        
        <div class="footer">
            <p><strong>🔥 BiggestLogs - Your Premium Digital Marketplace</strong></p>
            <p>Thank you for using our service!</p>
            <p>
                <a href="{{ config('app.url') }}">Visit Website</a> |
                <a href="{{ config('app.url') }}/login">Login</a> |
                <a href="{{ config('app.url') }}/contact">Contact Support</a>
            </p>
            <p style="font-size: 12px; margin-top: 20px;">
                &copy; {{ date('Y') }} BiggestLogs. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>

