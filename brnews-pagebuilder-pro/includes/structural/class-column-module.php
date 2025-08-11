<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class BRPB_Column_Module extends BRPB_Base_Module {
    public function get_id(): string { return 'column'; }
    public function get_name(): string { return __('Coluna','brnews-pagebuilder'); }
    public function get_icon(): string { return 'dashicons-format-aside'; }
    public function get_group(): string { return 'structural'; }

    public function get_controls(): array {
        $layout = [
            'layout_tab'=>[ 'label'=>__('Layout da Coluna','brnews-pagebuilder'),'type'=>'tab','controls'=>[
                'width'=>[ 'label'=>__('Largura','brnews-pagebuilder'),'type'=>'section','controls'=>[
                    'column_width'=>[ 'type'=>'slider','label'=>__('Largura (%)','brnews-pagebuilder'),'default'=>['desktop'=>100],'min'=>5,'max'=>100,'unit'=>'%','selector'=>'{{WRAPPER}}','property'=>'width' ],
                ]]
            ]]
        ];
        return array_merge( $layout, BRPB_Controls_Helper::get_style_tab() );
    }

    public function render( array $settings, string $element_id, array $children ): void {
        echo '<div class="brpb-column brpb-column-inner-content">';
        if(!empty($children)){
            $r = new BRPB_Frontend_Renderer();
            foreach($children as $child){ $r->render_element($child); }
        } else if ( isset($_GET['brpb_canvas']) ){
            echo '<div class="brpb-empty-column-placeholder">'.esc_html__('Arraste um elemento para cá','brnews-pagebuilder').'</div>';
        }
        echo '</div>';
    }
}
