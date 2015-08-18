<table cellspacing="10" width="100%">

	<?php foreach ( $fields as $key => $field ) : $meta_key = preg_replace('/[^A-Za-z0-9_\-]/', '', $key); ?>
	
		<?php woocommerce_form_field($key, $field, get_post_meta($post->ID, '_' . $meta_key, true)); ?>
			
	<?php endforeach; ?>

</table>

<?php wp_nonce_field('woocommerce_save_data', 'woocommerce_meta_nonce'); ?>

<script type="text/javascript">
	
	jQuery(document).ready(function($) {
		
		$('select.chosen').chosen();
		
	});
	
</script>