<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class BRPB_Row_Module extends BRPB_Base_Module {
    public function get_id(): string { return 'row'; }
    public function get_name(): string { return __('Linha','brnews-pagebuilder'); }
    public function get_icon(): string { return 'dashicons-editor-insertmore'; }
    public function get_group(): string { return 'structural'; }

    public function get_controls(): array {
        $layout = [
            'layout_tab'=>[ 'label'=>__('Layout da Linha','brnews-pagebuilder'),'type'=>'tab','controls'=>[
                'structure'=>[ 'label'=>__('Estrutura','brnews-pagebuilder'),'type'=>'section','controls'=>[
                    'content_width'=>[ 'type'=>'select','label'=>__('Largura do Conteúdo','brnews-pagebuilder'),'default'=>'boxed','options'=>[ 'boxed'=>'Boxed','full_width'=>'Full Width' ] ],
                    'gap'=>[ 'type'=>'select','label'=>__('Espaçamento entre Colunas','brnews-pagebuilder'),'default'=>'default','options'=>[ 'default'=>'Padrão','no_gap'=>'Sem Espaço','narrow'=>'Estreito','wide'=>'Largo' ] ],
                ]]
            ]]
        ];
        return array_merge( $layout, BRPB_Controls_Helper::get_style_tab() );
    }

    public function render( array $settings, string $element_id, array $children ): void {
        $s = $settings['layout_tab']['structure'] ?? [];
        $classes = [ 'brpb-row-inner-content','brpb-row' ];
        $classes[] = ( ($s['content_width'] ?? 'boxed') === 'full_width' ) ? 'brpb-row-full' : 'brpb-row-boxed';
        $classes[] = 'brpb-gap-'.($s['gap'] ?? 'default');

        echo '<div class="'.esc_attr(implode(' ',$classes)).'">';
        if(!empty($children)){
            $r = new BRPB_Frontend_Renderer();
            foreach($children as $child){ $r->render_element($child); }
        } else if ( isset($_GET['brpb_canvas']) ){
            echo '<div class="brpb-empty-row-placeholder">'.esc_html__('Linha vazia. Adicione uma Coluna.','brnews-pagebuilder').'</div>';
        }
        echo '</div>';
    }
}
