# Translation findings awaiting explicit acceptance

These 44 installed Plugin Check errors are still open. No exception has been accepted and no checker suppression is proposed. The current release scan retains all of them.

Thirty findings require the WooCommerce catalog for existing WooCommerce page titles, slugs, bank labels, privacy defaults, coupon and stock messages, or stored value fallbacks. Eleven findings concern the same dynamic stored value fallbacks, which primarily use Polylang registered string translations. Three findings concern existing plugin catalog lookup keys with unnumbered or mixed placeholders. Changing these domains or keys would change the existing translation contract. Existing catalog and reordered format regressions are in TranslationContractTest, native configured string and gateway evidence, and current output security tests. These findings are translation extraction and ownership diagnostics, not evidence of an unescaped output or unverified mutation.

| File | Line | Code | Current source |
| --- | --- | --- | --- |
| src/Hyyan/WPI/Plugin.php | 426 | TextDomainMismatch | `$post_name		 = _x( 'shop', 'Page slug', 'woocommerce' );` |
| src/Hyyan/WPI/Plugin.php | 427 | TextDomainMismatch | `$post_title		 = _x( 'Shop', 'Page title', 'woocommerce' );` |
| src/Hyyan/WPI/Plugin.php | 431 | TextDomainMismatch | `$post_name		 = _x( 'cart', 'Page slug', 'woocommerce' );` |
| src/Hyyan/WPI/Plugin.php | 432 | TextDomainMismatch | `$post_title		 = _x( 'Cart', 'Page title', 'woocommerce' );` |
| src/Hyyan/WPI/Plugin.php | 438 | TextDomainMismatch | `$post_name		 = _x( 'checkout', 'Page slug', 'woocommerce' );` |
| src/Hyyan/WPI/Plugin.php | 439 | TextDomainMismatch | `$post_title		 = _x( 'Checkout', 'Page title', 'woocommerce' );` |
| src/Hyyan/WPI/Plugin.php | 445 | TextDomainMismatch | `$post_name		 = _x( 'my-account', 'Page slug', 'woocommerce' );` |
| src/Hyyan/WPI/Plugin.php | 446 | TextDomainMismatch | `$post_title		 = _x( 'My account', 'Page title', 'woocommerce' );` |
| src/Hyyan/WPI/Gateways/GatewayCOD.php | 36 | NonSingularStringLiteralText | `echo wp_kses_post(wpautop(wptexturize(function_exists('pll__') ? pll__($this->instructions) : __($this->instructions, 'woocommerce'))));` |
| src/Hyyan/WPI/Gateways/GatewayCOD.php | 36 | TextDomainMismatch | `echo wp_kses_post(wpautop(wptexturize(function_exists('pll__') ? pll__($this->instructions) : __($this->instructions, 'woocommerce'))));` |
| src/Hyyan/WPI/Gateways/GatewayCOD.php | 53 | NonSingularStringLiteralText | `echo wp_kses_post(wpautop(wptexturize(function_exists('pll__') ? pll__($this->instructions) : __($this->instructions, 'woocommerce')))).PHP_EOL;` |
| src/Hyyan/WPI/Gateways/GatewayCOD.php | 53 | TextDomainMismatch | `echo wp_kses_post(wpautop(wptexturize(function_exists('pll__') ? pll__($this->instructions) : __($this->instructions, 'woocommerce')))).PHP_EOL;` |
| src/Hyyan/WPI/Gateways/GatewayCheque.php | 36 | NonSingularStringLiteralText | `echo wp_kses_post(wpautop(wptexturize(function_exists('pll__') ? pll__($this->instructions) : __($this->instructions, 'woocommerce'))));` |
| src/Hyyan/WPI/Gateways/GatewayCheque.php | 36 | TextDomainMismatch | `echo wp_kses_post(wpautop(wptexturize(function_exists('pll__') ? pll__($this->instructions) : __($this->instructions, 'woocommerce'))));` |
| src/Hyyan/WPI/Gateways/GatewayCheque.php | 53 | NonSingularStringLiteralText | `echo wp_kses_post(wpautop(wptexturize(function_exists('pll__') ? pll__($this->instructions) : __($this->instructions, 'woocommerce')))).PHP_EOL;` |
| src/Hyyan/WPI/Gateways/GatewayCheque.php | 53 | TextDomainMismatch | `echo wp_kses_post(wpautop(wptexturize(function_exists('pll__') ? pll__($this->instructions) : __($this->instructions, 'woocommerce')))).PHP_EOL;` |
| src/Hyyan/WPI/Gateways/GatewayBACS.php | 35 | NonSingularStringLiteralText | `echo wp_kses_post(wpautop(wptexturize(function_exists('pll__') ? pll__($this->instructions) : __($this->instructions, 'woocommerce'))));` |
| src/Hyyan/WPI/Gateways/GatewayBACS.php | 35 | TextDomainMismatch | `echo wp_kses_post(wpautop(wptexturize(function_exists('pll__') ? pll__($this->instructions) : __($this->instructions, 'woocommerce'))));` |
| src/Hyyan/WPI/Gateways/GatewayBACS.php | 54 | NonSingularStringLiteralText | `echo wp_kses_post(wpautop(wptexturize(function_exists('pll__') ? pll__($this->instructions) : __($this->instructions, 'woocommerce')))).PHP_EOL;` |
| src/Hyyan/WPI/Gateways/GatewayBACS.php | 54 | TextDomainMismatch | `echo wp_kses_post(wpautop(wptexturize(function_exists('pll__') ? pll__($this->instructions) : __($this->instructions, 'woocommerce')))).PHP_EOL;` |
| src/Hyyan/WPI/Gateways/GatewayBACS.php | 83 | TextDomainMismatch | `$sortcode = isset($locale[ $country ]['sortcode']['label']) ? $locale[ $country ]['sortcode']['label'] : __('Sort code', 'woocommerce');` |
| src/Hyyan/WPI/Gateways/GatewayBACS.php | 103 | TextDomainMismatch | `'label' => __('Bank', 'woocommerce'),` |
| src/Hyyan/WPI/Gateways/GatewayBACS.php | 107 | TextDomainMismatch | `'label' => __('Account number', 'woocommerce'),` |
| src/Hyyan/WPI/Gateways/GatewayBACS.php | 115 | TextDomainMismatch | `'label' => __('IBAN', 'woocommerce'),` |
| src/Hyyan/WPI/Gateways/GatewayBACS.php | 119 | TextDomainMismatch | `'label' => __('BIC', 'woocommerce'),` |
| src/Hyyan/WPI/Gateways/GatewayBACS.php | 135 | TextDomainMismatch | `echo '<section class="woocommerce-bacs-bank-details"><h2 class="wc-bacs-bank-details-heading">' . esc_html__('Our bank details', 'woocommerce') . '</h2>' . PHP_EOL . wp_kses_post($account_html) . '</section>';` |
| src/Hyyan/WPI/Privacy.php | 36 | TextDomainMismatch | `$this->registerString('woocommerce_checkout_privacy_policy_text', get_option('woocommerce_checkout_privacy_policy_text', sprintf(__('Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our %s.', 'woocommerce'), '[privacy_policy]')));` |
| src/Hyyan/WPI/Privacy.php | 38 | TextDomainMismatch | `$this->registerString('woocommerce_registration_privacy_policy_text', get_option('woocommerce_registration_privacy_policy_text', sprintf(__('Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our %s.', 'woocommerce'), '[privacy_policy]')));` |
| src/Hyyan/WPI/Cart.php | 240 | TextDomainMismatch | `__('You cannot add that amount of &quot;%1$s&quot; to the cart because there is not enough stock (%2$s remaining).', 'woocommerce'),` |
| src/Hyyan/WPI/Shipping.php | 161 | NonSingularStringLiteralText | `return function_exists('pll__') ? pll__($label) : __($label, 'woocommerce');` |
| src/Hyyan/WPI/Shipping.php | 161 | TextDomainMismatch | `return function_exists('pll__') ? pll__($label) : __($label, 'woocommerce');` |
| src/Hyyan/WPI/Shipping.php | 185 | NonSingularStringLiteralText | `$translated[] = __($shipping, 'woocommerce');` |
| src/Hyyan/WPI/Shipping.php | 185 | TextDomainMismatch | `$translated[] = __($shipping, 'woocommerce');` |
| src/Hyyan/WPI/Admin/StatusReport.php | 28 | TextDomainMismatch | `<td class="help"><?php echo wp_kses( wc_help_tip( esc_html__( 'The current language used by WordPress. Default = English', 'woocommerce' ) ), $allowed_tip_html ); ?></td>` |
| src/Hyyan/WPI/Gateways.php | 234 | NonSingularStringLiteralText | `return function_exists('pll__') ? pll__($title) : __($title, 'woocommerce');` |
| src/Hyyan/WPI/Gateways.php | 234 | TextDomainMismatch | `return function_exists('pll__') ? pll__($title) : __($title, 'woocommerce');` |
| src/Hyyan/WPI/Gateways.php | 247 | NonSingularStringLiteralText | `return function_exists('pll__') ? pll__($description) : __($description, 'woocommerce');` |
| src/Hyyan/WPI/Gateways.php | 247 | TextDomainMismatch | `return function_exists('pll__') ? pll__($description) : __($description, 'woocommerce');` |
| src/Hyyan/WPI/Gateways.php | 261 | NonSingularStringLiteralText | `$total_rows['payment_method']['value'] = function_exists('pll__') ? pll__($total_rows['payment_method']['value']) : __($total_rows['payment_method']['value'], 'woocommerce');` |
| src/Hyyan/WPI/Gateways.php | 261 | TextDomainMismatch | `$total_rows['payment_method']['value'] = function_exists('pll__') ? pll__($total_rows['payment_method']['value']) : __($total_rows['payment_method']['value'], 'woocommerce');` |
| src/Hyyan/WPI/Coupon.php | 119 | TextDomainMismatch | `return sprintf(esc_html__('Coupon: %s', 'woocommerce'), pll__($code));` |
| src/Hyyan/WPI/Views/Messages/endpointsTranslations.php | 12 | UnorderedPlaceholdersText | `__( 'You can translate woocommerce endpoints, email strings, shipping methods from polylang strings tab. <a target="_blank" href="%s">%s</a>', 'woo-poly-integration'),` |
| src/Hyyan/WPI/Tools/TranslationsDownloader.php | 46 | MixedOrderedPlaceholdersText | `'WooCommerce translation %s can not be found in : <a href="%2$s">%2$s</a>', 'woo-poly-integration'` |
| src/Hyyan/WPI/Tools/TranslationsDownloader.php | 56 | MixedOrderedPlaceholdersText | `__('Unable to download WooCommerce translation %s from : <a href="%2$s">%2$s</a>', 'woo-poly-integration'), esc_html(sprintf('%s(%s)', $name, $locale)), esc_url(static::getRepoUrl())` |

Acceptance would retain exactly these 44 findings with their existing catalog contracts. It would not waive the other remaining findings, change the scan result, certify directory approval or permit blanket suppressions. The goal remains active until every other finding is corrected or separately and explicitly accepted.
