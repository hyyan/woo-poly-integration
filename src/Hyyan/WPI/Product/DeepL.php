<?php // phpcs:ignore WordPress.Files.FileName -- The plugin autoloader maps class names directly to paths.
/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package WooPoly
 */

namespace Hyyan\WPI\Product;

/**
 * Translate product text with a merchant's server-side DeepL API key.
 */
final class DeepL {

	const STORE_KEY = '_woopoly_deepl_key';

	/**
	 * Derive the encryption key from the WordPress authentication salt.
	 */
	private static function encryption_key() {
		return hash_hmac( 'sha256', 'woopoly-deepl-store', wp_salt( 'auth' ), true );
	}

	/**
	 * Decrypt the shared store credential.
	 */
	public static function storeKey() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- Preserve the public credential API.
		$stored = get_option( self::STORE_KEY, '' );
		$bytes  = is_string( $stored ) ? base64_decode( $stored, true ) : false; // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Serialize encrypted binary data for option storage.
		if ( ! $bytes || strlen( $bytes ) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES ) {
			return false;
		}

		$nonce = substr( $bytes, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );

		return sodium_crypto_secretbox_open( substr( $bytes, strlen( $nonce ) ), $nonce, self::encryption_key() );
	}

	/**
	 * Encrypt and save a replacement store credential.
	 *
	 * @param string $key API credential.
	 */
	public static function saveStoreKey( $key ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- Preserve the public credential API.
		if ( ! is_string( $key ) || ! preg_match( '/^[!-~]{1,512}$/D', $key ) ) {
			return false;
		}

		$nonce     = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
		$encrypted = sodium_crypto_secretbox( $key, $nonce, self::encryption_key() );

		return (bool) update_option( self::STORE_KEY, base64_encode( $nonce . $encrypted ), false ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Serialize encrypted binary data for option storage.
	}

	/**
	 * Resolve the store credential or configured fallback.
	 */
	public static function getKey() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- Preserve the public credential API.
		$stored = self::storeKey();
		if ( $stored ) {
			return $stored;
		}

		return defined( 'WOOPOLY_DEEPL_AUTH_KEY' ) ? WOOPOLY_DEEPL_AUTH_KEY : getenv( 'DEEPL_AUTH_KEY' );
	}

	/**
	 * Translate unique text values in order, respecting the API's request limits.
	 * HTML input uses DeepL's HTML mode to preserve markup.
	 *
	 * @param array  $texts Source text values.
	 * @param string $source_locale Source locale.
	 * @param string $target_locale Target locale.
	 * @return array|\WP_Error map from original text to translated text
	 */
	public static function translate( array $texts, $source_locale, $target_locale ) {
		$key = self::getKey();
		if ( ! $key ) {
			return new \WP_Error( 'deepl_key', __( 'Configure a DeepL API key before translating products.', 'woo-poly-integration' ) );
		}

		$source     = strtoupper( strtok( $source_locale, '_-' ) );
		$locale     = strtoupper( str_replace( '_', '-', $target_locale ) );
		$target     = in_array( $locale, array( 'EN-US', 'EN-GB', 'PT-BR', 'PT-PT', 'ZH-HANS', 'ZH-HANT' ), true )
			? $locale : strtok( $locale, '-' );
		$url        = substr( $key, -3 ) === ':fx' ? 'https://api-free.deepl.com/v2/translate' : 'https://api.deepl.com/v2/translate';
		$translated = array();
		$groups     = array(
			'text' => array(),
			'html' => array(),
		);

		foreach ( array_unique( $texts ) as $text ) {
			if ( '' !== trim( $text ) ) {
				$format              = wp_strip_all_tags( $text ) === $text ? 'text' : 'html';
				$groups[ $format ][] = $text;
			}
		}

		foreach ( $groups as $format => $group ) {
			$params = array(
				'source_lang' => $source,
				'target_lang' => $target,
			);
			if ( 'html' === $format ) {
				$params['tag_handling'] = 'html';
			}

			while ( $group ) {
				$batch = array_slice( $group, 0, 50 );
				do {
					$body = wp_json_encode( array_merge( $params, array( 'text' => $batch ) ) );
					if ( false !== $body && strlen( $body ) < 128 * 1024 ) {
						break;
					}

					if ( count( $batch ) === 1 ) {
						return new \WP_Error( 'deepl_size', __( 'A text field exceeds the DeepL request limit.', 'woo-poly-integration' ) );
					}

					$batch = array_slice( $batch, 0, intdiv( count( $batch ), 2 ) );
				} while ( $batch );
				$result = self::request_translations( $url, $key, $body, $batch );
				if ( is_wp_error( $result ) ) {
					return $result;
				}

				$translated += $result;
				$group       = array_slice( $group, count( $batch ) );
			}
		}

		return $translated;
	}

	/**
	 * Send one batch and validate the returned translations.
	 *
	 * @param string $url Translation endpoint.
	 * @param string $key API credential.
	 * @param string $body Encoded request body.
	 * @param array  $batch Original text values.
	 */
	private static function request_translations( $url, $key, $body, array $batch ) {
		$response = wp_remote_post(
			$url,
			array(
				'timeout'     => 30,
				'redirection' => 0,
				'headers'     => array(
					'Authorization' => 'DeepL-Auth-Key ' . $key,
					'Content-Type'  => 'application/json',
				),
				'body'        => $body,
			)
		);
		$data     = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( wp_remote_retrieve_response_code( $response ) !== 200 || ! isset( $data['translations'] )
			|| ! is_array( $data['translations'] ) || count( $batch ) !== count( $data['translations'] ) ) {
			return new \WP_Error( 'deepl_request', __( 'DeepL could not translate this product. Check the API key, quota, and language pair.', 'woo-poly-integration' ) );
		}

		$translated = array();

		foreach ( $batch as $index => $original ) {
			if ( ! isset( $data['translations'][ $index ]['text'] ) || ! is_string( $data['translations'][ $index ]['text'] ) ) {
				return new \WP_Error( 'deepl_response', __( 'DeepL returned an incomplete translation.', 'woo-poly-integration' ) );
			}

			$translated[ $original ] = $data['translations'][ $index ]['text'];
		}

		return $translated;
	}
}
