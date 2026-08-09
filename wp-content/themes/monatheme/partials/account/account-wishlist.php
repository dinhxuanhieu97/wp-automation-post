<?php
$user_id = $args['current_userid'];
?>
<form class="formLoadAjax is-loading-btn">
    <?php
    $postss = 12;
    $postt = ['post', 'event']
    ?>
    <input type="hidden" name="post_type" value="">
    <input type="hidden" name="posts_per_page" value="<?php echo $postss ?>">
    <div class="like">
        <div class="like_wrap">
            <div class="like_title">
                <div class="like_title--search">
                    <div class="header-search-ip">
                        <input type="search" name="s" placeholder="Tìm kiếm">
                        <button class="btn-sub" type="submit"> <img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/SearchBtn.svg" alt="" />
                        </button>
                    </div>
                </div>
                <div class="like_title--filter">
                    <select class="like-filter onChangePostAjax" name="sort">
                        <option value=""><?php echo __('Sắp xếp theo', 'monamedia'); ?></option>
                        <option value="desc"><?php echo __('Mới nhất', 'monamedia'); ?></option>
                        <option value="asc"><?php echo __('Cũ nhất', 'monamedia'); ?></option>
                    </select>
                </div>
            </div>
            <?php
            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
            $args = array(
                'post_type' => ['post', 'event'],
                'post_status' => 'publish',
                'posts_per_page' => $postss,
                'order' => 'DESC',
                'paged' => $paged,
                'offset' => ($paged - 1) * $postss,
            );
            $user_meta_key = 'wishlist_user_';
            $wishlist_user = get_user_meta($user_id, $user_meta_key . $user_id, true);
            if (is_array($wishlist_user) && !empty($wishlist_user)) {
                $args['post__in'] = $wishlist_user;
            } else {
                $args['post__in'] = [0];
            }
            ?>
            <input type="hidden" name="post__in" value="<?php echo implode(',', $args['post__in']);
                                                        ?>" />
            <?php
            $list_posts = new WP_Query($args);
            $countposts = $list_posts->found_posts;
            ?>
            <div class="like_content">
                <div class="like_list d-wrap post-list">
                    <?php
                    if ($countposts > 0) {
                        while ($list_posts->have_posts()) {
                            $list_posts->the_post();
                    ?>
                            <div class="like_item d-item d-3">
                                <?php
                                get_template_part('partials/loop/box-blog', null, ['wishlist_check' => true])
                                ?>
                            </div>
                    <?php }
                        wp_reset_postdata();
                    } else {
                        echo '<div class="mona-empty-message-large">' .  __('Không tìm thấy bài viết', 'monamedia') . '</div>';
                    }
                    ?>
                </div>
                <div class="pagination-posts-ajax">
                    <?php
                    mona_pagination_links($list_posts);
                    ?>
                </div>
            </div>
        </div>
    </div>
</form>