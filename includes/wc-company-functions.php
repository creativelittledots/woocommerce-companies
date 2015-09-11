<?php
	
/**
 * Clear all transients cache for company data.
 *
 * @param int $post_id (default: 0)
 */
function wc_delete_company_transients( $post_id = 0 ) {
	$post_id             = absint( $post_id );
	$transients_to_clear = array();

	// Clear report transients
	$reports = WC_Admin_Reports::get_reports();

	foreach ( $reports as $report_group ) {
		foreach ( $report_group['reports'] as $report_key => $report ) {
			$transients_to_clear[] = 'wc_report_' . $report_key;
		}
	}

	// clear API report transient
	$transients_to_clear[] = 'wc_admin_report';

	// Clear transients where we have names
	foreach( $transients_to_clear as $transient ) {
		delete_transient( $transient );
	}

	do_action( 'woocommerce_delete_company_transients', $post_id );
}

/**
 * Get companies
 *
 * @param array $args (default: Array)
 */
function  wc_get_companies( $args = array(), $output = 'objects' ) {
	
	return WC_Company::find($args, $output);
	
}

/**
 * Create Company
 *
 * @param array $args (default: Array)
 */
function wc_create_company( $args = array() ) {
	
	$company = new WC_Company();
	
	foreach(WC_Companies()->addresses->get_company_fields() as $key => $field) {
		
		$key = preg_replace('/[^A-Za-z0-9_\-]/', '', $key);
		
		if( isset($args[$key]) )
			$company->$key = $args[$key];
		
	}
	
	if(  ! $company_id = $company->check_exists() ) {
		
		return $company->save();
		
	}
	
	return false;
	
}

/**
 * Add an address to a company
 *
 * @param int $company_id (default: null)
 * @param int $address_id (default: null)
 * @param string $address_type (default: billing)
 */
function add_company_address( $company_id = null, $address_id = null, $load_address = 'billing' ) {
			
	if($company_id && $address_id) {
		
		$company = wc_get_company($company_id);
		
		$addresses = $company->{$load_address . '_addresses'} ? $company->{$load_address . '_addresses'} : array();
		
		$addresses[] = $address_id;
		
		$company->{$load_address . '_addresses'} = $addresses;
		
		return $company->save();
		
	}

	return false;
	
}

/**
 * Main function for returning companies, uses the WC_Company_Factory class.
 *
 * @since  1.0
 * @param  mixed $the_company Post object or post ID of the company.
 * @return WC_Company
 */
function wc_get_company( $the_company = false ) {
	
	return WC_Companies()->company_factory->get_company( $the_company );
	
}

/**
 * Get a company type by post type name
 * @param  string post type name
 * @return bool|array of datails about the order type
 */
function wc_get_company_type( $type ) {
	
	global $wc_company_types;

	if ( isset( $wc_company_types[ $type ] ) ) {
		
		return $wc_company_types[ $type ];
		
	} else {
		
		return false;
		
	}
	
}

/**
 * Register company type. Do not use before init.
 *
 * Wrapper for register post type, as well as a method of telling WC which
 * post types are types of companys, and having them treated as such.
 *
 * $args are passed to register_post_type, but there are a few specific to this function:
 * 		- exclude_from_companys_screen (bool) Whether or not this company type also get shown in the main
 * 		companys screen.
 * 		- add_company_meta_boxes (bool) Whether or not the company type gets shop_company meta boxes.
 * 		- exclude_from_company_count (bool) Whether or not this company type is excluded from counts.
 * 		- exclude_from_company_views (bool) Whether or not this company type is visible by customers when
 * 		viewing companys e.g. on the my account page.
 * 		- exclude_from_company_reports (bool) Whether or not to exclude this type from core reports.
 * 		- exclude_from_company_sales_reports (bool) Whether or not to exclude this type from core sales reports.
 *
 * @since  1.0
 * @see    register_post_type for $args used in that function
 * @param  string $type Post type. (max. 20 characters, can not contain capital letters or spaces)
 * @param  array $args An array of arguments.
 * @return bool Success or failure
 */
function wc_register_company_type( $type, $args = array() ) {
	if ( post_type_exists( $type ) ) {
		return false;
	}

	global $wc_company_types;

	if ( ! is_array( $wc_company_types ) ) {
		$wc_company_types = array();
	}

	// Register as a post type
	if ( is_wp_error( register_post_type( $type, $args ) ) ) {
		return false;
	}

	// Register for WC usage
	$company_type_args = array(
		'exclude_from_companys_screen'       => false,
		'add_company_meta_boxes'             => true,
		'exclude_from_company_count'         => false,
		'exclude_from_company_views'         => false,
		'exclude_from_company_webhooks'      => false,
		'exclude_from_company_reports'       => false,
		'exclude_from_company_sales_reports' => false,
		'class_name'                       => 'WC_Company'
	);

	$args                    = array_intersect_key( $args, $company_type_args );
	$args                    = wp_parse_args( $args, $company_type_args );
	$wc_company_types[ $type ] = $args;

	return true;
}