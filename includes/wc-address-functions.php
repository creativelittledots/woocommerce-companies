<?php
	
/**
 * Clear all transients cache for address data.
 *
 * @param int $post_id (default: 0)
 */
function wc_delete_address_transients( $post_id = 0 ) {
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

	do_action( 'woocommerce_delete_address_transients', $post_id );
}

/**
 * Get addresses
 *
 * @param array $args (default: Array)
 */
function wc_get_addresses( $args = array(), $output = 'objects' ) {
	
	return WC_Address::find($args, $output);
	
}

/**
 * Create Address
 *
 * @param array $args (default: Array)
 */

function wc_create_address( $args = array() ) {
	
	$address = new WC_Address();
	
	foreach(WC_Companies()->addresses->get_address_fields() as $key => $field) {
		
		$key = preg_replace('/[^A-Za-z0-9_\-]/', '', $key);
		
		if( isset($args[$key]) )
			$address->$key = $args[$key];
		
	}
	
	$address_id = $address->check_exists();
	
	if( ! $address_id ) {
		
		return $address->save();
		
	} else {
    	
    	return $address_id;
    	
	}
	
}

/**
 * Main function for returning addresses, uses the WC_Address_Factory class.
 *
 * @since  1.0
 * @param  mixed $the_address Post object or post ID of the address.
 * @return WC_Address
 */
function wc_get_address( $the_address = false ) {
	
	return WC_Companies()->address_factory->get_address( $the_address );
	
}

/**
 * Get a address type by post type name
 * @param  string post type name
 * @return bool|array of datails about the order type
 */
function wc_get_address_type( $type ) {
	
	global $wc_address_types;

	if ( isset( $wc_address_types[ $type ] ) ) {
		
		return $wc_address_types[ $type ];
		
	} else {
		
		return false;
		
	}
	
}

/**
 * Register address type. Do not use before init.
 *
 * Wrapper for register post type, as well as a method of telling WC which
 * post types are types of addresss, and having them treated as such.
 *
 * $args are passed to register_post_type, but there are a few specific to this function:
 * 		- exclude_from_addresss_screen (bool) Whether or not this address type also get shown in the main
 * 		addresss screen.
 * 		- add_address_meta_boxes (bool) Whether or not the address type gets shop_address meta boxes.
 * 		- exclude_from_address_count (bool) Whether or not this address type is excluded from counts.
 * 		- exclude_from_address_views (bool) Whether or not this address type is visible by customers when
 * 		viewing addresss e.g. on the my account page.
 * 		- exclude_from_address_reports (bool) Whether or not to exclude this type from core reports.
 * 		- exclude_from_address_sales_reports (bool) Whether or not to exclude this type from core sales reports.
 *
 * @since  1.0
 * @see    register_post_type for $args used in that function
 * @param  string $type Post type. (max. 20 characters, can not contain capital letters or spaces)
 * @param  array $args An array of arguments.
 * @return bool Success or failure
 */
function wc_register_address_type( $type, $args = array() ) {
	if ( post_type_exists( $type ) ) {
		return false;
	}

	global $wc_address_types;

	if ( ! is_array( $wc_address_types ) ) {
		$wc_address_types = array();
	}

	// Register as a post type
	if ( is_wp_error( register_post_type( $type, $args ) ) ) {
		return false;
	}

	// Register for WC usage
	$address_type_args = array(
		'exclude_from_addresss_screen'       => false,
		'add_address_meta_boxes'             => true,
		'exclude_from_address_count'         => false,
		'exclude_from_address_views'         => false,
		'exclude_from_address_webhooks'      => false,
		'exclude_from_address_reports'       => false,
		'exclude_from_address_sales_reports' => false,
		'class_name'                       => 'WC_Address'
	);

	$args                    = array_intersect_key( $args, $address_type_args );
	$args                    = wp_parse_args( $args, $address_type_args );
	$wc_address_types[ $type ] = $args;

	return true;
}