#!/bin/bash
set -e

case "${RAILWAY_SERVICE_NAME}" in
  scheduler)
    exec php artisan schedule:work
    ;;
  worker)
    exec php artisan queue:work --sleep=3 --tries=3 --max-time=3600
    ;;
  *)
    exec /start-container.sh
    ;;
esac
