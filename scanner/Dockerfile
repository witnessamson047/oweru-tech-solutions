# Oweru scanner/scraper engine — Flask app + polite business scraper.
# Minimal, production-grade: gunicorn serves the app, curl exists for the
# container healthcheck. Playwright (headless Chromium for SPA sites) is
# OPT-IN at build time because it adds ~500MB:
#   docker build --build-arg INSTALL_PLAYWRIGHT=1 .
# (Render/Fly: enable via the INSTALL_PLAYWRIGHT env var in render.yaml /
#  fly.toml build args if you want SPA rendering in the cloud.)

FROM python:3.12-slim

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1

WORKDIR /app

# curl is only here so the platform healthcheck can hit /health.
RUN apt-get update \
    && apt-get install -y --no-install-recommends curl ca-certificates \
    && rm -rf /var/lib/apt/lists/*

COPY requirements.txt ./
RUN pip install --no-cache-dir -r requirements.txt

# Optional headless browser for SPA sites (adds ~500MB to the image).
ARG INSTALL_PLAYWRIGHT=0
RUN if [ "$INSTALL_PLAYWRIGHT" = "1" ]; then \
        pip install --no-cache-dir playwright \
        && playwright install --with-deps chromium; \
    fi

COPY scanner.py business_scraper.py ./

# SCANNER_API_KEY, SCANNER_PORT come from the platform's env settings.
EXPOSE 5000

# 2 workers cover concurrent public scans; 300s timeout matches the longest
# request the engine handles (a 4-page scrape with headless rendering).
CMD ["sh", "-c", "exec gunicorn --bind 0.0.0.0:${SCANNER_PORT:-5000} --workers 2 --threads 4 --timeout 300 scanner:app"]
