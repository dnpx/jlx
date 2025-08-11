<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class BRPB_Raw_HTML_Module extends BRPB_Base_Module {
    public function get_id(): string { return 'raw-html'; }
    public function get_name(): string { return __('HTML Livre','brnews-pagebuilder'); }
    public function get_icon(): string { return 'dashicons-editor-code'; }
    public function get_group(): string { return 'extended_shortcodes'; }

    public function get_controls(): array {
        $content=[ 'content_tab'=>[ 'label'=>__('Conteúdo','brnews-pagebuilder'),'type'=>'tab','controls'=>[
            'html'=>[ 'label'=>__('HTML','brnews-pagebuilder'),'type'=>'section','controls'=>[
                'code'=>[ 'type'=>'textarea','label'=>__('Código HTML','brnews-pagebuilder') ]
            ]]
        ] ] ];
        return array_merge( $content, BRPB_Controls_Helper::get_style_tab() );
    }

    public function render( array $settings, string $element_id, array $children ): void {
        $code = $settings['content_tab']['html']['code'] ?? '';
        if($code){ echo wp_kses_post($code); }
        elseif( isset($_GET['brpb_canvas']) ){ echo '<div class="brpb-rawhtml-placeholder">'.esc_html__('Clique para editar HTML.','brnews-pagebuilder').'</div>'; }
    }
}
