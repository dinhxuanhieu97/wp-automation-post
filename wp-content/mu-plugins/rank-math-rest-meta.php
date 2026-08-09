<?php
/**
 * mu-plugin: Expose các field SEO của Rank Math qua WordPress REST API.
 *
 * Mặc định Rank Math KHÔNG đăng ký các field `rank_math_*` với `show_in_rest`,
 * nên tool không thể set focus keyword / SEO title / meta description khi
 * POST /wp/v2/posts hoặc /wp/v2/posts/{id}. File này dùng register_post_meta()
 * để mở các field cần thiết cho post type "post" và "page".
 *
 * TÙY CHỌN: chỉ cần file này nếu muốn ghi field SEO Rank Math qua REST
 * (ví dụ site Navycons trong CLAUDE.md mục 3.2). Nếu chỉ cần fix lỗi 401 do
 * LiteSpeed strip header Authorization thì dùng rest-auth-header.php là đủ.
 *
 * Xoá file này = REST không ghi được các field SEO nữa, không ảnh hưởng gì
 * khác tới Rank Math hay giao diện wp-admin.
 */

add_action('init', function () {
    $string_fields = [
        'rank_math_focus_keyword',
        'rank_math_title',
        'rank_math_description',
        'rank_math_canonical_url',
        'rank_math_robots',
        'rank_math_facebook_title',
        'rank_math_facebook_description',
    ];

    $auth_callback = function () {
        return current_user_can('edit_posts');
    };

    foreach (['post', 'page'] as $post_type) {
        foreach ($string_fields as $field) {
            register_post_meta($post_type, $field, [
                'show_in_rest'  => true,
                'single'        => true,
                'type'          => 'string',
                'auth_callback' => $auth_callback,
            ]);
        }

        register_post_meta($post_type, 'rank_math_pillar_content', [
            'show_in_rest'  => true,
            'single'        => true,
            'type'          => 'boolean',
            'auth_callback' => $auth_callback,
        ]);
    }
});
