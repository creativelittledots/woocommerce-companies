<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WooCommerce WC_Companies_Display
 *
 *
 * @class 		WC_Companies_Display
 * @version		1.0.0
 * @package		WooCommerce Companies/Classes
 * @category	Class
 * @author 		Creative Little Dots
 */
class WC_Companies_Display {
	
	/**
	 * Hook in methods
	 */
	public function init() {

		add_action( 'woocommerce_after_my_account', array($this, 'display_my_companies') );
		
		add_filter( 'woocommerce_my_account_my_address_formatted_address', array($this, 'get_address_on_my_account'), 10, 3 );
			
		add_filter( 'woocommerce_my_account_get_addresses', array($this, 'get_addresses_on_my_account'), 10, 2 );
			
		add_filter( 'woocommerce_my_account_my_address_title', array($this, 'add_edit_tag_to_address_title') );
		
		add_action( 'woocommerce_checkout_before_customer_details', array($this, 'add_checkout_type_field') );
			
		add_action( 'woocommerce_checkout_before_customer_details', array($this, 'add_company_id_field') );
		
		add_action( 'woocommerce_checkout_before_customer_details', array($this, 'add_company_details_fields') );
		
		add_action( 'woocommerce_before_checkout_billing_form', array($this, 'before_checkout_billing_fields') );
		
		add_action( 'woocommerce_after_checkout_billing_form', array($this, 'after_checkout_billing_fields') );
		
		add_action( 'woocommerce_before_checkout_shipping_form', array($this, 'before_checkout_shipping_fields') );
		
		add_action( 'woocommerce_after_checkout_shipping_form', array($this, 'after_checkout_shipping_fields') );
		
		add_filter( 'woocommerce_billing_fields', array($this, 'hide_billing_company_on_checkout') );
			
		add_filter( 'woocommerce_shipping_fields', array($this, 'hide_shipping_compan_on_checkout') );
				
	}
	
	/**
	 * displays companies on my account page
	 *
	 */
	public function display_my_companies() {
			
		$user_id = get_current_user_id();
		
		$companies = get_user_companies();
		
		if(count($companies) > 0) {
			
			wc_get_template('myaccount/my-companies.php', array(
				'user' => get_user_by('id', $user_id),
				'companies' => $companies,
			), '', WC_Companies()->plugin_path() . '/templates/');
		
		}
		
		
		
	}
	
	/**
	 * returns billing and shipping field of company or user on my account page
	 *
	 * @param array $address Array of address data for us in formatted address
	 * @param int $customer_id ID of user logged in
	 * @param string $name Address type 'billing' or 'shipping'
	 */
	public function get_address_on_my_account($address, $customer_id, $name) {
		
		$companies = get_user_companies();
		
		if(count($companies) == 1) {
			
			$company = reset($companies);
			
			$address = get_object_vars(reset($company->{$name . '_addresses'}));
			
		}
		
		else {
			
			$address = array();
			
		}
		
		return $address;
		
	}
	
	/**
	 * hides addresses on my account when user has more than one company
	 *
	 * @param array $addresses Array of addresses
	 * @param int $customer_id ID of user logged in
	 */
	public function get_addresses_on_my_account($addresses, $customer_id) {
		
		if(count(get_user_companies()) > 1) {
			
			$addresses = array();
			
		}
		
		return $addresses;
		
	}
	
	/**
	 * adds edit tage to the address header title
	 *
	 * @param string $title Title of of address section
	 */
	public function add_edit_tag_to_address_title($title) {
		
		if(!is_wc_endpoint_url( 'edit-address' )) {

			$title = '<strong>' . $title . '</strong> <small><a href="' . wc_get_endpoint_url( 'view-addresses' ) . '">' . __( 'View all addresses', 'woocommerce' ) . '</a></small>';
				
		}
		
		return $title;
		
	}
	
	/**
	 * adds checkout type field to checkout
	 *
	 */
	public function add_checkout_type_field() {
				
		woocommerce_form_field('checkout_type', array(
			'label' => __('How are you checking out?', 'woocommerce'),
			'type' => 'radio',
			'options' => array(
				'customer' =>__( ' As an Individual', 'woocommerce'),
				'company' => __(' As a Company', 'woocommerce'),
			),
			'default' => WC_Companies()->checkout()->checkout_type,
			'label_class' => array('inline'),
		));
		
	}
	
	/**
	 * adds company id field to checkout
	 *
	 */
	public function add_company_id_field() {
		
		$style = WC_Companies()->checkout()->checkout_type != 'company' ? 'style="display:none;"' : '';
		
		echo '<div class="checkout_company_fields" ' . $style . '>';
			
			echo '<div class="checkout_select_company_field"';
		
			if(!WC_Companies()->checkout()->companies) {
				
				echo 'style="display:none;"';
				
			}
			
			echo '>';
			
			woocommerce_form_field('company_id', array(
				'label' => __('Which Company are you respresenting?', 'woocommerce'),
				'type' => 'select',
				'options' => array(0 => 'Select or Add new Company', -1 => 'Add new Company') + (WC_Companies()->checkout()->companies ? WC_Companies()->checkout()->companies : array()),
				'default' => WC_Companies()->checkout()->companies ? reset(array_keys(WC_Companies()->checkout()->companies)) : -1,
				'input_class' => array('company_select'),
			));
			
		echo '</div>';
			
	}
	
