<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Handle frontend forms
 *
 * @class 		WC_Companies_Form_Handler 
 * @version		1.0.0
 * @package		WooCommerce Companies/Classes/
 * @category	Class
 * @author 		Creative Little Dots
 */
class WC_Companies_Form_Handler extends WC_Form_Handler {

	/**
	 * Hook in methods
	 */
	public function __construct() {
		
		add_filter( 'woocommerce_default_address_fields', array($this, 'remove_required_from_name_fields' ) );
		add_filter( 'woocommerce_billing_fields', array($this, 'remove_required_from_email_field' ) );
		
		add_action( 'woocommerce_customer_save_address', array($this, 'customer_save_address'), 10, 2);
		add_action( 'woocommerce_companies_save_address', array($this, 'company_save_address'), 10, 3);
		
		add_action( 'woocommerce_companies_save_company', 'add_user_company', 10, 2);
		add_action( 'woocommerce_companies_save_address', 'add_user_address', 10, 2);
		
		
	}
	
	public function remove_required_from_name_fields($fields) {
		
		global $wp;

		if( ! empty( $wp->query_vars['view-address'] ) ) {
		
			$fields['first_name']['required'] = false;
			$fields['last_name']['required'] = false;
			
		}
		
		return $fields;
		
	}
	
	public function remove_required_from_email_field($fields) {
		
		global $wp;

		if( ! empty( $wp->query_vars['view-address'] ) ) {
		
			$fields['billing_email']['required'] = false;
			
		}
		
		return $fields;
		
	}
	
	public function customer_save_address($user_id, $load_address) {
		
		global $wp;
		
		$customer = get_user_by('id', $user_id);
		
		$address = isset( $wp->query_vars['view-address'] ) ? wc_get_address($wp->query_vars['view-address']) : ( $customer->{ 'primary_' . $load_address . '_address' } && get_post($customer->{ 'primary_' . $load_address . '_address' }) ? wc_get_address($customer->{ 'primary_' . $load_address . '_address' }) : new WC_Address());
			
		foreach ( WC()->countries->get_address_fields( esc_attr( $_POST[ $load_address . '_country' ] ), $load_address . '_' ) as $key => $field ) {
			
			$property = ltrim(str_replace($load_address, '', $key), '_');
		
			$address->$property = get_user_meta( $user_id, $key, true);
			
			update_user_meta( $user_id, $key,  ''); // in order to reset user meta
			
		}
		
		$address->save();
		
		do_action( 'woocommerce_companies_save_address', $user_id, $address->id, $load_address );
		
		wp_safe_redirect( wc_get_endpoint_url( 'addresses', '', wc_get_page_permalink( 'myaccount' ) ) );
		
		exit();
		
	}
	
	public function company_save_address($user_id, $address_id, $load_address) {
		
		$company_id = false;
		
		if( isset($wp->query_vars['company_id']) ) {
			
			$company_id = $wp->query_vars['company_id'];
			
		} else if ( count( wc_get_user_companies( $user_id ) ) == 1 ) {
			
			$company_id = reset( wc_get_user_companies( $user_id ) )->id;
			
		}
		
		if($company_id) {
			
			wc_add_company_address( $company_id, $address_id, $load_address );
			
			wc_remove_user_address( $user_id, $address_id, $load_address );
			
		}
		
	}

}

new WC_Companies_Form_Handler();