<?php
function lds_build_stylesheet() {

	// Open the compiled file
	$lds_compiled_styles = fopen( LDS_PATH . 'assets/css/learndash-compiled.css', 'w' ) or lds_switch_stylesheet_method();
	
	fopen( 'readme.txt' , 'w' ) or die(print_r(error_get_last(),true));
	
	$styles = lds_generate_styles();
	
	fwrite( $lds_compiled_styles , $styles );
	
	fclose( $lds_compiled_styles );
	
}

function lds_switch_stylesheet_method() { 
	
	echo 'Could not open file!';

	// Switch the method from generated to inline
	update_option( 'ldvc_add_method' , 'inline' );

}

function lds_generate_styles() { 
	
	ob_start();

	$lds_heading_bg 			= get_option('lds_heading_bg');
	$lds_heading_txt 			= get_option('lds_heading_txt');
	$lds_row_bg 				= get_option('lds_row_bg');
	$lds_row_bg_alt 			= get_option('lds_row_bg_alt');
	$lds_row_txt 				= get_option('lds_row_txt');

	$lds_sub_row_bg 			= get_option('lds_sub_row_bg');
	$lds_sub_row_bg_alt 		= get_option('lds_sub_row_bg_alt');
	$lds_sub_row_txt 			= get_option('lds_sub_row_txt');

	$lds_button_bg 				= get_option('lds_button_bg');
	$lds_button_txt 			= get_option('lds_button_txt');
	$lds_complete_button_bg 	= get_option('lds_complete_button_bg');
	$lds_complete_button_txt 	= get_option('lds_complete_button_txt');

	$lds_progress 				= get_option('lds_progress');
	$lds_links 					= get_option('lds_links');
	$lds_complete 				= get_option('lds_complete');

	$lds_widget_bg 				= get_option('lds_widget_bg');
	$lds_widget_header_bg 		= get_option('lds_widget_header_bg');
	$lds_widget_header_txt 		= get_option('lds_widget_header_txt');
	$lds_widget_txt 			= get_option('lds_widget_txt');

	$lds_checkbox_incomplete 	= get_option('lds_checkbox_incomplete');
	$lds_checkbox_complete 		= get_option('lds_checkbox_complete');

	$lds_arrow_incomplete 		= get_option('lds_arrow_incomplete');
	$lds_arrow_complete 		= get_option('lds_arrow_complete');

	$lds_border_radius 			= get_option('lds_button_border_radius');

	$lds_icon_style 			= get_option('lds_icon_style');
	
	$lds_open_css 				= get_option('lds_open_css');
	
	$widget_title				= get_option( 'lds_widget_title' , '.widget-title');
	

	echo file_get_contents( LDS_PATH . 'assets/css/ldvc-base.css' );

	if(get_option('lds_skin') == 'modern'):
		
		$css = file_get_contents( LDS_PATH . 'assets/css/modern.css' );
		
	endif;

	if(get_option('lds_skin') == 'rustic'):
		
		$css = file_get_contents( LDS_PATH . 'assets/css/rustic.css' ); 
		
	endif;

	if(get_option('lds_skin') == 'classic'):
		
		$css = file_get_contents( LDS_PATH . 'assets/css/classic.css' ); 
		
	endif;

	if(get_option('lds_skin') == 'playful'):
		$css = file_get_contents( LDS_PATH . 'assets/css/playful.css' ); 
	endif;

	if(get_option('lds_skin') == 'upscale'):
		$css = file_get_contents( LDS_PATH . 'assets/css/upscale.css' );
	endif;

	$css = str_replace( '.widget-title' , $widget_title , $css );

	echo $css;

	if(get_option('lds_animation') == 'yes') { 
		echo file_get_contents( LDS_PATH . 'assets/css/animation.css' );
	}

	// ========================
	// = Custom Border Radius =
	// ========================

	if(!empty($lds_border_radius)) { ?>
	
		/* CUSTOM BORDER RADIUS */
	
		.wpProQuiz_button,
		.wpProQuiz_button:hover,
		#uploadfile_btn,
		input.wpProQuiz_button2,
		input.wpProQuiz_button,
		input.wpProQuiz_button2:hover,
		input.wpProQuiz_button:hover,
		.btn-join,
		#btn-join,
		a#quiz_continue_link,
		#learndash_mark_complete_button,
		#learndash_next_prev_link a {
			border-radius: <?php echo $lds_border_radius; ?>px !important;
		}
	
	
	<?php } 

	// ================
	// = Custom Icons =
	// ================
 
	if( ( !empty( $lds_icon_style ) ) && ( $lds_icon_style != 'default' ) ) {
		
		echo '/* ICON STYLE IS '. $lds_icon_style .' */';
	
		$styles = array(
			'modern',
			'minimal',
			'chunky',
			'playful'
		);
	
		echo file_get_contents( LDS_PATH . 'icons/reset.css' ); ?>
	
		<?php 
	
		foreach($styles as $style) { 
		
			if($lds_icon_style == $style) { 
					
				echo file_get_contents( LDS_PATH . 'assets/css/icons/' . $style . '.css' );
		
			}
	
		}

	} ?>
 
	/*
	 * Global Styling for all styles
	 *
	 *
	 */
 
	 .widget_ldcourseprogress ul,
	 .widget_sfwd-lessons-widget ul,
	 .widget_ldcoursenavigation ul,
	 .widget_ldcourseinfo ul,
	 .widget_sfwd-certificates-widget ul,
	 .widget_sfwd-courses-widget ul {
	 	list-style: none;
		margin: 0;
		padding: 0 15px;
	 }
 
	 #ld_course_info,
	 #course_navigation { 
	 	padding: 0 15px 15px;
	 }
 

	/* 
	 * Table Heading Backgrounds 
	 *
	 */

	#lesson_heading,
	#quiz_heading,
	#learndash_lessons #lesson_heading,
	#learndash_profile .learndash_profile_heading,
	#learndash_quizzes #quiz_heading,
	#learndash_lesson_topics_list div > strong {
		background-color:<?php echo $lds_heading_bg; ?> !important;
	}

	/* 
	 * Heading Text
	 * 
	 */ 

	#lesson_heading span,
	#quiz_heading span,
	#learndash_lesson_topics_list div > strong,
	.learndash_profile_heading span,
	.learndash_profile_heading {
		color: <?php echo $lds_heading_txt; ?> !important ;	
	}

	/* 
	 * Table Cells
	 *
	 */
	#learndash_profile .profile_info,
	#lessons_list > div,
	#quiz_list > div,
	#learndash_profile .course_progress,
	#learndash_profile #course_list > div,
	#learndash_lesson_topics_list ul > li {
	    background: <?php echo $lds_row_bg; ?>;
		color: <?php echo $lds_row_txt; ?>
	}

	#lessons_list > div:nth-child(odd),
	#quiz_list > div:nth-child(odd),
	#learndash_lesson_topics_list ul > li.nth-of-type-odd 
	{
	    background: <?php echo $lds_row_bg_alt; ?>;
		color: <?php echo $lds_row_txt; ?>
	}

	#lessons_list h4 a,
	#quiz_list h4 a {
	    color: <?php echo $lds_row_txt; ?>
	}

	/* 
	 * Buttons 
	 * 
	 */ 

	/* Complete Button */ 

	#sfwd-mark-complete input.button,
	#sfwd-mark-complete input[type="submit"],
	#learndash_mark_complete_button {
		background-color: <?php echo $lds_complete_button_bg; ?> !important;
		color: <?php echo $lds_complete_button_txt; ?> !important;
	}

	/* Standard Button */

	.btn-join, #btn-join, a#quiz_continue_link, .wpProQuiz_button, #uploadfile_btn, #learndash_next_prev_link a { 
		background-color: <?php echo $lds_button_bg; ?> !important;
		color: <?php echo $lds_button_txt; ?> !important;
	}


	/*
	 * Visual Elements
	 * 
	 */ 

	.course_progress { 
		max-width: 90%;
		margin-left: auto !important;
		margin-right: auto !important;
		display: block;
	}

	dd.course_progress div.course_progress_blue {
		background-color: <?php echo $lds_progress; ?> !important;
	}


	/* 
	 * Links
	 *
	 */

	.widget_ldcourseprogress a,
	.widget_sfwd-lessons-widget a,
	.widget_ldcoursenavigation a,
	.widget_ldcourseinfo a,
	.widget_sfwd-certificates-widget a,
	.widget_sfwd-courses-widget a,
	#learndash_profile .profile_info a, #learndash_profile #course_list a, #learndash_profile #course_list a span,
	#learndash_lessons a, #learndash_quizzes a, .learndash_topic_dots a, .learndash_topic_dots a > span, #learndash_lesson_topics_list span a {
		color: <?php echo $lds_links; ?> !important;
	}


	/* 
	 * Widgets
	 *
	 */

	.widget_ldcourseprogress,
	#sfwd-certificates-widget-2,
	#sfwd-courses-widget-2,
	#ldcourseinfo-2,
	.widget_sfwd-lessons-widget,
	.widget_ldcoursenavigation,
	.widget_ldcourseinfo,
	.widget_sfwd-certificates-widget,
	.widget_sfwd-courses-widget {
		background-color: <?php echo $lds_widget_bg; ?> !important;
		color: <?php echo $lds_widget_txt; ?> !important;
	}

	#learndash_course_content .learndash_topic_dots ul > li:nth-of-type(2n+1) { 
		background: <?php echo $lds_sub_row_bg; ?>;
	}

	#learndash_course_content .learndash_topic_dots ul > li {
		background: <?php echo $lds_sub_row_bg_alt; ?>;
	}

	#learndash_course_content .learndash_topic_dots ul > li a span { 
		color: <?php echo $lds_sub_row_txt; ?> !important;
	}

	#learndash_course_content .learndash_topic_dots ul > li:hover { 
		background: <?php echo $lds_sub_row_bg; ?>
	}

	#learndash_course_content .learndash_topic_dots ul > li:nth-of-type(2n+1) { 
		background: <?php echo $lds_sub_row_bg_alt; ?>;
	}

	.widget_ldcourseprogress <?php echo $widget_title; ?>,
	.widget_sfwd-lessons-widget <?php echo $widget_title; ?>,
	.widget_ldcoursenavigation <?php echo $widget_title; ?>,
	.widget_ldcourseinfo <?php echo $widget_title; ?>,
	.widget_sfwd-certificates-widget <?php echo $widget_title; ?>,
	.widget_sfwd-courses-widget <?php echo $widget_title; ?> {
		background-color: <?php echo $lds_widget_header_bg; ?> !important;
	}

	.widget_ldcourseprogress <?php echo $widget_title; ?>,
	.widget_sfwd-lessons-widget <?php echo $widget_title; ?>,
	.widget_ldcoursenavigation <?php echo $widget_title; ?>,
	.widget_ldcourseinfo <?php echo $widget_title; ?>,
	.widget_sfwd-certificates-widget <?php echo $widget_title; ?>,
	.widget_sfwd-courses-widget <?php echo $widget_title; ?> {
		color: <?php echo $lds_widget_header_txt; ?> !important;
	}

	#course_navigation a,
	.widget_sfwd-lessons-widget ul li a { 
		color: <?php echo $lds_links; ?> !important;
	}

	.learndash_profile_quizzes .passed_icon:before,
	.learndash .completed:after,
	#learndash_profile .completed:after,
	.learndash .topic-completed span:after,
	.learndash_nevigation_lesson_topics_list .list_arrow.collapse.lesson_completed:before,
	.learndash a.completed::after, #learndash_profile a.completed:after,
	#learndash_profile .list_arrow.collapse:before,
	#learndash_profile .list_arrow.expand:before,
	.learndash .topic-completed span::before
	 {
		color: <?php echo $lds_checkbox_complete; ?>
	}

	.list_arrow.expand.lesson_completed:before,
	.learndash_nevigation_lesson_topics_list .topic-completed:before,
	.list_arrow.expand.lesson_completed:before {
		color: <?php echo $lds_arrow_complete; ?>
	}


	.learndash .notcompleted:after,
	#learndash_profile .notcompleted:after,
	#leardash_profile .topic-notcompleted span:before, 
	.learndash .topic-notcompleted span:before,
	.learndash .notcompleted:before,
	#learndash_profile .notcompleted:before,
	#leardash_profile .topic-notcompleted span:before,
	.learndash .topic-notcompleted span:before,
	.learndash_nevigation_lesson_topics_list .topic-notcompleted:before { 
		color: <?php echo $lds_checkbox_incomplete; ?>;
	}

	.learndash_nevigation_lesson_topics_list .list_arrow.collapse:before,
	.lesson_incomplete.list_arrow.expand::before
	{
		color: <?php echo $lds_arrow_incomplete; ?>
	}

	/*
	 .IconColor{color:<?php echo $IconColor; ?>;}
	 .CompleteColor{color:<?php echo $CompleteColor; ?>;}
	*/

	#learndash_profile .profile_info .profile_avatar img {
		border-color: <?php echo $lds_button_bg; ?>
	}

	<?php echo $lds_open_css; ?>
	
	<?php
	return ob_get_clean();

}