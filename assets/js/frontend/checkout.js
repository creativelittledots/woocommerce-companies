jQuery(document).ready(function($) {
	
	if (!Object.keys) {
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
	
	$('#billing_address_id').change(function() {
		
		if($(this).val() == -1) {
			
			$('.checkout_billing_fields').slideDown(300);
			
		}
		
		else {
			
			$('.checkout_billing_fields').slideUp(300);
			
		}
		
	});
	
	$('#shipping_address_id').change(function() {
		
		if($(this).val() == -1) {
			
			$('.checkout_shipping_fields').slideDown(300);
			
		}
		
		else {
			
			$('.checkout_shipping_fields').slideUp(300);
			
		}
		
	});
	
	if($('select.company_select, select.address_select, select.country_select').length)
		$('select.company_select, select.address_select, select.country_select').select2();
	
	$('input[name="checkout_type"]').change(function() {
		
		var val = $(this).val();
		
		if(val == 'company') {
			
			$('.checkout_company_fields').slideDown(300);
			
			if($('select.address_select').length)
				$('select.address_select').select2();
			
		}
		
		else {
			
			$('.checkout_company_fields').slideUp(300, function() {
				
				$('#company_id').val(0).change();
				
			});
			
		}
		
		$('#billing_address_id, #shipping_address_id').trigger('updateAddresses');
		
	});
	
	$('#company_id').change(function() {
		
		if($(this).val() == -1) {
			
			$('.checkout_add_company_fields').slideDown(300);
			
		}
		
		else if($(this).find('option').length < 3) {
			
			$('.checkout_add_company_fields').slideDown(300);
			
		}
		
		else {
			
			$('.checkout_add_company_fields').slideUp(300);
			
		}
		
		$('#billing_address_id, #shipping_address_id').trigger('updateAddresses');
		
	});
	
	$('#billing_address_id, #shipping_address_id').bind('updateAddresses', function() {
		
		var field = $(this);
		
		if($('input[name="checkout_type"]:checked').length) {
			
			var data = {
				action : 'woocommerce_companies_get_addresses',
				address_type : $(this).data('address_type'),
				checkout_type : $('input[name="checkout_type"]:checked').val(),
				company_id : $('#company_id').val(),
			}
			
			$.post(woocommerce_params.ajax_url, data, function(response) {
				
				if(response.result == 'success') {
					
					field.find('option').each(function() {
						
						if($(this).val() > 0)
							$(this).remove();
						
					});
					
					if(Object.keys(response.addresses).length) {
						
						field.closest('div').slideDown(300);
						
						for(var address_id in response.addresses) {
					
							address = response.addresses[address_id];
							
							field.append('<option value="' + address_id + '">' + address + '</option>');
							
						}
						
						field.val(Object.keys(response.addresses)[0]).change();
						
					} else {
						
						field.closest('div').slideUp(300);
						
						field.val(-1).change();
						
					}
					
				}
				
			}, 'JSON');
		
		}
		
	});
	
});