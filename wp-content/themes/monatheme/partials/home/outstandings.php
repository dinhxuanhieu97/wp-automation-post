<?php

/**
 * Section name: Tin tức nổi bậc
 * Description: 
 * Author: Monamedia
 * Order: 0
 */
?>
<section class="post_lq free-slide">
	<div class="container">
		<div class="post_lq--wrap">
			<div class="title-box">
				<h2 class="title-sm2 fw-7 cl-text2 title-main">
					<?php echo !empty(get_field('mon_home_news_outstanding_title')) ? get_field('mon_home_news_outstanding_title') : __('Tin tức nổi bậc', 'monamedia'); ?>
				</h2>
				<a class="see-more" href="<?php echo !empty(get_field('home_news_outstanding_link')) ? (get_field('home_news_outstanding_link')['url']) : get_the_permalink(MONA_PAGE_BLOG); ?>">
					<?php echo !empty(get_field('home_news_outstanding_link')) ? (get_field('home_news_outstanding_link')['title']) : __('Xem tất cả ', 'monamedia'); ?>
				</a>
			</div>
			<div class="post_lq--list">
				<div class="post_lq--list-wrap d-wrap">
					<div class="swiper mySwiper">
						<div class="swiper-wrapper">
							<?php $outstanding_post = get_field('mon_home_news_outstanding_list');
							if (is_array($outstanding_post) && !empty($outstanding_post)) {
								foreach ($outstanding_post as $post) {
									setup_postdata($post);  ?>
									<div class="swiper-slide d-item d-4">
										<?php
										/**
										 * Section name: Post
										 * Description: 
										 * Author: Monamedia
										 */
										get_template_part('partials/loop/box-blog');
										?>
									</div>
								<?php }
								wp_reset_postdata(); ?>
								<?php } else {
								$args = array(
									'post_type' => 'post',
									'post_status' => 'publish',
									'posts_per_page' => 12,
									'offset' => 4,
								);
								$query_Pro = new WP_Query($args);
								if ($query_Pro->have_posts()) {
									while ($query_Pro->have_posts()) {
										$query_Pro->the_post(); ?>
										<div class="swiper-slide d-item d-4">
											<?php
											/**
											 * Section name: Post
											 * Description: 
											 * Author: Monamedia
											 */
											get_template_part('partials/loop/box-blog');
											?>
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