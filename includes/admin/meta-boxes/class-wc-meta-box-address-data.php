<?php
/**
 * Address Data
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
 * WC_Meta_Box_Address_Data Class
 */
class WC_Meta_Box_Address_Data {
	
	/**
	 * Address fields
	 *
	 * @var array
	 */
	protected static $address_fields = array();

	/**
	 * Init address fields we display + save
	 */
	public static function init_address_fields() {

		self::$address_fields = apply_filters( 'woocommerce_companies_admin_address_fields', array(
			'first_name' => array(
				'label' => __('First Name', 'woocommerce'),
				'type' => 'text',
				'input_class' => array('widefat'),
				'required' => true,
				'placeholder' => __('Please enter the first name for the contact at this address'),
			),
			'last_name' => array(
				'label' => __('Last Name', 'woocommerce'),
				'type' => 'text',
				'input_class' => array('widefat'),
				'required' => true,
				'placeholder' => __('Please enter the last name for the contact at this address'),
			),
			'address_1' => array(
				'label' => __('Address 1', 'woocommerce'),
				'type' => 'text',
				'input_class' => array('widefat'),
				'required' => true,
				'placeholder' => __('Please enter the first line of address for this address'),
			),
			'address_2' => array(
				'label' => __('Address 2', 'woocommerce'),
				'type' => 'text',
				'input_class' => array('widefat'),
				'placeholder' => __('Please enter the second line of address for this address'),
			),
			'city' => array(
				'label' => __('City / Town', 'woocommerce'),
				'type' => 'text',
				'input_class' => array('widefat'),
				'required' => true,
				'placeholder' => __('Please enter the city / town for this address'),
			),
			'state' => array(
				'label' => __('State / Province', 'woocommerce'),
				'type' => 'text',
				'input_class' => array('widefat',),
			),
			'postcode' => array(
				'label' => __('Zip / Postcode', 'woocommerce'),
				'type' => 'text',
				'input_class' => array('widefat'),
				'required' => true,
				'placeholder' => __('Please enter the zip / postcode for this address'),
			),
			'country' => array(
				'label' => __('Country', 'woocommerce'),
				'type' => 'country',
				'required' => true,
				'input_class' => array('wc-enhanced-select'),
			),
			'email' => array(
				'label' => __('Email Address', 'woocommerce'),
				'type' => 'text',
				'required' => false,
				'input_class' => array('widefat'),
				'placeholder' => __('Please enter the email address for this address'),
			),
			'phone' => array(
				'label' => __('Phone', 'woocommerce'),
				'type' => 'text',
				'required' => false,
				'input_class' => array('widefat'),
				'placeholder' => __('Please enter the telephone for this address'),
			),
		) );
		
		return self::$address_fields;
		
	}

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
		
		self::init_address_fields();
		
		$fields = self::$address_fields;
			
		ob_start();
		
		include('views/html-address-data.php');
		
		$html = ob_get_contents();
		
		ob_end_clean();
		
		echo $html;
		
	}
	
	/**
	 * Save meta box data
	 */
	public static function save( $post_id, $post ) {
		
		global $wpdb;

		self::init_address_fields();
		
		$fields = self::$address_fields;

		foreach(array_keys($fields) as $field_key) {
				
			$field_key = preg_replace('/[^A-Za-z0-9_\-]/', '', $field_key);
			
			if( isset( $_REQUEST[$field_key] ) ) {
				
				update_post_meta($post_id, '_' . $field_key, $_REQUEST[$field_key]);
				
			} else {
				
				update_post_meta($post_id, '_' . $field_key, '');
				
			}
			
		}

		wc_delete_address_transients( $post_id );
		
	}	
	
}	
	