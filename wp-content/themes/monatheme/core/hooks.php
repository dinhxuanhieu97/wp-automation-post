<?php
add_action('after_setup_theme', 'add_after_setup_theme');
function add_after_setup_theme()
{
    // regsiter menu
    register_nav_menus(
        [
            'primary-menu' => __('Header Menu', 'monamedia'),
            'footer-menu' => __('Footer Menu', 'monamedia'),
            'footer-menu-2' => __('Footer Menu 2', 'monamedia'),
        ]
    );
    // add size image
    // add_image_size( 'banner-desktop-image', 1920, 790, false );
    // add_image_size( 'banner-mobile-image', 400, 675, false );
}
add_action('wp_enqueue_scripts', 'mona_add_styles_scripts');
function mona_add_styles_scripts()
{
    // loading custom styles, scripts
    wp_enqueue_style('sweetalert2-css', MONA_THEME_PATH . '/public/css/sweetalert2.min.css', array(), THEME_VERSION, true);

    wp_enqueue_script('sweetalert2-js', MONA_THEME_PATH . '/public/scripts/sweetalert2.all.min.js', array(), THEME_VERSION, true);

    // loading template styles
    do_config_enqueue_scripts('templates');
    // loading themes styles
    do_config_enqueue_scripts('themes');
    // loading localize script

    wp_localize_script(
        'mona-frontend',
        'mona_ajax_url',
        [
            'ajaxURL'   => admin_url('admin-ajax.php'),
            'siteURL'   => get_site_url(),
            'ajaxNonce' => wp_create_nonce('mona-ajax-security'),
        ]
    );
}
add_action('wp_print_styles', 'mona_deregister_styles', 100);
function mona_deregister_styles()
{
    wp_deregister_style('wp-block-library');
    wp_deregister_style('global-styles');
    wp_deregister_style('classic-theme-styles');
    // wp_deregister_style(' contact-form-7' );
    if (!is_user_logged_in()) {
        wp_deregister_style('dashicons');
    }
}
add_action('wp_print_scripts', 'mona_deregister_scripts', 100);
function mona_deregister_scripts()
{
    wp_deregister_script('yoast-seo-premium-commons');
    wp_deregister_script('hoverintent-js');
    // wp_deregister_script( 'swv' );
}
add_filter('script_loader_tag', 'mona_add_module_to_my_script', 10, 3);
function mona_add_module_to_my_script($tag, $handle, $src)
{
    if (in_array($handle, array('mona-frontend', 'mona-main'))) {
        $tag = '<script type="module" src="' . esc_url($src) . '"></script>';
    }
    return $tag;
}
//add_action( 'wp_logout', 'mona_redirect_external_after_logout' );
function mona_redirect_external_after_logout()
{
    wp_redirect(get_the_permalink(MONA_PAGE_HOME));
    exit();
}
add_filter('pre_get_posts', 'mona_parse_request_post_type');
function mona_parse_request_post_type($query)
{
    if (!is_admin()) {
        $query->set('ignore_sticky_posts', true);
        $ptype = $query->get('post_type', true);
        $ptype = (array) $ptype;
        // if ( isset( $_GET['s'] ) ) {
        //     $ptype[] = 'post';
        //     $query->set('post_type', $ptype);
        //     $query->set( 'posts_per_page' , 12);
        // }
        // if ( $query->is_main_query() && $query->is_tax( 'category_library' ) ) {
        //     $ptype[] = 'mona_library';
        //     $query->set('post_type', $ptype);
        //     $query->set('posts_per_page', 12);
        // }
        if (isset($_GET['s']) && is_search()) {
            $ptype[] = 'post';
            $ptype[] = 'mona_recruit';
            $query->set('post_type', $ptype);
            $query->set('posts_per_page', 12);
        }

        if ($query->is_main_query() && ($query->is_category() || $query->is_tag())) {
            $query->set('posts_per_page', 12);
        }

        if ($query->is_main_query() && $query->is_tax()) {
            $cat = get_queried_object();
            $tax = $cat->taxonomy;
            $postt = get_taxonomy($tax)->object_type;
            $ptype = $postt;
            $query->set('post_type', $ptype);
            $query->set('posts_per_page', 12);
        }
    }
    return $query;
}
// add_action('widgets_init', 'mona_register_sidebars');
function mona_register_sidebars()
{
    register_sidebar(
        [
            'id'            => 'footer_column1',
            'name'          => __('Footer Column 1', 'mona-admin'),
            'description'   => __('Nội dung widget.', 'mona-admin'),
            'before_widget' => '<div id="%1$s" class="widget footer-menu-item footer-menu-item-first %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="head mona-widget-title">',
            'after_title'   => '</h3>',
        ]
    );
}
add_filter('display_post_states', 'mona_add_post_state', 10, 2);
function mona_add_post_state($post_states, $post)
{
    if ($post->ID == MONA_PAGE_HOME) {
        $post_states[] = __('PAGE - Trang chủ', 'mona-admin');
    }
    if ($post->ID == MONA_PAGE_BLOG) {
        $post_states[] = __('PAGE - Trang Tin tức', 'mona-admin');
    }
    return $post_states;
}
add_filter('get_custom_logo', 'mona_change_logo_class');
function mona_change_logo_class($html)
{
    $custom_logo_id = get_theme_mod('custom_logo');
    $html           = sprintf(
        '<a href="%1$s" class="header-icon header-logo" rel="home" itemprop="url"><div class="icon">%2$s</div></a>',
        esc_url(home_url()),
        wp_get_attachment_image(
            $custom_logo_id,
            'full',
            false,
            [
                'class'  => 'header-logo-image',
            ]
        )
    );
    return $html;
}
add_filter('admin_url', 'mona_filter_admin_url', 999, 3);
function mona_filter_admin_url($url, $path, $blog_id)
{
    if ($path === 'admin-ajax.php' && !is_admin()) {
        $url .= '?mona-ajax';
    }
    return $url;
}
add_filter('wp_get_attachment_image_attributes', 'mona_image_remove_attributes');
function mona_image_remove_attributes($attr)
{
    unset($attr['sizes']);
    return $attr;
}
add_action(' wp_footer', 'mona_filter_front_footer');
function mona_filter_front_footer()
{
    echo '<div id="mona-toast"></div>';
}
add_filter('post_thumbnail_html', 'mona_set_post_thumbnail_default', 20, 5);
function mona_set_post_thumbnail_default($html, $post_id, $post_thumbnail_id, $size, $attr)
{
    if (empty($html)) {
        return wp_get_attachment_image(MONA_CUSTOM_LOGO, 'full', "", ['class' => 'cg-image-default']);
    }
    return $html;
}
/* ----------------------- Add icon for admin nav menu ---------------------- */
add_action('admin_head', 'my_custom_fonts');
function my_custom_fonts()
{
    echo '<style>
  #adminmenu .wp-menu-image img {
    padding: 5px 0 0;
    opacity: 1;
    height: 25px;
    width: 25px;
}
.taxonomy-department_program .acf-image-uploader .image-wrap img {
    position: relative;
    float: left;
    background-color: #0000002b !important;
    padding: 10px !important;
}
  </style>';
}
/* -------------------------- Add custom cf7 submit ------------------------- */
// add_action('wpcf7_init', 'wpcf7_add_shortcode_submit_button');
function wpcf7_add_shortcode_submit_button()
{
    wpcf7_add_form_tag('submitbtn', 'wpcf7_submit_button_shortcode_handler');
    wpcf7_add_form_tag('submitbtn2', 'wpcf7_submit_button_shortcode_handler_2');
}
function wpcf7_submit_button_shortcode_handler($tag)
{
    $tag = new WPCF7_FormTag($tag);
    $class = wpcf7_form_controls_class($tag->type);
    $atts = array();
    $atts['class'] = $tag->get_class_option($class);
    $atts['id'] = $tag->get_id_option();
    $atts['tabindex'] = $tag->get_option('tabindex', 'int', true);
    $value = isset($tag->values[0]) ? $tag->values[0] : '';
    if (empty($value))
        $value = __('Submit', 'contact-form-7');
    $atts['type'] = 'submit';
    $atts = wpcf7_format_atts($atts);
    $spinner = '<span class="wpcf7-spinner"></span>';
    $html = sprintf('<button %1$s><span class="inner">%2$s</span><i class="fa-light fa-arrow-right icon"></i></button>%3$s', $atts, $value, $spinner);
    return $html;
}
function wpcf7_submit_button_shortcode_handler_2($tag)
{
    $tag = new WPCF7_FormTag($tag);
    $class = wpcf7_form_controls_class($tag->type);
    $atts = array();
    $atts['class'] = $tag->get_class_option($class);
    $atts['id'] = $tag->get_id_option();
    $atts['tabindex'] = $tag->get_option('tabindex', 'int', true);
    $value = isset($tag->values[0]) ? $tag->values[0] : '';
    if (empty($value))
        $value = __('Submit', 'contact-form-7');
    $atts['type'] = 'submit';
    $atts = wpcf7_format_atts($atts);
    $spinner = '<span class="wpcf7-spinner"></span>';
    $html = sprintf('<button %1$s><span class="inner">%2$s</span><i class="fa-light fa-paper-plane"></i></button>%3$s', $atts, $value, $spinner);
    return $html;
}
function signature_shortcode()
{
    $box = get_field('mona_servicedt_signature');
    if (!empty($box)) {
        ob_start();
        // Extract shortcode attributes
?>
        <p class="signlang">
            <?php echo $box ?>
        </p>
    <?php
        return ob_get_clean();
    }
}
// Register the shortcode
// add_shortcode('signature', 'signature_shortcode');
function title_filter($where, $wp_query)
{
    global $wpdb;
    if ($search_term = $wp_query->get('search_prod_title')) {
        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql($wpdb->esc_like($search_term)) . '%\'';
    }
    return $where;
}
// add_filter('posts_where', 'title_filter', 10, 2);
/* --------------------- ACF editor decrease height size -------------------- */
function PREFIX_apply_acf_modifications()
{
    ?>
    <style>
        .acf-editor-wrap iframe {
            min-height: 0;
        }
    </style>
    <script>
        (function($) {
            // (filter called before the tinyMCE instance is created)
            acf.add_filter('wysiwyg_tinymce_settings', function(mceInit, id, $field) {
                // enable autoresizing of the WYSIWYG editor
                mceInit.wp_autoresize_on = true;
                return mceInit;
            });
            // (action called when a WYSIWYG tinymce element has been initialized)
            acf.add_action('wysiwyg_tinymce_init', function(ed, id, mceInit, $field) {
                // reduce tinymce's min-height settings
                ed.settings.autoresize_min_height = 100;
                // reduce iframe's 'height' style to match tinymce settings
                $('.acf-editor-wrap iframe').css('height', '100px');
            });
        })(jQuery)
    </script>
<?php
}
add_action('acf/input/admin_footer', 'PREFIX_apply_acf_modifications');
// Remove WooCommerce Admin Features
// add_filter('admin_init', 'my_remove_admin_menus');
// function my_remove_admin_menus($features)
// {
//     // Remove Analytics
// 	remove_menu_page('wc-admin&path=/analytics/overview');
//     // Remove Marketing
// 	remove_menu_page('woocommerce-marketing');
//     // Remove WooCommerce and Product Menu
// 	remove_menu_page('woocommerce');
// 	remove_menu_page('edit.php?post_type=product');
// 	remove_menu_page('wc-admin&path=/wc-pay-welcome-page');
//     return $features;
// }

