#!/usr/bin/env bash
set -euo pipefail

# All commands run inside the dedicated manual store container.
wp() {
    php -d error_reporting='E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED' /tools/wp.phar --allow-root --path=/var/www/html --url=http://localhost:8097 "$@"
}

mkdir -p /tools
if [[ ! -f /tools/wp.phar ]]; then
    curl -fsSL https://github.com/wp-cli/wp-cli/releases/download/v2.12.0/wp-cli-2.12.0.phar -o /tools/wp.phar
fi

# Wait for the WordPress container to populate its shared volume.
for attempt in {1..30}; do
    if [[ -f wp-config.php ]]; then break; fi
    sleep 2
done
[[ -f wp-config.php ]] || { echo 'WordPress did not initialize.' >&2; exit 1; }

if ! wp core is-installed; then
    wp core install \
        --url=http://localhost:8097 \
        --title='WooPoly manual store' \
        --admin_user=manual-admin \
        --admin_password=manual-password \
        --admin_email=manual-admin@example.invalid \
        --skip-email
fi

wp eval 'if ($GLOBALS["wpdb"]->prefix !== "wpi_manual_") { throw new RuntimeException("Dedicated manual store required"); }'

mkdir -p wp-content/mu-plugins /var/www/manual-mail
cp /workspace/tests/manual/mail.php wp-content/mu-plugins/wpi-manual-mail.php
chown www-data:www-data /var/www/manual-mail

if ! wp option get wpi_manual_fixture >/dev/null 2>&1; then
    wp core update --version=7.1.3
    wp language core install de_DE
    wp plugin install woocommerce --version=11.1.2 --activate
    wp language plugin install woocommerce de_DE
    wp plugin install polylang --version=3.8.10 --activate
    wp eval-file /workspace/tests/setup-lab.php

fi

# Format changes retain the installed plugin version and existing fixture data.
if [[ -n "${WPI_MANUAL_MODE:-}" ]]; then
    wp eval-file /workspace/tests/manual/seed.php
    exit
fi

# Copy the package out of the source mount. The repository stays read only.
cp /workspace/work/manual-lab/candidate.zip /tmp/manual-plugin.zip
wp plugin install /tmp/manual-plugin.zip --force --activate
rm /tmp/manual-plugin.zip

wp eval-file /workspace/tests/manual/seed.php
wp rewrite flush --hard
chown -R www-data:www-data /var/www/html

printf '\nManual store is ready.\n'
printf 'English shop: http://localhost:8097/shop-classic-en/\n'
printf 'German shop: http://localhost:8097/de/shop-classic-de/\n'
printf 'Admin: http://localhost:8097/wp-admin/\n'
printf 'Users: manual-admin, manual-manager, manual-customer, manual-subscriber\n'
printf 'Password for every manual account: manual-password\n'
