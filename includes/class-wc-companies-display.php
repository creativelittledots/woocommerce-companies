<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WooCommerce WC_Companies_Display
 *
 *
 * @class 		WC_Companies_Display
 * @version		1.0.0
 * @package		WooCommerce Companies/Classes
 * @category	Class
 * @author 		Creative Little Dots
 */
class WC_Companies_Display {
	
	/**
	 * Hook in methods
	 */
	public function __construct() {

		add_action( 'woocommerce_after_my_account', array($this, 'display_my_companies') );
			
		add_filter( 'woocommerce_my_account_my_address_description', array($this, 'my_account_my_address_description_actions') );
		
		add_action( 'woocommerce_checkout_before_customer_details', array($this, 'add_checkout_type_field') );
			
		add_action( 'woocommerce_checkout_before_customer_details', array($this, 'add_company_id_field') );
		
		add_action( 'woocommerce_checkout_before_customer_details', array($this, 'add_company_details_fields') );
		
		add_action( 'woocommerce_before_checkout_billing_form', array($this, 'before_checkout_billing_fields') );
		
		add_action( 'woocommerce_after_checkout_billing_form', array($this, 'after_checkout_billing_fields') );
		
		add_action( 'woocommerce_before_checkout_shipping_form', array($this, 'before_checkout_shipping_fields') );
		
		add_action( 'woocommerce_after_checkout_shipping_form', array($this, 'after_checkout_shipping_fields') );
		
		add_filter( 'woocommerce_billing_fields', array($this, 'hide_billing_company_on_checkout') );
			
		add_filter( 'woocommerce_shipping_fields', array($this, 'hide_shipping_company_on_checkout') );
		
		add_filter( 'woocommerce_my_account_edit_address_title', array($this, 'my_account_edit_address_title') );
		
		add_filter( 'woocommerce_my_account_get_addresses', array($this, 'my_account_get_addresses') );
		
		add_action( 'woocommerce_form_field_multi-select', array($this, 'multi_select_field'), 10, 4 );
		
		add_filter( 'woocommerce_companies_view_addresses_title', array($this, 'companies_view_addresses_title') );
				
	}
	
	/**
	 * displays companies on my account page
	 *
	 */
	public function display_my_companies() {
		
		if( get_the_ID () != get_option ( 'woocommerce_myaccount_page_id' , 0 ) )
			return;
			
		$user_id = get_current_user_id();
		
		$companies = get_user_companies();
		
		$company_count = apply_filters('woocommerce-companies-my-account-company-count', 3);
			
		wc_get_template('myaccount/my-companies.php', array(
			'company_count' 	=> 'all' == $company_count ? -1 : $company_count,
			'user' => get_user_by('id', $user_id),
			'companies' => $companies,
		), '', WC_Companies()->plugin_path() . '/templates/');
		
	
	}
	
	/**
	 * adds edit tage to the address header title
	 *
	 * @param string $title Title of of address section
	 */
	public function my_account_my_address_description_actions($text) {
		
		if(!is_wc_endpoint_url( 'edit-address' )) {
			
			ob_start();
			
			wc_get_template('myaccount/my-addresses.php', array('addresses' => get_user_all_addresses( get_current_user_id() )), '', WC_Companies()->plugin_path() . '/templates/');
			
			$text .= ob_get_contents();
			
			ob_end_clean();
				
		}
		
		return $text;
		
	}
	
	/**
	 * adds checkout type field to checkout
	 *
	 */
	public function add_checkout_type_field() {
				
		woocommerce_form_field('checkout_type', array(
			'label' => __('How are you checking out?', 'woocommerce'),
			'type' => 'radio',
			'options' => array(
				'customer' =>__( ' As an Individual', 'woocommerce'),
				'company' => __(' As a Company', 'woocommerce'),
			),
			'default' => WC_Companies()->checkout()->checkout_type,
			'label_class' => array('inline'),
		));
		
	}
	
