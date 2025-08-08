<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
  <label>
    <span class="screen-reader-text"><?php _e('Search for:', 'persian-edu'); ?></span>
    <input type="search" class="search-field" placeholder="<?php esc_attr_e('جستجو...', 'persian-edu'); ?>" value="<?php echo get_search_query(); ?>" name="s" />
  </label>
  <button type="submit" class="button"><?php esc_html_e('جستجو', 'persian-edu'); ?></button>
</form>