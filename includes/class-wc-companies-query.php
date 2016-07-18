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
	
		var $custom_vars = [
			'companies' => 'companies',
			'edit-company' => 'edit-company',
			'add-company' => 'add-company',
			'remove-company' => 'remove-company',
			'primary-company' => 'primary-company',
			'company-addresses' => 'company-addresses',
			'company-primary-address' => 'company-primary-address',
			'company-remove-address' => 'company-remove-address',
			'addresses' => 'addresses',
			'view-address' => 'view-address',
			'add-address' => 'add-address',
			'remove-address' => 'remove-address',
			'primary-address' => 'primary-address',
		];
	
		public function __construct() {
			
			add_filter( 'query_vars', array( $this, 'add_query_vars'), 0 );
			add_action( 'init', array($this, 'add_rewrite_rules') );
			
		}
		
		public function add_rewrite_rules() {
			
			$mask = $this->get_endpoints_mask();
				
			foreach ( $this->custom_vars as $key => $var ) {
				
				if ( ! empty( $var ) ) {
					
					add_rewrite_endpoint( $var, $mask );
					
				}
				
			}
			
		}
		
		public function get_endpoints_mask() {
				
			if ( 'page' === get_option( 'show_on_front' ) ) {
				
				$page_on_front     = get_option( 'page_on_front' );
				
				$myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
				
				$checkout_page_id  = get_option( 'woocommerce_checkout_page_id' );
				
				if ( in_array( $page_on_front, array( $myaccount_page_id, $checkout_page_id ) ) ) {
					
					return EP_ROOT | EP_PAGES;
					
				}
				
			}
			
			return EP_PAGES;
			
		}
		
		public function add_query_vars( $vars ) {
				
			$vars = array_merge($vars, $this->custom_vars);
			
			return $vars;
			
		}
	
	}

endif;

return new WC_Companies_Query();
