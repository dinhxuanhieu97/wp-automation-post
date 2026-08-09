<?php
if (is_singular('post')) {
    $box = get_field('mona_post_details', MONA_PAGE_BLOG);
    $postt = 'post';
} elseif (is_singular('event')) {
    $postt = 'event';
    $box = get_field('mona_post_details', MONA_PAGE_EVENT);
}
$posts_per_page = 4;
$args = array(
    'post_type' => $postt,
    'post_status' => 'publish',
    'posts_per_page' => $posts_per_page,


);

if (!empty($box['box'])) {
    $args['post__in'] = $box['box'];
    $args['orderby'] = 'post__in';
} else {
    $args['meta_query'] = [
        'relation' => 'OR',
        array(
            'key' => '_mona_post_view',
            'compare' => 'NOT EXISTS'
        ),
        array(
            'key' => '_mona_post_view',
            'compare' => 'EXISTS'
        ),
    ];
    $args['orderby'] = 'meta_value_num date';
    $args['order'] = 'DESC';
}
$list_posts = new WP_Query($args);
$countposts = $list_posts->found_posts;
if ($countposts > 0) {
?>
    <div class="news_ct--rt d-item">
        <div class="news_ct--rt-wrap">
            <div class="news_ct--rt-box">
                <div class="title-box">
                    <p class="title-mn cl-text2 fw-7"><?php echo $box['tt'] ?></p>
                </div>
                <ul class="news_ct--rt-list">
                    <?php
                    while ($list_posts->have_posts()) {
                        $list_posts->the_post();
                    ?>
                        <li class="news_ct--rt-item">
                            <?php
                            get_template_part('partials/loop/box-blog')
                            ?>
                        </li>
                    <?php }
                    wp_reset_postdata()
                    ?>
                </ul>
            </div>
        </div>
    </div>
<?php
}
?>