jQuery(document).ready(function($) {
   
    $('.js-address-select').change(function() {
      
        var address_id = $(this).val();
        
        var args = {
            'action' : 'get_address',
            'address_id' : address_id
        };
        
        alert('Make an ajax request with args ' . JSON.stringify(args));
       
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