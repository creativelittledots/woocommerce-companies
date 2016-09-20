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

<?php echo $description; ?>

 <label class="required"><?php _e('Purchase Order Number', 'woocommerce-companies'); ?></label>
	
 <input type="text" size="16" name="purchase_order_number" placeholder="<?php _e('Your internal Purchase Order Number for this Order.', 'woocommerce-companies'); ?>" value="<?php WC()->checkout()->get_value('purchase_order_number'); ?>" />