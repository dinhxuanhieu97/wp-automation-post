<?php

/**
 * The template for displaying single.
 *
 * @package MONA.Media / Website
 */
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

get_header();
while (have_posts()) :
  the_post();
  mona_set_post_view();
?>
  <main class="main pt">
    <?php
    get_template_part('partials/breadcrumb')
    ?>
    <h1 class="hidden-tt"> <?php the_title(); ?> </h1>
    <section class="news_ct">
      <div class="news_ct--op">
        <div class="control-1">
          <ul class="control-1-list">
            <li class="control-1-item">
              <?php if (is_user_logged_in()) { ?>
                <?php
                $user_meta_key = 'wishlist_user_';
                $user_id = get_current_user_id();
                $wishlist_user = get_user_meta($user_id, $user_meta_key . $user_id, true);
                $act = in_array($post->ID, $wishlist_user) ? 'remove' : 'add';
                $checked = in_array($post->ID, $wishlist_user) ? 'checked' : '';
                $wishlistCheck = isset($args['wishlist_check']) && $args['wishlist_check'] ? 'del' : '';
                ?>
                <label class="control-img heart monaWishListJS detail <?php echo $wishlistCheck ?>" data-act="<?php echo $act ?>" data-id="<?php echo $post->ID ?>">
                  <input type="checkbox" <?php echo $checked ?>>
                  <span class="ic">
                  </span>
                  <img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/s1.svg" alt="" />
                </label>
              <?php
              } else {
              ?>
                <a class="control-img heart ask-login" href="<?php echo get_the_permalink(MONA_PAGE_LOGIN) . '?redirect=' . get_permalink(); ?>">
                  <span class="ic">
                  </span>
                  <img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/s1.svg" alt="" />
                </a>
              <?php
              }
              ?>
            </li>
            <li class="control-1-item print"> <span class="control-img"><img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/s3.svg" alt="" /></span></li>
          </ul>
        </div>
        <div class="control-2">
          <?php get_template_part('partials/social-share') ?>
        </div>
      </div>
      <div class="container">
        <div class="news_ct--wrap">
          <div class="news_ct--box d-wrap">
            <div class="news_ct--lf d-item">
              <div class="news_ct--lf-wrap">
                <div class="news_ct--lf-wrap-content">
                  <div class="news_ct--title">
                    <?php
                    $term = get_primary_taxonomy_term('');
                    if (content_exists($term)) {
                      $tax = get_term_by('id', $term['id'], 'category');
                      $color = get_field('mona_tax_color', $tax);
                      $bgcolor = get_field('mona_tax_bgcolor', $tax);
                    ?>
                      <a class="box-dm" <?php echo 'style="background-color:' . $bgcolor . '; color:' . $color . '"' ?> href="<?php echo $term['url'] ?>"><?php echo $term['title'] ?></a>
                    <?php
                    }
                    ?>
                    <h2 class="title-sm2 cl-text2 fw-7"><?php echo get_the_title() ?></h2>
                    <div class="box-info2">
                      <div class="box-info2-author"><span class="ic"><img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/user.svg" alt="" /></span><span class="note-sm fw-7"><?php the_author() ?></span></div>
                      <div class="box-info2-date"><span class="ic"><img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/time.svg" alt="" /></span>
                        <p class="note-sm cl-gray">
                          <?php the_date('m/Y')
                          // the_date('H:i d/m/Y')
                          ?>
                        </p>
                      </div>
                      <div class="box-info2-time"> <span class="ic"><img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/time.svg" alt="" /></span>
                        <p class="note-sm cl-gray"><?php echo do_shortcode('[rt_reading_time postfix="' . __('phút đọc', 'monamedia') . '" postfix_singular="' . __('phút đọc', 'monamedia') . '"]') ?></p>
                      </div>
                    </div>
                  </div>
                  <div class="news_ct--content mona-content">
                    <?php echo the_content(); ?>
                  </div>
                </div>
                <div class="news_ct--utility">
                  <div class="news_ct--control">
                    <p class="note-text"><?php echo __('Chia sẻ bài viết:', 'monamedia'); ?></p>
                    <div class="news_ct--control-wrap">
                      <div class="news_ct--control-lf">
                        <div class="control-1">
                          <ul class="control-1-list">
                            <li class="control-1-item">
                              <?php if (is_user_logged_in()) { ?>
                                <?php
                                $user_meta_key = 'wishlist_user_';
                                $user_id = get_current_user_id();
                                $wishlist_user = get_user_meta($user_id, $user_meta_key . $user_id, true);
                                $act = in_array($post->ID, $wishlist_user) ? 'remove' : 'add';
                                $checked = in_array($post->ID, $wishlist_user) ? 'checked' : '';
                                $wishlistCheck = isset($args['wishlist_check']) && $args['wishlist_check'] ? 'del' : '';
                                ?>
                                <label class="control-img heart monaWishListJS detail <?php echo $wishlistCheck ?>" data-act="<?php echo $act ?>" data-id="<?php echo $post->ID ?>">
                                  <input type="checkbox" <?php echo $checked ?>>
                                  <span class="ic">
                                  </span>
                                  <img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/s1.svg" alt="" />
                                </label>
                              <?php
                              } else {
                              ?>
                                <a class="control-img heart" href="<?php echo get_the_permalink(MONA_PAGE_LOGIN) . '?redirect=' . get_permalink(); ?>">
                                  <span class="ic">
                                  </span>
                                  <img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/s1.svg" alt="" />
                                </a>
                              <?php
                              }
                              ?>
                            </li>
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
                  <?php
                  $post_tag = get_the_tags();
                  if ($post_tag && !is_wp_error($post_tag)) {
                  ?>
                    <div class="news_ct--keys">
                      <div class="news_ct--keys-wrap">
                        <p class="note-text"><?php echo __('Từ khoá:', 'monamedia'); ?></p>
                        <ul class="news_ct--keys-list">
                          <?php
                          foreach ($post_tag as $item) {
                          ?>
                            <li class="news_ct--keys-item"><a class="box-dm" href="<?php echo get_term_link($item) ?>"><?php echo $item->name ?></a>
                            </li>
                          <?php
                          }
                          ?>
                        </ul>
                      </div>
                    </div>
                  <?php
                  }
                  ?>
                </div>
                <?php
                $args = array(
                  'post_type' => ['post', 'event'],
                  'post_status' => 'publish',
                  'posts_per_page' => 3,
                );
                // Define an empty array
                if (content_exists($term)) {
                  $args['tax_query'] = [
                    [
                      'taxonomy' => 'category',
                      'field' => 'term_id',
                      'terms' => $term['id'],
                    ]
                  ];
                }
                $list_posts = new WP_Query($args);
                $countposts = $list_posts->found_posts;
                if ($countposts > 0) {
                ?>
                  <div class="news_ct--maybe">
                    <p class="title-mn fw-7 cl-text2"><?php echo __('Có thể bạn sẽ thích', 'monamedia'); ?></p>
                    <ul class="news_ct--maybe-list">
                      <?php
                      while ($list_posts->have_posts()) {
                        $list_posts->the_post();
                      ?>
                        <li class="news_ct--maybe-item">
                          <a class="news_ct--maybe-link" href="<?php echo get_permalink() ?>">
                            <?php echo get_the_title() ?>
                          </a>
                        </li>
                      <?php }
                      wp_reset_postdata()
                      ?>
                    </ul>
                  </div>
                <?php
                }
                ?>
              </div>
            </div>
            <?php
            get_template_part('partials/single/read-more')
            ?>
          </div>
        </div>
      </div>
    </section>
    <?php
    get_template_part('partials/global/slide-ads')
    ?>
    <?php
    $args = array(
      'post_type' => ['post', 'event'],
      'post_status' => 'publish',
      'posts_per_page' => 6,

    );
    $ids = array();
    // Check if the post has any tags
    if ($post_tag && !is_wp_error($post_tag)) {
      foreach ($post_tag as $tag) {
        $ids[] = $tag->term_id;
      }
    }
    if (!empty($ids)) {
      $args['tag__in'] = $ids;
      $args['post__not_in'] = [get_the_ID()];
      $args['orderby'] = 'rand';
    }
    $list_posts = new WP_Query($args);
    $countposts = $list_posts->found_posts;
    if ($countposts > 0) {
    ?>
      <section class="post_lq free-slide">
        <div class="container">
          <div class="post_lq--wrap">
            <div class="title-box">
              <h3 class="title-sm2 fw-7 cl-text2 title-main"> <strong><?php echo __('Bài viết', 'monamedia'); ?> </strong> <?php echo __('liên quan', 'monamedia'); ?></h3>
            </div>
            <div class="post_lq--list d-wrap">
              <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                  <?php
                  while ($list_posts->have_posts()) {
                    $list_posts->the_post();
                  ?>
                    <div class="swiper-slide d-item d-4">
                      <?php
                      get_template_part('partials/loop/box-blog')
                      ?>
                    </div>
                  <?php }
                  wp_reset_postdata()
                  ?>
                </div>
              </div>
              <div class="swiper-pagination"> </div>
              <div class="box-navi">
                <div class="btn-navi prev"> <i class="fa-light fa-angle-left"></i></div>
                <div class="btn-navi next"> <i class="fa-light fa-angle-right"></i></div>
              </div>
            </div>
          </div>
        </div>
      </section>
    <?php
    }
    ?>
    <?php
    get_template_part('partials/single/post-care')
    ?>
    <!-- <section class="news_login newsLogin <php echo !is_user_logged_in() ? 'showed' : ''; ?>">
      <div class="container">
        <div class="news_login--wrap d-wrap">
          <div class="news_login--lf d-item">
            <div class="news_login--lf-wrap">
              <h3 class="title-sm2 cl-text2 fw-7"><php echo __('Tạo một tài khoản miễn phí hoặc đăng nhập.', 'monamedia'); ?></h3>
              <p class="note-text"><php echo __('Có quyền truy cập vào các bài viết miễn phí có giới hạn, thông báo tin tức, bản tin chọn lọc, podcast và một số trò chơi hàng ngày.', 'monamedia'); ?></p>
            </div>
          </div>
          <div class="news_login--rt d-item">
            <div class="news_login--rt-wrap">
              <div class="news_login--rt-list">
                <div class="news_login--rt-item actived">
                  <div class="sign_form">
                    <php
                    if (isset($_GET['redirect'])) {
                      $redirect = @$_GET['redirect'];
                    } else {
                      $redirect = get_permalink();
                    } ?>
                    <form id="formLogin" class="is-loading-btn">
                      <input type="hidden" name="redirect" value="<php echo $redirect; ?>">
                      <div class="sign_form--wrap">
                        <div class="sign_form--list">
                          <div class="sign_form--item">
                            <div class="form-ip">
                              <div class="form-ip-label">
                                <label for="email"><php echo __('Email', 'monamedia'); ?></label>
                              </div>
                              <div class="form-ip-ip">
                                <input id="email" type="text" name="user_email" class="monaField" placeholder="<php echo  __('Email của bạn', 'monamedia') ?>">
                                <div class="mona-error mona-error-user-email"></div>
                              </div>
                            </div>
                          </div>
                          <div class="sign_form--item">
                            <div class="form-ip">
                              <div class="form-ip-label">
                                <label for="pass"><php echo __('Mật khẩu', 'monamedia'); ?></label>
                              </div>
                              <div class="form-ip-ip pass">
                                <input class="password" id="pass" type="password" name="user_password" class="monaField" placeholder="<php echo  __('Mật khẩu của bạn', 'monamedia') ?>">
                                <div class="mona-error mona-error-user-password"></div>

                                <span class="form-ip-ic hidden-pass">
                                  <i class="fal fa-eye-slash"></i>
                                </span>
                                <span class="form-ip-ic show-pass">
                                  <i class="fal fa-eye"></i>
                                </span>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="mona-error-primary mona-error-login"></div>
                        <php wp_nonce_field('login_action', 'login_nonce_field'); ?>
                        <div class="sign_control">
                          <div class="sign_save">
                            <label class="sign_save--label">
                              <input type="checkbox" name="user_remember" value="1">
                              <span class="box"></span>
                              <span class="note-sm cl-text2"><php echo __('Lưu mật khẩu', 'monamedia'); ?></span>
                            </label>
                          </div>
                          <a class="note-sm fl-third cl-gray5" href="<php echo get_permalink(MONA_PAGE_FORGOT) ?>"><php echo __('Quên mật khẩu?', 'monamedia'); ?></a>
                        </div>
                        <div class="sign_btn">
                          <button class="btn cl-2" type="submit">
                            <span class="btn-text"><php echo __('Đăng Nhập', 'monamedia'); ?></span>
                          </button>
                          <div class="sign_order">
                            <div class="sign_order--title">
                              <p class="note-text"><php echo __('hoặc', 'monamedia'); ?></p>
                            </div>
                            <ul class="sign_order--list">
                              <li class="sign_order--item">
                                <a class="btn trans" href="javascript:;">
                                  <span class="btn-ic">
                                    <img src="<php echo MONA_SITE_TEMPLATE ?>/assets/images/gg.svg" alt="" />
                                  </span>
                                  <span class="btn-text"><php echo __('Đăng nhập với Google', 'monamedia'); ?></span>
                                </a>
                              </li>
                              <li class="sign_order--item">
                                <a class="btn trans" href="<php echo MONA_SITE_URL ?>/wp-login.php?loginSocial=facebook" data-plugin="nsl" data-action="connect" data-redirect="current" data-provider="facebook" data-popupwidth="600" data-popupheight="679">
                                  <span class="btn-ic">
                                    <img src="<php echo MONA_SITE_TEMPLATE ?>/assets/images/fb.svg" alt="" />
                                  </span>
                                  <span class="btn-text"><php echo __('Đăng nhập với Facebook', 'monamedia'); ?></span>
                                </a>
                              </li>
                            </ul>
                          </div>
                        </div>
                        <div class="sign_ok">
                          <p class="note-text cl-gray5"> <php echo __('Bạn chưa có tài khoản?', 'monamedia'); ?>
                            <a class="fw-7 cl-text2" id="tabRegister" href=""> <php echo __('Đăng kí ngay', 'monamedia'); ?> </a>
                          </p>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
                <div class="news_login--rt-item">
                  <div class="sign_form">
                    <form id="formRegister" class="tab-login is-loading-btn">
                      <input type="hidden" name="redirect" value="<php echo $redirect; ?>">
                      <div class="sign_form--wrap">
                        <div class="sign_form--list">
                          <div class="sign_form--item">
                            <div class="form-ip">
                              <div class="form-ip-label">
                                <label for="email"><php echo __('Email', 'monamedia'); ?></label>
                              </div>
                              <div class="form-ip-ip">
                                <input id="email" name="user_email" class="monaField" type="text" placeholder="<php echo  __('Email của bạn', 'monamedia') ?>">
                                <div class="mona-error mona-error-user-email"></div>

                              </div>
                            </div>
                          </div>
                          <div class="sign_form--item">
                            <div class="form-ip">
                              <div class="form-ip-label">
                                <label for="pass"><php echo __('Tạo mật khẩu', 'monamedia'); ?></label>
                              </div>
                              <div class="form-ip-ip pass">
                                <input class="password monaField" name="user_password" id="pass" type="password" placeholder="<php echo  __('Tạo mật khẩu mới của bạn', 'monamedia') ?>"><span class="form-ip-ic hidden-pass"><i class="fal fa-eye-slash"></i></span><span class="form-ip-ic show-pass"><i class="fal fa-eye"></i></span>
                                <div class="mona-error mona-error-user-password"></div>

                              </div>
                            </div>
                          </div>
                          <div class="sign_form--item">
                            <div class="form-ip">
                              <div class="form-ip-label">
                                <label for="repass"><php echo __('Xác minh mật khẩu', 'monamedia'); ?></label>
                              </div>
                              <div class="form-ip-ip pass">
                                <input class="password monaField" id="repass" type="password" name="user_repassword" placeholder="<php echo  __('Xác minh mật khẩu mới của bạn', 'monamedia') ?>"><span class="form-ip-ic hidden-pass"><i class="fal fa-eye-slash"></i></span><span class="form-ip-ic show-pass"><i class="fal fa-eye"> </i></span>
                                <div class="mona-error mona-error-user-repassword"></div>

                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="mona-error-primary mona-error-register"></div>
                        <php wp_nonce_field('register_action', 'register_nonce_field'); ?>
                        <div class="sign_control">
                          <div class="sign_cs">
                            <label class="sign_cs--label">
                              <input type="checkbox" value="1" name="rule_check"><span class="box"></span>
                              <div class="note-sm cl-text2 mona-content">
                                <php echo get_field('mona_register_rule', MONA_PAGE_REGISTER) ?></div>
                            </label>
                            <div class="mona-error mona-error-rule-check"></div>

                          </div>
                        </div>
                        <div class="sign_btn">
                          <button class="btn cl-2" type="submit"> <span class="btn-text"><php echo __('Đăng ký', 'monamedia'); ?></span>
                          </button>
                          <div class="sign_order">
                            <div class="sign_order--title">
                              <p class="note-text"><php echo __('hoặc', 'monamedia'); ?></p>
                            </div>
                            <ul class="sign_order--list">
                              <li class="sign_order--item">
                                <a class="btn trans" href="javascript:;">
                                  <span class="btn-ic">
                                    <img src="<php echo MONA_SITE_TEMPLATE ?>/assets/images/gg.svg" alt="" />
                                  </span>
                                  <span class="btn-text"><php echo __('Đăng ký với Google', 'monamedia'); ?></span>
                                </a>
                              </li>
                              <li class="sign_order--item">
                                <a class="btn trans" href="<php echo MONA_SITE_URL ?>/wp-login.php?loginSocial=facebook" data-plugin="nsl" data-action="connect" data-redirect="current" data-provider="facebook" data-popupwidth="600" data-popupheight="679">
                                  <span class="btn-ic">
                                    <img src="<php echo MONA_SITE_TEMPLATE ?>/assets/images/fb.svg" alt="" />
                                  </span>
                                  <span class="btn-text"><php echo __('Đăng ký với Facebook', 'monamedia'); ?></span>
                                </a>
                              </li>
                            </ul>
                          </div>
                        </div>
                        <div class="sign_ok">
                          <p class="note-text cl-gray5"> <php echo __('Bạn đã tài khoản?', 'monamedia'); ?>
                            <a class="fw-7 cl-text2" href="" id="tabLogin"> <php echo __('Đăng nhập ngay', 'monamedia'); ?> </a>
                          </p>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <div class="news_login--modal"></div>
    <php
    get_template_part('partials/global/signup');
    ?> -->
  </main>
<?php
endwhile;
get_footer();
