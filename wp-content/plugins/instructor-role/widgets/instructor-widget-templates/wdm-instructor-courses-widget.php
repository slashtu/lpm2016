<?php
// template for displaying other instructor Courses in the widget
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<div class="wdmir-more-from-instructor">
    <?php the_post_thumbnail(array( 70, 70 ), array( 'class' => 'wdmir-instructor-courses-img' )); ?>
    <a href="<?php the_permalink(); ?>" class="wdmir-instructor-course-title"><?php the_title(); ?></a>
    <span class="wdmir-instuctor-course-price-label">Price : </span>
    <?php if (! empty(get_post_meta(get_the_ID(), '_sfwd-courses', true)['sfwd-courses_course_price'])) {
    ?>
        <span class="wdmir-instructor-course-price"><?php echo get_post_meta(get_the_ID(), '_sfwd-courses', true)['sfwd-courses_course_price']; ?></span>
	<?php
} else { ?>
        <span class="wdmir-instructor-course-price">Free</span>
	<?php
}
    ?>
</div>
<?php
