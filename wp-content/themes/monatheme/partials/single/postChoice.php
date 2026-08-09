<?php
/**
 * Section name: BÀI VIẾT CHỌN LỌC
 * Description: 
 * Author: Monamedia
 * Order: 0
 */
$mon_pj_related = get_field('mon_pj_related');
if( !empty( $mon_pj_related ) || !empty($mon_pj_related) ){ 
?>
<?php foreach ($mon_pj_related as $post) { 
	setup_postdata($post); ?>
<div class="swiper-slide col">
	<div class="pj-item">
		<div class="inner">
			<div class="img">
				<a class="img-inner" href="<?php echo get_the_permalink($post->ID) ?>">
					<?php echo get_post_thumbnail_monamedia( get_the_post_thumbnail($post->ID, 'full') ); ?>
				</a>
			</div>
			<div class="info">
				<h4>
					<a class="info-tt" href="<?php echo get_the_permalink($post->ID) ?>"><?php echo the_title() ?></a>
				</h4>
				<a class="seeDetail" href="<?php echo get_the_permalink($post->ID) ?>">
					<span class="seeDetail-txt"><?php echo __('Xem chi tiết','monamedia'); ?></span>
					<i class="fa-solid fa-chevron-right">
					</i>
				</a>
			</div>
		</div>
	</div>
</div>
<?php } wp_reset_postdata() ?>
<?php } ?>