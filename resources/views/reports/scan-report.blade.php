<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .report { width: 100%; max-width: 800px; margin: 0 auto; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #D4AF37; padding-bottom: 15px; margin-bottom: 20px; }
        .brand { display: flex; align-items: center; gap: 10px; }
        .brand-logo { width: 40px; height: 40px; background: #1a1a1a; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #D4AF37; font-weight: bold; font-size: 18px; }
        .brand-name { font-weight: bold; font-size: 14px; color: #1a1a1a; }
        .brand-sub { font-size: 8px; color: #666; text-transform: uppercase; letter-spacing: 1px; }
        .report-date { font-size: 9px; color: #666; text-align: right; }

        .business-info { background: #f8f9fa; padding: 12px 15px; border-radius: 6px; margin-bottom: 20px; display: flex; justify-content: space-between; }
        .business-info div { }
        .business-info .label { font-size: 8px; color: #999; text-transform: uppercase; letter-spacing: 0.5px; }
        .business-info .value { font-weight: 600; font-size: 11px; color: #333; }

        .score-section { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; padding: 15px; border: 2px solid #e5e7eb; border-radius: 8px; }
        .score-circle { width: 80px; height: 80px; border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 4px solid; flex-shrink: 0; }
        .score-number { font-size: 28px; font-weight: 800; }
        .score-label { font-size: 8px; color: #666; }
        .score-band { font-size: 14px; font-weight: 700; }
        .score-explanation { font-size: 9px; color: #666; }

        .findings { margin-bottom: 20px; }
        .findings-title { font-size: 12px; font-weight: 700; color: #1a1a1a; margin-bottom: 10px; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px; }
        .finding { margin-bottom: 8px; padding: 8px 10px; background: #fffbeb; border-left: 3px solid #D4AF37; border-radius: 0 4px 4px 0; }
        .finding-header { font-weight: 600; font-size: 10px; color: #333; margin-bottom: 2px; }
        .finding-area { font-size: 8px; color: #999; text-transform: uppercase; }
        .finding-text { font-size: 9px; color: #555; margin-top: 3px; }
        .finding-consequence { font-size: 9px; color: #b91c1c; font-style: italic; margin-top: 2px; }
        .finding-solution { font-size: 9px; color: #065f46; margin-top: 3px; font-weight: 600; }

        .scoring-explanation { background: #f0f9ff; padding: 10px 15px; border-radius: 6px; margin-bottom: 15px; }
        .scoring-explanation h4 { font-size: 10px; font-weight: 700; color: #1a1a1a; margin-bottom: 5px; }
        .scoring-explanation p { font-size: 9px; color: #555; }
        .scoring-bands { display: flex; gap: 10px; margin-top: 8px; }
        .scoring-band { flex: 1; text-align: center; padding: 4px; border-radius: 4px; font-size: 8px; font-weight: 600; }
        .band-strong { background: #ecfdf5; color: #065f46; }
        .band-adequate { background: #eff6ff; color: #1e40af; }
        .band-weak { background: #fffbeb; color: #92400e; }
        .band-critical { background: #fef2f2; color: #991b1b; }

        .cta { background: #1a1a1a; color: white; padding: 15px 20px; border-radius: 8px; text-align: center; border-top: 3px solid #D4AF37; }
        .cta h3 { font-size: 13px; margin-bottom: 5px; color: #D4AF37; }
        .cta p { font-size: 9px; opacity: 0.9; margin-bottom: 8px; }
        .cta .contact { font-size: 10px; font-weight: 600; }

        .footer { margin-top: 15px; padding-top: 10px; border-top: 1px solid #e5e7eb; font-size: 8px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="report">

        {{-- Header --}}
        <div class="header">
            <div class="brand">
                <div class="brand-logo">O</div>
                <div>
                    <div class="brand-name">OWERU INTERNATIONAL LTD</div>
                    <div class="brand-sub">Website Health Report</div>
                </div>
            </div>
            <div class="report-date">
                Report Generated: {{ $scan->completed_at?->format('d M Y') ?? now()->format('d M Y') }}<br>
                Scan Date: {{ $scan->started_at?->format('d M Y H:i') ?? $scan->created_at->format('d M Y H:i') }}
            </div>
        </div>

        {{-- Business Info --}}
        <div class="business-info">
            <div>
                <div class="label">Business Name</div>
                <div class="value">{{ $scan->website->business_name ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="label">Website</div>
                <div class="value">{{ $scan->url }}</div>
            </div>
            <div>
                <div class="label">Sector</div>
                <div class="value">{{ $scan->website->sector ?? 'N/A' }}</div>
            </div>
        </div>

        {{-- Score --}}
        @php
            $score = $scan->score ?? 0;
            $bandColor = $score < 40 ? '#dc2626' : ($score < 60 ? '#f59e0b' : ($score < 80 ? '#3b82f6' : '#10b981'));
            $bandBg = $score < 40 ? '#fef2f2' : ($score < 60 ? '#fffbeb' : ($score < 80 ? '#eff6ff' : '#ecfdf5'));
            $bandText = $score < 40 ? '#991b1b' : ($score < 60 ? '#92400e' : ($score < 80 ? '#1e40af' : '#065f46'));
        @endphp
        <div class="score-section">
            <div class="score-circle" style="border-color: {{ $bandColor }}; background: {{ $bandBg }}">
                <div class="score-number" style="color: {{ $bandColor }}">{{ $score }}</div>
                <div class="score-label">/100</div>
            </div>
            <div>
                <div class="score-band" style="color: {{ $bandText }}">{{ $scan->band }} Website Health</div>
                <div class="score-explanation">
                    @if($score >= 80)
                        Your website is performing well across most areas. Minor improvements could make it even stronger.
                    @elseif($score >= 60)
                        Your website meets basic standards but has areas that need attention to improve visitor experience and trust.
                    @elseif($score >= 40)
                        Your website has significant issues that may be affecting visitor confidence, search visibility, and conversions.
                    @else
                        Your website has critical issues across multiple areas. Visitors may be leaving before engaging with your business.
                    @endif
                </div>
            </div>
        </div>

        {{-- Top 5 Findings --}}
        <div class="findings">
            <div class="findings-title">Priority Findings (Areas Needing Attention)</div>
            @foreach($findings as $finding)
                <div class="finding">
                    <div class="finding-header">{{ $finding->check->name ?? $finding->check_name }}</div>
                    <div class="finding-area">{{ $finding->area }}</div>
                    <div class="finding-text">{{ $finding->finding_text }}</div>
                    @if($finding->consequence)
                        <div class="finding-consequence">Impact: {{ $finding->consequence }}</div>
                    @endif
                    @if($finding->recommendation)
                        <div class="finding-solution">✓ Recommended solution: {{ $finding->recommendation->solution }} ({{ $finding->recommendation->service_type }})</div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Scoring Explanation --}}
        <div class="scoring-explanation">
            <h4>How This Score Was Calculated</h4>
            <p>This score is based on {{ $scan->results->count() }} automated checks across 8 areas: Security, Mobile, Speed, Functionality, Findability, Trust, Commerce, and Freshness. Each check either passes and earns its assigned points, or fails and earns zero.</p>
            <div class="scoring-bands">
                <div class="scoring-band band-strong">80-100: Strong</div>
                <div class="scoring-band band-adequate">60-79: Adequate</div>
                <div class="scoring-band band-weak">40-59: Weak</div>
                <div class="scoring-band band-critical">Under 40: Critical</div>
            </div>
        </div>

        {{-- CTA --}}
        <div class="cta">
            <h3>Ready to Improve Your Website?</h3>
            <p>Our team can help you address these issues and build a stronger digital presence.</p>
            <div class="contact">📧 inf@oweru.com &nbsp;|&nbsp; 📞 +255 711 890 764 &nbsp;|&nbsp; 📍 Dar es Salaam, Tanzania</div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            This report was generated by Oweru International Ltd. Scan performed on {{ $scan->started_at?->format('d M Y H:i') ?? 'N/A' }}.
            Website conditions may change after scan date. © {{ date('Y') }} Oweru International Ltd. All rights reserved.
        </div>
    </div>
</body>
</html>
