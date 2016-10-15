jQuery(document).ready(function($) {
					
	$("select.billing.chosen, select.shipping.chosen").prop('multiple', true).find('option').each(function() {
		
		var name = $(this).parent().hasClass('shipping') ? 'shipping' : 'billing';
		
		if(wc_companies_user[name].length) {
		
			if($.inArray($(this).attr('value'), wc_companies_user[name]) > -1) {
				
				$(this).prop('selected', true);
				
			} else {
				
				$(this).prop('selected', false);
				
			}
		
		} else {
		
			$(this).prop('selected', false);
		
		}
		
	}).end();
	
	$("select.company.chosen").prop('multiple', true).find('option').each(function() {
		
		var name = 'companies';
		
		if(wc_companies_user[name].length) {
		
			if($.inArray($(this).attr('value'), wc_companies_user[name]) > -1) {
				
				$(this).prop('selected', true);
				
			} else {
				
				$(this).prop('selected', false);
				
			}
		
		} else {
		
			$(this).prop('selected', false);
		
		}
		
	}).end();
	
});