<?php
/**
* To remove other product types except 'course'. If you want to add other product types
* use 'wdmir_product_types' filter
*/
function wdmir_remove_product_types($product_type)
{

    if (wdm_is_instructor()) {
        /**
        * added in version 1.3
        * filter name: wdmir_product_types
        * param: array of product types
        */
        $product_type = apply_filters('wdmir_product_types', array( 'course' => 'Course' ));
    }

    return $product_type;
}
add_filter('product_type_selector', 'wdmir_remove_product_types');
