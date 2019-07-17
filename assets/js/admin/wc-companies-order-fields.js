jQuery(document).ready(function($) {
    
    var defaults = {
		message: null,
		overlayCSS: {
			background: '#fff',
			opacity: 0.6,
			cursor: 'default'
		}
	};
   
    $('select.js-address-select').change(function() {
        
        var select = $(this);
        
        var address_id = select.val();
        
        if(address_id > 0) {
            
            var parent = select.closest('.order_data_column');
            
            parent.block(defaults);
            
            var args = {
                'action' : 'woocommerce_json_get_address',
                'address_id' : address_id,
                'security' : select.data('nonce')
            };
            
            var type = select.data('address_type');
            
            $.post(ajaxurl, args, function(response) {
	            
	            $('[name^="_' + type + '_"]').each(function() {
		           
		        	if( wc_companies_order_fields.ignore_fields.indexOf( $(this).attr( 'name' ) ) == -1 ) {
			        	
			        	$(this).val('');
			        	
		        	} 
		            
	            });
                
                if(response.address) {
                    
                    for(var field in response.address) {
	                    
	                    var name = '_' + type + '_' + field;
	                    
	                    if( wc_companies_order_fields.ignore_fields.indexOf( name ) == -1 ) {
		                    
		                    var value = response.address[field];
                        	
                        	if( field == 'state') {
	                        	
	                        	setTimeout(function() {
		                        	
		                        	var name = '_' + type + '_state';
		                        	
		                        	var value = response.address.state;
		                        	
		                        	var $field = $('[name="' + name + '"]');
									
									$field.closest('.edit_address').find('.js_field-country').data( 'woocommerce.stickState-' + response.address.country, value );
									
									$field.val(value);
                        	
									console.log($field, value)
                        	
									$field.trigger('change');
		                        	
	                        	}, 0);
	                        	
	                        	$field.closest('.edit_address').find('.js_field-country').data( 'woocommerce.stickState-' + response.address.country, value );
	                        	
                        	} else {
	                        	
	                        	var $field = $('[name="' + name + '"]');
	                        	
	                        	$field.val(value);
                        	
								$field.trigger('change');
	                        	
                        	}
                        	
                        }
                        
                    }
                    
                }
                
            }, 'json').always(function(response) {
                
                parent.unblock();
                
            });
            
        }
       
    });
    
    $('.wc-company-search, .wc-customer-search').change(function() {
       
        var companySelect = $('.wc-company-search');
        var customerSelect = $('.wc-customer-search');
            
        $('.order_data_column').block(defaults);
		
		var args = {
            'action' : 'woocommerce_json_get_user_company_addresses',
            'company_id' : companySelect.val(),
            'user_id' : customerSelect.val()
        };
        
        $.post(ajaxurl, args, function(response) {
                
            $('select.js-address-select').each(function() {
                
                optionsAsString = '';
                
                if(response.addresses) {
                    
                    var i = 1;
                    var openingString = '';
               
                    for(var address in response.addresses) {
                        
                        if( i == 1 ) {
                            
                            openingString += response.addresses[address].title;
                            
                        }
                        
                        optionsAsString += "<option value='" + response.addresses[address].id + "'>" + response.addresses[address].title + "</option>";
                        
                        i++;
                        
                    }
                    
                }
                
                $(this).find('option').remove().end().append($(optionsAsString)).closest('.form-field').find('span.select2-chosen').text(openingString);
                
            });
            
        }, 'json').always(function(response) {
           
            console.log(response);
            
            $('.order_data_column').unblock();
            
        });
        
    });

    $('.js-create-entity-form').submit(function(e) {
        
        e.preventDefault();
        
        var form = $(this),
            response = form.find('.response'),
            field = $(form.data('search-field'));
        
        response.html('');
        form.block(defaults);

        $.post(ajaxurl, $(this).serializeObject(), function(data) {
            
            response.append(data.message);

            if(data.response == 'success') {
                
                tb_remove();
                form.trigger("reset");
                
                if( field.is('select') ) {
	                
	                var option = $('<option>');
	                
	                option.attr('value', data.object_id).text(data.object_title);
	                
	                field.prepend(option);
	                
                }

                field.val(data.object_id).trigger('change').siblings('.select2-container').find('.select2-selection__placeholder').text(data.object_title);

            }

        }, 'json').always(function(data) {
            
            console.log(data);
            
            form.unblock();
            
        });

    });

});

function onCountryChange(e, country, parent) {
	
	let $country = jQuery(parent).find( '.js_field-country' );
	
	jQuery(parent).find('input.js_field-state').val($country.data( 'woocommerce.stickState-' + $country.val() ));

}

jQuery( document.body ).on( 'country-change.woocommerce', onCountryChange );