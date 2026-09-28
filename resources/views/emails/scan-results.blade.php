<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #000; color: #fff; padding: 30px; text-align: center; border-radius: 12px 12px 0 0; }
        .score-circle { width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 800; margin: 20px auto; border: 4px solid; }
        .score-critical { border-color: #000; background: #f9f9f9; color: #000; }
        .score-weak { border-color: #92400e; background: #fffbeb; color: #92400e; }
        .score-adequate { border-color: #d97706; background: #fffbeb; color: #92400e; }
        .score-strong { border-color: #f59e0b; background: #ecfdf5; color: #065f46; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-critical { background: #000; color: #fff; }
        .badge-weak { background: #92400e; color: #fff; }
        .badge-adequate { background: #d97706; color: #fff; }
        .badge-strong { background: #16a34a; color: #fff; }
        .content { padding: 30px; background: #f9fafb; }
        .finding { background: #fff; border-left: 3px solid #ef4444; padding: 12px 16px; margin: 8px 0; border-radius: 0 8px 8px 0; }
        .finding h4 { margin: 0 0 4px; font-size: 14px; color: #111; }
        .finding p { margin: 0; font-size: 13px; color: #666; }
        .cta { text-align: center; margin: 30px 0; }
        .cta a { display: inline-block; background: #eab308; color: #000; text-decoration: none; padding: 12px 30px; border-radius: 8px; font-weight: 600; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin:0;font-size:20px;">🌐 Website Health Report</h1>
        <p style="margin:8px 0 0;opacity:0.8;font-size:14px;">{{ $url }}</p>
        <div class="score-circle {{ $score < 40 ? 'score-critical' : ($score < 60 ? 'score-weak' : ($score < 80 ? 'score-adequate' : 'score-strong')) }}">
            {{ $score }}
        </div>
        <span class="badge badge-{{ strtolower($band) }}">{{ $band }} ({{ $score }}/100)</span>
    </div>

    <div class="content">
        <h2 style="margin:0 0 16px;font-size:18px;">Your Results</h2>
        <p style="font-size:14px;color:#555;margin:0 0 20px;">
            We scanned <strong>{{ $url }}</strong> across 23 checks in 8 areas.
            Your website scored <strong>{{ $score }}/100</strong> — rated as <strong>{{ $band }}</strong>.
        </p>

        @if($findings->count() > 0)
            <h3 style="font-size:16px;margin:0 0 12px;">⚠️ Top Issues Found</h3>
            @foreach($findings as $finding)
                <div class="finding">
                    <h4>{{ $finding->check_name }} — {{ $finding->area }}</h4>
                    <p>{{ $finding->finding_text }}</p>
                    @if($finding->consequence)
                        <p style="color:#999;font-size:12px;margin-top:4px;"><em>{{ $finding->consequence }}</em></p>
                    @endif
                </div>
            @endforeach
        @else
            <div style="background:#ecfdf5;padding:20px;border-radius:8px;text-align:center;">
                <p style="margin:0;color:#065f46;font-weight:600;">✅ All checks passed! Your website is in great shape.</p>
            </div>
        @endif

        <div class="cta">
            <a href="{{ route('scanner.index') }}">🔍 Scan Another Website</a>
        </div>

        <p style="font-size:13px;color:#888;text-align:center;margin:0;">
            Need help fixing these issues? <a href="mailto:info@oweru.co.tz" style="color:#d97706;">Contact Oweru Tech Solutions</a> for a free consultation.
        </p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} Oweru Tech Solutions. All rights reserved.</p>
        <p>This report was generated automatically by our website health scanner.</p>
    </div>
</body>
</html>
