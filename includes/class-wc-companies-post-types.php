<?php
/**
 * Post Types
 *
 * Registers post types and taxonomies
 *
 * @class       WC_Companies_Post_types
 * @version    	1.0.0
 * @package     WooCommerce Companies/Classes/Products
 * @category    Class
 * @author      Creative Little Dots
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Companies_Post_types Class
 */
class WC_Companies_Post_types {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
	}

	/**
	 * Register core post types.
	 */
	public static function register_post_types() {

		do_action( 'woocommerce_companies_register_post_type' );
		
		register_post_type( 'wc-address', apply_filters('wc_address_post_type_args', array(
				'labels'             => apply_filters('wc_address_post_type_labels', array(
					'name'               => _x( 'Addresses', 'post type general name', 'woocommerce-companies' ),
					'singular_name'      => _x( 'Address', 'post type singular name', 'woocommerce-companies' ),
					'menu_name'          => _x( 'Addresses', 'admin menu', 'woocommerce-companies' ),
					'name_admin_bar'     => _x( 'Address', 'add new on admin bar', 'woocommerce-companies' ),
					'add_new'            => _x( 'Add New', 'address', 'woocommerce-companies' ),
					'add_new_item'       => __( 'Add New Address', 'woocommerce-companies' ),
					'new_item'           => __( 'New Address', 'woocommerce-companies' ),
					'edit_item'          => __( 'Edit Address', 'woocommerce-companies' ),
					'view_item'          => __( 'View Address', 'woocommerce-companies' ),
					'all_items'          => __( 'All Addresses', 'woocommerce-companies' ),
					'search_items'       => __( 'Search Addresses', 'woocommerce-companies' ),
					'parent_item_colon'  => __( 'Parent Addresses:', 'woocommerce-companies' ),
					'not_found'          => __( 'No addresses found.', 'woocommerce-companies' ),
					'not_found_in_trash' => __( 'No addresses found in Trash.', 'woocommerce-companies' )
				)),
				'public'             => false,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => false,
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => 57,
				'menu_icon'					=> 'dashicons-id',
				'supports'           => array( '' )
		)) );
		
		register_post_type( 'wc-company', apply_filters('wc_company_post_type_args', array(
				'labels'             => apply_filters('wc_company_post_type_labels', array(
					'name'               => _x( 'Companies', 'post type general name', 'woocommerce-companies' ),
					'singular_name'      => _x( 'Company', 'post type singular name', 'woocommerce-companies' ),
					'menu_name'          => _x( 'Companies', 'admin menu', 'woocommerce-companies' ),
					'name_admin_bar'     => _x( 'Company', 'add new on admin bar', 'woocommerce-companies' ),
					'add_new'            => _x( 'Add New', 'company', 'woocommerce-companies' ),
					'add_new_item'       => __( 'Add New Company', 'woocommerce-companies' ),
					'new_item'           => __( 'New Company', 'woocommerce-companies' ),
					'edit_item'          => __( 'Edit Company', 'woocommerce-companies' ),
					'view_item'          => __( 'View Company', 'woocommerce-companies' ),
					'all_items'          => __( 'All Companies', 'woocommerce-companies' ),
					'search_items'       => __( 'Search Companies', 'woocommerce-companies' ),
					'parent_item_colon'  => __( 'Parent Companies:', 'woocommerce-companies' ),
					'not_found'          => __( 'No companies found.', 'woocommerce-companies' ),
					'not_found_in_trash' => __( 'No companies found in Trash.', 'woocommerce-companies' )
				)),
				'public'             => false,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => false,
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => 56,
				'menu_icon'				=> 'dashicons-groups',
				'supports'           => array( 'thumbnail' )
		)) );
		
	}
	
}

WC_Companies_Post_types::init();
