<?php

/**
 * Template name: Liên hệ
 * @author : MONA.Media / Website
 */
get_header();
while (have_posts()) :
	the_post();
?>
	<main class="main pt-2">
		<?php get_template_part('partials/breadcrumb'); ?>
		<h1 class="hidden-tt">
			<?php
			the_title();
			?>
		</h1>
		<section class="contact">
			<div class="container">
				<div class="contact_wrap d-wrap">
					<?php
					$main = get_field('mona_ct_info');
					if (content_exists($main)) {
					?>
						<div class="contact_lf d-item">
							<div class="contact_lf--wrap">
								<div class="contact_lf--bg">
									<img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/bgct.png" alt="" />
								</div>
								<div class="contact_lf--title">
									<h2 class="title-sm2 cl-white fw-7"><?php echo $main['tt'] ?></h2>
									<p class="note-text cl-white"><?php echo $main['ct'] ?></p>
								</div>
								<ul class="contact_lf--list">
									<?php
									$box = $main['info'];
									if (content_exists($box)) {
										foreach ($box as $item) {
									?>
											<li class="contact_lf--item">
												<p class="note-text fw-7 cl-white">
													<?php echo $item['tt'] ?>
												</p>
												<?php
												$box1 = $item['box'];
												if (content_exists($box1)) {
													foreach ($box1 as $item1) {
														switch ($item1['type']) {
															case 'text':
																echo '<p class="note-text cl-white">' . $item1['tt'] . ' ' . $item1['ct'] . '</p>';
																break;
															case 'tel':
																echo '<a href="' . mona_replace_tel($item1['ct']) . '" class="note-text cl-white">' . $item1['tt'] . ' ' . $item1['ct'] . '</a>';
																break;
															case 'mail':
																echo '<a href="mailto:' . $item1['ct'] . '" class="note-text cl-white">' . $item1['tt'] . ' ' . $item1['ct'] . '</a>';
																break;
														}
													}
												}
												?>
											</li>
									<?php
										}
									}
									?>
									<?php
									$box = get_field('mona_ft_social', 'option');
									if (content_exists($box)) {
									?>
										<li class="contact_lf--item">
											<p class="note-text fw-7 cl-white">
												<?php echo get_field('mona_ft_tt', 'option') ?>
											</p>
											<div class="footer-mxh">
												<ul class="footer-mxh-list">
													<?php foreach ($box as $item) : ?>
														<li class="footer-mxh-item">
															<a class="footer-mxh-link" href="<?php echo !empty($item['url']) ? $item['url'] : 'javascript:;' ?>" target="<?php echo !empty($item['url'])  ? '_blank' : '' ?>">
																<?php echo wp_get_attachment_image($item['icon'], 'full') ?>
															</a>
														</li>
													<?php endforeach; ?>
												</ul>
											</div>
										</li>
									<?php } ?>
								</ul>
							</div>
						</div>
					<?php
					}
					?>
					<div class=" contact_rt d-item">
						<div class="contact_rt--wrap tab-box">
							<ul class="contact_rt--tabs">
								<li class="contact_rt--tab tab-btn actived">
									<?php echo get_field('mon_ct_adv_title') ?>
								</li>
								<li class="contact_rt--tab tab-btn">
									<?php echo get_field('mon_ct_shopping_title') ?>
								</li>
							</ul>
							<div class="contact_rt--list">
								<div class="contact_rt--item tab-content showed">
									<?php
									$form = get_field('mon_ct_form_adv_shortcode');
									if (!empty($form))
										echo do_shortcode('[contact-form-7 id="' . $form . '" html_id="" html_class=""]');
									?>
								</div>
								<div class="contact_rt--item tab-content">
									<?php
									$form = get_field('mon_ct_form_shopping_shortcode');
									if (!empty($form))
										echo do_shortcode('[contact-form-7 id="' . $form . '" html_id="" html_class=""]');
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<div class="contact_map">
			<div class="contact_map--iframe">
				<?php echo get_field('mon_ct_Iframe_google_map') ?>
			</div>
		</div>
		<?php
		get_template_part('partials/global/signup');

		?>
	</main>
<?php
endwhile;
get_footer();
