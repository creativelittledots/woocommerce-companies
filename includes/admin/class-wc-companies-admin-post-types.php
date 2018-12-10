<?php
/**
 * Post Types Admin
 *
 * @author      Creative Little DOts
 * @category    Admin
 * @package     WooCommerce Companies/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Companies_Admin_Post_Types' ) ) :

/**
 * WC_Admin_Post_Types Class
 *
 * Handles the edit posts views and some functionality on the edit post screen for WC post types.
 */
class WC_Companies_Admin_Post_Types extends WC_Admin_Post_Types {

	/**
	 * Constructor
	 */
	public function __construct() {
		
		// Companies Columns
		add_filter( 'manage_edit-wc-company_columns' , array($this, 'company_columns') );
		add_filter( 'manage_wc-company_posts_custom_column' , array($this, 'render_company_columns'), 10, 2 );
		add_filter( 'manage_edit-wc-company_sortable_columns', array($this, 'company_sortable_columns') );
		
		// Companies Validation
		add_filter( 'wp_insert_post_data', array($this, 'company_before_save'), 10, 2 );
		add_filter( 'wp_insert_post_data' , array($this, 'company_set_title'), 99, 2 );
		add_action( 'admin_notices', array($this, 'print_company_transients') );
		
		// Addresses Columns
		add_filter( 'manage_edit-wc-address_columns' , array($this, 'address_columns') );
		add_filter( 'manage_wc-address_posts_custom_column' , array($this, 'render_address_columns'), 10, 2 );
		add_filter( 'manage_edit-wc-address_sortable_columns', array($this, 'address_sortable_columns') );
		
		// Addresses Validation
		add_filter( 'wp_insert_post_data', array($this, 'address_before_save'), 10, 2 );
		add_filter( 'wp_insert_post_data' , array($this, 'address_set_title'), 99, 2 );
		add_action( 'admin_notices', array($this, 'print_address_transients') );
		
		add_action( 'pre_get_posts', array($this, 'query_addresses') );
		add_action( 'pre_get_posts', array($this, 'query_companies') );
		
		

	}
	
	public function query_addresses( $query ) {
		
		if ( $query->is_post_type_archive( 'wc-address' ) && $query->is_main_query() && get_query_var( 's' ) ) {
			
			$term = get_query_var( 's' );
			
			$meta_query = $query->get( 'meta_query' ) ? $query->get( 'meta_query' ) : array();
			
			$meta_query = array_merge($meta_query, array_map(function($meta) use($term) {
				return [
					'key' => $meta,
					'value' => $term,
					'compare' => 'LIKE'
				];
			}, array(
				'_address_1',
				'_address_2',
				'_city',
				'_state',
				'_postcode',
				'_accounting_reference'
			)));
			
			$meta_query['relation'] = 'OR';
			
	        $query->set( 'meta_query', $meta_query );
	        
	        $query->set( 's', '' );
	        
	    }
		
	}
	
	public function query_companies( $query ) {
		
		if ( $query->is_post_type_archive( 'wc-company' ) && $query->is_main_query() && get_query_var( 's' ) ) {
			
			$term = get_query_var( 's' );
			
			$meta_query = $query->get( 'meta_query' ) ? $query->get( 'meta_query' ) : array();
			
			$meta_query = array_merge($meta_query, array_map(function($meta) use($term) {
				return [
					'key' => $meta,
					'value' => $term,
					'compare' => 'LIKE'
				];
			}, array(
				'_number',
				'_company_name',
				'_accounting_reference'
			)));
			
			$meta_query['relation'] = 'OR';
			
	        $query->set( 'meta_query', $meta_query );
	        
	        $query->set( 's', '' );
	        
	    }
		
	}
	
