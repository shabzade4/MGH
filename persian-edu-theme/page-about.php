<?php /* Template Name: About */ get_header(); ?>
<section class="section">
  <div class="section-header"><div class="title">درباره ما</div></div>
  <article class="card">
    <div class="content">
      <h1 style="margin-top:0;"><?php the_title(); ?></h1>
      <div class="entry-content"><?php while (have_posts()): the_post(); the_content(); endwhile; ?></div>
    </div>
  </article>
</section>
<?php get_footer(); ?>