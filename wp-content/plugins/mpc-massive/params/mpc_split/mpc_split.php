<?php
/*----------------------------------------------------------------------------*\
	MPC_SPLIT Param
\*----------------------------------------------------------------------------*/

vc_add_shortcode_param( 'mpc_split', 'mpc_split_settings', MPC_MASSIVE_URL . '/assets/js/mpc-params.js' );
function mpc_split_settings( $settings, $value ) {
	return '<textarea class="mpc-vc-split-text">' . ( $value != '' ? str_replace( '|||', "\n", $value ) : '' ) . '</textarea><input type="hidden" class="mpc-vc-split wpb_vc_param_value" name="' . esc_attr( $settings[ 'param_name' ] ) . '" value="' . esc_attr( $value ) . '" />';
}
