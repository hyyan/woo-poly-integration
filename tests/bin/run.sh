#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/../.."
compose=(docker compose -f tests/compose.yaml)
"${compose[@]}" exec -T wordpress phpunit -c wp-content/plugins/woo-poly-integration/phpunit-unit.xml
"${compose[@]}" exec -T -e WP_ENVIRONMENT_TYPE=local wordpress phpunit -c wp-content/plugins/woo-poly-integration/phpunit-integration.xml
"${compose[@]}" exec -T -e WP_ENVIRONMENT_TYPE=local wordpress phpunit -c wp-content/plugins/woo-poly-integration/phpunit-frontend.xml
"${compose[@]}" exec -T -e WP_ENVIRONMENT_TYPE=local wordpress phpunit -c wp-content/plugins/woo-poly-integration/phpunit-admin.xml
