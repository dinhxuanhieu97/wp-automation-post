<?php
if (class_exists('Kirki')) {

    function kirki_demo_scripts() 
    {
        wp_enqueue_style('kirki-demo', get_stylesheet_uri(), array(), time());
    }

    add_action('wp_enqueue_scripts', 'kirki_demo_scripts');

    $priority = 1;

    /**
     * Add panel
     */
    // Kirki::add_panel( 'panel_contacts', 
    //     [
    //         'title'     => __( 'Liên hệ', 'mona-admin' ),
    //         'priority'   => $priority++,
    //         'capability' => 'edit_theme_options',
    //     ]
    // );

    /**
     * Add section
     */
    Kirki::add_section( 'section_default', 
        [
            'title'      => __('Thông tin liên hệ', 'mona-admin'),
            'priority'   => $priority++,
            'capability' => 'edit_theme_options',
        ]
    );

    /**
     * Add field
     */
    // Kirki::add_field( 'mona_setting', 
    //     [
    //         'type'        => 'text',
    //         'settings'    => 'section_default_text',
    //         'label'       => __( 'Shortcode from single product', 'mona-admin' ),
    //         'description' => '',
    //         'help'        => '',
    //         'section'     => 'section_default',
    //         'default'     => '',
    //         'priority'    => $priority++,
    //     ]
    // );

    /**
     * Add field 
     */
    // kirki::add_field( 'mona_setting', [
    //     'type'        => 'repeater',
    //     'label'       => __( 'Danh sách liên kết', 'mona-admin' ),
    //     'section'     => 'section_contact_socials',
    //     'priority'    =>  $priority++,
    //     'row_label' => [
    //         'type'  => 'text',
    //         'value' => __( 'Liên kết', 'mona-admin' ),

    //     ],
    //     'button_label' => __( 'Thêm mới', 'mona-admin' ),
    //     'settings'     => 'contact_social_items',
    //     'fields' => [
    //         'icon' => [
    //             'type'        => 'image',
    //             'label'       => __( 'Icon', 'mona-admin' ),
    //             'description' => '',
    //             'default'     => '',
    //         ],
    //         'link' => [
    //             'type'        => 'text',
    //             'label'       => __( 'Link', 'mona-admin' ),
    //             'description' => '',
    //             'default'     => '',
    //         ],
    //     ]
    // ]);

}

if ( ! function_exists ( 'mona_option' ) ) {

    function mona_option($setting, $default = '') {
        echo mona_get_option( $setting, $default );
    }

    function mona_get_option( $setting, $default = '' ) {
        if ( class_exists ( 'Kirki' ) ) {
            $value = $default;
            $options = get_option( 'option_name', array() );
            $options = get_theme_mod( $setting, $default );
            if ( isset ( $options ) ) {
                $value = $options;
            }
            return $value;
        }
        return $default;
    }

}
