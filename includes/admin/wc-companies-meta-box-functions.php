<?php
	
	
	add_action( 'woocommerce_form_field_multi-select', 'wc_multi_select_field', 10, 4 );
	
	function wc_multi_select_field($field, $key, $args, $value) {
	
		if ( ( ! empty( $args['clear'] ) ) ) {
			$after = '<div class="clear"></div>';
		} else {
			$after = '';
		}
	
		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required = ' <abbr class="required" title="' . esc_attr__( 'required', 'woocommerce'  ) . '">*</abbr>';
		} else {
			$required = '';
		}
	
		$args['maxlength'] = ( $args['maxlength'] ) ? 'maxlength="' . absint( $args['maxlength'] ) . '"' : '';
	
		if ( is_string( $args['label_class'] ) ) {
			$args['label_class'] = array( $args['label_class'] );
		}
	
		if ( is_null( $value ) ) {
			$value = $args['default'];
		}
	
		// Custom attribute handling
		$custom_attributes = array();
	
		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}
	
		if ( ! empty( $args['validate'] ) ) {
			foreach( $args['validate'] as $validate ) {
				$args['class'][] = 'validate-' . $validate;
			}
		}
		
		$options = $field = '';
	
		if ( ! empty( $args['options'] ) ) {
			foreach ( $args['options'] as $option_key => $option_text ) {
				if ( "" === $option_key ) {
					// If we have a blank option, select2 needs a placeholder
					if ( empty( $args['placeholder'] ) ) {
						$args['placeholder'] = $option_text ? $option_text : __( 'Choose an option', 'woocommerce' );
					}
					$custom_attributes[] = 'data-allow_clear="true"';
				}
				$options .= '<option value="' . esc_attr( $option_key ) . '" '. bnm_selected( $value, $option_key, false ) . '>' . esc_attr( $option_text ) .'</option>';
			}
	
			$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $args['id'] ) . '_field">';
	
			if ( $args['label'] ) {
				$field .= '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) .'">' . $args['label']. $required . '</label>';
			}
	
			$field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="select '.esc_attr( implode( ' ', $args['input_class'] ) ) .'" ' . implode( ' ', $custom_attributes ) . ' placeholder="' . esc_attr( $args['placeholder'] ) . '">
					' . $options . '
				</select>';
	
			if ( $args['description'] ) {
				$field .= '<span class="description">' . esc_attr( $args['description'] ) . '</span>';
			}
	
			$field .= '</p>' . $after;
		}
		
		return $field;
		
	}
	
	function bnm_selected($haystack, $current, $echo) {
		if(is_array($haystack) && in_array($current, $haystack)) {
			$current = $haystack = 1;
		} else {
			$haystack = '';
		}
		return selected($haystack, $current, $echo);
	}