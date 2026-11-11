#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
app_dir="$repo_root/laravel-app"
version="${1:-$(date -u +%Y%m%d%H%M%S)}"
output_dir="$repo_root/release-packages"

if [[ ! "$version" =~ ^[A-Za-z0-9._-]+$ ]]; then
    echo "Version may contain only letters, numbers, dots, underscores, and hyphens." >&2
    exit 2
fi

if [[ ! -f "$app_dir/public/build/manifest.json" ]]; then
    echo "Compiled Vite assets are missing. Run npm run build from laravel-app first." >&2
    exit 1
fi

if [[ ! -f "$app_dir/composer.lock" ]]; then
    echo "composer.lock is missing; create a locked dependency set before packaging." >&2
    exit 1
fi

if [[ ! -f "$app_dir/package-lock.json" ]]; then
    echo "package-lock.json is missing. Repair Node/npm, run npm install from laravel-app, and commit the generated lockfile before packaging." >&2
    exit 1
fi

command -v rsync >/dev/null || { echo "rsync is required to stage a clean release." >&2; exit 1; }
command -v zip >/dev/null || { echo "zip is required to create the release archive." >&2; exit 1; }
command -v composer >/dev/null || { echo "Composer is required to install production dependencies." >&2; exit 1; }

mkdir -p "$output_dir"
stage="$(mktemp -d)"
trap 'rm -rf "$stage"' EXIT
package_dir="$stage/janjgir-egov-$version"
mkdir -p "$package_dir"

rsync -a --delete \
    --include='/.env.example' \
    --exclude='/.env' \
    --exclude='/.env.*' \
    --exclude='/.git/' \
    --exclude='/node_modules/' \
    --exclude='/tests/' \
    --exclude='/vendor/' \
    --exclude='/.phpunit.cache/' \
    --exclude='/public/hot' \
    --exclude='/storage/app/private/***' \
    --exclude='/storage/logs/*.log' \
    --exclude='/bootstrap/cache/*.php' \
    "$app_dir/" "$package_dir/"

cp "$repo_root/DEPLOYMENT.md" "$package_dir/DEPLOYMENT.md"
cp "$repo_root/ADMIN_GUIDE.md" "$package_dir/ADMIN_GUIDE.md"
cp "$repo_root/SECURITY.md" "$package_dir/SECURITY.md"
cp "$app_dir/THIRD_PARTY_NOTICES.md" "$package_dir/THIRD_PARTY_NOTICES.md"

composer install \
    --working-dir="$package_dir" \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

mkdir -p \
    "$package_dir/storage/app/private/media" \
    "$package_dir/storage/framework/cache/data" \
    "$package_dir/storage/framework/sessions" \
    "$package_dir/storage/framework/views" \
    "$package_dir/storage/logs" \
    "$package_dir/bootstrap/cache"

archive="$output_dir/janjgir-egov-$version.zip"
rm -f "$archive"
(cd "$stage" && zip -qr "$archive" "janjgir-egov-$version")

echo "Created $archive"
echo "Review archive contents and test this package on staging before deployment."
