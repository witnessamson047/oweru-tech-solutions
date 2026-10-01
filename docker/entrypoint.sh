#!/bin/sh
# Container boot: wait for MySQL, migrate once, warm caches, then serve.
set -e

echo "[entrypoint] waiting for database..."
until php -r '
  try {
    new PDO(
      sprintf("mysql:host=%s;dbname=%s", getenv("DB_HOST") ?: "db", getenv("DB_DATABASE") ?: "oweru_tech_solutions"),
      getenv("DB_USERNAME") ?: "oweru",
      getenv("DB_PASSWORD") ?: "",
      [PDO::ATTR_TIMEOUT => 3]
    );
    exit(0);
  } catch (Throwable $e) {
    exit(1);
  }
' >/dev/null 2>&1; do
  sleep 2
done

# Only the container with MIGRATE_ON_BOOT=1 migrates — queue/scheduler/
# engine containers would otherwise race it on first boot.
if [ "$MIGRATE_ON_BOOT" = "1" ]; then
  echo "[entrypoint] migrating..."
  php artisan migrate --force
fi

php artisan storage:link || true

echo "[entrypoint] caching config + routes + views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
