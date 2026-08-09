<?php

/**
 * Section name: Category List
 * Description: 
 * Author: Monamedia
 * Order: 
 */
?>
<section class="home_tabs free-slide2">
	<div class="container">
		<h2 class="title-sm2 fw-7 cl-text2 title-main">
			<p><strong><?php echo __('Danh sách', 'monamedia'); ?></strong> <?php echo __('danh mục', 'monamedia'); ?></p>
			
		</h2>
		<div class="home_tabs--wrap list-post">
			<div class="home_tabs--list d-wrap">
				<div class="swiper mySwiper">
					<div class="swiper-wrapper">
						<div class="swiper-slide d-item">
							<div class="home_tabs--item tab-click actived" data-cat="" data-layout="main">
								<span class="ic">
									<img src="<?php echo MONA_SITE_TEMPLATE; ?>/assets/images/t1.svg" alt="" />
								</span>
								<p class="title"><?php echo __('Tất cả', 'monamedia') ?></p>
							</div>
						</div>
						<?php
						// $box = get_field('mon_home_list_cat');
						$box = get_terms(array(
							'taxonomy' => 'category',
							'hide_empty' => true,
							'include' => array(40, 79, 64, 65, 1, 32),
							'orderby' => 'include',
						));
						if (content_exists($box)) {
							foreach ($box as $key => $item) {
								if (!is_wp_error($item)) {
						?>
									<div class="swiper-slide d-item">
										<div class="home_tabs--item tab-click" data-cat="<?php echo $item->term_id ?>" data-layout="main">
											<span class="ic">
												<?php echo wp_get_attachment_image(get_field('mon_icon_category', $item), 'full') ?>
											</span>
											<p class="title"><?php echo $item->name ?></p>
										</div>
									</div>
						<?php }
							}
						} ?>
					</div>
				</div>
				<div class="swiper-scrollbar"></div>
			</div>
			<?php
				$posts_per_page = 7;
				$args = array(
					'post_type' => 'post',
					'post_status' => 'publish',
					'posts_per_page' => $posts_per_page,
				);
				$list_posts = new WP_Query($args);
				$countposts = $list_posts->found_posts;
			?>
			<div class="home_tabs--content is-loading-btn mona-list-posts">
				<?php
				if ($countposts > 0) {
					$count = 0;
				?>
					<div class="news_wrap--content d-wrap">
						<div class="news_lf d-item">
							<div class="news_lf--wrap">
								<div class="news_lf--lf">
									<div class="news_lf--lf-list">
										<?php
										while ($list_posts->have_posts()) {
											$list_posts->the_post();
										?>
											<div class="news_lf--lf-item">
												<?php
												/**
												 * Section name: Post
												 * Description: 
												 * Author: Monamedia
												 */
												get_template_part('partials/loop/box-blog');
												?>
											</div>
										<?php
											break;
										}
										?>
									</div>
								</div>
								<?php
								if ($countposts > 1) {
								?>
									<div class="news_lf--rt">
										<div class="news_lf--rt-list">
											<?php while ($list_posts->have_posts()) {
												if ($count > 1) {
													break;
												}
												$list_posts->the_post();
											?>
												<div class="news_lf--rt-item">
													<?php
													/**
													 * Section name: Post
													 * Description: 
													 * Author: Monamedia
													 */
													get_template_part('partials/loop/box-blog');
													?>
												</div>
											<?php
												$count++;
											}
											?>
										</div>
									</div>
								<?php
								} else wp_reset_postdata();
								?>
							</div>
						</div>
						<div class="news_rt d-item">
							<div class="news_rt--wrap">
								<div class="news_ct--rt-box">
									<div class="title-box">
										<p class="title-mn cl-text2 fw-7">
											<?php echo __('Xem nhanh', 'monamedia'); ?>
										</p>
										<a class="see-more" href="<?php echo get_permalink(MONA_PAGE_BLOG) ?>">
											<?php echo __('Xem tất cả ', 'monamedia'); ?></a>
									</div>
									<?php
									if ($countposts > 3) {
									?>
										<ul class="news_ct--rt-list">
											<?php while ($list_posts->have_posts()) {
												if ($count > 5) {
													break;
												}
												$list_posts->the_post();
											?>
												<li class="news_ct--rt-item">
													<?php
													/**
													 * Section name: Post
													 * Description: 
													 * Author: Monamedia
													 */
													get_template_part('partials/loop/box-blog');
													?>
												</li>
											<?php
												$count++;
											}
											wp_reset_postdata(); ?>
										</ul>
									<?php
									}
									?>
								</div>
							</div>
						</div>
					</div>
				<?php
				} else { ?>
					<div class="mona-empty-message-large">
						<p><?php echo __('Không tìm thấy bài viết!', 'monamedia') ?></p>
					</div>
				<?php
				}
				?>
			</div>

		</div>
	</div>
</section>