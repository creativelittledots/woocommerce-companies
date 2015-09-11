<?php
/**
 * Addresses
 *
 * @class    WC_Addresses
 * @version  1.0.0
 * @package  WooCommerce Companies/Classes
 * @category Class
 * @author   Creative Little Dots
 */
class WC_Companies_Addresses extends WC_Countries {
	
	public function __construct() {
		
		add_filter( 'woocommerce_my_account_my_address_formatted_address', array($this, 'my_account_my_address_formatted_address'), 10, 3);
		
		add_filter( 'woocommerce_my_account_edit_address_field_value', array($this, 'my_account_edit_address_field_value'), 10, 3);
		
		add_filter( 'woocommerce_companies_edit_company_fields', array($this, 'companies_edit_company_fields'), 10, 2 );
		
	}

	/**
	 * Apply locale and get address fields
	 * @param  mixed  $country
	 * @return array
	 */
	public function get_address_fields( $country = '', $type = 'billing_' ) {
		if ( ! $country ) {
			$country = $this->get_base_country();
		}

		$fields = $this->get_default_address_fields();
		$locale = $this->get_country_locale();

		if ( isset( $locale[ $country ] ) ) {
			$fields = wc_array_overlay( $fields, $locale[ $country ] );
		}
		
		unset($fields['company']);
		
		$fields['email'] = array(
			'label'		=> __( 'Email Address', 'woocommerce' ),
			'required'	=> true,
			'type'		=> 'email',
			'class'		=> array( 'form-row-first' ),
			'validate'	=> array( 'email' ),
		);
		$fields['phone'] = array(
			'label'    	=> __( 'Phone', 'woocommerce' ),
			'required' 	=> true,
			'type'		=> 'tel',
			'class'    	=> array( 'form-row-last' ),
			'clear'    	=> true,
			'validate' 	=> array( 'phone' ),
		);

		$address_fields = apply_filters( 'woocommerce_companies_addresses_fields', $fields, $country );

		return $address_fields;
	}
	
	/**
	 * Get company fields
	 * @return array
	 */
	public function get_company_fields( $country = '' ) {
		
		$fields = WC_Meta_Box_Company_Data::init_company_fields();
		
		$company_fields = array();
		
		foreach($fields as $key => $field) {
			
			if($field['public']) {
				
				unset($field['public']);
				
				$company_fields[$key] = $field;
				
			}
			
		}

		$company_fields = apply_filters( 'woocommerce_companies_addresses_fields', $company_fields );

		return $company_fields;
	}
	
	/**
	 * retrieves address data from stored primary address
	 *
	 * @param array $address Array of address
	 * @param string $customer_id int of user id
	 * @param string $name String of type of address
	 */
	public function my_account_my_address_formatted_address($address, $customer_id, $load_address) {
		
		$primary_address = get_user_by('id', $customer_id)->{ 'primary_' . $load_address . '_address' };
		
		if($primary_address) {
			
			$primary_address = wc_get_address($primary_address);
			
			$address = $primary_address->get_meta_data();
			
		}
		
		return $address;
		
	}
	
	/**
	 * retrieves address data from stored primary address
	 *
	 * @param string $value String of field value
	 * @param string $key String of field key
	 * @param string $load_address String of type of address
	 */
	public function my_account_edit_address_field_value($value, $key, $load_address) {
		
		$address = get_user_by('id', get_current_user_id())->{ 'primary_' . $load_address . '_address' };
		
		$key = ltrim(str_replace($load_address, '', $key), '_');
			
		return $address->$key ? $address->$key : $value;
		
	}
	
	/**
	 * retriveing edit comapny fields for front end
	 *
	 * @param string $comapny_id Int of company id
	 */
	public function companies_edit_company_fields($fields, $company_id) {
		
		$company = wc_get_company($company_id);
		
		$billing_addresses = array(0 => 'None');
		
		foreach($company->get_billing_addresses() as $address) {
			
			$billing_addresses[$address->id] = $address->get_title();
			
		}
		
		$shipping_addresses = array(0 => 'None');
		
		foreach($company->get_shipping_addresses() as $address) {
			
			$shipping_addresses[$address->id] = $address->get_title();
			
		}
		
		$user_addresses = array();
		
		foreach(get_user_all_addresses( get_current_user_id() ) as $address) {
			
			$user_addresses[$address->id] = $address->get_title();
			
		}
		
		$fields['primary_billing_address']['options'] = $billing_addresses;
		
		$fields['primary_shipping_address']['options'] = $shipping_addresses;
		
		$fields['billing_addresses']['options'] = $fields['shipping_addresses']['options'] = $user_addresses;
		
		return $fields;
		
	}

}

return new WC_Companies_Addresses();