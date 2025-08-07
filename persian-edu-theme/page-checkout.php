<?php /* Template Name: Checkout */ get_header(); ?>
<section class="section">
  <div class="section-header"><div class="title">تسویه حساب</div></div>
  <?php $items = pe_cart_items(); if (!$items) { echo '<p class="muted">سبد خرید شما خالی است.</p>'; get_footer(); exit; } ?>
  <div class="grid">
    <div class="col-8 col-md-12">
      <form method="post">
        <?php wp_nonce_field('pe_checkout','pe_checkout_nonce'); ?>
        <div class="form-row">
          <div><label>نام و نام خانوادگی</label><input name="fullname" required></div>
          <div><label>ایمیل</label><input type="email" name="email" required></div>
        </div>
        <div class="form-row">
          <div><label>موبایل</label><input name="phone" required></div>
          <div><label>کد پستی</label><input name="zip"></div>
        </div>
        <div>
          <label>آدرس</label>
          <textarea name="address" rows="3"></textarea>
        </div>
        <button class="button" type="submit" name="pe_start_payment" value="1">پرداخت</button>
      </form>
      <?php
      if (isset($_POST['pe_start_payment']) && wp_verify_nonce($_POST['pe_checkout_nonce'] ?? '', 'pe_checkout')) {
          // Store order snapshot in session (demo)
          $_SESSION['pe_checkout'] = [
            'fullname' => sanitize_text_field($_POST['fullname'] ?? ''),
            'email' => sanitize_email($_POST['email'] ?? ''),
            'phone' => sanitize_text_field($_POST['phone'] ?? ''),
            'address' => sanitize_textarea_field($_POST['address'] ?? ''),
            'total' => pe_cart_total(),
          ];
          // TODO: integrate ZarinPal in next step
          echo '<div class="card" style="padding:1rem; margin-top:1rem;">'
            .'<p>فعلاً برای تست، فرایند پرداخت شبیه‌سازی می‌شود.</p>'
            .'<p>مبلغ قابل پرداخت: <strong>'.pe_format_price(pe_cart_total()).'</strong></p>'
            .'<a class="button" href="'.esc_url( add_query_arg('pe_payment', 'success', home_url('/checkout')) ).'">شبیه‌سازی پرداخت موفق</a> '
            .'<a class="button secondary" href="'.esc_url( add_query_arg('pe_payment', 'failed', home_url('/checkout')) ).'">شبیه‌سازی پرداخت ناموفق</a>'
            .'</div>';
      }
      if (isset($_GET['pe_payment'])) {
          if ($_GET['pe_payment'] === 'success') {
              echo '<div class="card" style="padding:1rem; margin-top:1rem;"><h3>پرداخت موفق</h3><p>سپاس از خرید شما.</p></div>';
              // Clear cart
              pe_set_cart([]);
          } else {
              echo '<div class="card" style="padding:1rem; margin-top:1rem;"><h3>پرداخت ناموفق</h3><p>لطفاً مجدداً تلاش کنید.</p></div>';
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