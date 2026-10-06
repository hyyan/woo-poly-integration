<?php
use PHPUnit\Framework\TestCase;

final class ProductImportSynchronizationTest extends TestCase
{
    private array $products = array();
    private $originalLanguage;
    private array $originalSync;
    private int $originalUser;
    private string $csv;
    private $observedPost;

    protected function setUp(): void
    {
        $this->originalLanguage = PLL()->curlang;
        $this->originalSync = PLL()->options['sync'];
        $this->originalUser = get_current_user_id();
        wp_set_current_user(1);
        PLL()->options['sync'] = array('post_date', 'menu_order', 'comment_status');
        foreach (array('en', 'de') as $language) {
            $product = new WC_Product_Simple();
            $product->set_name('Import preservation ' . $language);
            $product->set_status('publish');
            $product->set_regular_price('10');
            $product->set_date_created('2020-02-03 04:05:06');
            $product->set_menu_order(7);
            $product->set_reviews_allowed(true);
            $product->save();
            pll_set_post_language($product->get_id(), $language);
            $this->products[$language] = $product;
        }
        pll_save_post_translations(array_map(function ($product) {
            return $product->get_id();
        }, $this->products));
        PLL()->curlang = PLL()->model->get_language('en');
        $temporary = tempnam(sys_get_temp_dir(), 'woopoly-import-');
        $this->csv = $temporary . '.csv';
        rename($temporary, $this->csv);
        $file = fopen($this->csv, 'w');
        fputcsv($file, array('ID', 'Regular price'), ',', '"', '');
        fputcsv($file, array($this->products['en']->get_id(), '25'), ',', '"', '');
        fclose($file);
        add_action('pll_save_post', array($this, 'observePost'), 1, 3);
    }

    protected function tearDown(): void
    {
        remove_action('pll_save_post', array($this, 'observePost'), 1);
        PLL()->options['sync'] = $this->originalSync;
        foreach ($this->products as $product) {
            $product->delete(true);
        }
        PLL()->curlang = $this->originalLanguage;
        wp_set_current_user($this->originalUser);
        unlink($this->csv);
    }

    public function observePost($id, $post, $translations): void
    {
        if ($id === $this->products['en']->get_id()) {
            $this->observedPost = $post;
        }
    }

    private function importPrice(): void
    {
        require_once WC_ABSPATH . 'includes/import/class-wc-product-csv-importer.php';
        $importer = new WC_Product_CSV_Importer($this->csv, array(
            'mapping' => array('ID' => 'id', 'Regular price' => 'regular_price'),
            'parse' => true,
            'update_existing' => true,
        ));
        $result = $importer->import();
        self::assertSame(array($this->products['en']->get_id()), $result['updated']);
        self::assertSame(array(), $result['failed']);
    }

    public function testNativeImportPassesTheStoredPostToPolylang(): void
    {
        $this->importPrice();
        self::assertInstanceOf(WP_Post::class, $this->observedPost);
        self::assertSame($this->products['en']->get_id(), $this->observedPost->ID);
    }

    public function testPriceImportPreservesPublicationFieldsAndSynchronizesPrice(): void
    {
        $source = get_post($this->products['en']->get_id());
        $this->importPrice();
        foreach ($this->products as $product) {
            $post = get_post($product->get_id());
            self::assertSame($source->post_date, $post->post_date);
            self::assertSame($source->post_date_gmt, $post->post_date_gmt);
            self::assertSame($source->menu_order, $post->menu_order);
            self::assertSame($source->comment_status, $post->comment_status);
            self::assertSame('25', wc_get_product($product->get_id())->get_regular_price());
        }
    }
}
