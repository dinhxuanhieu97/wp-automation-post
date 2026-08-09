<?php 
add_action( 'wp_ajax_mona_ajax_loadmore_events',  'mona_ajax_loadmore_events' ); // login
add_action( 'wp_ajax_nopriv_mona_ajax_loadmore_events',  'mona_ajax_loadmore_events' ); // no login
function mona_ajax_loadmore_events() {
    $form = array();
    parse_str( $_POST['formdata'], $form );
    $paged              = $_POST['paged'] ? $_POST['paged'] : 1;
    $action             = $_POST['action_layout'] ? $_POST['action_layout'] : 'reload';
    $flagAction         = $_POST['action_flag'];

    $action_return      = $action;
    $post_type          = $form['post_type'] ? $form['post_type'] : 'event';
    $posts_per_page     = $form['posts_per_page'] ? $form['posts_per_page'] : 4;
    $offset             = ( $paged - 1 ) * $posts_per_page;
    $order              = 'DESC';
    $argsEvent = array(
    'post_type' => $post_type,
    'post_status' => 'publish',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'offset' => $offset,
    'meta_query' => [
      'relation' => 'AND',
    ],
    'tax_query' => [
      'relation'=>'AND',
    ]
    );


    if( $flagAction == 'false' && $action == 'loadmore' ){
    $action_return = 'reload';
    }

    if( is_array( $form['taxonomies'] ) && !empty( $form['taxonomies'] ) ){
    foreach ($form['taxonomies'] as $taxonomy => $slug) {
      if( $slug != '' ){
          $argsEvent['tax_query'][] =  array(
              'taxonomy'  => $taxonomy,
              'field'     => 'slug',
              'terms'     => $slug,
              'operator'  => 'IN'
          );
      }
    }
    }

    ob_start(); 
    $list_posts = new WP_Query( $argsEvent );
    if( $list_posts->have_posts() ){ ?>
<?php while( $list_posts->have_posts() ){
              $list_posts->the_post();
              if( $post_type == 'event' ){
                  $class_item = '';
              }
              ?>
<div class="events_new--item d-item d-4">
	<?php if ($post_type == 'event') {
                    /**
                    * GET TEMPLATE PART
                    * event
                    */
                    $slug = '/partials/loop/box';
                    $name = 'event';
                    echo get_template_part( $slug, $name );
                
            } ?>
</div>
<?php } wp_reset_query(); ?>

<?php if( $action == 'loadmore' ){ ?>
<?php if( $paged < $list_posts->max_num_pages ){ ?>
<a class="btn cl-2 eventLoadMoreJS is-loading-btn" href="javascript:;" data-paged="<?php echo ++$paged; ?>">
	<span class="btn-text">
		<?php echo __('Xem thêm tin tức', 'monamedia'); ?></span>
</a>
<?php } ?>

<?php } ?>

<?php } else{ ?>

<div class="m-box-empty">
	<span class="icon-empty">
		<img src="/wp-content/themes/monatheme/public/images/empty-box.png" alt="">
	</span>
	<span class="m-txt-empty"><?php echo __('Đang cập nhật bài viết', 'monamedia'); ?></span>
</div>

<?php } ?>

<?php if ($post_type == 'event') {
      $class_scroll = 'newsn-list';
    }
    wp_send_json_success( 
    [
      'title'         => __( 'Thông báo!', 'monamedia' ),
      'message'       =>  __( 'Load thêm thành công!', 'monamedia' ),
      'title_close'   =>  __('Đóng', 'monamedia'),
      'posts_html'    => ob_get_clean(),
      'argsEvent'     => $argsEvent,
      'scroll'        => $class_scroll,
      'action_return' => $action_return
    ]
    );
    wp_die();

}

add_action( 'wp_ajax_mona_ajax_loadmore_post',  'mona_ajax_loadmore_post' ); // login
add_action( 'wp_ajax_nopriv_mona_ajax_loadmore_post',  'mona_ajax_loadmore_post' ); // no login
function mona_ajax_loadmore_post() {
    $form = array();
    parse_str( $_POST['formdata'], $form );
    $paged              = $_POST['paged'] ? $_POST['paged'] : 1;
    $action             = $_POST['action_layout'] ? $_POST['action_layout'] : 'reload';
    $flagAction         = $_POST['action_flag'];

    $action_return      = $action;
    $post_type          = $form['post_type'] ? $form['post_type'] : 'event';
    $posts_per_page     = $form['posts_per_page'] ? $form['posts_per_page'] : 10;
    $offset             = ( $paged - 1 ) * $posts_per_page;
    $order              = 'DESC';
    $argsPost = array(
    'post_type' => $post_type,
    'post_status' => 'publish',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'offset' => $offset,
    'meta_query' => [
      'relation' => 'AND',
    ],
    'tax_query' => [
      'relation'=>'AND',
    ]
    );


    if( $flagAction == 'false' && $action == 'loadmore' ){
    $action_return = 'reload';
    }

    if( is_array( $form['taxonomies'] ) && !empty( $form['taxonomies'] ) ){
    foreach ($form['taxonomies'] as $taxonomy => $slug) {
      if( $slug != '' ){
          $argsPost['tax_query'][] =  array(
              'taxonomy'  => $taxonomy,
              'field'     => 'slug',
              'terms'     => $slug,
              'operator'  => 'IN'
          );
      }
    }
    }

    ob_start(); 
    $list_posts = new WP_Query( $argsPost );
    if( $list_posts->have_posts() ){ ?>
<?php while( $list_posts->have_posts() ){
              $list_posts->the_post();
              if( $post_type == 'post' ){
                  $class_item = '';
              }
              ?>
<div class="events_new--item d-item d-4">
	<?php if ($post_type == 'post') {
                    /**
                    * GET TEMPLATE PART
                    * event
                    */
                    $slug = '/partials/loop/box';
                    $name = 'blog';
                    echo get_template_part( $slug, $name );
                
            } ?>
</div>
<?php } wp_reset_query(); ?>

<?php if( $action == 'loadmore' ){ ?>
<?php if( $paged < $list_posts->max_num_pages ){ ?>
<a class="btn cl-2 eventLoadMoreJS is-loading-btn" href="javascript:;" data-paged="<?php echo ++$paged; ?>">
	<span class="btn-text">
		<?php echo __('Xem thêm tin tức', 'monamedia'); ?></span>
</a>
<?php } ?>

<?php } ?>

<?php } else{ ?>

<div class="m-box-empty">
	<span class="icon-empty">
		<img src="/wp-content/themes/monatheme/public/images/empty-box.png" alt="">
	</span>
	<span class="m-txt-empty"><?php echo __('Đang cập nhật bài viết', 'monamedia'); ?></span>
</div>

<?php } ?>

<?php if ($post_type == 'post') {
      $class_scroll = 'newsn-list';
    }
    wp_send_json_success( 
    [
      'title'         => __( 'Thông báo!', 'monamedia' ),
      'message'       =>  __( 'Load thêm thành công!', 'monamedia' ),
      'title_close'   =>  __('Đóng', 'monamedia'),
      'posts_html'    => ob_get_clean(),
      'argsEvent'     => $argsEvent,
      'scroll'        => $class_scroll,
      'action_return' => $action_return
    ]
    );
    wp_die();

}