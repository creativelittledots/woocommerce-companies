<?php
/**
 * Contains the email functions for WooCommerce Companies which alter the content on emails.
 *
 * @class 		WC_Companies_Emails
 * @version		1.0.0
 * @package		WooCommerce Companies/Classes
 * @category	Class
 * @author 		Creatove Little Dots
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Companies_Emails' ) ) :

    class WC_Companies_Emails {
    	
    	public function __construct() {
	    	
	    	add_action( 'woocommerce_email', array( $this, 'remove_customer_details' ) );
	    	add_action( 'woocommerce_email_customer_details', array( $this, 'customer_details' ), 10, 3 );
    		
    	}
    	
    	public function remove_customer_details($email) {
        	
        	remove_action( 'woocommerce_email_customer_details', array( $email, 'customer_details' ) );
        	
    	}
    	
    	public function customer_details($order, $sent_to_admin = false, $plain_text = false) {
	    	
	    	$customer_fields = $company_fields = array();

			if ( $order->customer_note ) {
				
				$customer_fields['customer_note'] = array(
					'label' => __( 'Note', 'woocommerce' ),
					'value' => wptexturize( $order->customer_note )
				);
				
			}
			
			if ( $order->billing_first_name ) {
				
				$customer_fields['billing_first_name'] = array(
					'label' => __( 'Name', 'woocommerce' ),
					'value' => wptexturize( $order->billing_first_name . ( $order->billing_last_name ? ' ' . $order->billing_last_name : '' ) )
				);
				
		    }
			
			if ( $order->billing_email ) {
				
				$customer_fields['billing_email'] = array(
					'label' => __( 'Email', 'woocommerce' ),
					'value' => wptexturize( $order->billing_email )
				);
				
		    }
	
		    if ( $order->billing_phone ) {
			    
				$customer_fields['billing_phone'] = array(
					'label' => __( 'Tel', 'woocommerce' ),
					'value' => wptexturize( $order->billing_phone )
				);
				
		    }
		    
		    if( $order->company_id ) {
			    
			    $company = wc_get_company($order->company_id);
			    
			    $company_fields['company_name'] = array(
					'label' => __( 'Name', 'woocommerce' ),
					'value' => wptexturize( $company->get_title() )
				);
				
				if( $company->accounting_reference ) {
    				
    				$company_fields['accounting_reference'] = array(
    					'label' => __( 'Company Ref', 'woocommerce' ),
    					'value' => wptexturize( $company->accounting_reference )
    				);
    				
                }
    			
    		}
	
			$customer_fields = array_filter( apply_filters( 'woocommerce_email_customer_details_fields', $customer_fields, $sent_to_admin, $order ), array( WC_Emails::instance(), 'customer_detail_field_is_valid' ) );
			
			$company_fields = array_filter( apply_filters( 'woocommerce_email_company_details_fields', $company_fields, $sent_to_admin, $order ), array( WC_Emails::instance(), 'customer_detail_field_is_valid' ) );
	
			if ( $plain_text ) {
				
				wc_get_template( 'emails/plain/email-customer-details.php', compact('customer_fields', 'company_fields'), '', WC_Companies()->plugin_path() . '/templates/' );
				
			} else {
				
				wc_get_template( 'emails/email-customer-details.php', compact('customer_fields', 'company_fields'), '', WC_Companies()->plugin_path() . '/templates/' );
				
			}
    		
    	}
    	
    }

endif;

return new WC_Companies_Emails();