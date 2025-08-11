<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class BRPB_Button_Module extends BRPB_Base_Module {
    public function get_id(): string { return 'button'; }
    public function get_name(): string { return __('Botão','brnews-pagebuilder'); }
    public function get_icon(): string { return 'dashicons-admin-links'; }
    public function get_group(): string { return 'multipurpose_shortcodes'; }

    public function get_controls(): array {
        $content=[ 'content_tab'=>[ 'label'=>__('Conteúdo do Botão','brnews-pagebuilder'),'type'=>'tab','controls'=>[
            'main'=>[ 'label'=>__('Principal','brnews-pagebuilder'),'type'=>'section','controls'=>[
                'text'=>[ 'type'=>'text','label'=>__('Texto','brnews-pagebuilder'),'default'=>'Clique aqui' ],
                'link'=>[ 'type'=>'link','label'=>__('Link','brnews-pagebuilder'),'default'=>[ 'url'=>'#' ] ],
                'alignment'=> BRPB_Controls_Helper::get_alignment_control('{{WRAPPER}}','left'),
            ]]
        ] ] ];
        return array_merge( $content, BRPB_Controls_Helper::get_style_tab([
            'btnstyle'=>[ 'label'=>__('Estilo do Botão','brnews-pagebuilder'),'type'=>'section','controls'=>[
                'txt_color'=> BRPB_Controls_Helper::get_color_control('Cor do Texto','#fff','{{WRAPPER}} .brpb-button'),
                'bg_color'=> BRPB_Controls_Helper::get_color_control('Cor de Fundo','#2271b1','{{WRAPPER}} .brpb-button','background-color'),
            ]]
        ]) );
    }

    public function render( array $settings, string $element_id, array $children ): void {
        $m = $settings['content_tab']['main'] ?? [];
        $text = esc_html($m['text'] ?? '');
        $link = $m['link']['url'] ?? '#';
        $target = !empty($m['link']['is_external']) ? ' target="_blank"' : '';
        $nofollow = !empty($m['link']['nofollow']) ? ' rel="nofollow"' : '';
        echo '<div class="brpb-button-wrapper"><a href="'.esc_url($link).'" class="brpb-button"'.$target.$nofollow.'><span class="brpb-button-text">'.$text.'</span></a></div>';
    }
}
