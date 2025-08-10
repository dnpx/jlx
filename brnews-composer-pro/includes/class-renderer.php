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
			'posts_per_page' => isset( $props['posts'] ) ? absint( $props['posts'] ) : 5,
			'category_name'  => isset( $props['cat'] ) ? sanitize_text_field( $props['cat'] ) : '',
			'ignore_sticky_posts' => 1,
		];

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '<!-- No posts found -->';
		}

		$output = '<div class="brcp-big-grid-1">';

		$counter = 0;
		while( $query->have_posts() ) {
			$query->the_post();
			$counter++;

			$thumb_url = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'large' ) : 'https://picsum.photos/800/600?random=' . get_the_ID();
			$category = get_the_category();

			if ( $counter == 1 ) {
				$output .= '<div class="brcp-post-item brcp-post-item--large">';
				$output .= '<a href="' . get_permalink() . '" class="brcp-post-thumb"><img src="' . esc_url( $thumb_url ) . '" alt="' . get_the_title() . '"></a>';
				$output .= '<div class="brcp-post-content">';
				if ( ! empty( $category ) ) {
					$output .= '<a href="' . get_category_link( $category[0]->term_id ) . '" class="brcp-post-category">' . $category[0]->name . '</a>';
				}
				$output .= '<h3 class="brcp-post-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
				$output .= '<div class="brcp-post-meta">';
				$output .= '<span class="brcp-post-author">' . get_the_author() . '</span>';
				$output .= '<span class="brcp-post-date">' . get_the_date() . '</span>';
				$output .= '</div>';
				$output .= '</div>';
				$output .= '</div>';
				if ( $query->post_count > 1 ) {
					$output .= '<div class="brcp-big-grid-1-sidebar">';
				}
			} else {
				$output .= '<div class="brcp-post-item brcp-post-item--small">';
				$output .= '<a href="' . get_permalink() . '" class="brcp-post-thumb-small"><img src="' . esc_url( $thumb_url ) . '" alt="' . get_the_title() . '"></a>';
				$output .= '<div class="brcp-post-content">';
				$output .= '<h4 class="brcp-post-title-small"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h4>';
				$output .= '</div>';
				$output .= '</div>';
			}
		}

		if ( $query->post_count > 1 ) {
			$output .= '</div>'; // close sidebar
		}

		$output .= '</div>'; // close main grid

		wp_reset_postdata();

		return $output;
	}

	public function render_flex_block_1( $props ) {
		$args = [
			'post_type'      => 'post',
			'posts_per_page' => isset( $props['posts'] ) ? absint( $props['posts'] ) : 4,
			'category_name'  => isset( $props['cat'] ) ? sanitize_text_field( $props['cat'] ) : '',
			'ignore_sticky_posts' => 1,
		];

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '<!-- No posts found -->';
		}

		$cols = isset( $props['cols'] ) ? absint( $props['cols'] ) : 4;
		$output = '<div class="brcp-flex-block-1" style="--brcp-cols: ' . $cols . '">';

		while( $query->have_posts() ) {
			$query->the_post();

			$thumb_url = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'medium_large' ) : 'https://picsum.photos/400/300?random=' . get_the_ID();
			$category = get_the_category();

			$output .= '<div class="brcp-post-item">';
			$output .= '<a href="' . get_permalink() . '" class="brcp-post-thumb"><img src="' . esc_url( $thumb_url ) . '" alt="' . get_the_title() . '"></a>';
			$output .= '<div class="brcp-post-content">';
			if ( ! empty( $category ) ) {
				$output .= '<a href="' . get_category_link( $category[0]->term_id ) . '" class="brcp-post-category">' . $category[0]->name . '</a>';
			}
			$output .= '<h3 class="brcp-post-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
			$output .= '</div>';
			$output .= '</div>';
		}

		$output .= '</div>';

		wp_reset_postdata();

		return $output;
	}
}
