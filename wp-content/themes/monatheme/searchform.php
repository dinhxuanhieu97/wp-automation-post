<div class="header-search">
    <form method="get" id="searchform" class="searchform" action="<?php echo esc_url(home_url('/')); ?>">
        <div class="header-search-wrap">
            <span class="header-search-ic open btn-search">
                <img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/SearchBtn.svg" alt="" />
            </span>
            <span class="header-search-ic close btn-search-exit">
                <img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/SearchEx.svg" alt="" />
            </span>
            <div class="header-search-ip">
                <input type="search" class="search-field" name="s" value="<?php echo get_search_query(); ?>" id="s" placeholder="<?php echo esc_attr_x('Tìm kiếm &hellip;', 'placeholder', 'monamedia'); ?>" />
                <button class="btn-sub" type="submit">
                    <img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/SearchBtn.svg" alt="" />
                </button>
            </div>
        </div>
    </form>
</div>