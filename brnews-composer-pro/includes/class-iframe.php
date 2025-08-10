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
                .brcp-module {
                    position: relative;
                    cursor: pointer;
                }
                .brcp-module--selected {
                    outline: 2px solid #0073aa;
                    outline-offset: -2px;
                }
            </style>
        </head>
        <body class="brcp-iframe-body">
            <div id="brcp-iframe-content">
                <?php
                // This is where the rendered layout will go.
                echo BRCP_Renderer::instance()->render_layout_from_meta( $post_id );
                ?>
            </div>
            <?php wp_footer(); ?>
            <script>
                window.addEventListener('message', function (event) {
                    if (event.data.action === 'renderLayout') {
                        wp.apiFetch({
                            path: '/brcp/v1/render-layout',
                            method: 'POST',
                            data: event.data.layout
                        }).then(response => {
                            document.getElementById('brcp-iframe-content').innerHTML = response.html;
                        });
                    }
                });

                document.getElementById('brcp-iframe-content').addEventListener('click', function (e) {
                    const module = e.target.closest('.brcp-module');
                    if (module) {
                        // Remove existing selected class
                        document.querySelectorAll('.brcp-module--selected').forEach(el => {
                            el.classList.remove('brcp-module--selected');
                        });
                        // Add selected class
                        module.classList.add('brcp-module--selected');

                        window.parent.postMessage({
                            action: 'selectBlock',
                            moduleId: module.dataset.moduleId,
                            blockType: module.dataset.type
                        }, '*');
                    }
                });
            </script>
        </body>
        </html>
        <?php
        exit;
    }
}
