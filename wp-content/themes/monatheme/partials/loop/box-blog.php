<?php

/**
 * Section name: Tin tức
 * Description: 
 * Author: Monamedia
 * Order: 0
 */
?>
<?php
$postID = get_the_ID();
?>
<div class="like_item--wrap">
	<div class="like_item--top">
		<a class="like_item--img" href="<?php echo get_the_permalink() ?>">
			<?php echo get_the_post_thumbnail('', 'full'); ?>
		</a>
		<?php
		$term = get_primary_taxonomy_term('');
		if (content_exists($term)) {
			$tax = get_term_by('id', $term['id'], 'category');
			$color = get_field('mona_tax_color', $tax);
			$bgcolor = get_field('mona_tax_bgcolor', $tax);
		?>
			<a class="box-dm" <?php echo  'style="background-color: #b2e08b; color:#3fad35"' ?> href="<?php echo $term['url'] ?>">
				<?php echo $term['title'] ?>
			</a>
		<?php } ?>
	</div>
	<div class="like_item--bottom">
		<div class="like_item--info">
			<div class="box-info">
				<div class="box-info-author">
					<a class="note-sm fw-7" href="javascript:;">
						<?php echo get_the_author(); ?>
					</a>
				</div>
				<div class="box-info-date">
					<p class="note-sm cl-gray">
						<?php echo get_the_time('m/Y'); ?>
					</p>
				</div>
			</div>
			<!-- ?php if (is_user_logged_in()) { ?>
				?php
				$user_meta_key = 'wishlist_user_';
				$user_id = get_current_user_id();
				$wishlist_user = get_user_meta($user_id, $user_meta_key . $user_id, true);
				$act = in_array($postID, $wishlist_user) ? 'remove' : 'add';
				$checked = in_array($postID, $wishlist_user) ? 'checked' : '';
				$wishlistCheck = isset($args['wishlist_check']) && $args['wishlist_check'] ? 'del' : '';
				?>
				<label class="btn-like monaWishListJS ?php echo $wishlistCheck ?>" data-act="?php echo $act ?>" data-id="?php echo $postID ?>">
					<input type="checkbox" ?php echo $checked ?>>
					<span class="ic">
						<i class="fa-light fa-heart"></i>
					</span>
				</label>
			?php
			} else {
			?> -->
				<!-- <a class="btn-like ask-login" href="?php echo get_the_permalink(MONA_PAGE_LOGIN) . '?redirect=' . get_permalink(); ?>">
					<span class="ic">
						<i class="fa-light fa-heart"></i>
					</span>
				</a> -->
			<!-- ?php
			}
			?> -->
		</div>
		<div class="like_item--des">
			<h4 class="note-text cl-text2 fw-7 title">
				<a href="<?php echo get_the_permalink() ?>">
					<?php the_title(); ?>
				</a>
			</h4>
			<div class="note-text content">
				<?php echo get_the_excerpt(); ?>
			</div>
		</div>
	</div>
</div>