	/**
	 * adds company id field to checkout
	 *
	 */
	public function add_company_id_field() {
		
		$style = WC_Companies()->checkout()->checkout_type != 'company' ? 'style="display:none;"' : '';
		
		echo '<div class="checkout_company_fields" ' . $style . '>';
			
			echo '<div class="checkout_select_company_field"';
		
			if(!WC_Companies()->checkout()->companies) {
				
				echo 'style="display:none;"';
				
			}
			
			echo '>';
			
			woocommerce_form_field('company_id', array(
				'label' => __('Which Company are you representing?', 'woocommerce'),
				'type' => 'select',
				'options' => array(0 => 'Select or Add new Company', -1 => 'Add new Company') + (WC_Companies()->checkout()->get_companies() ? WC_Companies()->checkout()->get_companies() : array()),
				'default' => WC_Companies()->checkout()->get_company() ? WC_Companies()->checkout()->get_company()->id : ( WC_Companies()->checkout()->get_companies() ? reset( array_keys( WC_Companies()->checkout()->get_companies() ) ) : -1 ),
				'input_class' => array('company_select'),
			));
			
		echo '</div>';
			
	}
	
	/**
	 * adds company details fields to checkout
	 *
	 */
	public function add_company_details_fields() {
			
		echo '<div class="checkout_add_company_fields"';
			
			if(WC_Companies()->checkout()->companies) {
				
				echo 'style="display:none;"';
				
			}
			
			echo '>';
				
				woocommerce_form_field('company_name', array(
					'label' => __('Company Name', 'woocommerce'),
					'type' => 'text',
					'required' => true,
					'placeholder' => __('Company Name', 'woocommerce'),
					'class' => array('form-row form-row-first'),
					'default' => WC()->checkout()->get_value('company_name'),
					'input_class' => array('widefat'),
				));
				
				woocommerce_form_field('company_number',  array(
					'label' => __('Company Number', 'woocommerce'),
					'type' => 'text',
					'required' => true,
					'placeholder' => __('Company Number', 'woocommerce'),
					'class' => array('form-row form-row-last'),
					'default' => WC()->checkout()->get_value('company_number'),
					'input_class' => array('widefat'),
				));
			
			echo '</div>';
			
			do_action('woocommerce_companies_after_company_fields');
			
		echo '</div>';
		
	}
	
	/**
	 * add billing address if field to the checkout
	 *
	 */
	public function before_checkout_billing_fields() {
		
		echo '<div class="checkout_select_billing_address_field"';
		
		if(!WC_Companies()->checkout()->get_billing_addresses()) {
			
			echo 'style="display:none;"';
			
		}
		
		echo '>';

		$billing_addresses = array();
		
		foreach(WC_Companies()->checkout()->get_billing_addresses() as $billing_address) {
			
			$billing_addresses[$billing_address->id] = $billing_address->title; 
			
		}
		
		woocommerce_form_field('billing_address_id', array(
			'label' => __('Billing Address', 'woocommerce'),
			'type' => 'select',
			'options' => array(0 => 'Select or Add new Address', -1 => 'Add new Address') + ($billing_addresses ? $billing_addresses : array()),
			'input_class' => array('address_select'),
			'default' => ($billing_addresses ? reset(array_keys($billing_addresses)) : -1),
			'custom_attributes' => array(
				'data-address_type' => 'billing',	
			)
		));
		
		echo '</div>';
		
		echo '<div class="checkout_billing_fields"';
		
		if(WC_Companies()->checkout()->get_billing_addresses()) {
			
			echo 'style="display:none;"';
			
		}
		
		echo '>';
		
	}
	
	/**
	 * display closeing div after checkout billing fields
	 *
	 */
	public function after_checkout_billing_fields() {
		
		echo '</div>';
		
	}
	
	/**
	 * add shipping address if field to the checkout
	 *
	 */
	public function before_checkout_shipping_fields() {
		
		echo '<div class="checkout_select_shipping_address_field"';
		
		if(!WC_Companies()->checkout()->get_shipping_addresses()) {
			
			echo 'style="display:none;"';
			
		}
		
		echo '>';
		
		woocommerce_form_field('shipping_address_id', array(
			'label' => __('Shipping Address', 'woocommerce'),
			'type' => 'select',
			'options' => array(0 => 'Select or Add new Address', -1 => 'Add new Address') + (WC_Companies()->checkout()->get_shipping_addresses() ? WC_Companies()->checkout()->get_shipping_addresses() : array()),
			'input_class' => array('country_select'),
			'default' => (WC_Companies()->checkout()->get_shipping_addresses() ? reset(array_keys(WC_Companies()->checkout()->get_shipping_addresses())) : -1),
			'custom_attributes' => array(
				'data-address_type' => 'shipping',	
			)
		));
		
		echo '</div>';
		
		echo '<div class="checkout_shipping_fields"';
		
		if(WC_Companies()->checkout()->get_shipping_addresses()) {
			
			echo 'style="display:none;"';
			
		}
		
		echo '>';
		
	}
	
