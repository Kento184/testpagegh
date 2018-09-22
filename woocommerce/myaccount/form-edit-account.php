<?php
/**
 * Edit account form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woothemes.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_edit_account_form' ); ?>

<?php # start: modified by Arlind ?>
<div class="section-title">
	<h1><?php kalium_woocmmerce_get_i18n_str( 'My Account', true ); ?></h1>
	<p><?php kalium_woocmmerce_get_i18n_str( 'Edit your account details or change your password', true ); ?></p>
</div>

<div class="bordered-block edit-account-block">
	<form class="woocommerce-EditAccountForm edit-account" action="" method="post">
	
		<?php do_action( 'woocommerce_edit_account_form_start' ); ?>
	
		<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first labeled-input-row labeled-input-row--equal-width">
			<label for="account_first_name"><?php _e( 'First name', 'woocommerce' ); ?> <span class="required">*</span></label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name" id="account_first_name" value="<?php echo esc_attr( $user->first_name ); ?>" />
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last labeled-input-row">
			<label for="account_last_name"><?php _e( 'Last name', 'woocommerce' ); ?> <span class="required">*</span></label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name" id="account_last_name" value="<?php echo esc_attr( $user->last_name ); ?>" />
		</p>
		<div class="clear"></div>
	
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide labeled-input-row">
			<label for="account_email"><?php _e( 'Email address', 'woocommerce' ); ?> <span class="required">*</span></label>
			<input type="email" class="woocommerce-Input woocommerce-Input--email input-text" name="account_email" id="account_email" value="<?php echo esc_attr( $user->user_email ); ?>" />
		</p>
		
		
		<h2 class="change-account-password-head">
			<?php kalium_woocmmerce_get_i18n_str( 'Password Change', true ); ?>
			<small><?php kalium_woocmmerce_get_i18n_str( '(leave blank to leave unchanged)', true ); ?></small>
		</h2>
	
		<fieldset>
			
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide labeled-input-row labeled-input-row--equal-width">
				<label for="password_current"><?php kalium_woocmmerce_get_i18n_str( 'Current Password', true ); ?></label>
				<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_current" id="password_current" />
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide labeled-input-row">
				<label for="password_1"><?php kalium_woocmmerce_get_i18n_str( 'New Password', true ); ?></label>
				<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_1" id="password_1" />
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide labeled-input-row">
				<label for="password_2"><?php _e( 'Confirm new password', 'woocommerce' ); ?></label>
				<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_2" id="password_2" />
			</p>
	
		</fieldset>
		<div class="clear"></div>
	
		<?php do_action( 'woocommerce_edit_account_form' ); ?>
	
		<p>
			<?php wp_nonce_field( 'save_account_details' ); ?>
			<input type="submit" class="woocommerce-Button button" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'woocommerce' ); ?>" />
			<input type="hidden" name="action" value="save_account_details" />
		</p>
	
		<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
	</form>
</div>

<?php do_action( 'woocommerce_after_edit_account_form' ); ?>

<a href="<?php echo get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ); ?>" class="my-account-go-back"><?php kalium_woocmmerce_get_i18n_str( '&laquo; Go back', true ); ?></a>
<?php # end: modified by Arlind ?>