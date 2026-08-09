<?php

/**
 * Section name: Sự kiện mới nhất
 * Description: 
 * Author: Monamedia
 * Order: 0
 */

$post_type          = 'event';
$posts_per_page = 12;
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$offset = ($paged - 1) * $posts_per_page;
$args = [
	'post_type' => $post_type,
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
	<form id="formEventPostAjax">
		<input type="hidden" name="post_type" value="<?php echo $post_type; ?>" />
		<input type="hidden" name="posts_per_page" value="<?php echo $posts_per_page; ?>" />
		<input type="hidden" name="layout" value="loadmore" />

		<section class="events_new">
			<div class="container">
				<div class="events_new--wrap">
					<div class="title-box">
						<h2 class="title-sm2 fw-7 cl-text2 title-main">
							<?php
							$tt = get_field('mon_single_title_news');
							?>
							<?php echo !empty($tt) ? $tt : __('Sự kiện mới nhất', 'monamedia'); ?>
						</h2>
					</div>

					<div class="events_new--box">
						<div class="events_new--list d-wrap  newsn-list home_more--list">
							<?php
							while ($list_posts->have_posts()) {
								$list_posts->the_post();
							?>
								<div class="events_new--item d-item d-4">
									<?php
									get_template_part('partials/loop/box-event');
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
				</div>
			</div>
		</section>
	</form>
<?php
}
?>