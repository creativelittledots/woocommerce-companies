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
	public static function init_company_fields() {
		
		$list_addresses = array();
		
		foreach(wc_get_addresses() as $address) {
			
			$list_addresses[$address->id] = $address->title;
			
		}

		self::$company_fields = apply_filters( 'woocommerce_companies_admin_company_fields', array(
			'company_name' => array(
				'label' => __('Company Name', 'woocommerce'),
				'type' => 'text',
				'required' => true,
				'input_class' => array('widefat'),
				'placeholder' => __('Please enter the company name'),
				'public' => true,
			),
			'company_number' => array(
				'label' => __('Company Number', 'woocommerce'),
				'type' => 'text',
				'required' => true,
				'input_class' => array('widefat'),
				'placeholder' => __('Please enter the company number'),
				'public' => true,
			),
			'internal_company_id' => array(
				'label' => __('Internal Company ID', 'woocommerce'),
				'type' => 'text',
				'input_class' => array('widefat'),
				'placeholder' => __('Please enter the your internal company ID'),
				'public' => false,
			),
			'available_credit' => array(
				'label' => __('Available Credit', 'woocommerce'),
				'type' => 'text',
				'input_class' => array('widefat'),
				'placeholder' => __('Please enter the your available credit for this company'),
				'public' => false,
			),
			'primary_billing_address' => array(
				'label' => __('Primary Billing Address', 'woocommerce'),
				'type' => 'select',
				'options' =>  array_merge(array(0 => 'None'), $list_addresses),
				'input_class' => array('widefat', 'chosen'),
				'placeholder' => __('Please enter the primary billing address for this company'),
				'public' => true,
			),
			'billing_addresses' => array(
				'label' => __('Billing Addresses', 'woocommerce'),
				'type' => 'multi-select',
				'options' =>  $list_addresses,
				'input_class' => array('widefat', 'chosen'),
				'custom_attributes' => array(
					'multiple' => 'multiple'
				),
				'placeholder' => __('Please enter the billing addresses for this company'),
				'public' => true,
			),
			'primary_shipping_address' => array(
				'label' => __('Primary Shipping Address', 'woocommerce'),
				'type' => 'select',
				'options' =>  array_merge(array(0 => 'None'), $list_addresses),
				'input_class' => array('widefat', 'chosen'),
				'placeholder' => __('Please enter the primary shipping addresses for this company'),
				'public' => true,
			),
			'shipping_addresses' => array(
				'label' => __('Shipping Addresses', 'woocommerce'),
				'type' => 'multi-select',
				'options' =>  $list_addresses,
				'input_class' => array('widefat', 'chosen'),
				'custom_attributes' => array(
					'multiple' => 'multiple'
				),
				'placeholder' => __('Please enter the shipping addresses for this company'),
				'public' => true,
			),
		) );
		
		return self::$company_fields;
	}

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
		
		self::init_company_fields();
		
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

		foreach(array_keys($fields) as $field_key) {
				
			$field_key = preg_replace('/[^A-Za-z0-9_\-]/', '', $field_key);
			
			if(isset($_REQUEST[$field_key])) {
				
				update_post_meta($post_id, '_' . $field_key, $_REQUEST[$field_key]);
				
			} else {
				
				update_post_meta($post_id, '_' . $field_key, '');
				
			}
			
		}

		wc_delete_company_transients( $post_id );
		
	}	
	
}	
	