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

do_action( 'woocommerce_before_my_account' );

?>

<h3><?php echo apply_filters( 'woocommerce_companies_view_companies_title', __('My Companies', 'woocommerce-companies') ); ?></h3>

<?php if ( $companies ) : ?>

	<table class="<?php echo implode(' ', apply_filters('woocommerce_companies_table_classes', array('companies') ) ); ?>">
		
		<thead>
			
			<tr>
				
				<th><?php _e('Company Name'); ?></th>
				
				<th><?php _e('Company Number'); ?></th>
				
				<th><?php _e('Billing Address'); ?></th>
				
				<th><?php _e('Shipping Address'); ?></th>
				
				<th></th>
				
			</tr>
			
		</thead>
	
		<?php foreach($companies as $company) : ?>
		
			<tr id="company_<?php echo $company->id; ?>" class="<?php echo $current_user->primary_company == $company->id ? implode(' ', apply_filters('woocommerce_companies_primary_company_row_class', array('primary'))) : ''; ?>">
				
				<td>
					
					<p><?php echo $company->get_title(); ?></p>
					
					<?php if ( $current_user->primary_company == $company->id ) : ?>
					
						<br>
						
						<strong><?php _e('Primary Company', 'woocommerce-companies'); ?></strong>
						
					<?php endif; ?>
					
				</td>
				
				<td>
					
					<?php echo $company->company_number; ?>
					
				</td>
				
				<td>
					
					<?php if( $company->get_primary_billing_address() ) : ?>
					
						<p><?php echo $company->get_primary_billing_address()->get_title(); ?></p>
						
						<ul class="<?php echo implode(' ', apply_filters('woocommerce_companies_button_list_classes', array('address-actions') ) ); ?>">
							
							<li>		
						
								<a href='<?php echo $company->get_primary_billing_address()->get_view_address_url(); ?>' class="<?php echo implode(' ', apply_filters('woocommerce_companies_view_address_button_classes', array('button edit-address') ) ); ?>"><?php _e('Edit Address', 'woocommerce-companies'); ?></a>
								
							</li>
							
							<li>
						
								<a href='<?php echo wc_get_endpoint_url( 'my-companies/addresses', $company->id, wc_get_page_permalink( 'myaccount' ) ); ?>' class="<?php echo implode(' ', apply_filters('woocommerce_companies_view_all_addresses_button_classes', array('button view-all-addresses') ) ); ?>"><?php _e('View all addresses', 'woocommerce-companies'); ?></a>
								
							</li>
							
						</ul>
						
					<?php else : ?>
					
						<p><?php _e('No address set.', 'woocommerce-companies'); ?>
						
					<?php endif; ?>
					
				</td>
				
				<td>
					
					<?php if( $company->get_primary_shipping_address() ) : ?>
					
						<p><?php echo $company->get_primary_shipping_address()->get_title(); ?></p> 
						
						<ul class="<?php echo implode(' ', apply_filters('woocommerce_companies_button_list_classes', array('address-actions') ) ); ?>">
							
							<li>		
						
								<a href='<?php echo $company->get_primary_shipping_address()->get_view_address_url(); ?>' class="<?php echo implode(' ', apply_filters('woocommerce_companies_view_address_button_classes', array('button edit-address') ) ); ?>"><?php _e('Edit Address', 'woocommerce-companies'); ?></a>
								
							</li>
							
							<li>
						
								<a href='<?php echo wc_get_endpoint_url( 'my-companies/addresses', $company->id, wc_get_page_permalink( 'myaccount' ) ); ?>' class="<?php echo implode(' ', apply_filters('woocommerce_companies_view_all_addresses_button_classes', array('button view-all-addresses') ) ); ?>"><?php _e('View all addresses', 'woocommerce-companies'); ?></a>
								
							</li>
							
						</ul>
					
					<?php else : ?>
					
						<p><?php _e('No address set.', 'woocommerce-companies'); ?></p>
						
					<?php endif; ?>
					
				</td>
				
				<td>
		
					<a class="<?php echo implode(' ', apply_filters('woocommerce_companies_view_company_button_classes', array('button') ) ); ?>" href='<?php echo wc_get_endpoint_url( 'edit', $company->id, get_permalink(get_option('woocommerce_mycompanies_page_id')) ); ?>'><?php _e('Edit Company', 'woocommerce-companies'); ?></a>
					
				</td>
				
			</tr>
		
		<?php endforeach; ?>
	
	</table>
	
<?php else : ?>

	<p><?php _e('No companies found.', 'woocommerce-companies'); ?>
	
<?php endif; ?>

<?php do_action( 'woocommerce_after_my_account' ); ?>