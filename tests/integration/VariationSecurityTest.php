<?php
use Hyyan\WPI\Product\Variable;
use Hyyan\WPI\Product\Variation;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
final class VariationSecurityTest extends TestCase
{
    private array $ids = array();
    private int $source;
    private int $translated;
    private int $unrelated;
    protected function setUp(): void
    {
        wp_set_current_user(1);
        foreach (array('en', 'de') as $language) {
            $parent = new WC_Product_Variable(); $parent->set_name('PHPUnit variable ' . $language); $parent->save();
            $this->ids[] = $parent->get_id(); pll_set_post_language($parent->get_id(), $language);
            $variation = new WC_Product_Variation(); $variation->set_parent_id($parent->get_id()); $variation->set_regular_price('10'); $variation->save();
            $id = $variation->get_id(); $this->ids[] = $id;
            if ($language === 'en') { $this->source = $id; } else { $this->translated = $id; }
        }
        foreach (array($this->source, $this->translated) as $id) { update_post_meta($id, Variation::DUPLICATE_KEY, $this->source); }
        $other = new WC_Product_Simple(); $other->set_name('Unrelated simple product'); $other->save();
        $this->unrelated = $other->get_id(); $this->ids[] = $this->unrelated;
        $_POST = $_REQUEST = array();
    }
    protected function tearDown(): void
    {
        wp_set_current_user(1);
        foreach (array_reverse($this->ids) as $id) { wp_delete_post($id, true); }
        $_POST = $_REQUEST = array();
    }
    private function remove(array $values, ?string $nonce): void
    {
        $_POST = array('variation_ids' => $values);
        $_REQUEST = $_POST;
        if ($nonce !== null) { $_REQUEST['security'] = $_POST['security'] = $nonce; }
        try { do_action('wp_ajax_woocommerce_remove_variations'); } catch (WooPolyTestDie $e) {}
    }
    public function testSubscriberCannotDeleteEvenWithValidNonce(): void
    {
        $subscriber = get_user_by('login', 'lab-subscriber'); wp_set_current_user($subscriber->ID);
        $this->remove(array($this->source), wp_create_nonce('delete-variations'));
        self::assertNotNull(get_post($this->source)); self::assertNotNull(get_post($this->translated));
    }
    #[DataProvider('invalidNonces')]
    public function testAdministratorCannotDeleteWithoutValidNonce($nonce): void
    {
        $this->remove(array($this->source), $nonce);
        self::assertNotNull(get_post($this->source)); self::assertNotNull(get_post($this->translated));
    }
    public static function invalidNonces(): array { return array('missing' => array(null), 'invalid' => array('invalid')); }
    public function testMalformedIdDoesNotDeleteValidPrefixOrExecuteSql(): void
    {
        $this->remove(array($this->source . ' OR 1=1'), wp_create_nonce('delete-variations'));
        self::assertNotNull(get_post($this->source)); self::assertNotNull(get_post($this->translated));
        self::assertEmpty(Variation::getRelatedVariation($this->source . ' OR 1=1', true));
    }
    public function testValidBatchDoesNotPartiallyDeleteBeforeMalformedId(): void
    {
        $this->remove(array($this->source, array('nested')), wp_create_nonce('delete-variations'));
        self::assertNotNull(get_post($this->source)); self::assertNotNull(get_post($this->translated));
    }
    public function testShopManagerRemovalKeepsOriginalTranslationSynchronization(): void
    {
        $manager = get_user_by('login', 'lab-manager'); wp_set_current_user($manager->ID);
        $this->remove(array((string) $this->source), wp_create_nonce('delete-variations'));
        self::assertNull(get_post($this->source)); self::assertNull(get_post($this->translated));
        self::assertNotNull(get_post($this->unrelated));
    }
    public function testAdministratorRemovalKeepsOriginalTranslationSynchronization(): void
    {
        $this->remove(array($this->source), wp_create_nonce('delete-variations'));
        self::assertNull(get_post($this->source));
        self::assertNull(get_post($this->translated));
        self::assertNotNull(get_post($this->unrelated));
    }
    public function testCustomerCannotDeleteEvenWithValidNonce(): void
    {
        $customer = get_user_by('login', 'lab-customer');
        wp_set_current_user($customer->ID);
        $this->remove(array($this->source), wp_create_nonce('delete-variations'));
        self::assertNotNull(get_post($this->source));
        self::assertNotNull(get_post($this->translated));
    }
    public function testCorruptRelationshipCannotDeleteSimpleProduct(): void
    {
        update_post_meta($this->unrelated, Variation::DUPLICATE_KEY, $this->source);
        $this->remove(array($this->source), wp_create_nonce('delete-variations'));
        self::assertNotNull(get_post($this->source));
        self::assertNotNull(get_post($this->translated));
        self::assertNotNull(get_post($this->unrelated));
    }
    public function testZeroAndNonexistentIdsAreHarmless(): void
    {
        $this->remove(array(0, 999999999), wp_create_nonce('delete-variations'));
        self::assertNotNull(get_post($this->source)); self::assertNotNull(get_post($this->translated));
    }
    public function testLookupPreservesBothLanguages(): void
    {
        $ids = array_map('intval', Variation::getRelatedVariation($this->source, true)); sort($ids);
        $expected = array($this->source, $this->translated); sort($expected);
        self::assertSame($expected, $ids);
    }
}
