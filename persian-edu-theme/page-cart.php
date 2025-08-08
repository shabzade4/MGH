<?php /* Template Name: Cart */ get_header(); ?>
<section class="section">
  <div class="section-header"><div class="title">سبد خرید</div></div>
  <?php
    if (!empty($_POST['pe_coupon_apply']) && isset($_POST['pe_coupon_nonce']) && wp_verify_nonce($_POST['pe_coupon_nonce'], 'pe_coupon')){
        if (!empty($_POST['coupon'])) pe_apply_coupon($_POST['coupon']); else pe_clear_coupon();
    }
    $items = pe_cart_items();
    $totals = pe_cart_totals();
  ?>
  <?php if ($items): ?>
  <table class="table">
    <thead>
      <tr><th>محصول</th><th>تعداد</th><th>قیمت واحد</th><th>مبلغ</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($items as $it): ?>
        <tr data-row-id="<?php echo (int)$it['id']; ?>">
          <td style="text-align:right">
            <a href="<?php echo esc_url($it['permalink']); ?>" style="display:flex; align-items:center; gap:.6rem; text-decoration:none;">
              <?php if ($it['thumb']) echo '<img src="'.esc_url($it['thumb']).'" alt="" style="width:60px; height:60px; object-fit:cover; border-radius:8px;"/>'; ?>
              <span><?php echo esc_html($it['title']); ?></span>
            </a>
          </td>
          <td><?php echo (int)$it['qty']; ?></td>
          <td><?php echo pe_format_price($it['price']); ?></td>
          <td><?php echo pe_format_price($it['total']); ?></td>
          <td><button class="button secondary" data-remove data-id="<?php echo (int)$it['id']; ?>">حذف</button></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="grid" style="margin-top:1rem;">
    <div class="col-6 col-sm-12">
      <form method="post" class="card" style="padding:1rem; display:flex; gap:.5rem; align-items:flex-end;">
        <?php wp_nonce_field('pe_coupon','pe_coupon_nonce'); ?>
        <div style="flex:1;">
          <label>کد تخفیف</label>
          <input name="coupon" value="<?php echo esc_attr(pe_get_applied_coupon()); ?>" placeholder="مثلاً OFF20">
        </div>
        <button class="button" name="pe_coupon_apply" value="1">اعمال</button>
      </form>
    </div>
    <div class="col-6 col-sm-12">
      <div class="card" style="padding:1rem;">
        <div style="display:flex; justify-content:space-between; margin:.25rem 0;"><span class="muted">جمع جزء</span><span><?php echo pe_format_price($totals['subtotal']); ?></span></div>
        <div style="display:flex; justify-content:space-between; margin:.25rem 0;"><span class="muted">تخفیف</span><span><?php echo $totals['discount']>0? ('-'.pe_format_price($totals['discount'])):'—'; ?></span></div>
        <div class="hr"></div>
        <div style="display:flex; justify-content:space-between; font-weight:800;"><span>قابل پرداخت</span><span><?php echo pe_format_price($totals['total']); ?></span></div>
      </div>
    </div>
  </div>
  <div style="display:flex; justify-content:space-between; align-items:center; margin-top:1rem;">
    <a class="button secondary" href="<?php echo esc_url(get_post_type_archive_link('product')); ?>">ادامه خرید</a>
    <a class="button" href="<?php echo esc_url(home_url('/checkout')); ?>">ادامه فرایند خرید</a>
  </div>
  <script>
    document.addEventListener('click', function(e){
      const btn = e.target.closest('[data-remove]');
      if (!btn) return;
      const id = btn.getAttribute('data-id');
      fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({ action:'pe_remove_from_cart', product_id:id })
      }).then(r=>r.json()).then(json=>{ if(json.success){ location.reload(); } });
    });
  </script>
  <?php else: ?>
    <p class="muted">سبد خرید شما خالی است.</p>
  <?php endif; ?>
</section>
<?php get_footer(); ?>