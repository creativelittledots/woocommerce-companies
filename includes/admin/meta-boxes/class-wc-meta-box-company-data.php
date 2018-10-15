<?php
/**
 * Company Data
 *
 * Functions for displaying the order actions meta box.
 *
 * @author      WooThemes
 * @category    Admin
 * @package     WooCommerce/Admin/Meta Boxes
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WC_Meta_Box_Company_Data Class
 */
class WC_Meta_Box_Company_Data {
	
	/**
	 * Company fields
	 *
	 * @var array
	 */
	protected static $company_fields = array();

	/**
	 * Init company fields we display + save
	 */
	public static function init_company_fields( $post = null ) {
    	
    	$billing_addresses = $shipping_addresses = array();
		
		if( $post ) {
    		
    		$post_billing_addresses = get_post_meta($post->ID, '_billing_addresses', true);
    		
    		$post_billing_addresses = $post_billing_addresses ? $post_billing_addresses : array();
		
    		foreach($post_billing_addresses as $address) {
        		
        		if( $address = wc_get_address( $address ) ) {
    			
    				$billing_addresses[$address->id] = $address->get_title();
    				
    			}
    			
    		}
    		
    		$post_shipping_addresses = get_post_meta($post->ID, '_shipping_addresses', true);
    		
    		$post_shipping_addresses = $post_shipping_addresses ? $post_shipping_addresses : array();
    		
    		foreach($post_shipping_addresses  as $address) {
        		
        		if( $address = wc_get_address( $address ) ) {
    			
    				$shipping_addresses[$address->id] = $address->get_title();
    				
    			}
    			
    		}
    		
        }

		self::$company_fields = apply_filters( 'woocommerce_companies_admin_company_fields', array(
			'company_name' => array(
				'label' => __('Company Name', 'woocommerce'),
				'type' => 'text',
				'required' => true,
				'input_class' => array('widefat'),
				'placeholder' => __('Please enter the company name'),
				'public' => true,
				'quick_edit' => true,
			),
			'number' => array(
				'label' => __('Company Number', 'woocommerce'),
				'type' => 'text',
				'input_class' => array('widefat'),
				'placeholder' => __('Please enter the company number'),
				'public' => true,
				'quick_edit' => true,
			),
			'accounting_reference' => array(
				'label' => __('Accounting Reference', 'woocommerce'),
				'type' => 'text',
				'input_class' => array('widefat'),
				'placeholder' => __('Please enter the your accounting reference for this company'),
				'public' => false,
				'quick_edit' => true,
			),
			'available_credit' => array(
				'label' => __('Available Credit', 'woocommerce'),
				'type' => 'text',
				'input_class' => array('widefat'),
				'placeholder' => __('Please enter the your available credit for this company'),
				'public' => false,
				'quick_edit' => false,
			),
			'primary_billing_address' => array(
				'label' => __('Primary Billing Address', 'woocommerce'),
				'type' => 'select',
				'input_class' => array('widefat'),
				'placeholder' => __('Please enter the primary billing address for this company'),
				'options' =>  array(0 => 'None') + $billing_addresses,
				'public' => true,
				'quick_edit' => false,
			),
			'billing_addresses[]' => array(
				'label' => __('Billing Addresses', 'woocommerce'),
				'type' => 'advanced_search',
				'input_class' => array('wc-advanced-search'),
				'defaults' => $billing_addresses,
				'custom_attributes' => array(
        			'data-action' => 'woocommerce_json_search_addresses',
                    'data-nonce' => wp_create_nonce( 'search-addresses' ),
                    'data-placeholder' => __('Please enter the billing addresses for this company'),
    			),
				'multiple' => true,
				'public' => true,
				'quick_edit' => false,
			),
			'primary_shipping_address' => array(
				'label' => __('Primary Shipping Address', 'woocommerce'),
				'type' => 'select',
				'input_class' => array('widefat'),
				'placeholder' => __('Please enter the primary shipping addresses for this company'),
				'options' => array(0 => 'None') + $shipping_addresses,
				'public' => true,
				'quick_edit' => false,
			),
			'shipping_addresses[]' => array(
				'label' => __('Shipping Addresses', 'woocommerce'),
				'type' => 'advanced_search',
				'input_class' => array('wc-advanced-search'),
				'defaults' => $shipping_addresses,
				'custom_attributes' => array(
        			'data-action' => 'woocommerce_json_search_addresses',
                    'data-nonce' => wp_create_nonce( 'search-addresses' ),
                    'data-placeholder' => __('Please enter the shipping addresses for this company'),
    			),
				'multiple' => true,
				'public' => true,
				'quick_edit' => false,
			),
			'free_shipping' => array(
				'label' => __('Free Shipping?', 'woocommerce'),
				'type' => 'select',
				'options' =>  array(
					0 => __('No', 'woocommerce'),
					1 => __('Yes', 'woocommerce'),
				),
				'input_class' => array('inline'),
				'public' => false,
				'quick_edit' => false,
			),
		) );
		
		return self::$company_fields;
	}

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
		
		self::init_company_fields( $post );
		
		$fields = self::$company_fields;
			
		ob_start();
		
		include('views/html-company-data.php');
		
		$html = ob_get_contents();
		
		ob_end_clean();
		
		echo $html;
		
	}

	/**
	 * Save meta box data
	 */
	public static function save( $post_id, $post ) {
		
		global $wpdb;

		self::init_company_fields();
		
		$fields = self::$company_fields;

		foreach($fields as $key => $field) {
			
			$key = str_replace('[]', '', $key);
			
			if( isset( $_REQUEST[ $key ] ) ) {
				
				update_post_meta($post_id, '_' . $key, $_REQUEST[ $key ] );
				
			} else {
				
				update_post_meta($post_id, '_' . $key, '');
				
			}
			
		}

		wc_delete_company_transients( $post_id );
		
	}	
	
}	
	