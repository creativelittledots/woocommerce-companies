<?php

/**
 * Get user companies
 *
 * @param int $user (default: null)
 */
function wc_get_user_companies( $user_id = null, $output = 'objects', $count = '-1' ) {
	
	$user_id = $user_id ? $user_id : get_current_user_id();
			
	if($user_id) {
		
		$companies = get_user_meta($user_id, 'companies', true);
		
		$args = array(
			'post__in' => $companies ? $companies : array(0),
			'numberposts' => $count,
		);
		
		return wc_get_companies( $args, $output );
		
	}
	
	else {
			
		return array();
		
	}
	
}

/**
 * Get user addresses
 *
 * @param int $user (default: null)
 * @param string $type (default: 'billing')
 */
function wc_get_user_addresses( $user_id = null, $type = 'billing', $output = 'objects', $count = '-1' ) {
	
	$user_id = $user_id ? $user_id : get_current_user_id();
			
	if($user_id) {
		
		$addresses = get_user_meta($user_id, $type . '_addresses', true);
		
		$args = array(
			'post__in' => $addresses ? $addresses : array(0),
			'showposts' => $count,
		);
		
		return wc_get_addresses( $args, $output );
		
	}
	
	else {
			
		return array();
		
	}
	
}

/**
 * Get user company addresses
 *
 * @param int $user (default: null)
 * @param string $type (default: 'billing')
 */
function wc_get_user_company_addresses( $user_id = null, $output = 'objects' ) {
	
	$user_id = $user_id ? $user_id : get_current_user_id();
			
	if($user_id) {
		
		$company_addresses = array();
					
		foreach(wc_get_user_companies($user_id) as $company) {
			
			$company_addresses = $company_addresses + ($company->get_billing_addresses($output) ? $company->get_billing_addresses($output) : array());
			
			$company_addresses = $company_addresses + ($company->get_shipping_addresses($output) ? $company->get_shipping_addresses($output) : array());
			
		}
		
		$company_addresses = array_unique($company_addresses);
		
		return $company_addresses;
		
	}
	
	else {
			
		return array();
		
	}
	
}

/**
 * Get user created addresses
 *
 * @param int $user (default: null)
 * @param string $type (default: 'billing')
 */
function wc_get_user_created_addresses( $user_id = null, $output = 'objects', $count = '-1' ) {
	
	$user_id = $user_id ? $user_id : get_current_user_id();
			
	if($user_id) {
		
		$addresses = array();
					
		foreach(get_posts(
			array(
				'post_type' => 'wc-address',
				'author' => $user_id,
				'numberposts' => $count,
			)
		) as $address) {
			
			switch($output) {
				
				case 'ids' :
				
					$addresses[] = $address->ID;
					
				break;
				
				default :
				
					$addresses[] = wc_get_address($address->ID);
					
				break;
				
			}
			
		}
		
		return $addresses;
		
	}
	
	else {
			
		return array();
		
	}
	
}

/**
 * Get user primary addresses
 *
 * @param int $user (default: null)
 * @param string $type (default: 'billing')
 */
function wc_get_user_primary_addresses( $user_id = null, $output = 'objects' ) {
	
	$user_id = $user_id ? $user_id : get_current_user_id();
	
	$user = get_user_by('id', $user_id);
			
	if($user_id) {
		
		$addresses = array();
		
		if( $user->primary_billing_address && get_post( $user->primary_billing_address ) ) {
			
			switch($output) {
				
				case 'ids' :
				
					$addresses['billing'] = $user->primary_billing_address;
				
				break;
				
				default :
				
					$addresses['billing'] = wc_get_address($user->primary_billing_address);
					
				break;
				
			}
			
		}
		
		if( $user->primary_shipping_address && get_post( $user->primary_shipping_address ) ) {
			
			switch($output) {
				
				case 'ids' :
				
					$addresses['shipping'] = $user->primary_shipping_address;
				
				break;
				
				default :
				
					$addresses['shipping'] = wc_get_address($user->primary_shipping_address);
					
				break;
				
			}
			
		}
		
		$addresses = apply_filters( 'woocommerce_companies_user_primary_addresses', array_unique($addresses), $user_id );
		
		return $addresses;
		
	}
	
	else {
			
		return array();
		
	}
	
}

