<?php
/**
 *	Kalium WordPress Theme
 *
 *	Laborator.co
 *	www.laborator.co
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

if ( ! is_shop_supported() ) {
	return;
}

// Remove WooCommerce Styles
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );


// Remove certain actions from shop archive page
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );

remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );

remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );


// Remove Link from Products
remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

// Change the order of product details on single page
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 29 );

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 21 );

// Site wide store notice
remove_action( 'wp_footer', 'woocommerce_demo_store' );
add_action( 'kalium_before_header', 'woocommerce_demo_store', 10 );


// Reset WooCommerce loop before entering products loop
add_action( 'woocommerce_before_shop_loop', 'woocommerce_reset_loop' );


// WooCommerce Image Dimensions
function kalium_woocommerce_set_image_dimensions() {
	$theme_id = sanitize_title( wp_get_theme() );
	$kalium_woocommerce_theme_dimensions = 'kalium_woocommerce_thumbnail_dimensions_' . $theme_id;
	
	if ( get_data( $kalium_woocommerce_theme_dimensions ) != 'true' || ! get_option( 'shop_catalog_image_size' ) ) {
		if ( ! isset( $_GET['override_shop_image_dimensions'] ) ) {
			return false;
		}
	}
	
	$catalog = array(
		'width' 	=> '550',
		'height'	=> '700',
		'crop'		=> 1
	);
	
	$single = array(
		'width' 	=> '550',
		'height'	=> '705',
		'crop'		=> 1
	);
	
	$thumbnail = array(
		'width' 	=> '220',
		'height'	=> '295',
		'crop'		=> 1
	);
	
	// Image sizes
	update_option( 'shop_catalog_image_size', $catalog ); 		// Product category thumbs
	update_option( 'shop_single_image_size', $single ); 		// Single product image
	update_option( 'shop_thumbnail_image_size', $thumbnail ); 	// Image gallery thumbs
	
	// Mark this task as complete, only once will be executed
	update_option( $kalium_woocommerce_theme_dimensions, 'true' );
	
	if ( isset( $_GET[ 'override_shop_image_dimensions' ] ) ) {
		die( 'Image dimensions have been reset!' );
	}
}

add_action( 'admin_init', 'kalium_woocommerce_set_image_dimensions', 1 );


// Default Shop Category Thumbnail Size
$shop_category_image_size   = get_data( 'shop_category_image_size' );
$shop_category_thumb_width  = 500;
$shop_category_thumb_height = 290;
$shop_category_thumb_crop   = true;

if ( preg_match_all( "/^([0-9]+)x?([0-9]+)?x?(0|1)?$/", $shop_category_image_size, $shop_category_image_dims ) ) {	
	$shop_category_thumb_width	 = intval( $shop_category_image_dims[1][0] );
	$shop_category_thumb_height	= intval( $shop_category_image_dims[2][0] );
	$shop_category_thumb_crop	  = intval( $shop_category_image_dims[3][0] ) == 1;
	
	if ( $shop_category_thumb_width == 0 || $shop_category_thumb_height == 0 ) {
		$shop_category_thumb_crop = false;
	}
} else if ( ! empty( $shop_category_image_size ) && is_string( $shop_category_image_size ) ) {
	add_filter( 'subcategory_archive_thumbnail_size', laborator_immediate_return_fn( $shop_category_image_size ), 1000 );
}

add_image_size( 'shop-category-thumb', $shop_category_thumb_width, $shop_category_thumb_height, $shop_category_thumb_crop );
add_filter( 'subcategory_archive_thumbnail_size', laborator_immediate_return_fn( 'shop-category-thumb' ) );


// Products per page
function kalium_woocommerce_loop_shop_per_page() {
	global $woocommerce_loop;
	
	$rows = str_replace( 'rows-', '', get_data( 'shop_products_per_page' ) );
	$columns = kalium_woocommerce_get_loop_shop_columns( $woocommerce_loop['columns'] );
	
	if ( empty( $rows ) ) {
		$rows = 4;
	}

	return $columns * $rows;
}

add_filter( 'loop_shop_per_page', 'kalium_woocommerce_loop_shop_per_page' );


// Page Title & Results Count Hide
if ( get_data( 'shop_title_show' ) == false ) {
	add_filter( 'woocommerce_show_page_title', '__return_false' );
	add_filter( 'kalium_woocommerce_show_results_count', '__return_false' );
}


// Sorting Hide
if ( get_data( 'shop_sorting_show' ) == false ) {
	add_filter( 'kalium_woocommerce_show_product_sorting', '__return_false' );
}


// Shop Image
function kalium_woocommerce_catalog_loop_thumbnail() {
	global $post, $product;
	
	$shop_catalog_layout   = get_data( 'shop_catalog_layout' );
	
	$thumb_size			= apply_filters( 'kalium_woocommerce_catalog_loop_thumb', 'shop_catalog' );
	$post_thumb_id		 = get_post_thumbnail_id();
	
	$item_preview_type	 = get_data( 'shop_item_preview_type' );
	
	// Product URL
	$product_url		   = apply_filters( 'kalium_woocommerce_loop_product_link', get_permalink() );
	$link_new_tab 		   = apply_filters( 'kalium_woocommerce_loop_product_link_new_tab', false );
	
	// Product Gallery
	$product_images			= $product->get_gallery_image_ids();
	
	if ( in_array( get_data( 'shop_catalog_layout' ), array( 'full-bg', 'distanced-centered', 'transparent-bg' ) ) ) {
		$item_preview_type = 'none';
	}
	
	// No featured image
	if ( ! has_post_thumbnail() ) {
		$post_thumb_id = wc_placeholder_img_src();
		$thumb_size = 'original';
	}
	
	?>
	<div class="item-images preview-type-<?php echo esc_attr( $item_preview_type ); ?>">
		<a href="<?php echo $product_url; ?>"<?php when_match( $link_new_tab, ' target="_blank"' ); ?> class="main-thumbnail">
			<?php laborator_show_image_placeholder( $post_thumb_id, $thumb_size ); ?>
		</a>
		
		<?php if ( is_array( $product_images ) && count( $product_images ) ) : ?>
		
			<?php
			// Show Second Image on Hover
			if ( $item_preview_type == 'fade' ) : 
			
				$first_image = array_shift( $product_images );
				
				// Remove Duplicate Image
				if ( $first_image == $post_thumb_id ) {
					$first_image = array_shift( $product_images );
				}
				
				?>
				<a href="<?php echo $product_url; ?>"<?php when_match( $link_new_tab, ' target="_blank"' ); ?> class="second-hover-image">
					<?php laborator_show_image_placeholder( $first_image, $thumb_size ); ?>
				</a>
				<?php 
			
			// Product Image Gallery
			elseif ( $item_preview_type == 'gallery' && ! empty( $product_images ) ) :
				
				$index = 1;
				
				foreach( $product_images as $attachment_id ) :
				
					if ( $attachment_id != $post_thumb_id ) :
					
						?>
						<a href="<?php echo $product_url; ?>"<?php when_match( $link_new_tab, ' target="_blank"' ); ?> class="product-gallery-image" data-index="<?php echo esc_attr( $index ); ?>">
							<?php laborator_show_image_placeholder( $attachment_id, $thumb_size ); ?>
						</a>
						<?php
							
						$index++;

					endif;
					
				endforeach;
			
				?>
				<div class="product-gallery-navigation">
					<a href="#" class="gallery-prev">
						<i class="flaticon-arrow427"></i>
					</a>
					
					<a href="#" class="gallery-next">
						<i class="flaticon-arrow413"></i>
					</a>
				</div>
				<?php
				
			endif; 
			?>
		
		<?php endif; ?>
		
		<?php if ( in_array( get_data( 'shop_catalog_layout' ), array( 'full-bg', 'distanced-centered', 'transparent-bg' ) ) ) : ?>
		<div class="product-internal-info">
			
			<?php kalium_woocommerce_product_loop_item_info(); ?>
			
		</div>
		<?php endif; ?>
	</div>
	<?php
}

add_action( 'woocommerce_before_shop_loop_item_title', 'kalium_woocommerce_catalog_loop_thumbnail', 10 );


// Pagination Next & Prev Labeling
add_filter( 'woocommerce_pagination_args', 'lab_bc_pagination_args_filter' );

function lab_bc_pagination_args_filter($args) {
	$args['prev_text'] = '<i class="flaticon-arrow427"></i> ';
	$args['prev_text'] .= __( 'Previous', 'kalium' );
	
	$args['next_text'] = __( 'Next', 'kalium' );
	$args['next_text'] .= ' <i class="flaticon-arrow413"></i>';
	
	return $args;
}


// Loop product info
function kalium_woocommerce_product_loop_item_info() {
	global $woocommerce, $product, $post;
	
	$shop_catalog_layout = get_data( 'shop_catalog_layout' );
	
	#$cart_url = $woocommerce->cart->get_cart_url();
	$cart_url = wc_get_page_permalink( 'cart' );
	$show_price = get_data( 'shop_product_price_listing' );
	
	$shop_product_category = get_data( 'shop_product_category_listing' );
	
	// Product URL
	$product_url = apply_filters( 'kalium_woocommerce_loop_product_link', get_permalink( $post ), $product );
	$link_new_tab = apply_filters( 'kalium_woocommerce_loop_product_link_new_tab', false, $product );
	
	// Full + Transparent Background Layout Type
	if ( in_array( $shop_catalog_layout, array( 'full-bg', 'transparent-bg' ) ) ) :
		?>
		<div class="item-info">
			
			<h3 <?php if ( $shop_catalog_layout == 'transparent-bg' && $shop_product_category == false ) : ?> class="no-category-present"<?php endif; ?>>
				<a href="<?php echo $product_url; ?>"<?php when_match( $link_new_tab, ' target="_blank"' ); ?>><?php the_title(); ?></a>
			</h3>
		
			<?php
				/**
				 * Filters after product title on loop view
				 */
				do_action( 'kalium_woocommerce_product_loop_after_title' ); 
			?>
			
			<?php if ( $shop_product_category ) : ?>
			<div class="product-category">
				<?php echo wc_get_product_category_list( $product->get_id() ); ?>
			</div>
			<?php endif; ?>
			
			
			<div class="product-bottom-details">
				
				<?php if ( $show_price ) : ?>
				<div class="price-column">
					<?php woocommerce_template_loop_price();  ?>
				</div>
				<?php endif; ?>
				
				<?php if ( false == kalium_woocommerce_is_catalog_mode() ) : ?>
				<div class="add-to-cart-column">
					<?php woocommerce_template_loop_add_to_cart(); ?>
				</div>
				<?php endif; ?>
				
			</div>
			
		</div>
		<?php
			
	// Centered – Distanced Background Layout Type
	elseif ( in_array( $shop_catalog_layout, array( 'distanced-centered' ) ) ) :
	
		?>		
		<div class="item-info">
			
			<div class="title-and-price">
				
				<h3>
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h3>
			
				<?php
					/**
					 * Filters after product title on loop view
					 */
					do_action( 'kalium_woocommerce_product_loop_after_title' ); 
				?>
				
				<?php if ( $show_price ) : woocommerce_template_loop_price(); endif; ?>
				
			</div>
			
			<?php woocommerce_template_loop_add_to_cart(); ?>
			
		</div>
		<?php
	
	else :
	
	?>
	<div class="item-info">
		
		<div class="item-info-row">
			<div class="title-column">
				<h3>
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h3>
			
				<?php
					/**
					 * Filters after product title on loop view
					 */
					do_action( 'kalium_woocommerce_product_loop_after_title' ); 
				?>
				
				<?php woocommerce_template_loop_add_to_cart(); ?>
			</div>
			
			<?php if ( $show_price ) : ?>
			<div class="price-column">
				<?php woocommerce_template_loop_price(); ?>
			</div>
			<?php endif; ?>
		</div>
		
		<?php /*
		<div class="row custom-margin">
			<div class="col-xs-<?php echo $show_price ? 9 : 12; ?>">
				<h3>
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h3>
				
				<?php woocommerce_template_loop_add_to_cart(); ?>
			</div>
			
			<?php if ( $show_price ) : ?>
			<div class="col-xs-3 product-price-col">
				<?php woocommerce_template_loop_price(); ?>
			</div>
			<?php endif; ?>
		</div>
		*/ ?>
		
	</div>
	
				
	<div class="added-to-cart-button">
		<a href="<?php echo $cart_url; ?>"><i class="icon icon-ecommerce-bag-check"></i></a>
	</div>
	<?php
		
	endif;
}

