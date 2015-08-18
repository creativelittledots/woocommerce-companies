<?php

/**
 * Get user companies
 *
 * @param int $user (default: null)
 */
function get_user_companies( $user_id = null ) {
	
	$user_id = $user_id ? $user_id : get_current_user_id();
			
	if($user_id) {
		
		$companies = get_user_meta($user_id, 'companies', true);
		
		$args = array(
			'post__in' => $companies ? $companies : array(0),
		);
		
		return wc_get_companies( $args );
		
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
function get_user_addresses( $user_id = null, $type = 'billing' ) {
	
	$user_id = $user_id ? $user_id : get_current_user_id();
			
	if($user_id) {
		
		$addresses = get_user_meta($user_id, $type . '_addresses', true);
		
		$args = array(
			'post__in' => $addresses ? $addresses : array(0),
		);
		
		return wc_get_addresses( $args );
		
	}
	
	else {
			
		return false;
		
	}
	
}

/**
 * Checks if a user has a certain capability
 *
 * @access public
 * @param array $allcaps
 * @param array $caps
 * @param array $args
 * @return bool
 */
function wc_companies_customer_has_capability( $allcaps, $caps, $args ) {
	if ( isset( $caps[0] ) ) {
		switch ( $caps[0] ) {
			case 'view_company' :
				$user_id = $args[1];
				$company   = new WC_Company( $args[2] );
				
				$user = get_user_by('id', $user_id);

				if ( $company && in_array($company->id, $user->companies) ) {
					$allcaps['view_company'] = true;
				}
			break;
			case 'remove_company' :
				$user_id = $args[1];
				$company   = new WC_Company( $args[2] );

				if ( $company && $user_id == $company->get_user_id()) {
					$allcaps['view_company'] = true;
				}
			break;
		}
	}
	return $allcaps;
}
add_filter( 'user_has_cap', 'wc_companies_customer_has_capability', 10, 3 );