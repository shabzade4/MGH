<?php /* Template Name: Front Page */ get_header(); ?>

<section class="hero">
  <div class="slider card">
    <div class="slides">
      <?php echo pe_render_home_slides(); ?>
    </div>
    <div class="dots">
      <button class="active" aria-label="slide 1"></button>
      <button aria-label="slide 2"></button>
      <button aria-label="slide 3"></button>
    </div>
  </div>
</section>

<section class="section">
  <div class="section-header">
    <div class="title">محصولات آموزشی</div>
    <a class="button secondary" href="<?php echo esc_url(get_post_type_archive_link('product')); ?>">نمایش همه</a>
  </div>
  <div class="grid">
    <?php echo pe_render_home_products(); ?>
  </div>
</section>

<section class="section">
  <div class="section-header">
    <div class="title">آخرین مقالات</div>
    <a class="button secondary" href="<?php echo esc_url(get_permalink( get_option('page_for_posts') )); ?>">بلاگ</a>
  </div>
  <div class="masonry">
    <?php echo pe_render_home_posts(); ?>
  </div>
</section>

<?php get_footer(); ?>