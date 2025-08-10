<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class BRCP_Install {

	public static function activate() {
		// Create cache directory
		if ( ! is_dir( BRCP_CACHE_DIR ) ) {
			wp_mkdir_p( BRCP_CACHE_DIR );
		}

		// Flush rewrite rules
		flush_rewrite_rules();

		// Add default options
		add_option( 'brcp_version', BRCP_VERSION );
		add_option( 'brcp_settings', [
			'enable_cache'     => true,
			'cache_duration'   => 3600,
			'enable_analytics' => false,
			'default_template' => 'default',
		] );
	}

	public static function deactivate() {
		// Clear cache directory
		if ( is_dir( BRCP_CACHE_DIR ) ) {
			self::delete_directory( BRCP_CACHE_DIR );
		}

		flush_rewrite_rules();
	}

	private static function delete_directory( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$files = array_diff( scandir( $dir ), [ '.', '..' ] );

		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;

			if ( is_dir( $path ) ) {
				self::delete_directory( $path );
			} else {
				unlink( $path );
			}
		}

		rmdir( $dir );
	}
}
