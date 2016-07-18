<?php
extract( shortcode_atts( array(
	'title' 			=> '',
	'show_as' 			=> 'slideshow',
	'column' 			=> 3,
	'style' 			=> 'avantgarde',
	'count'				=> 10,
	'orderby'			=> 'date',
	'testimonials' 		=> '',
	'categories'             => '',
	"animation_speed" 	=> 500,
	"slideshow_speed" 	=> 7000,
	'order'				=> 'ASC',
	'skin' 				=> 'dark',
	"el_class"			=> '',
	'animation' 		=> '',
), $atts ) );
Mk_Static_Files::addAssets('mk_testimonials');
