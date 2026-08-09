<?php

/**
 * Template name: Đăng ký
 * @author : MONA.Media / Website
 */
if (is_user_logged_in()) {
    $url = esc_url(@$_GET['redirect']);
    if ($url == '') {
        $url = get_the_permalink(MONA_PAGE_HOME);
    }
    wp_redirect($url);
}
get_header();
// wp_localize_script(
//     'mona-frontend',
//     'param',
//     array(
//         'rule' =>  __('Vui lòng chấp nhận điều khoản', 'monamedia'),
//     )
// );
while (have_posts()) :
    the_post();
?>
    <main class="main pt-2">
        <section class="sign">
            <div class="container">
                <div class="sign_wrap d-wrap">
                    <div class="sign_lf d-item d-2">
                        <div class="sign_lf--wrap">
                            <div class="sign_title">
                                <h1 class="title-lg cl-text2 t-up fw-7"><?php echo __('Đăng ký', 'monamedia'); ?></h1>
                                <div class="note-md cl-gray5 mona-content">
                                    <?php
                                    the_content()
                                    ?>
                                </div>
                            </div>
                            <div class="sign_form">
                                <?php
                                if (isset($_GET['redirect'])) {
                                    $redirect = @$_GET['redirect'];
                                } else {
                                    $redirect = '';
                                } ?>
                                <form id="formRegister" class="is-loading-btn">
                                    <input type="hidden" name="redirect" value="<?php echo $redirect; ?>">
                                    <div class="sign_form--wrap">
                                        <div class="sign_form--list">
                                            <div class="sign_form--item">
                                                <div class="form-ip">
                                                    <div class="form-ip-label">
                                                        <label for="email"><?php echo __('Email', 'monamedia'); ?></label>
                                                    </div>
                                                    <div class="form-ip-ip">
                                                        <input id="email" name="user_email" class="monaField" type="text" placeholder="<?php echo  __('Email của bạn', 'monamedia') ?>">
                                                        <div class="mona-error mona-error-user-email"></div>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="sign_form--item">
                                                <div class="form-ip">
                                                    <div class="form-ip-label">
                                                        <label for="pass"><?php echo __('Tạo mật khẩu', 'monamedia'); ?></label>
                                                    </div>
                                                    <div class="form-ip-ip pass">
                                                        <input class="password monaField" name="user_password" id="pass" type="password" placeholder="<?php echo  __('Tạo mật khẩu mới của bạn', 'monamedia') ?>"><span class="form-ip-ic hidden-pass"><i class="fal fa-eye-slash"></i></span><span class="form-ip-ic show-pass"><i class="fal fa-eye"></i></span>
                                                        <div class="mona-error mona-error-user-password"></div>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="sign_form--item">
                                                <div class="form-ip">
                                                    <div class="form-ip-label">
                                                        <label for="repass"><?php echo __('Xác minh mật khẩu', 'monamedia'); ?></label>
                                                    </div>
                                                    <div class="form-ip-ip pass">
                                                        <input class="password monaField" id="repass" type="password" name="user_repassword" placeholder="<?php echo  __('Xác minh mật khẩu mới của bạn', 'monamedia') ?>"><span class="form-ip-ic hidden-pass"><i class="fal fa-eye-slash"></i></span><span class="form-ip-ic show-pass"><i class="fal fa-eye"> </i></span>
                                                        <div class="mona-error mona-error-user-repassword"></div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mona-error-primary mona-error-register"></div>
                                        <?php wp_nonce_field('register_action', 'register_nonce_field'); ?>
                                        <div class="sign_control">
                                            <div class="sign_cs">
                                                <label class="sign_cs--label">
                                                    <input type="checkbox" value="1" name="rule_check"><span class="box"></span>
                                                    <div class="note-sm cl-text2 mona-content">
                                                        <?php echo get_field('mona_register_rule') ?></div>
                                                </label>
                                                <div class="mona-error mona-error-rule-check"></div>

                                            </div>
                                        </div>
                                        <div class="sign_btn">
                                            <button class="btn cl-2" type="submit"> <span class="btn-text"><?php echo __('Đăng ký', 'monamedia'); ?></span>
                                            </button>
                                            <div class="sign_order">
                                                <div class="sign_order--title">
                                                    <p class="note-text"><?php echo __('hoặc', 'monamedia'); ?></p>
                                                </div>
                                                <ul class="sign_order--list">
                                                    <li class="sign_order--item">
                                                        <a class="btn trans" href="javascript:;">
                                                            <span class="btn-ic">
                                                                <img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/gg.svg" alt="" />
                                                            </span>
                                                            <span class="btn-text"><?php echo __('Đăng ký với Google', 'monamedia'); ?></span>
                                                        </a>
                                                    </li>
                                                    <li class="sign_order--item">
                                                        <a class="btn trans" href="<?php echo MONA_SITE_URL ?>/wp-login.php?loginSocial=facebook" data-plugin="nsl" data-action="connect" data-redirect="current" data-provider="facebook" data-popupwidth="600" data-popupheight="679">
                                                            <span class="btn-ic">
                                                                <img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/fb.svg" alt="" />
                                                            </span>
                                                            <span class="btn-text"><?php echo __('Đăng ký với Facebook', 'monamedia'); ?></span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="sign_ok">
                                            <p class="note-text cl-gray5"> <?php echo __('Bạn đã tài khoản?', 'monamedia'); ?>
                                                <a class="fw-7 cl-text2" href="<?php echo get_permalink(MONA_PAGE_LOGIN) ?>"> <?php echo __('Đăng nhập ngay', 'monamedia'); ?> </a>
                                            </p>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="sign_rt d-item d-2">
                        <div class="sign_rt--wrap">
                            <div class="sign_rt--img">
                                <?php echo wp_get_attachment_image(get_field('mona_register_img'), 'full') ?>
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
