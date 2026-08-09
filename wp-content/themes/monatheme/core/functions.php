<?php
function mona_pagination_links($wp_query = '')
{
    if ($wp_query == '') {
        global $wp_query;
    }
    $bignum = 999999999;
    if ($wp_query->max_num_pages <= 1) {
        return;
    }
    echo '<div class="paginations">';
    echo paginate_links(
        [
            'base'      => str_replace($bignum, '%#%', esc_url(get_pagenum_link($bignum))),
            'format'    => '',
            'current'   => max(1, get_query_var('paged')),
            'total'     => $wp_query->max_num_pages,
            'prev_text' => '<i class="fa fa-arrow-left" aria-hidden="true"></i>',
            'next_text' => '<i class="fa fa-arrow-right" aria-hidden="true"></i>',
            'type'      => 'list',
            'end_size'  => 3,
            'mid_size'  => 3
        ]
    );
    echo '</div>';
}

function mona_replace($value_num = '')
{
    if (empty($value_num)) {
        return;
    }
    $string   = preg_replace('/\s+/', '', $value_num);
    $stringaz = preg_replace('/[^a-zA-Z0-9_ -]/s', '', $string);
    return $stringaz;
}

function mona_replace_tel($hotline = '')
{
    if (empty($hotline)) {
        return;
    }
    $string   = preg_replace('/\s+/', '', $hotline);
    $stringaz = preg_replace('/[^a-zA-Z0-9_ -]/s', '', $string);
    $tel = 'tel:' . $stringaz;
    return $tel;
}

function get_post_term_ids($postId = '', $taxonomy = 'category')
{
    global $array_ids;
    if ($postId == '') {
        $postId = get_the_ID();
    }
    $term_list = wp_get_post_terms($postId, $taxonomy);
    if (!empty($term_list)) {

        foreach ($term_list as $item) {
            $array_ids[] = $item->term_id;
        }
    } else {
        return;
    }
    return $array_ids;
}

function mona_set_post_view($postId = '')
{
    if (empty($postId)) {
        $postId = get_the_ID();
    }
    $count_key = '_mona_post_view';
    $count = get_post_meta($postId, $count_key, true);
    if ($count == '') {
        $count = 0;
        delete_post_meta($postId, $count_key);
        add_post_meta($postId, $count_key, '0');
    } else {
        $count++;
        update_post_meta($postId, $count_key, $count);
    }
}

/**
 * Undocumented function
 * lấy meta lượt xem cho post
 * @param [type] $postId
 * @return void
 */
function mona_get_post_view($postId = '')
{
    if (empty($postId)) {
        $postId = get_the_ID();
    }
    $count_key = '_mona_post_view';
    $count = get_post_meta($postId, $count_key, true);
    if ($count == '') {
        delete_post_meta($postId, $count_key);
        add_post_meta($postId, $count_key, '0');
        return 0;
    }

    return $count;
}

function mona_get_home_title()
{
    $home_title = get_the_title(get_option('page_on_front'));
    if ($home_title && $home_title != '') {
        $result_title = $home_title;
    } else {
        $result_title = __('Trang chủ', 'mona-admin');
    }
    return $result_title;
}

function mona_get_blogs_title()
{
    $blogs_title = get_the_title(get_option('page_for_posts', true));
    if ($blogs_title && $blogs_title != '') {
        $result_title = $blogs_title;
    } else {
        $result_title = __('Tin tức', 'mona-admin');
    }
    return $result_title;
}

function mona_get_blogs_url()
{
    $blogs_url = get_the_permalink(get_option('page_for_posts', true));
    return esc_url($blogs_url);
}

