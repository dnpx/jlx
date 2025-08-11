<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class BRPB_Weather_Module extends BRPB_Base_Module {
    public function get_id(): string { return 'weather'; }
    public function get_name(): string { return __('Clima / Tempo','brnews-pagebuilder'); }
    public function get_icon(): string { return 'dashicons-cloud'; }
    public function get_group(): string { return 'extended_shortcodes'; }

    public function get_controls(): array {
        $content=[ 'content_tab'=>[ 'label'=>__('Configuração do Clima','brnews-pagebuilder'),'type'=>'tab','controls'=>[
            'api'=>[ 'label'=>__('API','brnews-pagebuilder'),'type'=>'section','controls'=>[
                'api_key'=>[ 'type'=>'text','label'=>__('Chave API (OpenWeatherMap)','brnews-pagebuilder') ],
                'location'=>[ 'type'=>'text','label'=>__('Localização','brnews-pagebuilder'),'default'=>'Sao Paulo,BR' ],
                'units'=>[ 'type'=>'select','label'=>__('Unidades','brnews-pagebuilder'),'default'=>'metric','options'=>[ 'metric'=>'Celsius','imperial'=>'Fahrenheit','standard'=>'Kelvin' ] ]
            ]]
        ] ] ];
        return array_merge( $content, BRPB_Controls_Helper::get_style_tab() );
    }

    public function render( array $settings, string $element_id, array $children ): void {
        $api = $settings['content_tab']['api'] ?? [];
        $key = trim($api['api_key'] ?? '');
        $loc = trim($api['location'] ?? '');
        $units = $api['units'] ?? 'metric';
        if(!$key || !$loc){ if(isset($_GET['brpb_canvas'])) echo '<p>'.esc_html__('Configure a chave API e localização.','brnews-pagebuilder').'</p>'; return; }

        $transient = 'brpb_weather_'.md5($loc.$units.$key);
        $data = get_transient($transient);
        if(false === $data){
            $url = sprintf('https://api.openweathermap.org/data/2.5/weather?q=%s&units=%s&appid=%s&lang=pt_br', urlencode($loc), $units, $key);
            $res = wp_remote_get($url,[ 'timeout'=>10 ]);
            if( is_wp_error($res) || 200 !== wp_remote_retrieve_response_code($res) ){
                set_transient($transient,[ 'error'=>true ], 5*MINUTE_IN_SECONDS);
                $data = get_transient($transient);
            } else {
                $data = json_decode( wp_remote_retrieve_body($res), true );
                set_transient($transient, $data, HOUR_IN_SECONDS);
            }
        }
        if( isset($data['error']) || empty($data['main']) ){
            if(isset($_GET['brpb_canvas'])) echo '<p>'.esc_html__('Falha ao obter clima.','brnews-pagebuilder').'</p>';
            return;
        }
        $temp = round($data['main']['temp']);
        $icon = $data['weather'][0]['icon'];
        $desc = ucwords($data['weather'][0]['description']);
        $name = $data['name'];

        echo '<div class="brpb-weather-widget"><div class="brpb-weather-icon">';
        echo '<img src="https://openweathermap.org/img/wn/'.esc_attr($icon).'@2x.png" alt="'.esc_attr($desc).'" />';
        echo '</div><div class="brpb-weather-details"><span class="brpb-weather-temp">'.esc_html($temp).'°</span>';
        echo '<div class="brpb-weather-location-desc"><span class="brpb-weather-location">'.esc_html($name).'</span> <span class="brpb-weather-description">'.esc_html($desc).'</span></div></div></div>';
    }
}
