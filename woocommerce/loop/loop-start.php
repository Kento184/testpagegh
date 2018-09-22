<?php
/**
 * Product Loop Start
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

global $woocommerce_loop;

// Products container classes
$classes = array( 'row', 'products', 'shop-categories' );

if ( get_data( 'shop_loop_masonry' ) ) {
	$classes[] = 'products-masonry';
}

// Columns count
$columns = kalium_woocommerce_get_loop_shop_columns( get_array_key( $woocommerce_loop, 'columns' ) );
$classes[] = "columns-{$columns}";

// Loop name
$loop_name = ! empty( $woocommerce_loop['name'] ) ? $woocommerce_loop['name'] : 'main';
$classes[] = "loop-{$loop_name}";

// Create attribute string for classes
$classes = implode( ' ', array_map( 'sanitize_html_class', $classes ) );
?>
<div class="<?php echo esc_attr( $classes ); ?>" data-layout-mode="<?php echo get_data( 'shop_loop_masonry_layout_mode' ); ?>">
