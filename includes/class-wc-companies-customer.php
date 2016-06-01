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
		add_filter( 'user_has_cap', array($this, 'has_capability'), 10, 3 );
		
	}
	
	/**
	 * __get function.
	 *
	 * @param string $property
	 * @return string
	 */
	public function __get( $property ) {

		return isset( $this->_all_data[ $property ] ) ? $this->_all_data[ $property ] : '';
		
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
						
					$value = $billing_address->$property;
					
				}
				
				elseif($checkout_value = WC_Companies()->checkout()->get_value(( false === strstr( $key, 'shipping_' ) ? 'billing_' : '' ) . $key)) {
					
					$value = $checkout_value;
					
				}
				
				WC()->customer->{'set_' . $key}($value);
				
			}
			
		}
			
	}
	
	/**
	 * Checks if a customer has a certain capability
	 *
	 * @access public
	 * @param array $allcaps
	 * @param array $caps
	 * @param array $args
	 * @return bool
	 */
	public function has_capability( $allcaps, $caps, $args ) {
		
		if ( isset( $caps[0] ) ) {
			
			switch ( $caps[0] ) {
				
				case 'edit_company' :
				
					$user_id = $args[1];
					
					$company   = wc_get_company( $args[2] );
					
					$user = get_user_by('id', $user_id);
	
					if ( $company && ( in_array($company->id, $user->companies) || $company->get_user_id() == $user_id ) ) {
						
						$allcaps['edit_company'] = true;
						
					}
					
				break;
				
				case 'edit_address' :
				
					$user_id = $args[1];
					
					$address   = wc_get_address( $args[2] );
	
					if ( $address && ( in_array($address->id, wc_get_user_all_addresses($user_id, 'ids')) ) ) {
						
						$allcaps['edit_address'] = true;
						
					}
					
				break;
				
				case 'remove_company' :
				
					$user_id = $args[1];
					
					$company   = wc_get_company( $args[2] );
					
					$user = get_user_by('id', $user_id);
	
					if ( $company && ( $user_id == $company->get_user_id() || in_array($company->id, $user->companies) ) ) {
						
						$allcaps['remove_company'] = true;
						
					}
					
				break;
				
				case 'remove_address' :
				
					$user_id = $args[1];
					
					$address   = wc_get_address( $args[2] );
	
					$user = get_user_by('id', $user_id);
					
					$addresses = wc_get_user_all_addresses( $user_id = null, 'ids' );
	
					if ( $address && ( $user_id == $address->get_user_id() || in_array($address->id, $user->billing_addresses) || in_array($address->id, $user->shipping_addresses) || in_array($address->id, $addresses) ) ) {
						
						$allcaps['remove_address'] = true;
						
					}
					
				break;
				
				case 'remove_company_address' :
				
					$user_id = $args[1];
					
					$company  = wc_get_company( $args[2] );
					
					$address   = wc_get_address( $args[3] );
	
					$user = get_user_by('id', $user_id);
					
					$companies = get_user_companies( $user_id = null, 'ids' );
					
					$addresses = get_user_all_addresses( $user_id = null, 'ids' );
	
					if ( $address && in_array($company->id, $companies) && ( $user_id == $address->get_user_id() || in_array($address->id, $user->billing_addresses) || in_array($address->id, $user->shipping_addresses) || in_array($address->id, $addresses) ) ) {
						
						$allcaps['remove_address'] = true;
						
					}
					
				break;
								
				case 'add_company' :
				
					$allcaps['add_company'] =  user_can($args[1], 'read');
					
				break;
				
				case 'add_address' :
				
					$allcaps['add_address'] =  user_can($args[1], 'read');
					
				break;
				
				case 'make_primary_company' :
				
					$allcaps['make_primary_company'] =  (user_can($args[1], 'read') && in_array($args[2], wc_get_user_companies($args[1], 'ids')));
					
				break;
				
				case 'make_primary_address' :
				
					$allcaps['make_primary_address'] =  (user_can($args[1], 'read') && in_array($args[2], wc_get_user_all_addresses($args[1], 'ids')));
					
				break;
				
				case 'make_primary_company_address' :
				
					$allcaps['make_primary_company_address'] =  (user_can($args[1], 'read') && in_array($args[2], wc_get_user_companies($args[1], 'ids')) && in_array($args[3], get_user_all_addresses($args[1], 'ids')));
					
				break;
				
			}
			
		}
		
		return $allcaps;
		
	}
	
}
