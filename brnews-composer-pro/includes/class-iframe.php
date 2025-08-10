<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class BRCP_Iframe {

    private static $instance;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'template_redirect', [ $this, 'render_iframe' ], 1 );
    }

    public function render_iframe() {
        if ( ! isset( $_GET['brcp_iframe'] ) || ! current_user_can( 'edit_posts' ) ) {
            return;
        }

        $post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
        if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
            wp_die( __( 'Invalid post ID or insufficient permissions', 'brnews-composer-pro' ) );
        }

        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo( 'charset' ); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <?php wp_head(); ?>
            <style>
                body {
                    background-color: #fff;
                }
            </style>
        </head>
        <body class="brcp-iframe-body">
            <div id="brcp-iframe-content">
                <?php
                // This is where the rendered layout will go.
                // For now, it will be empty.
                echo '<h1>Iframe Content</h1>';
                ?>
            </div>
            <?php wp_footer(); ?>
        </body>
        </html>
        <?php
        exit;
    }
}
