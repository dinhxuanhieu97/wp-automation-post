<?php

/**
 * The template for displaying taxonomy.
 *
 * @package MONA.Media / Website
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

$obz = get_queried_object();
$tax = $obz->taxonomy;
$postt = get_taxonomy($tax)->object_type;
get_header();
?>
<main class="main pt">
	<?php
	get_template_part('partials/breadcrumb')
	?>
	<h1 class="hidden-tt">
		<?php
		echo $obz->name
		?>
	</h1>
	<?php
	$post_type = $postt;
	$posts_per_page = 12;
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	$offset = ($paged - 1) * $posts_per_page;
	$args = [
		'post_type' => $post_type,
		'posts_per_page' => $posts_per_page,
		'post_status'    => 'publish',
		'order' => 'DESC',
		'paged' => $paged,
		'offset' => $offset,
		'tax_query' => [
			'relation' => 'AND',
			[
				'taxonomy' => $tax,
				'field' => 'slug',
				'terms' => $obz->slug
			]
		]
	];
	$list_posts = new WP_Query($args);
	$max_num_pages = $list_posts->max_num_pages;
	$countposts = $list_posts->found_posts;

	?>
	<section class="events_new">
		<div class="container">
			<div class="events_new--wrap">
				<div class="title-box">
					<h2 class="title-sm2 fw-7 cl-text2 title-main">
						<?php echo $obz->name
						?>
					</h2>
				</div>
				<div class="events_new--box">
					<div class="events_new--list d-wrap  newsn-list home_more--list">
						<?php
						if ($countposts > 0) {
						?>
							<?php
							while ($list_posts->have_posts()) {
								$list_posts->the_post();
							?>
								<div class="events_new--item d-item d-4">
									<?php
									get_template_part('partials/loop/box-blog');
									?>
								</div>
							<?php
							}
							wp_reset_postdata();
							?>
						<?php
						} else {

						?>
							<div class="mona-empty-message-large">
								<p><?php echo __('Không tìm thấy bài viết!', 'monamedia') ?></p>
							</div>
						<?php
						}
						?>
					</div>
					<div class="pagination-ct">
						<?php
						mona_pagination_links($list_posts);
						?>
					</div>
				</div>
			</div>
		</div>
	</section>
	<?php
	get_template_part('partials/global/signup');

	?>

</main>
<?php get_footer();
