<?php
	
/**
 * View Companies
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

<table>

	<?php foreach($companies as $company) : ?>
	
		<tr id="company_<?php echo $company->id; ?>">
			
			<td>
	
				<ul>
							
					<li>
				
						<strong><?php echo $company->get_title(); ?></strong>
						
					</li>
					
					<li>
				
						<strong><?php _e('Company ID:', 'woocommerce-companies'); ?></strong> <?php echo $company->company_number; ?>
						
					</li>
					
				</ul>
				
			</td>
			
			<td>
			
				<ul>
					
					<li>
					
						<?php $billing_addresses = $company->billing_addresses; ?>
						
						<strong><?php _e('Billing Addresses:', 'woocommerce-companies'); ?></strong>
						
						<?php foreach($billing_addresses as $billing_address) : ?>
						
							<p><?php echo $billing_address->get_title(); ?> <a href='<?php echo wc_get_endpoint_url( 'edit-address', $billing_address->id ); ?>'><?php _e('Edit Address', 'woocommerce-companies'); ?></a></p>
							
						<?php endforeach; ?>
						
					</li>
					
					<li>
					
						<?php $shipping_addresses = $company->shipping_addresses; ?>
						
						<strong><?php _e('Shipping Addresses:', 'woocommerce-companies'); ?></strong>
						
						<?php foreach($shipping_addresses as $shipping_address) : ?>
						
							<p><?php echo $shipping_address->get_title(); ?> <a href='<?php echo wc_get_endpoint_url( 'edit-address', $shipping_address->id ); ?>'><?php _e('Edit Address', 'woocommerce-companies'); ?></a></p>
							
						<?php endforeach; ?>
						
					</li>
					
				</ul>
				
			</td>
			
			<td>
	
				<a class="button" href='<?php echo wc_get_endpoint_url( 'edit', $company->id, get_permalink(get_option('woocommerce_mycompanies_page_id')) ); ?>'><?php _e('Edit Company', 'woocommerce-companies'); ?></a>
				
			</td>
			
		</tr>
	
	<?php endforeach; ?>

</table>