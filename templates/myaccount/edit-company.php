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
	
	<h3 class="redHead"><i class="icon-address"></i> <?php echo apply_filters( 'woocommerce_companies_edit_company_title', __('Edit Company', 'woocommerce-companies') ); ?></h3>
	
	<?php foreach ( $fields as $key => $field ) : $key = preg_replace('/[^A-Za-z0-9_\-]/', '', $key);?>
	
		<?php woocommerce_form_field($key, $field, $company ? get_post_meta($company->id, '_' . $key, true) : ''); ?>
			
	<?php endforeach; ?>
	
	<input type="hidden" name="action" value="save_company" />
	
	<?php wp_nonce_field('woocommerce-save_company'); ?>
	
	<button class="<?php echo implode(' ', apply_filters('woocommerce_companies_save_company_button_classes', array('button') ) ); ?>"><?php _e( ($company ? 'Update' : 'Add') . ' Company', 'woocommerce-companies'); ?></button>
	
	<?php if ( $company ) : ?>
	
		<a class="<?php echo implode(' ', apply_filters('woocommerce_companies_remove_company_button_classes', array('button') ) ); ?>" href="<?php echo wc_get_endpoint_url('remove', $company->id); ?>" onClick="if(!confirm('Are you sure you want to remove this Company?')) { return false; }"><?php _e('Remove Company', 'woocommerce-companies'); ?></a>
		
	<?php endif; ?>
	
</form>