<ul class="control-2-list">
    <?php
    if (wp_is_mobile()) {
    ?>
        <li class="control-2-item"> <a class="control-img" href="fb-messenger://share/?link=<?php echo urlencode(get_the_permalink()); ?>&app_id=1854903944781072"><img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/s5.svg" alt="" /></a></li>
        <li class="control-2-item print"> <a class="control-img" href="http://www.twitter.com/share?url=<?php echo urlencode(get_the_permalink()); ?>" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=400,width=500');
                return false;"><img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/s7.svg" alt="" /></a></li>
        <li class="control-2-item print"> <a class="control-img" href="https://www.linkedin.com/cws/share?url=<?php echo urlencode(get_the_permalink()); ?>&title=<?php the_title(); ?>" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=400,width=500');
            return false;"><img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/s9.svg" alt="" /></a></li>
    <?php
    } else {
    ?>
        <li class="control-2-item"> <a class="control-img" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_the_permalink()); ?>&t=<?php the_title(); ?>" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=400,width=500');
                return false;"><img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/s5.svg" alt="" /></a></li>
        <li class="control-2-item print"> <a class="control-img" href="http://www.twitter.com/share?url=<?php echo urlencode(get_the_permalink()); ?>" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=400,width=500');
                return false;"><img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/s7.svg" alt="" /></a></li>
        <li class="control-2-item print"> <a class="control-img" href="https://www.linkedin.com/cws/share?url=<?php echo urlencode(get_the_permalink()); ?>&title=<?php the_title(); ?>" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=400,width=500');
            return false;"><img src="<?php echo MONA_SITE_TEMPLATE ?>/assets/images/s9.svg" alt="" /></a></li>
    <?php
    }
    ?>
</ul>