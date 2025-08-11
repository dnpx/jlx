<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class BRPB_Header_Logo_Module extends BRPB_Base_Module {
    public function get_id(): string { return 'header-logo'; }
    public function get_name(): string { return __('Logo do Cabeçalho','brnews-pagebuilder'); }
    public function get_icon(): string { return 'dashicons-format-image'; }
    public function get_group(): string { return 'header_shortcodes'; }

    public function get_controls(): array {
        $content=[ 'content_tab'=>[ 'label'=>__('Imagens do Logo','brnews-pagebuilder'),'type'=>'tab','controls'=>[
            'logo'=>[ 'label'=>__('Logo','brnews-pagebuilder'),'type'=>'section','controls'=>[
                'logo_image'=>[ 'type'=>'media','label'=>__('Imagem do Logo','brnews-pagebuilder') ],
                'logo_image_retina'=>[ 'type'=>'media','label'=>__('Imagem Retina (2x)','brnews-pagebuilder') ],
                'logo_link'=>[ 'type'=>'link','label'=>__('Link do Logo','brnews-pagebuilder') ],
            ]]
        ] ] ];
        return array_merge( $content, BRPB_Controls_Helper::get_style_tab() );
    }

    public function render( array $settings, string $element_id, array $children ): void {
        $s = $settings['content_tab']['logo'] ?? [];
        $media = $s['logo_image']['url'] ?? '';
        if(!$media && function_exists('get_custom_logo')){
            $custom_logo_id = get_theme_mod('custom_logo');
            if($custom_logo_id){ $media = wp_get_attachment_image_url($custom_logo_id,'full'); }
        }
        if(!$media){
            if(isset($_GET['brpb_canvas'])) echo '<div class="brpb-logo-placeholder">'.esc_html__('Selecione uma imagem de logo.','brnews-pagebuilder').'</div>';
            return;
        }
        $retina = $s['logo_image_retina']['url'] ?? '';
        $srcset = $retina ? ' srcset="'.esc_url($retina).' 2x"' : '';
        $link = $s['logo_link']['url'] ?? home_url('/');

        echo '<div class="brpb-header-logo"><a class="brpb-header-logo-link" href="'.esc_url($link).'">';
        echo '<img src="'.esc_url($media).'" alt="'.esc_attr(get_bloginfo('name')).'"'.$srcset.' />';
        echo '</a></div>';
    }
}
