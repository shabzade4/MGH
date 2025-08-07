<?php get_header(); ?>
<div class="grid">
  <div class="col-8 col-md-12">
    <?php if (have_posts()): while (have_posts()): the_post(); ?>
      <article <?php post_class('card'); ?>>
        <?php if (has_post_thumbnail()): ?>
          <div class="media"><?php the_post_thumbnail('post-card'); ?></div>
        <?php endif; ?>
        <div class="content">
          <h1><?php the_title(); ?></h1>
          <div class="meta">
            <span><?php the_author(); ?></span>
            <span>•</span>
            <span><?php echo get_the_date(); ?></span>
            <span>•</span>
            <span><?php the_category(', '); ?></span>
          </div>
          <div class="hr"></div>
          <div class="entry-content">
            <?php the_content(); ?>
          </div>
        </div>
      </article>
      <?php comments_template(); ?>
    <?php endwhile; endif; ?>
  </div>
  <aside class="col-4 col-md-12 sidebar">
    <?php get_sidebar(); ?>
  </aside>
</div>
<?php get_footer(); ?>