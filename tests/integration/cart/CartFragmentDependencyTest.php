<?php
use Hyyan\WPI\Cart;
use PHPUnit\Framework\TestCase;

final class CartFragmentDependencyTest extends TestCase
{
    public function testFragmentScriptLoadsItsCookieApiWithoutUnrelatedScripts(): void
    {
        $originalScripts = $GLOBALS['wp_scripts'] ?? null;
        $GLOBALS['wp_scripts'] = new WP_Scripts();
        try {
            WC_Frontend_Scripts::load_scripts();
            $handler = (new ReflectionClass(Cart::class))->newInstanceWithoutConstructor();
            $handler->replaceCartFragmentsScript();
            $scripts = wp_scripts();
            $scripts->queue = array('wc-cart-fragments');
            self::assertTrue($scripts->all_deps($scripts->queue));
            $sources = array();
            foreach ($scripts->to_do as $handle) {
                $sources[] = (string) $scripts->registered[$handle]->src;
            }
            self::assertTrue((bool) array_filter($sources, function ($source) {
                return strpos($source, '/js-cookie/js.cookie') !== false;
            }), 'The standalone fragment dependency graph must supply the Cookies API.');
            self::assertStringContainsString('public/js/Cart', $scripts->registered['wc-cart-fragments']->src);
        } finally {
            $GLOBALS['wp_scripts'] = $originalScripts;
        }
    }
}
