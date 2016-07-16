jQuery(document).ready(function($) {
	
	$.blockUI.defaults.overlayCSS.cursor = 'default';
	
	if ( ! Object.keys ) {
		
	    Object.keys = function (obj) {
	        var arr = [],
	            key;
	        for (key in obj) {
	            if (obj.hasOwnProperty(key)) {
	                arr.push(key);
	            }
	        }
	        return arr;
	    };
	}
	
	if( $('select.company_select, select.address_select, select.country_select').length && $().select2 ) {
		
		$('select.company_select, select.address_select, select.country_select').select2();
	
	}
	
	$('#billing_address_id, #shipping_address_id').bind( 'wc_companies_update_addresses', function() {
		
		var field = $(this),
			address_type = field.data('address_type'),
			container = $('.checkout_' +  address_type + '_fields');
			
		container.block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
			
		$.post(woocommerce_params.ajax_url, {
			security: wc_companies_checkout_params.get_addresses_nonce,
			action : 'woocommerce_json_get_addresses',
			address_type : address_type,
			checkout_type : $('input[name="checkout_type"]:checked').val(),
			company_id : $('#company_id').val(),
		}, function(response) {
			
			if(response.result === 'success') {
				
				field.find('option').each(function() {
					
					if($(this).val() > 0) {
						
						$(this).remove();
						
					}

				});
				
				if( Object.keys(response.addresses).length ) {
					
					field.closest('div').show(300);
					
					for(var address in response.addresses) {
				
						var address = response.addresses[address];
						
						field.append('<option value="' + address.id + '">' + address.title + '</option>');
						
					}
					
					field.trigger('wc_companies_change_address', [response.addresses[0], address_type]);
					
				} else {
					
					field.closest('div').hide(300);
					
					$('.checkout_' +  address_type + '_fields').find('input, select, textarea').val('');
					
				}
				
				field.trigger( 'wc_companies_updated_addresses', [response.addresses, address_type] );
				
			}
			
		}, 'json').always(function() {
			
			container.unblock();
			
			if( field.is('#billing_address_id') ) {
			
				$('#shipping_address_id').trigger('wc_companies_update_addresses');
				
			}
			
		});
		
	}).change(function() {
		
		var field = $(this),
			address_type = field.data('address_type')
			container = $('.checkout_' +  address_type + '_fields');
		
		if( field.val() > 0 ) {
			
			container.block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
		
			$.post(woocommerce_params.ajax_url, {
				security: wc_companies_checkout_params.get_address_nonce,
				action : 'woocommerce_json_get_address',
				address_id : field.val(),
			}, function(response) {
				
				if(response.address) {
					
					field.trigger( 'wc_companies_changed_address', [response.address, address_type] );
					
				}	
				
			}, 'json').always(function() {
				
				container.unblock();
				
			});
			
		} else {
			
			container.find('input, select, textarea').val('');
			
		}
		
	}).bind( 'wc_companies_change_address', function(e, address, address_type) {
		
		var field = $(this),
			container = $('.checkout_' +  address_type + '_fields');
			
		field.val(address.id);
		
		container.find('input, select, textarea').each(function() {
						
			if( $(this).attr('name') ) {
			
				var property = $(this).attr('name').replace(address_type + '_', '');
				
				if( address[property] ) {
					
					$(this).val( address[property] );
					
				}
			
			}
			
		});
		
		field.trigger('wc_companies_changed_address', [address, address_type]);
		
	});
	
	$('#company_id').change(function() {
		
		var field = $(this),
			container = $('.checkout_company_fields');
			
		if( field.val() > 0 ) {
			
			container.block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			
			$.post(woocommerce_params.ajax_url, {
				security: wc_companies_checkout_params.get_company_nonce,
				action : 'woocommerce_json_get_company',
				company_id : field.val(),
			}, function(response) {
				
				if(response.company) {
					
					container.find('input, select, textarea').each(function() {
						
						if( $(this).attr('name') ) {
							
							var property = $(this).attr('name').replace('company_', '');
							
							if( response.company[property] ) {
								
								$(this).val( response.company[property] );
								
							}
						
						}
						
					});
					
				}
				
			}, 'json').always(function() {
			
				container.unblock();
				
			});
			
		} else {
			
			container.find('input, select, textarea').val('');
			
		}
		
		$('#billing_address_id').trigger('wc_companies_update_addresses');
		
	});
	
	$('input[name="checkout_type"]').change(function() {
		
		if( $('input[name="checkout_type"]:checked').val() === 'company' ) {
			
			$('.checkout_company_fields').show(300);
			
			if( $('select.company_select, select.address_select, select.country_select').length && $().select2 ) {
		
				$('select.company_select, select.address_select, select.country_select').select2();
			
			}
			
		}
		
		else {
			
			$('#company_id').val(-1);
			
			$('.checkout_company_fields').hide(300);
			
		}
		
		$('#company_id').trigger('change');
		
	}).trigger( 'change' ); // dirt
	
});