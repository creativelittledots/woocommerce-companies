<?php
	
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WooCommerce WC_Companies_Checkout
 *
 * Handles Checkout Processes
 *
 * @class 		WC_Companies_Checkout
 * @version		1.0.0
 * @package		WooCommerce Companies/Classes
 * @category	Class
 * @author 		Creative Little Dots
 */
 
class WC_Companies_Checkout extends WC_Checkout {
	
	/**
	 * var $companies
	 */
	public $companies = array();
	
	/**
	 * var $billing_addresses
	 */
	public $billing_addresses = array();
	
	/**
	 * var $billing_addresses
	 */
	public $shipping_addresses = array();
	
	/**
	 * var $checkout_type
	 */
	public $checkout_type = 'customer';
	
	/**
	 * var $company_id
	 */
	public $company_id = 0;
	
	/**
	 * var $company
	 */
	public $company = null;
	
	/**
	 * var $billing_address
	 */
	private $billing_address = null;
	
	/**
	 * var $shipping_address
	 */
	private $shipping_address = null;
	
	/**
	 * var $set_checkout_fields
	
	/**
	 * @var WC_Companies_Checkout The single instance of the class
	 * @since 2.1
	 */
	protected static $_instance = null;

	/**
	 * Main WC_Checkout Instance
	 *
	 * Ensures only one instance of WC_Checkout is loaded or can be loaded.
	 *
	 * @since 2.1
	 * @static
	 * @return WC_Checkout Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	}

	public function __construct() {
		
		add_action( 'woocommerce_after_checkout_validation', array($this, 'billing_address_validation') );
		
		add_action( 'woocommerce_after_checkout_validation', array($this, 'shipping_address_validation') );
		
		add_action( 'woocommerce_after_checkout_validation', array($this, 'company_validation') );
		
		add_action( 'woocommerce_checkout_update_order_meta', array($this, 'update_order_addresses'), 10, 2);
		
		add_action( 'woocommerce_checkout_update_user_meta', array($this, 'update_user_addresses'), 10, 2);
		
		add_filter( 'woocommerce_checkout_update_customer_data', function() { return false; });
		
		add_filter( 'woocommerce_ship_to_different_address_checked', array($this, 'set_ship_to_different_address') );
		
		add_action( 'wp', array($this, 'set_variables') );
		
		add_action( 'woocommerce_checkout_init', array($this, 'set_variables') );
		
		add_action( 'woocommerce_checkout_init', array($this, 'set_checkout_fields_as_not_required') );
		
		add_filter( 'woocommerce_package_rates', array($this, 'display_only_free_shipping_when_company_has_free_shipping') );
		
	}
	
	/**
	 * Set variables for us in during class
	 *
	 */
	public function set_variables($checkout) {
		
		if(!is_a($checkout, 'WC_Checkout') && !is_checkout()) {
			return;
		}
		
		$checkout = is_a($checkout, 'WC_Checkout') ? $checkout : WC()->checkout();
		
		$this->checkout_type = $checkout->get_value('checkout_type') ? $checkout->get_value('checkout_type') : $this->checkout_type;
	
		$companies = wc_get_user_companies();
		
		if($companies) {
			
			$this->checkout_type = $checkout->get_value('checkout_type') ? $checkout->get_value('checkout_type') : 'company';
			
		}
		
		foreach($companies as $company) {
			
			$this->companies[$company->id] = $company->title;
			
		}
		
		$this->company_id = $checkout->get_value('company_id') ? $checkout->get_value('company_id') : reset(array_keys($this->companies) ? array_keys($this->companies) : array());
		
		$this->company = $this->company_id > 0 ? wc_get_company($this->company_id) : null;
		
		$this->billing_addresses = $this->company_id > 0 ? $this->company->get_billing_addresses() : ($companies ? reset($companies)->get_billing_addresses() : get_user_addresses(get_current_user_id(), 'billing'));
		
		$this->shipping_addresses = $this->company_id > 0 ? $this->company->get_shipping_addresses() : ($companies ? reset($companies)->get_shipping_addresses() : get_user_addresses(get_current_user_id(), 'shipping'));
		
		$billing_address_id = $checkout->get_value('billing_address_id');
		
		if($billing_address_id > 0) {
			
			$this->billing_address = wc_get_address($billing_address_id);
			
		}
		
		$shipping_address_id = $checkout->get_value('shipping_address_id');
		
		if($shipping_address_id > 0) {
			
			$this->shipping_address = wc_get_address($shipping_address_id);
			
		}
		
	}
	
	/**
	 * Validation message displayed when billing address is not set
	 *
	 */
	public function billing_address_validation() {
		
		if(is_user_logged_in() && ((isset($_POST['billing_address_id']) && $_POST['billing_address_id'] == 0) || (WC()->checkout()->get_value('billing_address_id') == 0))) {
			
			wc_add_notice( '<strong>Billing Address</strong> ' . __( 'is a required field.', 'woocommerce' ), 'error' );
			
		}
		
	}
	
