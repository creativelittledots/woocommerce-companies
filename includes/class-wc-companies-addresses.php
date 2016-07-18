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
		
		unset($fields['company']);
		
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
<<<<<<< HEAD
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
		
		if( $primary_address ) {
			
			if( $primary_address = wc_get_address($primary_address) ) {
			
			    $address = $primary_address->get_meta_data();
			    
            }
			
		}
		
		return $address;
		
	}
=======
	}	
>>>>>>> origin/Development
	
}

return new WC_Companies_Addresses();