<?php get_header(); ?>
<?php pe_breadcrumbs(); ?>
<div class="grid">
  <div class="col-8 col-md-12">
    <h1>فروشگاه</h1>
    <form method="get" style="margin:.5rem 0; display:flex; gap:.5rem;">
      <select name="sort">
        <option value="">مرتب‌سازی</option>
        <option value="newest" <?php selected($_GET['sort'] ?? '', 'newest'); ?>>جدیدترین</option>
        <option value="price_asc" <?php selected($_GET['sort'] ?? '', 'price_asc'); ?>>ارزان‌ترین</option>
        <option value="price_desc" <?php selected($_GET['sort'] ?? '', 'price_desc'); ?>>گران‌ترین</option>
      </select>
      <?php foreach ($_GET as $k=>$v) if (!in_array($k,['sort'])) echo '<input type="hidden" name="'.esc_attr($k).'" value="'.esc_attr($v).'">'; ?>
      <button class="button secondary">اعمال</button>
    </form>
    <div class="grid">
      <?php if (have_posts()): while (have_posts()): the_post(); ?>
        <div class="col-4 col-md-6 col-sm-12">
          <?php get_template_part('template-parts/product', 'card'); ?>
        </div>
      <?php endwhile; the_posts_pagination(); else: ?>
        <p class="muted">محصولی یافت نشد.</p>
      <?php endif; ?>
    </div>
  </div>
  <aside class="col-4 col-md-12 sidebar">
    <section class="widget">
      <h3>جستجو محصول</h3>
      <?php get_search_form(); ?>
    </section>
    <section class="widget">
      <h3>دسته‌بندی</h3>
      <ul>
        <?php wp_list_categories(['taxonomy'=>'product_cat','title_li'=>'']); ?>
      </ul>
    </section>
    <section class="widget">
      <h3>فیلتر قیمت</h3>
      <form method="get">
        <div class="form-row">
          <div>
            <label>حداقل</label>
            <input type="number" name="min_price" value="<?php echo isset($_GET['min_price'])? esc_attr((int)$_GET['min_price']) : ''; ?>" />
          </div>
          <div>
            <label>حداکثر</label>
            <input type="number" name="max_price" value="<?php echo isset($_GET['max_price'])? esc_attr((int)$_GET['max_price']) : ''; ?>" />
          </div>
        </div>
        <button class="button" type="submit">اعمال</button>
      </form>
    </section>
  </aside>
</div>
<?php get_footer(); ?>