<?php
global $mk_options;
$phpinfo =  pathinfo( __FILE__ );
$path = $phpinfo['dirname'];
include( $path . '/config.php' );

$id = Mk_Static_Files::shortcode_id();

$output = $border_color = $rounded_circle_css = '';

if ( !empty( $icon ) ) {
        $icon = (strpos($icon, 'mk-') !== FALSE) ? $icon : ( 'mk-'.$icon.'' );
}

$animation_css = get_viewport_animation_class($animation);




$output .= '<div id="box-icon-'.$id.'" style="margin-bottom:'.$margin.'px;" class="'.$el_class.' '.$visibility.' '.$style.'-style mk-box-icon clearfix">';
$svg = Mk_SVG_Icons::get_svg_icon_by_class_name(false, $icon);
if ( $style == "simple_minimal" ) {
    if ( $circled == 'true' ) {
        $border_css =  !empty( $icon_circle_border_color ) ? ( 'border:1px solid '.$icon_circle_border_color.';' ) : '';
        $output .= '<h4 class="icon-circled" style="font-size:'.$text_size.'px;font-weight:'.$font_weight.';">';
        $output .= !empty( $read_more_url ) ? '<a href="'.$read_more_url.'"><i class="'.$animation_css.' circled-icon mk-main-ico '.$icon_size.'" style="'.$border_css.'color:'.$icon_color.';background-color:'.$icon_circle_color.'">'.$svg.'</i></a>' : '<i class="'.$animation_css.' '.$icon_size.' circled-icon mk-main-ico" style="'.$border_css.'color:'.$icon_color.';background-color:'.$icon_circle_color.'">'.$svg.'</i>';
        $output .= !empty( $read_more_url ) ? '<a href="'.$read_more_url.'"><span class="'.$icon_size.'">'.$title.'</span></a>' : '<span class="'.$icon_size.'">'.$title.'</span>';
        $output .= '<div class="clearboth"></div>';
        $output .= '</h4>';
    }   else {
        $output .= '<h4 style="font-size:'.$text_size.'px;font-weight:'.$font_weight.';"><i style="color:'.$icon_color.'" class="'.$animation_css.' '.$icon_size.' mk-main-ico">'.$svg.'</i>';
        $output .= !empty( $read_more_url ) ? '<a href="'.$read_more_url.'"><span>'.$title.'</span></a>' : '<span>'.$title.'</span>';
        $output .= '<div class="clearboth"></div>';
        $output .= '</h4>';
    }

    $output .= wpb_js_remove_wpautop( $content, true );
    if ( $read_more_txt ) {
        $output .= '<div class="clearboth"></div><a class="icon-box-readmore" href="'.$read_more_url.'">'.$read_more_txt.' '.Mk_SVG_Icons::get_svg_icon_by_class_name(false, 'mk-icon-caret-right', 16).'</a>';
    }

} else if ( $style == "boxed" ) {

        $output .= '<div class="icon-box-boxed '.$icon_location.'">';
        $border_css =  !empty( $icon_circle_border_color ) ? ( 'border:1px solid '.$icon_circle_border_color.';' ) : '';
        if ( !empty( $icon ) ) {
            $output .= !empty( $read_more_url ) ? '<a href="'.$read_more_url.'">' : '';
            $output .= '<i style="'.$border_css.'background-color:'.$icon_circle_color.';color:'.$icon_color.';" class="'.$animation_css.' mk-main-ico">'.$svg.'</i>';
            $output .= !empty( $read_more_url ) ? '</a>' : '';
        }
        $output .= '<h4 style="font-size:'.$text_size.'px;font-weight:'.$font_weight.';">';
        $output .= !empty( $read_more_url ) ? '<a href="'.$read_more_url.'">'.$title.'</a>' : $title;
        $output .= '</h4>';
        $output .= wpb_js_remove_wpautop( $content, true );
        if ( $read_more_txt ) {
            $output .= '<div class="clearboth"></div><a class="icon-box-readmore" href="'.$read_more_url.'">'.$read_more_txt.' '.Mk_SVG_Icons::get_svg_icon_by_class_name(false, 'mk-icon-caret-right', 16).'</a>';
        }
        $output .= '<div class="clearboth"></div></div>';


    } else if ( $style == "simple_ultimate" ) {
            if($rounded_circle == 'true' && ($icon_size == 'small' || $icon_size == 'medium')) {
                $border_color = 'border-color:'.$icon_color.';';
                $rounded_circle_css = 'rounded-circle';
            }
        $output .= '<div class="'.$icon_location.'-side '.$rounded_circle_css.'">';
        if ( !empty( $icon ) ) {
            $output .= !empty( $read_more_url ) ? '<a href="'.$read_more_url.'"><i style="color:'.$icon_color.';'.$border_color.'" class="'.$animation_css.' '.$icon_size.' mk-main-ico">'.$svg.'</i></a>' : '<i style="color:'.$icon_color.';'.$border_color.'" class="'.$animation_css.' '.$icon_size.' mk-main-ico">'.$svg.'</i>';
        }
        $output .= '<div class="box-detail-wrapper '.$icon_size.'-size">';
        $output .= '<h4 style="font-size:'.$text_size.'px;font-weight:'.$font_weight.';">';
        $output .= !empty( $read_more_url ) ? '<a href="'.$read_more_url.'">'.$title.'</a>' : $title;
        $output .= '</h4>';
        $output .= wpb_js_remove_wpautop( $content, true );
        if ( $read_more_txt ) {
            $output .= '<div class="clearboth"></div><a class="icon-box-readmore" href="'.$read_more_url.'">'.$read_more_txt.' '.Mk_SVG_Icons::get_svg_icon_by_class_name(false, 'mk-icon-caret-right', 16).'</a>';
        }
        $output .= '</div><div class="clearboth"></div></div>';
    }
$output .= '<div class="clearboth"></div></div>';

echo $output;






$app_styles = !empty( $txt_color ) ? ( '#box-icon-'.$id.' p{color:'.$txt_color.';}' ) : '';
$app_styles .= !empty( $txt_link_color ) ? ( '#box-icon-'.$id.' p a{color:'.$txt_link_color.';}' ) : '';
if ( empty( $read_more_url ) ) {
    $app_styles .= !empty( $title_color ) ? ( '#box-icon-'.$id.' h4 {color:'.$title_color.'!important;}' ) : '';
} else {
    $app_styles .= !empty( $title_color ) ? ( '#box-icon-'.$id.' h4 a{color:'.$title_color.'!important;}' ) : '';
}


Mk_Static_Files::addCSS($app_styles, $id);