function breadcrumb_terms_list_html($term_id, $taxonomy, $args = array())
{
    $list = '';
    $term = get_term($term_id, $taxonomy);
    if (is_wp_error($term)) {
        return $term;
    }
    if (!$term) {
        return $list;
    }
    $term_id  = $term->term_id;
    $defaults = [
        'format'    => 'name',
        'separator' => '',
        'link'      => true,
        'inclusive' => true,
    ];
    $args = wp_parse_args($args, $defaults);
    foreach (array('link', 'inclusive') as $bool) {
        $args[$bool] = wp_validate_boolean($args[$bool]);
    }
    $parents = get_ancestors($term_id, $taxonomy, 'taxonomy');
    if ($args['inclusive']) {
        array_unshift($parents, $term_id);
    }
    $obz = get_queried_object();
    foreach (array_reverse($parents) as $term_id) {
        $parent = get_term($term_id, $taxonomy);
        $name   = ('slug' === $args['format']) ? $parent->slug : $parent->name;
        if ($obz->term_id != $term_id && $parent->parent == 0) {
            if ($args['link']) {
                $list .= '<li class="breadcrumb-list"><a class="item" href="' . esc_url(get_term_link($parent->term_id, $taxonomy)) . '">' . $name . '</a></li>' . $args['separator'];
            } else {
                $list .= $name . $args['separator'];
            }
        } else {
            $list .= '<li class="breadcrumb-list active"><a class="item" href="' . esc_url(get_term_link($parent->term_id, $taxonomy)) . '">' . $name . '</a></li>' . $args['separator'];
        }
    }
    return $list;
}

function mona_get_image_id_by_url($image_url = '')
{
    if (empty($image_url)) {
        return;
    }
    global $wpdb;
    $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url));
    if (!empty($attachment)) {
        return $attachment[0];
    }
}

function is_terms_active($term_id = '', $taxonomy = 'category')
{
    $termsObj = get_the_terms(get_the_ID(), $taxonomy);
    $count = 0;
    if (empty($termsObj)) {
        return $count;
    } else {
        foreach ($termsObj as $key => $item) {
            if ($item->term_id === $term_id) {
                $count++;
            }
        }
    }
    return $count;
}

function mona_checked($method = '', $value = '')
{
    if (isset($method) && is_array($method) || is_object($method)) {
        foreach ($method as $key => $item) {
            if ($item === $value) {
                $checked = "checked='checked'";
                return $checked;
            }
        }
    } elseif (!empty($method) && !is_array($method)) {
        if ($method === $value) {
            $checked = "checked='checked'";
            return $checked;
        }
    } else {
        $checked = '';
        return $checked;
    }
}

function _term_get_ancestors_count($term_id = '', $taxonomy_type = 'category')
{
    if ($term_id == '') {
        return;
    }
    $ancestors = get_ancestors($term_id, $taxonomy_type);
    return isset($ancestors) ? (count($ancestors) + 1) : 1;
}

