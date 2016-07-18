<?php

/**
 * To remove other's quizzes in prerequisite settings.
 */
add_action('admin_footer', 'wdm_prerequisite_remove_others');


/**
 * Function to remove other quizzes.
 */
function wdm_prerequisite_remove_others()
{
    if (wdm_is_instructor()) {
        $args = array(
            'post_type' => 'sfwd-quiz',
            'post_status' => 'publish',
            'author' => get_current_user_id(),
        );
        $quizzes = get_posts($args);

        $my_quizzes = array();
        if (function_exists('learndash_get_setting')) {
            foreach ($quizzes as $quiz) {
                $settings = learndash_get_setting($quiz, 'quiz_pro', true);
                array_push($my_quizzes, (string) $settings);
            }
        }
        ?>
        <script>
            var my_quizzes = <?php echo json_encode($my_quizzes);
        ?>;
            jQuery(document).ready(function () {
                jQuery("#sfwd-quiz_quiz_pro").hide();

                if (jQuery("select[name=quizList]").length) {

                    jQuery("select[name=quizList] option").each(function () {

                        if (jQuery.inArray(jQuery(this).val(), my_quizzes) == -1 && jQuery(this).val() != '0') {
                            jQuery(this).remove();
                        }
                    });
                }
            });
        </script>
        <?php

    } // if ( wdm_is_instructor() )
}


/*
 * for the admin quiz question
 * It restricts to instructor if she tries to access quiz page of other user.
 */
add_action('admin_init', 'wdm_restrict_quiz_edit');


/**
 * Function to restrict other users from quiz edit expect specific instructor.
 */
function wdm_restrict_quiz_edit()
{
    $wdm_user_id = get_current_user_id();

    if (wdm_is_instructor($wdm_user_id)) {
        $post_id = isset($_GET['post_id']) ? $_GET['post_id'] : 0;
        if (!empty($post_id)) {
            $authorID = wdm_get_author($post_id);
            if ($wdm_user_id != $authorID) {
                wp_die(__('Cheatin’ uh?'));
            }
        }
    }
}
