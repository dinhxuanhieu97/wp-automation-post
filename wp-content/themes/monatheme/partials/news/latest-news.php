<?php

/**
 * Section name: Tin tức mới nhất
 * Description: 
 * Author: Monamedia
 * Order: 0
 */
$posts_per_page = isset($args['limit']) ? $args['limit'] : 10;
if (is_page(MONA_PAGE_EVENT)) {
	$post_type = 'event';
} else {
	$post_type = 'post';
}
$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

$offset = ($paged - 1) * $posts_per_page;
$args = [
	'post_type' => 'post',
	'posts_per_page' => $posts_per_page,
	'post_status'    => 'publish',
	'order' => 'DESC',
	'paged' => $paged,
	'offset' => $offset,
];
$list_posts = new WP_Query($args);
$max_num_pages = $list_posts->max_num_pages;
$countposts = $list_posts->found_posts;
if ($countposts > 0) {
?>
	<section class="post_care">
		<div class="container">
			<div class="post_care--box">
				<div class="post_care--wrap d-wrap">
					<div class="post_care--lf d-item">
						<form id="formLoadMore">
							<input type="hidden" name="post_type" value="<?php echo $post_type; ?>" />
							<input type="hidden" name="posts_per_page" value="<?php echo $posts_per_page; ?>" />
							<input type="hidden" name="layout" value="loadmore" />
							<div class="post_care--lf-wrap">
								<div class="title-box">
									<h2 class="title-sm2 fw-7 cl-text2 title-main">
										<?php
										$tt = get_field('mon_single_blog_news_title', MONA_PAGE_BLOG);
										?>
										<?php echo !empty($tt) ? $tt : __('Tin tức mới nhất', 'monamedia'); ?>
									</h2>
								</div>
								<div class="post_care--lf-list newsn-list home_more--list">
									<?php
									while ($list_posts->have_posts()) {
										$list_posts->the_post();
									?>
										<div class="post_care--lf-item">
											<?php
											get_template_part('partials/loop/box-blog');
											?>
										</div>
									<?php
									}
									wp_reset_postdata();
									?>
								</div>
								<?php if ($paged < $max_num_pages) { ?>
									<div class="mona-load-btn is-loading-btn">
										<a class="btn cl-2" id="monaLoadMore" href="" data-paged="<?php echo ++$paged; ?>">
											<span class="btn-text">
												<?php echo __('Xem thêm tin tức', 'monamedia'); ?>
											</span>
										</a>
									</div>
								<?php }
								?>
							</div>
						</form>
					</div>
					<!-- <php
					//if (is_home()) {
					?> -->
						<!-- Section name: Tin tức bạn quan tâm  -->
						<!-- // $mon_single_choice_list = get_field('mon_single_choice_list', MONA_PAGE_BLOG); -->
						<?php
						// Lấy danh sách bài viết xem nhiều nhất (dùng meta key 'post_views_count')
						$popular_args = [
							'post_type'           => 'post',
							'posts_per_page'      => 5,
							'post_status'         => 'publish',
							// 'meta_key'            => '_mona_post_view',
							// 'orderby'             => ['meta_value_num' => 'DESC', 'date' => 'DESC'],
							// 'meta_type'           => 'NUMERIC',
							// 'ignore_sticky_posts' => true,
						];
						$mon_single_choice_list = get_posts($popular_args);

						// Nếu chưa có meta view hoặc kết quả rỗng, fallback ra bài mới nhất
						if (empty($mon_single_choice_list)) {
							$mon_single_choice_list = get_posts([
								'post_type'      => isset($post_type) ? $post_type : 'post',
								'posts_per_page' => 5,
								'post_status'    => 'publish',
								'orderby'        => 'date',
								'order'          => 'DESC',
								'meta_key'      => '_mona_post_view',
								'orderby'       => ['meta_value_num' => 'DESC', 'date' => 'DESC'],
								'meta_type'    => 'NUMERIC',
								'ignore_sticky_posts' => true,
							]);
						}
						// if (content_exists($mon_single_choice_list)) {
						?>
							<div class="post_care--rt d-item">
								<div class="post_care--rt-wrap">
									<div class="news_ct--rt-box">
										<div class="title-mn cl-text2 fw-7">
											<?php
											$tt = get_field('mon_single_choice_title', MONA_PAGE_BLOG);
											?>
											<?php
											echo !empty($tt) ? $tt : __('Danh sách bài viết phổ biến', 'monamedia');
											?>
										</div>
										<ul class="news_ct--rt-list">
											<?php
											// foreach ($mon_single_choice_list as $post) {
											// 	setup_postdata($post);
									foreach ($mon_single_choice_list as $post) {
                    setup_postdata($post);
									?>
									<li class="news_ct--rt-item">
													<?php
													/**
													 * Section name: Sự kiện
													 * Description: 
													 * Author: Monamedia
													 */
													get_template_part('partials/loop/box-blog');
													?>
												</li>
											<?php }
                wp_reset_postdata();
                ?>
										</ul>
									</div>
								</div>
							</div>
					<!-- <php } ?> -->
				</div>
			</div>
		</div>
	</section>
<?php
}
?>