	/**
	 * display closing div after checkout shipping fields
	 *
	 */
	public function after_checkout_shipping_fields() {
		
		echo '</div>';
		
	}
	
	/**
	 * hides billing company field on checkout
	 *
	 * @param array $fields Array of billing fields
	 */
	public function hide_billing_company_on_checkout($fields) {
			
		unset($fields['billing_company']);
		
		return $fields;
		
	}	
	
	/**
	 * hides shipping company field on checkout
	 *
	 * @param array $fields Array of shipping fields
	 */
	public function hide_shipping_company_on_checkout($fields) {
		
		unset($fields['shipping_company']);
		
		return $fields;
		
	}
	
	/**
	 * display title on edit address template
	 *
	 * @param array $title String of heading title
	 */
	public function my_account_edit_address_title($title) {
		
		$title = __('Edit Address', 'woocommerce-companies');
		
		return $title;
		
	}
	
	/**
	 * addresss on my acount area
	 *
	 * @param array $address array of address titles
	 */
	public function my_account_get_addresses($addresses) {
		
		$addresses['billing'] = __('Primary Billing Address', 'woocommerce-companies');
		
		$addresses['shipping'] = __('Primary Shipping Address', 'woocommerce-companies');
		
		return $addresses;
		
	}
	
	/**
	 * my addressses title on my companies area
	 *
	 * @param string $title String of heading title
	 */
	public function companies_view_addresses_title($title) {
		
		if( get_query_var('company_id') ) {
			
			$company = wc_get_company( get_query_var('company_id') );
			
			$title = __('Addresses for company: ', 'woocommerce-companies') . $company->get_title();
			
		}
		
		return $title;
		
	}
	
	/**
	 * multi select function
	 *
	 */
	public function multi_select_field($field, $key, $args, $value) {
	
		if ( ( ! empty( $args['clear'] ) ) ) {
			$after = '<div class="clear"></div>';
		} else {
			$after = '';
		}
	
		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required = ' <abbr class="required" title="' . esc_attr__( 'required', 'woocommerce'  ) . '">*</abbr>';
		} else {
			$required = '';
		}
	
		$args['maxlength'] = ( $args['maxlength'] ) ? 'maxlength="' . absint( $args['maxlength'] ) . '"' : '';
	
		if ( is_string( $args['label_class'] ) ) {
			$args['label_class'] = array( $args['label_class'] );
		}
	
		if ( is_null( $value ) ) {
			$value = $args['default'];
		}
	
		// Custom attribute handling
		$custom_attributes = array();
	
		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}
	
		if ( ! empty( $args['validate'] ) ) {
			foreach( $args['validate'] as $validate ) {
				$args['class'][] = 'validate-' . $validate;
			}
		}
		
		$options = $field = '';
	
		if ( ! empty( $args['options'] ) ) {
			foreach ( $args['options'] as $option_key => $option_text ) {
				if ( "" === $option_key ) {
					// If we have a blank option, select2 needs a placeholder
					if ( empty( $args['placeholder'] ) ) {
						$args['placeholder'] = $option_text ? $option_text : __( 'Choose an option', 'woocommerce' );
					}
					$custom_attributes[] = 'data-allow_clear="true"';
				}
				$options .= '<option value="' . esc_attr( $option_key ) . '" '. $this->bnm_selected( $value, $option_key, false ) . '>' . esc_attr( $option_text ) .'</option>';
			}
	
			$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $args['id'] ) . '_field">';
	
			if ( $args['label'] ) {
				$field .= '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) .'">' . $args['label']. $required . '</label>';
			}
	
			$field .= '<select name="' . esc_attr( $key ) . '[]" id="' . esc_attr( $args['id'] ) . '" class="select '.esc_attr( implode( ' ', $args['input_class'] ) ) .'" ' . implode( ' ', $custom_attributes ) . ' placeholder="' . esc_attr( $args['placeholder'] ) . '">
					' . $options . '
				</select>';
	
			if ( $args['description'] ) {
				$field .= '<span class="description">' . esc_attr( $args['description'] ) . '</span>';
			}
	
			$field .= '</p>' . $after;
		}
		
		return $field;
		
	}
	
	public function bnm_selected($haystack, $current, $echo) {
		
		if(is_array($haystack) && in_array($current, $haystack)) {
			
			$current = $haystack = 1;
			
		} else {
			
			$haystack = '';
			
		}
		
		return selected($haystack, $current, $echo);
		
	}

}