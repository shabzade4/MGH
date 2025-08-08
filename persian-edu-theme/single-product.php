<?php get_header(); ?>
<?php pe_breadcrumbs(); ?>
<div class="grid">
  <div class="col-8 col-md-12">
    <?php if (have_posts()): while (have_posts()): the_post(); $price = (int)get_post_meta(get_the_ID(), '_pe_price', true); ?>
      <article <?php post_class('card'); ?>>
        <?php if (has_post_thumbnail()): ?>
          <div class="media"><?php the_post_thumbnail('product-card'); ?></div>
        <?php endif; ?>
        <div class="content">
          <h1><?php the_title(); ?></h1>
          <div class="meta"><?php the_terms(get_the_ID(), 'product_cat'); ?></div>
          <div class="hr"></div>
          <div class="price"><?php echo pe_format_price($price); ?></div>
          <div class="hr"></div>
          <div class="entry-content"><?php the_content(); ?></div>
          <div style="display:flex; gap:.5rem; margin-top: .8rem;">
            <button class="button add-to-cart" data-add-to-cart data-product-id="<?php the_ID(); ?>" data-product-title="<?php echo esc_attr(get_the_title()); ?>">افزودن به سبد</button>
            <a class="button secondary" href="<?php echo esc_url(home_url('/cart')); ?>">مشاهده سبد</a>
          </div>
        </div>
      </article>
    <?php endwhile; endif; ?>
  </div>
  <aside class="col-4 col-md-12 sidebar">
    <?php if (is_active_sidebar('sidebar-product')) { dynamic_sidebar('sidebar-product'); } else { ?>
      <section class="widget">
        <h3>دسته‌بندی‌ها</h3>
        <ul><?php wp_list_categories(['taxonomy'=>'product_cat','title_li'=>'']); ?></ul>
      </section>
    <?php } ?>
  </aside>
</div>
<?php get_footer(); ?>