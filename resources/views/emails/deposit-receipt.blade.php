<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit Receipt</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { margin:0; padding:0; font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background:#111827; color:#e5e7eb; }
        .container { max-width:600px; margin:24px auto; background:#1f2937; border-radius:16px; overflow:hidden; border:1px solid rgba(239,68,68,0.2); }
        .header { padding:24px; background:linear-gradient(135deg,#ef4444,#f59e0b); color:#fff; }
        .content { padding:24px; }
        .row { display:flex; justify-content:space-between; margin:8px 0; }
        .label { color:#9ca3af; }
        .value { color:#f9fafb; font-weight:600; }
        .footer { padding:16px 24px 24px; color:#9ca3af; font-size:12px; }
        .card { background:#111827; border:1px solid #374151; border-radius:12px; padding:16px; }
    </style>
    </head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0; font-size:22px;">🔥 BiggestLogs</h1>
            <p style="margin:8px 0 0;">Deposit Successful</p>
        </div>
        <div class="content">
            <p>Hi {{ $transaction->user->name ?? 'there' }},</p>
            <p>Your wallet has been credited successfully.</p>

            <div class="card">
                <div class="row">
                    <span class="label">Reference</span>
                    <span class="value">{{ $transaction->reference }}</span>
                </div>
                <div class="row">
                    <span class="label">Amount</span>
                    <span class="value">₦{{ number_format($transaction->amount, 2) }}</span>
                </div>
                <div class="row">
                    <span class="label">Status</span>
                    <span class="value">Completed</span>
                </div>
                <div class="row">
                    <span class="label">Date</span>
                    <span class="value">{{ $transaction->updated_at?->format('M d, Y h:i A') ?? now()->format('M d, Y h:i A') }}</span>
                </div>
            </div>

            <p style="margin-top:16px;">You can view this deposit in your wallet anytime.</p>
        </div>
        <div class="footer">
            <p>Thanks for using BiggestLogs.</p>
        </div>
    </div>
</body>
</html>


