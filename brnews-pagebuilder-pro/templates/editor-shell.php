<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0;
$post = get_post($post_id);
global $post;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?php printf( esc_html__( 'Editando %s — Brnews Pagebuilder', 'brnews-pagebuilder' ), esc_html( $post->post_title ) ); ?></title>
<?php wp_head(); ?>
</head>
<body class="wp-admin wp-core-ui js brpb-editor-body">
<div id="brpb-editor-wrapper">
  <div id="brpb-header">
    <h1><?php printf( __('Editando: %s','brnews-pagebuilder'), '<span class="brpb-post-title">'.esc_html($post->post_title).'</span>' ); ?></h1>
    <div id="brpb-header-controls">
      <a href="<?php echo esc_url( get_edit_post_link($post_id) ); ?>" class="button"><?php _e('Voltar','brnews-pagebuilder'); ?></a>
      <a href="<?php echo esc_url( get_permalink($post_id) ); ?>" class="button" target="_blank"><?php _e('Visualizar','brnews-pagebuilder'); ?> <span class="dashicons dashicons-external"></span></a>
      <button id="brpb-save-button" class="button button-primary"><?php _e('Salvar Layout','brnews-pagebuilder'); ?></button>
    </div>
  </div>
  <div id="brpb-main">
    <div id="brpb-panel">
      <div id="brpb-panel-elements">
        <?php
        $grouped = brpb()->registry->get_all_modules_by_group();
        $titles = [
          'structural'=>__('Estrutura','brnews-pagebuilder'),
          'multipurpose_shortcodes'=>__('Multiuso','brnews-pagebuilder'),
          'block_shortcodes'=>__('Blocos de Conteúdo','brnews-pagebuilder'),
          'big_grid_shortcodes'=>__('Big Grids','brnews-pagebuilder'),
          'extended_shortcodes'=>__('Widgets Estendidos','brnews-pagebuilder'),
          'header_shortcodes'=>__('Cabeçalho','brnews-pagebuilder'),
        ];
        foreach($grouped as $gid=>$mods){
          echo '<h3>'.esc_html($titles[$gid] ?? ucfirst(str_replace('_',' ',$gid))).'</h3><div class="brpb-element-group">';
          foreach($mods as $m){
            echo '<div class="brpb-element-drag-item" data-element-type="'.esc_attr($m->get_id()).'"><span class="dashicons '.esc_attr($m->get_icon()).'"></span> '.esc_html($m->get_name()).'</div>';
          }
          echo '</div>';
        }
        ?>
      </div>
    </div>
    <div id="brpb-canvas-wrapper">
      <iframe id="brpb-canvas-iframe" src="<?php echo esc_url( add_query_arg( ['brpb_canvas'=>'1'], get_permalink($post_id) ) ); ?>" title="<?php esc_attr_e('Preview do Construtor Visual','brnews-pagebuilder'); ?>"></iframe>
    </div>
    <div id="brpb-inspector">
      <div id="brpb-inspector-content">
        <p class="placeholder-text"><?php _e('Selecione um elemento no canvas para editar.','brnews-pagebuilder'); ?></p>
      </div>
    </div>
  </div>
</div>
<?php wp_footer(); ?>
</body></html>
