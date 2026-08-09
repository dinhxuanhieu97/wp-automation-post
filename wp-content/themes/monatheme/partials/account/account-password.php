<?php
get_template_part('partials/account/account-header', null, ['tab' => $args['tab']]);
?>
<div class="change_pass">
    <form id="formUser">
        <div class="change_pass--wrap">
            <div class="sign_form--list">
                <div class="sign_form--item monaFieldItem">
                    <div class="form-ip">
                        <div class="form-ip-label">
                            <label for="old-pass"><?php echo __('Mật khẩu hiện tại', 'monamedia'); ?></label>
                        </div>
                        <div class="form-ip-ip pass">
                            <input class="password" id="old-pass" name="user_password" type="password" placeholder="<?php echo  __('Nhập mật khẩu hiện tại của bạn', 'monamedia') ?>"><span class="form-ip-ic hidden-pass"><i class="fal fa-eye-slash"></i></span><span class="form-ip-ic show-pass"><i class="fal fa-eye"></i></span>
                            <div class="mona-error mona-error-user-current-password"></div>
                        </div>
                    </div>
                </div>
                <div class="sign_form--item monaFieldItem">
                    <div class="form-ip">
                        <div class="form-ip-label">
                            <label for="pass"><?php echo __('Mật khẩu mới', 'monamedia'); ?></label>
                        </div>
                        <div class="form-ip-ip pass">
                            <input class="password" id="pass" name="user_newpassword" type="password" placeholder="<?php echo  __('Tạo mật khẩu mới của bạn', 'monamedia') ?>"><span class="form-ip-ic hidden-pass"><i class="fal fa-eye-slash"></i></span><span class="form-ip-ic show-pass"><i class="fal fa-eye"></i></span>
                            <div class="mona-error mona-error-user-new-password"></div>

                        </div>
                    </div>
                </div>
                <div class="sign_form--item monaFieldItem">
                    <div class="form-ip">
                        <div class="form-ip-label">
                            <label for="repass"><?php echo __('Xác minh mật khẩu', 'monamedia'); ?></label>
                        </div>
                        <div class="form-ip-ip pass">
                            <input class="password" id="repass" name="user_renewpassword" type="password" placeholder="<?php echo  __('Xác minh mật khẩu của bạn', 'monamedia') ?>"><span class="form-ip-ic hidden-pass"><i class="fal fa-eye-slash"></i></span><span class="form-ip-ic show-pass"><i class="fal fa-eye"> </i></span>
                            <div class="mona-error mona-error-user-renew-password"></div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="info_btn">
                <button class="btn cl-2 deactive" type="submit"> <span class="btn-text"><?php echo __('Lưu thay đổi', 'monamedia'); ?></span>
                </button>
                <button class="btn trans" id="clearBtn"> <span class="btn-text"><?php echo __('Hủy bỏ', 'monamedia'); ?></span>
                </button>
            </div>
        </div>
    </form>
</div>