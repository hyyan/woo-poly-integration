<?php
add_action('wp_loaded', static function () { if (get_option('wpi_published_account_fixture')) { $GLOBALS['wpi_account_request_before'] = array('locale' => get_locale(), 'language' => pll_current_language()); } }, 1);
add_filter('woocommerce_mail_callback_params', static function ($args, $email) {
    $f = get_option('wpi_published_account_fixture'); $user = $email->object;
    if (!$f || !($user instanceof WP_User) || !preg_match('/^account-(published|candidate)-(en|de)@example\.invalid$/D', $user->user_email)) { return $args; }
    $f['mail'][] = array('user' => $user->ID, 'type' => $email->id, 'language' => pll_current_language(), 'locale' => get_locale(), 'subject' => $args[1], 'english_heading' => strpos($args[2], 'Upgrade account heading') !== false, 'german_heading' => strpos($args[2], 'Aktualisierung Konto Titel') !== false, 'english_footer' => strpos($args[2], 'Upgrade account footer') !== false, 'german_footer' => strpos($args[2], 'Aktualisierung Konto Fuss') !== false);
    update_option('wpi_published_account_fixture', $f, false); $GLOBALS['wpi_account_request_sent'] = true; return $args;
}, 999, 2);
add_action('shutdown', static function () { if (empty($GLOBALS['wpi_account_request_sent'])) { return; } $f = get_option('wpi_published_account_fixture'); if (!$f) { return; } $f['requests'][] = array('before' => $GLOBALS['wpi_account_request_before'], 'after' => array('locale' => get_locale(), 'language' => pll_current_language())); update_option('wpi_published_account_fixture', $f, false); });
