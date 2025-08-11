<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class BRPB_Call_To_Action_Module extends BRPB_Base_Module {
    public function get_id(): string { return 'call-to-action'; }
    public function get_name(): string { return __('Chamada para Ação','brnews-pagebuilder'); }
    public function get_icon(): string { return 'dashicons-megaphone'; }
    public function get_group(): string { return 'multipurpose_shortcodes'; }

    public function get_controls(): array {
        $content=[ 'content_tab'=>[ 'label'=>__('Conteúdo','brnews-pagebuilder'),'type'=>'tab','controls'=>[
            'title'=>[ 'label'=>__('Título','brnews-pagebuilder'),'type'=>'section','controls'=> BRPB_Controls_Helper::get_title_controls('Título da Chamada',['html_tag'=>'h2']) ],
            'desc'=>[ 'label'=>__('Descrição','brnews-pagebuilder'),'type'=>'section','controls'=>[
                'description_text'=>[ 'type'=>'textarea','label'=>__('Texto','brnews-pagebuilder'),'default'=>'Descreva sua oferta aqui.' ]
            ]],
            'button'=>[ 'label'=>__('Botão','brnews-pagebuilder'),'type'=>'section','controls'=>[
                'button_text'=>[ 'type'=>'text','label'=>__('Texto do Botão','brnews-pagebuilder'),'default'=>'Saiba mais' ],
                'button_link'=>[ 'type'=>'link','label'=>__('Link','brnews-pagebuilder') ]
            ]]
        ] ] ];
        return array_merge( $content, BRPB_Controls_Helper::get_style_tab() );
    }

    public function render( array $settings, string $element_id, array $children ): void {
        $t = $settings['content_tab']['title'] ?? [];
        $d = $settings['content_tab']['desc'] ?? [];
        $b = $settings['content_tab']['button'] ?? [];
        $tag = tag_escape($t['html_tag'] ?? 'h2');
        echo '<div class="brpb-cta-box">';
        if(!empty($t['text'])){
            echo '<'.$tag.' class="brpb-cta-title">';
            if(!empty($t['link']['url'])) echo '<a href="'.esc_url($t['link']['url']).'">';
            echo esc_html($t['text']);
            if(!empty($t['link']['url'])) echo '</a>';
            echo '</'.$tag.'>';
        }
        if(!empty($d['description_text'])) echo '<div class="brpb-cta-description">'.wp_kses_post($d['description_text']).'</div>';
        if(!empty($b['button_text'])){
            $u = $b['button_link']['url'] ?? '#';
            $target = !empty($b['button_link']['is_external']) ? ' target="_blank"' : '';
            $nofollow = !empty($b['button_link']['nofollow']) ? ' rel="nofollow"' : '';
            echo '<div class="brpb-cta-button-wrapper"><a class="brpb-cta-button" href="'.esc_url($u).'"'.$target.$nofollow.'>'.esc_html($b['button_text']).'</a></div>';
        }
        echo '</div>';
    }
}
