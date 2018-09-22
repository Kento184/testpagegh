<?php
/**
 * The template for displaying product widget entries
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-widget-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.5.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product; ?>

<li>
	<div class="item-image">
		<?php echo $product->is_visible() ? sprintf( '<a href="%s">%s</a>', $product->get_permalink(), $product->get_image() ) : $product->get_image(); ?>
	</div>
	
	<div class="item-details">
		
		<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
			
			<span class="product-title"><?php echo $product->get_name(); ?></span>
			
		</a>
		
		<p class="price"><?php echo $product->get_price_html(); ?></p>
		
		<?php if ( ! empty( $show_rating ) ) : ?>
			<?php // start: modified by Arlind ?>
				<p class="rating">
					<i class="fa fa-star-o"></i>
					<?php echo $product->get_average_rating(); ?>
				</p>
			<?php // end: modified by Arlind ?>
		<?php endif; ?>
		
	</div>
</li>
