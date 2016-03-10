<?php

if ( ! defined( 'ABSPATH' ) ) {
    
	exit; // Exit if accessed directly
	
}

class WC_Companies_Admin_Order_Fields {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
    	
    	add_filter( 'woocommerce_admin_shipping_fields', array($this, 'remove_company_field') );
    	add_filter( 'woocommerce_admin_billing_fields', array($this, 'remove_company_field') );
		
		add_filter( 'woocommerce_admin_shipping_fields', array($this, 'add_shipping_address_field') );
		add_filter( 'woocommerce_admin_billing_fields', array($this, 'add_billing_address_field') );
		
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'add_create_customer_button' ), 30 );
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'add_company_field' ), 40 );
		
		add_action( 'save_post_shop_order', array( $this, 'maybe_save_company_to_order' ), 20 );
		add_action( 'save_post_shop_order', array( $this, 'maybe_create_addresses' ), 30 );
		add_action( 'save_post_shop_order', array( $this, 'maybe_save_company_to_customer' ), 40 );
		
		add_action( 'wp_ajax_get_address', array($this, 'ajax_get_address') );
		add_action( 'wp_ajax_createuser', array($this, 'ajax_createuser') );
		add_action( 'wp_ajax_get_user_company_addresses', array($this, 'ajax_get_user_company_addresses') );
		add_action( 'admin_enqueue_scripts', array($this, 'maybe_enqueue_order_fields_script') );
			
	}
	
	public function remove_company_field($fields) {
    	
    	unset($fields['company']);
    	
    	return $fields;
    	
	}
	
	public function add_shipping_address_field($fields) {
		
		return $this->add_address_field('shipping') + $fields;

		
	}
	
	public function add_billing_address_field($fields) {
		
		return $this->add_address_field() + $fields;
		
	}

	private function add_address_field($type = 'billing') {
    	
    	$addresses = array(
			0 => 'None',
		);
		
		global $post;
		
		if( $order = wc_get_order( $post ) ) {
    	
        	$addressesFound = array();
        		
    		if( $order->get_user_id() ) {
    		
        		$addressesFound = $addressesFound + wc_get_user_all_addresses( $order->get_user_id() );
        		
            }
            
            if( $order->company_id ) {
                
                $addressesFound = $addressesFound + wc_get_company_addresses( $order->company_id );
                
            }
        	
        	$addressesFound = array_unique( $addressesFound );
    		
    		foreach($addressesFound as $address) {
        		
        		$addresses[$address->id] = $address->get_title();
        		
    		}
    		
        }
		
		return array($type . '_address_id' => array(
    		'id' => '_' . $type . '_address_id',
			'label' => __( 'Address', 'woocommerce' ),
			'class' => 'wc-enhanced-select js-address-select',
			'wrapper_class' => 'form-field-wide',
			'custom_attributes' => array(
    			'data-address_type' => $type,
			),
			'type' => 'select',
			'description' => 'Please select ' . $type . ' address',
			'options' => $addresses,
		));
		
	}
	
	public function add_create_customer_button() {
		add_thickbox();
		echo $this->populate_registration_form();
		echo '<p class="form-field"><a href="#TB_inline?width=600&height=550&inlineId=modal-registration-form" class="thickbox js-customer-button button">'.__('Create a Customer', 'wo$ocommerce').'</a></p>';
	}

	public function add_company_field() {

		echo woocommerce_wp_select( array(
    		'id' => '_company_id',
			'label' => __( 'Company' ),
			'class' => 'wc-enhanced-select js-company-select',
			'wrapper_class' => 'form-field-wide',
			'type' => 'select',
			'description' => 'Please select company',
			'options' => $this->get_companies(),
		));
		
		
		echo '<p class="form-field"><a href="#" class="js-company-button button">'.__('Create a Company', 'woocommerce').'</a></p>';
	}
	
	private function get_companies() {
    	
		$companies = array(
    		0 => 'None',
		);

		foreach(wc_get_companies(array('showposts' => 50)) as $company) {

			$companies[$company->id] = ($company->internal_company_id ? $company->internal_company_id . ' - ' : '') . $company->title;

		}

		return $companies;
		
	}

	public function maybe_save_company_to_order( $post_id ) {
			
		if( isset( $_POST['_company_id'] ) && $_POST['_company_id'] ) {
    		
    		if( $company = wc_get_company( $_POST['_company_id'] ) ) {
    		
    		    update_post_meta($post_id, '_company_id', $_POST['_company_id']);
    		    
    		    update_post_meta($post_id, '_billing_company', $company->get_title());
    		    update_post_meta($post_id, '_shipping_company', $company->get_title());
    		    
            }
    		
		}
		
	}
	
	public function maybe_create_addresses( $post_id ) {
    	
    	if( $order = wc_get_order( $post_id ) ) {
        	
        	if( $billing_address = $order->get_address() && ! empty( $billing_address['address_1'] ) ) {
            	
            	if( $billing_address_id = wc_create_address( $billing_address ) ) {
                	
                	if( $order->company_id && $company = wc_get_company( $order->company_id ) ) {
                    	
                    	wc_add_company_address( $company->id, $billing_address_id );
                    	
                	}
                	
                	if( $user_id = $order->get_user_id() ) {
        		
                		wc_add_user_address( $user_id, $billing_address_id );
                		
            		}
                	
            	}
            	
        	}
        	
        	if( $shipping_address = $order->get_address( 'shipping' ) && ! empty( $shipping_address['address_1'] ) ) {
            	
            	if( $shipping_address_id = wc_create_address( $shipping_address ) ) {
                	
                	if( $order->company_id && $company = wc_get_company( $order->company_id ) ) {
                    	
                    	wc_add_company_address( $company->id, $shipping_address_id, 'shipping' );
                    	
                	}
                	
                	if( $user_id = $order->get_user_id() ) {
        		
                		wc_add_user_address( $user_id, $shipping_address_id, 'shipping' );
                		
            		}
                	
            	}
            	
        	}
        	
    	}
    	
	}
	
	public function maybe_save_company_to_customer( $post_id ) {
    	
    	if( $order = wc_get_order( $post_id ) ) {
        	
        	if( $order->company_id && $company = wc_get_company( $order->company_id ) && $user_id = $order->get_user_id() ) {
            	
                wc_add_user_company( $user_id, $company->id );
            	
            }
        	
        }
    	
	}
	
	public function ajax_get_address() {
    	
    	$reponse = array(
        	'request' => $_POST
    	);
    	
    	if( isset( $_POST['address_id'] ) && ! empty( $_POST['address_id'] ) ) {
        	
        	if( $address = wc_get_address($_POST['address_id']) ) {
            	
            	$reponse['address'] = $address;
            	
        	} 
        	 	
    	}
    	
    	echo json_encode($reponse);
    	
    	exit();
    	
	}
	
	public function ajax_get_user_company_addresses() {
    	
        $response = array(
        	'request' => $_POST,
    	);
    	
    	$addresses = array();
    	
    	if( isset( $_POST['user_id'] ) && ! empty( $_POST['user_id'] )  ) {
        	
        	$addresses = $addresses + wc_get_user_all_addresses( $_POST['user_id'] );
        	
    	}
    	
    	if( isset( $_POST['company_id'] ) && ! empty( $_POST['company_id'] ) ) {
        	
        	$addresses = $addresses + wc_get_company_addresses( $_POST['company_id'] );
        	 	
    	}
    	
    	$addresses = array_unique($addresses);
    	
    	array_unshift($addresses, (object) array(
        	    'id' => 0,
        	    'title' => 'None'
    	    )
        );
        
        $response['addresses'] = $addresses;
    	
    	echo json_encode($response);
    	
    	exit();

    	
	}
	
	public function maybe_enqueue_order_fields_script() {
    	
    	$screen = get_current_screen();
    	
    	if( $screen->id === 'shop_order' ) {
        	
        	wp_enqueue_script( 'order-fields-js', WC_Companies()->plugin_url() . '/assets/js/admin/wc-companies-order-fields.js', array('jquery'), '1.0.0', true );


    	}
    	
	}

	public function populate_registration_form()
	{
		wp_enqueue_script('wp-ajax-response');
		wp_enqueue_script( 'user-profile' );
		?>
		<div id="modal-registration-form" style="display: none">
			<p>
				<div class="wrap">
					<div id="ajax-response"></div>
					<p><?php _e('Create a brand new user and add them to this site.'); ?></p>
					<form action=""></form>
					<form method="post" name="createuser" id="createuser" class="validate" novalidate="novalidate" <?php	do_action( 'user_new_form_tag' ); ?>>
						<input name="action" type="hidden" value="createuser" />
						<?php wp_nonce_field( 'create-user', '_wpnonce_create-user' ); ?>

						<table class="form-table">
							<tr class="form-field form-required">
								<th scope="row"><label for="user_login"><?php _e('Username'); ?> <span class="description"><?php _e('(required)'); ?></span></label></th>
								<td><input name="user_login" type="text" id="user_login"  aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60" /></td>
							</tr>
							<tr class="form-field form-required">
								<th scope="row"><label for="email"><?php _e('Email'); ?> <span class="description"><?php _e('(required)'); ?></span></label></th>
								<td><input name="email" type="email" id="email" /></td>
							</tr>
						<tr class="form-field">
							<th scope="row"><label for="first_name"><?php _e('First Name') ?> </label></th>
							<td><input name="first_name" type="text" id="first_name" /></td>
						</tr>
						<tr class="form-field">
							<th scope="row"><label for="last_name"><?php _e('Last Name') ?> </label></th>
							<td><input name="last_name" type="text" id="last_name" /></td>
						</tr>
						<tr class="form-field">
							<th scope="row"><label for="url"><?php _e('Website') ?></label></th>
							<td><input name="url" type="url" id="url" class="code" /></td>
						</tr>
						<tr class="form-field form-required user-pass1-wrap">
							<th scope="row">
								<label for="pass1">
									<?php _e( 'Password' ); ?>
									<span class="description hide-if-js"><?php _e( '(required)' ); ?></span>
								</label>
							</th>
							<td>
								<input class="hidden" value=" " /><!-- #24364 workaround -->
								<button type="button" class="button button-secondary wp-generate-pw hide-if-no-js"><?php _e( 'Show password' ); ?></button>
								<div class="wp-pwd hide-if-js">
									<?php $initial_password = wp_generate_password( 24 ); ?>
									<span class="password-input-wrapper">
										<input type="password" name="pass1" id="pass1" class="regular-text" autocomplete="off" data-reveal="1" data-pw="<?php echo esc_attr( $initial_password ); ?>" aria-describedby="pass-strength-result" />
									</span>
									<button type="button" class="button button-secondary wp-hide-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Hide password' ); ?>">
										<span class="dashicons dashicons-hidden"></span>
										<span class="text"><?php _e( 'Hide' ); ?></span>
									</button>
									<button type="button" class="button button-secondary wp-cancel-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Cancel password change' ); ?>">
										<span class="text"><?php _e( 'Cancel' ); ?></span>
									</button>
									<div style="display:none" id="pass-strength-result" aria-live="polite"></div>
								</div>
							</td>
						</tr>
						<tr class="form-field form-required user-pass2-wrap hide-if-js">
							<th scope="row"><label for="pass2"><?php _e( 'Repeat Password' ); ?> <span class="description"><?php _e( '(required)' ); ?></span></label></th>
							<td>
								<input name="pass2" type="password" id="pass2" autocomplete="off" />
							</td>
						</tr>
						<tr class="pw-weak">
							<th><?php _e( 'Confirm Password' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="pw_weak" class="pw-checkbox" />
									<?php _e( 'Confirm use of weak password' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Send User Notification' ) ?></th>
							<td><label for="send_user_notification"><input type="checkbox" name="send_user_notification" id="send_user_notification" value="1" /> <?php _e( 'Send the new user an email about their account.' ); ?></label></td>
						</tr>
					<tr class="form-field">
						<th scope="row"><label for="role"><?php _e('Role'); ?></label></th>
						<td><select name="role" id="role">
								<?php
								$new_user_role = get_option('default_role');
								wp_dropdown_roles($new_user_role);
								?>
							</select>
						</td>
					</tr>
				</table>

				<?php
				/** This action is documented in wp-admin/user-new.php */
				do_action( 'user_new_form', 'add-new-user' );
				?>

				<?php submit_button( __( 'Add New User' ), 'primary', 'createuser', true, array( 'id' => 'createusersub' ) ); ?>

			</form>
		</div>
		</p>
		</div>
		<?php
	}

	public function ajax_createuser()
	{
		$response = [
			'response' => 'error',
			'message' => 'User Already Exists!'
		];
		$user_id = username_exists( $_POST['user_login'] );
		if ( !$user_id and email_exists($_POST['email']) == false ) {
			$password = (isset($_POST['pass1'])) ? $_POST['pass1'] : wp_generate_password( $length=12, $include_standard_special_chars=false );

			$user_id = wp_create_user( $_POST['user_login'], $password, $_POST['email'] );

			$user = new WP_User( $user_id );

			$role = (isset($_POST['role'])) ? $_POST['role'] : 'contributor' ;

			$user->set_role( $role );

			// Email the user
			wp_mail( $_POST['email'], 'Welcome!', 'Your Password: ' . $password );

			$response = [
				'response' => 'success',
				'user_id' => $user_id
			];
		}

		echo json_encode($response);
		die();
	}
}


return new WC_Companies_Admin_Order_Fields();
