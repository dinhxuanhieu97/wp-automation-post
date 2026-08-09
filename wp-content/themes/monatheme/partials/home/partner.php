<?php
/**
 * Section name: Đối tác
 * Description: 
 * Author: Monamedia
 * Order: 0
 */
$mon_home_partner = get_field('mon_home_partner', MONA_PAGE_HOME);
if (is_array( $mon_home_partner ) && !empty( $mon_home_partner )) {
?>
<section class="home_discover">
	<div class="container">
		<div class="home_discover--wrap d-wrap">
			<div class="home_discover--lf d-item">
				<div class="home_discover--lf-wrap">
					<?php echo !empty($mon_home_partner['content']) ? $mon_home_partner['content'] : __('Spotlight','monamedia'); ?>
				</div>
			</div>
			<div class="home_discover--rt d-item">
				<ul class="home_discover--list d-wrap">
					<?php foreach ($mon_home_partner['gallery'] as $item) { ?>
					<li class="home_discover--item d-item">
						<div class="home_discover--img">
							<?php echo wp_get_attachment_image($item, 'large') ?>
						</div>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>
	</div>
</section>
<?php } else { ?>
<section class="home_discover">
	<div class="container"> 
		<div class="home_discover--wrap d-wrap">
			<div class="home_discover--lf d-item">
				<div class="home_discover--lf-wrap"> 
					<h2 class="title-mn cl-text2 fw-7">Khám phá các mối quan hệ đối tác kinh doanh tiềm năng </h2>
					<p class="note-text">Tiếp cận các công ty khởi nghiệp đầy triển vọng ở Đông Nam Á hơn 34 000 công ty khởi nghiệp.</p>
				</div>
			</div>
			<div class="home_discover--rt d-item">
				<ul class="home_discover--list d-wrap">
					<li class="home_discover--item d-item"> 
						<div class="home_discover--img"> <img src="/template/assets/images/d1.png" alt=""/>
						</div>
					</li>
					<li class="home_discover--item d-item"> 
						<div class="home_discover--img"> <img src="/template/assets/images/d2.png" alt=""/>
						</div>
					</li>
					<li class="home_discover--item d-item"> 
						<div class="home_discover--img"> <img src="/template/assets/images/d3.png" alt=""/>
						</div>
					</li>
					<li class="home_discover--item d-item"> 
						<div class="home_discover--img"> <img src="/template/assets/images/d4.png" alt=""/>
						</div>
					</li>
					<li class="home_discover--item d-item"> 
						<div class="home_discover--img"> <img src="/template/assets/images/d5.png" alt=""/>
						</div>
					</li>
					<li class="home_discover--item d-item"> 
						<div class="home_discover--img"> <img src="/template/assets/images/d4.png" alt=""/>
						</div>
					</li>
					<li class="home_discover--item d-item"> 
						<div class="home_discover--img"> <img src="/template/assets/images/d5.png" alt=""/>
						</div>
					</li>
					<li class="home_discover--item d-item"> 
						<div class="home_discover--img"> <img src="/template/assets/images/h6.png" alt=""/>
						</div>
					</li>
					<li class="home_discover--item d-item"> 
						<div class="home_discover--img"> <img src="/template/assets/images/h7.png" alt=""/>
						</div>
					</li>
					<li class="home_discover--item d-item"> 
						<div class="home_discover--img"> <img src="/template/assets/images/h8.png" alt=""/>
						</div>
					</li>
					<li class="home_discover--item d-item"> 
						<div class="home_discover--img"> <img src="/template/assets/images/h7.png" alt=""/>
						</div>
					</li>
					<li class="home_discover--item d-item"> 
						<div class="home_discover--img"> <img src="/template/assets/images/h8.png" alt=""/>
						</div>
					</li>
				</ul>
			</div>
		</div>
	</div>
</section>
<?php } ?>
