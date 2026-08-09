<?php

/**
 * Section name: Tài chính phát triển
 * Description: 
 * Author: Monamedia
 * Order: 0
 */
?>
<?php
// $box = get_field('mona_home_finance_cat_list', MONA_PAGE_HOME);
$box = get_terms(array(
	'taxonomy' => 'category',
	'hide_empty' => true,
	'include' => array(40, 64),
	'orderby' => 'include',
	'offset' => 10,
));
if (content_exists($box)) {
?>
	<section class="home_finance">
		<div class="container">
			<div class="home_finance--wrap list-post">
				<div class="home_title">
					<h2 class="title-sm2 fw-7 cl-text2 title-main">
						<?php 
							$title = get_field('mona_home_finance_tt', MONA_PAGE_HOME);
							if (!empty($title)) {
								echo $title;
							} else {
								echo '<p><strong>Bài viết</strong> Phong thủy</p>';
							}
						?>
					</h2>
					<div class="home_title--list d-wrap free-slide2">
						<div class="swiper mySwiper">
							<div class="swiper-wrapper">
								<?php
								foreach ($box as $key => $item) {
									if (!is_wp_error($item)) {
								?>
										<div class="swiper-slide d-item">
											<div class="home_title--item tab-click <?php echo $key == 0 ? 'actived' : '' ?>" data-cat="<?php echo $item->term_id ?>" data-layout="main3">
												<a class="home_title--link" href="<?php echo get_term_link($item) ?>"><?php echo $item->name ?></a>
											</div>
										</div>
								<?php
									}
								}
								?>
							</div>
						</div>
						<div class="swiper-scrollbar">
						</div>
					</div>
				</div>
				<?php
				$posts_per_page = 9;
				$args = array(
					'post_type' => 'post',
					'post_status' => 'publish',
					'posts_per_page' => $posts_per_page,
					'tax_query' => array(
						array(
							'taxonomy' => 'category',
							'field' => 'term_id',
							'terms' => $box[0]->term_id
						)
					)
				);
				$list_posts = new WP_Query($args);
				$countposts = $list_posts->found_posts;

				?>
				<div class="home_finance--content is-loading-btn mona-list-posts">
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
													get_template_part('partials/loop/box-blog')
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
												<?php
												while ($list_posts->have_posts()) {
													if ($count > 3) {
														break;
													}
													$list_posts->the_post();
												?>
													<div class="news_lf--rt-item">
														<?php
														get_template_part('partials/loop/box-blog')
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
											<p class="title-mn cl-text2 fw-7"><?php echo __('Gợi ý bài viết', 'monamedia'); ?></p>
										</div>
										<?php
										if ($countposts > 5) {
										?>
											<ul class="news_ct--rt-list">
												<?php
												while ($list_posts->have_posts()) {
													if ($count > 7) {
														break;
													}
													$list_posts->the_post();
												?>
													<li class="news_ct--rt-item">
														<?php
														get_template_part('partials/loop/box-blog')
														?>
													</li>
												<?php
													$count++;
												}
												wp_reset_postdata()
												?>
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
<?php
}
?>