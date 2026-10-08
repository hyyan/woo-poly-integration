<?php
add_filter('woocommerce_mail_callback_params', static function ($args, $email) {
    $f = get_option('wpi_published_modern_fixture'); $order = $email->object;
    if (!$f || !($order instanceof WC_Order) || $order->get_customer_id() !== (int) $f['user']) { return $args; }
    $f['emails'][] = array('order' => $order->get_id(), 'type' => $email->id, 'language' => pll_current_language(), 'locale' => get_locale(), 'subject' => $args[1], 'english_heading' => strpos($args[2], 'Upgrade order heading') !== false, 'german_heading' => strpos($args[2], 'Aktualisierung Bestellung Titel') !== false, 'english_footer' => strpos($args[2], 'Upgrade order footer') !== false, 'german_footer' => strpos($args[2], 'Aktualisierung Bestellung Fuss') !== false);
    update_option('wpi_published_modern_fixture', $f, false); return $args;
}, 999, 2);
