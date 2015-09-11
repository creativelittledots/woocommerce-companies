<?php
/**
 * My Addresses
 *
 * @author 		Creative Little Dots
 * @package 	WooCommerce Companies/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

do_action( 'woocommerce_before_my_account' );

?>

<h3><?php echo apply_filters( 'woocommerce_companies_view_addresses_title', __('My Addresses', 'woocommerce-companies') ); ?></h3>

<?php if( $addresses ) : ?>

	<table class="<?php echo implode(' ', apply_filters('woocommerce_companies_table_classes', array('addresses') ) ); ?>">
		
		<thead>
			
			<tr>
				
				<th><?php _e('Address Line 1'); ?></th>
				
				<th></th>
				
			</tr>
			
		</thead>

		<?php foreach($addresses as $address) : ?>
		
			<tr id="address_<?php echo $address->id; ?>">
				
				<td>
				
					<address>
						
						<?php echo $address->get_title(); ?>
						
						<?php if ( $object->primary_billing_address == $address->id ) : ?>
						
						<br>
						
						<strong><?php _e('Primary Billing'); ?></strong>
							
						<?php endif; ?>
						
						<?php if ( $object->primary_shipping_address == $address->id ) : ?>
						
						<br>
						
						<strong><?php _e('Shipping Billing'); ?></strong>
							
						<?php endif; ?>
						
					</address>
				
				</td>
				
				<td>
					
					<ul class="<?php echo implode(' ', apply_filters('woocommerce_companies_address_list_classes', array('list') ) ); ?>">
						
						<li>
				
							<a href='<?php echo $address->get_view_address_url(); ?>' class="<?php echo implode(' ', apply_filters('woocommerce_companies_view_address_button_classes', array('button') ) ); ?>"><?php _e('Edit', 'woocommerce-companies'); ?></a>
							
						</li>
						
						<?php if ( $object->primary_billing_address != $address->id ) : ?>
						
							<li>
					
								<a href='<?php echo $address->get_make_primary_address_url('billing', $object); ?>' class="<?php echo implode(' ', apply_filters('woocommerce_companies_primary_address_button_classes', array('button') ) ); ?>"><?php _e('Make Primary Billing', 'woocommerce-companies'); ?></a>
								
							</li>
						
						<?php endif; ?>
						
						<?php if ( $object->primary_shipping_address != $address->id ) : ?>
						
							<li>
					
								<a href='<?php echo $address->get_make_primary_address_url('shipping', $object); ?>' class="<?php echo implode(' ', apply_filters('woocommerce_companies_primary_address_button_classes', array('button') ) ); ?>"><?php _e('Make Primary Shipping', 'woocommerce-companies'); ?></a>
								
							</li>
							
						<?php endif; ?>
						
						<li>
							
							<a href='<?php echo $address->get_remove_address_url($object); ?>' onClick="if(!confirm('Are you sure you want to remove this Address?')) { return false; }" class="<?php echo implode(' ', apply_filters('woocommerce_companies_remove_address_button_classes', array('button') ) ); ?>"><?php _e('Remove', 'woocommerce-companies'); ?></a>
							
						</li>
						
					</ul>
					
				</td>
				
			</tr>
		
		<?php endforeach; ?>
	
	</table>
	
<?php endif; ?>

<?php do_action( 'woocommerce_after_my_account' ); ?>