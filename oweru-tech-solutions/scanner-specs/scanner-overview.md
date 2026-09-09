# Oweru Website Health Scanner — Python Service

## Purpose
Specialist website scanning and analysis engine. Receives scan requests from Laravel, crawls permitted pages, runs 17 technical checks across 8 areas, and returns structured JSON results.

## Technology
- Python 3.10+
- Flask (API server)
- requests (HTTP client)
- python-dateutil (date parsing)
- urllib.robotparser (robots.txt)

## Install
```bash
pip install requests python-dateutil
```

## Run
```bash
# API server mode (default)
python scanner.py

# CLI scan mode
python scanner.py scan https://example.co.tz

# With output file
python scanner.py scan https://example.co.tz -o result.json
```

## Environment
```
SCANNER_PORT=5000        # API server port
SCANNER_DEBUG=false      # Flask debug mode
SCANNER_API_KEY=         # API key for Laravel communication
```

## API

### POST /api/scanner/scan
Request:
```json
{
  "url": "https://example.co.tz",
  "scan_id": 123
}
```
Response:
```json
{
  "success": true,
  "scan_id": 123,
  "url": "https://example.co.tz",
  "score": 67,
  "band": "Adequate",
  "elapsed_seconds": 23.4,
  "pages_scanned": 8,
  "results": [
    {
      "check_name": "SSL Certificate Valid",
      "area": "Security",
      "passed": true,
      "points": 10,
      "evidence": "HTTPS: true, Certificate valid: true",
      "finding_text": "The website uses HTTPS with a valid SSL certificate...",
      "consequence": null
    }
  ]
}
```

### GET /api/scanner/scans/{id}
Get scan status (placeholder — needs database integration).

### POST /api/scanner/callback
Receive async scan results from Python back to Laravel.

### GET /health
Health check.

## Scanning Rules
1. Check robots.txt first; stop if disallowed
2. User-Agent: `OweruScanner/1.0 (+https://oweru.co.tz; scanner@oweru.co.tz)`
3. Max 1 request per 2 seconds per host
4. Scan home + up to 10 internal pages from main nav
5. Delete raw content after scoring
6. Never access private areas

## Checks (17 total, 100 points)
See system-documentation.md Section 5 for full list.

## Integration with Laravel
- Laravel sends scan request to `SCANNER_SERVICE_URL/api/scanner/scan`
- Python returns JSON with score, band, and results
- Laravel stores results in `scans` and `scan_results` tables
- Laravel calculates band from score using `Scan::calculateBand()`
