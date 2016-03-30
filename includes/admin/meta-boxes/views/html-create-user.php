<div id="create-user-form" class="js-create-entity-form" style="display: none">

	<div class="wrap">
		
		<p><?php _e('After creating a customer, the customer will be applied to the order.'); ?></p>
		
		<form method="post" class="js-create-entity-form" data-search-field="#customer_user">
    		
    		<div class="response"></div>
    			
			<?php foreach($fields as $key => $field) : ?>
			
			    <?php woocommerce_form_field($key, $field); ?>
			
			<?php endforeach; ?>
	        
	        <input type="hidden" name="action" value="woocommerce_json_create_user" />
	        
	        <?php wp_nonce_field( 'create-user', 'security' ); ?>
    
    		<?php submit_button( __( 'Save User' ), 'primary', 'create_user', true ); ?>

        </form>
        
    </div>
    
</div>