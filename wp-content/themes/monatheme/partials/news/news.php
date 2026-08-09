<?php

/**
 * Section name: Tin tức 
 * Description: 
 * Author: Monamedia
 * Order: 0
 */

?>
<section class="news">
	<div class="container">
		<div class="news_wrap">
			<div class="title-box">
				<h1 class="title-lg fw-7 cl-text2">
					<?php echo !empty(get_field('mon_single_blog_title', MONA_PAGE_BLOG)) ? get_field('mon_single_blog_title', MONA_PAGE_BLOG) : __('Tin tức','monamedia'); ?>
				</h1>
			</div>
			<?php $mon_single_blog = get_field('mon_single_blog', MONA_PAGE_BLOG);
			if (is_array( $mon_single_blog ) && !empty( $mon_single_blog )) { 
				// $first_post_displayed = false;
				?>
			<div class="news_wrap--content d-wrap">
				<div class="news_lf d-item">
					<div class="news_lf--wrap">
						<div class="news_lf--lf">
							<div class="news_lf--lf-list">
								<?php foreach ($mon_single_blog as $post) {
									setup_postdata($post);
									?>
								<div class="news_lf--lf-item">
									<?php 
										/**
										 * Section name: Post
										 * Description: 
										 * Author: Monamedia
										 */
										get_template_part( 'partials/loop/box-blog' );
										// $first_post_displayed = true;
									?>
								</div>
								<?php break; } wp_reset_postdata(); ?>
							</div>
						</div>
						<div class="news_lf--rt">
							<div class="news_lf--rt-list">
								<?php $count = 0; foreach ($mon_single_blog as $post) {
									$count++;
									if ($count > 1 && $count < 4) {
									setup_postdata($post);
									?>
								<div class="news_lf--rt-item">
									<?php 
										/**
										 * Section name: Blog
										 * Description: 
										 * Author: Monamedia
										 */
										get_template_part( 'partials/loop/box-blog');
									?>
								</div>
								<?php } } wp_reset_postdata(); ?>
							</div>
						</div>
					</div>
				</div>
				<div class="news_rt d-item">
					<div class="news_rt--wrap">
						<div class="news_ct--rt-box">
							<div class="title-box">
								<p class="title-mn cl-text2 fw-7">
									<?php echo __('Xem nhanh', 'monamedia'); ?></p>
							</div>
							<ul class="news_ct--rt-list">
								<?php $count = 0; foreach ($mon_single_blog as $post) {
									$count++;
									if ($count > 3 && $count < 8) {
									setup_postdata($post);
									?>
								<li class="news_ct--rt-item">
									<?php 
										/**
										 * Section name: post
										 * Description: 
										 * Author: Monamedia
										 */
										get_template_part( 'partials/loop/box-blog');
									?>
								</li>
								<?php } } wp_reset_postdata(); ?>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
</section>