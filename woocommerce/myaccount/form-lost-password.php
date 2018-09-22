<?php
/**
 * Lost password form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-lost-password.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wc_print_notices(); ?>
<?php // start: modified by Arlind ?>
<div class="bordered-block">
<?php // end: modified by Arlind ?>
	
	<form method="post" class="woocommerce-ResetPassword lost_reset_password login message-form">
		<?php // start: modified by Arlind ?>
		<h2><?php kalium_woocmmerce_get_i18n_str( 'Lost Password', true ); ?></h2>
		<?php // end: modified by Arlind ?>
		
		<p><?php echo apply_filters( 'woocommerce_lost_password_message', __( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'woocommerce' ) ); ?></p>
		
		<br>
		
		<div class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first labeled-input-row">
			<label for="user_login"><?php _e( 'Username or email', 'woocommerce' ); ?></label>
			<input class="woocommerce-Input woocommerce-Input--text input-text" type="text" name="user_login" id="user_login" />
		</div>
	
		<div class="clear"></div>
	
		<?php do_action( 'woocommerce_lostpassword_form' ); ?>
	
		<p class="woocommerce-form-row form-row">
			<input type="hidden" name="wc_reset_password" value="true" />
			<input type="submit" class="woocommerce-Button button" value="<?php esc_attr_e( 'Reset password', 'woocommerce' ); ?>" />
		</p>
	
		<?php wp_nonce_field( 'lost_password' ); ?>
	
	</form>

<?php // start: modified by Arlind ?>
</div>

<a href="<?php echo get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ); ?>" class="my-account-go-back"><?php kalium_woocmmerce_get_i18n_str( '&laquo; Go back', true ); ?></a>
<?php // end: modified by Arlind ?>