/**
 * Get user all addresses
 *
 * @param int $user (default: null)
 * @param string $type (default: 'billing')
 */
function wc_get_user_all_addresses( $user_id = null, $output = 'objects' ) {
	
	$user_id = $user_id ? $user_id : get_current_user_id();
	
	$addresses = array();
			
	if($user_id) {
    	
    	if( ! user_can($user_id, 'manage_options') ) {
		
		    $addresses = array_merge($addresses, wc_get_user_created_addresses($user_id, $output));
		    
        }
		
		$addresses = array_merge($addresses, wc_get_user_company_addresses( $user_id, $output ));
		
		$addresses = array_merge($addresses, wc_get_user_addresses( $user_id, 'billing', $output ));
		
		$addresses = array_merge($addresses, wc_get_user_addresses( $user_id, 'shipping', $output ));
		
		$addresses = array_unique($addresses);
		
	}
	
	return $addresses;
	
}

/**
 * Add user company
 *
 * @param int $user_id (default: null)
 * @param int $company_id (default: null)
 */
function wc_add_user_company( $user_id = null, $company_id = null ) {
	
	$user_id = $user_id ? $user_id : get_current_user_id();
			
	if($user_id && $company_id) {
		
		$companies = get_user_meta($user_id, 'companies', true) && is_array(get_user_meta($user_id, 'companies', true)) ? get_user_meta($user_id, 'companies', true) : array();
		
		$companies[] = $company_id;
		
		$companies = array_unique($companies);
		
		if( count($companies) === 1 ) {
    		
    		update_user_meta($user_id, 'primary_company', reset($companies));
    		
		}
		
		update_user_meta($user_id, 'companies', $companies);
		
		return true;
		
	}

	return false;
	
}

/**
 * Add user address
 *
 * @param int $user_id (default: null)
 * @param int $address_id (default: null)
 * @param string $load_address (default: billing)
 */
function wc_add_user_address( $user_id = null, $address_id = null, $load_address = 'billing' ) {
	
	$user_id = $user_id ? $user_id : get_current_user_id();
			
	if($user_id && $address_id) {
		
		$addresses = get_user_meta($user_id, $load_address . '_addresses', true) && is_array(get_user_meta($user_id, $load_address . '_addresses', true)) ? get_user_meta($user_id, $load_address . '_addresses', true) : array();
		
		$addresses[] = $address_id;
		
		$addresses = array_unique($addresses);
		
		if( count($addresses) === 1 ) {
    		
    		update_user_meta($user_id, 'primary_' . $load_address . '_address', reset($addresses));
    		
		}
		
		update_user_meta($user_id, $load_address . '_addresses', $addresses);
		
		return true;
		
	}

	return false;
	
}

/**
 * Remove user address
 *
 * @param int $user_id (default: null)
 * @param int $address_id (default: null)
 * @param string $load_address (default: billing)
 */
function wc_remove_user_address( $user_id = null, $address_id = null, $load_address = 'billing' ) {
	
	$user_id = $user_id ? $user_id : get_current_user_id();
			
	if($user_id && $address_id) {
		
		$addresses = get_user_meta($user_id, $load_address . '_addresses', true) && is_array(get_user_meta($user_id, $load_address . '_addresses', true)) ? get_user_meta($user_id, $load_address . '_addresses', true) : array();
		
		if(array_search($address_id, $addresses) > -1) {
    		
    		$delete_address_id = $addresses[array_search($address_id, $addresses)];
			
			unset($addresses[array_search($address_id, $addresses)]);
			
			$addresses = array_unique($addresses);
			
			$user = get_user_by('id', $user_id);
			
			if( count($addresses) === 1 || $user->{'primary_' . $load_address . '_address'} == $delete_address_id ) {
    		
        		update_user_meta($user_id, 'primary_' . $load_address . '_address', reset($addresses));
        		
        	}
			
			update_user_meta($user_id, $load_address . '_addresses', $addresses);
			
			return true;
			
		}
			
	}

	return false;
	
}