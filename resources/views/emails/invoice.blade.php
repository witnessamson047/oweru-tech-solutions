<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #000; color: #fff; padding: 30px; text-align: center; border-radius: 12px 12px 0 0; }
        .content { padding: 30px; background: #f9fafb; }
        .amount-box { background: #fffbeb; border-left: 3px solid #d4af37; padding: 16px 20px; border-radius: 0 8px 8px 0; margin: 16px 0; }
        .totals { background: #fff; border-radius: 8px; padding: 16px 20px; margin: 16px 0; }
        .totals div { display: flex; justify-content: space-between; padding: 4px 0; font-size: 14px; }
        .totals .grand { border-top: 1px solid #eee; margin-top: 6px; padding-top: 10px; font-weight: 700; }
        .cta { text-align: center; margin: 24px 0; }
        .cta a { display: inline-block; background: #eab308; color: #000; text-decoration: none; padding: 12px 30px; border-radius: 8px; font-weight: 700; }
        .cta a.secondary { background: #000; color: #fff; margin-left: 8px; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #999; }
        .note { font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin:0;font-size:20px;">🧾 Invoice {{ $invoice->number }}</h1>
        <p style="margin:8px 0 0;opacity:0.8;font-size:14px;">{{ $invoice->title }}</p>
    </div>

    <div class="content">
        <p style="font-size:14px;color:#555;">
            Hi {{ $invoice->enquiry->name }},<br><br>
            Thank you for choosing Oweru International Ltd. Please find your invoice attached.
        </p>

        <div class="amount-box">
            <strong>To begin work, we require a {{ $invoice->deposit_percent }}% deposit:</strong>
            <div style="font-size:24px;font-weight:800;color:#92400e;margin-top:4px;">
                TZS {{ number_format($invoice->deposit_due, 0) }}
            </div>
            @if($invoice->due_at)
                <div class="note">Due by {{ $invoice->due_at->format('d F Y') }}</div>
            @endif
        </div>

        <div class="totals">
            <div><span>Total project value</span><span>TZS {{ number_format($invoice->total, 0) }}</span></div>
            <div><span>Deposit ({{ $invoice->deposit_percent }}%)</span><span>TZS {{ number_format($invoice->deposit_due, 0) }}</span></div>
            <div><span>Balance on completion</span><span>TZS {{ number_format($invoice->balance_amount, 0) }}</span></div>
        </div>

        <div class="cta">
            <a href="{{ $depositUrl }}">Pay Deposit Now</a>
            <a class="secondary" href="{{ route('payment.checkout', ['type' => 'invoice-full', 'id' => $invoice->id]) }}">Pay in Full</a>
        </div>

        <p class="note">
            Work on your project starts as soon as the deposit is received. The balance is payable on completion —
            an automatic receipt will be emailed to you once every payment has settled.
        </p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} {{ config('owers.company.name') }} · {{ config('owers.company.email') }} · {{ config('owers.company.phone') }}</p>
    </div>
</body>
</html>
