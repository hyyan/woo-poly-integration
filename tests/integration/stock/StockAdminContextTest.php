<?php
use Hyyan\WPI\Product\Stock;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class StockAdminContextTest extends TestCase
{
    #[DataProvider('contexts')]
    public function testOnlyAuthorizedProductEditorContextDefersStockHooks(string $login, $type, string $screen, bool $registered, string $action = 'editpost', string $nonceCase = 'valid'): void
    {
        require_once ABSPATH.'wp-admin/includes/admin.php';
        $user = get_current_user_id(); $post = $_POST; $current = $GLOBALS['current_screen'] ?? null;
        $hooks = $GLOBALS['wp_filter']; foreach ($hooks as $name => $hook) { $hooks[$name] = clone $hook; }
        $id = wp_insert_post(array('post_type' => 'product', 'post_status' => 'publish', 'post_title' => 'Stock request nonce fixture'));
        try {
            wp_set_current_user($login === '' ? 0 : get_user_by('login',$login)->ID);
            set_current_screen($screen); $_POST = array('product-type'=>$type, 'action'=>$action, 'post_ID'=>$id);
            if ($nonceCase === 'valid') { $_POST['_wpnonce'] = wp_create_nonce('update-post_'.$id); }
            elseif ($nonceCase === 'invalid') { $_POST['_wpnonce'] = 'invalid'; }
            elseif ($nonceCase === 'other product') { $_POST['_wpnonce'] = wp_create_nonce('update-post_'.($id + 1)); }
            elseif ($nonceCase === 'array') { $_POST['_wpnonce'] = array('invalid'); }
            remove_action('woocommerce_product_set_stock',array(Stock::class,'SyncStockSimple'));
            remove_action('woocommerce_variation_set_stock',array(Stock::class,'SyncStockVariation'));
            new Stock();
            self::assertSame($registered, has_action('woocommerce_product_set_stock',array(Stock::class,'SyncStockSimple')) !== false);
            self::assertSame($registered, has_action('woocommerce_variation_set_stock',array(Stock::class,'SyncStockVariation')) !== false);
        } finally {
            $_POST = array(); wp_delete_post($id, true);
            $_POST = $post; $GLOBALS['current_screen'] = $current; $GLOBALS['wp_filter'] = $hooks; wp_set_current_user($user);
        }
    }
    public static function contexts(): array
    {
        return array(
            array('', 'simple','dashboard',true),
            array('lab-subscriber','simple','dashboard',true),
            array('lab-customer','simple','dashboard',true),
            array('lab-manager','simple','dashboard',false),
            array('lab-manager',array('variable'),'dashboard',true),
            array('lab-manager','simple','product',false),
            array('lab-manager','simple','dashboard',true,'woocommerce_checkout'),
            array('lab-manager','simple','dashboard',true,'woocommerce_save_order_items'),
            array('lab-manager','simple','dashboard',true,'editpost','missing'),
            array('lab-manager','simple','dashboard',true,'editpost','invalid'),
            array('lab-manager','simple','dashboard',true,'editpost','other product'),
            array('lab-manager','simple','dashboard',true,'editpost','array')
        );
    }
}
