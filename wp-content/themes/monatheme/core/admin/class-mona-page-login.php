<?php
if ( class_exists ( 'MonaAdmin' ) ) {
    class MonaFilterPageLogin extends MonaAdmin {
    
        public function __init() 
        {
            add_action( 'login_enqueue_scripts', [ $this, 'mona_style_login_template' ] );
        }
    
        public function mona_style_login_template() 
        {
            wp_enqueue_media();
            // loading css
            wp_enqueue_style( 'mona-style-login-template', get_template_directory_uri() . '/core/admin/assets/css/page-login.css', array(), $this->version, 'all' );
        }
    }
    
    (new MonaFilterPageLogin())->__init();
}