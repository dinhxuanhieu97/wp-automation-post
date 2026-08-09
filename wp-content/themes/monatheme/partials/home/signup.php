<?php

/**
 * Section name: Sign Up Section
 * Description: 
 * Author: Monamedia
 * Order: 0
 */
?>
<?php $form_sign_up = get_field('mon_home_form_sign_up', 'option');
if (content_exists($form_sign_up)) { ?>
	<section class="home_alert">
		<div class="home_alert--bg">
			<?php echo wp_get_attachment_image($form_sign_up['backgroud'], 'full'); ?>
		</div>
		<div class="container">
			<div class="home_alert--wrap">
				<div class="home_alert--content">
					<div class="home_alert--top">
						<h2 class="title-sm cl-white fw-7"><?php echo $form_sign_up['tt'] ?></h2>

						<div class="note-text mona-content"><?php echo $form_sign_up['ct'] ?></div>
					</div>
					<?php
					$form = $form_sign_up['form_signup_shortcode'];
					if (!empty($form))
						echo do_shortcode('[contact-form-7 id="' . $form . '" html_id="" html_class=""]');
					?>
				</div>
			</div>
		</div>
	</section>
<?php } ?>