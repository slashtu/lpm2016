<?php

/*			
$ld_binary_selector_courses = new Learndash_Binary_Selector_Courses(
	array(
		'title' 			=>	'This is the Title',
		'html_id'			=>	'group-courses',
		'html_class'		=>	'group-courses',
		'html_name'			=>	'group-courses[]',
		'selection_right' 	=> 	$group_enrolled_courses
	)
);
$ld_binary_selector_courses->show();
*/


if (!class_exists('Learndash_Binary_Selector')) {
	class Learndash_Binary_Selector {
	    
		protected $args = array();
		private $defaults = array(
			'title' 					=> 	'',
			'html_id' 					=> 	'',
			'html_name'					=>	'',
			'html_class' 				=> 	'',
			'selection_left' 			=> 	array(),	// WP_Query, WP_User_Query, etc.
			'selection_right' 			=> 	array(),
			'pager_left_current_page'	=>	1,
			'pager_left_total_pages' 	=>	0,
			'pager_left_total_items'	=>	0
		);

		function __construct( $args = array() ) {
			//error_log('args<pre>'. print_r($args, true) .'</pre>');
			
			$this->args = wp_parse_args( $args, $this->defaults );
			$this->args['html_slug'] = sanitize_title_with_dashes($this->args['html_id']);
		}
		
		function set_selector_query($section, $query) {
			//if (get_class($query) == 'WP_Query') {
				//echo "query<pre>"; print_r($query); echo "</pre>";
				if ($section == 'left') {
					if ((isset($query->posts)) && (!empty($query->posts))) {
						$this->args['query'] = $query->query;
				
						$this->args['selection_left'] 			= 	$query->posts;
						$this->args['pager_left_current_page']	=	$query->query_vars['paged'];
						$this->args['pager_left_total_pages'] 	=	$query->max_num_pages;
						$this->args['pager_left_total_items']	=	$query->found_posts;
					}
				}
				//} else if (get_class($query) == 'WP_User_Query') {
			//}
		}
		
		function show() {

			if (empty($this->args['selection_left'])) return;
			
			?><div id="learndash-binary-selector-<?php echo $this->args['html_slug'] ?>" class="learndash-binary-selector"><?php
			$this->show_selections_title();
			$this->show_selections_left();
			$this->show_selections_navigation();
			$this->show_selections_right();
			?></div>
			
			<style>
			.learndash-binary-selector {
				clear: both;
				width: 100%;
				/* border: 1px solid red; */
				min-height: 200px;
				margin: 0;
				padding: 0;
			}
			
			.learndash-binary-selector .learndash-binary-selector-section {
				min-height: 200px;				
			}
			
			.learndash-binary-selector .learndash-binary-selector-left {
				width: 45%;
				float: left;
				/* border: 1px solid red; */
				/* min-height: 200px; */
			}
			.learndash-binary-selector .learndash-binary-selector-middle {
				width: 9%;
				float: left;
				text-align: center;
				margin-top: 8%;
				/* border: 1px solid red; */
				/* min-height: 200px; */
			}
			
			.learndash-binary-selector .learndash-binary-selector-right {
				width: 45%;
				float: left;
				/* border: 1px solid red; */
				/* min-height: 200px; */
			}
			
			.learndash-binary-selector .learndash-binary-selector-search {
				width: 100%;
				float: left;
			}
			
			.learndash-binary-selector select[multiple] {
				width: 100%;
				float: left;
				height: 200px;
			}
			
			.learndash-binary-selector option.selector-option-disabled {
				text-decoration: line-through;
				color: #ccc;
			}
			
			
			.learndash-binary-selector ul.learndash-binary-selector-pager {
				width: 100%;
			}
			.learndash-binary-selector ul.learndash-binary-selector-pager li.learndash-binary-selector-pager-prev {
				width: 30%;
				float: left;
				text-align: left;
			}
			.learndash-binary-selector ul.learndash-binary-selector-pager li.learndash-binary-selector-pager-info {
				width: 40%;
				float: left;
				text-align: center;
			}
			.learndash-binary-selector ul.learndash-binary-selector-pager li.learndash-binary-selector-pager-next {
				width: 30%;
				float: left;
				text-align: right;
			}
			.learndash-binary-selector ul.learndash-binary-selector-pager a {
				text-decoration: none;
				padding: 0 5px;
				display: block;
			}
			.learndash-binary-selector ul.learndash-binary-selector-pager a:hover {
				background-color: inherit;
			}
			</style>
			<?php
		}
		
		function show_selections_title() {
			if (!empty($this->args['title'])) {
				?><h3><?php echo $this->args['title'] ?></h3><?php
			}
		}
		
		function show_selections_navigation() {
			?>
			<div class="learndash-binary-selector-section learndash-binary-selector-middle">
				<a href="#" class="learndash-binary-selector-button-add"><img src="<?php echo LEARNDASH_LMS_PLUGIN_URL ."assets/images/arrow_right.png"; ?>" /></a><br>
				<a href="#" class="learndash-binary-selector-button-remove"><img src="<?php echo LEARNDASH_LMS_PLUGIN_URL ."assets/images/arrow_left.png"; ?>" /></a>
			</div>
			<?php
		}
		
		function show_selections_left() {
			//echo "selection_left<pre>"; print_r($this->args['selection_left']); echo "</pre>";
			
			$data = array(
				'data_query' => $this->args['query'],
				'pager'	=> array(
					'current_page' 	=> 	intval($this->args['pager_left_current_page']),
					'total_pages'	=>	intval($this->args['pager_left_total_pages']),
					'total_items'	=>	intval($this->args['pager_left_total_items'])
				),
			);
			?>
			<div class="learndash-binary-selector-section learndash-binary-selector-left" data="<?php echo  htmlspecialchars(json_encode($data)); ?>">
				<input placeholder="<?php echo $this->get_search_label() ?>" type="text" id="learndash-binary-selector-search-<?php echo $this->args['html_slug'] ?>" class="learndash-binary-selector-search" />
				
				<select multiple="multiple">
					<?php $this->show_selections_left_options(); ?>
				</select>
				<?php
					if ($this->args['pager_left_total_pages'] > 1) {
						?>
						<ul class="learndash-binary-selector-pager">
							<li class="learndash-binary-selector-pager-prev">
								<a href="#" <?php if ($this->args['pager_left_current_page'] == 1) { echo ' disabled="disabled" '; } ?> >&lsaquo; <?php _e('prev', 'learndash') ?></a>
							<li class="learndash-binary-selector-pager-info">
								<span class="current_page"><?php echo $this->args['pager_left_current_page'] ?></span> of <span class="total_page"><?php echo $this->args['pager_left_total_pages']?></span>
							</li>
							</li>
							<li class="learndash-binary-selector-pager-next">
								<a href="#" <?php if ($this->args['pager_left_current_page'] == $this->args['pager_left_total_pages']) { echo ' disabled="disabled" '; } ?>><?php _e('next', 'learndash') ?> &rsaquo;</a>
							</li>
						</ul>	
						<?php
					}
				?>
			</div>
			<?php
		}
		
		function show_selections_right() {
			?>
			<div class="learndash-binary-selector-section learndash-binary-selector-right">
				<input placeholder="<?php echo $this->get_search_label() ?>" type="text" id="learndash-binary-selector-search-<?php echo $this->args['html_slug'] ?>" class="learndash-binary-selector-search" />
				
				<select id="<?php echo $this->args['html_id'] ?>" class="<?php echo $this->args['html_class'] ?>" name="<?php echo $this->args['html_name'] ?>" multiple="multiple">
					<?php $this->show_selections_right_options(); ?>
				</select>
			</div>
			<?php /* ?>
			<ul class="learndash-binary-selector-pager">
				<li class="learndash-binary-selector-pager-prev">
					<a href="#"> &lsaquo; </a>
				</li>
				<li class="learndash-binary-selector-pager-next">
					<a href="#"> &rsaquo; </a>
				</li>
			</ul>
			<?php */ ?>
			<?php
		}
	}
}

