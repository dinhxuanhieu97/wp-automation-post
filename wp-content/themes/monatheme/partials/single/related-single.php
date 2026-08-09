<?php
/**
 * Section name: BÀI VIẾT LIÊN QUAN
 * Description: 
 * Author: Monamedia
 * Order: 0
 */
mona_set_post_view();
$current_post_id = get_the_ID();
// Lấy danh mục của bài viết hiện tại
$post_categories = wp_get_post_categories($current_post_id);

// Nếu bài viết có danh mục, thêm chúng vào tax_query
if (!empty($post_categories)) {
    $args['tax_query'][] = [
        'taxonomy' => 'category',
        'field' => 'id',
        'terms' => $post_categories,
        'operator' => 'IN',
    ];
}

$args['post__not_in'] = array($current_post_id);
$queryRelated = new WP_Query($args);
?>
<section class="reblog">
	<div class="reblog-wrap">
		<div class="reblog-inner">
			<div class="reblog-top">
				<div class="container">
					<p class="t40 fw-6 f-title c-black">
						<?php echo __('Bài viết liên quan', 'monamedia'); ?></h2>
					</p>
				</div>
			</div>
			<div class="reblog-slide">
				<div class="swiper rblogSwiper">
					<div class="swiper-wrapper">
						<?php if ($queryRelated->have_posts()) {
              while ($queryRelated->have_posts()) {
                $queryRelated->the_post();
                $title = get_the_title($post->ID);
                $thumbnail = get_the_post_thumbnail($post->ID, 'large');
                $link = get_the_permalink($post->ID);
								$exp = get_the_excerpt($post->ID);
								$date = get_the_date('d.m.Y', $post->ID);

								$author_id = get_the_author_meta('ID');
								$author_name = get_the_author_meta('display_name', $author_id);$author_position = get_the_author_meta('user_description', $author_id);$author_avatar = get_avatar_url($author_id);
              ?>
						<div class="swiper-slide">
							<div class="news-item" data-aos="flip-up">
								<div class="news-box">
									<div class="news-img">
										<a class="box hv-item" href="<?php echo $link; ?>">
											<?php echo get_post_thumbnail_monamedia( $thumbnail ); ?>
										</a>
									</div>
									<div class="news-desc">
										<div class="news-more">
											<div class="news-date">
												<span class="icon">
													<img src="<?php echo MONA_HOME_URL; ?>/template/assets/images/news-date.svg" alt="" />
												</span>
												<span class="text">
													<?php echo $date; ?>
												</span>
											</div>
											<div class="news-view">
												<span class="icon">
													<img src="<?php echo MONA_HOME_URL; ?>/template/assets/images/Eye.svg" alt="" />
												</span>
												<span class="text">
													<?= mona_get_post_view(); ?>
												</span>
											</div>
										</div>
										<a class="news-name" href="<?php echo $link; ?>">
											<?php echo $title; ?>
										</a>
										<p class="news-txt">
											<?php echo $exp; ?>
										</p>
										<div class="dblog-info">
											<div class="dblog-avt">
												<img src="<?php echo $author_avatar; ?>" alt="" />
											</div>
											<div class="dblog-info-desc">
												<p class="dblog-info-name"><?php echo $author_name; ?></p>
												<p class="dblog-info-date"><?php echo $author_position; ?></p>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php  } wp_reset_query(); } ?>
					</div>
					<div class="reblog-btn">
						<div class="swiper-button-next reblog-btn-next">
							<i class="fa-light fa-arrow-right-long"></i>
						</div>
						<div class="swiper-button-prev reblog-btn-prev">
							<i class="fa-light fa-arrow-left-long"></i>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>