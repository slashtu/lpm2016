<?php
/*
 * To override the default widget stylying just copy the instructor-widget-templates folder of this plugin and place it in your theme/child-theme and modify the widget front-end as you like.
 *
 *  */
// Register and load the widget
function wdm_load_instructor_widgets()
{

    require_once 'inc/class-wdm-instructor-bio-widget.php';
    require_once 'inc/class-wdm-instructor-courses-widget.php';

    register_widget('wdmInstructorBioWidgetNS\WdmInstructorBioWidget');
    register_widget('wdmInstructorCourseWidgetNS\WdmInstructorCoursesWidget');
}

add_action('widgets_init', 'wdm_load_instructor_widgets');
