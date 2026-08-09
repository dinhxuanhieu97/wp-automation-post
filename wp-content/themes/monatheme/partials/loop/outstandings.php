<?php
/**
 * Section name: Sự kiện nổi bậc
 * Description: 
 * Author: Monamedia
 * Order: 0
 */
$mon_pj_related = get_field('mon_pj_related');
?>

<div class="pj-relate ss-pd-b">
	<div class="container">
		<div class="prod-slider-head">
			<h2 class="title-big add-class" data-spl="data-spl">
				<?php echo __( 'DỰ ÁN NỔI BẬT LIÊN QUAN', 'monamedia' ) ?></h2>
			<div class="swiper-control">
				<div class="swiper-control-btn swiper-prev">
					<i class="far fa-long-arrow-left"></i>
				</div>
				<div class="swiper-control-btn swiper-next">
					<i class="far fa-long-arrow-right"></i>
				</div>
			</div>
		</div>
		<div class="swiper-container">
			<div class="swiper row">
				<div class="swiper-wrapper">
					<?php if( !empty( $mon_pj_related ) || !empty($mon_pj_related) ){ 
					foreach ($mon_pj_related as $post) { 
							setup_postdata($post); ?>
					<div class="swiper-slide col">
						<div class="pj-item">
							<div class="inner">
								<div class="img">
									<a class="img-inner" href="<?php echo get_the_permalink($post->ID) ?>">
										<?php echo get_post_thumbnail_monamedia( get_the_post_thumbnail($post->ID, 'full') ); ?>
									</a>
								</div>
								<div class="info">
									<h4>
										<a class="info-tt" href="<?php echo get_the_permalink($post->ID) ?>"><?php echo the_title() ?></a>
									</h4>
									<a class="seeDetail" href="<?php echo get_the_permalink($post->ID) ?>">
										<span class="seeDetail-txt"><?php echo __('Xem chi tiết','monamedia'); ?></span>
										<i class="fa-solid fa-chevron-right">
										</i>
									</a>
								</div>
							</div>
						</div>
					</div>
					<?php } wp_reset_postdata() ?>
					<?php } else {
						$args = array(
							'post_type' => 'event',
							'post_status' => 'publish',
							'posts_per_page' => 12,
						);
				
						$related_posts_query = new WP_Query($args);
						if ($related_posts_query->have_posts()) {
							while ($related_posts_query->have_posts()) {
								$related_posts_query->the_post(); 
						?>
					<div class="swiper-slide col">
						<div class="pj-item">
							<div class="inner">
								<div class="img">
									<a class="img-inner" href="<?php echo get_the_permalink($post->ID) ?>">
										<?php echo get_post_thumbnail_monamedia( get_the_post_thumbnail($post->ID, 'full') ); ?>
									</a>
								</div>
								<div class="info">
									<h4>
										<a class="info-tt" href="<?php echo get_the_permalink($post->ID) ?>"><?php echo the_title() ?></a>
									</h4>
									<a class="seeDetail" href="<?php echo get_the_permalink($post->ID) ?>">
										<span class="seeDetail-txt"><?php echo __('Xem chi tiết','monamedia'); ?></span>
										<i class="fa-solid fa-chevron-right">
										</i>
									</a>
								</div>
							</div>
						</div>
					</div>
					<?php } wp_reset_postdata(); } } ?>
				</div>
			</div>
		</div>
		<div class="swiper-pagination"></div>
	</div>
</div>