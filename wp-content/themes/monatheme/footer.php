<footer class="footer">
    <div class="container">
        <div class="footer-wrap">
            <div class="footer-top">
                <div class="footer-logo">
                    <?php echo get_custom_logo(); ?>
                </div>
            </div>
            <div class="footer-center">
                <div class="footer-list d-wrap">
                    <div class="footer-item d-item">
                        <div class="footer-item-wrap">
                            <div class="footer-item-content">
                                <p class="note-text cl-text2 fw-7">
                                    <?php echo !empty(get_field('mona_ft_slogan', 'option')) ? get_field('mona_ft_slogan', 'option') : 'Đồng hành cùng bạn trên con đường công nghệ'; ?>
                                </p>
                                <?php
                                $box = get_field('mona_ft_contact', 'option');
                                if (content_exists($box)) {
                                ?>
                                <div class="footer-info">
                                    <ul class="list-address">
                                        <?php
                                            foreach ($box as $item) {
                                            ?>
                                        <li class="item-address">
                                            <span class="ic-address">
                                                <?php echo wp_get_attachment_image($item['icon'], 'full') ?>
                                            </span>
                                            <?php
                                                switch ($item['type']) {
                                                    case 'text':
                                                        echo '<a class="link-address" href="javascript:;">' . $item['ct'] . '</a>';
                                                        break;
                                                    case 'tel':
                                                        echo '<a class="link-address" href="' . mona_replace_tel($item['ct']) . '">' . $item['ct'] . '</a>';
                                                        break;
                                                    case 'mail':
                                                        echo '<a class="link-address" href="mailto:' . $item['ct'] . '">' . $item['ct'] . '</a>';
                                                        break;
                                                }
                                            ?>
                                        </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                                <?php
                                }
                                ?>

                            </div>
                        </div>
                    </div>
                    <div class="footer-item d-item">
                        <?php
                        if (has_nav_menu('footer-menu')) {
                        ?>
                        <div class="footer-item-wrap">
                            <div class="footer-item-content">
                                <?php
                                    $menu_obj = get_term(get_nav_menu_locations()['footer-menu'], 'nav_menu')->term_id;
                                    $menu = get_field('mona_menu_tt', 'menu_' . $menu_obj);
                                    if (wp_get_nav_menu_items(get_nav_menu_locations()['footer-menu'])) { ?>
                                <p class="note-text cl-text2 fw-7">
                                    <!-- <?php echo $menu ?> -->
                                    <?php echo __('Tiện ích', 'monamedia'); ?>
                                </p>
                                <?php } ?>
                                <?php
                                    if (has_nav_menu('footer-menu')) {
                                        wp_nav_menu(array(
                                            'container' => false,
                                            'container_class' => '',
                                            'menu_class' => 'menu-list',
                                            'theme_location' => 'footer-menu',
                                            'walker' => new Mona_Walker_Nav_Menu,
                                        ));
                                    }
                                    ?>
                            </div>
                        </div>
                        <?php
                        }
                        ?>
                    </div>
                    <div class="footer-item d-item">
                        <?php
                        if (has_nav_menu('footer-menu-2')) {
                        ?>
                        <div class="footer-item-wrap">
                            <div class="footer-item-content">
                                <?php
                                    $menu_obj = get_term(get_nav_menu_locations()['footer-menu-2'], 'nav_menu')->term_id;
                                    $menu = get_field('mona_menu_tt', 'menu_' . $menu_obj);
                                    if (wp_get_nav_menu_items(get_nav_menu_locations()['footer-menu-2'])) { ?>
                                <p class="note-text cl-text2 fw-7">
                                    <!-- <php echo $menu ?> -->
                                    <?php echo __('Liên kết nhanh', 'monamedia'); ?>
                                </p>
                                <?php } ?>
                                <?php
                                    if (has_nav_menu('footer-menu-2')) {
                                        wp_nav_menu(array(
                                            'container' => false,
                                            'container_class' => '',
                                            'menu_class' => 'menu-list',
                                            'theme_location' => 'footer-menu-2',
                                            'walker' => new Mona_Walker_Nav_Menu,
                                        ));
                                    }
                                    ?>
                            </div>
                        </div>
                        <?php
                        }
                        ?>
                    </div>
                    <?php
                    $box = get_field('mona_ft_social', 'option');
                    if (content_exists($box)) {
                    ?>
                    <div class="footer-item d-item">
                        <div class="footer-item-wrap">
                            <div class="footer-item-content">
                                <p class="note-text cl-text2 fw-7">
                                    <?php
                                        $mona_ft_tt = get_field('mona_ft_tt', 'option');
                                        echo !empty($mona_ft_tt) ? $mona_ft_tt : 'Theo dõi chúng tôi';
                                        ?>
                                </p>
                                <div class="footer-mxh">
                                    <ul class="footer-mxh-list">
                                        <?php
                                            foreach ($box as $item) {
                                            ?>
                                        <li class="footer-mxh-item">
                                            <a class="footer-mxh-link"
                                                href="<?php echo !empty($item['url']) ? $item['url'] : 'javascript:;' ?>"
                                                target="<?php echo !empty($item['url'])  ? '_blank' : '' ?>">
                                                <?php echo wp_get_attachment_image($item['icon'], 'full') ?>
                                            </a>
                                        </li>
                                        <?php
                                            } ?>

                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    } else {
                    ?>
                    <div class="footer-item d-item">
                        <div class="footer-item-wrap">
                            <div class="footer-item-content">
                                <p class="note-text cl-text2 fw-7">
                                    <?php
                                        $mona_ft_tt = get_field('mona_ft_tt', 'option');
                                        echo !empty($mona_ft_tt) ? $mona_ft_tt : 'Theo dõi chúng tôi';
                                        ?>
                                </p>
                                <div class="footer-mxh">
                                    <ul class="footer-mxh-list">
                                        <li class="footer-mxh-item"> <a class="footer-mxh-link" href="javascript:;"><img
                                                    src="/template/assets/images/face.svg" alt="" /></a></li>
                                        <li class="footer-mxh-item"> <a class="footer-mxh-link" href="javascript:;"><img
                                                    src="/template/assets/images/Twitter.svg" alt="" /></a></li>
                                        <li class="footer-mxh-item"> <a class="footer-mxh-link" href="javascript:;"><img
                                                    src="/template/assets/images/ins.svg" alt="" /></a></li>
                                        <li class="footer-mxh-item"> <a class="footer-mxh-link" href="javascript:;"><img
                                                    src="/template/assets/images/lid.svg" alt="" /></a></li>
                                        <li class="footer-mxh-item"> <a class="footer-mxh-link" href="javascript:;"><img
                                                    src="/template/assets/images/ytb.svg" alt="" /></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                    } ?>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-wrap">
                <div class="footer-alert">
                    <p class="note-sm">
                        <?php echo __('© 2018 Điện Máy Kim Biên Pte. Ltd. All rights reserved.', 'monamedia'); ?>
                    </p>
                </div>
                <div class="footer-mona">
                    <p class="note-sm">
                        <?php echo __('KeyDev © All Right Reserved', 'monamedia'); ?>
                    </p>
                    <div class="footer-mona-img">
                        <!-- <img src="<php echo MONA_SITE_TEMPLATE ?>/assets/images/Signature2.svg" alt="" /> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
<div class="links-main2">
    <ul class="links-main2-list">
        <li class="links-main2-item">
            <div class="links-main2-link btnTop scroll-to-top"><span class="links-main2-ic"> <i
                        class="fas fa-chevron-up"></i></span></div>
        </li>
        <?php
        $form = get_field('mona_sb_form', 'option');
        if (!empty($form)) {
        ?>
        <li class="links-main2-item">
            <div class="links-main2-link btnFollow"><span class="links-main2-ic"> <img
                        src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/mailMain.svg" alt="" /></span></div>
            <div class="links-main2-popup linksMainPopup">
                <div class="links-main2-popup-wrap">
                    <div class="links-main2-popup-ex btnExFollow"> <i class="far fa-times"></i></div>
                    <div class="links-main2-popup-title">
                        <h6 class="title-mn cl-text2 fw-7">
                            <?php echo get_field('mona_sb_tt', 'option') ?>
                        </h6>
                        <div class="note-sm cl-text mona-content">
                            <?php echo get_field('mona_sb_ct', 'option') ?>
                        </div>
                    </div>
                    <?php

                        echo do_shortcode('[contact-form-7 id="' . $form . '" html_id="frmNews" html_class=""]');
                        ?>
                </div>
            </div>
        </li>
        <?php
        }
        ?>
    </ul>
</div>

<!-- footer -->
<?php wp_footer(); ?>
</body>

</html>