	/**
	 * adds company details fields to checkout
	 *
	 */
	public function add_company_details_fields() {
			
		echo '<div class="checkout_add_company_fields"';
			
			if(WC_Companies()->checkout()->companies) {
				
				echo 'style="display:none;"';
				
			}
			
			echo '>';
				
				woocommerce_form_field('company_name', array(
					'label' => __('Company Name', 'woocommerce'),
					'type' => 'text',
					'required' => true,
					'placeholder' => __('Company Name', 'woocommerce'),
					'class' => array('form-row form-row-first'),
					'default' => WC()->checkout()->get_value('company_name'),
					'input_class' => array('widefat'),
				));
				
				woocommerce_form_field('company_number',  array(
					'label' => __('Company Number', 'woocommerce'),
					'type' => 'text',
					'required' => true,
					'placeholder' => __('Company Number', 'woocommerce'),
					'class' => array('form-row form-row-last'),
					'default' => WC()->checkout()->get_value('company_number'),
					'input_class' => array('widefat'),
				));
			
			echo '</div>';
			
			do_action('woocommerce_companies_after_company_fields');
			
		echo '</div>';
		
	}
	
	/**
	 * add billing address if field to the checkout
	 *
	 */
	public function before_checkout_billing_fields() {
		
		echo '<div class="checkout_select_billing_address_field"';
		
		if(!WC_Companies()->checkout()->billing_addresses) {
			
			echo 'style="display:none;"';
			
		}
		
		echo '>';

		$billing_addresses = array();
		
		foreach(WC_Companies()->checkout()->billing_addresses as $billing_address) {
			
			$billing_addresses[$billing_address->id] = $billing_address->title; 
			
		}
		
		woocommerce_form_field('billing_address_id', array(
			'label' => __('Billing Address', 'woocommerce'),
			'type' => 'select',
			'options' => array(0 => 'Select or Add new Address', -1 => 'Add new Address') + ($billing_addresses ? $billing_addresses : array()),
			'input_class' => array('address_select'),
			'default' => ($billing_addresses ? reset(array_keys($billing_addresses)) : -1),
			'custom_attributes' => array(
				'data-address_type' => 'billing',	
			)
		));
		
		echo '</div>';
		
		echo '<div class="checkout_billing_fields"';
		
		if(WC_Companies()->checkout()->billing_addresses) {
			
			echo 'style="display:none;"';
			
		}
		
		echo '>';
		
	}
	
	/**
	 * display closeing div after checkout billing fields
	 *
	 */
	public function after_checkout_billing_fields() {
		
		echo '</div>';
		
	}
	
	/**
	 * add shipping address if field to the checkout
	 *
	 */
	public function before_checkout_shipping_fields() {
		
		echo '<div class="checkout_select_shipping_address_field"';
		
		if(!WC_Companies()->checkout()->shipping_addresses) {
			
			echo 'style="display:none;"';
			
		}
		
		echo '>';
		
		woocommerce_form_field('shipping_address_id', array(
			'label' => __('Shipping Address', 'woocommerce'),
			'type' => 'select',
			'options' => array(0 => 'Select or Add new Address', -1 => 'Add new Address') + (WC_Companies()->checkout()->shipping_addresses ? WC_Companies()->checkout()->shipping_addresses : array()),
			'input_class' => array('country_select'),
			'default' => (WC_Companies()->checkout()->shipping_addresses ? reset(array_keys(WC_Companies()->checkout()->shipping_addresses)) : -1),
			'custom_attributes' => array(
				'data-address_type' => 'shipping',	
			)
		));
		
		echo '</div>';
		
		echo '<div class="checkout_shipping_fields"';
		
		if(WC_Companies()->checkout()->shipping_addresses) {
			
			echo 'style="display:none;"';
			
		}
		
		echo '>';
		
	}
	
	/**
	 * display closing div after checkout shipping fields
	 *
	 */
	public function after_checkout_shipping_fields() {
		
		echo '</div>';
		
	}
	
	/**
	 * hides billing company field on checkout
	 *
	 * @param array $fields Array of billing fields
	 */
	public function hide_billing_company_on_checkout($fields) {
			
		unset($fields['billing_company']);
		
		return $fields;
		
	}	
	
	/**
	 * hides shipping company field on checkout
	 *
	 * @param array $fields Array of shipping fields
	 */
	public function hide_shipping_company_on_checkout($fields) {
		
		unset($fields['shipping_company']);
		
		return $fields;
		
	}

}