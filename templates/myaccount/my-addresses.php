<ul class="<?php echo implode(' ', apply_filters('woocommerce_companies_button_list_classes', array('address-actions') ) ); ?>">
	
	<?php if( $addresses ) : ?>

		<li><a href="<?php echo wc_get_endpoint_url( 'my-addresses' ); ?>" class="<?php echo implode(' ', apply_filters('woocommerce_companies_view_all_addresses_button_classes', array('button view-all-addresses') ) ); ?>"><?php _e( 'View all addresses', 'woocommerce' ); ?></a></li>
		
	<?php endif; ?>
			
	<li><a href="<?php echo wc_get_endpoint_url( 'add', '', wc_get_page_permalink('myaddresses') ); ?>" class="<?php echo implode(' ', apply_filters('woocommerce_companies_add_new_address_button_classes', array('button add-new-address') ) ); ?>"><?php _e( 'Add new address', 'woocommerce' ); ?></a></li>
			
</ul>