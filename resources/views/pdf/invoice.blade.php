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
        .doc-meta .doc-title { font-size: 16px; font-weight: 800; color: #1a1a1a; letter-spacing: 1px; }
        .doc-meta .doc-number { font-size: 11px; font-weight: 700; color: #1a1a1a; margin-top: 3px; }

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
        .totals .row.grand { background: #1a1a1a; color: #fff; font-weight: 700; border-radius: 4px; margin-top: 3px; }
        .totals .row.deposit { background: #fffbeb; border-left: 3px solid #D4AF37; font-weight: 600; margin-top: 3px; }
        .totals .row.paid { color: #065f46; }
        .totals .row.balance { color: #b91c1c; font-weight: 600; }

        .terms { background: #f0f9ff; padding: 10px 15px; border-radius: 6px; margin-top: 16px; }
        .terms h4 { font-size: 10px; font-weight: 700; color: #1a1a1a; margin-bottom: 4px; }
        .terms p { font-size: 9px; color: #555; }

        .status-pill { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .st-draft { background: #f3f4f6; color: #6b7280; }
        .st-issued { background: #fffbeb; color: #92400e; }
        .st-part_paid { background: #eff6ff; color: #1e40af; }
        .st-paid { background: #ecfdf5; color: #065f46; }

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
                <div class="doc-title">INVOICE</div>
                <div class="doc-number">{{ $invoice->number }}</div>
                <div style="margin-top:5px;">
                    Issued: {{ $invoice->issued_at?->format('d M Y') ?? now()->format('d M Y') }}<br>
                    Due: {{ $invoice->due_at?->format('d M Y') ?? '—' }}
                </div>
                <div style="margin-top:6px;">
                    <span class="status-pill st-{{ $invoice->status_label }}">{{ $invoice->status_label }}</span>
                </div>
            </div>
        </div>

        {{-- Parties --}}
        <div class="parties">
            <div class="party">
                <div class="label">Billed To</div>
                <div class="value">{{ $invoice->enquiry->business_name ?: $invoice->enquiry->name }}</div>
                <div class="muted">{{ $invoice->enquiry->name }}</div>
                <div class="muted">{{ $invoice->enquiry->email }}</div>
                @if($invoice->enquiry->phone)<div class="muted">{{ $invoice->enquiry->phone }}</div>@endif
            </div>
            <div class="party">
                <div class="label">Service</div>
                <div class="value">{{ $invoice->title }}</div>
                @if($invoice->enquiry->scan)
                    <div class="muted">Health scan: {{ $invoice->enquiry->scan->score }}/100 ({{ $invoice->enquiry->scan->band }})</div>
                @endif
            </div>
        </div>

        {{-- Line items --}}
        <table class="table">
            <thead>
                <tr>
                    <th style="width:55%;">Description</th>
                    <th class="num">Amount ({{ $invoice->currency }})</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $invoice->title }}</strong><br>
                        @if($invoice->description)<span style="color:#666;">{{ $invoice->description }}</span>@endif
                    </td>
                    <td class="num">{{ number_format($invoice->total, 0) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="totals">
            <div class="row"><span>Total project value</span><span>{{ $invoice->currency }} {{ number_format($invoice->total, 0) }}</span></div>
            <div class="row deposit"><span>Deposit required before work starts ({{ $invoice->deposit_percent }}%)</span><span>{{ $invoice->currency }} {{ number_format($invoice->deposit_due, 0) }}</span></div>
            <div class="row paid"><span>Paid to date</span><span>− {{ $invoice->currency }} {{ number_format($invoice->amount_paid, 0) }}</span></div>
            <div class="row balance"><span>Balance due on completion</span><span>{{ $invoice->currency }} {{ number_format($invoice->balance_due, 0) }}</span></div>
            <div class="row grand"><span>Status</span><span>{{ ucfirst(str_replace('_', ' ', $invoice->status_label)) }}</span></div>
        </div>

        {{-- Payment instructions --}}
        <div class="terms">
            <h4>Payment Terms</h4>
            <p>@if($invoice->deposit_percent > 0)
                Work begins once the {{ $invoice->deposit_percent }}% deposit ({{ $invoice->currency }} {{ number_format($invoice->deposit_due, 0) }}) is received.
                The remaining balance is payable on project completion. Pay securely online via the payment link in your email,
                or contact us at {{ config('owers.company.email') }}.
            @else
                Full payment is due as per the agreement. Pay securely online via the payment link in your email,
                or contact us at {{ config('owers.company.email') }}.
            @endif</p>
        </div>

        <div class="footer">
            {{ config('owers.company.name') }} · {{ config('owers.company.email') }} · {{ config('owers.company.phone') }}<br>
            Invoice {{ $invoice->number }} · Generated {{ now()->format('d M Y H:i') }} · © {{ date('Y') }} {{ config('owers.company.name') }}
        </div>
    </div>
</body>
</html>
