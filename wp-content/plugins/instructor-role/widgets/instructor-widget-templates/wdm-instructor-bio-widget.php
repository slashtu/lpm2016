<?php
// template for displaying instructor bio in the widget
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<div class='wdmir-author-bio'>
    <span class='wdmir-author-img'><?php echo get_avatar($author_id, 50); ?></span>
    <span class='wdmir-author-name'><?php the_author(); ?></span>
    <div class='wdmir-author-bio-desc'><?php the_author_meta('description', $author_id); ?></div>
</div>


