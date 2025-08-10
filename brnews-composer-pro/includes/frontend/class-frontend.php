<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class BRCP_Frontend {

	private static $instance;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_filter( 'the_content', [ $this, 'render_content' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
	}

	public function render_content( $content ) {
		$post_id = get_the_ID();
		if ( ! get_post_meta( $post_id, '_brcp_enabled', true ) ) {
			return $content;
		}

		$layout_json = get_post_meta( $post_id, '_brcp_layout', true );
		if ( empty( $layout_json ) ) {
			// Return original content if composer is enabled but there's no layout.
			return $content;
		}

		$layout = json_decode( $layout_json, true );

		// Check if layout is valid
		if ( empty( $layout ) || !is_array( $layout ) ) {
			return $content;
		}


		return BRCP_Renderer::instance()->render_layout( $layout );
	}

	public function enqueue_styles() {
		// Only enqueue styles if the composer is active on the current page
		if ( is_singular() && get_post_meta( get_the_ID(), '_brcp_enabled', true ) ) {
			wp_enqueue_style(
				'brcp-frontend',
				BRCP_ASSETS_URL . 'css/frontend.css',
				[],
				BRCP_VERSION
			);

			wp_enqueue_style(
				'brcp-responsive',
				BRCP_ASSETS_URL . 'css/responsive.css',
				[ 'brcp-frontend' ],
				BRCP_VERSION
			);
		}
	}
}
