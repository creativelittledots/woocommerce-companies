<?php
/**
 * Contains the query functions for WooCommerce Companies which alter the front-end post queries and loops.
 *
 * @class 		WC_Companies_Query
 * @version		1.0.0
 * @package		WooCommerce Companies/Classes
 * @category	Class
 * @author 		Creatove Little DOts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Companies_Query' ) ) :

/**
 * WC_Companies_Query Class
 */
class WC_Companies_Query {


	public function __construct() {
		
		add_action( 'init', array($this, 'companies_rewrite_rules') );
		
	}
	
	public function companies_rewrite_rules() {
			
		add_rewrite_tag('%edit-company%', '([^&]+)');
		
		$mycomanies_page_id = get_option('woocommerce_mycompanies_page_id');
		
		add_rewrite_rule('my-account/my-companies/edit/([^/]*)/?','index.php?page_id=' . $mycomanies_page_id . '&edit-company=$matches[1]','top');
		
	}

}

endif;

return new WC_Companies_Query();
