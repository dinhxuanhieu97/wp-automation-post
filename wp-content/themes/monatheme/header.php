<?php

/**
 * The template for displaying header.
 *
 * @package MONA.Media / Website
 */
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) & !(IE 8)]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->

<head>
  <!-- Meta ================================================== -->
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, width=device-width">
  <?php wp_site_icon(); ?>
  <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
  <meta name="google-adsense-account" content="ca-pub-6893565878966876">
  <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6893565878966876"
     crossorigin="anonymous"></script>

  <script async custom-element="amp-ad" src="https://cdn.ampproject.org/v0/amp-ad-0.1.js"></script>
  <script src="https://analytics.ahrefs.com/analytics.js" data-key="5UNKBK3jSSr8rxbvhRJ3Gw" async></script>
  <script>
  var ahrefs_analytics_script = document.createElement('script');
  ahrefs_analytics_script.async = true;
  ahrefs_analytics_script.src = 'https://analytics.ahrefs.com/analytics.js';
  ahrefs_analytics_script.setAttribute('data-key', '5UNKBK3jSSr8rxbvhRJ3Gw');
  document.getElementsByTagName('head')[0].appendChild(ahrefs_analytics_script);
</script>
  <?php wp_head(); ?> 

</head>
<?php
if (wp_is_mobile()) {
  $body = 'mobile-detect';
} else {
  $body = 'desktop-detect';
}
$class = is_user_logged_in() ? 'success-hd' : '';
if (!is_front_page() && !is_singular('post') && !is_singular('event') && !is_home() && !is_page(MONA_PAGE_EVENT))
  $class .= ' no-bn';
?>

<body <?php body_class($body); ?>>
  <header class="header <?php echo $class ?>">
    <div class="container">
      <div class="header-wrap">
        <?php
        $bn = get_field('mona_hd_bn', 'option');
        $url = get_field('mona_hd_url', 'option');
        if (!empty($bn)) {
        ?>
          <div class="header-top">
            <div class="header-top-wrap">
              <a class="header-banner" href="<?php echo !empty($url) ? $url : 'javascript:;' ?>">
                <?php echo wp_get_attachment_image($bn, 'full') ?>
              </a>
            </div>
          </div>
        <?php
        }
        ?>
        <div class="header-top-mb">
          <div class="header-top-mb-wrap">
            <?php
            echo get_custom_logo();
            ?>
            <?php
            get_template_part('partials/global/header-login')
            ?>
          </div>
        </div>
        <div class="header-bottom">
          <div class="header-bottom-wrap">
            <div class="header-mobi btn-mobi">
              <span class="line"></span>
              <span class="line"></span>
              <span class="line"></span>
            </div>
            <?php
            echo get_custom_logo()
            ?>
            <div class="header-nav">
              <?php
              if (has_nav_menu('primary-menu')) {
                wp_nav_menu(array(
                  'container' => false,
                  'container_class' => '',
                  'menu_class' => 'menu-list',
                  'theme_location' => 'primary-menu',
                  'walker' => new Mona_Walker_Nav_Menu,
                ));
              }
              ?>
            </div>
            <div class="header-box">
              <div class="header-lang">
                <div class="header-lang-title">
                <?php echo do_shortcode('[gtranslate]'); ?>

                  <!-- 
                  <div class="header-lang-wrap">
                    <span class="header-lang-img">
                      <img src="<php echo MONA_SITE_TEMPLATE ?>/assets/images/vn.png" alt="" />
                    </span>
                    <span class="note-text cl-text2 t-up">vn</span>
                  </div>
                  <ul class="header-lang-list">
                    <li class="header-lang-item">
                      <a class="header-lang-link" href="">
                        <span class="header-lang-wrap">
                          <span class="header-lang-img">
                            <img src="<php echo MONA_SITE_TEMPLATE ?>/assets/images/en.png" alt="" />
                          </span>
                          <span class="note-text cl-text2 t-up">en</span>
                        </span>
                      </a>
                    </li>
                  </ul>-->
                </div> 
              </div>
              <?php
              echo get_search_form();
              ?>
              <?php
              get_template_part('partials/global/header-login')
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
  <div class="menu-mb">
    <div class="menu-mb-wrap">
      <?php
      if (has_nav_menu('primary-menu')) {
        wp_nav_menu(array(
          'container' => false,
          'container_class' => '',
          'menu_class' => 'menu-list',
          'theme_location' => 'primary-menu',
          'walker' => new Mona_Walker_Nav_Menu,
        ));
      }
      ?>
      <div class="menu-mb-box">
        <?php
        $box = get_field('mona_ft_social', 'option');
        if (content_exists($box)) {
        ?>
          <div class="menu-mxh">
            <div class="menu-lh-title"><?php echo get_field('mona_ft_tt', 'option') ?></div>
            <div class="footer-mxh">
              <ul class="footer-mxh-list">
                <?php
                foreach ($box as $item) {
                ?>
                  <li class="footer-mxh-item">
                    <a class="footer-mxh-link" href="<?php echo !empty($item['url']) ? $item['url'] : 'javascript:;' ?>" target="<?php echo !empty($item['url'])  ? '_blank' : '' ?>">
                      <?php echo wp_get_attachment_image($item['icon'], 'full') ?>
                    </a>
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
        <?php
        $box = get_field('mona_ft_contact', 'option');
        if (content_exists($box)) {
        ?>
          <div class="menu-lh">
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
              <?php
              }
              ?>
            </ul>
          </div>
        <?php
        }
        ?>
      </div>
    </div>
  </div>
  <div class="menu-modal"></div>



<?php adsforwp_the_ad( 9518 ) ?>
<!-- <amp-ad width="100vw" height="320"
     type="adsense"
     data-ad-client="ca-pub-6893565878966876"
     data-ad-slot="1366008744"
     data-auto-format="rspv"
     data-full-width="">
  <div overflow=""></div>
</amp-ad> -->