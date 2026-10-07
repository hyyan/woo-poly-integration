<?php
use PHPUnit\Framework\TestCase;

final class CouponScreenSecurityTest extends TestCase
{
    private array $get;
    private $page;
    private $screen;
    private array $hooks;

    protected function setUp(): void
    {
        $this->get = $_GET;
        $this->page = $GLOBALS['pagenow'] ?? null;
        $this->screen = $GLOBALS['current_screen'] ?? null;
        $this->hooks = $GLOBALS['wp_filter'];
        foreach ($this->hooks as $name => $hook) { $this->hooks[$name] = clone $hook; }
        $GLOBALS['wp_filter']['current_screen'] = new WP_Hook();
    }

    protected function tearDown(): void
    {
        $_GET = $this->get;
        $GLOBALS['pagenow'] = $this->page;
        $GLOBALS['current_screen'] = $this->screen;
        $GLOBALS['wp_filter'] = $this->hooks;
    }

    public function testCouponStringsUseTheResolvedNativeScreen(): void
    {
        $_GET = array();
        set_current_screen('languages_page_mlang_strings');
        $handler = $this->getMockBuilder(Hyyan\WPI\Coupon::class)->disableOriginalConstructor()
            ->onlyMethods(array('registerCouponStringsForTranslation'))->getMock();
        $handler->expects($this->once())->method('registerCouponStringsForTranslation');
        $handler->adminRegisterCouponStrings();
    }

    public function testCouponQueryCannotSelectAnotherResolvedScreen(): void
    {
        $_GET = array('page' => 'mlang_strings');
        $GLOBALS['pagenow'] = 'admin.php';
        set_current_screen('dashboard');
        $handler = $this->getMockBuilder(Hyyan\WPI\Coupon::class)->disableOriginalConstructor()
            ->onlyMethods(array('registerCouponStringsForTranslation'))->getMock();
        $handler->expects($this->never())->method('registerCouponStringsForTranslation');
        $handler->adminRegisterCouponStrings();
    }

    public function testCouponRegistrationWaitsForNativeScreenResolution(): void
    {
        $handler = new Hyyan\WPI\Coupon();
        self::assertNotFalse(has_action('current_screen', array($handler, 'adminRegisterCouponStrings')));
        self::assertFalse(has_action('wp_loaded', array($handler, 'adminRegisterCouponStrings')));
    }

}
