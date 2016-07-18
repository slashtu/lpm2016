<?php
extract( shortcode_atts( array(
	'el_class'    	=> '',
	"style"     	=> 'quote-style',
	"font_size_combat"=> 'false',
	"text_size"   	=> '12',
	"font_family"   	=> '',
	'animation'   	=> '',
	"font_type"   	=> '',
), $atts ) );
Mk_Static_Files::addAssets('mk_blockquote');
