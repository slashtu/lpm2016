<?php
namespace wdmInstructorBioWidgetNS;

// Creating the widget to display the Instructor bio
//class class_wdm_instructor_bio_widget extends WP_Widget {
class WdmInstructorBioWidget extends \WP_Widget
{

    public function __construct()
    {

        parent::__construct(
            // Base ID of your widget
            'wdm_instructor_widget_bio',
            // Widget name will appear in UI
            __('Instructor Bio Widget', 'learndash'),
            // Widget description
            array( 'description' => __('Instructor Bio in the Course,lesson and quiz pages', 'learndash') )
        );
    }

    // Creating widget front-end
    // This is where the action happens
    public function widget($args, $instance)
    {

        $title = apply_filters('widget_title', $instance['title']);

        if (is_singular('sfwd-courses') || is_singular('sfwd-lessons') || is_singular('sfwd-quiz') || is_singular('sfwd-topic')) {
            global $post;

            $author_id = $post->post_author;
            if (user_can($author_id, 'wdm_instructor')) {
                // before and after widget arguments are defined by themes
                echo $args['before_widget'];

                if (! empty($title)) {
                    echo $args['before_title'] . $title . $args['after_title'];
                }

                if (locate_template(array( 'instructor-widget-templates/wdm-instructor-bio-widget.php' ))) {
                    include(locate_template(array( 'instructor-widget-templates/wdm-instructor-bio-widget.php' )));
                } else {
                    include(plugin_dir_path(__FILE__) . '../instructor-widget-templates/wdm-instructor-bio-widget.php');
                }
                echo $args['after_widget'];
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

// Class class_wdm_instructor_bio_widget ends here
