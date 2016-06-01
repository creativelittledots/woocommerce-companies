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
						
						<?php
							
							$actions = apply_filters( 'woocommerce_companies_company_actions', array(
								
								'view' => array(
									'classes' => apply_filters('woocommerce_companies_view_company_button_classes', array('button edit-company') ),
									'url' => $company->get_view_company_url(),
									'text' => __('Edit company', 'woocommerce-company-portals'),
								)
								
							), $company);
							
							if ( $actions ) : ?>
							
						<ul class="<?php echo implode(' ', apply_filters('woocommerce_companies_button_list_classes', array('company-actions') ) ); ?>">
							
							<?php foreach( $actions as $action ) : ?>
							
								<li>
					
									<a href='<?php echo $action['url']; ?>' class="<?php echo implode(' ', $action['classes']); ?>"><?php echo $action['text']; ?></a>
									
								</li>
								
							<?php endforeach; ?>
							
						</ul>
						
						<?php endif; ?>
						
					</td>
					
				</tr>
			
			<?php endforeach; ?>
		
		</table>
		
	<?php endif; ?>
	
	<small><?php _e( sprintf('Display %s of %s companies', min( $company_count, count($companies) ), count($companies)) ); ?></small>
	
	<ul class="<?php echo implode(' ', apply_filters('woocommerce_companies_button_list_classes', array('company-actions') ) ); ?>">
		
		<?php 
			
			$actions = array();
			
			if( $companies ) {
				 
				$actions['view_all_companies'] = array(
					'classes' => apply_filters('woocommerce_companies_view_all_companies_button_classes', array('button view-all-companies') ),
					'url' => wc_get_page_permalink('mycompanies'),
					'text' => 'View all companies',
				); 
				 
			}
			
			$actions['add_new_company'] = array(
				'classes' => apply_filters('woocommerce_companies_add_new_company_button_classes', array('button add-new-company') ),
				'url' => wc_get_endpoint_url( 'add', '', wc_get_page_permalink('mycompanies') ),
				'text' => 'Add new company',
			);
			
			foreach( apply_filters( 'woocommerce_companies_company_footer_actions', $actions, $companies ) as $action ) : ?>
		
			<li>
	
				<a class="<?php echo implode(' ', $action['classes'] ); ?>" href='<?php echo $action['url']; ?>'><?php _e( $action['text'], 'woocommerce-companies'); ?></a>
				
			</li>
		
		<?php endforeach; ?>
		
	</ul>
	
</div>

<?php do_action('woocommerce_after_my_companies'); ?>