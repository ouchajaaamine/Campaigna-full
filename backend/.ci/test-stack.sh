#!/usr/bin/env bash
set -e

# test-stack.sh
# Simple helper to start the test DB/Redis stack, wait for readiness,
# run Composer install + phpstan + phpunit, and tear down the stack.

# Move to backend root (script lives in backend/.ci)
cd "$(dirname "$0")/.."

COMPOSE_FILE=".ci/docker-compose.test.yml"

echo "Starting test stack via: docker compose -f ${COMPOSE_FILE} up -d"
docker compose -f "${COMPOSE_FILE}" up -d

# Wait for Postgres readiness
for i in {1..30}; do
  if command -v pg_isready >/dev/null 2>&1; then
    if pg_isready -h 127.0.0.1 -p 5432 -U app -d campaigna_test >/dev/null 2>&1; then
      echo "Postgres is ready"
      break
    fi
  else
    # fallback: inspect docker health
    STATUS="$(docker inspect --format='{{json .State.Health.Status}}' $(docker compose -f "${COMPOSE_FILE}" ps -q db) 2>/dev/null || true)"
    if [[ "$STATUS" == '"healthy"' ]]; then
      echo "Postgres container reports healthy"
      break
    fi
  fi
  echo "Waiting for Postgres... ($i)"
  sleep 2
done

# Wait for Redis readiness
for i in {1..30}; do
  if command -v redis-cli >/dev/null 2>&1; then
    if redis-cli -h 127.0.0.1 -p 6379 ping 2>/dev/null | grep -q PONG; then
      echo "Redis is ready"
      break
    fi
  else
    STATUS="$(docker inspect --format='{{json .State.Health.Status}}' $(docker compose -f "${COMPOSE_FILE}" ps -q redis) 2>/dev/null || true)"
    if [[ "$STATUS" == '"healthy"' ]]; then
      echo "Redis container reports healthy"
      break
    fi
  fi
  echo "Waiting for Redis... ($i)"
  sleep 2
done

# Export environment variables for tests
export DATABASE_URL="pgsql://app:app@127.0.0.1:5432/campaigna_test"
export REDIS_URL="redis://127.0.0.1:6379"

# Ensure build output folder exists
mkdir -p build || true

# Install PHP deps and run checks
if [ -x "$(command -v composer)" ]; then
  composer install --no-interaction --prefer-dist --no-progress
else
  echo "Composer not found in PATH — ensure Composer is installed or run inside a container"
fi

# Run phpstan and phpunit if available
if [ -x "vendor/bin/phpstan" ]; then
  vendor/bin/phpstan analyse || true
else
  echo "phpstan not found (vendor/bin/phpstan) — skipping analysis"
fi

if [ -x "vendor/bin/phpunit" ]; then
  vendor/bin/phpunit --log-junit build/junit.xml --colors=always || RC=$?
else
  echo "phpunit not found (vendor/bin/phpunit) — skipping tests"
fi

RC=${RC:-0}

# Tear down
echo "Tearing down test stack"
docker compose -f "${COMPOSE_FILE}" down -v || true

exit $RC
