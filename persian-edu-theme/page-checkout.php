<?php /* Template Name: Checkout */ get_header(); ?>
<section class="section">
  <div class="section-header"><div class="title">تسویه حساب</div></div>
  <?php $items = pe_cart_items(); if (!$items) { echo '<p class="muted">سبد خرید شما خالی است.</p>'; get_footer(); exit; } ?>
  <?php
    // Handle ZarinPal callback
    if (isset($_GET['Authority'])) {
        $authority = sanitize_text_field($_GET['Authority']);
        $status = sanitize_text_field($_GET['Status'] ?? '');
        $snapshot = $_SESSION['pe_checkout'] ?? [];
        $amount = isset($snapshot['total']) ? (int)$snapshot['total'] : pe_cart_total();
        if (strtolower($status) === 'ok') {
            $verify = pe_zp_verify_payment($amount, $authority);
            if (!is_wp_error($verify)) {
                echo '<div class="card" style="padding:1rem; margin:1rem 0;"><h3>پرداخت موفق</h3><p>کد پیگیری: <strong>'.esc_html($verify['ref_id'])."</strong></p></div>";
                pe_set_cart([]);
            } else {
                echo '<div class="card" style="padding:1rem; margin:1rem 0;"><h3>تایید پرداخت ناموفق</h3><p>لطفاً با پشتیبانی تماس بگیرید.</p></div>';
            }
        } else {
            echo '<div class="card" style="padding:1rem; margin:1rem 0;"><h3>پرداخت لغو شد</h3></div>';
        }
    }
  ?>
  <div class="grid">
    <div class="col-8 col-md-12">
      <form method="post">
        <?php wp_nonce_field('pe_checkout','pe_checkout_nonce'); ?>
        <div class="form-row">
          <div><label>نام و نام خانوادگی</label><input name="fullname" required value="<?php echo esc_attr($_SESSION['pe_checkout']['fullname'] ?? ''); ?>"></div>
          <div><label>ایمیل</label><input type="email" name="email" required value="<?php echo esc_attr($_SESSION['pe_checkout']['email'] ?? ''); ?>"></div>
        </div>
        <div class="form-row">
          <div><label>موبایل</label><input name="phone" required value="<?php echo esc_attr($_SESSION['pe_checkout']['phone'] ?? ''); ?>"></div>
          <div><label>کد پستی</label><input name="zip" value="<?php echo esc_attr($_SESSION['pe_checkout']['zip'] ?? ''); ?>"></div>
        </div>
        <div>
          <label>آدرس</label>
          <textarea name="address" rows="3"><?php echo esc_textarea($_SESSION['pe_checkout']['address'] ?? ''); ?></textarea>
        </div>
        <button class="button" type="submit" name="pe_start_payment" value="1">پرداخت</button>
      </form>
      <?php
      if (isset($_POST['pe_start_payment']) && wp_verify_nonce($_POST['pe_checkout_nonce'] ?? '', 'pe_checkout')) {
          $_SESSION['pe_checkout'] = [
            'fullname' => sanitize_text_field($_POST['fullname'] ?? ''),
            'email' => sanitize_email($_POST['email'] ?? ''),
            'phone' => sanitize_text_field($_POST['phone'] ?? ''),
            'address' => sanitize_textarea_field($_POST['address'] ?? ''),
            'zip' => sanitize_text_field($_POST['zip'] ?? ''),
            'total' => pe_cart_total(),
          ];
          $callback = add_query_arg([], get_permalink());
          $pay_url = pe_zp_request_payment(pe_cart_total(), 'خرید از '.get_bloginfo('name'), $callback, $_SESSION['pe_checkout']['email'], $_SESSION['pe_checkout']['phone']);
          if (!is_wp_error($pay_url)) {
              wp_redirect($pay_url);
              exit;
          } else {
              echo '<div class="card" style="padding:1rem; margin-top:1rem;"><h3>خطا در اتصال به درگاه</h3><p>'.esc_html($pay_url->get_error_message()).'</p></div>';
          }
      }
      ?>
    </div>
    <div class="col-4 col-md-12">
      <div class="card">
        <div class="content">
          <h3>خلاصه سفارش</h3>
          <div class="hr"></div>
          <?php foreach ($items as $it): ?>
            <div style="display:flex; justify-content:space-between; gap:.6rem; margin:.25rem 0;">
              <span class="muted"><?php echo esc_html($it['title']); ?> × <?php echo (int)$it['qty']; ?></span>
              <span><?php echo pe_format_price($it['total']); ?></span>
            </div>
          <?php endforeach; ?>
          <div class="hr"></div>
          <div style="display:flex; justify-content:space-between;">
            <strong>جمع کل</strong>
            <strong><?php echo pe_format_price(pe_cart_total()); ?></strong>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php get_footer(); ?>