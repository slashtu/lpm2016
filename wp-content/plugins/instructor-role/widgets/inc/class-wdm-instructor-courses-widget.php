<?php
namespace wdmInstructorCourseWidgetNS;

// Creating the widget to display the Other courses  by the instructor
//class class_wdm_instructor_courses_widget extends WP_Widget {
class WdmInstructorCoursesWidget extends \WP_Widget
{

    public function __construct()
    {

        parent::__construct(
            // Base ID of your widget
            'wdm_instructor_widget_courses',
            // Widget name will appear in UI
            __('Instructor Courses Widget', 'learndash'),
            // Widget description
            array( 'description' => __('Other courses from the instructor of current course page', 'learndash') )
        );
    }

    // Creating widget front-end
    // This is where the action happens
    public function widget($course_args, $course_instance)
    {
        $title = apply_filters('widget_title', $course_instance['title']);

        if (is_singular('sfwd-courses') || is_singular('sfwd-lessons') || is_singular('sfwd-quiz') || is_singular('sfwd-topic')) {
            global $post;

            $author_id = $post->post_author;
            if (user_can($author_id, 'wdm_instructor')) {
                // before and after widget arguments are defined by themes
                echo $course_args['before_widget'];

                // This is where you run the code and display the output

                $course_id       = learndash_get_course_id();
                $course_query_args   = array(
                    'author'     => $author_id,
                    'post_type'  => 'sfwd-courses',
                    'post__not_in'   => array( $course_id ),
                    'orderby'    => 'post_date',
                    'order'      => 'ASC',
                );

                $myposts = get_posts($course_query_args);

                if (! empty($myposts)) {
                    if (! empty($title)) {
                        echo $course_args['before_title'] . $title . $course_args['after_title'];
                    }

                    foreach ($myposts as $post) :
                        setup_postdata($post);
                        //$post_id = get_the_ID();

                        if (locate_template(array( 'instructor-widget-templates/wdm-instructor-courses-widget.php' ))) {
                            include(locate_template(array( 'instructor-widget-templates/wdm-instructor-courses-widget.php' )));
                        } else {
                            include(plugin_dir_path(__FILE__) . '../instructor-widget-templates/wdm-instructor-courses-widget.php');
                        }
                    endforeach;
                }
                echo $course_args['after_widget'];
            }
        }
    }

    // Widget Backend
    public function form($instance)
    {
        if (isset($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = __('New title', 'learndash');
        }
        // Widget admin form
    ?>
	<p>
	    <label for="<?php echo $this->get_field_id('title'); ?>">
	<?php _e('Title:'); ?>
	    </label>
	    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
	</p>
	<?php
    }

    // Updating widget replacing old instances with new
    public function update($new_instance, $old_instance)
    {
        $instance        = array();
        $old_instance = $old_instance;
        $instance['title']   = ( ! empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }
}

// Class class_wdm_instructor_courses_widget ends here
