<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class BRPB_Header_Date_Module extends BRPB_Base_Module {
    public function get_id(): string { return 'header-date'; }
    public function get_name(): string { return __('Data do Cabeçalho','brnews-pagebuilder'); }
    public function get_icon(): string { return 'dashicons-calendar-alt'; }
    public function get_group(): string { return 'header_shortcodes'; }

    public function get_controls(): array {
        $content=[ 'content_tab'=>[ 'label'=>__('Formato da Data','brnews-pagebuilder'),'type'=>'tab','controls'=>[
            'fmt'=>[ 'label'=>__('Formatação','brnews-pagebuilder'),'type'=>'section','controls'=>[
                'format_type'=>[ 'type'=>'select','label'=>__('Formato','brnews-pagebuilder'),'default'=>'default','options'=>[
                    'default'=>__('Padrão do WordPress','brnews-pagebuilder'),
                    'l, j \\d\\e F \\d\\e Y'=>date_i18n('l, j \\d\\e F \\d\\e Y'),
                    'j \\d\\e F, Y'=>date_i18n('j \\d\\e F, Y'),
                    'F j, Y'=>date_i18n('F j, Y'),
                    'd/m/Y'=>date_i18n('d/m/Y'),
                    'custom'=>__('Personalizado','brnews-pagebuilder')
                ]],
                'custom_format'=>[ 'type'=>'text','label'=>__('Formato personalizado','brnews-pagebuilder'),'default'=>'l, j F, Y' ]
            ]]
        ] ] ];
        return array_merge( $content, BRPB_Controls_Helper::get_style_tab() );
    }

    public function render( array $settings, string $element_id, array $children ): void {
        $f = $settings['content_tab']['fmt'] ?? [];
        $type = $f['format_type'] ?? 'default';
        if($type==='default'){ $fmt = get_option('date_format'); }
        elseif($type==='custom'){ $fmt = $f['custom_format'] ?? 'l, j F, Y'; }
        else { $fmt = $type; }
        echo '<div class="brpb-header-date">'.esc_html( date_i18n($fmt) ).'</div>';
    }
}
