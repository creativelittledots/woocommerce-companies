jQuery(document).ready(function($) {
	
	$('<option>').val('merge').text('Merge').appendTo("select[name='action'], select[name='action2']");
	
	$('#doaction').click(function(e) {
		
		if( $(this).prev().val() == 'merge' ) {
			
			e.preventDefault();
			
			var ids = [];
			
			$('[name="post[]"]:checked').each(function() {
				
				ids.push($(this).val());
				
			});
			
			$('.wc-companies-merge-fields').find('select:not(.country_to_state), input').val('').find('option[val!="0"]').remove();
			$('.wc-companies-merge-fields').find('.wc-advanced-search').select2('data', []);
			
			if( ids ) {
				
				$('.wc-companies-merge-fields').addClass('wc-companies-merge-fields--visible');
				
				var data = {
					action: 'woocommerce_json_get_merge_field_values',
					post_type: typenow,
					ids: ids	
				};
				
				$.post(ajaxurl, data, function(response) {
					
					for(var key in response.fields) {
						
						var field = response.fields[key];
						
						var el = $('.wc-companies-merge-fields').find('[name="wc-companies-merge[' + key + ']"]');

						if( el.is('select') && ! el.hasClass('.country_to_state') ) {
								
							for(var i in field.options) {
								
								var option = field.options[i];
								
								$('<option>').val(option.id).text(option.text).appendTo(el);
								
							}
							
						}
						
						el.val(field.value).trigger('change');
						
						if( el.hasClass('wc-advanced-search') ) {
							
							el.select2('data', field.options);
							
						}
						
					}
					
				}, 'json').always(function(response) {
					
					console.log(response);
					
				});
				
			}
			
		}
		
	});
	
	$('.wc-companies-merge-fields').click(function(e) {
		
		if( $( e.target ).hasClass('wc-companies-merge-fields--visible') ) {
			
			$(this).removeClass('wc-companies-merge-fields--visible');
			
			$(this).find('select:not(.country_to_state), input').val('').find('option[val!="0"]').remove();
			$(this).find('.wc-advanced-search').select2('data', []);
			
			$('.select2-drop').hide();
			
		}
		
	});

});