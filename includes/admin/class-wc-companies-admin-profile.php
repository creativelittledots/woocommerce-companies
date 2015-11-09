<?php
/**
 * Add extra profile fields for users in admin.
 *
 * @author      WooThemes
 * @category    Admin
 * @package     WooCommerce Companies/Admin
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Companies_Admin_Profile' ) ) :

/**
 * WC_Companies_Admin_Profile
 */
class WC_Companies_Admin_Profile extends WC_Admin_Profile {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		
		add_filter( 'woocommerce_customer_meta_fields', array($this, 'replace_customer_meta_fields') );
		
		add_filter( 'woocommerce_address_customer_meta_fields', array($this, 'add_customer_companies_meta_fields') );
		
		add_action( 'personal_options_update', array( $this, 'save_customer_company_meta_fields' ), 20 );
		add_action( 'edit_user_profile_update', array( $this, 'save_customer_company_meta_fields' ), 20 );
		
		add_action( 'personal_options_update', array( $this, 'save_customer_address_meta_fields' ), 20 );
		add_action( 'edit_user_profile_update', array( $this, 'save_customer_address_meta_fields' ), 20 );
		
		add_filter( 'manage_users_columns', array($this, 'user_columns') );
		add_action( 'manage_users_custom_column',  array($this, 'render_user_columns'), 10, 3);
			
	}
	
	/**
	 * Replace Address Fields on edit user pages
	 *
	 * @param array $fieldsets Fieldsets passed into hook 'woocommerce_customer_meta_fields'
	 */
	public function replace_customer_meta_fields($fieldsets = array()) {
		
		global $user_id;
		
		$addresses = array();
		
		foreach(wc_get_addresses() as $address) {
			
			$addresses[$address->id] = $address->get_title();
			
		}
		
		$primary_addresses = array(
			0 => 'None',
		);
		
		foreach(get_user_all_addresses($user_id) as $address) {
			
			$primary_addresses[$address->id] = $address->get_title();
			
		}
			
		$fieldsets = apply_filters('woocommerce_address_customer_meta_fields', array(
			'billing' => array(
				'title' => __( 'Customer Billing Address', 'woocommerce' ),
				'fields' => array(
					'primary_billing_address' => array(
						'label' => __( 'Primary Billing Address', 'woocommerce' ),
						'class' => 'billing',
						'type' => 'select',
						'description' => 'Please select primary billing address',
						'options' => $primary_addresses,
					),
					'billing_addresses[]' => array(
						'label' => __( 'Billing Addresses', 'woocommerce' ),
						'class' => 'chosen billing',
						'type' => 'select',
						'description' => 'Please select billing addresses',
						'options' => $addresses,
					),
				)
			),
			'shipping' => array(
				'title' => __( 'Customer Shipping Address', 'woocommerce' ),
				'fields' => array(
					'primary_shipping_address' => array(
						'label' => __( 'Primary Shipping Address', 'woocommerce' ),
						'class' => 'shipping',
						'type' => 'select',
						'description' => 'Please select primary shipping address',
						'multiple' => true,
						'options' => $primary_addresses,
					),
					'shipping_addresses[]' => array(
						'label' => __( 'Shipping Addresses', 'woocommerce' ),
						'class' => 'chosen shipping',
						'type' => 'select',
						'description' => 'Please select shipping addresses',
						'multiple' => true,
						'options' => $addresses,
					)
				)
			),
		));
		
		return $fieldsets;
		
	}
	
	/**
	 * Add Company Fields on edit user pages
	 *
	 * @param array $fieldsets Fieldsets passed into hook 'woocommerce_address_customer_meta_fields'
	 */
	public function add_customer_companies_meta_fields($fieldsets = array()) {
		
		global $user_id;
		
		$companies = array();
		
		foreach(wc_get_companies() as $company) {
			
			$companies[$company->id] = $company->title;
			
		}
		
		$customer_companies = array(
			0 => 'None',
		);
		
		foreach(get_user_companies($user_id) as $company) {
			
			$customer_companies[$company->id] = $company->get_title();
			
		}
			
		$fieldsets['companies'] = array(
			'title' => __( 'Customer Companies', 'woocommerce' ),
			'fields' => array(
				'primary_company' => array(
					'label' => __( 'Primary Company', 'woocommerce' ),
					'class' => 'company',
					'type' => 'select',
					'description' => 'Please select primary company',
					'options' => $customer_companies,
				),
				'companies[]' => array(
					'label' => __( 'Companies', 'woocommerce' ),
					'class' => 'chosen company',
					'type' => 'select',
					'description' => 'Please select companies',
					'options' => $companies,
				)
			)
		);
		
		return $fieldsets;
		
	}
	
	/**
	 * Save Company Fields on edit user pages
	 *
	 * @param mixed $user_id User ID of the user being saved
	 */
	public function save_customer_company_meta_fields( $user_id ) {
			
		$save_fields = $this->add_customer_companies_meta_fields();

		foreach( $save_fields as $fieldset ) {

			foreach( $fieldset['fields'] as $key => $field ) {
				
				$key = preg_replace('/[^A-Za-z0-9_\-]/', '', $key);

				if ( isset( $_POST[ $key ] ) ) {
					
					update_user_meta( $user_id, $key, $_POST[ $key ] );
				}
				
				else {
					
					update_user_meta( $user_id, $key, array() );
					
				}
				
			}
			
		}
		
	}
	
	/**
	 * Save Address Fields on edit user pages
	 *
	 * @param mixed $user_id User ID of the user being saved
	 */
	public function save_customer_address_meta_fields( $user_id ) {
			
		$save_fields = $this->replace_customer_meta_fields();

		foreach( $save_fields as $fieldset ) {

			foreach( $fieldset['fields'] as $key => $field ) {
				
				$key = preg_replace('/[^A-Za-z0-9_\-]/', '', $key);

				if ( isset( $_POST[ $key ] ) ) {
					
					update_user_meta( $user_id, $key, $_POST[ $key ] );
				}
				
				else {
					
					update_user_meta( $user_id, $key, array() );
					
				}
				
			}
			
		}
		
	}
	
	public function user_columns($columns) {
			
	    $columns['companies'] = __('Companies', 'woocommerce_companies');
	    
	    return $columns;
	    
	}
	
	public function render_user_columns($value, $column_name, $user_id) {
	    
		switch($column_name) {
			
			case 'companies' :
			
				$companies = get_user_meta($user_id, 'companies', true);
				
				$values = array();
				
				if($companies) {
					
					foreach($companies as $company_id) {
					
						$title = get_the_title($company_id);
						
						$link = get_edit_post_link($company_id);
						
						$values[] = "<a href=\"$link\">" . $title . "</a>";
						
					}
				
				}
				
				$values = array_unique($values);
				
				$value = implode(', ', $values);
			
			break;
			
		}
			
	    return $value;
	    
	}

}

endif;

return new WC_Companies_Admin_Profile();