if ( ! in_array( get_data( 'shop_catalog_layout' ), array( 'full-bg', 'distanced-centered', 'transparent-bg' ) ) ) {
	add_action( 'woocommerce_after_shop_loop_item', 'kalium_woocommerce_product_loop_item_info', 25 );
}


// Shop Sidebar
function kalium_woocommerce_shop_products_container_before() {
	$shop_sidebar = get_data( 'shop_sidebar' );
	
	?>
	<div class="shop-container<?php echo $shop_sidebar != 'hide' ? ' sidebar-is-present' : ''; ?> row">
	<?php
	
	switch ( $shop_sidebar ) {
		case 'left':
		case 'right':
			?>
			<div class="col-md-9<?php echo $shop_sidebar == 'left' ? ' pull-right-md' : ''; ?>">
			<?php
			break;
		
		default:
			?>
			<div class="col-md-12">
			<?php
	}
}

function kalium_woocommerce_shop_products_container_after() {
	$shop_sidebar = get_data( 'shop_sidebar' );
	
	?>
		</div><!-- close of products container -->
		
		<?php if ( $shop_sidebar != 'hide' ) : ?>
		<div class="col-md-3">
			
			<div class="blog-sidebar shop-sidebar shop-sidebar-<?php echo esc_attr( $shop_sidebar ); ?>">
				
				<?php //dynamic_sidebar( 'shop_sidebar' ); ?>
				<?php
					// Shop Widgets
					kalium_get_widgets( 'shop_sidebar', 'products-archive--widgets' );
				?>
				
			</div>
			
		</div>
		<?php endif; ?>
		
	</div>
	<?php
}

add_action( 'woocommerce_before_shop_loop', 'kalium_woocommerce_shop_products_container_before' );
add_action( 'woocommerce_after_shop_loop', 'kalium_woocommerce_shop_products_container_after' );


