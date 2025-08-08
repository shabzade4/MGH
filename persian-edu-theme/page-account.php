<?php /* Template Name: Account */ get_header(); ?>
<?php pe_breadcrumbs(); ?>
<section class="section">
  <div class="section-header"><div class="title">حساب کاربری</div></div>
  <?php if (!is_user_logged_in()): ?>
    <div class="grid">
      <div class="col-6 col-sm-12">
        <div class="card" style="padding:1rem;">
          <h3>ورود</h3>
          <form method="post">
            <?php wp_nonce_field('pe_login','pe_login_nonce'); ?>
            <label>نام کاربری یا ایمیل</label>
            <input name="log" required>
            <label>رمز عبور</label>
            <input type="password" name="pwd" required>
            <button class="button" name="pe_login" value="1" style="margin-top:.5rem;">ورود</button>
          </form>
          <?php
            if (!empty($_POST['pe_login']) && wp_verify_nonce($_POST['pe_login_nonce'] ?? '', 'pe_login')){
                $creds = ['user_login'=>sanitize_text_field($_POST['log']??''), 'user_password'=>$_POST['pwd']??'', 'remember'=>true];
                $user = wp_signon($creds, false);
                if (is_wp_error($user)) echo '<p class="muted">'.esc_html($user->get_error_message()).'</p>'; else wp_redirect(get_permalink());
            }
          ?>
        </div>
      </div>
      <div class="col-6 col-sm-12">
        <div class="card" style="padding:1rem;">
          <h3>ثبت‌نام</h3>
          <form method="post">
            <?php wp_nonce_field('pe_register','pe_register_nonce'); ?>
            <label>ایمیل</label>
            <input type="email" name="email" required>
            <label>نام کاربری</label>
            <input name="user" required>
            <label>رمز عبور</label>
            <input type="password" name="pass" required>
            <button class="button" name="pe_register" value="1" style="margin-top:.5rem;">ثبت‌نام</button>
          </form>
          <?php
            if (!empty($_POST['pe_register']) && wp_verify_nonce($_POST['pe_register_nonce'] ?? '', 'pe_register')){
                $email = sanitize_email($_POST['email'] ?? '');
                $user = sanitize_user($_POST['user'] ?? '');
                $pass = $_POST['pass'] ?? '';
                $uid = wp_create_user($user, $pass, $email);
                if (is_wp_error($uid)) echo '<p class="muted">'.esc_html($uid->get_error_message()).'</p>'; else { wp_set_current_user($uid); wp_set_auth_cookie($uid); wp_redirect(get_permalink()); }
            }
          ?>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="card" style="padding:1rem;">
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>سلام، <?php echo esc_html(wp_get_current_user()->display_name ?: wp_get_current_user()->user_login); ?></div>
        <a class="button secondary" href="<?php echo esc_url(wp_logout_url(get_permalink())); ?>">خروج</a>
      </div>
    </div>
    <div class="section" style="margin-top:1rem;">
      <h3>سفارش‌های شما</h3>
      <div class="hr"></div>
      <?php
        $orders = get_posts(['post_type'=>'pe_order','posts_per_page'=>20,'meta_key'=>'_pe_user_id','meta_value'=>get_current_user_id()]);
        if ($orders){
          echo '<ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:.5rem;">';
          foreach ($orders as $o){
            $total = (int) get_post_meta($o->ID, '_pe_total', true);
            $ref = (string) get_post_meta($o->ID, '_pe_ref_id', true);
            $items = json_decode((string)get_post_meta($o->ID, '_pe_items', true), true) ?: [];
            echo '<li class="card" style="padding:1rem;">';
            echo '<div style="display:flex; justify-content:space-between; align-items:center; gap:.5rem;"><strong>'.esc_html($o->post_title).'</strong><span>'.pe_format_price($total).' — '.esc_html($ref).'</span></div>';
            if ($items){
              echo '<ul style="margin:.5rem 0 0; padding:0 1rem;">';
              foreach ($items as $it){
                $p = (int)$it['id'];
                $dl = get_post_meta($p, '_pe_download_url', true);
                $token = pe_download_token($o->ID, $p);
                $dl_url = $dl ? add_query_arg(['pe_download'=>'1','order'=>$o->ID,'product'=>$p,'token'=>$token], home_url('/')) : '';
                echo '<li style="margin:.25rem 0; display:flex; justify-content:space-between; gap:.5rem;">'
                   .'<span>'.esc_html($it['title']).' × '.(int)$it['qty'].'</span>'
                   .'<span>'.($dl ? '<a class="button secondary" href="'.esc_url($dl_url).'">دانلود</a>' : '').'</span>'
                   .'</li>';
              }
              echo '</ul>';
            }
            $inv = add_query_arg(['pe_invoice'=>'1','order'=>$o->ID], home_url('/'));
            echo '<div style="margin-top:.5rem; display:flex; gap:.5rem;"><a class="button secondary" href="'.esc_url($inv).'">فاکتور</a></div>';
            echo '</li>';
          }
          echo '</ul>';
        } else {
          echo '<p class="muted">هنوز سفارشی ندارید.</p>';
        }
      ?>
    </div>
  <?php endif; ?>
</section>
<?php get_footer(); ?>