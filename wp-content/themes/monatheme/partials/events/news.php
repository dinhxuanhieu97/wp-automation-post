<?php

/**
 * Section name: Sự kiện mới nhất
 * Description: 
 * Author: Monamedia
 * Order: 0
 */
$args = [
	'post_type' => 'event',
	'posts_per_page' => 12,
	'post_status'    => 'publish',
	'order' => 'DESC',
];
$queryPro = new WP_Query($args);
?>
<section class="sec-pro">
	<div class="pro ss-pd">
		<div class="pro-dc-br">
			<img src="<?php echo MONA_HOME_URL; ?>/template/assets/img/song-decor-second.png " alt="">
		</div>
		<div class="decor-posi decor-like">
			<img src="<?php echo MONA_HOME_URL; ?>/template/assets/img/decor-like.png" alt="">
		</div>
		<div class="decor-posi decor-new">
			<img src="<?php echo MONA_HOME_URL; ?>/template/assets/img/decor-new.svg" alt="">
		</div>
		<div class="container">
			<div class="pro-head mb-40">
				<?php
				$mona_group_news = get_field('mona_group_news');
				$title = $mona_group_news['title'];
				$sub_title = $mona_group_news['sub_title'];
				if (is_array($mona_group_news) && !empty($mona_group_news)) { ?>
					<div class="head t-center">
						<p class="t-title-sub">
							<?php echo $sub_title; ?>
						</p>
						<h2 class="t-title-rm scroll-item" data-title="<?php echo $title; ?>">
							<?php echo $title; ?>
						</h2>
					</div>
				<?php } ?>
				<div class="pro-search">
					<form method="GET" action="<?php echo esc_url(home_url('/')); ?>">
						<div class="pro-search-wr">
							<input type="search" name="s" value="<?php echo get_search_query(); ?>" id="s" placeholder="<?php echo __('Tìm kiếm tin tức', 'monamedia'); ?>">
							<button type="submit" class="pro-search-btn">
								<img src="<?php echo MONA_HOME_URL; ?>/template/assets/img/icon-search.svg" alt="">
							</button>
						</div>
					</form>
				</div>
				<div class="pro-fil">
					<p class="title fw-7">
						<?php echo __('Danh mục:', 'monamedia'); ?>
					</p>
					<div class="pro-fil-slider">
						<div class="swiper-container rows">
							<div class="swiper">
								<div class="swiper-wrapper">
									<div class="swiper-slide col active">
										<a href="<?php echo esc_url(get_permalink(MONA_PAGE_BLOG)); ?>" class="pro-fil-btn">
											<?php echo __('Tất cả', 'monamedia'); ?>
										</a>
									</div>
									<?php
									$job_categories = get_terms(array(
										'taxonomy' => 'category',
										'hide_empty' => false,
									));
									foreach ($job_categories as $category) {
										$category_link = get_term_link($category);
									?>
										<div class="swiper-slide col">
											<a href="<?php echo esc_url($category_link); ?>" class="pro-fil-btn">
												<?php echo $category->name; ?>
											</a>
										</div>
									<?php } ?>
								</div>
							</div>
						</div>
						<div class="swiper-control">
							<div class="swiper-control-btn swiper-prev">
								<i class="fal fa-long-arrow-left"></i>
							</div>
							<div class="swiper-control-btn swiper-next">
								<i class="fal fa-long-arrow-right"></i>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php if ($queryPro->have_posts()) { ?>
				<div class="pro-list row">
					<?php
					while ($queryPro->have_posts()) {
						$queryPro->the_post();
						$title = get_the_title($post->ID);
						$exp = get_the_excerpt($post->ID);
						$date = get_the_date('d', $post->ID);
						$thumbnail = get_the_post_thumbnail($post->ID, 'medium');
						$link = get_the_permalink($post->ID);
						$tags = get_the_tags();
						$cat = get_the_category(get_the_ID());
						$author_id = get_post_field('post_author', get_the_ID());
						$author_name = get_the_author_meta('display_name', $author_id);
						$month_number = get_the_date('m', $post->ID);
						$month_names = array(
							'01' => 'TH 1', '02' => 'TH 2', '03' => 'TH 3', '04' => 'TH 4', '05' => 'TH 5', '06' => 'TH 6', '07' => 'TH 7', '08' => 'TH 8', '09' => 'TH 9', '10' => 'TH 10', '11' => 'TH 11', '12' => 'TH 12'
						);
						$month_name = $month_names[$month_number];
					?>
						<div class="pro-item col col-4" data-aos="fade-up">
							<div class="pro-wrap">
								<a href="<?php echo $link; ?>" class="pro-img hv-bd">
									<!-- <img src="./assets/img/pro-img-1.jpg" alt=""> -->
									<?php echo get_post_thumbnail_monamedia($thumbnail); ?>
								</a>
								<div class="pro-time">
									<span class="date">
										<?php echo $date; ?>
									</span>
									<span class="month">
										<?php echo $month_name; ?>
									</span>
								</div>
								<div class="pro-content">
									<div class="pro-note">
										<div class="pro-note-item">
											<img src="<?php echo MONA_HOME_URL; ?>/template/assets/img/pro-icon-1.svg" alt="">
											<span class="text">
												<?php echo $author_name; ?>
											</span>
										</div>
										<div class="pro-note-item">
											<img src="<?php echo MONA_HOME_URL; ?>/template/assets/img/pro-icon-2.svg" alt="">
											<?php if ($tags) {
												$first_tag = reset($tags);
												echo '<span class="text">' . $first_tag->name . '</span>';
											}
											?>
										</div>
									</div>
									<h3 class="pro-name">
										<a href="<?php echo $link; ?>">
											<?php echo $title; ?>
										</a>
									</h3>
									<p class="pro-des">
										<?php echo $exp; ?>
									</p>
									<div class="pro-content-bot">
										<a href="<?php echo $link; ?>" class="pro-btn">
											<i class="fas fa-arrow-alt-circle-right"></i>
											<span class="text">
												<?php echo __(' Đọc thêm', 'monamedia'); ?>
											</span>
										</a>
									</div>
								</div>
							</div>
						</div>
					<?php }
					wp_reset_query(); ?>
				</div>
				<div class="news-pagi" data-aos="fade-up">
					<div class="news-pagi-wr">
						<?php mona_pagination_links($queryPro); ?>
					</div>
				</div>
			<?php } else { ?>
				<div class="m-box-empty">
					<span class="icon-empty">
						<img src="/wp-content/themes/monatheme/public/helpers/images/empty-box.png" alt="">
					</span>
					<span class="m-txt-empty"><?php echo __('Đang cập nhật bài viết', 'monamedia'); ?></span>
				</div>
			<?php } ?>
		</div>
	</div>
</section>