<div class="not-found-wrapper">
    <span class="not-found-title"><?php _e('WHOOPS!', 'mk_framework'); ?></span>
    <span class="not-found-subtitle"><?php _e('404', 'mk_framework'); ?></span>
    <section class="widget widget_search"><p><?php _e('It looks like you are lost! Try searching here', 'mk_framework'); ?></p>
        <form class="mk-searchform" method="get" id="searchform" action="<?php echo home_url(); ?>">
            <input type="text" class="text-input" placeholder="<?php _e('Search site', 'mk_framework'); ?>" value="" name="s" id="s" />
            <i class="mk-searchform-icon"><?php Mk_SVG_Icons::get_svg_icon_by_class_name(true,'mk-icon-search'); ?><input value="" type="submit" class="search-button" type="submit" /></i>
        </form>
    </section>
    <div class="clearboth"></div>
</div>