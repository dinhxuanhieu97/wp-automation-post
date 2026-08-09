<?php

/**
 * Section name: Sự kiện nổi bậc
 * Description: 
 * Author: Monamedia
 * Order: 0
 */
?>
<section class="events_nb free-slide">
	<div class="container">
		<div class="events_nb--wrap">
			<div class="title-box">
				<h2 class="title-sm2 fw-7 cl-text2 title-main">
					<?php echo !empty(get_field('mon_single_outstanding_title')) ? get_field('mon_single_outstanding_title') : __('Sự kiện nổi bậc', 'monamedia'); ?>
				</h2>
			</div>
			<div class="events_nb--list">
				<div class="events_nb--list-wrap d-wrap">
					<div class="swiper mySwiper">
						<div class="swiper-wrapper">
							<?php $outstanding_post = get_field('mon_single_outstanding_post');
							if (is_array($outstanding_post) && !empty($outstanding_post)) {
								foreach ($outstanding_post as $post) {
									setup_postdata($post);  ?>
									<div class="swiper-slide d-item d-4">
										<div class="events_nb--item">
											<?php
											/**
											 * Section name: Sự kiện nổi bậc
											 * Description: 
											 * Author: Monamedia
											 */
											get_template_part('partials/loop/box-event');
											?>
										</div>
									</div>
								<?php }
								wp_reset_postdata(); ?>
								<?php } else {
								$args = array(
									'post_type' => 'event',
									'post_status' => 'publish',
									'posts_per_page' => 50,
								);
								$query_Pro = new WP_Query($args);
								if ($query_Pro->have_posts()) {
									while ($query_Pro->have_posts()) {
										$query_Pro->the_post(); ?>
										<div class="swiper-slide d-item d-4">
											<div class="events_nb--item">
												<?php
												/**
												 * Section name: Sự kiện nổi bậc
												 * Description: 
												 * Author: Monamedia
												 */
												get_template_part('partials/loop/box-event');
												?>
											</div>
										</div>
									<?php }
									wp_reset_query(); ?>
							<?php }
							} ?>
						</div>
					</div>
					<div class="swiper-pagination"></div>
				</div>
				<div class="box-navi">
					<div class="btn-navi prev">
						<i class="fa-light fa-angle-left"></i>
					</div>
					<div class="btn-navi next">
						<i class="fa-light fa-angle-right"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>