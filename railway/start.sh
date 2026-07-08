#!/bin/bash
set -e

run_migrations() {
  for attempt in $(seq 1 30); do
    if php artisan migrate --force; then
      return 0
    fi

    echo "Database not ready, retrying in 2s... (${attempt}/30)"
    sleep 2
  done

  echo "Database migrations failed after 30 attempts."
  return 1
}

case "${RAILWAY_SERVICE_NAME}" in
  scheduler)
    exec php artisan schedule:work
    ;;
  worker)
    exec php artisan queue:work --sleep=3 --tries=3 --max-time=3600
    ;;
  *)
    run_migrations
    php artisan config:cache
    exec /start-container.sh
    ;;
esac
