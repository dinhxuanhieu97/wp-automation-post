<?php

/**
 * Template name: Tài khoản
 * @author : MONA.Media / Website
 */
if (!is_user_logged_in()) {
    $url = esc_url(@$_GET['redirect']);
    if ($url == '') {
        $url = get_the_permalink(MONA_PAGE_LOGIN);
    }
    wp_redirect($url);
}
$current_userid = get_current_user_id();
$current_userdata = get_userdata($current_userid);
$tab = isset($_GET['tab']) && !empty($_GET['tab']) ? esc_attr($_GET['tab']) : '';
$first = $current_userdata->first_name;
$last = $current_userdata->last_name;
$username = $current_userdata->user_login;
$display_name = empty($first) ? $username : $last . ' ' . $first;
get_header();
while (have_posts()) :
    the_post();
?>
    <main class="main pt-2">
        <?php
        get_template_part('partials/breadcrumb')
        ?>
        <section class="admin">
            <div class="container">
                <div class="admin_content">

                    <div class="admin_wrap d-wrap is-loading-btn">
                        <div class="admin_lf d-item">
                            <div class="admin_lf--wrap">
                                <div class="admin_lf--content">
                                    <div class="admin_info">
                                        <div class="admin_info--ava <?php echo $tab == 'favorite' ? 'no-change' : '' ?> btn-upload">
                                            <input type="file" name="avatar-upload" id="fileUpload" accept="image/png, image/jpeg, image/jpg">
                                            <?php echo get_avatar($current_userid, 96, '', '', [
                                                'class' => 'img-src'
                                            ]) ?>
                                        </div>
                                        <div class="admin_info--if">
                                            <p class="note-lg fw-7 cl-text2"><?php echo $display_name ?></p>
                                            <p class="note-sm cl-gray5"><?php echo $current_userdata->user_email; ?></p>
                                        </div>
                                    </div>
                                    <ul class="admin_list">
                                        <li class="admin_item <?php if (empty($tab) || $tab == 'info') echo 'actived' ?>">
                                            <a class="admin_link" href="?tab=info"><span class="admin_ic"> <img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/adic1.svg" alt="" /></span><span class="note-text cl-gray5">Thông tin tài khoản</span></a>
                                        </li>
                                        <li class="admin_item <?php echo $tab == 'favorite' ? 'actived' : '' ?>">
                                            <a class="admin_link" href="?tab=favorite"><span class="admin_ic"> <img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/adic2.svg" alt="" /></span><span class="note-text cl-gray5">Bài viết yêu thích </span></a>
                                        </li>
                                    </ul>
                                    <div class="admin_out">
                                        <a class="admin_link out" href="<?php echo wp_logout_url(home_url()); ?>"><span class="admin_ic"> <img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/out.svg" alt="" /></span><span class="note-text cl-gray3">Đăng xuất</span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="admin_rt d-item">
                            <div class="admin_rt--wrap">
                                <?php
                                if ($tab == 'password') {

                                    get_template_part('partials/account/account', 'password', ['tab' => $tab, 'current_userid' => $current_userid, 'current_userdata' => $current_userdata]);
                                } elseif ($tab == 'link-account') {
                                    get_template_part('partials/account/account', 'link-account', ['tab' => $tab, 'current_userid' => $current_userid]);
                                } elseif ($tab == 'favorite') {
                                    get_template_part('partials/account/account', 'wishlist', ['current_userid' => $current_userid]);
                                } else {
                                    get_template_part('partials/account/account', 'info', ['tab' => $tab, 'current_userid' => $current_userid, 'current_userdata' => $current_userdata]);
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
        get_template_part('partials/global/signup');

        ?>
    </main>
<?php
endwhile;
get_footer();
