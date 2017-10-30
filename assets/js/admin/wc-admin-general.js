jQuery(document).ready(function($) {
    
    function getEnhancedSelectFormatString() {
		var formatString = {
			formatMatches: function( matches ) {
				if ( 1 === matches ) {
					return wc_enhanced_select_params.i18n_matches_1;
				}

				return wc_enhanced_select_params.i18n_matches_n.replace( '%qty%', matches );
			},
			formatNoMatches: function() {
				return wc_enhanced_select_params.i18n_no_matches;
			},
			formatAjaxError: function() {
				return wc_enhanced_select_params.i18n_ajax_error;
			},
			formatInputTooShort: function( input, min ) {
				var number = min - input.length;

				if ( 1 === number ) {
					return wc_enhanced_select_params.i18n_input_too_short_1;
				}

				return wc_enhanced_select_params.i18n_input_too_short_n.replace( '%qty%', number );
			},
			formatInputTooLong: function( input, max ) {
				var number = input.length - max;

				if ( 1 === number ) {
					return wc_enhanced_select_params.i18n_input_too_long_1;
				}

				return wc_enhanced_select_params.i18n_input_too_long_n.replace( '%qty%', number );
			},
			formatSelectionTooBig: function( limit ) {
				if ( 1 === limit ) {
					return wc_enhanced_select_params.i18n_selection_too_long_1;
				}

				return wc_enhanced_select_params.i18n_selection_too_long_n.replace( '%qty%', limit );
			},
			formatLoadMore: function() {
				return wc_enhanced_select_params.i18n_load_more;
			},
			formatSearching: function() {
				return wc_enhanced_select_params.i18n_searching;
			}
		};

		return formatString;
	}
    
    // Ajax customer search boxes
	$( '.wc-advanced-search' ).filter( ':not(.enhanced)' ).each( function() {
		
		var select2_args = {
			allowClear:  $( this ).data( 'allow_clear' ) ? true : false,
			placeholder: $( this ).data( 'placeholder' ),
			minimumInputLength: $( this ).data( 'minimum_input_length' ) ? $( this ).data( 'minimum_input_length' ) : '3',
			escapeMarkup: function( m ) {
				return m;
			},
			ajax: {
				url:         wc_enhanced_select_params.ajax_url,
				dataType:    'json',
				delay:       250,
				data:        function( params ) {
					return {
						term:     params.term,
						action:   $( this ).data( 'action' ),
						security: $( this ).data( 'nonce' ),
						exclude:  $( this ).data( 'exclude' ),
						include:  $( this ).data( 'include' ),
						limit:    $( this ).data( 'limit' )
					};
				},
				processResults: function( data ) {
					var terms = [];
					if ( data ) {
						$.each( data, function( id, text ) {
							terms.push( { id: id, text: text } );
						});
					}
					return {
						results: terms
					};
				},
				cache: true
			}
		};

		select2_args = $.extend( select2_args, getEnhancedSelectFormatString() );

		$( this ).select2( select2_args ).addClass( 'enhanced' );

		if ( $( this ).data( 'sortable' ) ) {
			var $select = $(this);
			var $list   = $( this ).next( '.select2-container' ).find( 'ul.select2-selection__rendered' );

			$list.sortable({
				placeholder : 'ui-state-highlight select2-selection__choice',
				forcePlaceholderSize: true,
				items       : 'li:not(.select2-search__field)',
				tolerance   : 'pointer',
				stop: function() {
					$( $list.find( '.select2-selection__choice' ).get().reverse() ).each( function() {
						var id     = $( this ).data( 'data' ).id;
						var option = $select.find( 'option[value="' + id + '"]' )[0];
						$select.prepend( option );
					} );
				}
			});
		}
		
	});

});

(function($){
    $.fn.serializeObject = function () {
        "use strict";

        var result = {};
        var extend = function (i, element) {
            var node = result[element.name];

            // If node with same name exists already, need to convert it to an array as it
            // is a multi-value field (i.e., checkboxes)

            if ('undefined' !== typeof node && node !== null) {
                if ($.isArray(node)) {
                    node.push(element.value);
                } else {
                    result[element.name] = [node, element.value];
                }
            } else {
                result[element.name] = element.value;
            }
        };

        $.each(this.serializeArray(), extend);
        return result;
    };
})(jQuery);