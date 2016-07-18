<?php


if(!function_exists('mk_create_custom_post_type')) {
	function mk_create_custom_post_type() {

		/*
		*
		* Function to create new custom post type
		* Change "sample-post-type" to the your post type name (e.g. books, tickets)
		*/
		Mk_Register_Custom_Post_Type('sfwd-topic', 
			
			// post type supports : https://codex.wordpress.org/Function_Reference/post_type_supports
			$supports = array(
			    'title',
			    'editor',
			    'thumbnail',
			    'page-attributes',
			), 

			$args = array(
			    'menu_icon' => 'dashicons-align-center', // Find your prefered icon from here : https://developer.wordpress.org/resource/dashicons/#admin-collapse
			    'show_in_nav_menus' => false,
			    'exclude_from_search' => true,
			),

			// Set it false if you do not want to have single page
			$singular = true
		);



		/*
		*
		* Function to create custom taxonomoy
		* change "sample-post-type_category" to your prefered taxonomy name
		* Change "sample-post-type" to the custom post type name you would like to assign this taxonomy
		*/
		Mk_Register_custom_taxonomy('post_tag', 'sfwd-topic', 
			array(
	        'rewrite' => array(
			            'slug' => _x('post_tag', 'URL slug', 'mk_framework') ,
			            'with_front' => FALSE
			        	)
			)
		);

	}
}

add_action('init', 'mk_create_custom_post_type');