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
		
		$fields['email'] = array(
			'label'		=> __( 'Email Address', 'woocommerce' ),
			'required'	=> false,
			'type'		=> 'email',
			'class'		=> array( 'form-row-first' ),
			'validate'	=> array( 'email' ),
		);
		$fields['phone'] = array(
			'label'    	=> __( 'Phone', 'woocommerce' ),
			'required' 	=> false,
			'type'		=> 'tel',
			'class'    	=> array( 'form-row-last' ),
			'clear'    	=> true,
			'validate' 	=> array( 'phone' ),
		);
		$fields['accounting_reference'] = array(
			'label'    	=> __( 'Accounting Reference', 'woocommerce' ),
			'required' 	=> false,
			'type'		=> 'text',
			'class'    	=> array( 'form-row-last' ),
			'clear'    	=> true,
			'public'	=> false,
		);

		$address_fields = apply_filters( 'woocommerce_companies_addresses_fields', $fields, $country );

		return $address_fields;
	}
	
	/**
	 * Get admin address fields
	 * @return array
	 */
	public function get_admin_address_fields( $public = false ) {
		
		$fields = WC_Meta_Box_Address_Data::init_address_fields();
		
		$address_fields = array();
		
		foreach($fields as $key => $field) {
				
			if( ( ! isset( $field['public'] ) || ! empty( $field['public'] ) ) || $public ) {
				
				unset( $field['public'] );
				
				$address_fields[$key] = $field;
				
			}
			
		}

		$address_fields = apply_filters( 'woocommerce_companies_addresses_fields', $address_fields );

		return $address_fields;
	}
	
	/**
	 * Get company fields
	 * @return array
	 */
	public function get_company_fields( $public = false ) {
		
		$fields = WC_Meta_Box_Company_Data::init_company_fields();
		
		$company_fields = array();
		
		foreach($fields as $key => $field) {
				
			if( $field['public'] || $public ) {
				
				unset($field['public']);
				
				$company_fields[$key] = $field;
				
			}
			
		}

		$company_fields = apply_filters( 'woocommerce_companies_addresses_fields', $company_fields );

		return $company_fields;

	}
	
}

return new WC_Companies_Addresses();