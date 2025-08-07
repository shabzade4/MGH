<?php get_header(); ?>
<div class="grid">
  <div class="col-8 col-md-12">
    <?php if (have_posts()): while (have_posts()): the_post(); ?>
      <article <?php post_class('card'); ?>>
        <div class="content">
          <h1><?php the_title(); ?></h1>
          <div class="entry-content">
            <?php the_content(); ?>
          </div>
        </div>
      </article>
    <?php endwhile; endif; ?>
  </div>
  <aside class="col-4 col-md-12 sidebar">
    <?php get_sidebar(); ?>
  </aside>
</div>
<?php get_footer(); ?>