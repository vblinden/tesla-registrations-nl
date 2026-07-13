#!/bin/bash
set -e

for attempt in $(seq 1 30); do
  if php artisan migrate:status --no-ansi > /dev/null 2>&1; then
    break
  fi

  echo "Database not ready, retrying in 2s... (${attempt}/30)"
  sleep 2

  if [ "$attempt" -eq 30 ]; then
    echo "Database not ready after 30 attempts."
    exit 1
  fi
done

exec php artisan rdw:sync-registrations "$@"
