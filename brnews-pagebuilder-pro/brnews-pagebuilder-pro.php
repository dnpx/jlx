<?php
/**
 * Plugin Name:       Brnews Pagebuilder PRO
 * Plugin URI:        https://br-news.com/pagebuilder
 * Description:       Um construtor de páginas visual completo, sem dependências externas, com uma vasta biblioteca de elementos para portais de notícias e conteúdo.
 * Version:           2.1.0
 * Author:            Arquiteto de Software Sênior
 * Author URI:        https://example.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       brnews-pagebuilder
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'BRPB_VERSION', '2.1.0' );
define( 'BRPB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BRPB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once BRPB_PLUGIN_DIR . 'includes/class-plugin.php';

/** @return BRPB_Plugin */
function brpb() { return BRPB_Plugin::get_instance(); }

add_action( 'plugins_loaded', 'brpb' );
register_activation_hook( __FILE__, [ 'BRPB_Plugin', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'BRPB_Plugin', 'deactivate' ] );
