<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WooCommerce WC_Companies_AJAX
 *
 * AJAX Event Handler
 *
 * @class 		WC_Companies_AJAX
 * @version		2.2.0
 * @package		WooCommerce Companies/Classes
 * @category	Class
 * @author 		Creative Little Dots
 */
class WC_Companies_AJAX extends WC_Ajax {

	/**
	 * Hook in methods
	 */
	public static function init() {

		// woocommerce_EVENT => nopriv
		$ajax_events = array(
			'companies_get_addresses' => true,
			'json_search_addresses' => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
		
	}
	
	/**
	 * Get companies addresses
	 */
	public static function companies_get_addresses() {
		
		global $woocommerce_companies;
		
		$addresses_found = array();
		
		if(is_user_logged_in()) {
			
			$checkout_type = $_POST['checkout_type'];
			
			$company_id = $_POST['company_id'];
			
			$address_type = $_POST['address_type'];
			
			$addresses = get_user_addresses( get_current_user_id(), $address_type );
		
			if($checkout_type == 'company' && $company_id > 0) {
			
				if($company = wc_get_company($company_id)) {
					
					if($company->{$address_type . '_addresses'})
						$addresses = $addresses + $company->{$address_type . '_addresses'};
					
				}
				
			}
			
			foreach($addresses as $address) {
				
				$addresses_found[$address->id] = $address->get_title();
				
			}
			
		}
		
		// Get messages if reload checkout is not true
		$messages = '';
		if ( ! isset( WC()->session->reload_checkout ) ) {
			ob_start();
			wc_print_notices();
			$messages = ob_get_clean();
		}
		
		$data = array(
			'result'    => empty( $messages ) ? 'success' : 'failure',
			'messages'  => $messages,
			'reload'    => isset( WC()->session->reload_checkout ) ? 'true' : 'false',
			'addresses' => $addresses_found,
			'request' => $_POST,
		);
		
		wp_send_json( $data );
		
		die();
		
	}
	
	/**
	 * Search for addresses
	 */
	public static function json_search_addresses() {
			
		ob_start();

		$term = wc_clean( stripslashes( $_GET['term'] ) );

		if ( empty( $term ) ) {
			die();
		}

		$args = array(
			's' => $term,
		);
		
		$addresses = wc_get_addresses($args);
		
		$addresses_found = array();
		
		foreach( $addresses as $address) {
			
			$addresses_found[$post->ID] = $address->post_title;
			
		}

		wp_send_json( $addresses_found );
		
	}

}

WC_Companies_AJAX::init();