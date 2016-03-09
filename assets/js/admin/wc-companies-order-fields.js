jQuery(document).ready(function($) {
   
    $('.js-address-select').change(function() {
        
        var select = $(this);
        var parent = select.closest('.order_data_column');
        
        var address_id = select.val();
        
        $.blockUI.defaults.overlayCSS.cursor = 'default';
        
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
                
                parent.unblock();
                
            }
            
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