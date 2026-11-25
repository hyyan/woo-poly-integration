<?php
/**
 * Verify language filtering on WooCommerce product collection routes.
 *
 * @package WooPoly
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Exercise each supported controller through the WordPress REST server. */
final class RestProductLanguageTest extends TestCase {

	/** WooCommerce product collection namespaces. */
	public static function routes(): array {
		return array( array( 'wc/v1' ), array( 'wc/v2' ), array( 'wc/v3' ) );
	}

	/**
	 * Verify IDs and pagination totals for valid, empty and invalid filters.
	 *
	 * @param string $route_prefix WooCommerce REST namespace.
	 */
	#[DataProvider( 'routes' )]
	public function testProductCollectionFiltersLanguageBeforePagination( string $route_prefix ): void {
		$user = get_current_user_id();
		$ids  = array();
		try {
			wp_set_current_user( 1 );
			foreach ( array( 'en', 'de', 'en' ) as $language ) {
				$product = new WC_Product_Simple();
				$product->set_name( 'REST collection ' . $language );
				$product->set_status( 'publish' );
				$id    = $product->save();
				$ids[] = $id;
				pll_set_post_language( $id, $language );
			}
			pll_save_post_translations(
				array(
					'en' => $ids[0],
					'de' => $ids[1],
				)
			);

			$request = new WP_REST_Request( 'GET', '/' . $route_prefix . '/products' );
			$request->set_param( 'include', $ids );
			$request->set_param( 'orderby', 'id' );
			$request->set_param( 'order', 'asc' );
			$baseline = rest_do_request( $request );
			self::assertSame( 200, $baseline->get_status() );
			self::assertNotEmpty( $baseline->get_data() );
			$request->set_param( 'lang', '' );
			self::assertSame( array_column( $baseline->get_data(), 'id' ), array_column( rest_do_request( $request )->get_data(), 'id' ) );

			$request->set_param( 'per_page', 1 );
			foreach ( array( array( 'en', 1, $ids[0], 2 ), array( 'en', 2, $ids[2], 2 ), array( 'de', 1, $ids[1], 1 ) ) as $case ) {
				list( $lang, $page, $expected_id, $total ) = $case;
				$request->set_param( 'lang', $lang );
				$request->set_param( 'page', $page );
				$response = rest_do_request( $request );
				self::assertSame( 200, $response->get_status() );
				self::assertSame( array( $expected_id ), array_column( $response->get_data(), 'id' ) );
				self::assertSame( $total, (int) $response->get_headers()['X-WP-Total'] );
				self::assertSame( $total, (int) $response->get_headers()['X-WP-TotalPages'] );
			}
			foreach ( array( 'unconfigured', array( 'en' ) ) as $invalid ) {
				$request->set_param( 'lang', $invalid );
				$response = rest_do_request( $request );
				self::assertSame( 200, $response->get_status() );
				self::assertSame( array(), $response->get_data() );
			}
		} finally {
			foreach ( $ids as $id ) {
				wp_delete_post( $id, true );
			}
			wp_set_current_user( $user );
		}
	}
}
