<?php

/**
 * Class for Beside Nav Search form ajax functionality.
 *
 * @author      Artbees
 * @version     5.0.0
 */

class Mk_Ajax_Search
{
    
    function __construct() {
        global $mk_options;
            $location = get_option(THEME_OPTIONS);
            $option = isset($location['header_search_location']) ? $location['header_search_location'] : '';
        if ($option == 'beside_nav') {
            add_action('wp_ajax_mk_ajax_search', array(&$this,
                'mk_ajax_search'
            ));
            add_action('wp_ajax_nopriv_mk_ajax_search', array(&$this,
                'mk_ajax_search'
            ));
            
            add_action('wp_enqueue_scripts', array(&$this,
                'enqueue'
            ));
        }
    }
    
    function mk_ajax_search() {

        check_ajax_referer('mk-ajax-search-form', 'security');
        
        $search_term = esc_html($_REQUEST['term']);
        $search_term = apply_filters('get_search_query', $search_term);
        
        $search_array = array(
            's' => $search_term,
            'showposts' => 8,
            'post_type' => 'any',
            'post_status' => 'publish',
            'post_password' => '',
            'suppress_filters' => 0
        );
        
        $query = http_build_query($search_array);
        
        $posts = get_posts($query);
        
        $suggestions = array();
        
        global $post;
        foreach ($posts as $post):
            setup_postdata($post);
            $suggestion = array();
            $suggestion['label'] = esc_html($post->post_title);
            $suggestion['link'] = get_permalink();
            $suggestion['date'] = get_the_date();
            $suggestion['image'] = (has_post_thumbnail($post->ID)) ? get_the_post_thumbnail($post->ID, 'thumbnail', array(
                'title' => ''
            )) : '<i>'.Mk_SVG_Icons::get_svg_icon_by_class_name(false, 'mk-moon-pencil').'</i>';
            
            $suggestions[] = $suggestion;
        endforeach;
        
        $response = esc_html($_GET["callback"]) . "(" . json_encode($suggestions) . ")";
        echo $response;
        

        exit;
    }
    
    function enqueue() {
        wp_enqueue_script('jquery-ui-autocomplete');
    }
}

new Mk_Ajax_Search();
