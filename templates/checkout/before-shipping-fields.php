<?php if( $shipping_addresses ) : ?>
		
	<div class="checkout_select_shipping_address_field">
			
		<?php  
        	
	    	foreach ( $checkout_fields['shipping_address_id'] as $key => $field ) {
	
				woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
			
			}
			
		?>
	
	</div>
		
<?php endif; ?>

<div class="checkout_shipping_fields">