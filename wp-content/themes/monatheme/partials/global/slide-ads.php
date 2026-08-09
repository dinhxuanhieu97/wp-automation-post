<?php

/**
 * Section name: Slide Advertisement
 * Description: 
 * Author: Monamedia
 * Order: 0
 */
$mon_slide_banner_ads = [];
if (is_home()) {
	$mon_slide_banner_ads = get_field('mon_slide_banner_ads', MONA_PAGE_BLOG);
} elseif (is_singular('post')) {
	if (!get_field('mona_post_banner_enable')) {
		if (!get_field('mona_post_banner_choice'))
			$mon_slide_banner_ads = get_field('mon_slide_banner_ads', MONA_PAGE_BLOG);
		else $mon_slide_banner_ads = get_field('mon_slide_banner_ads');
	}
} elseif (is_singular('event')) {
	if (!get_field('mona_post_banner_enable')) {
		if (!get_field('mona_post_banner_choice'))
			$mon_slide_banner_ads = get_field('mon_slide_banner_ads', MONA_PAGE_EVENT);
		else
			$mon_slide_banner_ads = get_field('mon_slide_banner_ads');
	}
} else {
	$mon_slide_banner_ads = get_field('mon_slide_banner_ads');
}
if (content_exists($mon_slide_banner_ads)) {
?>
	<div class="sl_img free-slide">
		<div class="container">
			<div class="sl_img--wrap">
				<div class="sl_img--list">
					<div class="sl_img--list-wrap d-wrap">
						<div class="swiper mySwiper --auto">
							<div class="swiper-wrapper">
								<?php foreach ($mon_slide_banner_ads as $item) { ?>
									<div class="swiper-slide d-item">
										<div class="sl_img--item">
											<?php
											$btn = $item['link'];
											if (content_exists($btn)) {
											?>
												<a class="sl_img--img" href="<?php echo $btn['url'] ?>" target="<?php echo $btn['target'] ?>">
													<?php echo wp_get_attachment_image($item['image'], 'full'); ?>
												</a>
											<?php
											} else {
											?>
												<span class="sl_img--img">
													<?php echo wp_get_attachment_image($item['image'], 'full'); ?>
												</span>
											<?php
											}
											?>
										</div>
									</div>
								<?php } ?>
							</div>
						</div>
						<div class="swiper-pagination"></div>
					</div>
					<div class="box-navi">
						<div class="btn-navi prev">
							<i class="fa-light fa-angle-left"></i>
						</div>
						<div class="btn-navi next">
							<i class="fa-light fa-angle-right"></i>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php } else { ?>
<section class="sl_img free-slide"> 
        <div class="container"> 
          <div class="sl_img--wrap"> 
            <div class="sl_img--list d-wrap">
              <div class="swiper mySwiper"> 
                <div class="swiper-wrapper"> 
                  <div class="swiper-slide d-item">
                    <div class="sl_img--item"><a class="sl_img--img"><img src="/wp-content/uploads/2025/10/bn1.png" alt=""/></a></div>
                  </div>
                  <div class="swiper-slide d-item">
                    <div class="sl_img--item"><a class="sl_img--img"><img src="/wp-content/uploads/2025/10/bn1.png" alt=""/></a></div>
                  </div>
                  <div class="swiper-slide d-item"> 
                    <div class="sl_img--item"><a class="sl_img--img"><img src="/wp-content/uploads/2025/10/bn1.png" alt=""/></a></div>
                  </div>
                </div>
              </div>
              <div class="box-navi">
                <div class="btn-navi prev"> <i class="fa-light fa-angle-left"></i></div>
                <div class="btn-navi next"> <i class="fa-light fa-angle-right"></i></div>
              </div>
            </div>
            <div class="swiper-pagination"></div>
          </div>
        </div>
      </section>
<?php } ?>
