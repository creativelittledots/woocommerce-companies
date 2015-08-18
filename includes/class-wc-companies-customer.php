<?php
/**
 * Customer
 *
 * The WooCommerce Companies customer class handles storage of the current customer's data, such as location.
 *
 * @class    WC_Companies_Customer
 * @version  1.0.0
 * @package  WooCommerce Companies/Classes
 * @category Class
 * @author   Creative Little Dots
 */
 
class WC_Companies_Customer extends WC_Customer {
	
	/**
	 * Stores customer data
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Constructor for the customer class loads the customer data.
	 *
	 */
	public function __construct() {
		
		// Defaults
		$this->_data = array(
			'postcode'            => '',
			'city'                => '',
			'address'             => '',
			'address_2'           => '',
			'state'               => '',
			'country'             => '',
			'shipping_postcode'   => '',
			'shipping_city'       => '',
			'shipping_address'    => '',
			'shipping_address_2'  => '',
			'shipping_state'      => '',
			'shipping_country'    => '',
		);

		add_action( 'woocommerce_review_order_before_shipping', array( $this, 'sync_customer_shipping_address' ), 11 );
		add_action( 'woocommerce_before_shipping_calculator', array( $this, 'sync_customer_shipping_address' ), 11 );
		add_action( 'woocommerce_checkout_init', array( $this, 'sync_customer_shipping_address' ) );
		add_action( 'woocommerce_before_calculate_totals', array($this, 'set_address_values') );
		
	}
	
	/**
	 * Sets customer shipping address details before specific actions
	 *
	 */
	public function sync_customer_shipping_address() {
		
		if($address = WC_Companies()->checkout()->get_shipping_address()) {
			
			foreach($this->_data as $key => $value) {
				
				$property = str_replace('shipping_', '', $key);
				
				if($address->$property && $address->$property != WC()->customer->{'get_' . $key }) {
					
					$value = $address->$property ? $address->$property : $this->_data[ $key ];
					
					WC()->customer->{'set_' . $key}( $value );
					
				}
				
			}
			
		}
		
	}
	
	/**
	 * Sets customer address details before calulating totals
	 *
	 */
	public function set_address_values() {
	
		$billing_address = WC_Companies()->checkout()->get_billing_address();
			
		$shipping_address = WC_Companies()->checkout()->get_shipping_address();
		
		foreach($this->_data as $key => $value) {
			
			$property = str_replace('shipping_', '', $key);
			
			$value = WC()->customer->{'get_' . $key}();
			
			if( empty( $value ) ) {
				
				if(strstr( $key, 'shipping_' ) && $shipping_address && $shipping_address->$property) {
						
					$value = $shipping_address->$property;
					
				}
					
				elseif($billing_address && $billing_address->$property) {
						
					$value = $shipping_address->$property;
					
				}
				
				elseif($checkout_value = WC_Companies()->checkout()->get_value(( false === strstr( $key, 'shipping_' ) ? 'billing_' : '' ) . $key)) {
					
					$value = $checkout_value;
					
				}
				
				WC()->customer->{'set_' . $key}($value);
				
			}
			
		}
			
	}
	
}
