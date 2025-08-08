<?php /* Template Name: Contact */ get_header(); ?>
<section class="section">
  <div class="section-header"><div class="title">تماس با ما</div></div>
  <div class="grid">
    <div class="col-8 col-md-12">
      <?php if (!empty($_POST['pe_contact_submit']) && isset($_POST['pe_contact_nonce']) && wp_verify_nonce($_POST['pe_contact_nonce'], 'pe_contact')): ?>
        <?php
          $name = sanitize_text_field($_POST['name'] ?? '');
          $email = sanitize_email($_POST['email'] ?? '');
          $message = sanitize_textarea_field($_POST['message'] ?? '');
          $to = get_option('admin_email');
          $subject = 'پیام جدید از فرم تماس';
          $headers = [];
          if ($email) { $headers[] = 'Reply-To: '.$email; }
          $sent = wp_mail($to, $subject, "نام: $name\nایمیل: $email\n\nپیام:\n$message", $headers);
        ?>
        <div class="card" style="padding:1rem;"><?php echo $sent ? 'پیام شما ارسال شد.' : 'ارسال پیام با خطا مواجه شد.'; ?></div>
      <?php endif; ?>
      <form method="post" class="card" style="padding:1rem;">
        <?php wp_nonce_field('pe_contact','pe_contact_nonce'); ?>
        <div class="form-row">
          <div><label>نام</label><input name="name" required></div>
          <div><label>ایمیل</label><input type="email" name="email" required></div>
        </div>
        <div>
          <label>پیام</label>
          <textarea name="message" rows="5" required></textarea>
        </div>
        <button class="button" name="pe_contact_submit" value="1">ارسال</button>
      </form>
    </div>
    <aside class="col-4 col-md-12 sidebar">
      <?php get_sidebar(); ?>
    </aside>
  </div>
</section>
<?php get_footer(); ?>