<?php if (is_active_sidebar('sidebar-1')) { dynamic_sidebar('sidebar-1'); } else { ?>
  <section class="widget"><h3 class="widget-title">جستجو</h3><?php get_search_form(); ?></section>
  <section class="widget"><h3 class="widget-title">دسته‌بندی‌ها</h3><ul><?php wp_list_categories(['title_li'=>'']); ?></ul></section>
<?php } ?>