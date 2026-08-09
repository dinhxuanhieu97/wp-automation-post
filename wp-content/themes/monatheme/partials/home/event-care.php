<?php
/**
 * Section name: Sự kiện đáng quan tâm
 * Description: 
 * Author: Monamedia
 * Order: 0
 */
$event_care = get_field('mon_home_event_care');
// if ( !empty( $event_care )) {
	if ($event_care) {
?>
<section class="home_care free-slide">
	<div class="container">
		<div class="home_care--wrap">
			<div class="title-box">
				<h2 class="title-sm2 fw-7 cl-text2 title-main">
					<?php echo !empty(get_field('mon_home_event_care_title', MONA_PAGE_HOME)) ? get_field('mon_home_event_care_title', MONA_PAGE_HOME) : __('Sự kiện','monamedia'); ?>
				</h2>
			</div>
			<div class="home_care--box">
				<div class="home_care--list d-wrap">
					<div class="swiper mySwiper">
						<div class="swiper-wrapper">
							<?php foreach ($event_care as $item) {
								$term = is_object( $item['mon_event_cat'] ) ? $item['mon_event_cat'] : get_term( $item['mon_event_cat'] );
								if( $term instanceof WP_Term ){
									$term_link = get_term_link( $term );
									$term_name = $term->name;
									$term_id = $term->term_id;
									$backgroud_tax = get_field('backgroud_tax', $term);
								?>
							<div class="swiper-slide d-3 d-item">
								<div class="home_care--item">
									<div class="home_care--item-wrap">
										<div class="home_care--top">
											<div class="home_care--img">
												<?php echo wp_get_attachment_image($backgroud_tax, 'full') ?>
											</div>
											<div class="home_care--top-title">
												<h3 class="title-mn cl-white fw-7"><?php echo $term_name ?></h3>
												<a class="follow" href="<?php echo $term_link ?>"><?php echo __('Theo dõi', 'monamedia') ?></a>
											</div>
										</div>
										<div class="home_care--bottom">
											<ul class="home_care--bottom-list">
												<?php foreach ($item['event_list_post'] as $post) {
													setup_postdata($post);  ?>
												<li class="home_care--bottom-item">
													<a class="home_care--bottom-link" href="<?php echo get_the_permalink($post->ID) ?>">
														<span class="note-text fw-6 cl-text2">
															<?php echo get_the_title($post->ID); ?>
														</span>
														<span class="note-sm cl-text"><?php echo get_the_time('m/Y'); ?></span>
													</a>
												</li>
												<?php } wp_reset_postdata(); ?>
											</ul>
										</div>
									</div>
								</div>
							</div>
							<?php } } ?>
						</div>
					</div>
					<div class="box-navi">
						<div class="btn-navi prev">
							<i class="fa-light fa-angle-left">

							</i>
						</div>
						<div class="btn-navi next">
							<i class="fa-light fa-angle-right">

							</i>
						</div>
					</div>
				</div>
				<div class="swiper-pagination">

				</div>
			</div>
		</div>
	</div>
</section>
<?php } ?>