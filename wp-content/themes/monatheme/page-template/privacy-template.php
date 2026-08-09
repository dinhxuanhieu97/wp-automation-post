<?php

/**
 * Template name: Policy Template
 * @author : MONA.Media / Website
 */
get_header();

?>
<main class="main pt-2">
	<?php get_template_part('partials/breadcrumb'); ?>
	<section class="cs">
		<div class="container">
			<div class="cs_wrap d-wrap">
				<div class="cs_lf d-item">
					<div class="cs_lf--wrap">
						<div class="cs_lf--content">
							<p class="title-mn cl-text2 fw-7"><?php echo __('Mục lục', 'monamedia'); ?></p>
							<ul class="menu-list">
								<li class="menu-item dropdown">
									<a class="menu-link" href="javascript:;">
										<?php echo get_the_title(); ?>
									</a>
									<ul class="menu-list">
										<?php if (preg_match('/<h[1-6][^>]*>.*?<\/h[1-6]>/i', get_the_content())) { ?>
											<?php echo do_shortcode('[ez-toc]'); ?>
										<?php } ?>
									</ul>
								</li>
								<?php
								$mon_policy_list = get_field('mon_policy_list');
								if (is_array($mon_policy_list) && !empty($mon_policy_list)) {
									foreach ($mon_policy_list as $post) {
										setup_postdata($post);
								?>
										<li class="menu-item">
											<a class="menu-link" href="<?php echo get_the_permalink($post->ID); ?>">
												<?php echo get_the_title($post->ID); ?>
											</a>
										</li>
								<?php }
									wp_reset_postdata();
								} ?>
								</li>
							</ul>
						</div>
					</div>
				</div>
				<div class="cs_rt d-item">
					<div class="cs_rt--wrap">
						<div class="cs_rt--title">
							<h2 class="title-sm2 cl-text2 fw-7">
								<?php echo get_the_title(); ?>
							</h2>
							<p class="note-text">
								<?php echo __('Cập nhật lần cuối: ', 'monamedia') . get_the_date('d/m/Y') ?>
							</p>
						</div>
						<div class="mona-content">
							<?php the_content(); ?>
						</div>
						<a class="btn cl-2" href="<?php the_permalink(MONA_PAGE_HOME); ?>">
							<span class="btn-text"><?php echo __('Quay về trang chủ', 'monamedia'); ?></span>
						</a>
					</div>
				</div>
			</div>
		</div>
	</section>
	<?php
	get_template_part('partials/global/signup');

	?>
</main>
<?php

get_footer();
