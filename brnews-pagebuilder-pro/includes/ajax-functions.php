<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action('wp_ajax_brpb_get_element_controls','brpb_ajax_get_element_controls');
function brpb_ajax_get_element_controls(){
    check_ajax_referer('brpb_editor_nonce','nonce');
    if( ! current_user_can('edit_posts') ){
        wp_send_json_error([ 'message'=>__('Permissão negada.','brnews-pagebuilder') ]);
    }
    $type = isset($_POST['element_type']) ? sanitize_text_field($_POST['element_type']) : '';
    $element_data_json = isset($_POST['element_data']) ? wp_unslash($_POST['element_data']) : '{}';
    $element = json_decode($element_data_json,true);
    if(empty($type) || json_last_error()!==JSON_ERROR_NONE){
        wp_send_json_error([ 'message'=>__('Dados inválidos.','brnews-pagebuilder') ]);
    }

    $module = brpb()->registry->get_module($type);
    if( ! $module ){ wp_send_json_error([ 'message'=>__('Módulo não encontrado.','brnews-pagebuilder') ]); }

    ob_start();
    echo '<h3>'.esc_html($module->get_name()).'</h3>';
    echo '<p class="brpb-element-id-display">ID: <code>'.esc_html($element['id']).'</code></p>';
    $schema = $module->get_controls();
    foreach($schema as $tab_id=>$tab){
        echo '<div class="brpb-inspector-tab" id="brpb-tab-'.esc_attr($tab_id).'">';
        echo '<h4>'.esc_html($tab['label'] ?? ucfirst($tab_id)).'</h4>';
        brpb_render_controls_group($tab['controls'] ?? [], $element['settings'] ?? [], $tab_id);
        echo '</div>';
    }
    echo '<a href="#" class="brpb-delete-element">'.esc_html__('Deletar Elemento','brnews-pagebuilder').'</a>';
    $html = ob_get_clean();
    wp_send_json_success([ 'html'=>$html ]);
}

/**
 * Render recursivo de grupos/controles. Converte tipos complexos em inputs práticos.
 */
