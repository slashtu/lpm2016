<?php
/**
 * Column header view
 */

 
// Prevent direct call
if ( !defined( 'WPINC' ) ) die;
if ( !class_exists( 'GW_GoPricing' ) ) die; 
 
global $go_pricing;
$general_settings = get_option( self::$plugin_prefix . '_table_settings' );

?>

<!-- Style -->
<?php
$postdata = array();
$raw_postdata = isset( $table_data['col-data'][$col_index]['header']['style'] ) ? $table_data['col-data'][$col_index]['header']['style'] : '';
if ( $raw_postdata !='' && is_string( $raw_postdata ) ) $postdata = GW_GoPricing_Helper::parse_data( $raw_postdata );
?>
<div class="gwa-col-box go-pricing-style-type" data-type="cpricing">
	<a href="#" class="gwa-col-box-link" title="<?php esc_attr_e( 'Style', 'go_pricing_textdomain' ); ?>" tabindex="-1"></a>
	<div class="gwa-assets-nav"><a href="#" class="gwa-asset-icon-edit" data-action="edit-box" data-popup="header_style" title="<?php esc_attr_e( 'Edit', 'go_pricing_textdomain' ); ?>" tabindex="-1"><span></span></a></div>
	<input type="hidden" name="col-data[<?php echo $col_index ?>][header][style]" value="<?php echo esc_attr( $raw_postdata ); ?>">
	<div class="gwa-col-box-header">
		<div class="gwa-col-box-header-icon"><i class="fa fa-stack-exchange"></i></div>
		<div class="gwa-col-box-title"><?php _e( 'Style', 'go_pricing_textdomain' ); ?></div>
	</div>
	<div class="gwa-col-box-content">
		<?php 
		
		if ( isset( $postdata['type'] ) ) {
		
			switch( $postdata['type'] ) {
				
				case 'standard' : 
					$postdata['type'] =  __( 'Standard (new)', 'go_pricing_textdomain' );
					break;

				default : 
					$postdata['type'] =  __( 'Circle', 'go_pricing_textdomain' );
				
			}
			
		}
		?>
		<p><?php _e( 'Style', 'go_pricing_textdomain' ); ?>: <span><?php echo isset( $postdata['type'] ) && $postdata['type'] != ''  ? htmlentities( $postdata['type'] ) : '-' ?></span></p>
	</div>
</div>
<!-- / Style -->

<!-- Title -->
<?php
$postdata = array();
$raw_postdata = isset( $table_data['col-data'][$col_index]['title'] ) ? $table_data['col-data'][$col_index]['title'] : '';
if ( $raw_postdata !='' && is_string( $raw_postdata ) ) $postdata = GW_GoPricing_Helper::parse_data( $raw_postdata );

?>
<div class="gwa-col-box go-pricing-style-type" data-type="pricing pricing2 pricing3 team product cpricing cpricing2 cpricing3 cteam cproduct">
	<a href="#" class="gwa-col-box-link" title="<?php esc_attr_e( 'Title', 'go_pricing_textdomain' ); ?>" tabindex="-1"></a>
	<div class="gwa-assets-nav"><a href="#" class="gwa-asset-icon-edit" data-action="edit-box" data-popup="header_title" title="<?php esc_attr_e( 'Edit', 'go_pricing_textdomain' ); ?>" tabindex="-1"><span></span></a></div>
	<input type="hidden" name="col-data[<?php echo $col_index ?>][title]" value="<?php echo esc_attr( $raw_postdata ); ?>">
	<div class="gwa-col-box-header">
		<div class="gwa-col-box-header-icon"><i class="fa fa-font"></i></div>
		<div class="gwa-col-box-title"><?php _e( 'Title', 'go_pricing_textdomain' ); ?></div>
	</div>
	<div class="gwa-col-box-content">
		<p><?php _e( 'Content', 'go_pricing_textdomain' ); ?>: <span><?php echo isset( $postdata['title']['content'] ) && $postdata['title']['content'] != ''  ? htmlentities( $postdata['title']['content'] ) : '-' ?></span></p>
	</div>
</div>
<!-- / Title -->

