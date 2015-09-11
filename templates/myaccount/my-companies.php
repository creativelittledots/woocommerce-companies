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
	
	do_action('woocommerce_before_my_companies');

?>

<div class="my-account-companies">
	
	<header class="title">
		
		<h3><?php _e('My Companies', 'woocommerce-companies'); ?></h3>
		
	</header>
	
	<?php if($companies) : ?>
	
		<table class="<?php echo implode(' ', apply_filters('woocommerce_companies_table_classes', array('companies') ) ); ?>">
		
			<?php foreach($companies as $company_id => $company) : ?>
			
				<tr>
					
					<td>
					
						<address>
							
							<?php echo $company->get_title(); ?>, <?php echo $company->company_number; ?>
							
						</address>
					
					</td>
					
					<td>
					
						<a href='<?php echo $company->get_view_company_url(); ?>' class="edit-company"><?php _e('Edit Company', 'woocommerce-companies'); ?></a>
						
					</td>
					
				</tr>
			
			<?php endforeach; ?>
		
		</table>
		
	<?php endif; ?>
	
	<small><?php _e( sprintf('Display %s of %s companies', min( $company_count, count($companies) ), count($companies)) ); ?></small>
	
	<ul class="<?php echo implode(' ', apply_filters('woocommerce_companies_button_list_classes', array('company-actions') ) ); ?>">
			
		<li>
	
			<a class="<?php echo implode(' ', apply_filters('woocommerce_companies_view_all_companies_button_classes', array('button view-all-companies') ) ); ?>" href='<?php echo wc_get_page_permalink('mycompanies'); ?>'><?php _e('View all companies', 'woocommerce-companies'); ?></a>
			
		</li>
		
		<li>
	
			<a class="<?php echo implode(' ', apply_filters('woocommerce_companies_add_new_company_button_classes', array('button add-new-company') ) ); ?>" href='<?php echo wc_get_endpoint_url( 'add', '', wc_get_page_permalink('mycompanies') ); ?>'><?php _e('Add New Company', 'woocommerce-companies'); ?></a>
			
		</li>
		
	</ul>
	
</div>

<?php do_action('woocommerce_after_my_companies'); ?>