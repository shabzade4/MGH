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

// Asset versioning by filemtime
function pe_file_version($relative){
    $path = get_template_directory() . $relative;
    return file_exists($path) ? filemtime($path) : PERSIAN_EDU_VERSION;
}

// Enqueue styles and scripts (override previous hook to add filemtime and hints)
remove_all_actions('wp_enqueue_scripts');
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

    // Main styles (versioned)
    wp_enqueue_style('pe-main', get_template_directory_uri() . '/assets/css/main.css', [], pe_file_version('/assets/css/main.css'));
    wp_enqueue_style('pe-style', get_stylesheet_uri(), ['pe-main'], pe_file_version('/style.css'));

    // RTL override
    if (is_rtl()) {
        wp_enqueue_style('pe-rtl', get_template_directory_uri() . '/assets/css/rtl.css', ['pe-main'], pe_file_version('/assets/css/rtl.css'));
    }

    // Scripts (versioned)
    wp_enqueue_script('pe-main', get_template_directory_uri() . '/assets/js/main.js', [], pe_file_version('/assets/js/main.js'), true);

    // Localize ajax
    wp_localize_script('pe-main', 'pe_ajax', [
        'url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('pe_ajax')
    ]);
}, 5);

// Critical CSS inline for above-the-fold (very small)
add_action('wp_head', function(){
    $css = '
    .site-header{position:sticky;top:0;backdrop-filter:blur(8px);background:rgba(11,16,32,.7);border-bottom:1px solid rgba(255,255,255,.06)}
    .container{width:min(100% - 2rem, 1200px);margin-inline:auto}
    .site-header .inner{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.75rem 0}
    .site-brand{display:flex;align-items:center;gap:.6rem;text-decoration:none}
    .site-brand .logo{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg, #2dd4bf, #60a5fa);display:grid;place-items:center;color:#062826;font-weight:900}
    .main-nav ul{list-style:none;display:flex;gap:.5rem;margin:0;padding:0}
    .main-nav a{padding:.6rem .8rem;border-radius:10px;color:inherit;text-decoration:none}
    ';
    echo '<style>'.$css.'</style>';
}, 1);

// Preload likely LCP image on front/home
add_action('wp_head', function(){
    if (is_front_page() || is_home()){
        $q = new WP_Query(['post_type'=> is_front_page() ? 'post' : 'post', 'posts_per_page'=>1]);
        if ($q->have_posts()){
            $q->the_post();
            if (has_post_thumbnail()){
                $src = wp_get_attachment_image_url(get_post_thumbnail_id(), 'post-card');
                if ($src){ echo '<link rel="preload" as="image" href="'.esc_url($src).'" fetchpriority="high">'; }
            }
            wp_reset_postdata();
        }
    }
}, 2);

// Prefer WebP for generated sizes
add_filter('image_editor_output_format', function($formats){
    $formats['image/jpeg'] = 'image/webp';
    $formats['image/png'] = 'image/webp';
    return $formats;
});

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

/**
 * ZarinPal integration (v4 API)
 */
function pe_zp_is_sandbox(){ return (bool) get_theme_mod('pe_zarinpal_sandbox', false); }
function pe_zp_api_base(){ return pe_zp_is_sandbox() ? 'https://sandbox.zarinpal.com/pg/v4' : 'https://api.zarinpal.com/pg/v4'; }
function pe_zp_startpay_base(){ return pe_zp_is_sandbox() ? 'https://sandbox.zarinpal.com/pg/StartPay/' : 'https://www.zarinpal.com/pg/StartPay/'; }

function pe_zp_request_payment($amount, $description, $callback_url, $email='', $mobile=''){
    $merchant_id = trim(get_theme_mod('pe_zarinpal_merchant_id', ''));
    if (empty($merchant_id)) return new WP_Error('no_merchant', __('ZarinPal Merchant ID is missing', 'persian-edu'));
    $payload = [
        'merchant_id' => $merchant_id,
        'amount' => (int) $amount,
        'description' => $description ?: get_bloginfo('name'),
        'callback_url' => $callback_url,
        'metadata' => array_filter([
            'email' => $email,
            'mobile' => $mobile,
        ]),
    ];
    $resp = wp_remote_post(pe_zp_api_base().'/payment/request.json', [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => wp_json_encode($payload),
        'timeout' => 20,
    ]);
    if (is_wp_error($resp)) return $resp;
    $code = wp_remote_retrieve_response_code($resp);
    $body = json_decode(wp_remote_retrieve_body($resp), true);
    if ($code !== 200 || empty($body['data']['authority'])) {
        return new WP_Error('bad_response', __('Payment request failed', 'persian-edu'), $body);
    }
    $authority = $body['data']['authority'];
    return pe_zp_startpay_base() . $authority;
}

function pe_zp_verify_payment($amount, $authority){
    $merchant_id = trim(get_theme_mod('pe_zarinpal_merchant_id', ''));
    if (empty($merchant_id)) return new WP_Error('no_merchant', __('ZarinPal Merchant ID is missing', 'persian-edu'));
    $payload = [
        'merchant_id' => $merchant_id,
        'amount' => (int) $amount,
        'authority' => $authority,
    ];
    $resp = wp_remote_post(pe_zp_api_base().'/payment/verify.json', [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => wp_json_encode($payload),
        'timeout' => 20,
    ]);
    if (is_wp_error($resp)) return $resp;
    $code = wp_remote_retrieve_response_code($resp);
    $body = json_decode(wp_remote_retrieve_body($resp), true);
    if ($code === 200 && isset($body['data']['code']) && (int)$body['data']['code'] === 100) {
        return [ 'ref_id' => $body['data']['ref_id'], 'card' => $body['data']['card_pan'] ?? '' ];
    }
    return new WP_Error('verify_failed', __('Payment verification failed', 'persian-edu'), $body);
}

/**
 * SEO meta: description, OpenGraph, Twitter
 */
add_action('wp_head', function(){
    if (is_admin()) return;
    global $post;
    $title = wp_get_document_title();
    $desc = '';
    if (is_singular()) {
        $desc = has_excerpt($post) ? get_the_excerpt($post) : wp_trim_words(wp_strip_all_tags($post->post_content), 30);
    } else {
        $desc = get_bloginfo('description');
    }
    $desc = esc_attr($desc);
    $url = esc_url((is_singular() ? get_permalink() : home_url(add_query_arg([], $wp->request ?? ''))));
    $site = esc_attr(get_bloginfo('name'));
    $image = '';
    if (is_singular() && has_post_thumbnail()) {
        $image = wp_get_attachment_image_url(get_post_thumbnail_id(), 'large');
    }
    echo "\n<meta name=\"description\" content=\"{$desc}\">\n";
    echo "<meta property=\"og:title\" content=\"{$title}\">\n";
    echo "<meta property=\"og:description\" content=\"{$desc}\">\n";
    echo "<meta property=\"og:type\" content=\"".(is_singular()?'article':'website')."\">\n";
    echo "<meta property=\"og:url\" content=\"{$url}\">\n";
    if ($image) echo "<meta property=\"og:image\" content=\"".esc_url($image)."\">\n";
    echo "<meta name=\"twitter:card\" content=\"summary_large_image\">\n";
    echo "<meta name=\"twitter:title\" content=\"{$title}\">\n";
    echo "<meta name=\"twitter:description\" content=\"{$desc}\">\n";
    if ($image) echo "<meta name=\"twitter:image\" content=\"".esc_url($image)."\">\n";
}, 5);

/**
 * Breadcrumbs
 */
function pe_breadcrumbs(){
    echo '<nav class="breadcrumbs" aria-label="Breadcrumbs">';
    echo '<a href="'.esc_url(home_url('/')).'">خانه</a>';
    if (is_home() || is_front_page()) {
        echo ' / <span>بلاگ</span>';
    } elseif (is_singular('post')) {
        $cats = get_the_category();
        if (!empty($cats)) {
            $primary = $cats[0];
            echo ' / <a href="'.esc_url(get_category_link($primary)).'">'.esc_html($primary->name).'</a>';
        }
        echo ' / <span>'.esc_html(get_the_title()).'</span>';
    } elseif (is_post_type_archive('product')) {
        echo ' / <span>فروشگاه</span>';
    } elseif (is_singular('product')) {
        echo ' / <a href="'.esc_url(get_post_type_archive_link('product')).'">فروشگاه</a>';
        $terms = get_the_terms(get_the_ID(), 'product_cat');
        if ($terms && !is_wp_error($terms)) {
            $t = array_shift($terms);
            echo ' / <a href="'.esc_url(get_term_link($t)).'">'.esc_html($t->name).'</a>';
        }
        echo ' / <span>'.esc_html(get_the_title()).'</span>';
    } elseif (is_archive()) {
        echo ' / <span>'.esc_html(get_the_archive_title()).'</span>';
    } elseif (is_page()) {
        echo ' / <span>'.esc_html(get_the_title()).'</span>';
    } elseif (is_search()) {
        echo ' / <span>جستجو: '.esc_html(get_search_query()).'</span>';
    }
    echo '</nav>';
}

/**
 * Shop sorting (price asc/desc, newest)
 */
add_action('pre_get_posts', function($q){
    if (is_admin() || !$q->is_main_query()) return;
    if ($q->is_post_type_archive('product') || $q->is_tax('product_cat')) {
        $sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : '';
        if ($sort === 'price_asc') {
            $q->set('meta_key', '_pe_price');
            $q->set('orderby', 'meta_value_num');
            $q->set('order', 'ASC');
        } elseif ($sort === 'price_desc') {
            $q->set('meta_key', '_pe_price');
            $q->set('orderby', 'meta_value_num');
            $q->set('order', 'DESC');
        } elseif ($sort === 'newest') {
            $q->set('orderby', 'date');
            $q->set('order', 'DESC');
        }
    }
});

// Customizer toggle for sandbox
add_action('customize_register', function($wp_customize){
    $wp_customize->add_setting('pe_zarinpal_sandbox', [
        'default' => false,
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control('pe_zarinpal_sandbox', [
        'label' => __('Use ZarinPal Sandbox', 'persian-edu'),
        'section' => 'pe_payments',
        'type' => 'checkbox',
    ]);
});

/**
 * Orders CPT
 */
add_action('init', function(){
    register_post_type('pe_order', [
        'label' => __('Orders', 'persian-edu'),
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-clipboard',
        'supports' => ['title'],
    ]);
});

/**
 * Coupons via Customizer (simple format per line: CODE:percent:20 or CODE:fixed:50000)
 */
add_action('customize_register', function($wp_customize){
    $wp_customize->add_section('pe_shop', [
        'title' => __('Shop Settings', 'persian-edu'),
        'priority' => 45,
    ]);
    $wp_customize->add_setting('pe_coupons', [
        'default' => '',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control('pe_coupons', [
        'label' => __('Coupons (one per line)', 'persian-edu'),
        'description' => __('Format: CODE:percent:20 or CODE:fixed:50000', 'persian-edu'),
        'section' => 'pe_shop',
        'type' => 'textarea',
    ]);
});

// Extend coupons: support optional expiry date as 4th segment (YYYY-MM-DD) or key=value (until=YYYY-MM-DD)
function pe_get_coupons_map(){
    $raw = (string) get_theme_mod('pe_coupons', '');
    $map = [];
    foreach (preg_split('/\r?\n/', $raw) as $line){
        $line = trim($line);
        if (!$line) continue;
        $parts = array_map('trim', explode(':', $line));
        if (count($parts) >= 3){
            [$code, $type, $val] = $parts;
            $code = strtoupper($code);
            $type = strtolower($type);
            $val = (int) $val;
            $until = '';
            if (isset($parts[3])){
                $p = $parts[3];
                if (stripos($p, 'until=') === 0) { $until = trim(substr($p, 6)); }
                elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $p)) { $until = $p; }
            }
            if (in_array($type, ['percent','fixed']) && $val > 0){
                $map[$code] = ['type'=>$type, 'value'=>$val, 'until'=>$until];
            }
        }
    }
    return $map;
}

function pe_calculate_discount($subtotal, $coupon_code){
    $coupon_code = strtoupper(trim((string)$coupon_code));
    if (!$coupon_code) return 0;
    $map = pe_get_coupons_map();
    if (!isset($map[$coupon_code])) return 0;
    $c = $map[$coupon_code];
    if (!empty($c['until'])){
        $today = date('Y-m-d');
        if ($today > $c['until']) return 0;
    }
    if ($c['type'] === 'percent') return (int) floor($subtotal * ($c['value']/100));
    if ($c['type'] === 'fixed') return min((int)$subtotal, (int)$c['value']);
    return 0;
}

function pe_get_applied_coupon(){ return isset($_SESSION['pe_coupon']) ? strtoupper($_SESSION['pe_coupon']) : ''; }
function pe_apply_coupon($code){ $_SESSION['pe_coupon'] = strtoupper(trim($code)); }
function pe_clear_coupon(){ unset($_SESSION['pe_coupon']); }

// Totals helpers
function pe_cart_subtotal(){ $sum = 0; foreach (pe_cart_items() as $it){ $sum += $it['total']; } return $sum; }
function pe_cart_totals(){
    $subtotal = pe_cart_subtotal();
    $coupon = pe_get_applied_coupon();
    $discount = pe_calculate_discount($subtotal, $coupon);
    $total = max(0, $subtotal - $discount);
    return [ 'subtotal'=>$subtotal, 'discount'=>$discount, 'total'=>$total, 'coupon'=>$coupon ];
}
// Keep old helper for BC
function pe_cart_total(){ $t = pe_cart_totals(); return $t['total']; }

/**
 * Create order and send emails
 */
function pe_create_order($verify){
    $totals = pe_cart_totals();
    $snapshot = $_SESSION['pe_checkout'] ?? [];
    $items = pe_cart_items();
    $title = 'سفارش - '.date_i18n('Y/m/d H:i');
    $order_id = wp_insert_post([
        'post_type' => 'pe_order',
        'post_title' => $title,
        'post_status' => 'publish',
    ]);
    if (is_wp_error($order_id)) return $order_id;
    update_post_meta($order_id, '_pe_items', wp_json_encode($items));
    update_post_meta($order_id, '_pe_subtotal', (int)$totals['subtotal']);
    update_post_meta($order_id, '_pe_discount', (int)$totals['discount']);
    update_post_meta($order_id, '_pe_total', (int)$totals['total']);
    update_post_meta($order_id, '_pe_coupon', $totals['coupon']);
    update_post_meta($order_id, '_pe_ref_id', sanitize_text_field($verify['ref_id'] ?? ''));
    update_post_meta($order_id, '_pe_card', sanitize_text_field($verify['card'] ?? ''));
    update_post_meta($order_id, '_pe_email', sanitize_email($snapshot['email'] ?? ''));
    update_post_meta($order_id, '_pe_phone', sanitize_text_field($snapshot['phone'] ?? ''));
    if (is_user_logged_in()) update_post_meta($order_id, '_pe_user_id', get_current_user_id());

    pe_send_order_emails($order_id);
    return $order_id;
}

function pe_build_order_summary_text($order_id){
    $items = json_decode((string)get_post_meta($order_id, '_pe_items', true), true) ?: [];
    $subtotal = (int) get_post_meta($order_id, '_pe_subtotal', true);
    $discount = (int) get_post_meta($order_id, '_pe_discount', true);
    $total = (int) get_post_meta($order_id, '_pe_total', true);
    $coupon = (string) get_post_meta($order_id, '_pe_coupon', true);
    $ref = (string) get_post_meta($order_id, '_pe_ref_id', true);
    $lines = [];
    foreach ($items as $it){ $lines[] = sprintf("- %s × %d = %s", $it['title'], $it['qty'], pe_format_price($it['total'])); }
    $lines[] = 'جمع جزء: ' . pe_format_price($subtotal);
    if ($discount > 0) $lines[] = 'تخفیف: -' . pe_format_price($discount) . ($coupon? " (کد: {$coupon})" : '');
    $lines[] = 'پرداختی: ' . pe_format_price($total);
    if ($ref) $lines[] = 'کد پیگیری: ' . $ref;
    return implode("\n", $lines);
}

function pe_send_order_emails($order_id){
    $admin = get_option('admin_email');
    $email = get_post_meta($order_id, '_pe_email', true);
    $subject = 'سفارش جدید - ' . get_bloginfo('name');
    $summary = pe_build_order_summary_text($order_id);
    @wp_mail($admin, $subject, "سفارش جدید ثبت شد:\n\n{$summary}");
    if ($email) @wp_mail($email, 'رسید سفارش شما', "سفارش شما با موفقیت ثبت شد:\n\n{$summary}\n\nبا تشکر");
}

/**
 * Schema.org JSON-LD
 */
add_action('wp_head', function(){
    // WebSite with SearchAction
    if (is_front_page() || is_home()) {
        $site = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => get_bloginfo('name'),
            'url' => home_url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => home_url('/?s={search_term_string}'),
                'query-input' => 'required name=search_term_string'
            ]
        ];
        echo '<script type="application/ld+json">'.wp_json_encode($site, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).'</script>';
    }

    // BreadcrumbList
    if (!is_front_page()){
        $items = [];
        $pos = 1;
        $items[] = [ '@type'=>'ListItem', 'position'=>$pos++, 'name'=>'خانه', 'item'=>home_url('/') ];
        if (is_home()){
            $items[] = [ '@type'=>'ListItem', 'position'=>$pos++, 'name'=>'بلاگ', 'item'=>get_permalink(get_option('page_for_posts')) ];
        } elseif (is_singular('post')){
            $cats = get_the_category();
            if ($cats){ $c = $cats[0]; $items[] = ['@type'=>'ListItem','position'=>$pos++,'name'=>$c->name,'item'=>get_category_link($c)]; }
            $items[] = [ '@type'=>'ListItem', 'position'=>$pos++, 'name'=>get_the_title(), 'item'=>get_permalink() ];
        } elseif (is_post_type_archive('product')){
            $items[] = [ '@type'=>'ListItem', 'position'=>$pos++, 'name'=>'فروشگاه', 'item'=>get_post_type_archive_link('product') ];
        } elseif (is_singular('product')){
            $items[] = [ '@type'=>'ListItem', 'position'=>$pos++, 'name'=>'فروشگاه', 'item'=>get_post_type_archive_link('product') ];
            $terms = get_the_terms(get_the_ID(), 'product_cat');
            if ($terms){ $t = array_shift($terms); $items[] = ['@type'=>'ListItem','position'=>$pos++,'name'=>$t->name,'item'=>get_term_link($t)]; }
            $items[] = [ '@type'=>'ListItem', 'position'=>$pos++, 'name'=>get_the_title(), 'item'=>get_permalink() ];
        } elseif (is_archive()){
            $items[] = [ '@type'=>'ListItem', 'position'=>$pos++, 'name'=>get_the_archive_title(), 'item'=>home_url(add_query_arg([], $GLOBALS['wp']->request ?? '')) ];
        } elseif (is_page()){
            $items[] = [ '@type'=>'ListItem', 'position'=>$pos++, 'name'=>get_the_title(), 'item'=>get_permalink() ];
        }
        if (count($items) > 1){
            $data = [ '@context'=>'https://schema.org', '@type'=>'BreadcrumbList', 'itemListElement'=>$items ];
            echo '<script type="application/ld+json">'.wp_json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).'</script>';
        }
    }

    // Article schema
    if (is_singular('post')){
        $img = has_post_thumbnail() ? wp_get_attachment_image_url(get_post_thumbnail_id(), 'large') : '';
        $data = [
            '@context'=>'https://schema.org',
            '@type'=>'Article',
            'headline'=>get_the_title(),
            'datePublished'=>get_the_date('c'),
            'dateModified'=>get_the_modified_date('c'),
            'author'=>[ '@type'=>'Person', 'name'=>get_the_author() ],
            'mainEntityOfPage'=>get_permalink(),
        ];
        if ($img) $data['image'] = [$img];
        echo '<script type="application/ld+json">'.wp_json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).'</script>';
    }

    // Product schema
    if (is_singular('product')){
        $price = (int) get_post_meta(get_the_ID(), '_pe_price', true);
        $sku = (string) get_post_meta(get_the_ID(), '_pe_sku', true);
        $img = has_post_thumbnail() ? wp_get_attachment_image_url(get_post_thumbnail_id(), 'large') : '';
        $offers = [
            '@type' => 'Offer',
            'price' => (string) $price,
            'priceCurrency' => 'IRR',
            'availability' => 'https://schema.org/InStock',
            'url' => get_permalink(),
        ];
        $data = [
            '@context'=>'https://schema.org',
            '@type'=>'Product',
            'name'=>get_the_title(),
            'description'=>wp_strip_all_tags(get_the_excerpt() ?: get_the_content()),
            'sku'=>$sku,
            'offers'=>$offers,
        ];
        if ($img) $data['image'] = [$img];
        echo '<script type="application/ld+json">'.wp_json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).'</script>';
    }
});

