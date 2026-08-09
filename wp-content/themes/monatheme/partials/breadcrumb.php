<?php
global $post;
$array = [
    [
        'url'   => get_the_permalink(MONA_PAGE_HOME),
        'title' =>  __('Trang chủ', 'monamedia')
    ],
];
if (wp_get_post_parent_id(get_the_ID())) {
    $parentId = wp_get_post_parent_id(get_the_ID());
    $array[] = [
        'url' => get_permalink($parentId),
        'title' => get_the_title($parentId),
    ];
}
if (is_home()) {
    $array[] = [
        'url' => '',
        'title' => get_the_title(MONA_PAGE_BLOG),
    ];
} elseif (is_singular('post')) {
    $array[] = [
        'url' => get_permalink(MONA_PAGE_BLOG),
        'title' => get_the_title(MONA_PAGE_BLOG),
    ];
    global $post;
    $primary_taxonomy_term = get_primary_taxonomy_term($post->ID, 'category');
    if (!empty($primary_taxonomy_term)) {
        $root = [];
        $root[$primary_taxonomy_term['slug']] = $primary_taxonomy_term['id'];
        $root = get_path_taxonomy_term_root($primary_taxonomy_term['parent'], $root, 'category');
        if (!empty($root)) {
            foreach ($root as $slug => $term_id) {
                $array[] = [
                    'url' => get_term_link($term_id, 'category'),
                    'title' => get_term_by('id', $term_id, 'category')->name,
                ];
            }
        } else {
            $array[] = [
                'url' => $primary_taxonomy_term['url'],
                'title' => $primary_taxonomy_term['title'],
            ];
        }
    }
    $array[] = [
        'url' => '',
        'title' => get_the_title(),
    ];
}
elseif (is_singular('event')) {
    $array[] = [
        'url' => get_permalink(MONA_PAGE_EVENT),
        'title' => get_the_title(MONA_PAGE_EVENT),
    ];
    global $post;
    $primary_taxonomy_term = get_primary_taxonomy_term($post->ID, 'event_cat');
    if (!empty($primary_taxonomy_term)) {
        $root = [];
        $root[$primary_taxonomy_term['slug']] = $primary_taxonomy_term['id'];
        $root = get_path_taxonomy_term_root($primary_taxonomy_term['parent'], $root, 'event_cat');
        if (!empty($root)) {
            foreach ($root as $slug => $term_id) {
                $array[] = [
                    'url' => get_term_link($term_id, 'event_cat'),
                    'title' => get_term_by('id', $term_id, 'event_cat')->name,
                ];
            }
        } else {
            $array[] = [
                'url' => $primary_taxonomy_term['url'],
                'title' => $primary_taxonomy_term['title'],
            ];
        }
    }
    $array[] = [
        'url' => 'javascript:;',
        'title' => get_the_title(),
    ];
} elseif (is_singular('mona_property')) {
    // $array[] = [
    //     'url'   => get_permalink(MONA_PAGE_ABOUT),
    //     'title' => get_the_title(MONA_PAGE_ABOUT),
    // ];
    global $post;
    $primary_taxonomy_term = get_primary_taxonomy_term($post->ID, 'cat_status');
    if (!empty($primary_taxonomy_term)) {
        $root = [];
        $root[$primary_taxonomy_term['slug']] = $primary_taxonomy_term['id'];
        $root = get_path_taxonomy_term_root($primary_taxonomy_term['parent'], $root, 'cat_status');
        if (!empty($root)) {
            foreach ($root as $slug => $term_id) {
                $array[] = [
                    'url' => get_term_link($term_id, 'cat_status'),
                    'title' => get_term_by('id', $term_id, 'cat_status')->name,
                ];
            }
        } else {
            $array[] = [
                'url' => $primary_taxonomy_term['url'],
                'title' => $primary_taxonomy_term['title'],
            ];
        }
    }
    $array[] = [
        'url' => '',
        'title' => get_the_title(),
    ];
    // } elseif (is_category()) {
    //     $array[] = [
    //         'url' => get_permalink(MONA_PAGE_BLOG),
    //         'title' => get_the_title(MONA_PAGE_BLOG),
    //     ];
    //     $array[] = [
    //         'url' => '',
    //         'title' => get_queried_object()->name,
    //     ];
} elseif (is_category() || is_tag()) {
    $array[] = [
        'url' => get_permalink(MONA_PAGE_BLOG),
        'title' => get_the_title(MONA_PAGE_BLOG),
    ];
    $array[] = [
        'url' => '',
        'title' => get_queried_object()->name,
    ];
} elseif (is_tax()) {
    // $array[] = [
    //     'url'   => get_permalink(MONA_PAGE_ABOUT),
    //     'title' => get_the_title(MONA_PAGE_ABOUT),
    // ];
    $current = get_queried_object();
    $root = [];
    if ($current->parent != 0) {
        $root[$current->slug] = $current->term_id;
        $root = get_path_taxonomy_term_root($current->parent, $root, $current->taxonomy);
        if (!empty($root)) {
            foreach ($root as $slug => $term_id) {
                $array[] = [
                    'url' => get_term_link($term_id, $current->taxonomy),
                    'title' => get_term_by('id', $term_id, $current->taxonomy)->name,
                ];
            }
        }
    } else {
        $array[] = [
            'url' => '',
            'title' => $current->name,
        ];
    }
} elseif (is_search()) {
    $array[] = [
        'url' => '',
        'title' => __('Kết quả tìm kiếm:', 'monamedia') . '<span class="keyword"> ' . get_search_query('s') . '</span>',
    ];
} else {
    $array[] = [
        'url' => '',
        'title' => get_the_title(),
    ];
}
$title_primary = $array[count($array) - 1]['title'];
?>
<div class="breadcrumbs">
	<div class="container">
		<div class="breadcrumbs-wrap">
			<ul class="breadcrumbs-list">
				<?php
                if (is_array($array)) {
                    $countArray = count($array);
                    foreach ($array as $key => $item) {
                        $title = $item['title'];
                        $url = $item['url'];
                ?>
				<li class="breadcrumbs-item <?php echo $key == array_key_last($array) ? 'current' : ''; ?>"
					data-aos="fade-right">
					<a href="<?php echo $url ? $url : 'javascript:;'; ?>" class="breadcrumbs-link">
						<?php
                                echo $title;
                                ?>
					</a>
				</li>
				<?php
                    }
                }
                ?>
			</ul>
		</div>
	</div>
</div>