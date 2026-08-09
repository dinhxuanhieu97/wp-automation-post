<?php

/**
 * Section name: Sự kiện 
 * Description: 
 * Author: Monamedia
 * Order: 0
 */

?>
<section class="events">
	<div class="container">
		<div class="events_wrap">
			<div class="title-box">
				<h1 class="title-lg fw-7 cl-text2">
					<?php echo !empty(get_field('mon_single_event_title')) ? get_field('mon_single_event_title') : the_title(); ?>
				</h1>
			</div>
			<div class="events_content">
				<?php $mon_single_event = get_field('mon_single_event');
            if (is_array( $mon_single_event ) && !empty( $mon_single_event )) {
              foreach ($mon_single_event as $post) {
            setup_postdata($post); 
            /**
             * Section name: Sự kiện
             * Description: 
             * Author: Monamedia
             */
            get_template_part( 'partials/loop/box-event' );
            ?>

				<?php } wp_reset_postdata() ?>
				<?php } else { ?>
				<?php  $args = array(
            'post_type' => 'event',
            'post_status' => 'publish',
            'posts_per_page' => 1,
          );
          $query_Pro = new WP_Query($args);
          if ($query_Pro->have_posts()) { 
          while ($query_Pro->have_posts()) {
            $query_Pro->the_post();
            /**
             * Section name: Sự kiện
             * Description: 
             * Author: Monamedia
             */
            get_template_part( 'partials/loop/box-event' );       
            ?>

				<?php } wp_reset_postdata(); ?>
				<?php } } ?>
			</div>
		</div>
	</div>
</section>