<div id="create-company-form" class="js-create-entity-form" style="display: none">

	<div class="wrap">
		
		<div id="ajax-response"></div>
		
		<p><?php _e('After creating a company, the company will be applied to the order.'); ?></p>
		
		<form method="post" class="js-create-entity-form" data-search-field="#_company_id">
    		
    		<div class="response"></div>

			<?php foreach($fields as $key => $field) : ?>
			
			    <?php woocommerce_form_field($key, $field); ?>
			
			<?php endforeach; ?>
	        
	        <input type="hidden" name="action" value="woocommerce_json_create_company" />
	        
	        <?php wp_nonce_field( 'create-company', 'security' ); ?>
    
    		<?php submit_button( __( 'Save Company' ), 'primary', 'create_company', true ); ?>

        </form>
        
    </div>
    
</div>