<?php get_header(); ?>
<section class="section">
  <div class="section-header">
    <div class="title">آخرین مطالب</div>
  </div>
  <div class="masonry">
    <?php if (have_posts()): while (have_posts()): the_post(); ?>
      <?php get_template_part('template-parts/content', 'card'); ?>
    <?php endwhile; else: ?>
      <p class="muted">مطلبی یافت نشد.</p>
    <?php endif; ?>
  </div>
</section>
<?php get_footer(); ?>