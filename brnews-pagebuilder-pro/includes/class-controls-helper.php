<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class BRPB_Controls_Helper {

    public static function get_style_tab( array $additional_controls = [] ): array {
        $default_controls = [
            'padding'       => [ 'type'=>'dimensions', 'label'=>__('Padding','brnews-pagebuilder') ],
            'margin'        => [ 'type'=>'dimensions', 'label'=>__('Margin','brnews-pagebuilder') ],
            'background'    => [ 'type'=>'background', 'label'=>__('Fundo','brnews-pagebuilder') ],
            'border'        => [ 'type'=>'border', 'label'=>__('Borda','brnews-pagebuilder') ],
            'visibility'    => [ 'type'=>'visibility', 'label'=>__('Visibilidade Responsiva','brnews-pagebuilder'),
                'controls'=>[
                    'hide_desktop'=>[ 'type'=>'toggle','label'=>__('Esconder no Desktop','brnews-pagebuilder') ],
                    'hide_tablet' =>[ 'type'=>'toggle','label'=>__('Esconder no Tablet','brnews-pagebuilder') ],
                    'hide_mobile' =>[ 'type'=>'toggle','label'=>__('Esconder no Celular','brnews-pagebuilder') ],
                ]
            ],
        ];
        return [
            'style_tab' => [
                'label' => __( 'Estilo', 'brnews-pagebuilder' ),
                'type'  => 'tab',
                'controls' => array_merge( $additional_controls, $default_controls ),
            ]
        ];
    }

    public static function get_post_query_controls(): array {
        return [
            'query_section' => [
                'label'=>__('Filtro de Conteúdo','brnews-pagebuilder'), 'type'=>'section',
                'controls'=>[
                    'include_categories'=>[ 'type'=>'select2', 'label'=>__('IDs de Categorias','brnews-pagebuilder'), 'description'=>__('Separe por vírgula ou use o seletor.','brnews-pagebuilder') ],
                    'include_tags'      =>[ 'type'=>'select2', 'label'=>__('IDs de Tags','brnews-pagebuilder') ],
                    'posts_per_page'    =>[ 'type'=>'number','label'=>__('Limite de Posts','brnews-pagebuilder'),'default'=>6 ],
                    'offset'            =>[ 'type'=>'number','label'=>__('Offset','brnews-pagebuilder'),'default'=>0 ],
                    'orderby'           =>[ 'type'=>'select','label'=>__('Ordenar por','brnews-pagebuilder'),'default'=>'date','options'=>[ 'date'=>'Data', 'comment_count'=>'Comentários','rand'=>'Aleatório','title'=>'Título' ]],
                    'order'             =>[ 'type'=>'select','label'=>__('Ordem','brnews-pagebuilder'),'default'=>'DESC','options'=>[ 'DESC'=>'DESC','ASC'=>'ASC' ]],
                ]
            ]
        ];
    }

    public static function get_alignment_control( string $selector, string $default='left' ): array {
        return [ 'type'=>'choices','label'=>__('Alinhamento','brnews-pagebuilder'),'default'=>$default,'selector'=>$selector,'property'=>'text-align','options'=>[
            'left'=>[ 'title'=>'Esquerda' ], 'center'=>[ 'title'=>'Centro' ], 'right'=>[ 'title'=>'Direita' ], 'justify'=>[ 'title'=>'Justificado' ]
        ]];
    }

    public static function get_color_control( string $label, string $default, string $selector, string $property='color' ): array {
        return [ 'type'=>'color','label'=>__( $label, 'brnews-pagebuilder'), 'default'=>$default, 'selector'=>$selector, 'property'=>$property ];
    }

    public static function get_typography_control( string $label, string $selector ): array {
        return [ 'type'=>'typography','label'=>__( $label, 'brnews-pagebuilder'), 'selector'=>$selector ];
    }

    public static function get_title_controls( string $default_text='Meu Título', array $defaults=[] ): array {
        return [
            'text'=>[ 'type'=>'text','label'=>__('Texto','brnews-pagebuilder'),'default'=>$defaults['text'] ?? $default_text ],
            'html_tag'=>[ 'type'=>'select','label'=>__('Tag HTML','brnews-pagebuilder'),'default'=>$defaults['html_tag'] ?? 'h3','options'=>[ 'h1'=>'H1','h2'=>'H2','h3'=>'H3','h4'=>'H4','h5'=>'H5','h6'=>'H6','div'=>'div','p'=>'p','span'=>'span' ] ],
            'link'=>[ 'type'=>'link','label'=>__('Link (opcional)','brnews-pagebuilder') ],
        ];
    }
}
