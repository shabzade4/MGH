<?php $price = (int)get_post_meta(get_the_ID(), '_pe_price', true); ?>
<article <?php post_class('card'); ?>>
  <a class="media" href="<?php the_permalink(); ?>">
    <?php if (has_post_thumbnail()) { the_post_thumbnail('product-card'); } ?>
  </a>
  <div class="content">
    <a href="<?php the_permalink(); ?>" style="text-decoration:none;"><h3><?php the_title(); ?></h3></a>
    <div class="meta">
      <span class="price"><?php echo pe_format_price($price); ?></span>
    </div>
    <div style="display:flex; gap:.5rem;">
      <button class="button add-to-cart" data-add-to-cart data-product-id="<?php the_ID(); ?>" data-product-title="<?php echo esc_attr(get_the_title()); ?>">افزودن</button>
      <a class="button secondary" href="<?php the_permalink(); ?>">جزئیات</a>
    </div>
  </div>
</article>