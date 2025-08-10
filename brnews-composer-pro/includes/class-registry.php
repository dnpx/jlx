<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class BRCP_Registry {

	private static $instance;

	private $libraries = [];
	private $blocks = [];
	private $fragments = [];

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->scan_libraries();
	}

	private function scan_libraries() {
		$library_paths = glob( BRCP_LIBRARIES_PATH . '*', GLOB_ONLYDIR );

		foreach ( $library_paths as $library_path ) {
			$manifest_file = $library_path . '/manifest.json';
			$blocks_file   = $library_path . '/blocks.json';
			$fragments_dir = $library_path . '/fragments';

			if ( ! file_exists( $manifest_file ) ) {
				continue;
			}

			$manifest = json_decode( file_get_contents( $manifest_file ), true );
			if ( ! $manifest ) {
				continue;
			}

			$library_slug = basename( $library_path );
			$this->libraries[ $library_slug ] = $manifest;

			if ( file_exists( $blocks_file ) ) {
				$blocks = json_decode( file_get_contents( $blocks_file ), true );
				if ( $blocks && isset( $blocks['groups'] ) ) {
					foreach ( $blocks['groups'] as $group ) {
						foreach ( $group['items'] as $item ) {
							$this->blocks[ $item['type'] ] = $item;
						}
					}
				}
			}

			if ( is_dir( $fragments_dir ) ) {
				$fragment_files = glob( $fragments_dir . '/*.json' );
				foreach ( $fragment_files as $fragment_file ) {
					$fragment_slug                = basename( $fragment_file, '.json' );
					$this->fragments[ $fragment_slug ] = json_decode( file_get_contents( $fragment_file ), true );
				}
			}
		}
	}

	public function get_libraries() {
		return $this->libraries;
	}

	public function get_blocks() {
		return $this->blocks;
	}

	public function get_fragments() {
		return $this->fragments;
	}

	public function get_block( $type ) {
		return isset( $this->blocks[ $type ] ) ? $this->blocks[ $type ] : null;
	}

	public function get_fragment( $slug ) {
		return isset( $this->fragments[ $slug ] ) ? $this->fragments[ $slug ] : null;
	}
}
