<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class BRCP_Plugin {

	private static $instance;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	private function define_constants() {
		define( 'BRCP_VERSION', '9.0.0' );
		define( 'BRCP_PLUGIN_FILE', __DIR__ . '/../brnews-composer-pro.php' );
		define( 'BRCP_PLUGIN_BASENAME', plugin_basename( BRCP_PLUGIN_FILE ) );
		define( 'BRCP_PLUGIN_PATH', plugin_dir_path( dirname( __FILE__ ) ) );
		define( 'BRCP_PLUGIN_URL', plugin_dir_url( dirname( __FILE__ ) ) );
		define( 'BRCP_ASSETS_URL', BRCP_PLUGIN_URL . 'assets/' );
		define( 'BRCP_LIBRARIES_PATH', BRCP_PLUGIN_PATH . 'libraries/' );
		define( 'BRCP_CACHE_DIR', WP_CONTENT_DIR . '/cache/brcp/' );
	}

	private function includes() {
		require_once BRCP_PLUGIN_PATH . 'includes/class-install.php';
		require_once BRCP_PLUGIN_PATH . 'includes/class-registry.php';
		require_once BRCP_PLUGIN_PATH . 'includes/class-rest-api.php';
		require_once BRCP_PLUGIN_PATH . 'includes/admin/class-admin.php';
		require_once BRCP_PLUGIN_PATH . 'includes/class-iframe.php';
		require_once BRCP_PLUGIN_PATH . 'includes/class-renderer.php';
		require_once BRCP_PLUGIN_PATH . 'includes/frontend/class-frontend.php';
		// require_once BRCP_PLUGIN_PATH . 'includes/class-shortcodes.php';
		// require_once BRCP_PLUGIN_PATH . 'includes/class-templates.php';
	}

	private function init_hooks() {
		register_activation_hook( BRCP_PLUGIN_FILE, [ 'BRCP_Install', 'activate' ] );
		register_deactivation_hook( BRCP_PLUGIN_FILE, [ 'BRCP_Install', 'deactivate' ] );

		add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );
	}

	public function on_plugins_loaded() {
		// Init classes
		BRCP_Registry::instance();
		BRCP_REST_Controller::instance();
		BRCP_Admin::instance();
		BRCP_Iframe::instance();
		BRCP_Renderer::instance();
		BRCP_Frontend::instance();
		// BRCP_Shortcodes::instance();
		// BRCP_Templates::instance();
	}
}
