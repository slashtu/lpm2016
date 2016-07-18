<?php
/*----------------------------------------------------------------------------*\
	PARAMS
\*----------------------------------------------------------------------------*/

$mpc_params = array(
	'mpc_align',
	'mpc_animation',
	'mpc_colorpicker',
	'mpc_css',
	'mpc_datetime',
	'mpc_divider',
	'mpc_gradient',
	'mpc_icon',
	'mpc_layout_select',
	'mpc_list',
	'mpc_preset',
	'mpc_slider',
	'mpc_split',
	'mpc_text',
	'mpc_typography',
);

foreach( $mpc_params as $param ) {
	require_once( MPC_MASSIVE_DIR . '/params/' . $param . '/' . $param . '.php' );
}
