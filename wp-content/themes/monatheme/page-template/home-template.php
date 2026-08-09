<?php

/**
 * Template name: Trang chủ
 * @author : MONA.Media / Website
 */
get_header();
while (have_posts()) :
  the_post();
?>
  <main class="main pt">
    <h1 class="hidden-tt">
      <?php
      the_title()
      ?>
    </h1>
    <?php
    get_template_part('partials/home/tab-cat');
    get_template_part('partials/home/outstandings');
    get_template_part('partials/global/slide-ads');
    get_template_part('partials/home/technology');
    get_template_part('partials/home/spotlight');
    get_template_part('partials/home/partner');
    get_template_part('partials/home/finance');
    get_template_part('partials/home/event-care');
    get_template_part('partials/news/latest-news', null, ['limit' => 5]);
    get_template_part('partials/global/signup');
    ?>

  </main>
<?php
endwhile;
get_footer();
