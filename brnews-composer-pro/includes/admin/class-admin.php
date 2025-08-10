<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class BRCP_Admin {

	private static $instance;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post', [ $this, 'save_post_meta' ], 10, 2 );
		add_filter( 'page_row_actions', [ $this, 'page_row_actions' ], 10, 2 );
		add_filter( 'post_row_actions', [ $this, 'page_row_actions' ], 10, 2 );
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_editor_assets' ] );
	}

	public function add_admin_menu() {
		add_menu_page(
			__( 'Brnews Composer', 'brnews-composer-pro' ),
			'Brnews Composer',
			'edit_posts',
			'brcp-dashboard',
			[ $this, 'render_dashboard_page' ],
			'dashicons-layout',
			6
		);

		add_submenu_page(
			'brcp-dashboard',
			__( 'Brnews Composer Editor', 'brnews-composer-pro' ),
			__( 'Editor', 'brnews-composer-pro' ),
			'edit_posts',
			'brcp-editor',
			[ $this, 'render_editor_page' ]
		);

		// Remove the submenu page from the menu
		remove_submenu_page( 'brcp-dashboard', 'brcp-editor' );
	}

	public function render_dashboard_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Brnews Composer Dashboard', 'brnews-composer-pro' ); ?></h1>
			<p><?php _e( 'Welcome to the Brnews Composer. You can start editing your pages by clicking the "Edit with Brnews Composer" button on the post or page edit screen.', 'brnews-composer-pro' ); ?></p>
		</div>
		<?php
	}

	public function enqueue_editor_assets( $hook ) {
		if ( 'toplevel_page_brcp-editor' !== $hook && 'admin_page_brcp-editor' !== $hook) {
			return;
		}

		wp_enqueue_style(
			'brcp-editor',
			BRCP_ASSETS_URL . 'css/editor.css',
			[],
			BRCP_VERSION
		);

		wp_enqueue_script(
			'brcp-editor',
			BRCP_ASSETS_URL . 'js/editor.js',
			[ 'wp-element', 'wp-components', 'wp-api-fetch' ],
			BRCP_VERSION,
			true
		);
	}

	public function render_editor_page() {
		$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( __( 'Invalid post ID or insufficient permissions.', 'brnews-composer-pro' ) );
		}

		// The get_permalink() function may not be available on the admin side.
		// We need to use get_edit_post_link() and modify it.
		$iframe_url = add_query_arg(
			[
				'brcp_iframe' => 'true',
				'post_id'     => $post_id,
			],
			get_permalink( $post_id )
		);

		?>
		<div id="brcp-editor-wrapper">
			<div id="brcp-editor-left-panel">
				<div class="brcp-panel-header">
					<h2><?php _e( 'Blocks', 'brnews-composer-pro' ); ?></h2>
				</div>
				<div id="brcp-block-library"></div>
			</div>
			<div id="brcp-editor-center-panel">
				<iframe id="brcp-editor-iframe" src="<?php echo esc_url( $iframe_url ); ?>"></iframe>
			</div>
			<div id="brcp-editor-right-panel">
				<div class="brcp-panel-header">
					<h2><?php _e( 'Inspector', 'brnews-composer-pro' ); ?></h2>
				</div>
				<div id="brcp-block-inspector"></div>
			</div>
		</div>
		<?php
	}

	public function add_meta_boxes() {
		$screens = [ 'post', 'page' ];

		foreach ( $screens as $screen ) {
			add_meta_box(
				'brcp-composer',
				__( 'Brnews Composer', 'brnews-composer-pro' ),
				[ $this, 'meta_box_callback' ],
				$screen,
				'normal',
				'high'
			);
		}
	}

	public function meta_box_callback( $post ) {
		wp_nonce_field( 'brcp_composer_meta_box', 'brcp_composer_meta_box_nonce' );

		$enabled = get_post_meta( $post->ID, '_brcp_enabled', true );

		?>
		<div class="brcp-admin-metabox">
			<p>
				<label>
					<input type="checkbox" name="brcp_enabled" value="1" <?php checked( $enabled, '1' ); ?>>
					<?php _e( 'Enable Brnews Composer for this page', 'brnews-composer-pro' ); ?>
				</label>
			</p>

			<p id="brcp-edit-button-wrapper" style="display: <?php echo $enabled ? 'block' : 'none'; ?>;">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=brcp-editor&post_id=' . $post->ID ) ); ?>"
				   class="button button-primary">
					<?php _e( 'Edit with Brnews Composer', 'brnews-composer-pro' ); ?>
				</a>
			</p>
		</div>

		<script>
			document.addEventListener('DOMContentLoaded', function () {
				const checkbox = document.querySelector('input[name="brcp_enabled"]');
				const editButtonWrapper = document.getElementById('brcp-edit-button-wrapper');

				if (checkbox) {
					checkbox.addEventListener('change', function () {
						if (editButtonWrapper) {
							editButtonWrapper.style.display = this.checked ? 'block' : 'none';
						}
					});
				}
			});
		</script>
		<?php
	}

	public function save_post_meta( $post_id, $post ) {
		// Check if nonce is set and valid
		if ( ! isset( $_POST['brcp_composer_meta_box_nonce'] ) ||
			 ! wp_verify_nonce( $_POST['brcp_composer_meta_box_nonce'], 'brcp_composer_meta_box' ) ) {
			return;
		}

		// Check if user has permission
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if not autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Save enabled status
		if ( isset( $_POST['brcp_enabled'] ) ) {
			update_post_meta( $post_id, '_brcp_enabled', '1' );
		} else {
			delete_post_meta( $post_id, '_brcp_enabled' );
		}
	}

	public function page_row_actions( $actions, $post ) {
		// Only add action for posts/pages that have Brnews Composer enabled
		if ( get_post_meta( $post->ID, '_brcp_enabled', true ) ) {
			$actions['brcp_edit'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( admin_url( 'admin.php?page=brcp-editor&post_id=' . $post->ID ) ),
				esc_attr( __( 'Edit with Brnews Composer', 'brnews-composer-pro' ) ),
				__( 'Composer', 'brnews-composer-pro' )
			);
		}

		return $actions;
	}
}
