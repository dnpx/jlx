<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class BRPB_Author_Box_Module extends BRPB_Base_Module {
    public function get_id(): string { return 'author-box'; }
    public function get_name(): string { return __('Caixa de Autor','brnews-pagebuilder'); }
    public function get_icon(): string { return 'dashicons-admin-users'; }
    public function get_group(): string { return 'extended_shortcodes'; }

    public function get_controls(): array {
        $content=[ 'content_tab'=>[ 'label'=>__('Configuração do Autor','brnews-pagebuilder'),'type'=>'tab','controls'=>[
            'src'=>[ 'label'=>__('Fonte','brnews-pagebuilder'),'type'=>'section','controls'=>[
                'author_source'=>[ 'type'=>'select','label'=>__('Exibir autor de','brnews-pagebuilder'),'default'=>'current','options'=>[ 'current'=>'Post Atual','specific'=>'Autor Específico' ] ],
                'specific_author'=>[ 'type'=>'number','label'=>__('ID do Autor (quando específico)','brnews-pagebuilder') ]
            ]],
            'layout'=>[ 'label'=>__('Exibir','brnews-pagebuilder'),'type'=>'section','controls'=>[
                'show_avatar'=>[ 'type'=>'toggle','label'=>__('Avatar','brnews-pagebuilder'),'default'=>true ],
                'show_name'=>[ 'type'=>'toggle','label'=>__('Nome','brnews-pagebuilder'),'default'=>true ],
                'show_bio'=>[ 'type'=>'toggle','label'=>__('Biografia','brnews-pagebuilder'),'default'=>true ],
                'show_posts_link'=>[ 'type'=>'toggle','label'=>__('Link para posts','brnews-pagebuilder'),'default'=>true ],
            ]]
        ] ] ];
        return array_merge( $content, BRPB_Controls_Helper::get_style_tab() );
    }

    public function render( array $settings, string $element_id, array $children ): void {
        $src = $settings['content_tab']['src'] ?? [];
        $lay = $settings['content_tab']['layout'] ?? [];
        $author_id = 0;
        if( ($src['author_source'] ?? 'current') === 'current' && is_singular() ){
            $author_id = (int) get_the_author_meta('ID');
        } else {
            $author_id = (int) ($src['specific_author'] ?? 0);
        }
        if(!$author_id){
            if(isset($_GET['brpb_canvas'])) echo '<p>'.esc_html__('Selecione um autor.','brnews-pagebuilder').'</p>';
            return;
        }
        $name = get_the_author_meta('display_name',$author_id);
        $bio = get_the_author_meta('description',$author_id);
        $url = get_author_posts_url($author_id);

        echo '<div class="brpb-author-box-wrapper">';
        if($lay['show_avatar'] ?? true){
            echo '<div class="brpb-author-avatar"><a href="'.esc_url($url).'">'.get_avatar($author_id,96,'',esc_attr($name)).'</a></div>';
        }
        echo '<div class="brpb-author-content">';
        if($lay['show_name'] ?? true){
            echo '<h4 class="brpb-author-name"><a href="'.esc_url($url).'">'.esc_html($name).'</a></h4>';
        }
        if(($lay['show_bio'] ?? true) && $bio){
            echo '<p class="brpb-author-bio">'.wp_kses_post($bio).'</p>';
        }
        if($lay['show_posts_link'] ?? true){
            printf('<a class="brpb-author-posts-link" href="%s">%s</a>', esc_url($url), esc_html(sprintf(__('Ver todos os posts de %s','brnews-pagebuilder'), $name)));
        }
        echo '</div></div>';
    }
}