	/**
	 * Validation message displayed when shipping address is not set
	 *
	 */
	public function shipping_address_validation() {
		
		if(is_user_logged_in() && ((isset($_POST['billing_address_id']) && $_POST['billing_address_id'] == 0) || (WC()->checkout()->get_value('billing_address_id') == 0 && !$_POST['shipping_address_id'] && !WC()->checkout()->get_value('shipping_address_id')))) {
			
			wc_add_notice( '<strong>Shipping Address</strong> ' . __( 'is a required field.', 'woocommerce' ), 'error' );
			
		}

		
	}
	
	/**
	 * Validation message displayed when company is not set
	 *
	 */
	public function company_validation() {
		
		if(isset($_POST['checkout_type']) && isset($_POST['checkout_type']) && $_POST['checkout_type'] == 'company' && 1 > $_POST['company_id']) {
			
			if(isset($_POST['company_name']) && !$_POST['company_name']) {
			
				wc_add_notice( '<strong>Company Name</strong> ' . __( 'is a required field.', 'woocommerce' ), 'error' );
				
			}
			
			if(isset($_POST['company_number']) && !$_POST['company_number']) {
				
				wc_add_notice( '<strong>Company Number</strong> ' . __( 'is a required field.', 'woocommerce' ), 'error' );
				
			}
		
		}
		
	}
	
	/**
	 * Get billing addresses
	 *
	 */
	public function get_billing_addresses() {
		return apply_filters('woocommerce_companies_checkout_get_billing_addresses', $this->billing_addresses, $this);
	}
	
	/**
	 * Get shipping addresses
	 *
	 */
	public function get_shipping_addresses() {
		return apply_filters('woocommerce_companies_checkout_get_shipping_addresses', $this->shipping_addresses, $this);
	}
	
	/**
	 * Updates the order addreses during checkout process
	 *
	 * @param int $order_id ID of Order being created
	 * @param array $posted Data sent in $POST
	 */
	public function update_order_addresses($order_id, $posted) {
		
		global $woocommerce_companies;
		
		$order = new WC_Order($order_id);
		
		if($this->billing_address) {
			
			// Billing address
			$billing_address = array();
			
			if ( $billingFields = array_keys(WC()->countries->get_address_fields( '', 'billing_' )) ) {
				
				foreach ( $billingFields as $field ) {
					
					$field = str_replace('billing_', '', $field);
					
					$billing_address[ $field ] = esc_sql($this->billing_address->$field);
					
				}
				
			}
			
			if($billing_address) {
				
				$order->set_address( $billing_address, 'billing' );
				
			}
			
		}
		
		if($this->shipping_address) {

			// Shipping address.
			$shipping_address = array();
			
			if ( $shippingFields = array_keys(WC()->countries->get_address_fields( '', 'shipping_' )) ) {
				
				foreach ( $shippingFields as $field ) {
					
					$field = str_replace('shipping_', '', $field);
					
					$shipping_address[ $field ] = esc_sql($this->shipping_address->$field);
					
				}
				
			}
			
			if($shipping_address) {
				
				$order->set_address( $shipping_address, 'shipping' );
				
			}
				
		}
		
		if($this->company) {
			
			update_post_meta($order_id, '_company_id', $this->company->id);
			
		}
		
	}
	
