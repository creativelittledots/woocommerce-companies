jQuery(document).ready(function($) {
    
    $.blockUI.defaults.overlayCSS.cursor = 'default';
   
    $('select.js-address-select').change(function() {
        
        var select = $(this);
        
        var address_id = select.val();
        
        if(address_id > 0) {
            
            var parent = select.closest('.order_data_column');
            
            parent.block({
    			message: null,
    			overlayCSS: {
    				background: '#fff',
    				opacity: 0.6
    			}
    		});
            
            var args = {
                'action' : 'woocommerce_json_get_address',
                'address_id' : address_id
            };
            
            var type = select.data('address_type');
            
            $.post(ajaxurl, args, function(response) {
                
                if(response.address) {
                    
                    for(var field in response.address) {
                        
                        $('[name="_' + type + '_' + field + '"]').val(response.address[field]).trigger('change');
                        
                    }
                    
                }
                
            }, 'json').always(function(response) {
               
                console.log(response);
                
                parent.unblock();
                
            });
            
        }
       
    });
    
    $('input.wc-company-search, input.wc-customer-search').change(function() {
       
        var companySelect = $('input.wc-company-search');
        var customerSelect = $('input.wc-customer-search');
            
        $('.order_data_column').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		
		var args = {
            'action' : 'woocommerce_json_get_user_company_addresses',
            'company_id' : companySelect.val(),
            'user_id' : customerSelect.val(),
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

        $.post(ajaxurl, $(this).serializeObject(), function(response) {

            if(response.response == 'success') {

                // do some stuff with  response.user_id

            }else {

                // do some stuff with  response.message
            }

        }, 'json');

    });

});