<?php
use Hyyan\WPI\Product\Meta;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ProductScreenSecurityTest extends TestCase
{
    private $product;
    private array $get;
    private int $user;
    private $screen;
    private array $hooks;

    protected function setUp(): void
    {
        require_once ABSPATH . 'wp-admin/includes/admin.php';
        $this->get = $_GET;
        $this->user = get_current_user_id();
        $this->screen = $GLOBALS['current_screen'] ?? null;
        $this->hooks = $GLOBALS['wp_filter'];
        foreach ($this->hooks as $name => $hook) { $this->hooks[$name] = clone $hook; }
        $_GET = array();
        wp_set_current_user(1);
        $this->product = new WC_Product_Simple();
        $this->product->set_name('Product screen security fixture');
        $this->product->set_status('publish');
        $this->product->save();
        pll_set_post_language($this->product->get_id(), 'en');
        delete_post_meta($this->product->get_id(), '_translation_porduct_type');
    }

    protected function tearDown(): void
    {
        $_GET = array();
        wp_set_current_user(1);
        wp_delete_post($this->product->get_id(), true);
        $_GET = $this->get;
        wp_set_current_user($this->user);
        $GLOBALS['current_screen'] = $this->screen;
        $GLOBALS['wp_filter'] = $this->hooks;
    }

    public static function screenUsers(): array
    {
        return array('administrator' => array('lab-admin', true), 'reader without source edit permission' => array('lab-contributor', false));
    }

    #[DataProvider('screenUsers')]
    public function testViewingTranslationEditorDoesNotWriteSourceMetadata(string $login, bool $canEdit): void
    {
        $user = $login === 'lab-admin' ? get_user_by('id', 1) : get_user_by('login', $login);
        self::assertNotFalse($user);
        wp_set_current_user($user->ID);
        self::assertTrue(current_user_can('read_post', $this->product->get_id()));
        self::assertSame($canEdit, current_user_can('edit_post', $this->product->get_id()));
        $_GET = array('from_post' => (string) $this->product->get_id(), 'new_lang' => 'de');
        set_current_screen('product');
        $handler = (new ReflectionClass(Meta::class))->newInstanceWithoutConstructor();
        $handler->handleProductScreen();
        self::assertFalse(metadata_exists('post', $this->product->get_id(), '_translation_porduct_type'));
    }

    public function testNativeProductSaveBackfillsItsOwnTypeMetadata(): void
    {
        set_current_screen('product');
        $handler = (new ReflectionClass(Meta::class))->newInstanceWithoutConstructor();
        $handler->handleProductScreen();
        $this->product->set_weight('2');
        $this->product->save();
        self::assertSame('simple', get_post_meta($this->product->get_id(), '_translation_porduct_type', true));
        self::assertSame('2', wc_get_product($this->product->get_id())->get_weight());
    }
}
