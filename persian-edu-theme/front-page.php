<?php /* Template Name: Front Page */ get_header(); ?>

<section class="hero">
  <div class="slider card">
    <div class="slides">
      <?php
      $slides = new WP_Query(['post_type'=>'post','posts_per_page'=>3]);
      if ($slides->have_posts()): while ($slides->have_posts()): $slides->the_post(); ?>
        <div class="slide">
          <a href="<?php the_permalink(); ?>">
            <?php if (has_post_thumbnail()) { the_post_thumbnail('post-card'); } else { echo '<div style="width:100%;height:100%;background:linear-gradient(135deg, rgba(255,255,255,.06), rgba(255,255,255,.02));"></div>'; } ?>
            <div class="caption"><?php the_title(); ?></div>
          </a>
        </div>
      <?php endwhile; wp_reset_postdata(); endif; ?>
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
    <?php
    $products = new WP_Query(['post_type'=>'product','posts_per_page'=>8]);
    if ($products->have_posts()): while ($products->have_posts()): $products->the_post(); ?>
      <div class="col-3 col-md-6 col-sm-12">
        <?php get_template_part('template-parts/product', 'card'); ?>
      </div>
    <?php endwhile; wp_reset_postdata(); else: ?>
      <p class="muted">محصولی یافت نشد.</p>
    <?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="section-header">
    <div class="title">آخرین مقالات</div>
    <a class="button secondary" href="<?php echo esc_url(get_permalink( get_option('page_for_posts') )); ?>">بلاگ</a>
  </div>
  <div class="masonry">
    <?php
    $posts_q = new WP_Query(['post_type'=>'post','posts_per_page'=>6]);
    if ($posts_q->have_posts()): while ($posts_q->have_posts()): $posts_q->the_post();
      get_template_part('template-parts/content', 'card');
    endwhile; wp_reset_postdata(); endif; ?>
  </div>
</section>

<?php get_footer(); ?>