	/**
	 * Define custom columns for companies
	 * @param  array $existing_columns
	 * @return array
	 */
	public function company_columns($columns) {
			
		$columns = apply_filters('woocommerce_companies_company_admin_columns', array(
			'cb' => __('Select All', 'woocommerce_companies'),
			'title' => __('Title', 'woocommerce_companies'),
			'accounting_reference' => __('Accounting Reference', 'woocommerce_companies'),
			'primary_shipping_address' => __('Primary Shipping Address', 'woocommerce_companies'),
			'primary_billing_address' => __('Primary Billing Address', 'woocommerce_companies'),
			'gdpr_consent' => __('GDPR Consent', 'woocommerce_companies'),
			'date' => __('Date Created', 'woocommerce_companies'),
		));
		
		return $columns;
		
	}
	
	/**
	 * Ouput custom columns for companies
	 * @param  string $column
	 * @param  int $post_id
	 */
	public function render_company_columns( $column, $post_id ) {
		
	    switch ( $column ) {
	
	        case 'accounting_reference' :
	            
	            echo get_post_meta($post_id, '_accounting_reference', true);
	            
	        break;
	        
	        case 'primary_shipping_address' :
	        
	        	$company = wc_get_company($post_id);
	        	
	        	if($primary_shipping_address = $company->get_primary_shipping_address()) {
		        	
		        	$formatted_address = $primary_shipping_address->get_formatted_address();
		        	
		        	$edit_address_link = get_edit_post_link($primary_shipping_address->id);
		        	
		        	$formatted_address .= "<br><a href=\"$edit_address_link\">Edit Address</a>";
		        	
		        	echo $formatted_address;
		        	
	        	} else {
		        	
		        	echo __('No address set.', 'woocommerce-companies');
		        	
	        	}
				
			break;
			
	        case 'primary_billing_address' :
	        
	        	$company = wc_get_company($post_id);
	        	
	        	if($primary_billing_address = $company->get_primary_billing_address()) {
		        	
		        	$formatted_address = $primary_billing_address->get_formatted_address();
		        	
		        	$edit_address_link = get_edit_post_link($primary_billing_address->id);
		        	
		        	$formatted_address .= "<br><a href=\"$edit_address_link\">Edit Address</a>";
		        	
		        	echo $formatted_address;
		        	
	        	} else {
		        	
		        	echo __('No address set.', 'woocommerce-companies');
		        	
	        	}
	            
	        break;
	        
	        case 'gdpr_consent' :
	        
	        	echo get_post_meta($post_id, '_gdpr_consent', true) ? '✔' : '✖';
	        	
	        break;
	
	    }
	    
	}
	
	/**
	 * Output custom columns for companies
	 * @param  string $column
	 */
	public function company_sortable_columns($columns) {
			
		$columns = apply_filters('woocommerce_companies_company_admin_sortable_columns', array(
			'title' => 'title',
			'accounting_reference' => 'accounting_reference',
			'date' => 'date',
		));
		
		return $columns;
		
	}
	
	/**
	 * Validation for companies
	 * @param  array $data
	 * @param  array $postarr
	 */
	public function company_before_save( $data, $postarr ) {
			
		if( $data['post_type'] == 'wc-company' && $_SERVER['REQUEST_METHOD'] == 'POST' && is_admin() && ! is_ajax() ) {
			
			$wc_companies_notices = get_transient('wc_companies_notices');
		
			$companyFields = WC_Meta_Box_Company_Data::init_company_fields();
			
			foreach($companyFields as $field_key => $field_value) {
				
				if((!isset($postarr[$field_key]) || empty( $postarr[$field_key] )) && isset($field_value['required']) && $field_value['required']) {
					
					$wc_companies_notices .= "<div class=\"error\"><p>{$field_value['label']} is a required field.</p></div>";
					
					set_transient('wc_companies_notices', $wc_companies_notices);
					  
					wp_redirect(wp_get_referer());
					
					exit;
					
				}
				
			}
			
		}
		
		return $data;
		
	}
	
	/**
	 * Print company transients
	 */
	public function print_company_transients() {
			
		$transient = get_transient('wc_companies_notices');

	    if(!empty($transient)) print $transient;
		
		delete_transient('wc_companies_notices'); 
	
	}
	
