<?php
if (!in_array($GLOBALS['wpdb']->prefix, array('wpi_package_', 'wpi_upgrade_'), true)) {
    return;
}
if (!isset($_GET['wpi_locale_probe'])) {
    return;
}
add_action('init', static function () {
    $render = static function () {
        PLL()->curlang = PLL()->model->get_language('de');
        $formatter = (new ReflectionClass('Hyyan\\WPI\\LocaleNumbers'))->newInstanceWithoutConstructor();
        $context = new ReflectionMethod($formatter, 'isFrontendPriceContext');
        $context->setAccessible(true);
        $intl = class_exists('NumberFormatter');
        wp_send_json(array(
            'hook' => current_filter(),
            'admin' => is_admin(),
            'logged_in' => is_user_logged_in(),
            'intl_available' => $intl,
            'frontend_price_context' => $context->invoke($formatter),
            'separator' => $intl ? $formatter->getLocaleDecimalSeparator('.') : wc_get_price_decimal_separator(),
            'number' => $intl ? $formatter->getLocalizedDecimal('1234.5', 1234.5) : wc_format_localized_decimal(1234.5),
        ));
    };
    foreach (array('woocs_get_products_price_html', 'woocs_get_variation_products_price_html', 'woocs_get_custom_price_html', 'woo_poly_integration_context_fixture') as $action) {
        add_action('wp_ajax_' . $action, $render);
        add_action('wp_ajax_nopriv_' . $action, $render);
    }
});
