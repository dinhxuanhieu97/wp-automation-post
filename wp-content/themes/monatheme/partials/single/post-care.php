<?php
if (is_singular('post')) {
    $box = get_field('mona_post_details', MONA_PAGE_BLOG);
    $postt = 'post';
} elseif (is_singular('event')) {
    $postt = 'event';
    $box = get_field('mona_post_details', MONA_PAGE_EVENT);
}
?>
<section class="post_care">
    <div class="container">
        <div class="post_care--box">
            <div class="post_care--wrap d-wrap">
                <div class="post_care--lf d-item">
                    <div class="post_care--lf-wrap">
                        <div class="title-box">
                            <h2 class="title-sm2 fw-7 cl-text2 title-main"><?php echo $box['tt_2'] ?></h2>
                        </div>
                        <?php
                        $posts_per_page = 5;
                        $args = array(
                            'post_type' => $postt,
                            'post_status' => 'publish',
                            'posts_per_page' => $posts_per_page,

                        );
                        if (!empty($box['box_2'])) {
                            $args['post__in'] = $box['box_2'];
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
                            <div class="post_care--lf-list">
                                <?php
                                while ($list_posts->have_posts()) {
                                    $list_posts->the_post();
                                ?>
                                    <div class="post_care--lf-item">
                                        <?php
                                        get_template_part('partials/loop/box-blog')
                                        ?>
                                    </div>
                                <?php }
                                wp_reset_postdata()
                                ?>
                            </div>

                        <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
                if (!empty($box['box_3'])) {

                    $posts_per_page = 4;
                    $args = array(
                        'post_type' => $postt,
                        'post_status' => 'publish',
                        'posts_per_page' => $posts_per_page,

                    );
                    $args['post__in'] = $box['box_3'];
                    $args['orderby'] = 'post__in';

                    $list_posts = new WP_Query($args);
                    $countposts = $list_posts->found_posts;
                    if ($countposts > 0) {
                ?>
                        <div class="post_care--rt d-item">
                            <div class="post_care--rt-wrap">
                                <div class="news_ct--rt-box">
                                    <div class="title-box">
                                        <h2 class="title-mn cl-text2 fw-7"><?php echo $box['tt_3'] ?></h2>
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
                }
                ?>
            </div>
        </div>
    </div>
</section>