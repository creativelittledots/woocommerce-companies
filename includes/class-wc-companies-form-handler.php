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
		
		add_action( 'wp', array( __CLASS__, 'save_company' ), 20 );
		add_action( 'woocommerce_customer_save_address', array($this, 'customer_save_address'), 10, 2);
		add_action( 'woocommerce_companies_save_address', array($this, 'company_save_address'), 10, 3);
		
		add_action( 'woocommerce_companies_save_company', 'add_user_company', 10, 2);
		add_action( 'woocommerce_companies_save_address', 'add_user_address', 10, 2);
		
		
	}

	/**
	 * Save and and update a company
	 */
	public static function save_company() {
		global $wp;

		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) {
			return;
		}

		if ( empty( $_POST[ 'action' ] ) || 'save_company' !== $_POST[ 'action' ] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'woocommerce-save_company' ) ) {
			return;
		}

		$user_id = get_current_user_id();

		if ( $user_id <= 0 ) {
			return;
		}
		
		$company = new WC_Company();
		
		foreach(WC_Companies()->addresses->get_company_fields() as $key => $field) {
			
			$key = preg_replace('/[^A-Za-z0-9_\-]/', '', $key);
			
			if ( $field['public'] && $field['required'] && ( ! isset( $_POST[$key] ) || empty($_POST[$key]) ) ) {
				
				wc_add_notice( __( $field['label'] . ' is a required field.', 'woocommerce-companies' ), 'error' );
				
			}
			
			if( isset($_POST[$key]) ) {
				
				$company->$key = $_POST[$key];
				
			}
				
		}
		
		if($company_id = $wp->query_vars['company_id'] ) {
			
			$company->id = $company_id;
			
			if( ! current_user_can( 'edit_company', $company_id ) ) {
				
				wc_add_notice( __( 'You do not have the privelages to edit this company.', 'woocommerce-companies' ), 'error' );
					
			}
			
		}

		if ( wc_notice_count( 'error' ) == 0 ) {
			
			$company->save();
			
			wc_add_notice( __( 'Company updated successfully.', 'woocommerce-companies' ) );

			do_action( 'woocommerce_companies_save_company', $user_id, $company->id );

			wp_safe_redirect( $company->get_view_company_url() );
			
			exit;
			
		}
		
	}
	
	public function customer_save_address($user_id, $load_address) {
		
		global $wp;
		
		$customer = get_user_by('id', $user_id);
		
		$address = isset( $wp->query_vars['address_id'] ) ? wc_get_address($wp->query_vars['address_id']) : ( $customer->{ 'primary_' . $load_address . '_address' } && get_post($customer->{ 'primary_' . $load_address . '_address' }) ? wc_get_address($customer->{ 'primary_' . $load_address . '_address' }) : new WC_Address());
			
		foreach ( WC()->countries->get_address_fields( esc_attr( $_POST[ $load_address . '_country' ] ), $load_address . '_' ) as $key => $field ) {
			
			$property = ltrim(str_replace($load_address, '', $key), '_');
		
			$address->$property = get_user_meta( $user_id, $key, true);
			
			update_user_meta( $user_id, $key,  ''); // in order to reset user meta
			
		}
		
		$address->save();
		
		do_action( 'woocommerce_companies_save_address', $user_id, $address->id, $load_address );
		
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