<?php 
class MonaAdmin {

    public $version = THEME_VERSION;
    public $currentPage;
    public $admin_url;
    protected $menu    = MENU_FILTER_ADMIN;
    protected $setting = FILTER_ADMIN_SETTING;
    protected $pagehook;
    protected $callClass;

    public function __construct() 
    {
        // get current page
        $this->currentPage = isset ( $_GET['tab'] ) ? esc_attr( $_GET['tab'] ) : 'overview';
        // check empty method
        if ( $this->currentPage == '' ) {
            $this->currentPage = 'overview';
        }
        // set admin url
        $this->admin_url = get_admin_url() . 'themes.php?page=' . $this->menu;
        // class callback
        $this->callClass = $this->setting . ucfirst( $this->currentPage );
    }

	public function __init() 
    {
        // hook admin
        add_action( 'admin_menu', [ $this, 'register_submenu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'resgsiter_scripts' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

    public function register_admin_pages() 
    {
        return apply_filters( 'mona_theme_register_admin_pages',
            [
                'overview' => $this->setting . 'Overview',
                'notfound' => $this->setting . 'NotFound',
                'buttons'  => $this->setting . 'Buttons',
            ]
        );
    }

	public function register_submenu() 
    {
        global $current_user;
        if ( $current_user->user_login == 'admin' ) {
            add_submenu_page( 
                'themes.php', 
                __( 'Cài đặt', 'mona-admin' ), 
                __( 'Cài đặt', 'mona-admin' ), 
                'manage_options', $this->menu,  [ $this, 'resgsiter_template' ]
            );
        }
        
        if ( $current_user->user_login != 'admin' ) {
            // remove_menu_page( 'tools.php' ); 
            // remove_submenu_page( 'options-general.php', 'options-privacy.php' );  
            // remove_submenu_page( 'index.php', 'update-core.php' );
            // remove_all_actions( 'admin_notices' );
            // add_filter('acf/settings/show_admin', '__return_false');
            // remove_menu_page( 'plugins.php' );
            
            // remove all menu page except pages, posts, media, dashboard
            // global $menu;
            // $allowed = [
            //     __('Dashboard'),
            //     __('Posts'),
            //     __('Media'),
            //     __('Pages'),
            // ];
            // foreach ( $menu as $key => $item ) {
            //     if ( ! in_array( $item[0], $allowed ) ) {
            //         remove_menu_page( $item[2] );
            //     }
            // }
        }
	}

    public function resgsiter_scripts() 
    {
        wp_enqueue_style( 'mona-style-global-template', get_template_directory_uri() . '/core/admin/assets/css/admin-global.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'mona-style-toolbar-template', get_template_directory_uri() . '/core/admin/assets/css/admin-toolbar.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'mona-style-styling-template', get_template_directory_uri() . '/core/admin/assets/css/admin-styling.css', array(), $this->version, 'all' );
        wp_enqueue_script( 'mona-script-global-template', get_template_directory_uri() . '/core/admin/assets/js/admin-global.js', array(), $this->version, true );
        // get enqueue scripts
        if ( class_exists ( $this->callClass ) ) {
            (new $this->callClass())->__resgsiter_scripts();
        }
        if ( get_current_screen()->id == 'appearance_page_mona-filter-admin' ) {
            $settings = wp_enqueue_code_editor( array( 'type' => 'text/x-php' ) );
            if ( $settings ) {
                wp_add_inline_script(
                    'code-editor',
                    sprintf(
                        'jQuery( function() { wp.codeEditor.initialize( "header_script", %s ); } );',
                        wp_json_encode( $settings )
                    )
                );
            }
        }
    }

    public function register_settings() 
    {
        if ( ! empty ( $admin_pages = $this->register_admin_pages() ) ) {
            foreach ( $admin_pages as $key => $className ) {
                if ( class_exists ( $className ) ) {
                    $callBack = (new $className());
                    if ( method_exists( (new $callBack()), '__resgsiter_settings' ) ) {
                        if ( class_exists( 'ACF' ) ) {
                            acf_form_head();
                        }
                        (new $callBack())->__resgsiter_settings();
                    }
                }
            }
        }
    }

	public function resgsiter_template() 
    {
        ?>
        <div id="mona-body-content">
            <?php
            // get header
            require_once( get_template_directory() . '/core/admin/partials/admin-header.php' );
            // get main content
            if ( class_exists ( $this->callClass ) ) {
                $callBack = (new $this->callClass());
                ?>
                <div class="mona-admin-headerbar">
                    <h1><?php echo $callBack->__title() ?></h1>
                </div>
                <div class="mona-admin-main">
                    <div class="wrap">
                        <form id="mona-form-settings" method="POST" action="<?php echo $callBack->__action() ?>">
                            <?php 
                            if ( method_exists( $callBack, '__option_page' ) ) {
                                $option_page = $callBack->__option_page();
                                // get input hidden
                                settings_fields( $option_page );
                                do_settings_sections( $option_page );
                                // call update / submit POST
                                $this->update_options();
                            }
                            ?>
                            <div id="mona-main-template">
                                <?php $callBack->__template() ?>
                            </div>
                            <?php 
                            // get footer
                            require_once( get_template_directory() . '/core/admin/partials/admin-footer.php' );
                            ?>
                        </form>
                    </div>
                </div>
                <?php 
            }
            ?>
        </div>
        <?php 
	}

    protected function update_options()
    {
        if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            if ( class_exists ( $this->callClass ) ) {
                $callBack = (new $this->callClass());
                $optionss = $callBack->__resgsiter_options();
                if ( is_array ( $optionss ) ) {
                    foreach ( $optionss as $key => $option ) {
                        update_option( $callBack->__option_name( $key ), $callBack->__get_submit_value( $key ) );
                    }
                }
            }
        }
    }

}