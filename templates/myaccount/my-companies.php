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

?>

<div class="my-account-companies">

	<h6>
		
		<strong><?php _e('My Companies', 'woocommerce-companies'); ?></strong> 
		
	</h6>
	
	<?php if($companies) : ?>
	
		<table>
		
			<?php foreach($companies as $company_id => $company) : ?>
			
				<tr>
					
					<td>
					
						<address>
							
							<?php echo $company->get_title(); ?>, <?php echo $company->company_number; ?>
							
						</address>
					
					</td>
					
					<td>
					
						<a href='<?php echo wc_get_endpoint_url( 'edit', $company->id, get_permalink(get_option('woocommerce_myacompanies_page_id')) ); ?>'><?php _e('Edit Company', 'woocommerce-companies'); ?></a>
						
					</td>
					
				</tr>
			
			<?php endforeach; ?>
		
		</table>
		
	<?php endif; ?>
	
	<a class="button" href='<?php echo wc_get_endpoint_url( 'my-companies' ) ; ?>'><?php _e('View all companies', 'woocommerce-companies'); ?></a>
	
</div>