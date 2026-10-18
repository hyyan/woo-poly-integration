<?php
use Hyyan\WPI\Emails;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AccountEmailTranslationTest extends TestCase
{
    private array $settings = array();
    private array $translations = array();
    private $originalLanguage;
    private int $userId;

    protected function setUp(): void
    {
        $this->originalLanguage = PLL()->curlang;
        $this->userId = wp_insert_user(array('user_login' => 'email-regression-' . wp_generate_password(8, false),
            'user_pass' => wp_generate_password(30), 'user_email' => 'email-regression@example.test', 'role' => 'customer'));
        foreach (array('en', 'de') as $language) {
            $object = PLL()->model->get_language($language);
            $mo = new PLL_MO();
            $mo->import_from_db($object);
            $this->translations[$language] = clone $mo;
            $mo->add_entry(new Translation_Entry(array(
                'singular' => 'Email regression {site_title}',
                'translations' => array($language === 'de' ? 'E-Mail Pruefung {site_title}' : 'Email regression {site_title}')
            )));
            $mo->export_to_db($object);
        }
        foreach (array('customer_new_account', 'customer_reset_password', 'customer_processing_order', 'customer_cancelled_order', 'customer_failed_order') as $type) {
            $key = 'woocommerce_' . $type . '_settings';
            $this->settings[$key] = get_option($key);
            update_option($key, array('subject' => 'Email regression {site_title}',
                'heading' => 'Email regression {site_title}', 'additional_content' => 'Email regression {site_title}'));
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->settings as $key => $value) {
            if ($value === false) {
                delete_option($key);
            } else {
                update_option($key, $value);
            }
        }
        foreach ($this->translations as $language => $mo) {
            $mo->export_to_db(PLL()->model->get_language($language));
        }
        require_once ABSPATH . 'wp-admin/includes/user.php';
        wp_delete_user($this->userId);
        PLL()->curlang = $this->originalLanguage;
        restore_previous_locale();
    }

    #[DataProvider('accountFields')]
    public function testAccountTemplatesTranslateAndKeepNativePlaceholderFormatting(string $class, string $field): void
    {
        $email = WC()->mailer()->get_emails()[$class];
        foreach (array('en', 'de') as $language) {
            update_user_meta($this->userId, 'locale', $language === 'de' ? 'de_DE' : 'en_US');
            $handler = (new ReflectionClass(Emails::class))->newInstanceWithoutConstructor();
            $source = $email->format_string('Email regression {site_title}');
            $expected = $email->format_string($language === 'de' ? 'E-Mail Pruefung {site_title}' : 'Email regression {site_title}');
            self::assertSame($expected, $handler->translateEmailStringToObjectLanguage(
                $source, new WP_User($this->userId), $field, $email));
        }
    }

    public static function accountFields(): array
    {
        $cases = array();
        foreach (array('WC_Email_Customer_New_Account', 'WC_Email_Customer_Reset_Password') as $class) {
            foreach (array('subject', 'heading', 'additional_content') as $field) {
                $cases[$class . ' ' . $field] = array($class, $field);
            }
        }
        return $cases;
    }

    #[DataProvider('nativeOrderEmails')]
    public function testNativeOrderEmailRestoresTheCallerLanguage(string $class): void
    {
        $language = PLL()->curlang;
        $direction = $GLOBALS['text_direction'] ?? null;
        $strings = $GLOBALS['l10n']['pll_string'] ?? null;
        $locale = get_locale();
        $order = wc_create_order();
        $observed = array();
        $email = WC()->mailer()->get_emails()[$class];
        $enabled = $email->enabled;
        $email->enabled = 'yes';
        $suppress = static function () use (&$observed) {
            $observed[] = array(get_locale(), pll_current_language());
            return true;
        };
        add_filter('pre_wp_mail', $suppress);
        try {
            $order->set_billing_email('email-regression@example.test');
            $order->save();
            pll_set_post_language($order->get_id(), 'de');
            $email->trigger($order->get_id());
            self::assertSame(array(array('de_DE', 'de')), $observed);
            self::assertSame($locale, get_locale());
            self::assertSame($language, PLL()->curlang);
            self::assertSame($direction, $GLOBALS['text_direction'] ?? null);
        } finally {
            $email->enabled = $enabled;
            $order->delete(true);
            remove_filter('pre_wp_mail', $suppress);
            restore_previous_locale();
            PLL()->curlang = $language;
            if ($direction === null) { unset($GLOBALS['text_direction']); }
            else { $GLOBALS['text_direction'] = $direction; }
            if ($strings === null) { unset($GLOBALS['l10n']['pll_string']); }
            else { $GLOBALS['l10n']['pll_string'] = $strings; }
        }
    }

    #[DataProvider('legacyShopEmails')]
    public function testShopEmailRestoresWithoutTheLaterCompletionAction(string $class): void
    {
        $language = PLL()->curlang;
        $locale = get_locale();
        $sentHook = $GLOBALS['wp_filter']['woocommerce_email_sent'] ?? null;
        unset($GLOBALS['wp_filter']['woocommerce_email_sent']);
        $handler = null;
        foreach ($sentHook->callbacks as $callbacks) {
            foreach ($callbacks as $registered) {
                if (is_array($registered['function']) && $registered['function'][0] instanceof Emails) {
                    $handler = $registered['function'][0];
                }
            }
        }
        self::assertInstanceOf(Emails::class, $handler);
        $wrap = new ReflectionMethod(Emails::class, 'wrapMailCallback');
        $legacyCallback = static function ($callback) use ($wrap, $handler) {
            return $wrap->invoke($handler, $callback);
        };
        add_filter('woocommerce_mail_callback', $legacyCallback);
        $order = wc_create_order();
        pll_set_post_language($order->get_id(), 'de');
        $observed = array();
        $suppress = static function () use (&$observed) {
            $observed[] = pll_current_language();
            return true;
        };
        add_filter('pre_wp_mail', $suppress);
        $email = WC()->mailer()->get_emails()[$class];
        $enabled = $email->enabled;
        $recipient = $email->recipient;
        try {
            $email->enabled = 'yes';
            $email->recipient = 'email-regression@example.test';
            $email->object = $order;
            $email->send($email->get_recipient(), $email->get_subject(), $email->get_content(),
                $email->get_headers(), $email->get_attachments());
            self::assertSame(array('de'), $observed);
            self::assertSame($locale, get_locale());
            self::assertSame($language, PLL()->curlang);
        } finally {
            $email->enabled = $enabled;
            $email->recipient = $recipient;
            $order->delete(true);
            remove_filter('pre_wp_mail', $suppress);
            remove_filter('woocommerce_mail_callback', $legacyCallback);
            if ($sentHook !== null) { $GLOBALS['wp_filter']['woocommerce_email_sent'] = $sentHook; }
            restore_previous_locale();
            PLL()->curlang = $language;
        }
    }

    public static function legacyShopEmails(): array
    {
        return array(array('WC_Email_New_Order'), array('WC_Email_Cancelled_Order'), array('WC_Email_Failed_Order'));
    }

    public static function nativeOrderEmails(): array
    {
        return array(
            array('WC_Email_Customer_Processing_Order'),
            array('WC_Email_Customer_On_Hold_Order'),
            array('WC_Email_New_Order'),
            array('WC_Email_Customer_Cancelled_Order'),
            array('WC_Email_Customer_Failed_Order'),
        );
    }

    #[DataProvider('nestedOrderEmails')]
    public function testNestedNativeEmailRestoresOuterAndCallerLanguages(string $innerClass): void
    {
        $language = PLL()->curlang;
        $direction = $GLOBALS['text_direction'] ?? null;
        $strings = $GLOBALS['l10n']['pll_string'] ?? null;
        $locale = get_locale();
        $orders = array();
        $observed = array();
        $emails = WC()->mailer()->get_emails();
        foreach (array('de', 'en') as $slug) {
            $order = wc_create_order();
            $order->set_billing_email('email-regression@example.test');
            $order->save();
            pll_set_post_language($order->get_id(), $slug);
            $orders[$slug] = $order;
        }
        $suppress = static function () use (&$observed, $emails, $orders, $innerClass) {
            $observed[] = array(get_locale(), pll_current_language());
            if (count($observed) === 1) {
                $emails[$innerClass]->trigger($orders['en']->get_id());
                self::assertSame('de_DE', get_locale());
                self::assertSame('de', pll_current_language());
            }
            return true;
        };
        add_filter('pre_wp_mail', $suppress);
        try {
            $emails['WC_Email_Customer_Processing_Order']->trigger($orders['de']->get_id());
            self::assertSame(array(array('de_DE', 'de'), array('en_US', 'en')), $observed);
            self::assertSame($locale, get_locale());
            self::assertSame($language, PLL()->curlang);
            self::assertSame($direction, $GLOBALS['text_direction'] ?? null);
        } finally {
            foreach ($orders as $order) { $order->delete(true); }
            remove_filter('pre_wp_mail', $suppress);
            restore_previous_locale();
            PLL()->curlang = $language;
            if ($direction === null) { unset($GLOBALS['text_direction']); }
            else { $GLOBALS['text_direction'] = $direction; }
            if ($strings === null) { unset($GLOBALS['l10n']['pll_string']); }
            else { $GLOBALS['l10n']['pll_string'] = $strings; }
        }
    }

    public static function nestedOrderEmails(): array
    {
        return array(array('WC_Email_Customer_On_Hold_Order'), array('WC_Email_New_Order'));
    }

    public function testOrderEmailTranslationRemainsUnchanged(): void
    {
        $order = wc_create_order();
        pll_set_post_language($order->get_id(), 'de');
        try {
            $email = WC()->mailer()->get_emails()['WC_Email_Customer_Processing_Order'];
            $handler = (new ReflectionClass(Emails::class))->newInstanceWithoutConstructor();
            self::assertSame($email->format_string('E-Mail Pruefung {site_title}'),
                $handler->translateEmailStringToObjectLanguage(
                    $email->format_string('Email regression {site_title}'), $order, 'additional_content', $email));
        } finally {
            $order->delete(true);
        }
    }
}
