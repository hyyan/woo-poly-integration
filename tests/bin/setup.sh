#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/../.."
compose=(docker compose -f tests/compose.yaml)
"${compose[@]}" up -d --wait
"${compose[@]}" exec -T wordpress sh -c '
  curl -fsSL https://github.com/wp-cli/wp-cli/releases/download/v2.12.0/wp-cli-2.12.0.phar -o /usr/local/bin/wp
  curl -fsSL https://phar.phpunit.de/phpunit-12.5.38.phar -o /usr/local/bin/phpunit
  chmod +x /usr/local/bin/wp /usr/local/bin/phpunit
'
wp=("${compose[@]}" exec -T -u www-data -e WP_CLI_CACHE_DIR=/tmp/wp-cli-cache wordpress wp)
if ! "${wp[@]}" core is-installed; then
  "${wp[@]}" core install --url=http://localhost:8098 --title='WooPoly test lab' --admin_user=lab-admin --admin_password="$(openssl rand -hex 24)" --admin_email=admin@example.test --skip-email
fi
"${wp[@]}" plugin install woocommerce --version=11.1.2 --activate
"${wp[@]}" plugin install polylang --version=3.8.10 --activate
"${wp[@]}" eval-file wp-content/plugins/woo-poly-integration/tests/setup-lab.php
"${wp[@]}" plugin activate woo-poly-integration
"${compose[@]}" exec -T wordpress php -v
"${wp[@]}" core version
"${wp[@]}" plugin list --fields=name,status,version
