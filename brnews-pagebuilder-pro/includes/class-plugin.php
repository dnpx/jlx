<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class BRPB_Plugin {

    private static ?BRPB_Plugin $instance = null;
    public ?BRPB_Module_Registry $registry = null;

    public static function get_instance(): BRPB_Plugin {
        if(null === self::$instance){ self::$instance = new self(); }
        return self::$instance;
    }

    private function __construct(){
        $this->load_dependencies();
        $this->init_hooks();
        $this->registry = new BRPB_Module_Registry();
    }

    private function load_dependencies(): void {
        require_once BRPB_PLUGIN_DIR.'includes/class-base-module.php';
        require_once BRPB_PLUGIN_DIR.'includes/class-controls-helper.php';
        require_once BRPB_PLUGIN_DIR.'includes/class-module-registry.php';
        require_once BRPB_PLUGIN_DIR.'includes/class-frontend-renderer.php';
        require_once BRPB_PLUGIN_DIR.'includes/class-css-generator.php';
        require_once BRPB_PLUGIN_DIR.'includes/ajax-functions.php';

        // Load modules
        foreach ( [
            'includes/structural/*.php',
            'includes/modules/block-shortcodes/*.php',
            'includes/modules/big-grid-shortcodes/*.php',
            'includes/modules/extended-shortcodes/*.php',
            'includes/modules/header-shortcodes/*.php',
            'includes/modules/multipurpose-shortcodes/*.php',
        ] as $globpath ){
            foreach( glob( BRPB_PLUGIN_DIR.$globpath ) as $file ){ require_once $file; }
        }
    }

    private function init_hooks(): void {
        add_action('admin_menu', [ $this, 'add_admin_menu' ] );
        add_action('admin_enqueue_scripts', [ $this, 'enqueue_editor_assets' ] );
        add_action('wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ], 20 );

        add_filter('the_content', [ new BRPB_Frontend_Renderer(), 'render_content_filter' ], 9);

        add_action('add_meta_boxes', [ $this, 'add_editor_button_meta_box' ] );
        add_action('admin_bar_menu', [ $this, 'add_admin_bar_button' ], 99 );
        add_action('wp_footer', [ $this, 'add_canvas_iframe_script' ] );
    }

    public function add_admin_menu(): void {
        add_menu_page( __( 'Brnews Pagebuilder', 'brnews-pagebuilder' ), 'Brnews PB', 'edit_posts', 'brpb_editor', [ $this, 'render_editor_page' ], 'dashicons-layout', 22 );
    }

    public function render_editor_page(): void {
        if( !isset($_GET['post_id']) || ! current_user_can('edit_post', (int)$_GET['post_id']) ){
            wp_die( esc_html__('Item inválido ou sem permissão.','brnews-pagebuilder') );
        }
        require_once BRPB_PLUGIN_DIR.'templates/editor-shell.php';
    }

    public function enqueue_editor_assets(string $hook): void {
        if ( 'toplevel_page_brpb_editor' !== $hook ) return;
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('brpb-editor', BRPB_PLUGIN_URL.'assets/css/editor.css', [], BRPB_VERSION );

        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('brpb-editor', BRPB_PLUGIN_URL.'assets/js/editor.js', [ 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ], BRPB_VERSION, true );

        $post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
        $layout = get_post_meta($post_id,'_brpb_layout_data',true);

        wp_localize_script('brpb-editor','brpb_editor_data',[
            'ajax_url'=>admin_url('admin-ajax.php'),
            'nonce'=>wp_create_nonce('brpb_editor_nonce'),
            'post_id'=>$post_id,
            'initial_layout'=> json_decode($layout,true) ?: [],
            'i18n'=>[ 'saving'=>__('Salvando...','brnews-pagebuilder'),'saved'=>__('Salvo!','brnews-pagebuilder') ]
        ]);
    }

    public function enqueue_frontend_assets(): void {
        if ( !is_singular() && !isset($_GET['brpb_canvas']) ) return;
        $post_id = get_the_ID();
        if ( get_post_meta($post_id,'_brpb_layout_data',true) || isset($_GET['brpb_canvas']) ){
            wp_enqueue_style('brpb-frontend', BRPB_PLUGIN_URL.'assets/css/frontend.css', [], BRPB_VERSION );
            $u = wp_upload_dir();
            $p = $u['basedir'].'/brpb-css/brpb-'.$post_id.'.css';
            if(file_exists($p)){
                $url = $u['baseurl'].'/brpb-css/brpb-'.$post_id.'.css';
                $ver = get_post_meta($post_id,'_brpb_css_version',true) ?: filemtime($p);
                wp_enqueue_style('brpb-post-'.$post_id, $url, [ 'brpb-frontend' ], $ver );
            }
        }
    }

    public function add_canvas_iframe_script(): void {
        if( !isset($_GET['brpb_canvas']) ) return; ?>
        <script>
        document.addEventListener('DOMContentLoaded',function(){
            document.body.addEventListener('click',function(e){
                var el=e.target.closest('.brpb-element');
                if(el){ e.preventDefault(); e.stopPropagation(); parent.postMessage({action:'brpb_element_clicked',elementId:el.id},'*'); }
            });
            window.addEventListener('message',function(e){
                if(e.data.action==='brpb_highlight_element'){
                    document.querySelectorAll('.brpb-active').forEach(function(n){n.classList.remove('brpb-active');});
                    var t=document.getElementById(e.data.elementId); if(t) t.classList.add('brpb-active');
                }
            });
            parent.postMessage({action:'brpb_canvas_ready'},'*');
        });
        </script>
        <style>.brpb-element.brpb-active{outline:2px solid #3858e9;outline-offset:2px;transition:all .2s}</style>
        <?php
    }

    public function add_admin_bar_button( WP_Admin_Bar $bar ): void {
        if( is_admin() || !is_singular() || ! current_user_can('edit_post', get_the_ID()) ) return;
        $bar->add_node([
            'id'=>'brpb-edit-link',
            'title'=>'<span class="ab-icon dashicons-layout"></span>'.esc_html__('Editar com Brnews PB','brnews-pagebuilder'),
            'href'=>admin_url('admin.php?page=brpb_editor&post_id='.get_the_ID())
        ]);
    }

    public function add_editor_button_meta_box(): void {
        foreach( get_post_types_by_support('editor') as $screen ){
            add_meta_box('brpb_editor_button', __('Brnews Pagebuilder','brnews-pagebuilder'), [ $this,'render_editor_button_meta_box' ], $screen, 'side', 'high');
        }
    }
    public function render_editor_button_meta_box( WP_Post $post ): void {
        echo '<p><a href="'.esc_url(admin_url('admin.php?page=brpb_editor&post_id='.$post->ID)).'" class="button button-primary button-large" style="width:100%"><span class="dashicons dashicons-layout" style="vertical-align:middle"></span> '.esc_html__('Abrir Editor Visual','brnews-pagebuilder').'</a></p>';
    }

    public static function activate(): void {
        $dir = trailingslashit(wp_upload_dir()['basedir']).'brpb-css';
        if( !is_dir($dir) ) wp_mkdir_p($dir);
    }
    public static function deactivate(): void { /* noop */ }
}
