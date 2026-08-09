<?php

/**
 * Template name: Quên mật khẩu
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
while (have_posts()) :
    the_post();
?>
    <main class="main pt-2">
        <section class="sign">
            <div class="container">
                <?php
                if (isset($_GET['redirect'])) {
                    $redirect = @$_GET['redirect'];
                } else {
                    $redirect = '';
                }
                ?>

                <?php if (!isset($_GET['reset'])) { ?>
                    <div class="sign_wrap d-wrap">
                        <div class="sign_lf d-item d-2">
                            <div class="sign_lf--wrap">
                                <div class="sign_title">
                                    <h1 class="title-lg cl-text2 t-up fw-7"><?php echo __('Quên mật khẩu', 'monamedia'); ?></h1>
                                </div>
                                <div class="sign_form">
                                    <form id="formForgot" class="is-loading-group">
                                        <input type="hidden" name="redirect" value="<?php echo $redirect; ?>">

                                        <div class="sign_form--wrap">
                                            <div class="sign_form--list">
                                                <div class="sign_form--item">
                                                    <div class="form-ip">
                                                        <div class="form-ip-label">
                                                            <label for="email"><?php echo __('Email', 'monamedia'); ?></label>
                                                        </div>
                                                        <div class="form-ip-ip">
                                                            <input id="email" name="user_login" class="monaField" type="text" placeholder="<?php echo  __('Email của bạn', 'monamedia') ?>">
                                                            <div class="mona-error mona-error-user-login"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mona-error-primary mona-error-forgot"></div>
                                            <?php wp_nonce_field('forgot_action', 'forgot_nonce_field'); ?>
                                            <div class="monaReturnMessage monaReturnMessageForgot"></div>

                                            <div class="sign_btn">
                                                <button class="btn cl-2" type="submit"> <span class="btn-text"><?php echo __('Xác nhận', 'monamedia'); ?></span>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="sign_rt d-item d-2">
                            <div class="sign_rt--wrap">
                                <div class="sign_rt--img">
                                    <?php echo wp_get_attachment_image(get_field('mona_login_img', MONA_PAGE_LOGIN), 'full') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                } else { ?>
                    <div class="sign_wrap d-wrap">
                        <div class="sign_lf d-item d-2">
                            <div class="sign_lf--wrap">
                                <div class="sign_title">
                                    <h1 class="title-lg cl-text2 t-up fw-7"><?php echo __('Quên mật khẩu', 'monamedia'); ?></h1>
                                </div>
                                <div class="sign_form">
                                    <form id="formReset" class="is-loading-group">
                                        <input type="hidden" name="key" value="<?php echo esc_attr($_GET['key']); ?>" />
                                        <input type="hidden" name="login" value="<?php echo esc_attr($_GET['login']); ?>" />
                                        <input type="hidden" name="redirect" value="<?php echo $redirect; ?>">
                                        <div class="sign_form--wrap">
                                            <div class="sign_form--list">
                                                <div class="sign_form--item">
                                                    <div class="form-ip">
                                                        <div class="form-ip-label">
                                                            <label for="pass"><?php echo __('Tạo mật khẩu mới', 'monamedia'); ?></label>
                                                        </div>
                                                        <div class="form-ip-ip pass">
                                                            <input class="password monaField" name="new_password" id="pass" type="password" placeholder="<?php echo  __('Tạo mật khẩu mới của bạn', 'monamedia') ?>"><span class="form-ip-ic hidden-pass"><i class="fal fa-eye-slash"></i></span><span class="form-ip-ic show-pass"><i class="fal fa-eye"></i></span>
                                                            <div class="mona-error mona-error-new-password"></div>

                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="sign_form--item">
                                                    <div class="form-ip">
                                                        <div class="form-ip-label">
                                                            <label for="repass"><?php echo __('Xác minh mật khẩu', 'monamedia'); ?></label>
                                                        </div>
                                                        <div class="form-ip-ip pass">
                                                            <input class="password monaField" id="repass" type="password" name="renew_password" placeholder="<?php echo  __('Xác minh mật khẩu mới của bạn', 'monamedia') ?>"><span class="form-ip-ic hidden-pass"><i class="fal fa-eye-slash"></i></span><span class="form-ip-ic show-pass"><i class="fal fa-eye"> </i></span>
                                                            <div class="mona-error mona-error-renew-password"></div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mona-error-primary mona-error-reset"></div>
                                            <?php wp_nonce_field('reset_action', 'reset_nonce_field'); ?>
                                            <div class="monaReturnMessage monaReturnMessageReset"></div>
                                            <div class="sign_btn">
                                                <button class="btn cl-2" type="submit"> <span class="btn-text"><?php echo __('Xác nhận', 'monamedia'); ?></span>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="sign_rt d-item d-2">
                            <div class="sign_rt--wrap">
                                <div class="sign_rt--img">
                                    <?php echo wp_get_attachment_image(get_field('mona_login_img', MONA_PAGE_LOGIN), 'full') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </section>
        <?php
        get_template_part('partials/global/signup');

        ?>
    </main>
<?php
endwhile;
get_footer();