// Thumbnail Loop Size
if ( get_data( 'shop_loop_thumb_proportional' ) ) {
	function kalium_woocommerce_catalog_loop_proportional_thumb_size( $size ) {
		return get_data( 'shop_loop_thumb_proportional_size' );
	}
	
	add_filter( 'kalium_woocommerce_catalog_loop_thumb', 'kalium_woocommerce_catalog_loop_proportional_thumb_size' );
}


// Shop Products Pagination (Endless)
add_action( 'wp_ajax_laborator_get_paged_shop_products', 'kalium_woocommerce_get_paged_products' );
add_action( 'wp_ajax_nopriv_laborator_get_paged_shop_products', 'kalium_woocommerce_get_paged_products' );

function kalium_woocommerce_get_paged_products() {
	global $woocommerce_loop;
	
	$resp = array(
		'content' => ''
	);

	// Query Meta Vars
	$page  = kalium()->post( 'page' );
	$opts  = kalium()->post( 'opts' );
	$pp	= kalium()->post( 'pp' );
	
	$q 	   = $opts['q'];
	
	$atts = array(
		'columns' => '4',
		'ids'	 => '',
		'skus'	=> ''
	);
	
	// Min/Max Price Filter
	$meta_query = WC()->query->get_meta_query();
	
	if ( kalium()->url->get( 'min_price', true ) || kalium()->url->get( 'max_price', true ) ) {
		$min = isset( $_REQUEST['min_price'] ) ? floatval( $_REQUEST['min_price'] ) : 0;
		$max = isset( $_REQUEST['max_price'] ) ? floatval( $_REQUEST['max_price'] ) : 9999999999;
		
		$meta_query[] = array(
			'key'		  => '_price',
			'value'		=> array( $min, $max ),
			'compare'	  => 'BETWEEN',
			'type'		 => 'DECIMAL',
			'price_filter' => true,
		);
	}
	
	$query_args = array(
		'post_type'		   => 'product',
		'post_status'		 => 'publish',
		
		'paged'		   	  => $page,
		'posts_per_page'	  => $pp,
		
		'ignore_sticky_posts' => 1,
		'meta_query'		  => $meta_query,
		'tax_query'			  => WC()->query->get_tax_query()
	);
	
	
	// Order by
	if ( isset( $_POST['orderby'] ) ) {
		$_GET['orderby'] = $_POST['orderby'];
	}
	
	$query_args = array_merge( $query_args, WC()->query->get_catalog_ordering_args() );
	
	// Ignore Shown Ids
	$ignore = kalium()->post( 'ignore' );
	
	if ( is_array( $ignore ) && count( $ignore ) ) {
		$query_args['post__not_in'] = $ignore;

		add_filter( 'post_limits', laborator_immediate_return_fn( sprintf( 'LIMIT %d', $pp ) ), 10, 2 );
		
	}

	if ( $q ) {
		$query_args = array_merge( $q, $query_args );
	}

	
	// Collect posts
	ob_start();
	
	// Init query
	$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $query_args, $atts ) );
	
	if ( $products->have_posts() ) :
	
		while( $products->have_posts() ) :
		
			$products->the_post();
				
			wc_get_template_part( 'content', 'product' );
		
		endwhile;
		
	endif;

	$content = ob_get_clean();

	// Set up content
	$resp['content'] = $content;

	echo json_encode( $resp );

	die();
}

// Remove Product Description
add_filter( 'woocommerce_product_description_heading', '__return_empty_string' );


// Render Rating
function kalium_woocommerce_show_rating( $average ) {
	$shop_single_rating_style = get_data( 'shop_single_rating_style' );
	?>
	<div class="star-rating-icons" data-toggle="tooltip" data-placement="right" title="<?php echo sprintf( __( '%s out of 5', 'kalium' ), $average ); ?>">
	<?php
	
	$average_int = intval( $average );	
	$average_floated = $average - $average_int;
	
	for ( $i = 1; $i <= 5; $i++ ) :

		if ( in_array( $shop_single_rating_style, array( 'circles', 'rectangles' ) ) ) :
			
			$fill = 100;
			
			if ( $i > $average ) {
				$fill = 0;
				
				if ( $average_int + 1 == $i ) {
					$fill = $average_floated * 100;
				}
			}
			?>
			<span class="circle<?php echo $shop_single_rating_style == 'circles' ? ' rounded' : ''; ?>">
				<i style="width: <?php echo esc_attr( $fill ); ?>%"></i>
			</span>
			<?php
		else:
			?>
			<i class="fa fa-star<?php echo round( $average ) >= $i ? ' filled' : ''; ?>"></i>
			<?php
		endif;
		
	endfor;
	
	?>
	</div>
	<?php
}


// Single Product Images Column Size
function lac_wc_single_product_image_columns( $column_size ) {
	return get_data( 'shop_single_image_column_size' );
}

add_filter( 'kalium_woocommerce_single_product_image_column_size', 'lac_wc_single_product_image_columns' );


// Single Product Image Size
if ( get_data( 'shop_single_image_size' ) != 'default' ) {
	function kalium_woocommerce_custom_single_product_image_size( $size ) {	
		$product_single_img_size = get_data( 'shop_single_image_size' );
		
		switch( $product_single_img_size ) {
			case 'large':
			case 'full':
				return $product_single_img_size;
				break;
		}
		
		return $size;
	}
	
	add_filter( 'single_product_large_thumbnail_size', 'kalium_woocommerce_custom_single_product_image_size' );
}


// Product Sharing
function kalium_woocommerce_share_product() {
	global $product;

	?>
	<div class="share-product-container">
		<h3><?php _e( 'Share this item:', 'kalium' ); ?></h3>
		
		<div class="share-product social-links">
		<?php
			
			$share_product_networks = get_data( 'shop_share_product_networks' );
	
			if ( is_array( $share_product_networks ) ) :
	
				foreach ( $share_product_networks['visible'] as $network_id => $network ) :
	
					if ( 'placebo' == $network_id ) {
						continue;
					}
	
					share_story_network_link( $network_id, $product->get_id(), '', true );
	
				endforeach;
	
			endif;
			
		?>
		</div>
	</div>
	<?php
}

if ( get_data( 'shop_single_share_product' ) ) {
	add_action( 'woocommerce_single_product_summary', 'kalium_woocommerce_share_product', 50 );
}

// Hide Related Products
if ( 0 == get_data( 'shop_related_products_per_page' ) ) {
	remove_filter( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
}


// Related Product Count
function laborator_woocommerce_related_products_args( $args ) {
	$args['posts_per_page'] = get_data( 'shop_related_products_per_page' );
	
	return $args;
}

add_filter( 'woocommerce_output_related_products_args', 'laborator_woocommerce_related_products_args' );


// Related Products and Upsells Columns Count
function kalium_woocommerce_related_products_columns( $args ) {
	return get_data( 'shop_related_products_columns' );
}

add_filter( 'woocommerce_related_products_columns', 'kalium_woocommerce_related_products_columns' );
add_filter( 'woocommerce_upsells_columns', 'kalium_woocommerce_related_products_columns' );


// Check if shop is in catalog mode
function kalium_woocommerce_is_catalog_mode() {
	return 1 == get_data( 'shop_catalog_mode' );
}

// Catalog Mode
if ( kalium_woocommerce_is_catalog_mode() ) {
	add_filter( 'get_data_shop_add_to_cart_listing', '__return_false' );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	
	if ( get_data( 'shop_catalog_mode_hide_prices' ) ) {
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 29 );
		add_filter( 'get_data_shop_product_price_listing', '__return_false' );
	}
}


