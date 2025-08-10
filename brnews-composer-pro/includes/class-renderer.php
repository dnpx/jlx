<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class BRCP_Renderer {

	private static $instance;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		//
	}

	public function render_layout_from_meta( $post_id ) {
		$layout = get_post_meta( $post_id, '_brcp_layout', true );
		return $this->render_layout( $layout );
	}

	public function render_layout( $layout ) {
		if ( ! is_array( $layout ) ) {
			$layout = json_decode( $layout, true );
		}

		if ( ! $layout || ! isset( $layout['rows'] ) ) {
			return '';
		}

		$output = '';
		foreach ( $layout['rows'] as $row ) {
			$output .= $this->render_row( $row );
		}

		return $output;
	}

	public function render_row( $row_data ) {
		$output = '<div class="brcp-row">';
		if ( ! empty( $row_data['columns'] ) ) {
			foreach ( $row_data['columns'] as $column ) {
				$output .= $this->render_column( $column );
			}
		}
		$output .= '</div>';
		return $output;
	}

	public function render_column( $column_data ) {
		$output = '<div class="brcp-col brcp-col--' . esc_attr( $column_data['width'] ) . '">';
		if ( isset( $column_data['modules'] ) ) {
			foreach ( $column_data['modules'] as $module ) {
				$output .= $this->render_module( $module );
			}
		}
		$output .= '</div>';
		return $output;
	}

	public function render_module( $module_data ) {
		$type = $module_data['type'];
		$props = isset( $module_data['props'] ) ? $module_data['props'] : [];
		$module_id = isset( $module_data['id'] ) ? $module_data['id'] : uniqid( 'brcp-module-' );

		$render_method = 'render_' . $type;

		if ( method_exists( $this, $render_method ) ) {
			$output = '<div class="brcp-module" data-type="' . esc_attr( $type ) . '" data-module-id="' . esc_attr( $module_id ) . '">';
			$output .= $this->{$render_method}( $props );
			$output .= '</div>';
			return $output;
		}

		return '<!-- Module ' . esc_html( $type ) . ' not found -->';
	}

	public function render_heading( $props ) {
		$text = isset( $props['text'] ) ? $props['text'] : '';
		$tag = isset( $props['tag'] ) ? $props['tag'] : 'h2';
		return '<' . esc_attr( $tag ) . '>' . esc_html( $text ) . '</' . esc_attr( $tag ) . '>';
	}

	public function render_text( $props ) {
		$content = isset( $props['content'] ) ? $props['content'] : '';
		return wpautop( wp_kses_post( $content ) );
	}

	public function render_button( $props ) {
		$text = isset( $props['text'] ) ? $props['text'] : '';
		$url = isset( $props['url'] ) ? $props['url'] : '#';
		$target = isset( $props['target'] ) ? $props['target'] : '_self';
		return '<a href="' . esc_url( $url ) . '" target="' . esc_attr( $target ) . '" class="brcp-button">' . esc_html( $text ) . '</a>';
	}

	public function render_big_grid_1( $props ) {
		$args = [
			'post_type'      => 'post',
			'posts_per_page' => isset( $props['posts'] ) ? absint( $props['posts'] ) : 7,
			'category_name'  => isset( $props['cat'] ) ? sanitize_text_field( $props['cat'] ) : '',
			'ignore_sticky_posts' => 1,
		];

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '<!-- No posts found -->';
		}

		$output = '<div class="brcp-big-grid brcp-big-grid--style-1">';
		// For now, just a placeholder
		while( $query->have_posts() ) {
			$query->the_post();
			$output .= '<div>' . get_the_title() . '</div>';
		}
		$output .= '</div>';

		wp_reset_postdata();

		return $output;
	}

	public function render_flex_block_1( $props ) {
		$args = [
			'post_type'      => 'post',
			'posts_per_page' => isset( $props['posts'] ) ? absint( $props['posts'] ) : 8,
			'category_name'  => isset( $props['cat'] ) ? sanitize_text_field( $props['cat'] ) : '',
			'ignore_sticky_posts' => 1,
		];

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '<!-- No posts found -->';
		}

		$output = '<div class="brcp-flex-block brcp-flex-block--style-1">';
		// For now, just a placeholder
		while( $query->have_posts() ) {
			$query->the_post();
			$output .= '<div>' . get_the_title() . '</div>';
		}
		$output .= '</div>';

		wp_reset_postdata();

		return $output;
	}
}
