<?php
$tab = $args['tab'];
?>
<h1 class="title-sm2 fw-7 cl-text2"><?php echo __('Thông tin tài khoản', 'monamedia'); ?></h1>
<ul class="info_tab--list">
    <li class="info_tab--item <?php if (empty($tab) || $tab == 'info') echo 'actived' ?>">
        <a class="info_tab--link" href="?tab=info"><?php echo __('Thông tin', 'monamedia'); ?></a>
    </li>
    <li class="info_tab--item <?php echo $tab == 'password' ? 'actived' : '' ?>">
        <a class="info_tab--link" href="?tab=password"><?php echo __('Mật khẩu', 'monamedia'); ?></a>
    </li>
    <li class="info_tab--item <?php echo $tab == 'link-account' ? 'actived' : '' ?>">
        <a class="info_tab--link" href="?tab=link-account"><?php echo __('Liên kết tài khoản', 'monamedia'); ?></a>
    </li>
</ul>