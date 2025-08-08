<?php
/** Header **/
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php if (function_exists('wp_body_open')) wp_body_open(); ?>
<header class="site-header">
  <div class="container inner">
    <a class="site-brand" href="<?php echo esc_url(home_url('/')); ?>">
      <span class="logo">آ</span>
      <span class="name"><?php bloginfo('name'); ?></span>
    </a>

    <nav class="main-nav" aria-label="Main Navigation">
      <?php
        wp_nav_menu([
          'theme_location' => 'primary',
          'container' => false,
          'items_wrap' => '<ul class="menu">%3$s</ul>',
          'fallback_cb' => false,
        ]);
      ?>
    </nav>

    <div class="nav-actions">
      <a class="button secondary" href="<?php echo esc_url(home_url('/cart')); ?>">
        <span>سبد خرید</span>
        <span class="badge" data-cart-badge>0</span>
      </a>
      <button class="mobile-toggle" aria-label="Toggle Navigation">☰</button>
    </div>
  </div>
</header>
<main class="site-main container">