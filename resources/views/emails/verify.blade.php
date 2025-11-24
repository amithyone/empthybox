<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - BiggestLogs</title>
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
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #ff4757 0%, #ffd32a 100%);
            color: #1a1a2e;
            padding: 18px 40px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 8px 20px rgba(255, 71, 87, 0.4);
            transition: all 0.3s ease;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(255, 71, 87, 0.6);
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
        .footer a {
            color: #ffd32a;
            text-decoration: none;
        }
        .icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        .highlight {
            color: #ffd32a;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">🔥</div>
            <h1>BiggestLogs</h1>
        </div>
        
        <div class="content">
            <h2>Welcome {{ $user->name }}! 🎉</h2>
            
            <p>Thank you for signing up with <span class="highlight">BiggestLogs</span>!</p>
            
            <p>To get started and start buying premium digital accounts, please verify your email address by clicking the button below:</p>
            
            <div class="button-container">
                <a href="{{ $verificationUrl }}" class="button">Verify My Email</a>
            </div>
            
            <p style="font-size: 14px; color: #888; text-align: center;">
                Or copy and paste this link into your browser:<br>
                <a href="{{ $verificationUrl }}" style="color: #ffd32a; word-break: break-all;">{{ $verificationUrl }}</a>
            </p>
            
            <p style="margin-top: 30px;">This link will expire in 24 hours for security reasons.</p>
            
            <p style="border-left: 3px solid #ff4757; padding-left: 15px; background: rgba(255, 71, 87, 0.1); padding: 15px; border-radius: 5px;">
                <strong>Not sure why you received this email?</strong><br>
                If you didn't create an account with BiggestLogs, you can safely ignore this email.
            </p>
        </div>
        
        <div class="footer">
            <p><strong>🔥 BiggestLogs - Your Premium Digital Marketplace</strong></p>
            <p>Thank you for choosing us!</p>
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