function content_exists($content_args = [])
{
    if (!empty($content_args)) {
        $done  = 0;
        $total = count($content_args);
        foreach ($content_args as $key => $value) {
            if (!is_array($value) && $value != '' || is_array($value) && content_exists($value)) {
                $done++;
            }
        }
        if (isset($done) && $done > 0) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function show($args)
{
    if (get_current_user_id() == 1) {
        echo '<pre>';
        print_r($args);
        echo '</pre>';
    }
}

function mona_render_field_settings($args = [])
{
    if (class_exists('Mona_Widgets')) {
        Mona_Widgets::create_field($args);
    }
}

function mona_update_field_settings($fied_value = '')
{
    if (class_exists('Mona_Widgets')) {
        Mona_Widgets::update_field($fied_value);
    }
}

function get_message_success($text = '')
{
    $message = '';
    $message .= '<div class="toast__icon"><span class="dashicons dashicons-yes"></span></div>';
    $message .= '<div class="toast__body">';
    $message .= '<h3 class="toast__title">' . __('Thông báo!', 'mona-admin') . '</h3>';
    $message .= '<p class="toast__msg">' . __($text, 'mona-admin') . '</p>';
    $message .= '</div>';
    $message .= '<div class="toast__close"><span class="dashicons dashicons-no"></span></div>';
    $message .= '<div class="progress"></div>';
    // result html
    return $message;
}

function get_message_error($text = '')
{
    $message = '';
    $message .= '<div class="toast__icon"><span class="dashicons dashicons-info"></span></div>';
    $message .= '<div class="toast__body">';
    $message .= '<h3 class="toast__title">' . __('Thông báo!', 'mona-admin') . '</h3>';
    $message .= '<p class="toast__msg">' . __($text, 'mona-admin') . '</p>';
    $message .= '</div>';
    $message .= '<div class="toast__close"><span class="dashicons dashicons-no"></span></div>';
    $message .= '<div class="progress"></div>';
    // result html
    return $message;
}

function do_config_enqueue_scripts($form = '')
{
    if (empty($form)) {
        return;
    }
    global $config;
    if ($form == 'templates') {
        $url = MONA_SITE_TEMPLATE;
    } elseif ($form == 'themes') {
        $url = MONA_THEME_PATH;
    }
    $incStyles = isset($config[$form]['includes']) ? $config[$form]['includes'] : [];
    if (is_array($incStyles)) {
        foreach ($incStyles as $key => $styles) {
            switch ($key) {
                case 'styles':
                    if (is_array($styles)) {
                        foreach ($styles as $path => $value) {
                            wp_enqueue_style('mona-' . $value['name'], $url . $path, array(), THEME_VERSION);
                        }
                    }
                    break;
                case 'scripts':
                    if (is_array($styles)) {
                        foreach ($styles as $path => $value) {
                            wp_enqueue_script('mona-' . $value['name'], $url . $path, array(), THEME_VERSION, true);
                        }
                    }
                    break;
            }
        }
    }
}


/* ----------------------------- Ajax Pagination ---------------------------- */

function mona_pagination_links_ajax($wp_query = '', $paged = 1)
{
    if ($wp_query == '') {
        global $wp_query;
    }
    $bignum = 999999999;
    if ($wp_query->max_num_pages <= 1) {
        return;
    }
    return '<div class="pagination-pri">' .
        paginate_links(
            [
                'base'      => str_replace($bignum, '%#%', esc_url(get_pagenum_link($bignum))),
                'format'    => '',
                'current'   => !empty($paged) ? $paged : max(1, get_query_var('paged')),
                'total'     => $wp_query->max_num_pages,
                'prev_text' =>  '<i class="fa fa-angle-left"></i>',
                'next_text' =>  '<i class="fa fa-angle-right"></i>',
                'type'      => 'list',
                'end_size'  => 3,
                'mid_size'  => 3
            ]
        ) .
        '</div>';
}

/* ------------------------- Create ACF Options Page ------------------------ */

if (function_exists('acf_add_options_page')) {

    acf_add_options_page(array(
        'page_title'    =>  __('Cài đặt Theme', 'monamedia'),
        'menu_title'    =>  __('Cài đặt Theme', 'monamedia'),
        'menu_slug'     => 'theme-general-settings',
        'capability'    => 'edit_posts',
        'redirect'      => false
    ));
}

/* --------------------------- Check banner field --------------------------- */

function check_banner_field($itemid = '', $itemid_mb = '')
{
    if (wp_is_mobile()) {
        if (!empty($itemid_mb)) {
            echo wp_get_attachment_image($itemid_mb, 'banner-mobile-image');
        } else {
            if (!empty($itemid))
                echo wp_get_attachment_image($itemid, 'banner-mobile-image');
            else
                echo '<img src="/wp-content/themes/monatheme/public/images/banner.jpg" alt="" />';
        }
    } else {
        if (!empty($itemid)) {
            echo wp_get_attachment_image($itemid, 'banner-desktop-image');
        } else {
            echo '<img src="/wp-content/themes/monatheme/public/images/banner.jpg" alt="" />';
        }
    }
}

/* ------------------------ get primary term for post ----------------------- */

function get_primary_taxonomy_term($post, $taxonomy = 'category')
{
    if (!$post) {
        $post = get_the_ID();
    }
    $terms        = get_the_terms($post, $taxonomy);
    $primary_term = array();
    if ($terms) {
        $term_display = '';
        $term_slug    = '';
        $term_link    = '';
        $term_id      = '';
        $term_parent  = '';
        if (class_exists('WPSEO_Primary_Term')) {
            $wpseo_primary_term = new WPSEO_Primary_Term($taxonomy, $post);
            $wpseo_primary_term = $wpseo_primary_term->get_primary_term();
            $term               = get_term($wpseo_primary_term);
            if (is_wp_error($term)) {
                if ($terms && !is_wp_error($terms)) {
                    $term_display = $terms[0]->name;
                    $term_slug    = $terms[0]->slug;
                    $term_link    = get_term_link($terms[0]->term_id);
                    $term_id      = $terms[0]->term_id;
                    $term_parent  = $terms[0]->parent;
                } else {
                    $term_display = '';
                    $term_slug    = '';
                    $term_link    = '';
                    $term_id      = '';
                    $term_parent  = '';
                }
            } else {
                $term_display = $term->name;
                $term_slug    = $term->slug;
                $term_link    = get_term_link($term->term_id);
                $term_id      = $term->term_id;
                $term_parent  = $term->parent;
            }
        } else {
            $term_display = $terms[0]->name;
            $term_slug    = $terms[0]->slug;
            $term_link    = get_term_link($terms[0]->term_id);
            $term_id      = $terms[0]->term_id;
            $term_parent  = $terms[0]->parent;
        }
        $primary_term['id']     = $term_id;
        $primary_term['parent'] = $term_parent;
        $primary_term['url']    = $term_link;
        $primary_term['slug']   = $term_slug;
        $primary_term['title']  = $term_display;
    }
    return $primary_term;
}
function get_path_taxonomy_term_root($taxonomy_id, $term_root = [], $taxonomy = 'category')
{
    if (!$taxonomy_id) {
        return;
    }

    $term_obj = get_term_by('id', $taxonomy_id, $taxonomy);

    if ($term_obj) {

        $term_root[$term_obj->slug] = $term_obj->term_id;
        if ($term_obj->parent == '0') {
            return array_reverse($term_root);
        } else {
            return get_path_taxonomy_term_root($term_obj->parent, $term_root, $term_obj->taxonomy);
        }
    } else {
        return $term_root;
    }
}

/* --------------------------- turn acf text to array --------------------------- */

function text_to_array($acf)
{
    $data = [];
    if (!empty($acf)) {
        // explode the value so that each line is a new array piece
        $data = explode("\n", $acf);
        // remove any unwanted white space
        $data = array_map('trim', $data);
    }
    return $data;
}

// /* ------------------------- my check thumb function ------------------------ */

// function check_thumb($itemid = '', $size = 'full', $default = '')
// {
//     if (has_post_thumbnail($itemid)) {
//         echo get_the_post_thumbnail($itemid, $size);
//     } else {
//         if ($default === '')
//             echo '<img src="' . get_template_directory_uri() . '/public/images/thumbnail.jpg" alt="" />';
//         else
//             echo '<img src="' . get_template_directory_uri() . '/public/images/thumbnail-2.jpg" alt="" />';
//     }
// }


function format_number($string)
{

    $number = (float) filter_var($string, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $formatted_number = number_format($number, 0, ',', '.');
    $formatted_string = str_replace($number, $formatted_number, $string);

    return $formatted_string;
}

// function get_social_user_data($user_id) {
//     global $wpdb;

//     // Define the table name with the WordPress prefix
//     $table_name = $wpdb->prefix . 'social_users';

//     // Your query to get data for the specified user ID
//     $user_data = $wpdb->get_results(
//         $wpdb->prepare("SELECT ID, type FROM $table_name WHERE ID = %d", $user_id, OBJECT)
//     );

//     // Check if the user exists
//     if ($user_data) {
//         // Access the data
//         return $user_data;
//     } else {
//         // Return false if the user doesn't exist
//         return false;
//     }
// }

function ht_custom_post_thumbnail( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
    // Đường dẫn ảnh mặc định
    $default_thumbnail = '/wp-content/uploads/2025/10/thumbnail.png';

    if ( $post_thumbnail_id ) {
        $image = wp_get_attachment_image_src( $post_thumbnail_id, 'full' );

        if ( $image ) {
            $width  = $image[1];
            $height = $image[2];
			$post_title = get_the_title( $post_id );

            // Kiểm tra nếu nhỏ hơn 150x150
            if ( $width < 150 || $height < 150 ) {
								$html = '<img src="' . esc_url( $default_thumbnail ) . '" alt="' . esc_attr( $post_title ) . '" />';
            }
        }
    } else {
        // Nếu không có thumbnail thì cũng thay bằng ảnh mặc định
        $html = '<img src="' . esc_url( $default_thumbnail ) . '" alt="' . esc_attr( $post_title ) . '" />';
    }

    return $html;
}
add_filter( 'post_thumbnail_html', 'ht_custom_post_thumbnail', 10, 5 );