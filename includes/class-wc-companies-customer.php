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
 
class WC_Companies_Customer {

	/**
	 * Constructor for the customer class loads the customer data.
	 *
	 */
	public function __construct() {

		add_filter( 'user_has_cap', array($this, 'has_capability'), 10, 3 );
		
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
					
					$companies = wc_get_user_companies( $user_id = null, 'ids' );
					
					$addresses = wc_get_user_all_addresses( $user_id = null, 'ids' );
	
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
				
					$allcaps['make_primary_company_address'] =  (user_can($args[1], 'read') && in_array($args[2], wc_get_user_companies($args[1], 'ids')) && in_array($args[3], wc_get_user_all_addresses($args[1], 'ids')));
					
				break;
				
			}
			
		}
		
		return $allcaps;
		
	}
	
}
