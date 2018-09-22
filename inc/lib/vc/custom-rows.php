<?php
/**
 *	Custom Row for this theme
 *	
 *	Laborator.co
 *	www.laborator.co 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Kalium Visual Composer row wrapper
 */
function kalium_vc_row_parent_wrapper( $output, $object, $atts ) {

	// Applied to rows only
	if ( in_array( $object->settings( 'base' ), array( 'vc_row', 'vc_row_inner' ) ) ) {
		$classes = array( 'vc-parent-row' );
		
		// Row width
		$full_width = get_array_key( $atts, 'full_width' );
		
		if ( $full_width ) {
			$classes[] = "row-{$full_width}";
		} else {
			$classes[] = "row-default";
		}
		
		// Custom CSS for row
		$css = get_array_key( $atts, 'css' );
		
		if ( $css ) {
			$classes[] = vc_shortcode_custom_css_class( $css );
		}
		
		// Columns gap
		$gap = get_array_key( $atts, 'gap' );
		
		if ( $gap ) {
			$classes[] = "columns-gap-{$gap}";
		}
	
		// Container wrap
		$output = sprintf( '<div class="%s">%s</div>', implode( ' ', $classes ), $output );
	}
	
	return $output;
}

add_filter( 'vc_shortcode_output', 'kalium_vc_row_parent_wrapper', 100, 3 );

/**
 * Since custom CSS class is applied to row parent, remove it from rows
 */
function kalium_vc_row_remove_custom_css_class( $classes, $base, $atts ) {
	
	if ( 'vc_row' == $base ) {
		if ( strpos( $classes, 'vc_custom_' ) ) {
			$classes = preg_replace( '/\s+vc_custom_[0-9]+/', '', $classes );
		}
	}
	
	return $classes;
}

add_filter( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 'kalium_vc_row_remove_custom_css_class', 10, 3 );

/**
 * Text column text formatting
 */
 
function kalium_vc_text_column_formatting( $classes, $base, $atts ) {
	
	if ( 'vc_column_text' == $base ) {
		
		$classes .= ' post-formatting ';
	}
	
	return $classes;
}

add_filter( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 'kalium_vc_text_column_formatting', 10, 3 );

/**
 * Widget skin class for VC elements
 */
 
function kalium_vc_widget_sidebar_classes( $classes, $base, $atts ) {
	
	if ( 'vc_widget_sidebar' == $base ) {
		$classes .= sprintf( ' widget-area %s', implode( ' ', kalium_set_widgets_classes() ) );
	}
	
	return $classes;
}

add_filter( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 'kalium_vc_widget_sidebar_classes', 10, 3 );