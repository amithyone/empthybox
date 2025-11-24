<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - BiggestLogs</title>
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
        .order-info {
            background: #1a1a2e;
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
            border: 2px solid #ff4757;
        }
        .order-info h3 {
            color: #ffd32a;
            margin: 0 0 15px 0;
            font-size: 18px;
            border-bottom: 2px solid #ff4757;
            padding-bottom: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #2a2a3e;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #888;
            font-size: 14px;
        }
        .info-value {
            color: #ffd32a;
            font-weight: bold;
            font-size: 16px;
            text-align: right;
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
        .success-icon {
            font-size: 80px;
            text-align: center;
            margin: 20px 0;
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
            <div class="success-icon">✅</div>
            
            <h2>Order Confirmed! 🎉</h2>
            
            <p>Thank you for your purchase, <span class="highlight">{{ $order->user->name }}</span>!</p>
            
            <p>Your order has been successfully processed and is ready for delivery.</p>
            
            <div class="order-info">
                <h3>Order Details</h3>
                
                <div class="info-row">
                    <span class="info-label">Order Number</span>
                    <span class="info-value">#{{ $order->order_number }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Product</span>
                    <span class="info-value">{{ $product->name }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Amount Paid</span>
                    <span class="info-value">₦{{ number_format($amount, 2) }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Payment Method</span>
                    <span class="info-value">{{ ucfirst($order->payment_method ?? 'N/A') }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Status</span>
                    <span class="info-value">{{ ucfirst($order->status) }}</span>
                </div>
            </div>
            
            <p style="border-left: 3px solid #ff4757; padding-left: 15px; background: rgba(255, 71, 87, 0.1); padding: 15px; border-radius: 5px;">
                <strong>🚀 Next Steps:</strong><br>
                Your product credentials are ready! Click the button below to access your order and view your login details.
            </p>
            
            <div class="button-container">
                <a href="{{ $verificationUrl }}" class="button">View My Order</a>
            </div>
            
            <p style="font-size: 14px; color: #888; text-align: center;">
                Need help? Our support team is ready to assist you!<br>
                <a href="{{ config('app.url') }}/tickets" style="color: #ffd32a;">Contact Support</a>
            </p>
        </div>
        
        <div class="footer">
            <p><strong>🔥 BiggestLogs - Your Premium Digital Marketplace</strong></p>
            <p>Thank you for choosing us!</p>
            <p>
                <a href="{{ config('app.url') }}">Visit Website</a> |
                <a href="{{ config('app.url') }}/orders">My Orders</a> |
                <a href="{{ config('app.url') }}/contact">Contact Support</a>
            </p>
            <p style="font-size: 12px; margin-top: 20px;">
                &copy; {{ date('Y') }} BiggestLogs. All rights reserved.<br>
                Order Date: {{ $order->created_at->format('F d, Y h:i A') }}
            </p>
        </div>
    </div>
</body>
</html>

