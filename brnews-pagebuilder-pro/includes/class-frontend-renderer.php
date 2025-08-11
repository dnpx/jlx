<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class BRPB_Frontend_Renderer {

    private ?BRPB_Module_Registry $registry = null;

    public function __construct(){ $this->registry = brpb()->registry; }

    public function render_content_filter( string $content ): string {
        $is_canvas = isset($_GET['brpb_canvas']);
        if( !is_singular() && !$is_canvas ) return $content;
        if( !in_the_loop() ) return $content;

        $post_id = get_the_ID();
        $layout_json = get_post_meta($post_id,'_brpb_layout_data',true);

        if($is_canvas){
            $data = json_decode($layout_json,true) ?: [];
            return $this->render_layout($data);
        }
        if(!empty($layout_json)){
            $data = json_decode($layout_json,true);
            if(json_last_error()===JSON_ERROR_NONE && !empty($data)){
                return $this->render_layout($data);
            }
        }
        return $content;
    }

    public function render_layout(array $layout): string {
        ob_start();
        echo '<div class="brpb-page-container">';
        foreach($layout as $element){ $this->render_element($element); }
        echo '</div>';
        return ob_get_clean();
    }

    public function render_element(array $element): void {
        $module = $this->registry->get_module($element['typeId'] ?? '');
        if(!$module) return;

        $eid = esc_attr($element['id']);
        $classes = [ 'brpb-element', 'brpb-element-type-'.esc_attr($element['typeId']) ];

        $visibility = $element['settings']['style_tab']['visibility'] ?? [];
        foreach($visibility as $k=>$hidden){ if($hidden){ $classes[] = 'brpb-'.$k; } }

        echo '<div id="'.$eid.'" class="'.esc_attr(implode(' ',$classes)).'">';
        $module->render( $element['settings'] ?? [], $eid, $element['children'] ?? [] );
        echo '</div>';
    }
}