// WooCommerce Fields
function kalium_woocommerce_woocommerce_form_field_args( $args ) {
	
	// Replace Input Labels with Placeholder (text, password, etc)
	if ( in_array( $args['type'], array( 'text', 'password', 'state', 'country', 'email', 'tel' ) ) ) {
		$args['placeholder'] = $args['label'];
		$args['label_class'][] = 'hidden';
	} 
	elseif ( in_array( $args['type'], array( 'checkbox', 'radio' ) ) ) {
		if ( 'checkbox' == $args['type'] ) {
			$args['label_class'][] = 'alternate-checkbox';
		} else {
			$args['label_class'][] = 'alternate-radio';
		}
	}
	
	return $args;
}

add_filter( 'woocommerce_form_field_args', 'kalium_woocommerce_woocommerce_form_field_args', 10, 3 );


// My Account Wrapper
add_action( 'woocommerce_before_my_account', 'kalium_woocommerce_before_my_account' );
add_action( 'woocommerce_after_my_account', 'kalium_woocommerce_after_my_account' );

function kalium_woocommerce_before_my_account() {
	?>
		<div class="my-account">
	<?php
}

function kalium_woocommerce_after_my_account() {
	?>
		</div>
	<?php	
}


// Bacs Details
add_action( 'woocommerce_thankyou_bacs', 'kalium_woocommerce_bacs_details_before', 1 );
add_action( 'woocommerce_thankyou_bacs', 'kalium_woocommerce_bacs_details_after', 100 );

function kalium_woocommerce_bacs_details_before() {
	?>
		<div class="bacs-details-container">
	<?php
}

function kalium_woocommerce_bacs_details_after() {
	?>
		</div>
	<?php
}


// Get columns boostrap based class names
function kalium_woocommerce_get_columns_class( $columns_count ) {
	$column_classes = array( 'col-xs-12', 'cols-' . $columns_count );
	
	// Two columns per mobile
	if ( 'two' == get_data( 'shop_product_columns_mobile' ) ) {
		$column_classes = array( 'col-xs-6' );
		$column_classes[] = 'mobile-two-columns';
	}

	switch ( $columns_count ) {
		case 2:
			$column_classes[] = 'col-sm-6';
			break;
		
		case 4:
			$column_classes[] = 'col-md-3';
			$column_classes[] = 'col-sm-6';
			break;
		
		case 5:
			$column_classes[] = 'col-md-2-4';
			break;
		
		case 6:
			$column_classes[] = 'col-md-2';
			break;
			
		// including 3 columns as well
		default: 
			$column_classes[] = 'col-md-4';
	}
	
	return $column_classes;
}


// Cart Menu Icon
function kalium_woocommerce_cart_menu_icon( $skin ) {
	if ( ! get_data( 'shop_cart_icon_menu' ) ) {
		return false;
	}
	
	$icon				= get_data( 'shop_cart_icon' );
	$hide_empty			= get_data( 'shop_cart_icon_menu_hide_empty' );
	$show_cart_contents	= get_data( 'shop_cart_contents' );
	$cart_items_counter	= get_data( 'shop_cart_icon_menu_count' );
	
	$cart_items = WC()->cart->get_cart_contents_count();
	
	
	?>
	<div class="menu-cart-icon-container <?php 
		
		echo esc_attr( $skin ); 
		when_match( $hide_empty && $cart_items == 0, 'hidden' );
		when_match( $show_cart_contents == 'show-on-hover', 'hover-show' );

	?>">
	
		<a href="<?php echo wc_get_cart_url(); ?>" class="cart-icon-link icon-type-<?php echo esc_attr( $icon ); ?>">
			<i class="icon-<?php echo esc_attr( $icon ); ?>"></i>
			
			<?php if ( $cart_items_counter ) : ?>
			<span class="items-count hide-notification cart-items-<?php echo esc_attr( $cart_items ); ?>">&hellip;</span>
			<?php endif; ?>
		</a>
		
		
		<?php if ( $show_cart_contents != 'hide' ) : ?>
		<div class="lab-wc-mini-cart-contents">
		<?php get_template_part( 'tpls/wc-mini-cart' ); ?>
		</div>
		<?php endif; ?>
	</div>
	<?php
}

function kalium_woocommerce_cart_menu_icon_mobile() {
	if ( ! get_data( 'shop_cart_icon_menu' ) || ! function_exists( 'WC' ) ) {
		return;
	}
	
	$icon				= get_data( 'shop_cart_icon' );
	$hide_empty			= get_data( 'shop_cart_icon_menu_hide_empty' );
	$show_cart_contents	= get_data( 'shop_cart_contents' );
	$cart_items_counter	= get_data( 'shop_cart_icon_menu_count' );
	
	$cart_items = WC()->cart->get_cart_contents_count();
	
	?>
	<div class="cart-icon-link-mobile-container">
		<a href="<?php echo wc_get_cart_url(); ?>" class="cart-icon-link-mobile icon-type-<?php echo esc_attr( $icon ); ?>">
			<i class="icon icon-<?php echo esc_attr( $icon ); ?>"></i>
			
			<?php _e( 'Cart', 'kalium' ); ?>
			
			<?php if ( $cart_items_counter ) : ?>
			<span class="items-count hide-notification cart-items-<?php echo esc_attr( $cart_items ); ?>">&hellip;</span>
			<?php endif; ?>
		</a>
	</div>
	<?php
}


// Cart Fragments for Minicart
function kalium_woocommerce_woocommerce_add_to_cart_fragments( $fragments_arr ) {
	ob_start();
	get_template_part( 'tpls/wc-mini-cart' ); 
	$cart_contents = ob_get_clean();
	
	$fragments_arr['labMiniCart'] = $cart_contents;
	$fragments_arr['labMiniCartCount'] = WC()->cart->get_cart_contents_count();
	
	return $fragments_arr;
}

add_filter( 'woocommerce_add_to_cart_fragments', 'kalium_woocommerce_woocommerce_add_to_cart_fragments' );


// Shop Description
function kalium_woocommerce_before_main_content() {
	
	$shop_page_id = get_option( 'woocommerce_shop_page_id' );
	$page_content = get_post( $shop_page_id )->post_content;
	
	$is_vc_container = preg_match( '/\[vc_row.*?\]/i', $page_content );
	
	if ( is_shop() && $is_vc_container ) {
		remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
		?>
		<div class="shop-vc-content-container">
			<?php echo apply_filters( 'the_content', $page_content ); ?>
		</div>
		<?php
	}
}

add_action( 'woocommerce_before_main_content', 'kalium_woocommerce_before_main_content' );

