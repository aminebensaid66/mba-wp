#!/usr/bin/env sh
set -eu

cd "$(dirname "$0")/.."

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker is required." >&2
  exit 1
fi

case "${1:-}" in
  --yes) ;;
  *)
    echo "This deletes only the local Docker database/media volumes for this project."
    printf "Type 'reset' to continue: "
    read -r answer
    [ "$answer" = "reset" ] || { echo "Cancelled."; exit 1; }
    ;;
esac

docker compose down --volumes --remove-orphans
rm -f .env
echo "Local containers, database, media volume, and .env removed. Run ./bin/setup.sh to rebuild."
