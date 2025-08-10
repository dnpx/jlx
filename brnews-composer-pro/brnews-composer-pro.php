<?php
/**
 * Plugin Name: Brnews Composer Pro
 * Plugin URI:  https://www.brnews.com/brnews-composer-pro
 * Description: A powerful and flexible page builder plugin for WordPress.
 * Version:     9.0.0
 * Author:      Brnews
 * Author URI:  https://www.brnews.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: brnews-composer-pro
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/includes/class-plugin.php';

function brcp() {
	return BRCP_Plugin::instance();
}

// Get the plugin running.
brcp();
