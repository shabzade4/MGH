<?php
/** Footer **/
?>
</main>
<footer class="site-footer">
  <div class="container">
    <div class="footer-widgets">
      <?php if (is_active_sidebar('footer-widgets')) { dynamic_sidebar('footer-widgets'); } ?>
    </div>
    <div class="hr"></div>
    <div class="footer-bottom" style="display:flex; align-items:center; justify-content:space-between; gap:1rem;">
      <div class="muted">&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?></div>
      <nav class="footer-nav">
        <?php wp_nav_menu(['theme_location'=>'footer','container'=>false,'fallback_cb'=>false,'items_wrap'=>'<ul class="menu" style="display:flex; gap:.5rem; list-style:none; margin:0; padding:0;">%3$s</ul>']); ?>
      </nav>
    </div>
  </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>