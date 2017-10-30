<div class="wc-companies-merge-fields">
	
	<div class="wc-companies-merge-fields__inner">
		
		<h2>Merge</h2>
		
		<p>Fill in the fields to merge these records</p>
	
		<?php foreach($fields as $key => $field) : ?>
		
			<div>
		
				<?php woocommerce_form_field("wc-companies-merge[$key]", $field); ?>
				
			</div>
			
		<?php endforeach; ?>
		
		<div>
		
			<p>
			
				<button class="button button-primary">Merge</button>
				
			</p>
			
		</div>
			
	</div>
	
</div>