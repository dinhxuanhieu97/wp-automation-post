<?php

/**
 * Section name: Sign Up Section
 * Description: 
 * Author: Monamedia
 * Order: 0
 */
?>
<?php 
$class = is_page(MONA_PAGE_CONTACT) ? 'su-ct' : '';
?>
<?php $form_sign_up = get_field('mon_home_form_sign_up', 'option');
if (content_exists($form_sign_up)) { ?>
	<section class="home_alert <?php echo $class ?>">
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
<?php } else { ?>
<section class="home_alert keydevelop"> 
	<div class="home_alert--bg"><img src="/template/assets/images/bgh.png" alt=""/>
	</div>
	<div class="container">
			<div class="home_alert--wrap"> 
				<div class="home_alert--content"> 
					<div class="home_alert--top"> 
						<h2 class="title-sm cl-white fw-7">Đăng kí để nhận thông báo và tin tức mới nhất</h2>
						<p class="note-text">Bằng cách để lại email của bạn, chúng tôi sẽ cập nhật cho bạn thông tin mới nhất từ Điện Máy Kim Biên</p>
					</div>

					<?php echo do_shortcode('[contact-form-7 id="9444" title="Form Contact Email"]'); ?>
				</div>

			</div>
	</div>
</section>
<?php } ?>