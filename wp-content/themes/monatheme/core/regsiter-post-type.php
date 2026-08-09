<?php 
function mona_regsiter_event_post_types() {

  $posttype_event = [
    'labels' => [
        'name'          => 'Sự kiện',
        'singular_name' => 'Sự kiện',
        'all_items'     => __( 'Tất cả bài viết', 'mona-admin' ),
        'add_new'       => __( 'Viết bài mới', 'mona-admin' ),
        'add_new_item'  => __( 'Bài viết mới', 'mona-admin' ),
        'edit_item'     => __( 'Chỉnh sửa bài viết', 'mona-admin' ),
        'new_item'      => __( 'Thêm bài viết', 'mona-admin' ),
        'view_item'     => __( 'Xem bài viết', 'mona-admin' ),
        'view_items'    => __( 'Xem bài viết', 'mona-admin' ),
    ],
    'description' => 'Thêm bài viết',
    'supports'    => [
        'title',
        'editor',
        'author',
        'thumbnail',
        'comments',
        'revisions',
        'custom-fields',
        'excerpt',
    ],
    'taxonomies'   => array( 'event_cat', 'post_tag' ),
    'hierarchical' => false,
    'show_in_rest' => true,
    'public'       => true,
    'has_archive'  => true,
    'rewrite'      => [
        'slug' => 'chi-tiet-su-kien',
        'with_front' => true
    ],
    'show_ui'             => true,
    'show_in_menu'        => true,
    'show_in_nav_menus'   => true,
    'show_in_admin_bar'   => true,
    'menu_position'       => 5,
    'menu_icon'           => MONA_THEME_PATH . '/public/images/calendar.png',
    'can_export'          => true,
    'has_archive'         => true,
    'exclude_from_search' => true,
    'publicly_queryable'  => true,
    'capability_type'     => 'post'
  ];

  register_post_type( 'event', $posttype_event );

  $tax_event = [
    'labels' => [
        'name'              => __( 'Sự kiện', 'mona-admin' ),
        'singular_name'     => __( 'Sự kiện', 'mona-admin' ),
        'search_items'      => __( 'Tìm kiếm', 'mona-admin' ),
        'all_items'         => __( 'Tất cả', 'mona-admin' ),
        'parent_item'       => __( 'Danh mục Sự kiện', 'mona-admin' ),
        'parent_item_colon' => __( 'Danh mục Sự kiện', 'mona-admin' ),
        'edit_item'         => __( 'Chỉnh sửa', 'mona-admin' ),
        'add_new'           => __( 'Thêm mới', 'mona-admin' ),
        'update_item'       => __( 'Cập nhật', 'mona-admin' ),
        'add_new_item'      => __( 'Thêm mới', 'mona-admin' ),
        'new_item_name'     => __( 'Thêm mới', 'mona-admin' ),
        'menu_name'         => __( 'Danh mục Sự kiện', 'mona-admin' ),
    ],
    'hierarchical' => true,
    'show_admin_column' => true,
    'show_in_rest' => true,
    'has_archive' => true,
    'public' => true,
    'rewrite' => array(
        'slug' => 'danh-muc-su-kien',
        'with_front' => true
    ),
    'capabilities' => [
        'manage_terms' => 'publish_posts',
        'edit_terms'   => 'publish_posts',
        'delete_terms' => 'publish_posts',
        'assign_terms' => 'publish_posts',
    ],
  ];
  register_taxonomy( 'event_cat', 'event', $tax_event );

  flush_rewrite_rules();
}
add_action( 'init', 'mona_regsiter_event_post_types' );