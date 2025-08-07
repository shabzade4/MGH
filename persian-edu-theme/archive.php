<?php get_header(); ?>
<div class="grid">
  <div class="col-8 col-md-12">
    <h1><?php the_archive_title(); ?></h1>
    <div class="masonry">
      <?php if (have_posts()): while (have_posts()): the_post();
        get_template_part('template-parts/content', 'card');
      endwhile; the_posts_pagination(); else: ?>
        <p class="muted">موردی یافت نشد.</p>
      <?php endif; ?>
    </div>
  </div>
  <aside class="col-4 col-md-12 sidebar"><?php get_sidebar(); ?></aside>
</div>
<?php get_footer(); ?>