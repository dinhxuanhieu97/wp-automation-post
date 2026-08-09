<?php

/**
 * Template name: Sự kiện
 * @author : MONA.Media / Website
 */
get_header();
while (have_posts()) :
  the_post();
?>
  <main class="main pt">
    <h1 class="hidden-tt">
      <?php
      the_title();
      ?>
    </h1>
    <?php
    get_template_part('partials/breadcrumb');
    get_template_part('partials/events/event');
    get_template_part('partials/events/outstandings');
    get_template_part('partials/global/slide-ads');
    get_template_part('partials/events/news-event');
    get_template_part('partials/global/signup');

    ?>
  </main>
<?php
endwhile;
get_footer();
