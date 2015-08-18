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
function  wc_get_companies( $args = array() ) {
	
	$args = array_merge(array(
		'post_type' => 'wc-company',
		'showposts' => -1,
	), $args);
	
	$companies = get_posts($args);
	
	foreach($companies as &$company) {
		
		$company = new WC_Company($company->ID);
		
	}
	
	return $companies;
	
}

/**
 * Create Company
 *
 * @param array $args (default: Array)
 */
function wc_create_company( $args = array() ) {
	
	$args = array_merge(array(
		'company_name' => '',
		'company_number' => '',
	), $args);
	
	extract($args);
	
	if(empty($company_name) || empty($company_number)) {
		return -1;
	}
			
	$args = array(
		'slug' => esc_sql($company_name),
		'meta_key' => '_company_number',
		'meta_value' => esc_sql($company_number)
	);
	
	if($companies = wc_get_companies( $args )) {
		
		$company_id = reset($companies)->ID;
		
	}
	
	else {
		
		$company_id = wp_insert_post(
			array(
				'post_title' => esc_sql($company_name), 
				'post_type' => 'wc-company', 
				'post_status' => 'publish',
				'post_author' => get_current_user_id(),
			),
			true
		);
		
		update_post_meta($company_id, '_company_name', $company_name);
		update_post_meta($company_id, '_company_number', $company_number);
		
	}
	
	return $company_id;
	
}