<!-- Price -->
<?php
$postdata = array();
$raw_postdata = isset( $table_data['col-data'][$col_index]['price'] ) ? $table_data['col-data'][$col_index]['price'] : '';
if ( $raw_postdata !='' && is_string( $raw_postdata ) ) $postdata = GW_GoPricing_Helper::parse_data( $raw_postdata );
$price_type = isset( $postdata['type'] ) ? $postdata['type'] : 'price';
?>
<div class="gwa-col-box go-pricing-style-type" data-type="pricing pricing2 pricing3 product cpricing cpricing2 cpricing3 cproduct">
	<a href="#" class="gwa-col-box-link" title="<?php esc_attr_e( 'Title', 'go_pricing_textdomain' ); ?>" tabindex="-1"></a>
	<div class="gwa-assets-nav"><a href="#" class="gwa-asset-icon-edit" data-action="edit-box" data-popup="header_price" title="<?php esc_attr_e( 'Edit', 'go_pricing_textdomain' ); ?>" tabindex="-1"><span></span></a></div>
	<input type="hidden" name="col-data[<?php echo $col_index ?>][price]" value="<?php echo esc_attr( $raw_postdata ); ?>">
	<div class="gwa-col-box-header">
		<div class="gwa-col-box-header-icon"><i class="fa fa-dollar"></i></div>
		<div class="gwa-col-box-title"><?php _e( 'Price', 'go_pricing_textdomain' ); ?></div>
	</div>
	<div class="gwa-col-box-content">
		<?php 
		$curr = '';
		if ( !empty( $general_settings['currency'] ) ) {
			$currency = $general_settings['currency'][0];
			$curr = !empty( $currency['currency'] ) ? sprintf( '(%s)', $currency['currency'] ) : '';
		}
		switch( $price_type ) {
		
			case 'price' : 
				?>
				<p><?php printf( __( 'Price %s', 'go_pricing_textdomain' ), $curr ); ?>: <span><?php echo isset( $postdata['type'] ) && isset( $postdata[$postdata['type']][0]['amount'][0] ) && $postdata[$postdata['type']][0]['amount'][0] != ''  ? htmlentities( $postdata[$postdata['type']][0]['amount'][0] ) : '-' ?></span></p>
				<p><?php _e( 'Payment', 'go_pricing_textdomain' ); ?>: <span><?php echo isset( $postdata['type'] ) && isset( $postdata[$postdata['type']][0]['name'] ) && $postdata[$postdata['type']][0]['name'] != ''  ? htmlentities( $postdata[$postdata['type']][0]['name'] ) : '-' ?></span></p>
				<?php 
				break;
				
			case 'price-html' : 
				?>
				<p><?php _e( 'Price', 'go_pricing_textdomain' ); ?>: <span><?php echo isset( $postdata['type'] ) && isset( $postdata[$postdata['type']]['content'] ) && $postdata[$postdata['type']]['content'] != ''  ? htmlentities( $postdata[$postdata['type']]['content'] ) : '-' ?></span></p>
				<p><?php _e( 'Payment', 'go_pricing_textdomain' ); ?>: <span><?php echo isset( $postdata['type'] ) && isset( $postdata['payment']['content'] ) && $postdata['payment']['content'] != ''  ? htmlentities( $postdata['payment']['content'] ) : '-' ?></span></p>
				<?php
				break;
		
		}
		?>	
	</div>
</div>
<!-- / Price -->

<!-- General -->
<?php
$raw_postdata = isset( $table_data['col-data'][$col_index]['header']['general'] ) ? $table_data['col-data'][$col_index]['header']['general'] : '';
?>
<div class="gwa-col-box go-pricing-style-type" data-type="pricing pricing2 pricing3 team product html cpricing cpricing2 cpricing3 chtml cteam cproduct">
	<a href="#" class="gwa-col-box-link" title="<?php esc_attr_e( 'General', 'go_pricing_textdomain' ); ?>" tabindex="-1"></a>
	<div class="gwa-assets-nav"><a href="#" class="gwa-asset-icon-edit" data-action="edit-box" data-popup="header_general" title="<?php esc_attr_e( 'Edit', 'go_pricing_textdomain' ); ?>" tabindex="-1"><span></span></a></div>
	<input type="hidden" name="col-data[<?php echo $col_index ?>][header][general]" value="<?php echo esc_attr( $raw_postdata ); ?>">
	<div class="gwa-col-box-header">
		<div class="gwa-col-box-header-icon"><i class="fa fa-cogs"></i></div>
		<div class="gwa-col-box-title"><?php _e( 'General', 'go_pricing_textdomain' ); ?></div>
	</div>
	<div class="gwa-col-box-content">
		<p><?php _e( 'Header colum type specific and general settings', 'go_pricing_textdomain' ); ?> <span></span></p>
	</div>
</div>
<!-- / General -->
