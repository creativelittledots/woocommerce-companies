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
		
		add_filter( 'woocommerce_customer_meta_fields', array($this, 'customer_meta_fields')  );
		
		add_filter( 'woocommerce_companies_address_customer_meta_fields', array($this, 'add_customer_companies_meta_fields') );
		
		add_action( 'personal_options_update', array( $this, 'save_customer_address_meta_fields' ), 20 );
		add_action( 'edit_user_profile_update', array( $this, 'save_customer_address_meta_fields' ), 20 );
		
		add_action( 'show_user_profile', array( $this, 'add_customer_meta_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'add_customer_meta_fields' ) );
		
		add_filter( 'manage_users_columns', array($this, 'user_columns') );
		add_action( 'manage_users_custom_column',  array($this, 'render_user_columns'), 10, 3);
			
	}
	
	public function customer_meta_fields() {
		
		return array();
		
	}
	
	/**
	 * Show Address Fields on edit user pages.
	 *
	 * @param WP_User $user
	 */
	public function add_customer_meta_fields( $user ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$show_fields = $this->get_customer_meta_fields();

		foreach ( $show_fields as $fieldset ) :
			?>
			<h3><?php echo $fieldset['title']; ?></h3>
			<table class="form-table">
				<?php
				foreach ( $fieldset['fields'] as $key => $field ) :
					?>
					<tr>
						<th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
						<td>
							<?php 
    							
                                unset($field['label']);
    							
    							woocommerce_form_field($key, $field); 
    							
    				        ?>
						</td>
					</tr>
					<?php
				endforeach;
				?>
			</table>
			<?php
		endforeach;
	}
	
	/**
	 * Replace Address Fields on edit user pages
	 *
	 * @param array $fieldsets Fieldsets passed into hook 'woocommerce_customer_meta_fields'
	 */
	public function get_customer_meta_fields() {
    	
    	global $user_id;
    	
    	$billing_addresses = $shipping_addresses = array();
    	$primary_billing_address = $primary_shipping_address = null;
    	
        if($user_id) {
            
            $user = get_user_by('id', $user_id);
            
            $primary_billing_address = $user->primary_billing_address;
            $primary_shipping_address = $user->primary_shipping_address;
		
    		foreach(wc_get_user_addresses($user_id, 'billing') as $address) {
    			
    			$billing_addresses[$address->id] = $address->get_title();
    			
    		}
    		
    		foreach(wc_get_user_addresses($user_id, 'shipping') as $address) {
    			
    			$shipping_addresses[$address->id] = $address->get_title();
    			
    		}
            
        }		
			
		$fieldsets = apply_filters('woocommerce_companies_address_customer_meta_fields', array(
			'billing' => array(
				'title' => __( 'Customer Billing Address', 'woocommerce' ),
				'fields' => array(
					'primary_billing_address' => array(
						'label' => __( 'Primary Billing Address', 'woocommerce' ),
						'type' => 'select',
						'description' => 'Please select primary billing address',
						'options' => array(0 => 'None') + $billing_addresses,
						'default' => $primary_billing_address
					),
					'billing_addresses' => array(
						'label' => __( 'Billing Addresses', 'woocommerce' ),
						'input_class' => array('wc-advanced-search'),
						'type' => 'advanced_search',
						'multiple' => true,
						'custom_attributes' => array(
            				'data-multiple' => true,
            				'data-selected' => json_encode($billing_addresses),
                			'data-action' => 'woocommerce_json_search_addresses',
                            'data-nonce' => wp_create_nonce( 'search-addresses' ),
            			),
						'description' => 'Please select billing addresses',
						'default' => implode(',', array_keys($billing_addresses)),
					),
				)
			),
			'shipping' => array(
				'title' => __( 'Customer Shipping Address', 'woocommerce' ),
				'fields' => array(
					'primary_shipping_address' => array(
						'label' => __( 'Primary Shipping Address', 'woocommerce' ),
						'type' => 'select',
						'description' => 'Please select primary shipping address',
						'options' => array(0 => 'None') + $shipping_addresses,
						'default' => $primary_shipping_address
					),
					'shipping_addresses' => array(
						'label' => __( 'Shipping Addresses', 'woocommerce' ),
						'input_class' => array('wc-advanced-search'),
						'type' => 'advanced_search',
						'multiple' => true,
						'custom_attributes' => array(
            				'data-multiple' => true,
            				'data-selected' => json_encode($shipping_addresses),
                			'data-action' => 'woocommerce_json_search_addresses',
                            'data-nonce' => wp_create_nonce( 'search-addresses' ),
            			),
						'description' => 'Please select shipping addresses',
						'default' => implode(',', array_keys($shipping_addresses)),
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
		
		$customer_companies = array();
		
		if($user_id) {
		
    		foreach(wc_get_user_companies($user_id) as $company) {
    			
    			$customer_companies[$company->id] = $company->get_title();
    			
    		}
    		
        }
        
        $primary_company = get_user_meta($user_id, 'primary_company', true);
			
		$fieldsets['companies'] = array(
			'title' => __( 'Customer Companies', 'woocommerce' ),
			'fields' => array(
				'primary_company' => array(
					'label' => __( 'Primary Company', 'woocommerce' ),
					'class' => array('company'),
					'type' => 'select',
					'description' => 'Please select primary company',
					'options' => array(0 => 'None') + $customer_companies,
					'default' => $primary_company
				),
				'companies' => array(
					'label' => __( 'Companies', 'woocommerce' ),
					'input_class' => array('wc-advanced-search'),
					'type' => 'advanced_search',
					'multiple' => true,
					'custom_attributes' => array(
            				'data-multiple' => true,
            				'data-selected' => json_encode($customer_companies),
                			'data-action' => 'woocommerce_json_search_companies',
                            'data-nonce' => wp_create_nonce( 'search-companies' ),
            			),
					'description' => 'Please select companies',
					'default' => $customer_companies ? implode(',', array_keys($customer_companies)) : '',
				)
			)
		);
		
		return $fieldsets;
		
	}
	
	/**
	 * Save Address Fields on edit user pages
	 *
	 * @param mixed $user_id User ID of the user being saved
	 */
	public function save_customer_address_meta_fields( $user_id ) {
			
		$save_fields = $this->get_customer_meta_fields();

		foreach( $save_fields as $fieldset ) {

			foreach( $fieldset['fields'] as $key => $field ) {

				if ( isset( $_POST[ $key ] ) ) {
    				
    				$value = isset($field['multiple']) && $field['multiple'] ? explode(',', $_POST[ $key ] ) : $_POST[ $key ];
					
					update_user_meta( $user_id, $key, $value );
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
