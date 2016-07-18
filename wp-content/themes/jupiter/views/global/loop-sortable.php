<?php
    $mode = !empty($view_params['sortable_mode']) ? $view_params['sortable_mode'] : 'ajax';
    $data_config[] = 'data-mk-component="Sortable"';
    $data_config[] = 'data-sortable-config=\'{"container":"'.$view_params['container'].'", "item":"'.$view_params['item'].'", "mode":"'.$mode.'"}\'';
?>

<?php $requested_term = isset($_REQUEST['term']) ? $_REQUEST['term'] : ''; ?> 
<header class="sortable-<?php echo $view_params['style']; ?>-style sortable-id-<?php echo $view_params['uniqid']; ?> <?php echo (isset($view_params['custom_class']) ? $view_params['custom_class'] : ''); ?> js-el" id="mk-filter-portfolio"<?php echo (isset($view_params['custom_style']) ? $view_params['custom_style'] : ''); ?> <?php echo implode(' ', $data_config); ?>>
    <div class="mk-grid">
        <ul class="align-<?php echo $view_params['align']; ?>">
            <?php
                $terms = array();
                if ($view_params['categories'] != '') {
                    foreach (explode(',', $view_params['categories']) as $term_slug) {
                        $terms[] = get_term_by('slug', $term_slug, $view_params['post_type'].'_category');
                    }
                    /* 
                        We add categories to the All categories item
                        so when sending ajax request for all categories 
                        we tell the php code that the query is not all available
                        categories but selective ones picked from shortcode params

                    */
                    $all_cats_items = $view_params['categories'];

                } else {
                    $terms = get_terms($view_params['post_type'].'_category', 'hide_empty=1&suppress_filters=0');

                    // Star means all categories available
                    $all_cats_items = '*';
            } ?>
            <li><a class="<?php echo (empty($requested_term) ? 'current' : ''); ?>" data-filter="<?php echo $all_cats_items; ?>" href="<?php echo get_permalink(); ?>"><?php _e('All', 'mk_framework'); ?></a></li>
            <?php foreach($terms as $term) { ?>
            <li><a class="<?php echo (($term->slug ==  $requested_term) ? 'current' : ''); ?>" data-filter="<?php echo $term->slug; ?>" href="<?php echo get_permalink(); ?>?term=<?php echo $term->slug; ?>"><?php echo  $term->name; ?></a></li>
            <?php } ?>
            <div class="clearboth"></div>
        </ul>
        <div class="clearboth"></div>
  </div>
</header>