// Product download URL meta
add_action('add_meta_boxes', function(){
    add_meta_box('pe_product_download', __('Digital Download', 'persian-edu'), function($post){
        $url = get_post_meta($post->ID, '_pe_download_url', true);
        echo '<p><label>'.__('Download URL (optional)', 'persian-edu').'</label><input type="url" name="pe_download_url" value="'.esc_attr($url).'" placeholder="https://..."/></p>';
    }, 'product', 'normal', 'default');
});
add_action('save_post_product', function($post_id){
    if (isset($_POST['pe_download_url'])){
        update_post_meta($post_id, '_pe_download_url', esc_url_raw($_POST['pe_download_url']));
    }
});

// Secure download and invoice handlers
function pe_download_token($order_id, $product_id){
    $secret = defined('AUTH_SALT') ? AUTH_SALT : wp_salt();
    return hash_hmac('sha256', $order_id.':'.$product_id, $secret);
}
function pe_order_contains_product($order_id, $product_id){
    $items = json_decode((string)get_post_meta($order_id, '_pe_items', true), true) ?: [];
    foreach ($items as $it){ if ((int)$it['id'] === (int)$product_id) return true; }
    return false;
}
add_action('template_redirect', function(){
    // File download
    if (isset($_GET['pe_download'])){
        $order_id = (int)($_GET['order'] ?? 0);
        $product_id = (int)($_GET['product'] ?? 0);
        $token = sanitize_text_field($_GET['token'] ?? '');
        if ($order_id && $product_id && hash_equals(pe_download_token($order_id, $product_id), $token) && pe_order_contains_product($order_id, $product_id)){
            $url = get_post_meta($product_id, '_pe_download_url', true);
            if ($url){
                wp_redirect($url);
                exit;
            }
        }
        wp_die(__('Invalid download link', 'persian-edu'));
    }
    // Invoice printable page
    if (isset($_GET['pe_invoice'])){
        $order_id = (int)($_GET['order'] ?? 0);
        if (!$order_id) wp_die('Invalid order');
        $user_id = (int) get_post_meta($order_id, '_pe_user_id', true);
        if (!current_user_can('manage_options') && (!is_user_logged_in() || get_current_user_id() !== $user_id)){
            wp_die(__('Unauthorized', 'persian-edu'));
        }
        $items = json_decode((string)get_post_meta($order_id, '_pe_items', true), true) ?: [];
        $subtotal = (int) get_post_meta($order_id, '_pe_subtotal', true);
        $discount = (int) get_post_meta($order_id, '_pe_discount', true);
        $total = (int) get_post_meta($order_id, '_pe_total', true);
        $coupon = (string) get_post_meta($order_id, '_pe_coupon', true);
        $ref = (string) get_post_meta($order_id, '_pe_ref_id', true);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html><head><meta charset="utf-8"><title>فاکتور</title><style>body{font-family:Tahoma, Arial, sans-serif; direction:rtl; margin:40px;} .table{width:100%; border-collapse:collapse;} th,td{border:1px solid #ccc; padding:8px; text-align:center;} .muted{color:#666;} .row{display:flex; justify-content:space-between; margin:6px 0;} @media print {.no-print{display:none;}}</style></head><body>';
        echo '<div class="row"><h2>'.esc_html(get_bloginfo('name')).'</h2><div class="muted">'.date_i18n('Y/m/d H:i').'</div></div>';
        echo '<div class="row"><div>شماره سفارش: '.$order_id.'</div><div>کد پیگیری: '.esc_html($ref).'</div></div>';
        echo '<table class="table"><thead><tr><th>محصول</th><th>تعداد</th><th>قیمت</th><th>مبلغ</th></tr></thead><tbody>';
        foreach ($items as $it){ echo '<tr><td>'.esc_html($it['title']).'</td><td>'.(int)$it['qty'].'</td><td>'.pe_format_price((int)$it['price']).'</td><td>'.pe_format_price((int)$it['total']).'</td></tr>'; }
        echo '</tbody></table>';
        echo '<div class="row"><span class="muted">جمع جزء</span><strong>'.pe_format_price($subtotal).'</strong></div>';
        echo '<div class="row"><span class="muted">تخفیف'.($coupon? ' ('.$coupon.')':'').'</span><strong>'.($discount>0? '-'.pe_format_price($discount):'—').'</strong></div>';
        echo '<div class="row"><span>قابل پرداخت</span><strong>'.pe_format_price($total).'</strong></div>';
        echo '<div class="no-print" style="margin-top:12px;"><button onclick="window.print()">چاپ / PDF</button></div>';
        echo '</body></html>';
        exit;
    }
});

// Performance: theme supports and head cleanup
add_action('after_setup_theme', function(){
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('automatic-feed-links');
});
add_action('init', function(){
    // Disable emojis
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    add_filter('emoji_svg_url', '__return_false');
    // Clean head
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    remove_action('wp_head', 'rest_output_link_wp_head');
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
});
add_action('wp_footer', function(){
    wp_dequeue_script('wp-embed');
}, 99);

// Resource hints for fonts CDNs
add_filter('wp_resource_hints', function($urls, $relation_type){
    if ('preconnect' !== $relation_type && 'dns-prefetch' !== $relation_type) return $urls;
    $font_choice = get_theme_mod('pe_font_choice', 'vazirmatn');
    if ($font_choice === 'vazirmatn'){
        $urls[] = 'https://fonts.googleapis.com';
        $urls[] = 'https://fonts.gstatic.com';
    } else {
        $urls[] = 'https://cdn.jsdelivr.net';
    }
    return array_unique($urls);
}, 10, 2);

// Defer theme script
add_filter('script_loader_tag', function($tag, $handle){
    if ($handle === 'pe-main'){
        $tag = str_replace('<script ', '<script defer ', $tag);
    }
    return $tag;
}, 10, 2);

// Image performance attributes
add_filter('wp_get_attachment_image_attributes', function($attr){
    $attr['decoding'] = 'async';
    if (!isset($attr['loading'])) $attr['loading'] = 'lazy';
    return $attr;
});
// Lazy iframe in content
add_filter('the_content', function($content){
    return preg_replace('/<iframe(?![^>]*loading=)/i', '<iframe loading="lazy"', $content);
});

// Simple HTML cache helpers
function pe_cache_html($key, $callback, $expire = 600){
    $cached = get_transient($key);
    if ($cached !== false) return $cached;
    $html = (string) call_user_func($callback);
    set_transient($key, $html, $expire);
    return $html;
}
function pe_invalidate_home_caches(){
    delete_transient('pe_home_slides');
    delete_transient('pe_home_products');
    delete_transient('pe_home_posts');
}
add_action('save_post', function($post_id){
    $type = get_post_type($post_id);
    if (in_array($type, ['post','product'])) pe_invalidate_home_caches();
});

// Renderers for homepage cached sections
function pe_render_home_slides(){
    return pe_cache_html('pe_home_slides', function(){
        ob_start();
        $slides = new WP_Query(['post_type'=>'post','posts_per_page'=>3]);
        if ($slides->have_posts()): while ($slides->have_posts()): $slides->the_post(); ?>
            <div class="slide">
              <a href="<?php the_permalink(); ?>">
                <?php if (has_post_thumbnail()) { the_post_thumbnail('post-card'); } else { echo '<div style="width:100%;height:100%;background:linear-gradient(135deg, rgba(255,255,255,.06), rgba(255,255,255,.02));"></div>'; } ?>
                <div class="caption"><?php the_title(); ?></div>
              </a>
            </div>
        <?php endwhile; wp_reset_postdata(); endif; 
        return ob_get_clean();
    });
}
function pe_render_home_products(){
    return pe_cache_html('pe_home_products', function(){
        ob_start();
        $products = new WP_Query(['post_type'=>'product','posts_per_page'=>8]);
        if ($products->have_posts()): while ($products->have_posts()): $products->the_post(); ?>
            <div class="col-3 col-md-6 col-sm-12">
              <?php get_template_part('template-parts/product', 'card'); ?>
            </div>
        <?php endwhile; wp_reset_postdata(); else: ?>
            <p class="muted">محصولی یافت نشد.</p>
        <?php endif;
        return ob_get_clean();
    });
}
function pe_render_home_posts(){
    return pe_cache_html('pe_home_posts', function(){
        ob_start();
        $posts_q = new WP_Query(['post_type'=>'post','posts_per_page'=>6]);
        if ($posts_q->have_posts()): while ($posts_q->have_posts()): $posts_q->the_post();
            get_template_part('template-parts/content', 'card');
        endwhile; wp_reset_postdata(); endif;
        return ob_get_clean();
    });
}

// Performance Customizer: CDN and AVIF preference
add_action('customize_register', function($wp_customize){
    $wp_customize->add_section('pe_perf', [
        'title' => __('Performance', 'persian-edu'),
        'priority' => 20,
    ]);
    $wp_customize->add_setting('pe_cdn_domain', [ 'default' => '', 'transport' => 'refresh' ]);
    $wp_customize->add_control('pe_cdn_domain', [
        'label' => __('CDN Domain (e.g. https://cdn.example.com)', 'persian-edu'),
        'section' => 'pe_perf',
        'type' => 'url',
    ]);
    $wp_customize->add_setting('pe_prefer_avif', [ 'default' => true, 'transport' => 'refresh' ]);
    $wp_customize->add_control('pe_prefer_avif', [
        'label' => __('Prefer AVIF for generated images (fallback WebP)', 'persian-edu'),
        'section' => 'pe_perf',
        'type' => 'checkbox',
    ]);
});

function pe_cdn_domain(){
    $cdn = trim((string)get_theme_mod('pe_cdn_domain', ''));
    if (!$cdn) return '';
    return rtrim($cdn, '/');
}
function pe_cdn_url($url){
    $cdn = pe_cdn_domain();
    if (!$cdn || is_admin()) return $url;
    $home = site_url();
    $uploads = wp_get_upload_dir();
    $hosts = [];
    $hosts[] = preg_replace('#^https?://#','', $home);
    $hosts[] = preg_replace('#^https?://#','', $uploads['baseurl']);
    $parsed = wp_parse_url($url);
    if (!$parsed || empty($parsed['host'])) return $url;
    $host = $parsed['host'] . (isset($parsed['port'])? (':'.$parsed['port']) : '');
    foreach ($hosts as $h){
        if (stripos($host, $h) !== false){
            // replace scheme+host with CDN
            $path = (isset($parsed['path'])? $parsed['path'] : '') . (isset($parsed['query'])? ('?'.$parsed['query']) : '') . (isset($parsed['fragment'])? ('#'.$parsed['fragment']) : '');
            return $cdn . $path;
        }
    }
    return $url;
}
add_filter('script_loader_src', function($src){ return pe_cdn_url($src); });
add_filter('style_loader_src', function($src){ return pe_cdn_url($src); });
add_filter('wp_get_attachment_url', function($url){ return pe_cdn_url($url); });
add_filter('the_content', function($html){
    if (is_admin()) return $html;
    $uploads = wp_get_upload_dir();
    $cdn = pe_cdn_domain();
    if (!$cdn) return $html;
    return str_replace($uploads['baseurl'], $cdn, $html);
});
add_filter('wp_resource_hints', function($urls, $relation_type){
    $cdn = pe_cdn_domain();
    if ($cdn && in_array($relation_type, ['preconnect','dns-prefetch'])){
        $urls[] = $cdn;
    }
    return array_unique($urls);
}, 10, 2);

// Preload theme CSS/JS
add_action('wp_head', function(){
    $css = get_template_directory_uri() . '/assets/css/main.css';
    $js = get_template_directory_uri() . '/assets/js/main.js';
    $css = pe_cdn_url($css);
    $js = pe_cdn_url($js);
    echo '<link rel="preload" as="style" href="'.esc_url($css).'">';
    echo '<link rel="preload" as="script" href="'.esc_url($js).'">';
}, 3);

// AVIF preference toggle (fallback WebP)
add_filter('image_editor_output_format', function($formats){
    $prefer_avif = (bool) get_theme_mod('pe_prefer_avif', true);
    if ($prefer_avif){
        $formats['image/jpeg'] = 'image/avif';
        $formats['image/png'] = 'image/avif';
    } else {
        $formats['image/jpeg'] = 'image/webp';
        $formats['image/png'] = 'image/webp';
    }
    return $formats;
});
add_filter('mime_types', function($m){ $m['avif'] = 'image/avif'; return $m; });
add_filter('upload_mimes', function($m){ $m['avif'] = 'image/avif'; return $m; });