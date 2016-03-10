<?php
/**
 * Contains the email functions for WooCommerce Companies which alter the content on emails.
 *
 * @class 		WC_Companies_Emails
 * @version		1.0.0
 * @package		WooCommerce Companies/Classes
 * @category	Class
 * @author 		Creatove Little Dots
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Companies_Emails' ) ) :

    class WC_Companies_Emails {
    	
    	public function __construct() {
    		
    		add_action('woocommerce_email_after_order_table', array($this, 'display_company_info_on_emails'), 2);
    		
    	}
    	
    	public function display_company_info_on_emails($order) {
    		
    		if( $order->company_id ) {
    				
    			$company = wc_get_company($order->company_id);
    			
    			?>
    			
    			<h2><?php _e('Company Details', 'woocommerce'); ?></h2>
    		    <p><?php echo $company->get_title(); ?></p>
    		    
    		    <?php
    			
    		}
    		
    	}
    	
    }

endif;

return new WC_Companies_Emails();