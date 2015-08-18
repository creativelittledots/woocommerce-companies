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
function wc_get_addresses( $args = array() ) {
	
	$args = array_merge(array(
		'post_type' => 'wc-address',
		'showposts' => -1,
	), $args);
	
	$addresses = get_posts($args);
	
	foreach($addresses as &$address) {
		
		$address = new WC_Address($address->ID);
		
	}
	
	return $addresses;
	
}

/**
 * Create Address
 *
 * @param array $args (default: Array)
 */

function wc_create_address( $args = array() ) {
	
	$meta_query = array();
			
	foreach($args as $key => $value) {
		
		if($value)
			$meta_query[$key] = array('key' => '_' . $key, 'value' => stripslashes($value));
		
	}
	
	unset($meta_query['email']);
	unset($meta_query['phone']);

	if($addresses = wc_get_addresses( array(
		'post_type' => 'wc-address',
		'meta_query' => $meta_query
	) )) {
		
		$address_id = reset($addresses)->id;
		
	}
	
	else {
		
		$address_id = wp_insert_post(
			array(
				'post_title' => $args['address_1'] . ' ' . $args['postcode'], 
				'post_type' => 'wc-address', 
				'post_status' => 'publish',
				'post_author' => get_current_user_id(),
			),
			true
		);
		
	}
	
	foreach($args as $key => $value) {
		
		if($value)
			update_post_meta($address_id, '_' . $key, stripslashes($value));
		
	}
	
	return $address_id;
	
}