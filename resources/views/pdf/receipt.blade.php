<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .doc { width: 100%; max-width: 800px; margin: 0 auto; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #D4AF37; padding-bottom: 15px; margin-bottom: 20px; }
        .brand { display: flex; align-items: center; gap: 10px; }
        .brand-logo { width: 40px; height: 40px; background: #1a1a1a; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #D4AF37; font-weight: bold; font-size: 18px; }
        .brand-name { font-weight: bold; font-size: 14px; color: #1a1a1a; }
        .brand-sub { font-size: 8px; color: #666; text-transform: uppercase; letter-spacing: 1px; }
        .doc-meta { text-align: right; font-size: 9px; color: #666; }
        .doc-meta .doc-title { font-size: 16px; font-weight: 800; color: #065f46; letter-spacing: 1px; }
        .doc-meta .doc-number { font-size: 11px; font-weight: 700; color: #1a1a1a; margin-top: 3px; }

        .paid-banner { background: #ecfdf5; border: 2px solid #10b981; color: #065f46; border-radius: 8px; padding: 10px 15px; text-align: center; font-size: 12px; font-weight: 700; margin-bottom: 20px; }

        .parties { display: flex; justify-content: space-between; margin-bottom: 20px; gap: 20px; }
        .party { background: #f8f9fa; padding: 10px 14px; border-radius: 6px; flex: 1; }
        .party .label { font-size: 8px; color: #999; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
        .party .value { font-weight: 600; font-size: 11px; color: #333; }
        .party .muted { font-size: 9px; color: #666; }

        .table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .table th { background: #1a1a1a; color: #fff; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; padding: 7px 10px; text-align: left; }
        .table td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; font-size: 10px; vertical-align: top; }
        .table .num { text-align: right; white-space: nowrap; }

        .totals { margin-left: auto; width: 55%; }
        .totals .row { display: flex; justify-content: space-between; padding: 5px 10px; font-size: 10px; }
        .totals .row.grand { background: #065f46; color: #fff; font-weight: 700; border-radius: 4px; margin-top: 3px; }

        .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #e5e7eb; font-size: 8px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="doc">
        {{-- Header --}}
        <div class="header">
            <div class="brand">
                <div class="brand-logo">O</div>
                <div>
                    <div class="brand-name">OWERU INTERNATIONAL LTD</div>
                    <div class="brand-sub">{{ config('owers.company.tagline') }} · Dar es Salaam, Tanzania</div>
                </div>
            </div>
            <div class="doc-meta">
                <div class="doc-title">RECEIPT</div>
                <div class="doc-number">{{ $invoice->number }}</div>
                <div style="margin-top:5px;">
                    Issued: {{ now()->format('d M Y') }}
                </div>
            </div>
        </div>

        <div class="paid-banner">
            ✓ PAID IN FULL — THANK YOU
        </div>

        {{-- Parties --}}
        <div class="parties">
            <div class="party">
                <div class="label">Received From</div>
                <div class="value">{{ $invoice->enquiry->business_name ?: $invoice->enquiry->name }}</div>
                <div class="muted">{{ $invoice->enquiry->name }}</div>
                <div class="muted">{{ $invoice->enquiry->email }}</div>
            </div>
            <div class="party">
                <div class="label">Service</div>
                <div class="value">{{ $invoice->title }}</div>
                <div class="muted">Invoice {{ $invoice->number }}</div>
            </div>
        </div>

        {{-- Payment history --}}
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Reference</th>
                    <th>Method</th>
                    <th>Type</th>
                    <th class="num">Amount ({{ $invoice->currency }})</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->completedPayments as $payment)
                    <tr>
                        <td>{{ $payment->paid_at?->format('d M Y') ?? $payment->created_at->format('d M Y') }}</td>
                        <td style="font-family: monospace; font-size: 8px;">{{ $payment->merchant_reference }}</td>
                        <td>{{ $payment->payment_method ?: 'PesaPal' }}</td>
                        <td>{{ $payment->kind_label }}</td>
                        <td class="num">{{ number_format($payment->amount, 0) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; color:#999;">No payments recorded</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="totals">
            <div class="row"><span>Invoice total</span><span>{{ $invoice->currency }} {{ number_format($invoice->total, 0) }}</span></div>
            <div class="row"><span>Total paid</span><span>{{ $invoice->currency }} {{ number_format($invoice->amount_paid, 0) }}</span></div>
            <div class="row grand"><span>BALANCE</span><span>{{ $invoice->currency }} {{ number_format($invoice->balance_due, 0) }}</span></div>
        </div>

        <div class="footer">
            This receipt was generated automatically by {{ config('owers.company.name') }} after all payments settled.<br>
            {{ config('owers.company.email') }} · {{ config('owers.company.phone') }} · © {{ date('Y') }} {{ config('owers.company.name') }}
        </div>
    </div>
</body>
</html>