	/**
	 * Updates the users addreses during checkout process
	 *
	 * @param int $user_id ID of User checking out
	 * @param array $posted Data sent in $POST
	 */
	public function update_user_addresses($user_id, $posted) {
		
		global $woocommerce_companies;
		
		if($this->checkout_type == 'company') {
			
			if(!$this->company) {
				
				if($this->company_id == -1) {
					
					$company_name = $this->get_value('company_name');
					
					$company_number = $this->get_value('company_number');
					
					$args = array(
							'company_name' => $company_name, 
							'company_number' => $company_number,
						);
				
					$company_id = wc_create_company(
						$args
					);
					
					$this->company = wc_get_company($company_id);
					
				}
				
			}
				
			if($this->company) {
					
				$companies = get_user_meta($user_id, 'companies', true);
			
				$companies = is_array($companies) ? $companies : array();
				
				array_unshift($companies, $this->company->id);
				
				update_user_meta($user_id, 'companies', $companies);
				
				do_action('checkout_updated_company_meta', $this->company->id, $posted);
				
			}
			
		}
		
		if(!$this->billing_address) {
			
			// Billing address
			$billing_address = array();
			
			if ( $billingFields = array_keys(WC()->countries->get_address_fields( '', 'billing_' )) ) {
				
				foreach ( $billingFields as $field ) {
					
					delete_user_meta($user_id, $field);
					
					$billing_address[ str_replace('billing_', '', $field) ] = esc_sql($this->get_value($field));
					
				}
				
			}
			
			if($billing_address) {
				
				$billing_address_id = wc_create_address($billing_address);
				
				$this->billing_address = wc_get_address($billing_address_id);
				
			}
			
		}
			
		if($this->billing_address) {
			
			$billing_addresses = $this->checkout_type == 'company' ? get_post_meta($this->company->id, '_billing_addresses', true) : get_user_meta($user_id, 'billing_addresses', true);
				
			$billing_addresses = is_array($billing_addresses) ? $billing_addresses : array();
			
			array_unshift($billing_addresses, $this->billing_address->id);
				
			$billing_addresses = array_unique($billing_addresses);
			
			$this->checkout_type == 'company' ? update_post_meta($this->company->id, '_billing_addresses', $billing_addresses) : update_user_meta($user_id, 'billing_addresses', $billing_addresses);
			
			do_action('checkout_updated_billing_address_meta', $this->billing_address->id, $posted);
			
		}
		
		if(!$this->shipping_address) {

			// Shipping address.
			$shipping_address = array();
			
			if ( $shippingFields = array_keys(WC()->countries->get_address_fields( '', 'shipping_' )) ) {
				
				foreach ( $shippingFields as $field ) {
					
					delete_user_meta($user_id, $field);
					
					$shipping_address[ str_replace('shipping_', '', $field) ] = esc_sql($this->get_value($field));
					
				}
				
			}
			
			if($shipping_address) {
				
				$shipping_address_id = wc_create_address($shipping_address);
				
				$this->shipping_address = wc_get_address($shipping_address_id);
				
			}
				
		}
			
		if($this->shipping_address) {
			
			$shipping_addresses = $this->checkout_type == 'company' ? get_post_meta($this->company->id, '_shipping_addresses', true) : get_user_meta($user_id, 'shipping_addresses', true);
				
			$shipping_addresses = is_array($shipping_addresses) ? $shipping_addresses : array();
			
			array_unshift($shipping_addresses, $this->shipping_address->id);
				
			$shipping_addresses = array_unique($shipping_addresses);
			
			$this->checkout_type == 'company' ? update_post_meta($this->company->id, '_shipping_addresses', $shipping_addresses) : update_user_meta($user_id, 'shipping_addresses', $shipping_addresses);
			
			do_action('checkout_updated_shipping_address_meta', $this->shipping_address->id, $posted);
			
		}
		
		
		
	}
	
	/**
	 * Sets ship to different address if shipping address 1 field is not empty
	 *
	 * @param boolean $ship_to_different_address
	 */
	public function set_ship_to_different_address($ship_to_different_address) {
		
		if(WC()->checkout()->get_value('shipping_address_1')) {
			
			$ship_to_different_address = true;
			
		}
		
		return $ship_to_different_address;
		
	}
	
	/**
	 * Sets checkout fields as not required if shipping and billing address ids are set
	 *
	 * @param object $checkout WC_Checkout object
	 */
	public function set_checkout_fields_as_not_required($checkout) {
		
		if($this->get_billing_address()) {
			
			foreach($checkout->checkout_fields['billing'] as &$field) {
				
				$field['required'] = false;
				
				$field['validate'] = array();
				
			}
			
		}
		
		if($this->get_shipping_address()) {
			
			foreach($checkout->checkout_fields['shipping'] as &$field) {
				
				$field['required'] = false;
				
				$field['validate'] = array();
				
			}
			
		}
		
	}
	
	/**
	 * Get companies from checkout instance
	 *
	 * @param object $checkout WC_Checkout object
	 */
	public function get_companies() {
		
		return apply_filters('wc_companies_checkout_get_companies', $this->companies, $this);
		
	}
	
	/**
	 * Get company from checkout instance
	 *
	 * @param object $checkout WC_Checkout object
	 */
	public function get_company() {
		
		return apply_filters('wc_companies_checkout_get_company', $this->company, $this);
		
	}
	
	/**
	 * Get billing_address from checkout instance
	 *
	 * @param object $checkout WC_Checkout object
	 */
	public function get_billing_address() {
		
		return apply_filters('wc_companies_checkout_get_billing_address', $this->billing_address, $this);
		
	}
	
	/**
	 * Get shipping_address from checkout instance
	 *
	 * @param object $checkout WC_Checkout object
	 */
	public function get_shipping_address() {
		
		return apply_filters('wc_companies_checkout_get_shipping_address', $this->shipping_address, $this);
		
	}
	
	/**
	 * Display only free shipping when company has free shipping
	 *
	 * @param array $rates
	 */
	public function display_only_free_shipping_when_company_has_free_shipping( $rates ) {
		
		if( is_user_logged_in() ) {
			
			global $current_user;
			
			$company = $this->get_company() ? $this->get_company() : ( $current_user->primary_company ? wc_get_company( $current_user->primary_company ) : false);
			
			if( $company && $company->has_free_shipping()  ) {
				
				foreach($rates as &$rate) {
						
					$rate->cost = apply_filters( 'woocommerce_companies_free_shipping_rate_cost', 0, $rate, $company );
					
				}
				
			}
			
		}
		
		return $rates;
		
	}
	
}
	
?>