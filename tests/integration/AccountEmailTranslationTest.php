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
        foreach (array('customer_new_account', 'customer_reset_password', 'customer_processing_order') as $type) {
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