function brpb_render_controls_group(array $controls, array $current_settings, string $prefix=''){
    foreach($controls as $id=>$control){
        $type = $control['type'] ?? 'text';
        $setting_id = ltrim($prefix.'.'.$id,'.');

        // Recursivos (sections, visibility, tabs)
        if(in_array($type,['section','visibility'],true)){
            echo '<div class="brpb-section">';
            if(!empty($control['label'])) echo '<h4>'.esc_html($control['label']).'</h4>';
            $child = $control['controls'] ?? [];
            brpb_render_controls_group($child, $current_settings, $setting_id);
            echo '</div>';
            continue;
        }
        if($type==='tabs'){
            $tabs = $control['tabs'] ?? [];
            foreach($tabs as $tab_key=>$tab_def){
                echo '<div class="brpb-subtab"><h4>'.esc_html($tab_def['label'] ?? $tab_key).'</h4>';
                brpb_render_controls_group($tab_def['controls'] ?? [], $current_settings, $setting_id.'.'.$tab_key);
                echo '</div>';
            }
            continue;
        }

        // resolve valor atual navegando pela árvore
        $value = $current_settings;
        foreach(explode('.',$setting_id) as $k){ $value = $value[$k] ?? null; }
        if($value===null && isset($control['default'])) $value = $control['default'];

        echo '<div class="brpb-control-wrapper" data-control-type="'.esc_attr($type).'">';
        if(!in_array($type,['toggle'])){
            echo '<label for="brpb-control-'.esc_attr($setting_id).'">'.esc_html($control['label'] ?? ucfirst($id)).'</label>';
        }

        switch($type){
            case 'text':
            case 'number':
                printf('<input type="%s" id="brpb-control-%s" data-setting-id="%s" value="%s" />',
                    esc_attr($type), esc_attr($setting_id), esc_attr($setting_id), esc_attr((string)($value ?? ''))
                );
                break;
            case 'textarea':
                printf('<textarea id="brpb-control-%s" data-setting-id="%s" rows="4">%s</textarea>',
                    esc_attr($setting_id), esc_attr($setting_id), esc_textarea((string)($value ?? ''))
                );
                break;
            case 'select':
                echo '<select id="brpb-control-'.esc_attr($setting_id).'" data-setting-id="'.esc_attr($setting_id).'">';
                foreach(($control['options'] ?? []) as $val=>$label){
                    printf('<option value="%s"%s>%s</option>', esc_attr($val), selected($value,$val,false), esc_html($label));
                }
                echo '</select>';
                break;
            case 'color':
                printf('<input type="text" class="brpb-color-picker" id="brpb-control-%s" data-setting-id="%s" value="%s" />',
                    esc_attr($setting_id), esc_attr($setting_id), esc_attr((string)($value ?? ''))
                );
                break;
            case 'toggle':
                $checked = !empty($value);
                printf('<label class="brpb-switch"><input type="checkbox" id="brpb-control-%s" data-setting-id="%s" %s><span class="brpb-slider"></span></label>',
                    esc_attr($setting_id), esc_attr($setting_id), checked($checked,true,false)
                );
                break;
            case 'select2':
                // Sem Select2 nativo: tratamos como input de IDs (CSV) por simplicidade
                $v = is_array($value) ? implode(',',$value) : (string)($value ?? '');
                printf('<input type="text" id="brpb-control-%s" data-setting-id="%s" value="%s" placeholder="%s" />',
                    esc_attr($setting_id), esc_attr($setting_id), esc_attr($v), esc_attr__('IDs separados por vírgula','brnews-pagebuilder')
                );
                break;
            case 'link':
                $url = is_array($value) ? ($value['url'] ?? '') : (string)($value ?? '');
                printf('<input type="text" id="brpb-control-%s-url" data-setting-id="%s.url" value="%s" placeholder="https://..." />',
                    esc_attr($setting_id), esc_attr($setting_id), esc_attr($url)
                );
                echo '<label><input type="checkbox" data-setting-id="'.esc_attr($setting_id).'.is_external" '.checked($value['is_external'] ?? false, true, false).'> '.__('Abrir em nova aba','brnews-pagebuilder').'</label><br>';
                echo '<label><input type="checkbox" data-setting-id="'.esc_attr($setting_id).'.nofollow" '.checked($value['nofollow'] ?? false, true, false).'> '.__('nofollow','brnews-pagebuilder').'</label>';
                break;
            case 'slider':
                $val = is_array($value) ? ($value['desktop'] ?? 0) : (int)($value ?? 0);
                printf('<input type="number" id="brpb-control-%s" data-setting-id="%s.desktop" value="%s" min="%s" max="%s" />',
                    esc_attr($setting_id), esc_attr($setting_id),
                    esc_attr($val),
                    esc_attr($control['min'] ?? 0), esc_attr($control['max'] ?? 500)
                );
                if(!empty($control['unit'])) echo '<small>&nbsp;'.$control['unit'].'</small>';
                break;
            case 'choices':
                echo '<select id="brpb-control-'.esc_attr($setting_id).'" data-setting-id="'.esc_attr($setting_id).'">';
                foreach(($control['options'] ?? []) as $val=>$opt){
                    $title = is_array($opt) ? ($opt['title'] ?? $val) : $opt;
                    printf('<option value="%s"%s>%s</option>', esc_attr($val), selected($value,$val,false), esc_html($title));
                }
                echo '</select>';
                break;
            case 'dimensions':
                $devices = ['desktop'=>'Desktop','tablet'=>'Tablet','mobile'=>'Mobile'];
                foreach($devices as $dev_key=>$dev_label){
                    $dv = is_array($value) && isset($value[$dev_key]) ? $value[$dev_key] : [];
                    echo '<fieldset style="border:1px solid #eee;padding:8px;margin-bottom:8px"><legend>'.esc_html($dev_label).'</legend>';
                    foreach(['top'=>'Topo','right'=>'Direita','bottom'=>'Baixo','left'=>'Esquerda'] as $k=>$lab){
                        $cur = isset($dv[$k]) ? intval($dv[$k]) : 0;
                        printf('<label style="display:inline-block;width:70px">%s</label><input style="width:80px" type="number" data-setting-id="%s.%s.%s" value="%s" /> ',
                            esc_html($lab), esc_attr($setting_id), esc_attr($dev_key), esc_attr($k), esc_attr($cur)
                        );
                    }
                    echo '</fieldset>';
                }
                break;
            case 'border':
                $width = is_array($value)? intval($value['width'] ?? 0) : 0;
                $type  = is_array($value)? ($value['type'] ?? 'none') : 'none';
                $color = is_array($value)? ($value['color'] ?? '#000000') : '#000000';
                printf('<label>%s</label><input type="number" data-setting-id="%s.width" value="%s" />px ',
                    esc_html__('Espessura','brnews-pagebuilder'), esc_attr($setting_id), esc_attr($width));
                echo '<select data-setting-id="'.esc_attr($setting_id).'.type">';
                foreach(['none'=>'none','solid'=>'solid','dashed'=>'dashed','dotted'=>'dotted','double'=>'double'] as $opt){
                    printf('<option value="%s"%s>%s</option>', esc_attr($opt), selected($type,$opt,false), esc_html($opt));
                }
                echo '</select> ';
                printf('<input type="text" class="brpb-color-picker" data-setting-id="%s.color" value="%s" />', esc_attr($setting_id), esc_attr($color));
                break;
            case 'background':
                $color = is_array($value)? ($value['color'] ?? '') : '';
                $img   = is_array($value)? ($value['image_url'] ?? '') : '';
                printf('<label>%s</label><input type="text" class="brpb-color-picker" data-setting-id="%s.color" value="%s" />',
                    esc_html__('Cor','brnews-pagebuilder'), esc_attr($setting_id), esc_attr($color));
                echo '<br>';
                printf('<label>%s</label><input type="text" data-setting-id="%s.image_url" value="%s" placeholder="https://.../bg.jpg" />',
                    esc_html__('Imagem (URL)','brnews-pagebuilder'), esc_attr($setting_id), esc_attr($img));
                break;
            case 'media':
                $url = is_array($value) ? ($value['url'] ?? '') : (string)($value ?? '');
                printf('<input type="text" id="brpb-control-%s" data-setting-id="%s.url" value="%s" placeholder="https://.../arquivo.jpg" />',
                    esc_attr($setting_id), esc_attr($setting_id), esc_attr($url));
                break;
            default:
                printf('<input type="text" id="brpb-control-%s" data-setting-id="%s" value="%s" />',
                    esc_attr($setting_id), esc_attr($setting_id), esc_attr((string)($value ?? ''))
                );
                break;
        }
        if(!empty($control['description'])){
            echo '<p class="description">'.wp_kses_post($control['description']).'</p>';
        }
        echo '</div>';
    }
}

