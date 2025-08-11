<?php
/**
 * Template: Block Style 1 — lista vertical simples
 * Vars: $posts_query (WP_Query), $meta_settings (array)
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="brpb-block-1-wrap">
<?php while ( $posts_query->have_posts() ) : $posts_query->the_post(); ?>
  <div class="brpb-block-1-post-item">
    <div class="brpb-entry-content">
      <?php the_title('<h4 class="brpb-entry-title"><a href="'.esc_url(get_permalink()).'">','</a></h4>'); ?>
      <div class="brpb-entry-meta">
        <?php if ( $meta_settings['show_date'] ?? true ) : ?>
          <span class="brpb-post-date"><?php echo esc_html( get_the_date() ); ?></span>
        <?php endif; ?>
        <?php if ( $meta_settings['show_comment_count'] ?? false ) : ?>
          <span class="brpb-post-comments"><?php comments_number(); ?></span>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endwhile; ?>
</div>
<?php wp_reset_postdata(); ?>