	/**
	 * Set post title for companies
	 * @param  array $data
	 * @param  array $postarr
	 */
	public function company_set_title($data , $postarr) {
			
		if( $data['post_type'] == 'wc-company' && $_SERVER['REQUEST_METHOD'] == 'POST' && is_admin() && ! is_ajax() ) {
		
			$data['post_title'] = apply_filters('woocommerce_company_title', $postarr['company_name'], $data , $postarr);
			
		}
		
		return $data;
		
	}
	
	/**
	 * Define custom columns for addresses
	 * @param  array $existing_columns
	 * @return array
	 */
	public function address_columns($columns) {
		
		$columns = apply_filters('woocommerce_companies_address_admin_columns', array(
			'cb' => __('Select All', 'woocommerce_companies'),
			'title' => __('Title', 'woocommerce_companies'),
			'first_name' => __('First Name', 'woocommerce_companies'),
			'last_name' => __('Last Name', 'woocommerce_companies'),
			'city' => __('Town / City', 'woocommerce_companies'),
			'state' => __('State / Province', 'woocommerce_companies'),
			'date' => __('Title', 'woocommerce_companies'),
		));
		
		return $columns;
		
	}
	
	/**
	 * Ouput custom columns for companies
	 * @param  string $column
	 * @param  int $post_id
	 */
	 
	public function render_address_columns( $column, $post_id ) {
			
	    switch ( $column ) {
	
	        case 'first_name' :
	        case 'last_name' :
	        case 'city' :
	        case 'state' :
	            
	            echo get_post_meta($post_id, '_' . $column, true);
	            
	        break;
	
	    }
	    
	}
	
	/**
	 * Output custom columns for addresses
	 * @param  string $column
	 */
	public function address_sortable_columns($columns) {
		
		$columns = apply_filters('woocommerce_companies_address_admin_sortable_columns', array(
			'title' => 'title',
			'first_name' => 'first_name',
			'last_name' => 'last_name',
			'city' => 'city',
			'state' => 'state',
			'date' => 'date',
		));
		
		return $columns;
		
	}
	
	/**
	 * Validation for addresses
	 * @param  array $data
	 * @param  array $postarr
	 */
	public function address_before_save( $data, $postarr ) {
			
		if( $data['post_type'] == 'wc-address' && $_SERVER['REQUEST_METHOD'] == 'POST' && is_admin() && ! is_ajax() ) {
			
			$wc_addresses_notices = get_transient('wc_addresses_notices');
		
			$addressFields = WC_Meta_Box_Address_Data::init_address_fields();
			
			foreach($addressFields as $field_key => $field_value) {
				
				if( ( ! isset($postarr[$field_key]) || empty( $postarr[$field_key] )) && isset( $field_value['required'])  && $field_value['required'] ) {
					
					$wc_addresses_notices .= "<div class=\"error\"><p>{$field_value['label']} is a required field.</p></div>";
					
					set_transient('wc_addresses_notices', $wc_addresses_notices);
					  
					wp_redirect(wp_get_referer());
					
					exit;
					
				}
				
			}
			
		}
		
		return $data;
		
	}
	
	/**
	 * Print address transients
	 */
	public function print_address_transients() {
			
		$transient = get_transient('wc_addresses_notices');

	    if(!empty($transient)) print $transient;
		
		delete_transient('wc_addresses_notices'); 
	
	}
	
	/**
	 * Set post title for addresses
	 * @param  array $data
	 * @param  array $postarr
	 */
	public function address_set_title($data , $postarr) {
			
		if( $data['post_type'] == 'wc-address' && $_SERVER['REQUEST_METHOD'] == 'POST' && is_admin() && ! is_ajax() ) {
		
			$data['post_title'] = apply_filters('woocommerce_address_title', $postarr['address_1'], $data , $postarr);
			
		}
		
		return $data;
		
	}
		
}

endif;

new WC_Companies_Admin_Post_Types();