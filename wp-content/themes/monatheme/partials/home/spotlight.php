<?php

/**
 * Section name: spotlight
 * Description: 
 * Author: Monamedia
 * Order: 0
 */
?>
<?php $spotlight_list = get_field('mon_home_news_spotlight_list', MONA_PAGE_HOME);
if (is_array($spotlight_list) && !empty($spotlight_list)) { ?>
<section class="home_sport free-slide">
	<div class="container">
		<div class="home_sport--wrap">
			<div class="title-box">
				<h2 class="title-sm2 fw-7 cl-text2 title-main">
					<?php echo !empty(get_field('mon_home_spotlight_news_title', MONA_PAGE_HOME)) ? get_field('mon_home_spotlight_news_title', MONA_PAGE_HOME) : __('Spotlight','monamedia'); ?>
				</h2>

			</div>
			<div class="home_sport--list">
				<div class="home_sport--list-wrap d-wrap">
					<div class="swiper mySwiper">
						<div class="swiper-wrapper">
							<?php foreach ($spotlight_list as $post) {
									setup_postdata($post);  ?>
							<div class="swiper-slide d-4 d-item">
								<h4 class="home_sport--item">
									<div class="home_sport--item-wrap">
										<span class="home_sport--img">
											<?php echo get_the_post_thumbnail('', 'full'); ?>
										</span>
										<?php $link_youtube = get_field('mon_single_link_youtube');
											if (!empty($link_youtube)) {
											?>
										<a href="<?php echo $link_youtube ; ?>" data-fancybox="gallery" class="btn-play">
											<i class="fa-solid fa-play"></i>
										</a>
										<?php } ?>

										<?php
											$term = get_primary_taxonomy_term('');
											if (content_exists($term)) {
												$tax = get_term_by('id', $term['id'], 'category');
												$color = get_field('mona_tax_color', $tax);
												$bgcolor = get_field('mona_tax_bgcolor', $tax);
										?>
										<a class="box-dm" <?php echo 'style="background-color:' . $bgcolor . '; color:' . $color . '"' ?>>
											<?php echo $term['title'] ?>
										</a>
										<?php } ?>
										<div class="home_sport--content">
											<a class="note-text cl-white fw-7" href="<?php echo get_the_permalink() ?>">
												<?php echo get_the_title(); ?>
											</a>
											<span class="box-info">
												<span class="box-info-author">
													<p class="note-sm fw-7">
														<?php echo get_the_author(); ?>
													</p>
												</span>
												<span class="box-info-date">
													<p class="note-sm cl-gray">
														<?php echo get_the_time('m/Y'); ?>
													</p>
												</span>
											</span>
										</div>
									</div>
								</h4>
							</div>
							<?php }
								wp_reset_postdata(); ?>
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
</section>
<?php } else { ?>
<!-- <section class="home_sport free-slide">
	<div class="container">
		<div class="home_sport--wrap">
			<div class="title-box">
				<h2 class="title-sm2 fw-7 cl-text2 title-main"> <strong>Spotlight </strong> </h2><a class="see-more"
					href="">Xem tất cả</a>
			</div>
			<div class="home_sport--box">
				<div class="home_sport--list d-wrap">
					<div class="swiper mySwiper">
						<div class="swiper-wrapper">
							<div class="swiper-slide d-4 d-item">
								<h2 class="home_sport--item"><a class="home_sport--item-wrap"
										href="https://www.youtube.com/watch?v=DSE6On5DM-g" data-fancybox="gallery"><span
											class="home_sport--img"><img src="./assets/images/sp1.png" alt="" /></span><span class="btn-play">
											<i class="fa-solid fa-play"></i></span><span class="box-dm blue">Công nghệ</span><span
											class="home_sport--content">
											<p class="note-text cl-white fw-7">Công nghệ pin lượng tử phá vỡ quy luật nhân quả</p><span
												class="box-info"><span class="box-info-author">
													<p class="note-sm fw-7">Ngọc Thành</p>
												</span><span class="box-info-date">
													<p class="note-sm cl-gray">8:30 25/06/2023</p>
												</span></span>
										</span></a></h2>
							</div>
							<div class="swiper-slide d-4 d-item">
								<h2 class="home_sport--item"><a class="home_sport--item-wrap"
										href="https://www.youtube.com/watch?v=DSE6On5DM-g" data-fancybox="gallery"><span
											class="home_sport--img"><img src="./assets/images/sp2.png" alt="" /></span><span class="btn-play">
											<i class="fa-solid fa-play"></i></span><span class="box-dm pup">Khởi nghiệp</span><span
											class="home_sport--content">
											<p class="note-text cl-white fw-7">Công nghệ pin lượng tử phá vỡ quy luật nhân quả</p><span
												class="box-info"><span class="box-info-author">
													<p class="note-sm fw-7">Ngọc Thành</p>
												</span><span class="box-info-date">
													<p class="note-sm cl-gray">8:30 25/06/2023</p>
												</span></span>
										</span></a></h2>
							</div>
							<div class="swiper-slide d-4 d-item">
								<h2 class="home_sport--item"><a class="home_sport--item-wrap"
										href="https://www.youtube.com/watch?v=DSE6On5DM-g" data-fancybox="gallery"><span
											class="home_sport--img"><img src="./assets/images/sp3.png" alt="" /></span><span class="btn-play">
											<i class="fa-solid fa-play"></i></span><span class="box-dm green">Tài chính</span><span
											class="home_sport--content">
											<p class="note-text cl-white fw-7">Công nghệ pin lượng tử phá vỡ quy luật nhân quả</p><span
												class="box-info"><span class="box-info-author">
													<p class="note-sm fw-7">Ngọc Thành</p>
												</span><span class="box-info-date">
													<p class="note-sm cl-gray">8:30 25/06/2023</p>
												</span></span>
										</span></a></h2>
							</div>
							<div class="swiper-slide d-4 d-item">
								<h2 class="home_sport--item"><a class="home_sport--item-wrap"
										href="https://www.youtube.com/watch?v=DSE6On5DM-g" data-fancybox="gallery"><span
											class="home_sport--img"><img src="./assets/images/sp4.png" alt="" /></span><span class="btn-play">
											<i class="fa-solid fa-play"></i></span><span class="box-dm red">Bảo mật</span><span
											class="home_sport--content">
											<p class="note-text cl-white fw-7">Công nghệ pin lượng tử phá vỡ quy luật nhân quả</p><span
												class="box-info"><span class="box-info-author">
													<p class="note-sm fw-7">Ngọc Thành</p>
												</span><span class="box-info-date">
													<p class="note-sm cl-gray">8:30 25/06/2023</p>
												</span></span>
										</span></a></h2>
							</div>
							<div class="swiper-slide d-4 d-item">
								<h2 class="home_sport--item"><a class="home_sport--item-wrap"
										href="https://www.youtube.com/watch?v=DSE6On5DM-g" data-fancybox="gallery"><span
											class="home_sport--img"><img src="./assets/images/sp1.png" alt="" /></span><span class="btn-play">
											<i class="fa-solid fa-play"></i></span><span class="box-dm blue">Công nghệ</span><span
											class="home_sport--content">
											<p class="note-text cl-white fw-7">Công nghệ pin lượng tử phá vỡ quy luật nhân quả</p><span
												class="box-info"><span class="box-info-author">
													<p class="note-sm fw-7">Ngọc Thành</p>
												</span><span class="box-info-date">
													<p class="note-sm cl-gray">8:30 25/06/2023</p>
												</span></span>
										</span></a></h2>
							</div>
							<div class="swiper-slide d-4 d-item">
								<h2 class="home_sport--item"><a class="home_sport--item-wrap"
										href="https://www.youtube.com/watch?v=DSE6On5DM-g" data-fancybox="gallery"><span
											class="home_sport--img"><img src="./assets/images/sp2.png" alt="" /></span><span class="btn-play">
											<i class="fa-solid fa-play"></i></span><span class="box-dm pup">Khởi nghiệp</span><span
											class="home_sport--content">
											<p class="note-text cl-white fw-7">Công nghệ pin lượng tử phá vỡ quy luật nhân quả</p><span
												class="box-info"><span class="box-info-author">
													<p class="note-sm fw-7">Ngọc Thành</p>
												</span><span class="box-info-date">
													<p class="note-sm cl-gray">8:30 25/06/2023</p>
												</span></span>
										</span></a></h2>
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
	</div>
</section> -->
<?php } ?>