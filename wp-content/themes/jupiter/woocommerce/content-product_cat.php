<?php
/**
 * The template for displaying product category thumbnails within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product_cat.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see 	    http://docs.woothemes.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.6.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce_loop, $mk_options;

// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) ){
	$woocommerce_loop['loop'] = 0;
}

// Store column count for displaying the grid
if ( empty( $woocommerce_loop['columns'] ) ){
	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );
}

$grid_width = $mk_options['grid_width'];
$content_width = $mk_options['content_width'];

$width = absint($grid_width/4) - 10;
$height = $mk_options['woo_loop_img_height'];
$column_width = absint($grid_width/4);

?>
<article class="product-category product item mk--col mk--col--3-12" style="max-width:<?php echo $column_width; ?>px">
	<div class="item-holder">
	<?php do_action( 'woocommerce_before_subcategory', $category ); ?>

	<a href="<?php echo get_term_link( $category->slug, 'product_cat' ); ?>">
		<h4>
			<?php
				echo $category->name;

				if ( $category->count > 0 )
					echo apply_filters( 'woocommerce_subcategory_count_html', ' <span class="count">' . $category->count . ' '.__('Items', 'mk_framework').'</span>', $category );
			?>
		</h4>

		<?php
	        $small_thumbnail_size   = apply_filters( 'single_product_small_thumbnail_size', 'shop_catalog' );
	        $thumbnail_id           = get_woocommerce_term_meta( $category->term_id, 'thumbnail_id', true  );

	        $featured_image_src = Mk_Image_Resize::resize_by_id_adaptive( $thumbnail_id, 'crop', $width, $height, $crop = false, $dummy = true);

	        echo '<img src="'.$featured_image_src['dummy'].'" '.$featured_image_src['data-set'].' width="'.$width.'" height="'.$height.'" alt="' . esc_attr( $category->name ) . '" />';
		?>

		<?php
			/**
			 * woocommerce_after_subcategory_title hook
			 */
			do_action( 'woocommerce_after_subcategory_title', $category );
		?>

	</a>

	<?php do_action( 'woocommerce_after_subcategory', $category ); ?>
</div>
</article>