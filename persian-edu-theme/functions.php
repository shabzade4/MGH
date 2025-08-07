<?php
/**
 * Functions and definitions for Persian Edu Theme
 */

if (!defined('PERSIAN_EDU_VERSION')) {
    define('PERSIAN_EDU_VERSION', '0.1.0');
}

// Ensure sessions for simple cart
add_action('init', function() {
    if (!session_id()) {
        session_start();
    }
});

// Theme setup
add_action('after_setup_theme', function() {
    load_theme_textdomain('persian-edu', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);
    add_theme_support('custom-logo', [
        'height' => 60,
        'width' => 60,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    register_nav_menus([
        'primary' => __('Primary Menu', 'persian-edu'),
        'footer'  => __('Footer Menu', 'persian-edu'),
    ]);

    add_image_size('post-card', 720, 405, true); // 16:9
    add_image_size('product-card', 720, 720, true); // square
});

// Enqueue styles and scripts
add_action('wp_enqueue_scripts', function() {
    // Font selection
    $font_choice = get_theme_mod('pe_font_choice', 'vazirmatn');
    if ($font_choice === 'vazirmatn') {
        wp_enqueue_style('pe-font', 'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;600;700;800&display=swap', [], null);
        $font_family = '"Vazirmatn"';
    } elseif ($font_choice === 'shabnam') {
        wp_enqueue_style('pe-font', 'https://cdn.jsdelivr.net/gh/rastikerdar/shabnam-font@v5.0.1/dist/font-face.css', [], null);
        $font_family = 'Shabnam';
    } else { // sahel
        wp_enqueue_style('pe-font', 'https://cdn.jsdelivr.net/gh/rastikerdar/sahel-font@v3.4.0/dist/font-face.css', [], null);
        $font_family = 'Sahel';
    }

    // Inject CSS variable for font
    $custom_css = ":root{ --font-body: {$font_family}, ui-sans-serif, system-ui; --font-heading: {$font_family}, ui-sans-serif, system-ui;}";
    wp_register_style('pe-inline-font', false);
    wp_enqueue_style('pe-inline-font');
    wp_add_inline_style('pe-inline-font', $custom_css);

    // Main styles
    wp_enqueue_style('pe-main', get_template_directory_uri() . '/assets/css/main.css', [], PERSIAN_EDU_VERSION);
    wp_enqueue_style('pe-style', get_stylesheet_uri(), ['pe-main'], PERSIAN_EDU_VERSION);

    // RTL override
    if (is_rtl()) {
        wp_enqueue_style('pe-rtl', get_template_directory_uri() . '/assets/css/rtl.css', ['pe-main'], PERSIAN_EDU_VERSION);
    }

    // Scripts
    wp_enqueue_script('pe-main', get_template_directory_uri() . '/assets/js/main.js', [], PERSIAN_EDU_VERSION, true);
});

// Widgets
add_action('widgets_init', function() {
    register_sidebar([
        'name'          => __('Main Sidebar', 'persian-edu'),
        'id'            => 'sidebar-1',
        'description'   => __('Sidebar for posts and pages', 'persian-edu'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => __('Product Sidebar', 'persian-edu'),
        'id'            => 'sidebar-product',
        'description'   => __('Sidebar for product pages and shop', 'persian-edu'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => __('Footer Widgets', 'persian-edu'),
        'id'            => 'footer-widgets',
        'description'   => __('Footer columns', 'persian-edu'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);
});

// Register Product CPT and taxonomy
add_action('init', function() {
    $labels = [
        'name' => __('Products', 'persian-edu'),
        'singular_name' => __('Product', 'persian-edu'),
        'add_new_item' => __('Add New Product', 'persian-edu'),
        'edit_item' => __('Edit Product', 'persian-edu'),
        'new_item' => __('New Product', 'persian-edu'),
        'view_item' => __('View Product', 'persian-edu'),
        'search_items' => __('Search Products', 'persian-edu'),
    ];
    register_post_type('product', [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'shop'],
        'menu_icon' => 'dashicons-cart',
        'supports' => ['title','editor','thumbnail','excerpt'],
        'show_in_rest' => true,
    ]);

    register_taxonomy('product_cat', ['product'], [
        'label' => __('Product Categories', 'persian-edu'),
        'rewrite' => ['slug' => 'product-category'],
        'hierarchical' => true,
        'show_in_rest' => true,
    ]);
});

// Product Price Meta Box
add_action('add_meta_boxes', function(){
    add_meta_box('pe_product_price', __('Product Details', 'persian-edu'), function($post){
        $price = get_post_meta($post->ID, '_pe_price', true);
        $sku = get_post_meta($post->ID, '_pe_sku', true);
        wp_nonce_field('pe_save_product', 'pe_product_nonce');
        echo '<p><label>'.__('Price (Toman)', 'persian-edu').'</label><input type="number" name="pe_price" value="'.esc_attr($price).'" min="0" step="1"/></p>';
        echo '<p><label>'.__('SKU', 'persian-edu').'</label><input type="text" name="pe_sku" value="'.esc_attr($sku).'"/></p>';
    }, 'product', 'side');
});

add_action('save_post_product', function($post_id){
    if (!isset($_POST['pe_product_nonce']) || !wp_verify_nonce($_POST['pe_product_nonce'], 'pe_save_product')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    $price = isset($_POST['pe_price']) ? intval($_POST['pe_price']) : 0;
    $sku = isset($_POST['pe_sku']) ? sanitize_text_field($_POST['pe_sku']) : '';
    update_post_meta($post_id, '_pe_price', $price);
    update_post_meta($post_id, '_pe_sku', $sku);
});

// Simple Cart helpers
function pe_get_cart(){
    if (!isset($_SESSION['pe_cart'])) { $_SESSION['pe_cart'] = []; }
    return $_SESSION['pe_cart'];
}
function pe_set_cart($cart){ $_SESSION['pe_cart'] = $cart; }
function pe_cart_add($product_id, $qty=1){
    $cart = pe_get_cart();
    if (!isset($cart[$product_id])) { $cart[$product_id] = 0; }
    $cart[$product_id] += max(1, (int)$qty);
    pe_set_cart($cart);
}
function pe_cart_remove($product_id){ $cart = pe_get_cart(); unset($cart[$product_id]); pe_set_cart($cart); }
function pe_cart_items(){
    $items = [];
    foreach (pe_get_cart() as $pid => $qty){
        $post = get_post($pid);
        if (!$post || $post->post_type !== 'product') continue;
        $price = (int)get_post_meta($pid, '_pe_price', true);
        $items[] = [
            'id' => $pid,
            'title' => get_the_title($pid),
            'qty' => (int)$qty,
            'price' => $price,
            'total' => $price * (int)$qty,
            'permalink' => get_permalink($pid),
            'thumb' => get_the_post_thumbnail_url($pid, 'product-card'),
        ];
    }
    return $items;
}
function pe_cart_total(){ $sum = 0; foreach (pe_cart_items() as $it){ $sum += $it['total']; } return $sum; }

// AJAX endpoints for cart
add_action('wp_ajax_pe_add_to_cart', 'pe_ajax_add_to_cart');
add_action('wp_ajax_nopriv_pe_add_to_cart', 'pe_ajax_add_to_cart');
function pe_ajax_add_to_cart(){
    $pid = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
    if ($pid && get_post_type($pid)==='product') { pe_cart_add($pid, $qty); wp_send_json_success(['count' => array_sum(pe_get_cart())]); }
    wp_send_json_error();
}

add_action('wp_ajax_pe_remove_from_cart', 'pe_ajax_remove_from_cart');
add_action('wp_ajax_nopriv_pe_remove_from_cart', 'pe_ajax_remove_from_cart');
function pe_ajax_remove_from_cart(){
    $pid = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    if ($pid) { pe_cart_remove($pid); wp_send_json_success(['count' => array_sum(pe_get_cart()), 'total' => pe_cart_total()]); }
    wp_send_json_error();
}

// Localize script with ajax URL
add_action('wp_enqueue_scripts', function(){
    wp_localize_script('pe-main', 'pe_ajax', [
        'url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('pe_ajax')
    ]);
}, 20);

// Customizer: Font choice and Payment MerchantID placeholder
add_action('customize_register', function($wp_customize){
    $wp_customize->add_section('pe_typography', [
        'title' => __('Typography (Persian Fonts)', 'persian-edu'),
        'priority' => 30,
    ]);
    $wp_customize->add_setting('pe_font_choice', [
        'default' => 'vazirmatn',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control('pe_font_choice', [
        'label' => __('Choose Persian Font', 'persian-edu'),
        'section' => 'pe_typography',
        'type' => 'select',
        'choices' => [
            'vazirmatn' => 'Vazirmatn',
            'shabnam' => 'Shabnam',
            'sahel' => 'Sahel',
        ],
    ]);

    $wp_customize->add_section('pe_payments', [
        'title' => __('Payments', 'persian-edu'),
        'priority' => 40,
    ]);
    $wp_customize->add_setting('pe_zarinpal_merchant_id', [
        'default' => '',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control('pe_zarinpal_merchant_id', [
        'label' => __('ZarinPal Merchant ID', 'persian-edu'),
        'description' => __('برای اتصال درگاه پرداخت زرین‌پال، مرچنت آیدی خود را وارد کنید.', 'persian-edu'),
        'section' => 'pe_payments',
        'type' => 'text',
    ]);
});

// Helper: formatted price
function pe_format_price($amount){
    if (!is_numeric($amount)) return '';
    return number_format((int)$amount) . ' ' . __('Toman', 'persian-edu');
}

// Query modifications: price filter on product archives
add_action('pre_get_posts', function($q){
    if (is_admin() || !$q->is_main_query()) return;
    if ($q->is_post_type_archive('product') || $q->is_tax('product_cat')) {
        $meta_query = [];
        $min = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
        $max = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 0;
        if ($min > 0) {
            $meta_query[] = [
                'key' => '_pe_price',
                'value' => $min,
                'compare' => '>=',
                'type' => 'NUMERIC'
            ];
        }
        if ($max > 0) {
            $meta_query[] = [
                'key' => '_pe_price',
                'value' => $max,
                'compare' => '<=',
                'type' => 'NUMERIC'
            ];
        }
        if (!empty($meta_query)) {
            $q->set('meta_query', $meta_query);
        }
    }
});

// AJAX: cart count
function pe_cart_count(){ return array_sum(pe_get_cart()); }
add_action('wp_ajax_pe_cart_count', function(){ wp_send_json_success(['count' => pe_cart_count()]); });
add_action('wp_ajax_nopriv_pe_cart_count', function(){ wp_send_json_success(['count' => pe_cart_count()]); });