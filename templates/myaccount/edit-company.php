<?php
/**
 * My Companies
 *
 * @author 		Creative Little Dots
 * @package 	WooCommerce Companies/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

wc_print_notices();

?>

<form method="post" class="edit_company">
	
	<label for="company_name"><strong><?php _e('Company Name', 'woocommerce-companies'); ?></strong></label>
	
	<input type="text" name="company_name" id="company_name" placeholder="Company Name" readonly="true" value="<?php echo $company->company_name; ?>" />
	
	<label for="company_id"><strong><?php _e('Company Number', 'woocommerce-companies'); ?></strong></label>
	
	<input type="text" name="company_number" id="company_number" placeholder="Company Number" readonly="true" value="<?php echo $company->company_number; ?>" />
	
	<div class="company_addresses">
		
		<?php $billing_addresses = $company->billing_addresses; ?>
				
		<p>
			
			<strong><?php _e('Billing Addresses:', 'woocommerce-companies'); ?></strong>
			
		</p>
		
		<table>
			
			<tr>
		
				<?php foreach($billing_addresses as $billing_address) : ?>
				
					<td>
				
						<address>
						
							<?php
								$address = apply_filters( 'woocommerce_company_address_formatted_address', array(
									'first_name'  => $billing_address->first_name,
									'last_name'   => $billing_address->last_name,
									'address_1'   => $billing_address->address_1,
									'address_2'   => $billing_address->address_2,
									'city'        => $billing_address->city,
									'state'       => $billing_address->state,
									'postcode'    => $billing_address->postcode,
									'country'     => $billing_address->coutnry
								), $company_id, 'billing' );
				
								$formatted_address = WC()->countries->get_formatted_address( $address );
				
								if ( ! $formatted_address )
									_e( 'You have not set up this type of address yet.', 'woocommerce' );
								else
									echo $formatted_address;
							?>
						
						</address>
								
						<p>
							<small>
								
								<a href='<?php echo wc_get_endpoint_url( 'edit-address', $billing_address->ID ); ?>'><?php _e('Edit Address', 'woocommerce-companies'); ?></a>
								
							</small>
							
						</p>
						
					</td>
					
				<?php endforeach; ?>
				
			</tr>
			
		</table>
			
	</div>
			
	<div class="company_addresses">
		
		<?php $shipping_addresses = $company->shipping_addresses; ?>
		
		<p>
				
			<strong><?php _e('Shipping Addresses:', 'woocommerce-companies'); ?></strong>
			
		</p>
		
		<table>
			
			<tr>
		
				<?php foreach($shipping_addresses as $shipping_address) : ?>
				
					<td>
				
						<address>
							
							<?php
								$address = apply_filters( 'woocommerce_company_address_formatted_address', array(
									'first_name'  => $shipping_address->first_name,
									'last_name'   => $shipping_address->last_name,
									'address_1'   => $shipping_address->address_1,
									'address_2'   => $shipping_address->address_2,
									'city'        => $shipping_address->city,
									'state'       => $shipping_address->state,
									'postcode'    => $shipping_address->postcode,
									'country'     => $shipping_address->coutnry
								), $company_id, 'billing' );
				
								$formatted_address = WC()->countries->get_formatted_address( $address );
				
								if ( ! $formatted_address )
									_e( 'You have not set up this type of address yet.', 'woocommerce' );
								else
									echo $formatted_address;
							?>
							
						</address>
						
						<p>
							
							<small>
					
								<a href='<?php echo wc_get_endpoint_url( 'edit-address', $shipping_address->ID ); ?>'><?php _e('Edit Address', 'woocommerce-companies'); ?></a>
								
							</small>
							
						</p>
						
					</td>
					
				<?php endforeach; ?>
				
			</tr>
			
		</table>
			
	</div>
	
	<input type="hidden" name="action" value="update_comany" />
	
	<?php wp_nonce_field('wooocommerce_companies_update_company'); ?>
	
	<button class="button"><?php _e('Update Company', 'woocommerce-companies'); ?></button>
	
	<a class="button" href="<?php echo add_query_arg(array('action' => 'remove_company', 'company_id' => $company->ID, '_wpnonce' => wp_create_nonce('woocommerce_companies_remove_company'))); ?>" onClick="if(!confirm('Are you sure you want to remove this Company?')) { return false; }"><?php _e('Remove Company', 'woocommerce-companies'); ?></a>
	
</form>