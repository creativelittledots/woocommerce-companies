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

<h3 class="redHead"><i class="icon-address"></i> <?php echo apply_filters( 'woocommerce_companies_view_companies_title', __('My Companies', 'woocommerce-companies') ); ?></h3>

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
						
					<?php else : ?>
					
						<p><?php _e('No address set.', 'woocommerce-companies'); ?>
						
					<?php endif; ?>
					
				</td>
				
				<td>
					
					<?php if( $company->get_primary_shipping_address() ) : ?>
					
						<p><?php echo $company->get_primary_shipping_address()->get_title(); ?></p> 
					
					<?php else : ?>
					
						<p><?php _e('No address set.', 'woocommerce-companies'); ?></p>
						
					<?php endif; ?>
					
				</td>
				
				<td>
					
					<?php
    					
    					$actions = apply_filters( 'woocommerce_companies_company_actions',  array(
        					
        					'edit_company' => array(
								'url' => $company->get_view_company_url(),
								'text' => __('Edit Company', 'woocommerce-companies'),
							)
        					
    					), $company );
    					
    					if( $company->get_primary_billing_address() ) {
        					
        					$actions['edit_billing_address'] = array(
								'url' => $company->get_primary_billing_address()->get_view_address_url(),
								'text' => __('Edit Billing Address', 'woocommerce-companies'),
							);
        					
    					}
    					
    					if( $company->get_primary_shipping_address() ) {
        					
        					$actions['edit_shipping_address'] = array(
								'url' => $company->get_primary_shipping_address()->get_view_address_url(),
								'text' => __('Edit Billing Address', 'woocommerce-companies'),
							);
        					
    					}
    					
    					$actions['view_company_addresses'] = array(
							'url' => wc_get_endpoint_url( 'my-companies/addresses', $company->id, wc_get_page_permalink( 'myaccount' ) ),
							'text' => __('View Company Addresses', 'woocommerce-companies'),
						);
						
						if ( $actions ) : ?>
						
						<button href="#" data-dropdown="drop1" aria-controls="drop1" aria-expanded="false" class="button dropdown line tiny no-margin">Actions</button><br>
						
						<ul class="f-dropdown <?php echo implode(' ', apply_filters('woocommerce_companies_button_list_classes', array('company-actions') ) ); ?>" id="drop1" data-dropdown-content aria-hidden="true">
							
							<?php foreach( $actions as $action ) : ?>
													
								<li>
				
									<a href='<?php echo $action['url']; ?>'><?php echo $action['text']; ?></a>
									
								</li>
								
							<?php endforeach; ?>
							
						</ul>
						
					<?php endif; ?>
					
				</td>
				
			</tr>
		
		<?php endforeach; ?>
	
	</table>
	
<?php else : ?>

	<p><?php _e('No companies found.', 'woocommerce-companies'); ?>
	
<?php endif; ?>

<?php do_action( 'woocommerce_after_my_account' ); ?>