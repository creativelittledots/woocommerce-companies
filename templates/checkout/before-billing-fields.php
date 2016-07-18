<?php
/**
 * Cart errors page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/cart-errors.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woothemes.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
    
foreach ( $checkout_fields['checkout_type'] as $key => $field ) {

	woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );

}

?>

<div class="checkout_company_fields" <?php echo $checkout->is_type( 'company' ) ? 'style="display:none;"' : ''; ?>>
	
	<div class="checkout_select_company_field" <?php echo ! $companies ? 'style="display:none;"' : ''; ?>>
    	
    	<?php  
        	
        	foreach ($checkout_fields['company_id'] as $key => $field ) {

				woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
			
			}
			
		?>
    	
	</div>

	<div class="checkout_company_fields">
    	
    	<?php  
        	
        	foreach ( $checkout_fields['company'] as $key => $field ) {

				woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
			
			}
			
		?>
    	
	</div>
	
</div>

<div class="checkout_select_billing_address_field" <?php echo ! $billing_addresses ? 'style="display:none;"' : ''; ?>>

    <?php  
        	
    	foreach ( $checkout_fields['billing_address_id'] as $key => $field ) {

			woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
		
		}
		
	?>

	
</div>
	
<div class="checkout_billing_fields">
