<?php
if ($GLOBALS['wpdb']->prefix !== 'wpi_package_' || wp_get_environment_type() !== 'local' || has_filter('pre_wp_mail', '__return_true') === false) { throw new RuntimeException('Owned account store with suppressed transport required'); }
$phase = getenv('WPI_ACCOUNT_PHASE'); $key = 'wpi_published_account_fixture'; $f = get_option($key);
if ($phase === 'prepare') {
    if ($f || Hyyan\WPI\Plugin::getVersion() !== '1.5.0') { throw new RuntimeException('Clean published account baseline required'); }
    $f = array('options' => array(), 'translations' => array(), 'mail' => array(), 'requests' => array(), 'baseline' => array());
    foreach (array('woocommerce_enable_myaccount_registration', 'woocommerce_registration_generate_username', 'woocommerce_registration_generate_password', 'woocommerce_customer_new_account_settings', 'woocommerce_customer_reset_password_settings', 'woocommerce_email_footer_text', 'hyyan-wpi-flash-messages') as $name) { $f['options'][$name] = get_option($name, null); }
    update_option($key, $f, false);
    foreach (array('published', 'candidate') as $release) { foreach (array('en', 'de') as $lang) { if (get_user_by('email', 'account-' . $release . '-' . $lang . '@example.invalid')) { throw new RuntimeException('Owned account email exists'); } } }
    foreach (array('en', 'de') as $lang) {
        $language = PLL()->model->get_language($lang); $f['translations'][$lang] = array('exists' => metadata_exists('term', $language->term_id, '_pll_strings_translations'), 'value' => get_term_meta($language->term_id, '_pll_strings_translations', true));
        $mo = new PLL_MO(); $mo->import_from_db($language);
        foreach (array('Upgrade account subject' => 'Aktualisierung Konto Betreff', 'Upgrade account heading' => 'Aktualisierung Konto Titel', 'Upgrade account footer' => 'Aktualisierung Konto Fuss') as $source => $target) { $mo->add_entry(new Translation_Entry(array('singular' => $source, 'translations' => array($lang === 'de' ? $target : $source)))); } $mo->export_to_db($language);
    }
    foreach (array('customer_new_account', 'customer_reset_password') as $type) { $name = 'woocommerce_' . $type . '_settings'; $settings = (array) get_option($name); $settings['enabled'] = 'yes'; $settings['subject'] = 'Upgrade account subject'; $settings['heading'] = 'Upgrade account heading'; update_option($name, $settings); }
    foreach (array('woocommerce_enable_myaccount_registration', 'woocommerce_registration_generate_username', 'woocommerce_registration_generate_password') as $name) { update_option($name, 'yes'); }
    update_option('woocommerce_email_footer_text', 'Upgrade account footer'); update_option($key, $f, false);
}
if (!$f) { throw new RuntimeException('Owned account notification fixture missing'); }
if ($phase === 'prepare' || $phase === 'urls') {
    echo wp_json_encode(array('account_urls' => array('en' => get_permalink(pll_get_post(wc_get_page_id('myaccount'), 'en')), 'de' => get_permalink(pll_get_post(wc_get_page_id('myaccount'), 'de'))))), "\n"; return;
}
$users = array();
foreach (array('published', 'candidate') as $release) { foreach (array('en', 'de') as $lang) { $user = get_user_by('email', 'account-' . $release . '-' . $lang . '@example.invalid'); if ($user) { $users[$release][$lang] = $user; } } }
if ($phase === 'cleanup') {
    require_once ABSPATH . 'wp-admin/includes/user.php'; $count = 0;
    foreach ($users as $pair) { foreach ($pair as $user) { if ($user->roles !== array('customer')) { throw new RuntimeException('Owned customer role mismatch'); } $GLOBALS['wpdb']->delete($GLOBALS['wpdb']->prefix . 'woocommerce_sessions', array('session_key' => (string) $user->ID), array('%s')); wp_delete_user($user->ID); if (get_userdata($user->ID)) { throw new RuntimeException('Owned customer remained'); } ++$count; } }
    foreach ($f['translations'] as $lang => $value) { $id = PLL()->model->get_language($lang)->term_id; if ($value['exists']) { update_term_meta($id, '_pll_strings_translations', wp_slash($value['value'])); } else { delete_term_meta($id, '_pll_strings_translations'); } if (get_term_meta($id, '_pll_strings_translations', true) !== $value['value']) { throw new RuntimeException('Owned strings restoration mismatch'); } }
    foreach ($f['options'] as $name => $value) { if ($value === null) { delete_option($name); } else { update_option($name, $value); } if (get_option($name, null) !== $value) { throw new RuntimeException('Owned account option restoration mismatch'); } }
    delete_option($key); echo wp_json_encode(array('removed_customers' => $count, 'options_restored' => true, 'translations_restored' => true)), "\n"; return;
}
if (!in_array($phase, array('capture', 'verify', 'complete'), true) || ($phase === 'capture' ? Hyyan\WPI\Plugin::getVersion() !== '1.5.0' : Hyyan\WPI\Plugin::getVersion() !== '1.5.1')) { throw new RuntimeException('Account verification phase mismatch'); }
foreach ($f['requests'] as $request) { if ($request['before'] !== $request['after']) { throw new RuntimeException('Native account request context leaked'); } }
$expected = $phase === 'complete' ? array('published', 'candidate') : array('published'); $actual = array();
foreach ($expected as $release) {
    if (!isset($users[$release]) || count($users[$release]) !== 2) { throw new RuntimeException('Native bilingual accounts missing'); }
    foreach ($users[$release] as $lang => $user) {
        if ($user->roles !== array('customer')) { throw new RuntimeException('Native account role changed'); }
        $actual[$user->ID] = array('login' => $user->user_login, 'email' => $user->user_email, 'registered' => $user->user_registered, 'password_hash' => $user->user_pass, 'roles' => $user->roles, 'locale' => get_user_meta($user->ID, 'locale', true), 'activation_key_hash' => hash('sha256', $user->user_activation_key));
        $types = array();
        foreach ($f['mail'] as $mail) { if ($mail['user'] !== $user->ID) { continue; } $german = $lang === 'de';
            if ($release === 'candidate' && ($mail['language'] !== $lang || $mail['subject'] !== ($german ? 'Aktualisierung Konto Betreff' : 'Upgrade account subject') || $mail['english_heading'] !== !$german || $mail['german_heading'] !== $german || $mail['english_footer'] !== !$german || $mail['german_footer'] !== $german)) { throw new RuntimeException('Native account mail localization mismatch'); } $types[] = $mail['type'];
        }
        sort($types); if ($types !== array('customer_new_account', 'customer_reset_password')) { throw new RuntimeException('Native account mail pair missing'); }
    }
}
if ($phase === 'capture') { $f['baseline'] = $actual; update_option($key, $f, false); }
else { if (count($f['baseline']) !== 2) { throw new RuntimeException('Published customer snapshot missing'); } foreach ($f['baseline'] as $id => $value) { if (!isset($actual[$id]) || $actual[$id] !== $value) { throw new RuntimeException('Published customer changed after update'); } } }
echo wp_json_encode(array('version' => Hyyan\WPI\Plugin::getVersion(), 'native_accounts' => count($actual), 'native_account_mails' => count($f['mail']), 'published_customers_retained' => $phase !== 'capture', 'mail' => $f['mail'], 'requests' => $f['requests'])), "\n";
