<?php 
/*
Plugin Name: Learndash Topic Categories
Description: This plugin adds categories to the learndash plugin.
Plugin URI: http://www.jonbrantingham.com
Author: Jon Brantingham
Author URI: http://www.jonbrantingham.com
Version: 1.0
License: GPL2
*/

/*

    Copyright (C) Year  Author  Email

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Register Topics admin columns on post list view
 */
function add_tag_init_topics()	{
	$tag_args = array( 'taxonomies' => array( 'post_tag', 'category' ) );
	register_post_type( 'sfwd-topic',$tag_args ); //Tag arguments for $post_type='sfwd-topics'
}

add_action( 'init', 'add_tag_init_topics' ); //Initialise the tagging capability here