add_action('wp_ajax_brpb_save_layout','brpb_ajax_save_layout');
function brpb_ajax_save_layout(){
    check_ajax_referer('brpb_editor_nonce','nonce');
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    if( ! current_user_can('edit_post',$post_id) ){
        wp_send_json_error([ 'message'=>__('Permissão negada.','brnews-pagebuilder') ]);
    }
    $layout_json = isset($_POST['layout_data']) ? wp_unslash($_POST['layout_data']) : '[]';
    $data = json_decode($layout_json,true);
    if(json_last_error()!==JSON_ERROR_NONE){
        wp_send_json_error([ 'message'=>__('Layout inválido (JSON).','brnews-pagebuilder') ]);
    }

    // Gera CSS
    $css = (new BRPB_Css_Generator())->generate_for_layout($data);
    $upload = wp_upload_dir();
    $dir = trailingslashit($upload['basedir']).'brpb-css';
    if( ! file_exists($dir) ) wp_mkdir_p($dir);
    $file = $dir.'/brpb-'.$post_id.'.css';
    $ok = file_put_contents($file, $css);
    if(false === $ok){
        wp_send_json_error([ 'message'=>__('Falha ao escrever CSS em uploads/brpb-css.','brnews-pagebuilder') ]);
    }

    update_post_meta($post_id,'_brpb_layout_data', wp_slash($layout_json));
    update_post_meta($post_id,'_brpb_css_version', time());

    wp_send_json_success([ 'message'=>__('Layout salvo!','brnews-pagebuilder') ]);
}