// Account Navigation
function kalium_woocommerce_before_account_navigation() {
	global $current_user;
	
	$account_page_id	= wc_get_page_id( 'myaccount' );
	$account_url		= get_permalink( $account_page_id );
	$logout_url		 = wp_logout_url( $account_url );
	
	?>
	<div class="wc-my-account-tabs">
		
		<div class="user-profile">
			<a class="image">
				<?php echo get_avatar( $current_user->ID, 128 ); ?>
			</a>
			<div class="user-info">
				<a class="name" href="<?php echo the_author_meta( 'user_url', $current_user->ID ); ?>"><?php echo $current_user->display_name; ?></a>
				<a class="logout" href="<?php echo $logout_url; ?>"><?php _e( 'Logout', 'kalium' ); ?></a>
			</div>
		</div>
	<?php
}

function kalium_woocommerce_after_account_navigation() {
	?>
	</div>
	<?php
}

add_action( 'woocommerce_before_account_navigation', 'kalium_woocommerce_before_account_navigation' );
add_action( 'woocommerce_after_account_navigation', 'kalium_woocommerce_after_account_navigation' );


// My Orders Page Title
function kalium_woocommerce_before_account_orders( $has_orders ) {
	
	?>
	<div class="section-title">
		<h1><?php _e( 'My Orders', 'kalium' ); ?></h1>
		<p><?php _e( 'Your recent orders are displayed in the table below.', 'kalium' ); ?></p>
	</div>
	<?php
}

add_action( 'woocommerce_before_account_orders', 'kalium_woocommerce_before_account_orders' );

// My Downloads Page Title
function kalium_woocommerce_before_account_downloads( $has_orders ) {
	
	?>
	<div class="section-title">
		<h1><?php _e( 'My Downloads', 'kalium' ); ?></h1>
		<p><?php _e( 'Your digital downloads are displayed in the table below.', 'kalium' ); ?></p>
	</div>
	<?php
}

add_action( 'woocommerce_before_account_downloads', 'kalium_woocommerce_before_account_downloads' );



// Shop Loop Clearing
function kalium_woocommerce_catalog_loop_clear_row( $shop_columns ) {
	global $woocommerce_loop;
	
	if ( $shop_columns ) {
		echo $woocommerce_loop['loop'] % $shop_columns == 0 ? '<div class="clear-md"></div>' : '';
		echo $woocommerce_loop['loop'] % 2 == 0 ? '<div class="clear-sm"></div>' : '';
	}
}

add_action( 'kalium_woocommerce_catalog_loop_clear_row', 'kalium_woocommerce_catalog_loop_clear_row', 10, 2 );


// Kalium Shop Translations
function kalium_woocmmerce_get_i18n_str( $str_id, $echo = false ) {
	
	$found_string = 'kalium_woocmmerce_get_i18n_str::notFoundString';
	
	$strings = array(
		'Login'                                               => __( 'Login', 'kalium' ),
		'&laquo; Go back'                                     => __( '&laquo; Go back', 'kalium' ),
		'Payment Method'                                      => __( 'Payment method', 'kalium' ),
		'Added to cart'                                       => __( 'Added to cart', 'kalium' ),
		'Out of stock'                                        => __( 'Out of stock', 'kalium' ),
		'Featured'                                            => __( 'Featured', 'kalium' ),
		'Loading products...'                                 => __( 'Loading products...', 'kalium' ),
		'No more products to show'                            => __( 'No more products to show', 'kalium' ),
		'Edit information for this address type'              => __( 'Edit information for this address type', 'kalium' ),
		'Reset Password'                                      => __( 'Reset password', 'kalium' ),
		'Lost Password'                                       => __( 'Lost password', 'kalium' ),
		'Login or Register'                                   => __( 'Login or register', 'kalium' ),
		'Manage your account and see your orders'             => __( 'Manage your account and see your orders', 'kalium' ),
		'Go'                                                  => _x( 'Go', 'submit button', 'kalium' ),
		'My Account'                                          => __( 'My account', 'kalium' ),
		'Edit your account details or change your password'   => __( 'Edit your account details or change your password', 'kalium' ),
		'Password Change'                                     => __( 'Password change', 'kalium' ),
		'(leave blank to leave unchanged)'                    => __( '(leave blank to leave unchanged)', 'kalium' ),
		'Current Password'                                    => __( 'Current password', 'kalium' ),
		'New Password'                                        => __( 'New password', 'kalium' ),
	);
	
	if ( isset( $strings[ $str_id ] ) ) {
		$found_string = $strings[ $str_id ];
	}
	
	if ( ! $echo ) {
		return $found_string;
	}
	
	echo $found_string;
}

// Show mini cart icon and contents in header
function kalium_woocommerce_header_mini_cart( $skin ) {
	if ( is_shop_supported() ) {	
		kalium_woocommerce_cart_menu_icon( $skin ); 
	}
}

// Sidebar wrapper
function kalium_woocommerce_single_product_sidebar_wrapper_start() {
	$sidebar_position = get_data( 'shop_single_sidebar_position' );
	
	?>
	<div class="row">
		<div class="col-md-9<?php when_match( 'left' == $sidebar_position, 'pull-right-lg' ); ?>">
	<?php
}

function kalium_woocommerce_single_product_sidebar_wrapper_end() {
	$sidebar_position = get_data( 'shop_single_sidebar_position' );
	?>
		</div>
		<div class="single-product-sidebar-container sidebar-position-<?php echo esc_attr( $sidebar_position ); ?> col-md-3">
			<div class="blog-sidebar shop-sidebar">
			<?php
			if ( false === dynamic_sidebar( 'shop_sidebar_single' ) ) {
				dynamic_sidebar( 'shop_sidebar' );
			}
			?>
			</div>
		</div>
	</div>
	<?php
}

// Single Product Sidebar
if ( in_array( get_data( 'shop_single_sidebar_position' ), array( 'left', 'right' ) ) ) {
	add_action( 'woocommerce_before_single_product', 'kalium_woocommerce_single_product_sidebar_wrapper_start' );
	add_action( 'woocommerce_after_single_product', 'kalium_woocommerce_single_product_sidebar_wrapper_end' );
}

// WooCommerce Additional Variation Images Handler
function kalium_woocommerce_additional_variation_images_handler( $output, $attachment_id ) {
	return $output;
}

add_filter( 'woocommerce_single_product_image_html', 'kalium_woocommerce_additional_variation_images_handler', 10, 2 );


// Cart remove link replace icon
function kalium_woocommerce_woocommerce_cart_item_remove_link( $remove_link ) {
	return str_replace( '&times;', '<i class="flaticon-cross37"></i>', $remove_link );
}

add_filter( 'woocommerce_cart_item_remove_link', 'kalium_woocommerce_woocommerce_cart_item_remove_link' );


