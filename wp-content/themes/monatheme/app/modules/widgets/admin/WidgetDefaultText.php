<?php
class WidgetDefaultText extends WP_Widget {

    function __construct() 
    {
        parent::__construct(
            'm_default_text',
            __( 'Mona - Default Text', 'mona-admin' ),
            [
                'description' => __( 'Hiển thị nhập giá trị văn bản ngắn', 'mona-admin' ),
            ]
        );
    }

    public function widget( $args, $instance ) 
    {
        $widget_id = $args['widget_id'];
        $title = isset( $instance['title'] ) ? $instance['title'] : '';
        ?>
        <div class="mona-ft-text-default">
            <p><?php echo esc_attr( $title ) ?></p>
        </div>
        <?php
    }

    public function form( $instance ) 
    {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        } else {
            $title = '';
        }

        if ( class_exists ( 'Mona_Widgets' ) ) {
            mona_render_field_settings(
                [
                    'type'        => 'text',
                    'name'        => $this->get_field_name( 'title' ),
                    'id'          => $this->get_field_id( 'title' ),
                    'value'       => $title,
                    'title'       => __( 'Text', 'mona-admin' ),
                    'placeholder' => __( 'Nhập nội dung văn bản', 'mona-admin' ),
                    'docs'        => false,
                ]
            );
        }
    }

    public function update( $new_instance, $old_instance ) 
    {
        $instance = [];
        if ( class_exists ( 'Mona_Widgets' ) ) { 
            $instance['title'] = mona_update_field_settings( $new_instance['title'] );
        }
        return $instance;
    }

}

add_action( 'widgets_init', function() {
    register_widget( 'WidgetDefaultText' );
});