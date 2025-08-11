<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class BRPB_Big_Grid_Flex_1_Module extends BRPB_Base_Module {
    public function get_id(): string { return 'big-grid-flex-1'; }
    public function get_name(): string { return __('Big Grid Flex 1','brnews-pagebuilder'); }
    public function get_icon(): string { return 'dashicons-layout'; }
    public function get_group(): string { return 'big_grid_shortcodes'; }

    public function get_controls(): array {
        $content = [
            'content_tab'=>[ 'label'=>__('Conteúdo & Layout','brnews-pagebuilder'),'type'=>'tab','controls'=>[
                'header'=>[ 'label'=>__('Cabeçalho','brnews-pagebuilder'),'type'=>'section','controls'=>[
                    'header_text'=>[ 'type'=>'text','label'=>__('Título do Bloco','brnews-pagebuilder'),'default'=>'Últimas Notícias' ],
                ]],
                'query'=> BRPB_Controls_Helper::get_post_query_controls(),
                'layout'=>[ 'label'=>__('Exibição','brnews-pagebuilder'),'type'=>'section','controls'=>[
                    'show_category'=>[ 'type'=>'toggle','label'=>__('Mostrar Categoria','brnews-pagebuilder'),'default'=>true ],
                    'show_author'=>[ 'type'=>'toggle','label'=>__('Mostrar Autor','brnews-pagebuilder'),'default'=>false ],
                    'show_date'=>[ 'type'=>'toggle','label'=>__('Mostrar Data','brnews-pagebuilder'),'default'=>true ],
                    'show_excerpt'=>[ 'type'=>'toggle','label'=>__('Mostrar Resumo (post grande)','brnews-pagebuilder'),'default'=>true ],
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
            'post_type'=>'post', 'posts_per_page'=>5, 'post_status'=>'publish', 'ignore_sticky_posts'=>true,
            'orderby'=>$q['orderby'] ?? 'date', 'order'=>$q['order'] ?? 'DESC', 'offset'=>intval($q['offset'] ?? 0),
        ];
        $tax=[];
        if(!empty($q['include_categories'])){
            $ids = is_array($q['include_categories']) ? $q['include_categories'] : array_map('intval', array_filter(array_map('trim', explode(',', (string)$q['include_categories']))));
            if($ids) $tax[] = [ 'taxonomy'=>'category','field'=>'term_id','terms'=>$ids ];
        }
        if(!empty($q['include_tags'])){
            $ids = is_array($q['include_tags']) ? $q['include_tags'] : array_map('intval', array_filter(array_map('trim', explode(',', (string)$q['include_tags']))));
            if($ids) $tax[] = [ 'taxonomy'=>'post_tag','field'=>'term_id','terms'=>$ids ];
        }
        if($tax){ $args['tax_query']=$tax; }

        $loop = new WP_Query($args);
        if(!empty($c['header']['header_text'])) echo '<div class="brpb-block-header"><h4>'.esc_html($c['header']['header_text']).'</h4></div>';

        if($loop->have_posts()){
            echo '<div class="brpb-big-grid-flex-1">';
            $i=0;
            while($loop->have_posts()){ $loop->the_post(); $i++;
                if($i===1){
                    echo '<div class="brpb-grid-post-large">';
                    echo '<div class="brpb-post-thumb"><a href="'.esc_url(get_permalink()).'">'.get_the_post_thumbnail(get_the_ID(),'large').'</a></div>';
                    echo '<div class="brpb-entry-content">';
                    if(($l['show_category'] ?? true)){
                        $cats = get_the_category();
                        if($cats){ echo '<a class="brpb-post-category" href="'.esc_url(get_category_link($cats[0]->term_id)).'">'.esc_html($cats[0]->name).'</a>'; }
                    }
                    the_title('<h3 class="brpb-entry-title"><a href="'.esc_url(get_permalink()).'">','</a></h3>');
                    if( ($l['show_author'] ?? false) || ($l['show_date'] ?? true) ){
                        echo '<div class="brpb-entry-meta">';
                        if($l['show_author'] ?? false){ echo '<span class="brpb-author-name">'.esc_html(get_the_author()).'</span>'; }
                        if($l['show_date'] ?? true){ echo '<span class="brpb-post-date">'.esc_html(get_the_date()).'</span>'; }
                        echo '</div>';
                    }
                    if($l['show_excerpt'] ?? true){ echo '<div class="brpb-entry-excerpt">'.esc_html(get_the_excerpt()).'</div>'; }
                    echo '</div></div><div class="brpb-grid-posts-small">';
                } else {
                    echo '<div class="brpb-grid-post-small">';
                    echo '<div class="brpb-post-thumb-small"><a href="'.esc_url(get_permalink()).'">'.get_the_post_thumbnail(get_the_ID(),'thumbnail').'</a></div>';
                    echo '<div class="brpb-entry-content-small">';
                    the_title('<h4 class="brpb-entry-title"><a href="'.esc_url(get_permalink()).'">','</a></h4>');
                    if($l['show_date'] ?? true){ echo '<div class="brpb-entry-meta"><span class="brpb-post-date">'.esc_html(get_the_date()).'</span></div>'; }
                    echo '</div></div>';
                }
            }
            echo '</div></div>';
        } else {
            echo '<p>'.esc_html__('Nenhum post encontrado.','brnews-pagebuilder').'</p>';
        }
        wp_reset_postdata();
    }
}