// WooCommerce Single Product Thumbnails Carousel Setup
function kalium_woocommerce_single_product_images_carousel_setup_options( $thumbnail_columns = 4 ) {
	$shop_single_product_images_layout = get_data( 'shop_single_product_images_layout' );
	$shop_single_auto_rotate_image	 = get_data( 'shop_single_auto_rotate_image' );
	$shop_product_image_columns		= apply_filters( 'kalium_woocommerce_single_product_image_column_size', 'small' );
	
	if ( '' == $shop_single_auto_rotate_image ) {
		$shop_single_auto_rotate_image = 5;
	}
	
	$shop_single_auto_rotate_image = absint( $shop_single_auto_rotate_image );
	
	if ( ! in_array( $shop_single_product_images_layout, array( 'plain', 'plain-sticky' ) ) ) {
		$image_carousel_options = array();
		
		// Thumbnails to Show
		$image_carousel_options['thumbnailsToShow'] = $thumbnail_columns;
		
		// Auto Rotate Images
		$image_carousel_options['autoRotateImage'] = $shop_single_auto_rotate_image * 1000;
		
		// Image Transition Type
		$image_carousel_options['carouselFade'] = 'slide' == get_data( 'shop_single_image_carousel_transition' ) ? false : true;
		
		// Parse options to JSON
		?>
		<script type="text/javascript">
			window.singleShopProductCarouselOptions = <?php echo json_encode( $image_carousel_options ); ?>
		</script>
		<?php
	}
}


// Product Images Layout
function kalium_woocommerce_show_product_images_custom_layout( $images_layout_type = 'carousel' ) {
	global $post, $product;
	
	// Attachments
	$attachment_ids = $product->get_gallery_image_ids();
	$shop_single_product_images_layout = get_data( 'shop_single_product_images_layout' );
	
	$images_container_classes = array( 'kalium-woocommerce-product-gallery' );
	$images_container_classes[] = "images-layout-type-{$shop_single_product_images_layout}";
	
	// Is Carousel Type
	$is_carousel = true;
	
	// Toggles
	$zoom_enabled = kalium_woocommerce_is_product_gallery_zoom_enabled();
	$lightbox_enabled = kalium_woocommerce_is_product_gallery_lightbox_enabled();
	
	// Product image setup options
	$single_product_params_js = array(
		'images' => array(),
		
		'zoom' => array(
			'enabled' => $zoom_enabled,
			'options' => array(
				'magnify' => 1
			)
		),
		
		'lightbox' => array(
			'enabled' => $lightbox_enabled,
			'options' => array(
				'shareEl'			   => false,
				'closeOnScroll'		 => false,
				'history'			   => false,
				'hideAnimationDuration' => 0,
				'showAnimationDuration' => 0
			)
		)
	);
	
	
	// Thumbnail columns
	$thumbnails_columns = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
	
	
	// Images Carousel
	$images_carousel_classes = array( 'main-product-images' );
	
	if ( in_array( $shop_single_product_images_layout, array( 'plain', 'plain-sticky' ) ) ) {
		$is_carousel = false; // not carousel product images
		
		$images_carousel_classes[] = 'plain';
		
		// Stretch images to browser edge	
		if ( 'yes' == get_data( 'shop_single_plain_image_stretch' ) ) {
			$images_carousel_classes[] = 'stretched-image';
			$images_carousel_classes[] = 'right' == get_data( 'shop_single_image_alignment' ) ? 'right-edge-sticked' : 'left-edge-sticked';
		}
		
		// Add animation for plain type
		add_filter( 'kalium_woocommerce_single_product_link_image_classes', 'kalium_woocommerce_single_product_link_image_classes_plain' );
	} else {
		// Enqueue carousel library
		kalium_enqueue_slick_slider_library();
		
		$images_carousel_classes[] = 'carousel';
		
		// Add animation for carousel type
		add_filter( 'kalium_woocommerce_single_product_link_image_classes', 'kalium_woocommerce_single_product_link_image_classes_carousel' );
	}
	
	// Product gallery is sticky
	if ( 'plain-sticky' == $shop_single_product_images_layout ) {
		$images_carousel_classes[] = 'sticky';
	}
	
	// When lightbox is enabled
	if ( $lightbox_enabled ) {
		$images_carousel_classes[] = 'has-lightbox';
	}
	
	
	// Populate Images Array
	$images = array();
	
	// Featured image first
	if ( has_post_thumbnail() ) {
		$images[] = get_post_thumbnail_id( $post->ID );
	}
	
	// Gallery images
	$images = array_merge( $images, $attachment_ids );
	
	// Carousel Skip Featured Image
	$carousel_skip_featured_image = true === apply_filters( 'kalium_woocommerce_skip_featured_image_in_carousel', false );
	
	if ( $is_carousel && $carousel_skip_featured_image ) {
		$images_carousel_classes[] = 'skip-featured-image';
	}
	
	// No Spacing for carousel images
	if ( apply_filters( 'kalium_woocommerce_single_product_images_carousel_no_spacing' , false ) ) {
		$images_carousel_classes[] = 'no-spacing';
	}
	

	
	// Show product images
	?>
	<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $images_container_classes ) ) ); ?>">
	
		<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $images_carousel_classes ) ) ); ?>">
			
			<?php
				// Image sizes
				$size_shop_single = kalium_woocommerce_get_product_image_size( 'shop_single' );
				$size_shop_thumbnail = kalium_woocommerce_get_product_image_size( 'shop_thumbnail' );
				
				// Show images
				if ( count ( $images ) ) :
				
					foreach ( $images as $i => $attachment_id ) :
					
						$full_size_image = wp_get_attachment_image_src( $attachment_id, 'full' );
						
						$html = kalium_woocommerce_get_product_image( $attachment_id, $size_shop_single, $lightbox_enabled );
						
						// Product lightbox image entry
						$single_product_params_js['images'][] = array(
							'index'	=> $i,
							'id'  	=> absint( $attachment_id ),
							'src' 	=> $full_size_image[0],
							'w'   	=> $full_size_image[1],
							'h'   	=> $full_size_image[2],
						);
						
						echo apply_filters( 'kalium_woocommerce_single_product_image_html', $html, $attachment_id );
						
					endforeach;
					
				
				// Show placeholder
				else : 
					
					$placeholder_image = wc_placeholder_img_src();
					
					$html = kalium_get_image_placeholder( $placeholder_image );
					
					echo apply_filters( 'kalium_woocommerce_single_product_image_placeholder_html', $html );
					
				endif;
			?>
			
		</div>
		
		<?php
		// Product thumbnails	
		if ( $is_carousel ) :
			
			// Thumbnail Coursel JS Options
			kalium_woocommerce_single_product_images_carousel_setup_options( $thumbnails_columns );
			
			// Skip featured image
			if ( $carousel_skip_featured_image ) {
				$images = array_slice( $images, 1, count( $images ) - 1 );
			}
		?>
		<div class="thumbnails" data-columns="<?php echo $thumbnails_columns; ?>">
			<?php
				
				foreach ( $images as $attachment_id ) :
					
					$html = kalium_woocommerce_get_product_image( $attachment_id, $size_shop_thumbnail );
					
					echo apply_filters( 'kalium_woocommerce_single_product_image_html', $html, $attachment_id );
					
				endforeach;
				
			?>
		</div>
		<?php endif; ?>
		
		<script> 
			var kalium_wc_single_product_params = <?php echo json_encode( apply_filters( 'kalium_woocommerce_single_product_params_js', $single_product_params_js ) ); ?>;
		</script>
	</div>
	<?php
}
	
