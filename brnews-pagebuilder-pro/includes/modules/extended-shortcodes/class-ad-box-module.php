<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class BRPB_Ad_Box_Module extends BRPB_Base_Module {
    public function get_id(): string { return 'ad-box'; }
    public function get_name(): string { return __('Caixa de Anúncio','brnews-pagebuilder'); }
    public function get_icon(): string { return 'dashicons-megaphone'; }
    public function get_group(): string { return 'extended_shortcodes'; }

    public function get_controls(): array {
        $content=[ 'content_tab'=>[ 'label'=>__('Conteúdo do Anúncio','brnews-pagebuilder'),'type'=>'tab','controls'=>[
            'ad'=>[ 'label'=>__('Código','brnews-pagebuilder'),'type'=>'section','controls'=>[
                'ad_code'=>[ 'type'=>'textarea','label'=>__('HTML/Shortcode','brnews-pagebuilder'),'placeholder'=>'[seu_shortcode]' ],
                'description_text'=>[ 'type'=>'text','label'=>__('Texto descritivo','brnews-pagebuilder'),'default'=>'Publicidade' ]
            ] ]
        ] ] ];
        return array_merge( $content, BRPB_Controls_Helper::get_style_tab() );
    }

    public function render( array $settings, string $element_id, array $children ): void {
        $s = $settings['content_tab']['ad'] ?? [];
        echo '<div class="brpb-ad-box-wrapper">';
        if(!empty($s['description_text'])) echo '<span class="brpb-ad-description">'.esc_html($s['description_text']).'</span>';
        if(!empty($s['ad_code'])) echo do_shortcode( wp_kses_post($s['ad_code']) );
        elseif( isset($_GET['brpb_canvas']) ) echo '<div class="brpb-adbox-placeholder">'.esc_html__('Área de Anúncio - cole o código.','brnews-pagebuilder').'</div>';
        echo '</div>';
    }
}
