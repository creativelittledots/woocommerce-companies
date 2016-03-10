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
                'action' : 'get_address',
                'address_id' : address_id
            };
            
            var type = select.data('address_type');
            
            $.post(ajaxurl, args, function(response) {
                
                if(response.address) {
                    
                    for(var field in response.address) {
                        
                        $('[name="_' + type + '_' + field + '"]').val(response.address[field]);
                        
                    }
                    
                }
                
                parent.unblock();
                
            }, 'json').always(function(response) {
               
                console.log(response);
                
            });
            
        }
       
    });
    
    $('select.js-company-select, input.wc-customer-search').change(function() {
       
        var companySelect = $('select.js-company-select');
        var customerSelect = $('input.wc-customer-search');
            
        $('.order_data_column').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		
		var args = {
            'action' : 'get_user_company_addresses',
            'company_id' : companySelect.val(),
            'user_id' : customerSelect.val(),
        };
        
        $.post(ajaxurl, args, function(response) {
                
            $('select.js-address-select').each(function() {
                
                optionsAsString = '';
                
                if(response.addresses) {
               
                    for(var address in response.addresses) {
                        
                        optionsAsString += "<option value='" + response.addresses[address].id + "'>" + response.addresses[address].title + "</option>";
                        
                    }
                    
                }
                
                $(this).find('option').remove().end().append($(optionsAsString));
                
            });
            
            $('.order_data_column').unblock();
            
        }, 'json').always(function(response) {
           
            console.log(response);
            
        });
        
    });
    
    $('.js-customer-button').click(function(e) {
        
        e.preventDefault();
                
        alert('Add Customer Popup?');
       
    });
    
    $('.js-company-button').click(function(e) {
        
        e.preventDefault();
                
        alert('Add Company Popup?');
       
    });
    
});