add_filter('get_avatar', 'slug_get_avatar', 10, 6);
function slug_get_avatar($avatar, $id_or_email, $size, $default, $alt, $args)
{

    //If is email, try and find user ID
    if (!is_numeric($id_or_email) && is_email($id_or_email)) {
        $user  =  get_user_by('email', $id_or_email);
        if ($user) {
            $id_or_email = $user->ID;
        }
    }

    //if not user ID, return
    if (!is_numeric($id_or_email)) {
        return $avatar;
    }

    //Find ID of attachment saved user meta
    $saved = get_user_meta($id_or_email, 'mona_user_avatar', true);
    if (0 < absint($saved)) {
        //return saved image
        return wp_get_attachment_image_no_srcset($saved, [$size, $size], false, [
            'alt' => $alt,
            'class' => isset($args['class']) ? $args['class'] : '',
        ]);
    }

    //return normal
    return $avatar;
}

function wp_get_attachment_image_no_srcset($attachment_id, $size = 'thumbnail', $icon = false, $attr = '')
{
    // add a filter to return null for srcset
    add_filter('wp_calculate_image_srcset_meta', '__return_null');
    // get the srcset-less img html
    $html = wp_get_attachment_image($attachment_id, $size, $icon, $attr);
    // remove the above filter
    remove_filter('wp_calculate_image_srcset_meta', '__return_null');
    return $html;
}


function social_user_meta($user_id, $provider, $social_id)
{
    if ($provider == 'facebook') {
        delete_user_meta($user_id, 'fb_user_access_token');
    }
}
add_action('nsl_unlink_user', 'social_user_meta', 10, 3);


function restrict_admin_access()
{
    // Check if the user is logged in
    if (is_user_logged_in()) {
        // Get the current user
        $user = wp_get_current_user();

        // Check if the user has the role 'subscriber'
        if (in_array('subscriber', $user->roles)) {
            if (defined('DOING_AJAX') && DOING_AJAX) {
                return; // Allow AJAX requests
            }
            // Redirect the user to the home page or another page of your choice
            wp_redirect(get_permalink(MONA_PAGE_ACCOUNT));
            exit;
        }
    }
}

// Hook the function to the 'admin_init' action
add_action('admin_init', 'restrict_admin_access');
