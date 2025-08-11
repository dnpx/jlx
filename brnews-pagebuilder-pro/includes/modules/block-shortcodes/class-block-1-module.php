<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class BRPB_Block_1_Module extends BRPB_Base_Module {
    public function get_id(): string { return 'block-1'; }
    public function get_name(): string { return __('Bloco de Posts 1','brnews-pagebuilder'); }
    public function get_icon(): string { return 'dashicons-editor-ul'; }
    public function get_group(): string { return 'block_shortcodes'; }

    public function get_controls(): array {
        $content = [
            'content_tab'=>[ 'label'=>__('Conteúdo & Layout','brnews-pagebuilder'),'type'=>'tab','controls'=>[
                'header'=>[ 'label'=>__('Cabeçalho','brnews-pagebuilder'),'type'=>'section','controls'=>[
                    'header_text'=>[ 'type'=>'text','label'=>__('Título','brnews-pagebuilder'),'default'=>'Posts Recentes' ],
                ]],
                'query'=> BRPB_Controls_Helper::get_post_query_controls(),
                'layout'=>[ 'label'=>__('Exibição','brnews-pagebuilder'),'type'=>'section','controls'=>[
                    'show_thumbnail'=>[ 'type'=>'toggle','label'=>__('Mostrar Miniatura','brnews-pagebuilder'),'default'=>true ],
                    'show_date'=>[ 'type'=>'toggle','label'=>__('Mostrar Data','brnews-pagebuilder'),'default'=>true ],
                ]]
            ]]
        ];
        return array_merge( $content, BRPB_Controls_Helper::get_style_tab() );
    }

    public function render( array $settings, string $element_id, array $children ): void {
        $c = $settings['content_tab'] ?? [];
        $q = $c['query']['query_section'] ?? [];
        $l = $c['layout'] ?? [];

        $args = [
            'post_type'=>'post',
            'posts_per_page'=> intval($q['posts_per_page'] ?? 5),
            'post_status'=>'publish',
            'ignore_sticky_posts'=> true,
            'orderby'=> $q['orderby'] ?? 'date',
            'order'=> $q['order'] ?? 'DESC',
            'offset'=> intval($q['offset'] ?? 0),
        ];
        $tax = [];
        if(!empty($q['include_categories'])){
            $ids = is_array($q['include_categories']) ? $q['include_categories'] : array_map('intval', array_filter(array_map('trim', explode(',', (string)$q['include_categories']))));
            if(!empty($ids)) $tax[] = [ 'taxonomy'=>'category','field'=>'term_id','terms'=>$ids ];
        }
        if(!empty($q['include_tags'])){
            $ids = is_array($q['include_tags']) ? $q['include_tags'] : array_map('intval', array_filter(array_map('trim', explode(',', (string)$q['include_tags']))));
            if(!empty($ids)) $tax[] = [ 'taxonomy'=>'post_tag','field'=>'term_id','terms'=>$ids ];
        }
        if($tax){ $args['tax_query'] = $tax; }

        $loop = new WP_Query($args);

        if(!empty($c['header']['header_text'])){
            echo '<div class="brpb-block-header"><h4>'.esc_html($c['header']['header_text']).'</h4></div>';
        }

        if($loop->have_posts()){
            echo '<div class="brpb-block-1-wrap">';
            while($loop->have_posts()){ $loop->the_post();
                echo '<div class="brpb-block-1-post-item">';
                if( ($l['show_thumbnail'] ?? true) && has_post_thumbnail() ){
                    echo '<div class="brpb-post-thumb-small"><a href="'.esc_url(get_permalink()).'">'.get_the_post_thumbnail(get_the_ID(),'thumbnail').'</a></div>';
                }
                echo '<div class="brpb-entry-content">';
                the_title('<h4 class="brpb-entry-title"><a href="'.esc_url(get_permalink()).'">','</a></h4>');
                if( $l['show_date'] ?? true ){
                    echo '<div class="brpb-entry-meta"><span class="brpb-post-date">'.esc_html(get_the_date()).'</span></div>';
                }
                echo '</div></div>';
            }
            echo '</div>';
        } else {
            echo '<p>'.esc_html__('Nenhum post encontrado.','brnews-pagebuilder').'</p>';
        }
        wp_reset_postdata();
    }
}
