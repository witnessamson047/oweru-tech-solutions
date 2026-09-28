<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #92400e; color: #fff; padding: 30px; text-align: center; border-radius: 12px 12px 0 0; }
        .content { padding: 30px; background: #f9fafb; }
        .amount-box { background: #fffbeb; border-left: 3px solid #d4af37; padding: 16px 20px; border-radius: 0 8px 8px 0; margin: 16px 0; }
        .details { background: #fff; border-radius: 8px; padding: 16px 20px; margin: 16px 0; }
        .details div { display: flex; justify-content: space-between; padding: 4px 0; font-size: 14px; }
        .details .grand { border-top: 1px solid #eee; margin-top: 6px; padding-top: 10px; font-weight: 700; }
        .cta { text-align: center; margin: 24px 0; }
        .cta a { display: inline-block; background: #eab308; color: #000; text-decoration: none; padding: 12px 30px; border-radius: 8px; font-weight: 700; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin:0;font-size:20px;">⏰ Friendly Payment Reminder</h1>
        <p style="margin:8px 0 0;opacity:0.85;font-size:14px;">Invoice {{ $invoice->number }}</p>
    </div>

    <div class="content">
        <p style="font-size:14px;color:#555;">
            Hi {{ $invoice->enquiry->name }},<br><br>
            Just a quick note that the deposit for <strong>{{ $invoice->title }}</strong> appears to still be outstanding:
        </p>

        <div class="amount-box">
            <strong>Deposit required before work starts ({{ $invoice->deposit_percent }}%):</strong>
            <div style="font-size:24px;font-weight:800;color:#92400e;margin-top:4px;">
                TZS {{ number_format($invoice->next_payment_amount, 0) }}
            </div>
            @if($invoice->days_overdue > 0)
                <div style="font-size:12px;color:#b45309;">{{ $invoice->days_overdue }} day{{ $invoice->days_overdue === 1 ? '' : 's' }} past the due date ({{ $invoice->due_at->format('d F Y') }})</div>
            @endif
        </div>

        <div class="details">
            <div><span>Invoice total</span><span>TZS {{ number_format($invoice->total, 0) }}</span></div>
            <div><span>Deposit ({{ $invoice->deposit_percent }}%)</span><span>TZS {{ number_format($invoice->deposit_due, 0) }}</span></div>
            @if($invoice->amount_paid > 0)
                <div><span>Already paid</span><span style="color:#065f46;font-weight:600;">TZS {{ number_format($invoice->amount_paid, 0) }}</span></div>
            @endif
            <div class="grand"><span>Outstanding deposit</span><span>TZS {{ number_format($invoice->next_payment_amount, 0) }}</span></div>
        </div>

        <div class="cta">
            <a href="{{ $depositUrl }}">Pay Deposit Now</a>
        </div>

        <p style="font-size:12px;color:#888;">
            If you've already paid, thank you — this reminder may have crossed with your payment and you can ignore it.
            If you'd like to discuss the payment schedule, just reply to this email or call
            {{ config('owers.company.phone') }}.
        </p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} {{ config('owers.company.name') }} · {{ config('owers.company.email') }} · {{ config('owers.company.phone') }}</p>
    </div>
</body>
</html>
