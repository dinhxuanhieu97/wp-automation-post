<?php

/**
 * Section name: Sự kiện
 * Description: 
 * Author: Monamedia
 * Order: 0
 */
?>
<?php
$postID = get_the_ID();
?>
<div class="events_item--wrap">
	<div class="events_item--top">
		<div class="events_item--img">
			<?php echo get_the_post_thumbnail('', 'full') ?>
		</div>
		<?php
		$term = get_primary_taxonomy_term('', 'event_cat');
		if (content_exists($term)) {
			$tax = get_term_by('id', $term['id'], 'event_cat');
			$color = get_field('mona_tax_color', $tax);
			$bgcolor = get_field('mona_tax_bgcolor', $tax);
		?>
			<a class="box-dm" <?php echo 'style="background-color:' . $bgcolor . '; color:' . $color . '"' ?> href="<?php echo $term['url'] ?>">
				<?php echo $term['title'] ?>
			</a>
		<?php } ?>
		<?php if (is_user_logged_in()) { ?>
			<?php
			$user_meta_key = 'wishlist_user_';
			$user_id = get_current_user_id();
			$wishlist_user = get_user_meta($user_id, $user_meta_key . $user_id, true);
			$act = in_array($postID, $wishlist_user) ? 'remove' : 'add';
			$checked = in_array($postID, $wishlist_user) ? 'checked' : '';
			$wishlistCheck = isset($args['wishlist_check']) && $args['wishlist_check'] ? 'del' : '';
			?>
			<label class="btn-like monaWishListJS <?php echo $wishlistCheck ?>" data-act="<?php echo $act ?>" data-id="<?php echo $postID; ?>">
				<input type="checkbox" <?php echo $checked ?>>
				<span class="ic">
					<i class="fa-light fa-heart"></i>
				</span>
			</label>
		<?php
		} else {
		?>
			<a class="btn-like ask-login" href="<?php echo get_the_permalink(MONA_PAGE_LOGIN) . '?redirect=' . get_permalink(); ?>">
				<span class="ic">
					<i class="fa-light fa-heart"></i>
				</span>
			</a>
		<?php
		}
		?>
	</div>
	<div class="events_item--bottom">
		<div class="events_item--title">
			<h4 class="title note-text cl-text2 fw-6">
				<a href="<?php echo get_permalink() ?>">
					<?php echo get_the_title() ?>
				</a>
			</h4>
			<div class="content cl-text"><?php echo get_the_excerpt() ?></div>
		</div>
		<?php
		$mon_single_inform = get_field('mon_single_inform');
		if (content_exists($mon_single_inform)) {
		?>
			<div class="events_item--content">
				<ul class="events_item--list">
					<li class="events_item--item tc">
						<p class="title note-sm fw-7"><?php echo __('Tổ chức', 'monamedia') ?></p>
						<p class="content note-sm">
							<?php echo !empty($mon_single_inform['organization']) ? $mon_single_inform['organization'] : __('Đang cập nhật', 'monamedia') ?>
						</p>
					</li>
					<li class="events_item--item">
						<p class="title note-sm fw-7"><?php echo __('Thời gian', 'monamedia') ?></p>
						<p class="content note-sm">
							<?php echo !empty($mon_single_inform['time']) ? $mon_single_inform['time'] : __('Đang cập nhật', 'monamedia') ?>
						</p>
					</li>
					<li class="events_item--item">
						<p class="title note-sm fw-7"><?php echo __('Địa chỉ', 'monamedia') ?></p>
						<p class="content note-sm">
							<?php echo !empty($mon_single_inform['address']) ? $mon_single_inform['address'] : __('Đang cập nhật', 'monamedia') ?>
						</p>
					</li>
					<li class="events_item--item gv">
						<p class="title note-sm fw-7"><?php echo __('Giá vé', 'monamedia') ?></p>
						<p class="content note-sm">
							<?php echo !empty($mon_single_inform['price_ticket']) ? format_number($mon_single_inform['price_ticket']) .  (!empty($mon_single_inform['price_unit']) ? $mon_single_inform['price_unit'] : __(' VND', 'monamedia')) : __('Đang cập nhật', 'monamedia') ?>
						</p>
					</li>
				</ul>
			</div>
		<?php } ?>
	</div>
</div>