// Get product image for Kalium image gallery
function kalium_woocommerce_get_product_image( $attachment_id, $image_size, $lightbox_link = false ) {
	$image_post = get_post( $attachment_id );
	
	if ( is_null( $image_post ) ) {
		return '';
	}
	
	$image_title = $image_post->post_content;
	
	$full_size_image = wp_get_attachment_image_src( $attachment_id, 'full' );
	
	$attributes = array(
		'title'				   => $image_title,
		'data-src'				=> $full_size_image[0],
		'data-large_image'		=> $full_size_image[0],
		'data-large_image_width'  => $full_size_image[1],
		'data-large_image_height' => $full_size_image[2],
	);
	
	// Thumbnail
	$image = kalium_get_image_placeholder( $attachment_id, $image_size, '', true, null, $attributes );
	
	// Product link image classes
	$product_link_image_classes = implode( ' ', apply_filters( 'kalium_woocommerce_single_product_link_image_classes', array( 'wow' ) ) );
	
	// HTML image object
	$html  = '<div class="woocommerce-product-gallery__image">';
	$html .= sprintf( '<a href="%s" class="%s">', esc_url( $full_size_image[0] ), esc_attr( $product_link_image_classes ) );
	$html .= $image;
	$html .= '</a>';
	
	// Add image lightbox open link
	$html .= $lightbox_link ? kalium_woocommerce_get_lightbox_trigger_button( $attachment_id ) : '';
	
	$html .= '</div>';
	
	return $html;
}

// Custom Variation Image
function kalium_woocommerce_variation_image_handler( $variation_arr, $variable_product, $variation ) {
	$attachment_id = $variation->get_image_id();
	
	$variation_arr['kalium_image'] = array();
	
	// Product main and thumbmail image
	if ( $attachment_id ) {
		$variation_arr['kalium_image']['main'] = kalium_woocommerce_get_product_image( $attachment_id, kalium_woocommerce_get_product_image_size( 'shop_single' ), kalium_woocommerce_is_product_gallery_lightbox_enabled() );
		$variation_arr['kalium_image']['thumb'] = kalium_woocommerce_get_product_image( $attachment_id, kalium_woocommerce_get_product_image_size( 'shop_thumbnail' ) );
	}
	
	return $variation_arr;
}

if ( kalium_woocommerce_use_custom_product_gallery_layout() ) {
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
	add_action( 'woocommerce_before_single_product_summary', 'kalium_woocommerce_show_product_images_custom_layout', 20 );
	add_filter( 'woocommerce_available_variation', 'kalium_woocommerce_variation_image_handler', 10, 3 );
}


// Add to cart link replacement
function kalium_woocommerce_loop_add_to_cart_link( $html, $product ) {
		
	$show_add_to_cart = get_data( 'shop_add_to_cart_listing' );
	
	$shop_catalog_layout = get_data( 'shop_catalog_layout' );
	$shop_product_category = get_data( 'shop_product_category_listing' );
	
	// Replace class
	$html = preg_replace( '/class="\s*button/', 'class="add_to_cart_button', $html );
	$html = str_replace( '<a ', '<a data-added_to_cart_text="' . kalium_woocmmerce_get_i18n_str( 'Added to cart' ) . '"', $html );
	
	ob_start();
	?>
	<div class="product-loop-add-to-cart-container">
		
		<?php if ( $shop_product_category && $shop_catalog_layout == 'default' ) : ?>
		<div class="product-category<?php when_match( $show_add_to_cart, 'category-hoverable' ); ?>">
			<?php echo wc_get_product_category_list( $product->get_id() ); ?>
		</div>
		<?php endif; ?>
		
		<?php if ( $show_add_to_cart ) : ?>
		<div class="add-to-cart-link">
			<?php echo $html ?>
		</div>
		<?php endif; ?>
		
	</div>
	<?php
		
	$html = ob_get_clean();
		
	return $html;
}

add_filter( 'woocommerce_loop_add_to_cart_link', 'kalium_woocommerce_loop_add_to_cart_link', 10, 2 );


// Default shop columns
function kalium_woocommerce_get_loop_shop_columns( $columns = null, $is_category = false ) {
	$default_columns = get_data( 'shop_product_columns' );
	
	// For categories
	if ( $is_category ) {
		$default_columns = get_data( 'shop_category_columns' );
	}
	
	if ( 'decide' == $default_columns ) {
		$default_columns = 'hide' == get_data( 'shop_sidebar' ) ? 4 : 3;
	}
	
	$default_columns = kalium_get_number_from_word( $default_columns );
	
	// Custom columns
	if ( is_numeric( $columns ) ) {
		return $columns;
	}
	
	return $default_columns;
}

add_filter( 'loop_shop_columns', 'kalium_woocommerce_get_loop_shop_columns' );


// Loop product classes
function kalium_woocommerce_catalog_loop_product_classes( $classes ) {
	global $woocommerce_loop;
	
	// Use default number of columns for product
	if ( empty( $woocommerce_loop['columns' ] ) ) {
		$woocommerce_loop['columns'] = kalium_woocommerce_get_loop_shop_columns();
	}
	
	// Shop columns
	$shop_item_layout = get_data( 'shop_catalog_layout' );
	
	// Get column class (Bootstrap)
	$columns_classes = kalium_woocommerce_get_columns_class( $woocommerce_loop['columns'] );
	
	// Only when is AJAX request
	if ( is_ajax() ) {
		$classes[] = 'product';
	}
	
	// Product layout type
	$classes[] = "catalog-layout-{$shop_item_layout}";
	
	return array_merge( $columns_classes, $classes );
}

add_filter( 'kalium_woocommerce_catalog_loop_product_classes', 'kalium_woocommerce_catalog_loop_product_classes' );


// Loop category classes
function kalium_woocommerce_catalog_loop_category_classes( $classes ) {
	global $woocommerce_loop;
	
	// Use default number of columns for product category
	if ( empty( $woocommerce_loop['columns' ] ) ) {
		$woocommerce_loop['columns'] = kalium_woocommerce_get_loop_shop_columns( null, 'category' );
	}
	
	// Get column class (Bootstrap)
	$columns_classes = kalium_woocommerce_get_columns_class( $woocommerce_loop['columns'] );
	
	return array_merge( $columns_classes, $classes );
}

add_filter( 'kalium_woocommerce_catalog_loop_category_classes', 'kalium_woocommerce_catalog_loop_category_classes' );


// Check if zoom is enabled
function kalium_woocommerce_is_product_gallery_zoom_enabled() {
	return get_theme_support( 'wc-product-gallery-zoom' );
}

// Check if gallery lightbox is enabled
function kalium_woocommerce_is_product_gallery_lightbox_enabled() {
	return get_theme_support( 'wc-product-gallery-lightbox' );
}

// Trigger lightbox button
function kalium_woocommerce_get_lightbox_trigger_button( $attachment_id ) {
	return '<button class="product-gallery-lightbox-trigger" data-id="' . $attachment_id . '" title="' . __( 'View full size', 'kalium' ) . '"><i class="flaticon-close38"></i></button>';
}

// Use Kalium's default product gallery layout
function kalium_woocommerce_use_custom_product_gallery_layout() {
	$not_supported_plugins = array(
		'woocommerce-additional-variation-images/woocommerce-additional-variation-images.php'
	);
	
	// Disable custom product gallery when certain plugins are activated (not supported)
	foreach ( $not_supported_plugins as $plugin_file ) {
		if ( kalium()->helpers->isPluginActive( $plugin_file ) ) {
			return false;
		}
	}
	
	return true;
}

