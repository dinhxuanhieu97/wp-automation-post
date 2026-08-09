<?php
/**
 * Section name: Từ khóa
 * Description: 
 * Author: Monamedia
 * Order: 0
 */

?>
<div class="tags">
	<div class="tags-inner">
		<p class="t24 fw-6 c-black f-title"><?php echo __('Chủ đề', 'monamedia') ?></p>
		<div class="tags-list">
			<?php
			$current_post_id = get_the_ID();
			$post_tags = wp_get_post_tags($current_post_id);
			// $tags = get_tags();
			if ($post_tags) {
				foreach ($post_tags as $tag) {
						echo '<a class="tags-item" href="' . get_tag_link($tag->term_id) . '">' . esc_html($tag->name) . '</a>';
				}
			} else { ?>
			<li class="news_ct--rt-center-item">
				<?php echo __('Không có từ khóa liên quan.', 'monamedia') ?></li>;
			<?php }
			?>
		</div>
	</div>
</div>