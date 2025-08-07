<?php /* Template Name: Cart */ get_header(); ?>
<section class="section">
  <div class="section-header"><div class="title">سبد خرید</div></div>
  <?php $items = pe_cart_items(); if ($items): ?>
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
  <div style="display:flex; justify-content:space-between; align-items:center; margin-top:1rem;">
    <div class="h3">جمع کل: <?php echo pe_format_price(pe_cart_total()); ?></div>
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