<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class BRCP_REST_Controller {

	private static $instance;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
		add_action( 'init', [ $this, 'register_meta_fields' ] );
	}

	public function register_meta_fields() {
		register_post_meta( 'post', '_brcp_layout', [
			'show_in_rest' => true,
			'single'       => true,
			'type'         => 'string',
			'auth_callback' => function() {
				return current_user_can( 'edit_posts' );
			}
		] );
		register_post_meta( 'page', '_brcp_layout', [
			'show_in_rest' => true,
			'single'       => true,
			'type'         => 'string',
			'auth_callback' => function() {
				return current_user_can( 'edit_posts' );
			}
		] );
	}

	public function register_routes() {
		register_rest_route( 'brcp/v1', '/layout/(?P<id>\d+)', [
			'methods'  => 'POST',
			'callback' => [ $this, 'save_layout' ],
			'permission_callback' => [ $this, 'save_layout_permissions_check' ],
		] );

		register_rest_route( 'brcp/v1', '/library', [
			'methods'  => 'GET',
			'callback' => [ $this, 'get_library' ],
			'permission_callback' => '__return_true',
		] );

		register_rest_route( 'brcp/v1', '/fragment/(?P<slug>[a-zA-Z0-9_-]+)', [
			'methods'  => 'GET',
			'callback' => [ $this, 'get_fragment' ],
			'permission_callback' => '__return_true',
		] );

		register_rest_route( 'brcp/v1', '/block/(?P<type>[a-zA-Z0-9_-]+)', [
			'methods'  => 'GET',
			'callback' => [ $this, 'get_block' ],
			'permission_callback' => '__return_true',
		] );

		register_rest_route( 'brcp/v1', '/render-layout', [
			'methods'  => 'POST',
			'callback' => [ $this, 'render_layout_endpoint' ],
			'permission_callback' => '__return_true',
		] );
	}

	public function save_layout( $request ) {
		$post_id = $request->get_param( 'id' );
		$layout  = $request->get_json_params();

		// TODO: Add sanitization and validation
		update_post_meta( $post_id, '_brcp_layout', wp_slash( json_encode( $layout ) ) );

		// TODO: Generate and return a module map for incremental updates
		return new WP_REST_Response( [ 'success' => true ], 200 );
	}

	public function save_layout_permissions_check( $request ) {
		return current_user_can( 'edit_post', $request->get_param( 'id' ) );
	}

	public function get_library( $request ) {
		$registry = BRCP_Registry::instance();
		return new WP_REST_Response( $registry->get_blocks(), 200 );
	}

	public function get_fragment( $request ) {
		$registry = BRCP_Registry::instance();
		$fragment = $registry->get_fragment( $request->get_param( 'slug' ) );

		if ( ! $fragment ) {
			return new WP_Error( 'brcp_fragment_not_found', __( 'Fragment not found', 'brnews-composer-pro' ), [ 'status' => 404 ] );
		}

		return new WP_REST_Response( $fragment, 200 );
	}

	public function get_block( $request ) {
		$registry = BRCP_Registry::instance();
		$block = $registry->get_block( $request->get_param( 'type' ) );

		if ( ! $block ) {
			return new WP_Error( 'brcp_block_not_found', __( 'Block not found', 'brnews-composer-pro' ), [ 'status' => 404 ] );
		}

		return new WP_REST_Response( $block, 200 );
	}

	public function render_layout_endpoint( $request ) {
		$layout = $request->get_json_params();
		$html = BRCP_Renderer::instance()->render_layout( $layout );
		return new WP_REST_Response( [ 'html' => $html ], 200 );
	}
}
