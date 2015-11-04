<?php

/**
 * Get user companies
 *
 * @param int $user (default: null)
 */
function get_user_companies( $user_id = null, $output = 'objects', $count = '-1' ) {
	
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
function get_user_addresses( $user_id = null, $type = 'billing', $output = 'objects', $count = '-1' ) {
	
	$user_id = $user_id ? $user_id : get_current_user_id();
			
	if($user_id) {
		
		$addresses = get_user_meta($user_id, $type . '_addresses', true);
		
		global $current_user;
		
		$args = array(
			'post__in' => $addresses ? $addresses : array(0),
			'numberposts' => $count,
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
function get_user_company_addresses( $user_id = null, $output = 'objects' ) {
	
	$user_id = $user_id ? $user_id : get_current_user_id();
			
	if($user_id) {
		
		$company_addresses = array();
					
		foreach(get_user_companies($user_id) as $company) {
			
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
function get_user_created_addresses( $user_id = null, $output = 'objects', $count = '-1' ) {
	
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
function get_user_primary_addresses( $user_id = null, $output = 'objects' ) {
	
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
function get_user_all_addresses( $user_id = null, $output = 'objects' ) {
	
	$user_id = $user_id ? $user_id : get_current_user_id();
			
	if($user_id) {
		
		$addresses = array_merge(get_user_created_addresses($user_id, $output), get_user_addresses( $user_id, 'billing', $output ), get_user_addresses( $user_id, 'shipping', $output ), get_user_company_addresses( $user_id, $output ));
		
		$addresses = array_unique($addresses);
		
		return $addresses;
		
	}
	
	else {
			
		return array();
		
	}
	
}

/**
 * Add user company
 *
 * @param int $user_id (default: null)
 * @param int $company_id (default: null)
 */
function add_user_company( $user_id = null, $company_id = null ) {
	
	$user_id = $user_id ? $user_id : get_current_user_id();
			
	if($user_id && $company_id) {
		
		$companies = get_user_meta($user_id, 'companies', true) && is_array(get_user_meta($user_id, 'companies', true)) ? get_user_meta($user_id, 'companies', true) : array();
		
		$companies[] = $company_id;
		
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
function add_user_address( $user_id = null, $address_id = null, $load_address = 'billing' ) {
	
	$user_id = $user_id ? $user_id : get_current_user_id();
			
	if($user_id && $address_id) {
		
		$addresses = get_user_meta($user_id, $load_address . '_addresses', true) && is_array(get_user_meta($user_id, $load_address . '_addresses', true)) ? get_user_meta($user_id, $load_address . '_addresses', true) : array();
		
		$addresses[] = $address_id;
		
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
function remove_user_address( $user_id = null, $address_id = null, $load_address = 'billing' ) {
	
	$user_id = $user_id ? $user_id : get_current_user_id();
			
	if($user_id && $address_id) {
		
		$addresses = get_user_meta($user_id, $load_address . '_addresses', true) && is_array(get_user_meta($user_id, $load_address . '_addresses', true)) ? get_user_meta($user_id, $load_address . '_addresses', true) : array();
		
		if(array_search($address_id, $addresses) > -1) {
			
			unset($addresses[array_search($address_id, $addresses)]);
			
			update_user_meta($user_id, $load_address . '_addresses', $addresses);
			
			return true;
			
		}
			
	}

	return false;
	
}