// Product image sizes
function kalium_woocommerce_get_product_image_size( $type = 'single' ) {
	
	// Thumbnail product image
	if ( in_array( $type, array( 'thumb', 'thumbnail', 'shop_thumbnail' ) ) ) {
		return apply_filters( 'single_product_small_thumbnail_size', 'shop_thumbnail' );
	}
	
	// Larger product image
	return apply_filters( 'single_product_large_thumbnail_size', 'shop_single' );
	
}


// Single Product Image – fadeIn effect for carousel type
function kalium_woocommerce_single_product_link_image_classes_carousel( $classes ) {
	$classes[] = 'fadeIn';
	$classes[] = 'fast';
	return $classes;
}

// Single Product Image – fadeInLab effect for carousel type
function kalium_woocommerce_single_product_link_image_classes_plain( $classes ) {
	$classes[] = 'fadeInLab';
	return $classes;
}

// Login page heading
function kalium_woocommerce_my_account_login_page_heading() {
	
	?>
		<div class="section-title">
			
			<h1><?php
				if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) {
					_e( 'Login or register', 'kalium' );
				} else {
					_e( 'Login', 'kalium' );
				}
			?></h1>
			
			<p><?php kalium_woocmmerce_get_i18n_str( 'Manage your account and see your orders', true ); ?></p>
		</div>
		
	<?php
}

add_action( 'woocommerce_before_customer_login_form', 'kalium_woocommerce_my_account_login_page_heading', 10 );

// Review product form
function kalium_woocommerce_product_review_comment_form_args( $args ) {
	$args['class_submit'] = 'button';
	
	// Comment textarea
	$args['comment_field'] = preg_replace( '/(<p.*?)class="(.*?)"/', '\1class="labeled-textarea-row \2"', $args['comment_field'] );
	
	// Comment fields
	if ( ! empty( $args['fields'] ) ) {
		foreach ( $args['fields'] as & $field ) {
			$field = preg_replace( '/(<p.*?)class="(.*?)"/', '\1class="labeled-input-row \2"', $field );
		}
		
		// Clear last field
		$field_keys = array_keys( $args['fields'] );
		
		$args['fields'][ end( $field_keys ) ] .= '<div class="clear"></div>';
	}
	
	return $args;
}

add_filter( 'woocommerce_product_review_comment_form_args', 'kalium_woocommerce_product_review_comment_form_args', 10 );

// WooCommerce Archive Header
function kalium_woocommerce_archive_header_display( $show_title, $show_ordering ) {
	
	// Classes
	$classes = array( 'woocommerce-shop-header' );
	
	if ( $show_title && $show_ordering ) {
		$classes[] = 'woocommerce-shop-header--columned';
	}
	?>	
	<header class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
		
		<?php if ( $show_title ) : ?>
		<div class="woocommerce-shop-header--title">

			<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
	
				<h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
	
			<?php endif; ?>
	
			<?php
				/**
				 * woocommerce_archive_description hook.
				 *
				 * @hooked woocommerce_taxonomy_archive_description - 10
				 * @hooked woocommerce_product_archive_description - 10
				 */
				do_action( 'woocommerce_archive_description' );
			?>
			
		</div>
		<?php endif; ?>
		
		<?php if ( $show_ordering ) : ?>
		<div class="woocommerce-shop-header--sorting">
			
			<?php
				/**
				 * Shop archive product sorting
				 */
				woocommerce_catalog_ordering();
				
		   ?>
		   
		</div>
		<?php endif; ?>

	</header>
	<?php
}

add_action( 'kalium_woocommerce_archive_header', 'kalium_woocommerce_archive_header_display', 10, 2 );

// Results count for shop archive
add_action( 'woocommerce_archive_description', 'woocommerce_result_count', 20 );

// Shop Archive before main content
function kalium_woocommerce_archive_title() {
			
	// Shop header
	$show_page_title = apply_filters( 'woocommerce_show_page_title', true );
	$show_ordering = apply_filters( 'kalium_woocommerce_show_product_sorting', true );
	$show_shop_header = $show_page_title || $show_ordering;
	
	if ( $show_shop_header ) {
		do_action( 'kalium_woocommerce_archive_header', $show_page_title, $show_ordering );
	
	}
}

// Payment method title
function kalium_woocommerce_review_order_before_payment_title() {
	
	?>
	<h2 id="payment_method_heading"><?php _e( 'Payment method', 'kalium' ); ?></h2>
	<?php
}

add_action( 'woocommerce_review_order_before_payment', 'kalium_woocommerce_review_order_before_payment_title', 10 );


// Return to shop after cart item adding (option enabled in Woo)
function kalium_woocommerce_continue_shopping_redirect_to_shop( $url ) {
	return wc_get_page_permalink( 'shop' );
}

add_filter( 'woocommerce_continue_shopping_redirect', 'kalium_woocommerce_continue_shopping_redirect_to_shop', 10 );

// Product rating
function kalium_woocommerce_single_product_rating_stars() {
	global $product;
	
	$average = $product->get_average_rating();
	
	?>
	<div class="star-rating" title="<?php printf( __( 'Rated %s out of 5', 'kalium' ), $average ); ?>">
		<?php kalium_woocommerce_show_rating( $average ); ?>
	</div>
	<?php
}

add_action( 'kalium_woocommerce_single_product_rating_stars', 'kalium_woocommerce_single_product_rating_stars', 10 );

// Review rating
function kalium_woocommerce_product_get_rating_html( $html, $rating, $count ) {
	
	ob_start();
	?>
	<div class="star-rating">
		<?php
			kalium_woocommerce_show_rating( $rating );	
		?>
	</div>
	<?php
		
	return ob_get_clean();
}

add_action( 'woocommerce_product_get_rating_html', 'kalium_woocommerce_product_get_rating_html', 10, 3 );

// Double variation image fix
function kalium_woocommerce_variation_remove_featured_image( $variation, $variable ) {
	
	if ( kalium_woocommerce_use_custom_product_gallery_layout() ) {
		$product_id = $variable->get_id();
		
		if ( isset( $variation['image_id'] ) && $variation['image_id'] == get_post_thumbnail_id( $product_id ) ) {
			$variation['image_id'] = '';
			$variation['image'] = null;
		}
	}
	
	return $variation;
}

add_filter( 'woocommerce_available_variation', 'kalium_woocommerce_variation_remove_featured_image', 1, 2 );

// Support multi currency in AJAX mode for paged products page
function kalium_wcml_multi_currency_ajax_actions( $actions ) {
	$actions[] = 'laborator_get_paged_shop_products';
	return $actions;
}

add_filter( 'wcml_multi_currency_ajax_actions', 'kalium_wcml_multi_currency_ajax_actions' );

// Fix the issue with Product Filter plugin
function kalium_prdctfltr_init_product_filter_globals( $query ) {
	if ( class_exists( 'WC_Prdctfltr' ) ) {
		WC_Prdctfltr::make_global( $_REQUEST, $query );
	}
}

add_action( 'kalium_woocommerce_paged_products_query_ajax', 'kalium_prdctfltr_init_product_filter_globals' );