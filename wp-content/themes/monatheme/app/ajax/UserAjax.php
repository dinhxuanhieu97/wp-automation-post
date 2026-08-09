<?php
add_action('wp_ajax_nopriv_mona_ajax_register',  'mona_ajax_register'); // no login
function mona_ajax_register()
{
    // $user_phone         = esc_attr( $_POST['user_phone']        );
    $user_email         = esc_attr($_POST['user_email']);
    // $user_first_name    = esc_attr( $_POST['user_first_name']   );
    // $user_last_name     = esc_attr( $_POST['user_last_name']    );
    $user_password      = esc_attr($_POST['user_password']);
    $user_repassword    = esc_attr($_POST['user_repassword']);
    // $user_name = $user_first_name . ' ' . $user_last_name;
    $errors = [];
    // verify
    if (!wp_verify_nonce($_POST['register_nonce_field'], 'register_action')) {
        $errors['mona-error-register'] = __('Hành động không được xác thực!', 'monamedia');
        wp_send_json_error(
            [
                'error'     => $errors,
            ]
        );
        wp_die();
    }
    // if( empty( $user_first_name ) ){
    //     $errors['mona-error-user-first-name'] = __( 'First name is required', 'monamedia' );
    // }
    // if( empty( $user_last_name ) ){
    //     $errors['mona-error-user-last-name'] = __( 'Last name is required', 'monamedia' );
    // }
    if (empty($user_email)) {
        $errors['mona-error-user-email'] = __('Email là bắt buộc', 'monamedia');
    } else if (is_email($user_email) && email_exists($user_email)) {
        $errors['mona-error-user-email'] = __('Email này đã được đăng ký!', 'monamedia');
        wp_send_json_error(
            [
                'error'     => $errors,
            ]
        );
        wp_die();
    } else if (!empty($user_email) && !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $errors['mona-error-user-email'] = __('Định dạng email không đúng!', 'monamedia');
    }
    // if ( empty( $user_phone ) ){
    //     $errors['mona-error-user-phone'] = __( 'Phone number is required', 'monamedia' );
    // }else if ( !mona_validate_phone( $user_phone ) ){
    //     $errors['mona-error-user-phone'] = __( 'Invalid phone number!', 'monamedia' );
    // }elseif( is_numeric( $user_phone ) && mona_validate_phone( $user_phone ) ){
    //     // check user by phone number
    //     global $wpdb;
    //     $tbl_usermeta = $wpdb->prefix.'usermeta';
    //     $user_id = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $tbl_usermeta WHERE meta_key=%s AND meta_value=%s", 'mona_user_phone', $user_phone ) );
    //     $user = get_user_by( 'ID', $user_id );
    //     if ( !empty ( $user ) ) {  
    //         $errors['mona-error-user-phone'] = __( 'This phone number is registered!', 'monamedia' );
    //     }
    // }
    if (strlen($user_password) < 6) {
        $errors['mona-error-user-password'] = __('Mật khẩu phải có ít nhất 6 ký tự!', 'monamedia');
    }
    if ($user_password == '' || $user_password != $user_repassword) {
        $errors['mona-error-user-repassword'] = __('Mật khẩu và mật khẩu xác nhận không khớp!', 'monamedia');
    }
    if (empty($errors)) {
        if (!empty($_POST['user_email'])) {
            $argsEmail = explode('@', $_POST['user_email']);
            if (!empty($argsEmail)) {
                $user_login = $argsEmail[0];
            }
        }
        //$user_login = $user_name;
        $args_regsiter = [
            'user_login'        => $user_login,
            'user_email'        => $user_email,
            'user_pass'         => $user_password,
            // 'first_name'        => $user_first_name,
            // 'last_name'         => $user_last_name,
            'user_nicename'     => $user_login,
            'display_name'      => $user_login,
            'nickname'          => $user_login,
        ];
        $counter = 0;
        while (username_exists($user_login)) {
            // Append a random letter to the original username
            $user_login = $user_login . '_' . wp_rand(1,100);
            
            // Ensure the username is not too long
            if (strlen($user_login) > 60) {
                $user_login = substr($user_login, 0, 60);
            }
        
            $counter++;
        
            // Break the loop after a certain number of attempts (optional)
            if ($counter > 5) {
                $errors['mona-error-register'] =  __('Đã có lỗi xảy ra. Vui lòng liên hệ admin!', 'monamedia');
                wp_send_json_error(['error' => $errors]);
                wp_die();
            }
        }
        $user = wp_insert_user($args_regsiter);
        if (is_wp_error($user)) {
            $errors['mona-error-register'] = $user->get_error_message();
            wp_send_json_error(
                [
                    'error'     => $errors,
                ]
            );
            wp_die();
        }
        // ACF Field
        // update_field( 'mona_user_phone' , $user_phone   , 'user_'.$user );
        // update_user_meta($user, 'billing_email' , $user_email);
        // update_user_meta($user, 'billing_phone' , $user_phone);
        // update_user_meta($user, 'billing_first_name' , $user_first_name);
        // update_user_meta($user, 'billing_last_name' , $user_last_name);
        $message  = __('Xin chào', 'monamedia') . ' '  . $user->data->display_name . ', ' . "\r\n\r\n";
        $message .= __('Bạn đã tạo tài khoản thành công!', 'monamedia')  . "\r\n\r\n" . get_bloginfo('name') . "\r\n\r";
        // $message .= __( 'Vui lòng chờ quản trị viên xác thực tài khoản của bạn', 'monamedia' ) . "\r\n";
        if ($message && !wp_mail([get_bloginfo('admin_email'), $user_email], wp_specialchars_decode(get_bloginfo('name') . ' ' .  __('Tạo tài khoản thành công!', 'monamedia')), $message)) {
            $errors['mona-error-register'] = __('Vui lòng liên hệ với quản trị viên! Đã xảy ra lỗi trong quá trình tạo tài khoản', 'monamedia');
            wp_send_json_error(
                [
                    'error' => $errors,
                ]
            );
            wp_die();
        }
        wp_send_json_success(
            [
                'title'         => __('Message', 'monamedia'),
                'message'       => __('Bạn đã đăng ký tài khoản thành công!', 'monamedia'),
                'redirect'      => !empty($_POST['redirect']) ? $_POST['redirect'] : get_the_permalink(MONA_PAGE_LOGIN),
            ]
        );
        wp_die();
    } else {
        wp_send_json_error(
            [
                'error'     => $errors,
            ]
        );
        wp_die();
    }
}
/** **/
add_action('wp_ajax_nopriv_mona_ajax_login', 'mona_ajax_login');
function mona_ajax_login()
{
    $user_email         = esc_attr($_POST['user_email']);
    $user_password      = esc_attr($_POST['user_password']);
    $errors = [];
    if (!wp_verify_nonce($_POST['login_nonce_field'], 'login_action')) {
        $errors['mona-error-login'] = __('Hành động không được xác thực', 'monamedia');
        wp_send_json_error(
            [
                'error'     => $errors,
            ]
        );
        wp_die();
    }
    if (empty($user_email)) {
        $errors['mona-error-user-email'] = __('Email là bắt buộc', 'monamedia');
    } else if (is_email($user_email) && !email_exists($user_email)) {
        $errors['mona-error-user-email'] = __('Thông tin đăng nhập sai!', 'monamedia');
    } else if (!empty($user_email) && !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $errors['mona-error-user-email'] = __('Định dạng email không đúng!', 'monamedia');
    }
    if (empty($user_password)) {
        $errors['mona-error-user-password'] = __('Mật khẩu là bắt buộc', 'monamedia');
    } else if (strlen($user_password) < 6) {
        $errors['mona-error-user-password'] = __('Mật khẩu phải từ 6 kí tự!', 'monamedia');
    }
    if (is_email($user_email)) {
        $user = get_user_by('email', $user_email);
    }
    // elseif( !is_email( $user_email ) && !empty( $user_email ) ){
    //     global $wpdb;
    //     $tbl_usermeta = $wpdb->prefix . 'usermeta';
    //     $user_id      = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $tbl_usermeta WHERE meta_key=%s AND meta_value=%s", 'mona_user_phone', $user_email));
    //     $user         = get_user_by('ID', $user_id);
    //     if ( empty( $user ) ) {
    //         $user = get_user_by('login', $user_email);
    //     }
    // }
    if (empty($user)) {
        $errors['mona-error-user-login'] = __('Tài khoản không tìm thấy!', 'monamedia');
        wp_send_json_error(
            [
                'error' => $errors,
            ]
        );
        wp_die();
    }
    if (empty($errors)) {
        $args = [
            'user_login' => @$user->user_email,
            'user_password' => @$user_password,
            'remember' => (@$_POST['user_remember'] ? true : false)
        ];
        $on = wp_signon($args);
        if (is_wp_error($on)) {
            $errors['mona-error-user-email'] = __('Thông tin đăng nhập sai!', 'monamedia');
            wp_send_json_error(
                [
                    'error' => $errors
                ]
            );
            wp_die();
        } else {
            $_SESSION['has_destroy'] = 'no';
            wp_send_json_success(
                [
                    'title' => __('Message', 'monamedia'),
                    'message' => __('Đăng nhập thành công!', 'monamedia'),
                    'redirect' => @$_POST['redirect'] ? $_POST['redirect'] : esc_url(home_url('/')),
                ]
            );
            wp_die();
        }
    } else {
        wp_send_json_error(
            [
                'error' => $errors
            ]
        );
        wp_die();
    }
}
// ******************************************* //
add_action('wp_ajax_nopriv_mona_ajax_forgot',  'mona_ajax_forgot'); // no login
function mona_ajax_forgot()
{
    $error = [];
    if (!isset($_POST['forgot_nonce_field']) || !wp_verify_nonce($_POST['forgot_nonce_field'], 'forgot_action')) {
        $error['mona-error-forgot'] = __('Hành động không được xác thực!', 'monamedia');
        wp_send_json_error(
            [
                'error' => $error,
            ]
        );
        wp_die();
    }
    $user_email         = esc_attr($_POST['user_login']);
    if (!empty($user_email) && !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $error['mona-error-user-login'] = __('Định dạng email không đúng!', 'monamedia');
        wp_send_json_error(
            [
                'error' => $error
            ]
        );
        wp_die();
    }
    if (is_email($user_email)) {
        // check user by email
        $user = get_user_by('email', $user_email);
    }
    // elseif (is_numeric($_POST['user_login']) && mona_validate_phone($_POST['user_login'])) {
    //     // check user by phone number
    //     global $wpdb;
    //     $tbl_usermeta = $wpdb->prefix . 'usermeta';
    //     $user_id = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $tbl_usermeta WHERE meta_key=%s AND meta_value=%s", 'mona_user_phone', $_POST['user_login']));
    //     $user = get_user_by('ID', $user_id);
    // } else {
    //     // check user by username
    //     $user = get_user_by('login', $_POST['user_login']);
    // }
    if (!empty($user)) {
        $user_login = $user->user_login;
    } else {
        $error['mona-error-user-login'] = __('Tài khoản không tồn tại!', 'monamedia');
        wp_send_json_error(
            [
                'error' => $error
            ]
        );
        wp_die();
    }
    if (empty($error)) {
        $user_login = $user->user_login;
        $user_email = $user->user_email;
        $result = strstr($user_email, '@', true);
        if (!empty($result)) {
            $countResult = strlen($result);
            $user_display_message = str_replace(
                substr($result, 0, ceil($countResult / 2)),
                '*****',
                $user_email
            );
        } else {
            $user_display_message = $user_email;
        }
        $key = get_password_reset_key($user);
        $message  = __('Xin chào', 'monamedia') . ' ' . $user->data->display_name . ', ' . "\r\n\r\n";
        $message .= __('Bạn đã yêu cầu lấy lại mật khẩu cho tài khoản trên', 'monamedia')  . "\r\n\r\n" . get_bloginfo('name') . "\r\n\r";
        $message .= __('Để lấy lại mật khẩu. Vui lòng nhấp vào liên kết bên dưới:', 'monamedia') . "\r\n";
        if (empty($_POST['redirect'])) {
            $message .= '<' . get_the_permalink(MONA_PAGE_FORGOT) . "?reset&key=$key&login=" . rawurlencode($user_login) . ">\r\n";
        } else {
            $message .= '<' . get_the_permalink(MONA_PAGE_FORGOT) . "?reset&key=$key&login=" . rawurlencode($user_login) . "&redirect=" . $_POST['redirect'] . ">\r\n";
        }
        if ($message && !wp_mail($user_email, wp_specialchars_decode(get_bloginfo('name') . ' Reset password'), $message)) {
            $error['mona-error-forgot'] = __('Không thể gửi email. Vui lòng liên hệ quản trị viên để tìm hiểu thêm', 'monamedia');
            echo wp_send_json_error(
                [
                    'error' => $error
                ]
            );
            wp_die();
        } else {
            wp_send_json_success(
                [
                    'message' => __('Email đổi mật khẩu đã được gửi tới email', 'monamedia') . ' '  . $user_display_message .  ' ' . __('được liên kết với tài khoản của bạn! Vui lòng kiểm tra hộp thư của bạn', 'monamedia')
                ]
            );
            wp_die();
        }
    } else {
        wp_send_json_error(
            [
                'error' => $error
            ]
        );
        wp_die();
    }
}
// ** ** //
add_action('wp_ajax_nopriv_mona_ajax_reset_password',  'mona_ajax_reset_password'); // no login
function mona_ajax_reset_password()
{
    $error              = [];
    if (!isset($_POST['reset_nonce_field']) || !wp_verify_nonce($_POST['reset_nonce_field'], 'reset_action')) {
        $error['mona-error-reset'] = __('Hành động không được xác thực!', 'monamedia');
        wp_send_json_error(
            [
                'error' => $error,
            ]
        );
        wp_die();
    }
    $check = check_password_reset_key(@$_POST['key'], @$_POST['login']);
    if (is_wp_error($check)) {
        $error['mona-error-reset'] = __('Liên kết đặt lại mật khẩu đã hết hạn!', 'monamedia');
        wp_send_json_error(
            [
                'error' => $error,
            ]
        );
        wp_die();
    }
    if (strlen($_POST['new_password']) < 6) {
        $error['mona-error-new-password'] = __('Mật khẩu phải có ít nhất 6 ký tự!', 'monamedia');
        wp_send_json_error(
            [
                'error' => $error
            ]
        );
        wp_die();
    }
    if (@$_POST['new_password'] !== @$_POST['renew_password']) {
        $error['mona-error-renew-password'] = __('Mật khẩu xác nhận không khớp!', 'monamedia');
        wp_send_json_error(
            [
                'error' => $error
            ]
        );
        wp_die();
    }
    $user_data = get_user_by('login', $_POST['login']);
    reset_password($user_data, @$_POST['new_password']);
    if (!isset($_POST['redirect'])) {
        $redirect = get_the_permalink(MONA_PAGE_LOGIN);
    } else {
        $redirect = get_the_permalink(MONA_PAGE_LOGIN) . '?redirect=' . $_POST['redirect'];
    }
    wp_send_json_success(
        [
            'title' => __('Message', 'monamedia'),
            'message' => __('Thay đổi mật khẩu thành công!', 'monamedia'),
            'redirect' => get_the_permalink(MONA_PAGE_LOGIN),
        ]
    );
    wp_die();
}
// ** ** //
add_action('wp_ajax_mona_ajax_update_account',  'mona_ajax_update_account'); // login
function mona_ajax_update_account()
{
    $formdata = array();
    parse_str($_POST['formdata'], $formdata);
    $account = wp_get_current_user();
    $user_first_name    = esc_attr($formdata['user_first_name']);
    $user_last_name     = esc_attr($formdata['user_last_name']);
    $user_bod           = esc_attr($formdata['user_bod']);
    $user_gender        = esc_attr($formdata['user_gender']);
    $user_billing_phone     = esc_attr($formdata['user_phone']);
    // $user_billing_email     = esc_attr($formdata['user_email']);
    $user_billing_address_1 = esc_attr($formdata['user_address']);
    $user_country = esc_attr($formdata['user_country']);
    $user_password        = esc_attr($formdata['user_password']);
    $user_newpassword     = esc_attr($formdata['user_newpassword']);
    $user_renewpassword   = esc_attr($formdata['user_renewpassword']);
    if (isset($_FILES) && !empty($_FILES)) {
        $listFileupload = $_FILES;
        foreach ($listFileupload as $key => $itemFile) {
            $itemFiletype    = $itemFile['type'];
            $itemFilesize    = $itemFile['size'];
            $alow_extensions = array('image/jpeg', 'image/jpg', 'image/png');
            if (!function_exists('wp_handle_upload')) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
            }
            if (!in_array($itemFiletype, $alow_extensions)) {
                $errors['mona-error-update'] = __('Chỉ chấp nhận các định dạng ảnh: jpeg, jpg, png', 'monamedia');
                wp_send_json_error(
                    [
                        'error' => $errors,
                    ]
                );
                wp_die();
            } elseif ($itemFilesize > 5000000) {
                $errors['mona-error-update'] = __('Tệp không được vượt quá 5MB', 'monamedia');
                wp_send_json_error(
                    [
                        'error' => $errors,
                    ]
                );
                wp_die();
            } else {
                $itemFileobject  = wp_handle_upload($itemFile, array('test_form' => false));
                if ($itemFileobject && !isset($itemFileobject['error'])) {
                    $wp_upload_dir = wp_upload_dir();
                    $attachment    = [
                        'guid'           => $wp_upload_dir['url'] . '/' . basename($itemFileobject['file']),
                        'post_mime_type' => $itemFileobject['type'],
                        'post_title'     => preg_replace('/\.[^.]+$/', '', basename($itemFileobject['file'])),
                        'post_content'   => '',
                        'post_status'    => 'inherit'
                    ];
                    $attach_id = wp_insert_attachment($attachment, $itemFileobject['file']);
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $attach_data = wp_generate_attachment_metadata($attach_id, $itemFileobject['file']);
                    wp_update_attachment_metadata($attach_id, $attach_data);
                    // update
                    if ($key == 'mona_user_avatar') {
                        update_field('mona_user_avatar', $attach_id, 'user_' . $account->ID);
                    } elseif ($key == 'mona_user_background') {
                        update_field('mona_user_background', $attach_id, 'user_' . $account->ID);
                    }
                } else {
                    $errors['mona-error-update'] = __('Hình ảnh tải lên không khớp', 'monamedia');
                    wp_send_json_error(
                        [
                            'error' => $errors,
                        ]
                    );
                    wp_die();
                }
            }
        }
    }
    if (!empty($user_password) || !empty($user_newpassword) || !empty($user_renewpassword)) {
        if (!wp_check_password($user_password, $account->user_pass, $account->ID)) {
            $errors['mona-error-user-current-password'] = __('Mật khẩu hiện tại không đúng!', 'monamedia');
        }
        if (strlen($user_newpassword) < 6) {
            $errors['mona-error-user-new-password'] = __('Mật khẩu phải có ít nhất 6 ký tự!', 'monamedia');
        }
        if (@$user_newpassword != @$user_renewpassword) {
            $errors['mona-error-user-renew-password'] = __('Mật khẩu xác nhận chưa khớp!', 'monamedia');
        }
    }
    if (!empty($user_bod)) {
        $bod = DateTime::createFromFormat('d/m/Y', $user_bod);
        if (!$bod) {
            $errors['mona-error-user-bod'] = __('Ngày sinh không hợp lệ!', 'monamedia');
        }
    }
    if (!empty($user_billing_phone) && !preg_match('/^[0-9]{10}+$/', $user_billing_phone)) {

        $errors['mona-error-user-phone'] = __('SĐT phải là số và 10 ký tự', 'monamedia');
    }
    if (empty($errors)) {
        $flagReset = false;
        if (!empty($user_password) && !empty($user_newpassword) && !empty($user_renewpassword)) {
            $userData = get_user_by('login', $account->user_login);
            reset_password($userData, @$user_newpassword);
            $flagReset = true;
        }
        update_user_meta($account->ID, 'mona_user_bod', $user_bod);
        update_user_meta($account->ID, 'mona_user_gender', $user_gender);
        update_user_meta($account->ID, 'mona_user_tel', $user_billing_phone);
        update_user_meta($account->ID, 'mona_user_address', $user_billing_address_1);
        update_user_meta($account->ID, 'mona_user_country', $user_country);
        wp_update_user([
            'ID'            => $account->ID, // this is the ID of the user you want to update.
            'first_name'    => $user_first_name,
            'last_name'     => $user_last_name,
            'display_name' => !empty($user_first_name) ? $user_last_name . ' ' . $user_first_name : $account->user_login,
        ]);
        wp_send_json_success(
            [
                'title'     => __('Thông báo', 'monamedia'),
                'message'   => __('Thay đổi thông tin thành công!', 'monamedia'),
                'redirect'  => $flagReset ? get_the_permalink(MONA_PAGE_LOGIN) : ''
            ]
        );
        wp_die();
    } else {
        wp_send_json_error(
            [
                'error' => $errors,
            ]
        );
        wp_die();
    }
}
// add_action('wp_ajax_nopriv_mona_ajax_update_coupon', 'mona_ajax_update_coupon'); // login
// add_action('wp_ajax_mona_ajax_update_coupon', 'mona_ajax_update_coupon'); // login
// function mona_ajax_update_coupon() {
//     global $woocommerce;
//     $code = esc_attr( $_POST['coupon'] );
//     $coupon     = new WC_Coupon( $code );
//     $discounts  = new WC_Discounts( $woocommerce->cart );
//     $woocommerce->cart->add_discount( $code );
//     wp_send_json_success( 
//         [
//             'title'     => __( 'Message', 'monamedia' ),
//             'message'   => __( "Mã khuyến mãi được thêm thành công", 'monamedia' ),
//             'close'     => __( 'Close', 'monamedia' ),
//         ]
//     );
//     wp_die();
// }
add_action('wp_ajax_mona_ajax_wishlist',  'mona_ajax_wishlist'); // login
function mona_ajax_wishlist()
{
    $productid = intval($_POST['product_id']);
    $act = $_POST['act'];
    if (!is_user_logged_in()) {
        wp_die();
    } else {
        $arrayProductIDs = [];
        $user_meta_key = 'wishlist_user_';
        $user_id = get_current_user_id();
        $wishlist_user = get_user_meta($user_id, $user_meta_key . $user_id, true);
        if (!empty($wishlist_user)) {
            $arrayProductIDs = $wishlist_user;
            if ($act == 'remove') {
                if (is_array($arrayProductIDs) && in_array($productid, $arrayProductIDs) && ($key = array_search($productid, $arrayProductIDs)) !== false) {
                    unset($arrayProductIDs[$key]);
                    delete_user_meta($user_id, $user_meta_key . $user_id);
                    update_user_meta($user_id, $user_meta_key . $user_id, $arrayProductIDs);
                    wp_die();
                }
            } else {
                if (is_array($arrayProductIDs) && !in_array($productid, $arrayProductIDs)) {
                    $arrayProductIDs[] = $productid;
                    delete_user_meta($user_id, $user_meta_key . $user_id);
                    update_user_meta($user_id, $user_meta_key . $user_id, $arrayProductIDs);
                    wp_die();
                }
            }
        } else {
            // array_push($arrayProductIDs, $productid);
            $arrayProductIDs[] = $productid;
            delete_user_meta($user_id, $user_meta_key . $user_id);
            add_user_meta($user_id, $user_meta_key . $user_id, $arrayProductIDs, true);
            wp_die();
        }
    }
}
