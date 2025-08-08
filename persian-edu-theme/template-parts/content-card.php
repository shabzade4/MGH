<article <?php post_class('card masonry-item'); ?>>
  <a class="media" href="<?php the_permalink(); ?>">
    <?php if (has_post_thumbnail()) { the_post_thumbnail('post-card'); } ?>
  </a>
  <div class="content">
    <a href="<?php the_permalink(); ?>" style="text-decoration:none;"><h3><?php the_title(); ?></h3></a>
    <div class="meta">
      <span><?php echo get_the_date(); ?></span>
      <span>•</span>
      <span><?php the_author(); ?></span>
    </div>
    <p class="excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 22)); ?></p>
    <a class="button secondary" href="<?php the_permalink(); ?>">مطالعه</a>
  </div>
</article>