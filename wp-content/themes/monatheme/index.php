<?php

/**
 * The template for displaying index.
 *
 * @package MONA.Media / Website
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

get_header();
?>
<main class="main pt">
	<h1 class="hidden-tt">
		<?php
		echo get_the_title(MONA_PAGE_BLOG);
		?>
	</h1>
	<?php
	get_template_part('partials/breadcrumb');
	get_template_part('partials/news/news');
	get_template_part('partials/global/slide-ads');
	get_template_part('partials/news/latest-news', null, ['limit' => 10]);
	get_template_part('partials/global/signup');

	?>
</main>
<?php
get_footer();
