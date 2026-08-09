<?php

/**
 * The template for displaying page template.
 *
 * @package MONA.Media / Website
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

get_header();
while (have_posts()) :
    the_post();
?>
    <main class="main pt">
        <?php
        get_template_part('partials/breadcrumb')
        ?>
        <h1 class="hidden-tt">
            <?php
            the_title();
            ?>
        </h1>
        <section class="news_ct">
            <div class="news_ct--op">
                <div class="control-1">
                    <ul class="control-1-list">
                        <li class="control-1-item print"> <span class="control-img"><img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/s3.svg" alt="" /></span></li>
                    </ul>
                </div>
                <div class="control-2">
                    <?php
                    get_template_part('partials/social-share')
                    ?>
                </div>
            </div>
            <div class="container">
                <div class="news_ct--wrap">
                    <div class="news_ct--box d-wrap">
                        <div class="news_ct--lf d-item w-100">
                            <div class="news_ct--lf-wrap">
                                <div class="news_ct--lf-wrap-content">
                                    <div class="news_ct--title">
                                        <h2 class="title-sm2 cl-text2 fw-7"><?php echo get_the_title() ?></h2>
                                        <div class="box-info2">
                                            <div class="box-info2-author"><span class="ic"><img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/user.svg" alt="" /></span><span class="note-sm fw-7"><?php the_author() ?></span></div>
                                            <div class="box-info2-date"><span class="ic"><img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/time.svg" alt="" /></span>
                                                <p class="note-sm cl-gray">
                                                    <?php
                                                    the_date('m/Y')
                                                    ?>
                                                </p>
                                            </div>
                                            <div class="box-info2-time"> <span class="ic"><img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/time.svg" alt="" /></span>
                                                <p class="note-sm cl-gray"><?php echo do_shortcode('[rt_reading_time postfix="' . __('phút đọc', 'monamedia') . '" postfix_singular="' . __('phút đọc', 'monamedia') . '"]') ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="news_ct--content mona-content">
                                        <?php
                                        the_content();
                                        ?>
                                    </div>
                                </div>
                                <div class="news_ct--utility">
                                    <div class="news_ct--control">
                                        <p class="note-text"><?php echo __('Chia sẻ bài viết:', 'monamedia'); ?></p>
                                        <div class="news_ct--control-wrap">
                                            <div class="news_ct--control-lf">
                                                <div class="control-1">
                                                    <ul class="control-1-list">
                                                        <li class="control-1-item print"> <span class="control-img"><img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/s3.svg" alt="" /></span></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="news_ct--control-rt">
                                                <div class="control-2">
                                                    <?php
                                                    get_template_part('partials/social-share')
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
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
