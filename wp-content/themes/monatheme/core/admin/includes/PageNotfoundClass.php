<?php 
if ( class_exists ( 'MonaAdmin' ) ) {
    class MonaSettingNotFound extends MonaAdmin {

        protected $page = 'notfound';
    
        public function __resgsiter_scripts() 
        {
            // loading css
            wp_enqueue_style( 'mona-style-'.esc_attr( $this->page ).'-template', get_template_directory_uri() . '/core/admin/assets/css/admin-'.esc_attr( $this->page ).'.css', array(), $this->version, 'all' );
            // loading js
            wp_enqueue_script( 'mona-script-'.esc_attr( $this->page ).'-template', get_template_directory_uri() . '/core/admin/assets/js/admin-'.esc_attr( $this->page ).'.js', array(), $this->version, true );
            wp_localize_script( 'mona-script-'.esc_attr( $this->page ).'-template', 'mona_admin_ajax', 
                [
                    'ajaxURL'  => admin_url('admin-ajax.php'),
                    'adminURL' => get_admin_url(),
                    'siteURL'  => get_site_url(),
                ]
            );
        }

        public function __resgsiter_settings() 
        {
            $resgsiter_options = $this->__resgsiter_options();
            if ( ! empty ( $resgsiter_options ) ) {
                foreach ( $resgsiter_options as $key => $option ) {
                    register_setting( $this->__option_page(), $this->__option_name( $key ), $option ); 
                }
            }
        }

        public function __resgsiter_options()
        {
            return [
                'template' => [
                    'type'              => 'string', 
                    'sanitize_callback' => 'sanitize_text_field',
                    'default'           => 'default',
                ],
            ];
        }

        public function __title() 
        {
            return __( '404', 'mona-admin' );
        }

        public function __icon() 
        {
            return '<span class="dashicons dashicons-admin-page"></span>';
        }

        public function __link() 
        {
            return esc_url( $this->admin_url ) . '&tab=' . $this->page;
        }

        public function __classes() 
        {
            // default class
            $classes = 'toolbar-menu-item';
            // check current page
            if ( $this->currentPage === $this->page ) {
                $classes .= ' current-page';
            }
            // result string class html
            return esc_attr( $classes );
        }

        public function __action()
        {
            return esc_url( $this->admin_url ) . '&tab=' . $this->page;
        }

        public function __field_name( $name = '' ) 
        {
            if ( empty ( $name ) ) {
                return;
            }
            // ressult string
            return $this->page . '['.esc_attr( $this->__get_key( $name ) ).']';
        }

        public function __field_value( $name = '', $default = false ) 
        {
            $cache_key   = $this->__option_name( $name );
            $cache_value = wp_cache_get( $cache_key, $this->__option_page() );
            if ( false === $cache_value ) { 
                $cache_value = get_option( $cache_key, $default );
                wp_cache_set( $cache_key, $cache_value, $this->__option_page(), HOUR_IN_SECONDS );
            }
            // ressult string
            return $cache_value;
        }

        public function __option_name( $name = '' ) 
        {
            if ( empty ( $name ) ) {
                return '';
            }
            return 'mona' . '_' . $this->page . '_' . $name;
        }

        public function __option_page() 
        {
            return 'mona' . '_' . $this->page;
        }

        public function __get_submit_value( $name = '' ) 
        {
            if ( empty ( $name ) ) {
                return false;
            }
            // get request
            return isset ( $_POST[$this->page][$this->__get_key( $name )] ) ? $_POST[$this->page][$this->__get_key( $name )] : '';
        }

        public function __get_reuqest_value( $name = '', $formdata = [] ) 
        {
            if ( empty ( $name ) ) {
                return false;
            }
            // get request
            return isset ( $formdata[$this->page][$this->__get_key( $name )] ) ? $formdata[$this->page][$this->__get_key( $name )] : '';
        }

        public function __get_key( $name = '' ) 
        {
            if ( empty ( $name ) ) {
                return '';
            }
            return esc_attr( $name );
        }

        public function __nonce_key() 
        {
            return $this->__option_page() . '-options';
        }
        
        public function __template() 
        {
            $template = $this->__field_value( 'template', 'default' );
            ?>
            <div class="mona-row setN mona--adminNotfound">
                <label class="mona-col-xl field-radio" id="<?php echo $this->__option_name( 'default' ) ?>">
                    <input type="radio" id="<?php echo $this->__option_name( 'default' ) ?>" 
                        name="<?php echo $this->__field_name( 'template' ) ?>" 
                        class="form-radio" value="default" <?php echo checked( $template, 'default' ) ?> />
                    <div class="mona-card">
                        <div class="card-header">
                            <div class="card-title"><?php echo __( 'Mặc định', 'mona-admin' ) ?></div>
                        </div>
                        <div class="card-body">
                            <div class="form-field">
                                <div class="preview-image">
                                    <img src="<?php echo get_template_directory_uri() . '/core/admin/assets/images/preview-image-notfound.jpg' ?>" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </label>
                <label class="mona-col-xl field-radio" id="<?php echo $this->__option_name( '22052023' ) ?>">
                    <input type="radio" id="<?php echo $this->__option_name( '22052023' ) ?>" 
                        name="<?php echo $this->__field_name( 'template' ) ?>" 
                        class="form-radio" value="22052023" <?php echo checked( $template, '22052023' ) ?> />
                    <div class="mona-card">
                        <div class="card-header">
                            <div class="card-title"><?php echo __( '22052023', 'mona-admin' ) ?></div>
                        </div>
                        <div class="card-body">
                            <div class="form-field">
                                <div class="preview-image">
                                    <img src="<?php echo get_template_directory_uri() . '/core/admin/assets/images/preview-image-notfound-22052023.jpg' ?>" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </label>
                <label class="mona-col-xl field-radio" id="<?php echo $this->__option_name( '14092023' ) ?>">
                    <input type="radio" id="<?php echo $this->__option_name( '14092023' ) ?>" 
                        name="<?php echo $this->__field_name( 'template' ) ?>" 
                        class="form-radio" value="14092023" <?php echo checked( $this->__field_value( 'template' ), '14092023' ) ?> />
                    <div class="mona-card">
                        <div class="card-header">
                            <div class="card-title"><?php echo __( '14092023', 'mona-admin' ) ?></div>
                        </div>
                        <div class="card-body">
                            <div class="form-field">
                                <div class="preview-image">
                                    <img src="<?php echo get_template_directory_uri() . '/core/admin/assets/images/preview-image-notfound-14092023.png' ?>" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </label>
            </div>
            <?php 
        }

        public function __front_template()
        {
            $template = $this->__field_value( 'template', 'default' );
            ?>
            <div id="notfound_template" class="mona-notfound-template notfound-<?php echo $template ?>">
                <?php get_template_part( 'partials/templates/404/notfound', $this->__field_value( 'template', false ) ); ?>
            </div>
            <?php 
        }

        public function __front_styles()
        {
            // loading css
            $template   = $this->__field_value( 'template', 'default' );
            $style_path  = 'notfound';
            if ( $template && $template != 'default' ) {
                $style_path .= '-' . esc_attr( $template );
            }
            wp_enqueue_style( 'mona-style-'.esc_attr( $this->page ).'-template', get_template_directory_uri() . '/public/css/404/'.$style_path.'.css', array(), $this->version );
        }

    }
}