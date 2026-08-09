<div class="header-login">
    <div class="header-btn">
        <a class="btn" href="<?php echo get_permalink(MONA_PAGE_LOGIN) ?>">
            <span class="btn-text"><?php echo __('Đăng nhập', 'monamedia'); ?></span>
        </a>
    </div>
    <?php
    $user = (is_user_logged_in()) ? wp_get_current_user() : false;
    if ($user) {
        $first = $user->first_name;
        $last = $user->last_name;
        $username = $user->user_login;
        $display_name = empty($first) ? $username : $last . ' ' . $first;

    ?>
        <div class="header-user">
            <div class="admin_info">
                <div class="admin_info--ava">
                    <?php echo get_avatar($user->ID, 96, '', '') ?>
                </div>
                <div class="admin_info--if">
                    <p class="note-text fw-7"><?php echo $display_name ?></p>
                    <p class="note-sm cl-gray5"><?php echo $user->user_email ?></p>
                </div>
            </div>
            <ul class="menu-list">
                <li class="menu-item">
                    <a class="menu-link" href="<?php echo get_permalink(MONA_PAGE_ACCOUNT) ?>"><?php echo __('Tài khoản', 'monamedia'); ?></a>
                </li>
                <li class="menu-item">
                    <a class="menu-link" href="<?php echo wp_logout_url(home_url()); ?>"><?php echo __('Đăng xuất', 'monamedia'); ?></a>
                </li>
            </ul>
        </div>
    <?php
    } else {
        wp_localize_script(
            'mona-frontend',
            'noti',
            array(
                'message' =>  __('Bạn cần phải đăng nhập!', 'monamedia'),
                'title' =>  __('Lưu ý', 'monamedia'),
                'icon' => 'warning',
                'timer' => 2500,
                'rule' =>  __('Vui lòng chấp nhận điều khoản', 'monamedia'),
            )
        );
    }
    ?>
</div>