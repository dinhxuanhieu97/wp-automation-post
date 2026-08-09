<?php
add_action('wp_ajax_mona_ajax_load_more',  'mona_ajax_load_more'); // login
add_action('wp_ajax_nopriv_mona_ajax_load_more',  'mona_ajax_load_more'); // no login
function mona_ajax_load_more()
{
    $form = array();
    parse_str($_POST['formdata'], $form);
    $paged              = $_POST['paged'] ? $_POST['paged'] : 1;
    $post_type          = $form['post_type'] ? $form['post_type'] : ['post', 'event'];
    $posts_per_page     = $form['posts_per_page'] ? $form['posts_per_page'] : 12;
    $offset             = ($paged - 1) * $posts_per_page;
    $layout = $form['layout'] ? $form['layout'] : 'reload';
    $btn = '';
    $args = array(
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => $posts_per_page,
        'paged' => $paged,
        'offset' => $offset,
    );
    // $flagPostIn = false;
    if (!empty($form['post__in'])) {
        $post__in = explode(",", $form['post__in']);
        if (is_array($post__in) && !empty($post__in)) {
            $args['post__in'] = $post__in;
            // $flagPostIn = true;
        }
    }
    if (isset($form['s']) && !empty($form['s'])) {
        $args['s'] = esc_attr($form['s']);
    }
    if (isset($form['sort']) && !empty($form['sort'])) {
        $sort = esc_attr($form['sort']);
        switch ($sort) {
            case 'view':
                $args['meta_query']['meta_view'] = [
                    'key' => '_mona_post_view',
                    'value' => 0,
                    'compare' => '>=',
                    'type' => 'numeric',
                ];
                $args['orderby']['meta_view'] = 'desc';
                break;
            case 'asc':
                $args['order'] = 'asc';
                break;
            default:
                $args['order'] = 'desc';
                break;
        }
    } else {
        $args['order'] = 'desc';
    }
    $list_posts = new WP_Query($args);
    $countposts = $list_posts->found_posts;
    ob_start();
    if ($countposts > 0) {
        switch ($post_type) {
            case 'post':
                $class = 'post_care--lf-item';
                break;
            case 'event':
                $class = 'events_new--item d-item d-4';
                break;
            default:
                $class = 'like_item d-item d-3';
                break;
        }
?>
        <?php
        while ($list_posts->have_posts()) {
            $list_posts->the_post();
            switch ($post_type) {
                case 'post':
        ?>
                    <div class="<?php echo $class ?>">
                        <?php
                        get_template_part('partials/loop/box-blog')
                        ?>
                    </div>
                <?php
                    break;
                case 'event':
                ?>
                    <div class="<?php echo $class ?>">
                        <?php
                        get_template_part('partials/loop/box-event')
                        ?>
                    </div>
                <?php
                    break;
                default:
                ?>
                    <div class="<?php echo $class ?>">
                        <?php
                        get_template_part('partials/loop/box-blog', null, ['wishlist_check' => true])
                        ?>
                    </div>
        <?php
                    break;
            }
        }
        wp_reset_postdata();
        ?>
        <?php
    } else {
        echo '<div class="mona-empty-message-large">' .  __('Không tìm thấy bài viết', 'monamedia') . '</div>';
    }
    if ($layout == 'reload') {
        $btn = mona_pagination_links_ajax($list_posts, $paged);
    } else {
        if ($paged < $list_posts->max_num_pages) {
            $btn = '<a class="btn cl-2" id="monaLoadMore" href="" data-paged="' . ++$paged . '">
            <span class="btn-text">' . __('Xem thêm tin tức', 'monamedia') . '</span>
        </a>';
        } else {
            $btn = '';
        }
    }
    wp_send_json_success(
        [
            'html' => ob_get_clean(),
            'btn' => $btn,
            'layout' => $layout,
            'args' => $args,
        ]
    );
    wp_die();
}
add_action('wp_ajax_mona_ajax_tab_load',  'mona_ajax_tab_load'); // login
add_action('wp_ajax_nopriv_mona_ajax_tab_load',  'mona_ajax_tab_load'); // no login
function mona_ajax_tab_load()
{
    $cat              = $_POST['cat'] ? $_POST['cat'] : '';
    $layout              = $_POST['layout'] ? $_POST['layout'] : 'main';
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
    );
    $url = get_permalink(MONA_PAGE_BLOG);
    if (!empty($cat)) {
        $category = get_term_by('id', $cat, 'category');
        $url = get_term_link($category);

        $args['tax_query'] = array(
            array(
                'taxonomy' => 'category',
                'field' => 'term_id',
                'terms' => $cat
            )
        );
    }
    if ($layout == 'home') {
        $args['posts_per_page'] = 5;
    } else {
        $args['posts_per_page'] = 7;
    }
    $list_posts = new WP_Query($args);
    $countposts = $list_posts->found_posts;
    ob_start();
    if ($countposts > 0) {
        $count = 0;
        if ($layout == 'main') {

        ?>
            <div class="news_wrap--content d-wrap">
                <div class="news_lf d-item">
                    <div class="news_lf--wrap">
                        <div class="news_lf--lf">
                            <div class="news_lf--lf-list">
                                <?php
                                while ($list_posts->have_posts()) {

                                    $list_posts->the_post();
                                ?>
                                    <div class="news_lf--lf-item">
                                        <?php
                                        /**
                                         * Section name: Post
                                         * Description: 
                                         * Author: Monamedia
                                         */
                                        get_template_part('partials/loop/box-blog');
                                        ?>
                                    </div>
                                <?php
                                    break;
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                        if ($countposts > 1) {
                        ?>
                            <div class="news_lf--rt">
                                <div class="news_lf--rt-list">
                                    <?php while ($list_posts->have_posts()) {
                                        if ($count > 1) {
                                            break;
                                        }
                                        $list_posts->the_post();

                                    ?>
                                        <div class="news_lf--rt-item">
                                            <?php
                                            /**
                                             * Section name: Post
                                             * Description: 
                                             * Author: Monamedia
                                             */
                                            get_template_part('partials/loop/box-blog');
                                            ?>
                                        </div>
                                    <?php
                                        $count++;
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php
                        } else wp_reset_postdata();
                        ?>
                    </div>
                </div>
                <div class="news_rt d-item">
                    <div class="news_rt--wrap">
                        <div class="news_ct--rt-box">
                            <div class="title-box">
                                <p class="title-mn cl-text2 fw-7">
                                    <?php echo __('Xem nhanh', 'monamedia'); ?>
                                </p>
                                <a class="see-more" href="<?php echo $url ?>">
                                    <?php echo __('Xem tất cả ', 'monamedia'); ?></a>
                            </div>
                            <?php
                            if ($countposts > 3) {
                            ?>
                                <ul class="news_ct--rt-list">
                                    <?php while ($list_posts->have_posts()) {
                                        if ($count > 5) {
                                            break;
                                        }
                                        $list_posts->the_post();

                                    ?>
                                        <li class="news_ct--rt-item">
                                            <?php
                                            /**
                                             * Section name: Post
                                             * Description: 
                                             * Author: Monamedia
                                             */
                                            get_template_part('partials/loop/box-blog');
                                            ?>
                                        </li>
                                    <?php
                                        $count++;
                                    }
                                    wp_reset_postdata(); ?>
                                </ul>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        } elseif ($layout == 'main2') {
        ?>
            <div class="news_wrap--content d-wrap">
                <div class="news_lf d-item">
                    <div class="news_lf--wrap">
                        <div class="news_lf--lf">
                            <div class="news_lf--lf-list">
                                <?php
                                while ($list_posts->have_posts()) {
                                    $list_posts->the_post();
                                ?>
                                    <div class="news_lf--lf-item">
                                        <?php
                                        get_template_part('partials/loop/box-blog')
                                        ?>
                                    </div>
                                <?php
                                    break;
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                        if ($countposts > 1) {
                        ?>
                            <div class="news_lf--rt">
                                <div class="news_lf--rt-list">
                                    <?php
                                    while ($list_posts->have_posts()) {
                                        if ($count > 3) {
                                            break;
                                        }
                                        $list_posts->the_post();
                                    ?>
                                        <div class="news_lf--rt-item">
                                            <?php
                                            get_template_part('partials/loop/box-blog')
                                            ?>
                                        </div>
                                    <?php
                                        $count++;
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php
                        } else wp_reset_postdata();
                        ?>
                    </div>
                </div>
                <div class="news_rt d-item">
                    <div class="news_rt--wrap">
                        <div class="news_ct--rt-box">
                            <div class="title-box">
                                <p class="title-mn cl-text2 fw-7"><?php echo __('Gợi ý bài viết', 'monamedia'); ?></p>
                            </div>
                            <?php
                            if ($countposts > 4) {
                            ?>
                                <ul class="news_ct--rt-list">
                                    <?php
                                    while ($list_posts->have_posts()) {
                                        if ($count > 5) {
                                            break;
                                        }
                                        $list_posts->the_post();
                                    ?>
                                        <li class="news_ct--rt-item">
                                            <?php
                                            get_template_part('partials/loop/box-blog')
                                            ?>
                                        </li>
                                    <?php
                                        $count++;
                                    }
                                    wp_reset_postdata()
                                    ?>
                                </ul>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        } else {
        ?>
            <div class="news_wrap--content d-wrap">
                <div class="news_lf d-item">
                    <div class="news_lf--wrap">
                        <div class="news_lf--lf">
                            <div class="news_lf--lf-list">
                                <?php
                                while ($list_posts->have_posts()) {
                                    $list_posts->the_post();
                                ?>
                                    <div class="news_lf--lf-item">
                                        <?php
                                        get_template_part('partials/loop/box-blog')
                                        ?>
                                    </div>
                                <?php
                                    break;
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                        if ($countposts > 1) {
                        ?>
                            <div class="news_lf--rt">
                                <div class="news_lf--rt-list">
                                    <?php
                                    while ($list_posts->have_posts()) {
                                        if ($count > 3) {
                                            break;
                                        }
                                        $list_posts->the_post();
                                    ?>
                                        <div class="news_lf--rt-item">
                                            <?php
                                            get_template_part('partials/loop/box-blog')
                                            ?>
                                        </div>
                                    <?php
                                        $count++;
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php
                        } else wp_reset_postdata();
                        ?>
                    </div>
                </div>
                <div class="news_rt d-item">
                    <div class="news_rt--wrap">
                        <div class="news_ct--rt-box">
                            <div class="title-box">
                                <p class="title-mn cl-text2 fw-7"><?php echo __('Gợi ý bài viết', 'monamedia'); ?></p>
                            </div>
                            <?php
                            if ($countposts > 5) {
                            ?>
                                <ul class="news_ct--rt-list">
                                    <?php
                                    while ($list_posts->have_posts()) {
                                        if ($count > 7) {
                                            break;
                                        }
                                        $list_posts->the_post();
                                    ?>
                                        <li class="news_ct--rt-item">
                                            <?php
                                            get_template_part('partials/loop/box-blog')
                                            ?>
                                        </li>
                                    <?php
                                        $count++;
                                    }
                                    wp_reset_postdata()
                                    ?>
                                </ul>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }
    } else {
        ?>
        <div class="mona-empty-message-large">
            <p><?php echo __('Không tìm thấy bài viết!', 'monamedia') ?></p>
        </div>
<?php
    }
    wp_send_json_success(
        [
            'html' => ob_get_clean(),
        ]
    );
    wp_die();
}
