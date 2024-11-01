<?php
/**
 * REST API Pattern And Types Controller
 *
 * @author    Easily Amused, Inc.
 * @package   ea-styles-library
 */

namespace EASL\Classes;

defined( 'ABSPATH' ) || exit;

/**
 * Handles requests to easl/v1/pattern-types.
 */
class PatternAndTypesRESTController extends \WP_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'easl/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'pattern-types';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_pattern_types_and_patterns' ),
					'permission_callback' => '__return_true', // Read only, so anyone can view.
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Get a collection of items
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_pattern_types_and_patterns() {

		$pattern_category_types = apply_filters( 'easl_pattern_api_response_transient', get_transient( 'easl_pattern_api_response' ) );

		if ( empty( $pattern_category_types ) ) {

			$license     = get_option( 'honors_license_key', array() );
			$license_key = '';

			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if (
				is_plugin_active( 'ea-block-styles/ea-block-styles.php' ) &&
				! empty( $license['ea-block-styles']['licence_key'] ) &&
				! empty( $license['ea-block-styles']['status'] ) &&
				'valid' === $license['ea-block-styles']['status']
			) {
				$license_key = $license['ea-block-styles']['licence_key'];
			}

			try {
				$response = wp_remote_get(
					'https://blockstyles.com/wp-json/easl/v1/patterns',
					array(
						'headers' => array(
							'Accept'      => 'application/json',
							'X-WP-EAkey'  => $license_key,
							'X-WP-EAsite' => get_site_url(),
						),
					)
				);

				if ( ! is_wp_error( $response ) ) {
					$pattern_category_types = json_decode( $response['body'], true );
					set_transient( 'easl_pattern_api_response', $pattern_category_types, 1 * HOUR_IN_SECONDS );
				}
			} catch ( \Exception $ex ) {
				$pattern_category_types = '';
			}
		}

		if ( is_array( $pattern_category_types ) ) {
			return new \WP_REST_Response( array( 'patternTypesAndPatterns' => $pattern_category_types ), 200 );
		} else {
			return new \WP_Error( '404', __( 'Something went wrong, the category types could not be found.', 'ea-styles-library' ), array( 'status' => 404 ) );
		}
	}
}
