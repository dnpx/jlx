<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class BRPB_Post_Grid_Module extends BRPB_Base_Module {
    public function get_id(): string { return 'post-grid'; }
    public function get_name(): string { return __('Grade de Posts (Flexível)','brnews-pagebuilder'); }
    public function get_icon(): string { return 'dashicons-grid-view'; }
    public function get_group(): string { return 'block_shortcodes'; }

    public function get_controls(): array {
        $content = [
            'content_tab'=>[ 'label'=>__('Conteúdo & Layout','brnews-pagebuilder'),'type'=>'tab','controls'=>[
                'header'=>[ 'label'=>__('Cabeçalho','brnews-pagebuilder'),'type'=>'section','controls'=>[
                    'header_text'=>[ 'type'=>'text','label'=>__('Título','brnews-pagebuilder') ],
                ]],
                'layout'=>[ 'label'=>__('Layout','brnews-pagebuilder'),'type'=>'section','controls'=>[
                    'layout_style'=>[ 'type'=>'select','label'=>__('Estilo','brnews-pagebuilder'),'default'=>'block-style-1','options'=>[
                        'block-style-1'=>'Block Style 1 (Lista)',
                        'big-grid-flex-1'=>'Big Grid Flex 1',
                    ]]
                ]],
                'query'=> BRPB_Controls_Helper::get_post_query_controls(),
                'meta'=>[ 'label'=>__('Metadados','brnews-pagebuilder'),'type'=>'section','controls'=>[
                    'show_category'=>[ 'type'=>'toggle','label'=>__('Mostrar Categoria','brnews-pagebuilder'),'default'=>true ],
                    'show_author'=>[ 'type'=>'toggle','label'=>__('Mostrar Autor','brnews-pagebuilder'),'default'=>false ],
                    'show_date'=>[ 'type'=>'toggle','label'=>__('Mostrar Data','brnews-pagebuilder'),'default'=>true ],
                    'show_excerpt'=>[ 'type'=>'toggle','label'=>__('Mostrar Resumo','brnews-pagebuilder'),'default'=>false ],
                    'show_comment_count'=>[ 'type'=>'toggle','label'=>__('Mostrar Nº de Comentários','brnews-pagebuilder'),'default'=>false ],
                ]]
            ]]
        ];
        return array_merge( $content, BRPB_Controls_Helper::get_style_tab() );
    }

    public function render( array $settings, string $element_id, array $children ): void {
        $c = $settings['content_tab'] ?? [];
        $q = $c['query']['query_section'] ?? [];
        $meta = $c['meta'] ?? [];
        $layout = $c['layout']['layout_style'] ?? 'block-style-1';

        $args = [
            'post_type'=>'post',
            'posts_per_page'=> intval($q['posts_per_page'] ?? 6),
            'post_status'=>'publish',
            'ignore_sticky_posts'=> true,
            'orderby'=> $q['orderby'] ?? 'date',
            'order'=> $q['order'] ?? 'DESC',
            'offset'=> intval($q['offset'] ?? 0),
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
        if($tax) $args['tax_query']=$tax;

        $loop = new WP_Query($args);

        if(!empty($c['header']['header_text'])){
            echo '<div class="brpb-block-header"><h4>'.esc_html($c['header']['header_text']).'</h4></div>';
        }

        if($loop->have_posts()){
            $template = BRPB_PLUGIN_DIR.'templates/post-item-parts/'.sanitize_file_name($layout).'.php';
            if(file_exists($template)){
                $posts_query = $loop;
                $meta_settings = $meta;
                include $template;
            } else {
                echo '<p>'.sprintf(esc_html__('Template %s não encontrado.','brnews-pagebuilder'), esc_html($layout).'.php').'</p>';
            }
        } else {
            echo '<p>'.esc_html__('Nenhum post encontrado.','brnews-pagebuilder').'</p>';
        }
        wp_reset_postdata();
    }
}
