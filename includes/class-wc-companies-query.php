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
		
		add_action( 'init', array($this, 'companies_rewrite_rules'), 20 );
		add_action( 'init', array($this, 'addresses_rewrite_rules'), 20 );
		
	}
	
	public function companies_rewrite_rules() {
			
		add_rewrite_tag('%company_action%', '([^&]+)');
		
		add_rewrite_tag('%company_id%', '([^&]+)');
		
		add_rewrite_rule('my-account/my-companies/addresses/([^/]*)/primary/([^/]*)/([^/]*)?','index.php?page_id=' . wc_get_page_id('mycompanies') . '&company_action=primary-address&company_id=$matches[1]&address_type=$matches[2]&address_id=$matches[3]','top');
		
		add_rewrite_rule('my-account/my-companies/addresses/([^/]*)/remove/([^/]*)?','index.php?page_id=' . wc_get_page_id('mycompanies') . '&company_action=remove-address&company_id=$matches[1]&address_type=$matches[2]&address_id=$matches[3]','top');
		
		add_rewrite_rule('my-account/my-companies/([^/]*)/([^/]*)/?','index.php?page_id=' . wc_get_page_id('mycompanies') . '&company_action=$matches[1]&company_id=$matches[2]','top');
		
		add_rewrite_rule('my-account/my-companies/([^/]*)/?','index.php?page_id=' . wc_get_page_id('mycompanies') . '&company_action=$matches[1]','top');
		
	}
	
	public function addresses_rewrite_rules() {
		
		add_rewrite_tag('%address_action%', '([^&]+)');
		
		add_rewrite_tag('%address_type%', '([^&]+)');
		
		add_rewrite_tag('%address_id%', '([^&]+)');
		
		add_rewrite_rule('my-account/edit-address/([^/]*)/?','index.php?page_id=' . wc_get_page_id('myaddresses') . '&address_action=edit&address_type=$matches[1]','top');
		
		add_rewrite_rule('my-account/my-addresses/primary/([^/]*)/([^/]*)/?','index.php?page_id=' . wc_get_page_id('myaddresses') . '&address_action=primary&address_type=$matches[1]&address_id=$matches[2]','top');
		
		add_rewrite_rule('my-account/my-addresses/([^/]*)/([^/]*)/?','index.php?page_id=' . wc_get_page_id('myaddresses') . '&address_type=billing&address_action=$matches[1]&address_id=$matches[2]','top');
		
		add_rewrite_rule('my-account/my-addresses/([^/]*)/?','index.php?page_id=' . wc_get_page_id('myaddresses') . '&address_type=billing&address_action=$matches[1]','top');
		
	}

}

endif;

return new WC_Companies_Query();
