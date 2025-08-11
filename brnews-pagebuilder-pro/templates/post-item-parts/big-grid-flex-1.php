<?php
/**
 * Template: Big Grid Flex 1
 * Vars: $posts_query (WP_Query), $meta_settings (array)
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$i = 0;
?>
<div class="brpb-big-grid-flex-1">
<?php while ( $posts_query->have_posts() ) : $posts_query->the_post(); $i++; ?>
  <?php if ($i===1): ?>
    <div class="brpb-grid-post-large">
      <div class="brpb-post-thumb"><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('large'); ?></a></div>
      <div class="brpb-entry-content">
        <?php if ( $meta_settings['show_category'] ?? true ): $cats = get_the_category(); if($cats): ?>
          <a class="brpb-post-category" href="<?php echo esc_url( get_category_link($cats[0]->term_id) ); ?>"><?php echo esc_html($cats[0]->name); ?></a>
        <?php endif; endif; ?>
        <?php the_title('<h3 class="brpb-entry-title"><a href="'.esc_url(get_permalink()).'">','</a></h3>'); ?>
        <div class="brpb-entry-meta">
          <?php if ( $meta_settings['show_author'] ?? false ): ?><span class="brpb-author-name"><?php echo esc_html( get_the_author() ); ?></span><?php endif; ?>
          <?php if ( $meta_settings['show_date'] ?? true ): ?><span class="brpb-post-date"><?php echo esc_html( get_the_date() ); ?></span><?php endif; ?>
        </div>
        <?php if ( $meta_settings['show_excerpt'] ?? false ): ?>
          <div class="brpb-entry-excerpt"><?php echo esc_html( get_the_excerpt() ); ?></div>
        <?php endif; ?>
      </div>
    </div>
    <div class="brpb-grid-posts-small">
  <?php else: ?>
    <div class="brpb-grid-post-small">
      <div class="brpb-post-thumb-small"><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('thumbnail'); ?></a></div>
      <div class="brpb-entry-content-small">
        <?php the_title('<h4 class="brpb-entry-title"><a href="'.esc_url(get_permalink()).'">','</a></h4>'); ?>
        <?php if ( $meta_settings['show_date'] ?? true ): ?><div class="brpb-entry-meta"><span class="brpb-post-date"><?php echo esc_html( get_the_date() ); ?></span></div><?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
<?php endwhile; ?>
</div>
<?php wp_reset_postdata(); ?>
