<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class BRCP_Registry {

	private static $instance;

	private $libraries = [];
	private $block_groups = [];
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
				$blocks_data = json_decode( file_get_contents( $blocks_file ), true );
				if ( $blocks_data && isset( $blocks_data['groups'] ) ) {
					foreach ( $blocks_data['groups'] as $group ) {
						$group_id = $group['id'];
						if ( ! isset( $this->block_groups[ $group_id ] ) ) {
							$this->block_groups[ $group_id ] = [
								'id'    => $group_id,
								'title' => $group['title'],
								'items' => [],
							];
						}

						foreach ( $group['items'] as $item ) {
							$this->block_groups[ $group_id ]['items'][] = $item;
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
		return array_values( $this->block_groups );
	}

	public function get_block( $type ) {
		foreach ( $this->block_groups as $group ) {
			foreach ( $group['items'] as $item ) {
				if ( $item['type'] === $type ) {
					return $item;
				}
			}
		}
		return null;
	}

	public function get_fragments() {
		return $this->fragments;
	}

	public function get_fragment( $slug ) {
		return isset( $this->fragments[ $slug ] ) ? $this->fragments[ $slug ] : null;
	}
}
