<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #065f46; color: #fff; padding: 30px; text-align: center; border-radius: 12px 12px 0 0; }
        .content { padding: 30px; background: #f9fafb; }
        .banner { background: #ecfdf5; border: 2px solid #10b981; border-radius: 8px; padding: 16px; text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; }
        th { background: #1a1a1a; color: #fff; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; padding: 8px 12px; text-align: left; }
        td { padding: 8px 12px; border-bottom: 1px solid #eee; font-size: 13px; }
        .num { text-align: right; }
        .grand { font-weight: 700; background: #ecfdf5; color: #065f46; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin:0;font-size:20px;">✅ Payment Received — Thank You!</h1>
        <p style="margin:8px 0 0;opacity:0.85;font-size:14px;">{{ $invoice->title }}</p>
    </div>

    <div class="content">
        <div class="banner">
            <strong style="font-size:16px;">PAID IN FULL</strong><br>
            <span style="font-size:13px;">Your receipt is attached as a PDF.</span>
        </div>

        <p style="font-size:14px;color:#555;">
            Hi {{ $invoice->enquiry->name }},<br><br>
            We've received all payments for <strong>{{ $invoice->title }}</strong>. Here's the exact record:
        </p>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Reference</th>
                    <th class="num">Amount (TZS)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->completedPayments as $payment)
                    <tr>
                        <td>{{ $payment->paid_at?->format('d M Y') }}</td>
                        <td>{{ $payment->kind_label }}</td>
                        <td style="font-size:10px;">{{ $payment->merchant_reference }}</td>
                        <td class="num">{{ number_format($payment->amount, 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;color:#999;">No payments recorded</td></tr>
                @endforelse
                <tr class="grand">
                    <td colspan="3">TOTAL PAID</td>
                    <td class="num">{{ number_format($invoice->amount_paid, 0) }}</td>
                </tr>
            </tbody>
        </table>

        <p style="font-size:13px;color:#666;margin-top:16px;">
            Our team will be in touch about next steps. If you have any questions about this receipt,
            simply reply to this email.
        </p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} {{ config('owers.company.name') }} · {{ config('owers.company.email') }} · {{ config('owers.company.phone') }}</p>
        <p>This receipt was generated automatically after all payments settled.</p>
    </div>
</body>
</html>
