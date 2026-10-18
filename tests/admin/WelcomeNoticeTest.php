<?php
use Hyyan\WPI\Plugin;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class WelcomeNoticeTest extends TestCase
{
    private int $user;
    private $screen;
    private array $welcomeVersion;
    private $noticeHook;

    protected function setUp(): void
    {
        $this->user = get_current_user_id();
        $this->screen = $GLOBALS['current_screen'] ?? null;
        $this->noticeHook = $GLOBALS['wp_filter']['admin_notices'];
        $GLOBALS['wp_filter']['admin_notices'] = new WP_Hook();
        // Exercise the plugin's registered callback without unrelated plugin notices.
        foreach ($this->noticeHook->callbacks as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $function = $callback['function'];
                if (is_array($function) && $function[0] instanceof Plugin && $function[1] === 'showWelcomeNotice') {
                    add_action('admin_notices', $function, $priority, $callback['accepted_args']);
                }
            }
        }
        self::assertTrue(has_action('admin_notices'));
        $this->welcomeVersion = get_user_meta(1, 'wpi_welcome_version', false);
        delete_user_meta(1, 'wpi_welcome_version');
        wp_set_current_user(1);
        $GLOBALS['current_screen'] = WP_Screen::get('plugins');
    }

    protected function tearDown(): void
    {
        delete_user_meta(1, 'wpi_welcome_version');
        foreach ($this->welcomeVersion as $value) {
            add_user_meta(1, 'wpi_welcome_version', $value);
        }
        wp_set_current_user($this->user);
        $GLOBALS['current_screen'] = $this->screen;
        $GLOBALS['wp_filter']['admin_notices'] = $this->noticeHook;
    }

    private function renderNotices(): string
    {
        ob_start();
        try {
            do_action('admin_notices');
            return ob_get_contents();
        } finally {
            ob_end_clean();
        }
    }

    public function testFirstVisitShowsOptionalLinksOnlyOnce(): void
    {
        $output = $this->renderNotices();
        self::assertStringContainsString('Thanks for using WooPoly', $output);
        self::assertStringContainsString('notice notice-info is-dismissible', $output);
        self::assertStringContainsString('https://github.com/hyyan/woo-poly-integration', $output);
        self::assertStringContainsString('https://www.linkedin.com/in/hyyan/', $output);
        self::assertStringNotContainsString('twitter.com', $output);
        self::assertSame(Plugin::getVersion(), get_user_meta(1, 'wpi_welcome_version', true));
        self::assertStringNotContainsString('Thanks for using WooPoly', $this->renderNotices());
    }

    public function testNewVersionShowsNoticeAgainOnSettingsScreen(): void
    {
        update_user_meta(1, 'wpi_welcome_version', '1.5.0');
        $GLOBALS['current_screen'] = WP_Screen::get('settings_page_hyyan-wpi');
        self::assertStringContainsString('Thanks for using WooPoly', $this->renderNotices());
        self::assertSame(Plugin::getVersion(), get_user_meta(1, 'wpi_welcome_version', true));
    }

    #[DataProvider('unrelatedScreens')]
    public function testOtherScreensDoNotShowOrConsumeWelcome(string $screen): void
    {
        $GLOBALS['current_screen'] = WP_Screen::get($screen);
        self::assertStringNotContainsString('Thanks for using WooPoly', $this->renderNotices());
        self::assertSame('', get_user_meta(1, 'wpi_welcome_version', true));
    }

    public static function unrelatedScreens(): array
    {
        return array(array('dashboard'), array('edit-product'), array('woocommerce_page_wc-orders'));
    }

    #[DataProvider('restrictedRoles')]
    public function testNonAdministratorsDoNotSeeNotice(string $login): void
    {
        wp_set_current_user(get_user_by('login', $login)->ID);
        self::assertStringNotContainsString('Thanks for using WooPoly', $this->renderNotices());
        self::assertSame('', get_user_meta(1, 'wpi_welcome_version', true));
    }

    public static function restrictedRoles(): array
    {
        return array(array('lab-subscriber'), array('lab-customer'), array('lab-manager'));
    }
}