if (!class_exists('Learndash_Binary_Selector_Courses')) {
	class Learndash_Binary_Selector_Courses extends Learndash_Binary_Selector {
		var $selected_items = array();
		
		function __construct( $args = array() ) {
			parent::__construct( $args );	
			
			//$this->args['selection_left'] = ld_course_list( array( 'orderby' => 'name', 'order' => 'ASC', 'array' => true ) );
			
			$course_query_args = array(
				'post_type'			=>	'sfwd-courses',
				'post_status'		=>	'publish',
				'orderby'			=>	'name',
				'order'				=>	'ASC',
				'paged'				=>	1,
				'posts_per_page'	=>	5
			);
			$course_query = new WP_Query($course_query_args);
			$this->set_selector_query('left', $course_query);
		}
		
		function show_selections_left_options() {
			if (!empty($this->args['selection_left'])) {
				//error_log("selection_left<pre>". print_r($this->args['selection_left'], true). "</pre>");
				foreach ( $this->args['selection_left'] as $item ) { 
					if ( in_array($item->ID, $this->args['selection_right'] ) ) { 
						$this->selected_items[$item->ID] = $item;
						$disabled_class = 'selector-option-disabled';
					} else {
						$disabled_class = '';
					}
					?><option class="<?php echo $disabled_class ?>" value="<?php echo $item->ID; ?>"><?php echo $item->post_title; ?></option><?php 
				} 
			}
		}

		function show_selections_right_options() {
			//echo "selected_items<pre>"; print_r($this->selected_items); echo "</pre>";
			if (!empty($this->selected_items)) {
				
				$course_query_args = array(
					'post_type'			=>	'sfwd-courses',
					'post_status'		=>	'publish',
					'orderby'			=>	'name',
					'order'				=>	'ASC',
					'posts_per_page'	=>	-1,
					'include'			=>	$this->selected_items
				);
				$course_query = new WP_Query($course_query_args);
				//echo "course_query<pre>"; print_r($course_query); echo "</pre>";
				
				if ((isset($course_query->posts)) && (!empty($course_query->posts))) {
					foreach ( $course_query->posts as $item ) { 
						?><option value="<?php echo $item->ID; ?>"><?php echo $item->post_title; ?></option><?php 
					}
				} 
			}
		}
		
		function get_search_label() {
			return sprintf( __( 'Search %s:', 'learndash' ), LearDash_Custom_Label::get_label( 'courses' ) );
		}
	}
}
