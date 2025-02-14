<?php
/**
 * Booster for WooCommerce - Functions - General
 *
 * @version 4.4.0
 * @author  Algoritmika Ltd.
 * @todo    add `wcj_add_actions()` and `wcj_add_filters()`
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'wcj_range_match' ) ) {
	/**
	 * wcj_range_match.
	 *
	 * @version 4.0.0
	 * @since   3.4.0
	 */
	function wcj_range_match( $postcode_range, $postcode_to_check ) {
		$postcode_range = explode( '...', $postcode_range );
		return ( 2 === count( $postcode_range ) && $postcode_to_check >= $postcode_range[0] && $postcode_to_check <= $postcode_range[1] );
	}
}

if ( ! function_exists( 'wcj_check_postcode' ) ) {
	/**
	 * wcj_check_postcode.
	 *
	 * @version 4.0.0
	 * @since   3.4.0
	 */
	function wcj_check_postcode( $postcode_to_check, $postcodes ) {
		foreach ( $postcodes as $postcode ) {
			if (
				( $postcode === $postcode_to_check ) ||
				( false !== strpos( $postcode, '*' )   && fnmatch( $postcode, $postcode_to_check ) ) ||
				( false !== strpos( $postcode, '...' ) && wcj_range_match( $postcode, $postcode_to_check ) )
			) {
				return true;
			}
		}
		return false;
	}
}

if ( ! function_exists( 'wcj_help_tip' ) ) {
	/**
	 * wcj_help_tip.
	 *
	 * @version 3.9.0
	 * @since   3.9.0
	 */
	function wcj_help_tip( $text ) {
		return ' <img style="display:inline;" class="wcj-question-icon" src="' . wcj_plugin_url() . '/assets/images/question-icon.png' . '" title="' . esc_html( $text ) . '">';
	}
}

if ( ! function_exists( 'wcj_get_wpml_default_language' ) ) {
	/**
	 * wcj_get_wpml_default_language.
	 *
	 * @version 3.7.0
	 * @since   3.7.0
	 */
	function wcj_get_wpml_default_language() {
		global $sitepress;
		if ( $sitepress ) {
			return $sitepress->get_default_language();
		} elseif ( function_exists( 'icl_get_setting' ) ) {
			return icl_get_setting( 'default_language' );
		} else {
			return null;
		}
	}
}

if ( ! function_exists( 'wcj_get_array' ) ) {
	/**
	 * wcj_get_array.
	 *
	 * @version 3.6.0
	 * @since   3.6.0
	 */
	function wcj_get_array( $value ) {
		return ( ! is_array( $value ) ? array( $value ) : $value );
	}
}

if ( ! function_exists( 'wcj_is_product_in_cart' ) ) {
	/**
	 * wcj_is_product_in_cart.
	 *
	 * @version 3.6.0
	 * @since   2.9.1
	 */
	function wcj_is_product_in_cart( $product_id ) {
		if ( 0 != $product_id ) {
			if ( isset( WC()->cart->cart_contents ) && is_array( WC()->cart->cart_contents ) ) {
				foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item_data ) {
					if (
						( isset( $cart_item_data['product_id'] )   && $product_id == $cart_item_data['product_id'] ) ||
						( isset( $cart_item_data['variation_id'] ) && $product_id == $cart_item_data['variation_id'] )
					) {
						return true;
					}
				}
			}
		}
		return false;
	}
}

if ( ! function_exists( 'wcj_send_file' ) ) {
	/**
	 * wcj_send_file.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 * @todo    use where needed
	 * @todo    add more cases for `$file_type`
	 */
	function wcj_send_file( $file_name, $file_path, $file_type, $do_clean_up = true ) {
		switch ( $file_type ) {
			default: // 'zip'
				header( "Content-Type: application/octet-stream" );
				header( "Content-Disposition: attachment; filename=" . urlencode( $file_name ) );
				header( "Content-Type: application/octet-stream" );
				header( "Content-Type: application/download" );
				header( "Content-Description: File Transfer" );
				header( "Content-Length: " . filesize( $file_path ) );
				break;
		}
		flush(); // this doesn't really matter.
		if ( false !== ( $fp = fopen( $file_path, "r" ) ) ) {
			while ( ! feof( $fp ) ) {
				echo fread( $fp, 65536 );
				flush(); // this is essential for large downloads
			}
			fclose( $fp );
			if ( $do_clean_up ) {
				@unlink( $file_path );
			}
			exit();
		} else {
			die( __( 'Unexpected error', 'woocommerce-jetpack' ) );
		}
	}
}

if ( ! function_exists( 'wcj_parse_number' ) ) {
	/**
	 * wcj_parse_number.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 * @todo    maybe there is a better way (e.g. `numfmt_parse()`)
	 */
	function wcj_parse_number( $number_string ) {
		if ( false !== strpos( $number_string, '.' ) ) {
			return str_replace( ',', '', $number_string );
		} else {
			return str_replace( ',', '.', $number_string );
		}
	}
}

if ( ! function_exists( 'wcj_handle_replacements' ) ) {
	/**
	 * wcj_handle_replacements.
	 *
	 * @version 3.4.0
	 * @since   3.4.0
	 */
	function wcj_handle_replacements( $replacements, $template ) {
		return str_replace( array_keys( $replacements ), $replacements, $template );
	}
}

if ( ! function_exists( 'wcj_get_js_confirmation' ) ) {
	/**
	 * wcj_get_js_confirmation.
	 *
	 * @version 3.4.0
	 * @since   3.3.0
	 * @todo    use where needed
	 */
	function wcj_get_js_confirmation( $confirmation_message = '' ) {
		if ( '' === $confirmation_message ) {
			$confirmation_message = __( 'Are you sure?', 'woocommerce-jetpack' );
		}
		return ' onclick="return confirm(\'' . $confirmation_message . '\')"';
	}
}

if ( ! function_exists( 'wcj_tcpdf_method' ) ) {
	/**
	 * wcj_tcpdf_method.
	 *
	 * @version 3.6.0
	 * @since   3.4.0
	 */
	function wcj_tcpdf_method( $method, $params ) {
		return '<tcpdf method="' . $method . '" wcj_tcpdf_method_params_start' . serialize( $params ) . 'wcj_tcpdf_method_params_end />';
	}
}

if ( ! function_exists( 'wcj_tcpdf_barcode' ) ) {
	/**
	 * wcj_tcpdf_barcode.
	 *
	 * @version 3.4.0
	 * @since   3.3.0
	 * @todo    `color`
	 * @todo    `align` (try 'T')
	 */
	function wcj_tcpdf_barcode( $atts ) {
		if ( '' === $atts['code'] ) {
			return '';
		}
		if ( '' === $atts['type'] ) {
			$atts['type'] = ( '1D' === $atts['dimension'] ? 'C39' : 'PDF417' );
		}
		if ( 0 === $atts['width'] ) {
			$atts['width']  = ( '1D' === $atts['dimension'] ? 80 : 80 );
		}
		if ( 0 === $atts['height'] ) {
			$atts['height'] = ( '1D' === $atts['dimension'] ? 30 : 80 );
		}
		if ( '1D' === $atts['dimension'] ) {
			$params = array(
				$atts['code'],
				$atts['type'],
				'',  // x
				'',  // y
				$atts['width'],
				$atts['height'],
				0.4, // xres
				array( // style
					'position'      => 'S',
					'border'        => false,
					'padding'       => 4,
					'fgcolor'       => array( 0, 0, 0 ),
					'bgcolor'       => array( 255, 255, 255 ),
					'text'          => false,
				),
				'N', // align
			);
		} else {
			$params = array(
				$atts['code'],
				$atts['type'],
				'',  // x
				'',  // y
				$atts['width'],
				$atts['height'],
				array( // style
					'border'        => false,
					'vpadding'      => 'auto',
					'hpadding'      => 'auto',
					'fgcolor'       => array( 0, 0, 0 ),
					'bgcolor'       => array( 255, 255, 255 ),
					'module_width'  => 1, // width of a single module in points
					'module_height' => 1, // height of a single module in points
				),
				'N', // align
				false, // distort
			);
		}
		return wcj_tcpdf_method( ( '1D' === $atts['dimension'] ? 'write1DBarcode' : 'write2DBarcode' ), $params );
	}
}

if ( ! function_exists( 'wcj_barcode' ) ) {
	/**
	 * wcj_barcode.
	 *
	 * @version 3.4.0
	 * @since   3.3.0
	 * @todo    (maybe) "Barcodes" module
	 * @todo    (maybe) `getBarcodePNG()`
	 */
	function wcj_barcode( $atts ) {
		if ( '' === $atts['code'] ) {
			return '';
		}
		if ( '' === $atts['type'] ) {
			$atts['type'] = ( '1D' === $atts['dimension'] ? 'C39' : 'PDF417' );
		}
		if ( 0 === $atts['width'] ) {
			$atts['width']  = ( '1D' === $atts['dimension'] ? 2  : 10 );
		}
		if ( 0 === $atts['height'] ) {
			$atts['height'] = ( '1D' === $atts['dimension'] ? 30 : 10 );
		}
		if ( '1D' === $atts['dimension'] ) {
			require_once( WCJ_PLUGIN_PATH . '/includes/lib/tcpdf/tcpdf_barcodes_1d.php' );
			$barcode = new TCPDFBarcode( $atts['code'], $atts['type'] );
		} else {
			require_once( WCJ_PLUGIN_PATH . '/includes/lib/tcpdf/tcpdf_barcodes_2d.php' );
			$barcode = new TCPDF2DBarcode( $atts['code'], $atts['type'] );
		}
		$barcode_array = $barcode->getBarcodeArray();
		return ( ! empty( $barcode_array ) && is_array( $barcode_array ) ? $barcode->getBarcodeHTML( $atts['width'], $atts['height'], $atts['color'] ) : '' );
	}
}

if ( ! function_exists( 'wcj_session_maybe_start' ) ) {
	/**
	 * wcj_session_maybe_start.
	 *
	 * @version 4.3.0
	 * @since   3.1.0
	 */
	function wcj_session_maybe_start() {
		switch ( WCJ_SESSION_TYPE ) {
			case 'wc':
				if ( function_exists( 'WC' ) && WC()->session && ! WC()->session->has_session() ) {
					WC()->session->set_customer_session_cookie( true );
				}
				break;
			default:
				if ( session_status() == PHP_SESSION_NONE && ! headers_sent() ) {
					$read_and_close = ( 'yes' === get_option( 'wcj_general_advanced_session_read_and_close', 'no' ) ) && PHP_VERSION_ID > 70000 ? array( 'read_and_close' => true ) : array();
					session_start( $read_and_close );
				}
				break;
		}
	}
}

if ( ! function_exists( 'wcj_session_set' ) ) {
	/**
	 * wcj_session_set.
	 *
	 * @version 3.4.0
	 * @since   3.1.0
	 */
	function wcj_session_set( $key, $value ) {
		switch ( WCJ_SESSION_TYPE ) {
			case 'wc':
				if ( function_exists( 'WC' ) && WC()->session ) {
					WC()->session->set( $key, $value );
				}
				break;
			default: // 'standard'
				$_SESSION[ $key ] = $value;
				break;
		}
	}
}

if ( ! function_exists( 'wcj_session_get' ) ) {
	/**
	 * wcj_session_get.
	 *
	 * @version 3.4.0
	 * @since   3.1.0
	 */
	function wcj_session_get( $key, $default = null ) {
		switch ( WCJ_SESSION_TYPE ) {
			case 'wc':
				return ( function_exists( 'WC' ) && WC()->session ? WC()->session->get( $key, $default ) : $default );
			default: // 'standard'
				return ( isset( $_SESSION[ $key ] ) ? $_SESSION[ $key ] : $default );
		}
	}
}

if ( ! function_exists( 'wcj_wrap_in_wc_email_template' ) ) {
	/**
	 * wcj_wrap_in_wc_email_template.
	 *
	 * @version 3.9.0
	 * @since   3.1.0
	 */
	function wcj_wrap_in_wc_email_template( $content, $email_heading = '' ) {
		return wcj_get_wc_email_part( 'header', $email_heading ) .
			$content .
		str_replace( '{site_title}', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), wcj_get_wc_email_part( 'footer' ) );
	}
}

if ( ! function_exists( 'wcj_get_wc_email_part' ) ) {
	/**
	 * wcj_get_wc_email_part.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function wcj_get_wc_email_part( $part, $email_heading = '' ) {
		ob_start();
		switch ( $part ) {
			case 'header':
				wc_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );
				break;
			case 'footer':
				wc_get_template( 'emails/email-footer.php' );
				break;
		}
		return ob_get_clean();
	}
}

if ( ! function_exists( 'wcj_maybe_add_date_query' ) ) {
	/**
	 * wcj_maybe_add_date_query.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function wcj_maybe_add_date_query( $args ) {
		if ( ( isset( $_GET['start_date'] ) && '' != $_GET['start_date'] ) || ( isset( $_GET['end_date'] ) && '' != $_GET['end_date'] ) )  {
			$date_query = array();
			$date_query['inclusive'] = true;
			if ( isset( $_GET['start_date'] ) && '' != $_GET['start_date'] ) {
				$date_query['after'] = $_GET['start_date'];
			}
			if ( isset( $_GET['end_date'] ) && '' != $_GET['end_date'] ) {
				$date_query['before'] = $_GET['end_date'];
			}
			$args['date_query'] = array( $date_query );
		}
		return $args;
	}
}

if ( ! function_exists( 'wcj_is_module_deprecated' ) ) {
	/**
	 * wcj_is_module_deprecated.
	 *
	 * @version 2.9.0
	 * @since   2.9.0
	 * @return  array|false
	 */
	function wcj_is_module_deprecated( $module_id, $by_module_option = false, $check_for_disabled = false ) {
		if ( $check_for_disabled ) {
			$module_option = ( $by_module_option ? $module_id : 'wcj_' . $module_id . '_enabled' );
			if ( 'yes' === get_option( $module_option, 'no' ) ) {
				return false;
			}
		}
		if ( $by_module_option ) {
			$module_id = str_replace( array( 'wcj_', '_enabled' ), '', $module_id );
		}
		$deprecated_and_replacement_modules = array(
			'product_info' => array(
				'cat'    => 'products',
				'module' => 'product_custom_info',
				'title'  => __( 'Product Info', 'woocommerce-jetpack' ),
			),
		);
		if ( ! array_key_exists( $module_id, $deprecated_and_replacement_modules ) ) {
			return false;
		} else {
			return ( isset( $deprecated_and_replacement_modules[ $module_id ] ) ? $deprecated_and_replacement_modules[ $module_id ] : array() );
		}
	}
}

if ( ! function_exists( 'wcj_customer_get_country' ) ) {
	/**
	 * wcj_customer_get_country.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 * @todo    (maybe) move to `wcj-functions-users.php`
	 */
	function wcj_customer_get_country() {
		return ( WCJ_IS_WC_VERSION_BELOW_3 ? WC()->customer->get_country() : WC()->customer->get_billing_country() );
	}
}

if ( ! function_exists( 'wcj_customer_get_country_state' ) ) {
	/**
	 * wcj_customer_get_country_state.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 * @todo    (maybe) move to `wcj-functions-users.php`
	 */
	function wcj_customer_get_country_state() {
		return ( WCJ_IS_WC_VERSION_BELOW_3 ? WC()->customer->get_state() : WC()->customer->get_billing_state() );
	}
}

if ( ! function_exists( 'wcj_is_bot' ) ) {
	/**
	 * wcj_is_bot.
	 *
	 * @version 3.9.0
	 * @since   2.5.6
	 */
	function wcj_is_bot() {
		return ( isset( $_SERVER['HTTP_USER_AGENT'] ) && preg_match( '/Google-Structured-Data-Testing-Tool|bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT'] ) );
	}
}

if ( ! function_exists( 'wcj_add_files_upload_form_to_checkout_frontend' ) ) {
	/**
	 * wcj_add_files_upload_form_to_checkout_frontend.
	 *
	 * @version 2.5.2
	 * @since   2.5.2
	 */
	function wcj_add_files_upload_form_to_checkout_frontend() {
		WCJ()->modules['checkout_files_upload']->add_files_upload_form_to_checkout_frontend_all( true );
	}
}

if ( ! function_exists( 'wcj_replace_values_in_template' ) ) {
	/**
	 * wcj_replace_values_in_template.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function wcj_replace_values_in_template( $values_to_replace, $template ) {
		return str_replace( array_keys( $values_to_replace ), array_values( $values_to_replace ), $template );
	}
}

if ( ! function_exists( 'wcj_variation_radio_button' ) ) {
	/**
	 * wcj_variation_radio_button.
	 *
	 * @version 3.1.0
	 * @since   2.4.8
	 * @todo    (maybe) check - maybe we can use `$variation['variation_description']` instead of `get_post_meta( $variation_id, '_variation_description', true )`
	 */
	function wcj_variation_radio_button( $_product, $variation ) {
		$attributes_html                     = '';
		$variation_attributes_display_values = array();
		$is_checked                          = true;
		foreach ( $variation['attributes'] as $attribute_full_name => $attribute_value ) {
			$attributes_html .= ' ' . $attribute_full_name . '="' . $attribute_value . '"';
			// Attribute name
			$attribute_name = $attribute_full_name;
			$prefix         = 'attribute_';
			if ( substr( $attribute_full_name, 0, strlen( $prefix ) ) === $prefix ) {
				$attribute_name = substr( $attribute_full_name, strlen( $prefix ) );
			}
			// Checked
			$checked = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ?
				wc_clean( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) : $_product->get_variation_default_attribute( $attribute_name );
			if ( $checked != $attribute_value ) {
				$is_checked = false;
			}
			// Attribute value
			$terms = get_terms( $attribute_name );
			foreach ( $terms as $term ) {
				if ( is_object( $term ) && isset( $term->slug ) && $term->slug === $attribute_value && isset( $term->name ) ) {
					$attribute_value = $term->name;
				}
			}
			// Display values
			$variation_attributes_display_values[] = $attribute_value;
		}
		// Variation label
		$variation_label = wcj_replace_values_in_template( array(
			'%variation_title%' => implode( ', ', $variation_attributes_display_values ),
			'%variation_price%' => wc_price( $variation['display_price'] ),
		), get_option( 'wcj_add_to_cart_variable_as_radio_variation_label_template', '%variation_title% (%variation_price%)' ) );
		// Variation ID and "is checked"
		$variation_id    = $variation['variation_id'];
		$is_checked      = checked( $is_checked, true, false );
		// Final HTML
		$html = '';
		$html .= '<td class="wcj_variable_as_radio_input_td" style="' . get_option( 'wcj_add_to_cart_variable_as_radio_input_td_style', 'width:10%;' ) . '">';
		$html .= '<input id="wcj_variation_' . $variation_id . '" name="wcj_variations" type="radio"' . $attributes_html . ' variation_id="' .
			$variation_id . '"' . $is_checked . '>';
		$html .= '</td>';
		$html .= '<td class="wcj_variable_as_radio_label_td">';
		$html .= '<label for="wcj_variation_' . $variation_id . '">';
		$html .= $variation_label;
		if ( '' != ( $variation_description = get_post_meta( $variation_id, '_variation_description', true ) ) ) {
			$html .= wcj_replace_values_in_template( array(
				'%variation_description%' => $variation_description,
			), get_option( 'wcj_add_to_cart_variable_as_radio_variation_desc_template', '<br><small>%variation_description%</small>' ) );
		}
		$html .= '</label>';
		$html .= '</td>';
		echo $html;
	}
}

if ( ! function_exists( 'wcj_current_filter_priority' ) ) {
	/*
	 * wcj_current_filter_priority.
	 *
	 * @version 2.5.8
	 * @since   2.4.6
	 */
	function wcj_current_filter_priority() {
		global $wp_filter;
		$current_filter_data = $wp_filter[ current_filter() ];
		if ( class_exists( 'WP_Hook' ) && is_a( $current_filter_data, 'WP_Hook' ) ) {
			// since WordPress v4.7
			return $current_filter_data->current_priority();
		} else {
			// before WordPress v4.7
			return key( $current_filter_data );
		}
	}
}

if ( ! function_exists( 'wcj_maybe_implode' ) ) {
	/*
	 * wcj_maybe_implode.
	 *
	 * @version 3.2.1
	 * @since   3.2.1
	 * @return  string
	 */
	function wcj_maybe_implode( $value, $glue = ' ' ) {
		if ( is_array( $value ) ) {
			$value = implode( $glue, $value );
		}
		return $value;
	}
}

if ( ! function_exists( 'wcj_maybe_unserialize_and_implode' ) ) {
	/*
	 * wcj_maybe_unserialize_and_implode.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 * @return  string
	 * @todo    `if ( ! is_array() )`
	 */
	function wcj_maybe_unserialize_and_implode( $value, $glue = ' ' ) {
		if ( is_serialized( $value ) ) {
			$value = unserialize( $value );
			if ( is_array( $value ) ) {
				$value = implode( $glue, $value );
			}
		}
		return $value;
	}
}

if ( ! function_exists( 'wcj_get_cart_filters' ) ) {
	/*
	 * wcj_get_cart_filters()
	 *
	 * @version 2.4.4
	 * @since   2.4.4
	 * @return  array
	 */
	function wcj_get_cart_filters() {
		return array(
			'woocommerce_before_cart'                    => __( 'Before cart', 'woocommerce-jetpack' ),
			'woocommerce_before_cart_table'              => __( 'Before cart table', 'woocommerce-jetpack' ),
			'woocommerce_before_cart_contents'           => __( 'Before cart contents', 'woocommerce-jetpack' ),
			'woocommerce_cart_contents'                  => __( 'Cart contents', 'woocommerce-jetpack' ),
			'woocommerce_cart_coupon'                    => __( 'Cart coupon', 'woocommerce-jetpack' ),
			'woocommerce_cart_actions'                   => __( 'Cart actions', 'woocommerce-jetpack' ),
			'woocommerce_after_cart_contents'            => __( 'After cart contents', 'woocommerce-jetpack' ),
			'woocommerce_after_cart_table'               => __( 'After cart table', 'woocommerce-jetpack' ),
			'woocommerce_cart_collaterals'               => __( 'Cart collaterals', 'woocommerce-jetpack' ),
			'woocommerce_after_cart'                     => __( 'After cart', 'woocommerce-jetpack' ),

			'woocommerce_before_cart_totals'             => __( 'Before cart totals', 'woocommerce-jetpack' ),
			'woocommerce_cart_totals_before_shipping'    => __( 'Cart totals: Before shipping', 'woocommerce-jetpack' ),
			'woocommerce_cart_totals_after_shipping'     => __( 'Cart totals: After shipping', 'woocommerce-jetpack' ),
			'woocommerce_cart_totals_before_order_total' => __( 'Cart totals: Before order total', 'woocommerce-jetpack' ),
			'woocommerce_cart_totals_after_order_total'  => __( 'Cart totals: After order total', 'woocommerce-jetpack' ),
			'woocommerce_proceed_to_checkout'            => __( 'Proceed to checkout', 'woocommerce-jetpack' ),
			'woocommerce_after_cart_totals'              => __( 'After cart totals', 'woocommerce-jetpack' ),

			'woocommerce_before_shipping_calculator'     => __( 'Before shipping calculator', 'woocommerce-jetpack' ),
			'woocommerce_after_shipping_calculator'      => __( 'After shipping calculator', 'woocommerce-jetpack' ),

			'woocommerce_cart_is_empty'                  => __( 'If cart is empty', 'woocommerce-jetpack' ),
		);
	}
}

if ( ! function_exists( 'wcj_get_rates_for_tax_class' ) ) {
	/* Used by admin settings page.
	 *
	 * @param string $tax_class
	 *
	 * @return array|null|object
	 *
	 * @version 2.3.10
	 * @since   2.3.10
	 */
	function wcj_get_rates_for_tax_class( $tax_class ) {
		global $wpdb;

		// Get all the rates and locations. Snagging all at once should significantly cut down on the number of queries.
		$rates     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}woocommerce_tax_rates` WHERE `tax_rate_class` = %s ORDER BY `tax_rate_order`;", sanitize_title( $tax_class ) ) );
		$locations = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}woocommerce_tax_rate_locations`" );

		// Set the rates keys equal to their ids.
		$rates = array_combine( wp_list_pluck( $rates, 'tax_rate_id' ), $rates );

		// Drop the locations into the rates array.
		foreach ( $locations as $location ) {
			// Don't set them for unexistent rates.
			if ( ! isset( $rates[ $location->tax_rate_id ] ) ) {
				continue;
			}
			// If the rate exists, initialize the array before appending to it.
			if ( ! isset( $rates[ $location->tax_rate_id ]->{$location->location_type} ) ) {
				$rates[ $location->tax_rate_id ]->{$location->location_type} = array();
			}
			$rates[ $location->tax_rate_id ]->{$location->location_type}[] = $location->location_code;
		}

		return $rates;
	}
}

if ( ! function_exists( 'wcj_get_select_options' ) ) {
	/**
	 * wcj_get_select_options()
	 *
	 * @version  4.3.0
	 * @since    2.3.0
	 * @return   array
	 */
	function wcj_get_select_options( $select_options_raw, $do_sanitize = true ) {
		if ( '' === $select_options_raw ) {
			return array();
		}
		$select_options_raw = array_map( 'trim', explode( PHP_EOL, $select_options_raw ) );
		$select_options = array();
		foreach ( $select_options_raw as $select_options_title ) {
			$select_options_key = ( $do_sanitize ) ? urldecode( sanitize_title( $select_options_title ) ) : $select_options_title;
			$select_options[ $select_options_key ] = $select_options_title;
		}
		return $select_options;
	}
}

if ( ! function_exists( 'wcj_is_frontend' ) ) {
	/**
	 * wcj_is_frontend()
	 *
	 * @since  4.0.0
	 * @return boolean
	 */
	function wcj_is_frontend() {
		if ( ! is_admin() ) {
			return true;
		} elseif ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return ( ! isset( $_REQUEST['action'] ) || ! is_string( $_REQUEST['action'] ) || ! in_array( $_REQUEST['action'], array(
					'woocommerce_load_variations',
				) ) );
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'wcj_get_wcj_uploads_dir' ) ) {
	/**
	 * wcj_get_wcj_uploads_dir.
	 *
	 * @version 3.9.0
	 * @todo    no need to `mkdir` after `wcj_get_wcj_uploads_dir`
	 */
	function wcj_get_wcj_uploads_dir( $subdir = '', $do_mkdir = true ) {
		$upload_dir = wp_upload_dir();
		$upload_dir = $upload_dir['basedir'];
		$upload_dir = $upload_dir . '/woocommerce_uploads';
		if ( $do_mkdir && ! file_exists( $upload_dir ) ) {
			mkdir( $upload_dir, 0755, true );
		}
		$upload_dir = $upload_dir . '/wcj_uploads';
		if ( $do_mkdir && ! file_exists( $upload_dir ) ) {
			mkdir( $upload_dir, 0755, true );
		}
		if ( '' != $subdir ) {
			$upload_dir = $upload_dir . '/' . $subdir;
			if ( $do_mkdir && ! file_exists( $upload_dir ) ) {
				mkdir( $upload_dir, 0755, true );
			}
		}
		return $upload_dir;
	}
}

if ( ! function_exists( 'wcj_hex2rgb' ) ) {
	/**
	 * wcj_hex2rgb.
	 */
	function wcj_hex2rgb( $hex ) {
		return sscanf( $hex, "#%2x%2x%2x" );
	}
}

if ( ! function_exists( 'wcj_get_the_ip' ) ) {
	/**
	 * wcj_get_the_ip.
	 *
	 * @version 4.3.0
	 * @see http://stackoverflow.com/questions/3003145/how-to-get-the-client-ip-address-in-php
	 */
	function wcj_get_the_ip() {
		$ip                   = null;
		$ip_detection_methods = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
		$ip_detection_methods = 'yes' === get_option( 'wcj_general_enabled', 'no' ) ? explode( PHP_EOL, get_option( 'wcj_general_advanced_ip_detection', implode( PHP_EOL, $ip_detection_methods ) ) ) : $ip_detection_methods;
		foreach ( $ip_detection_methods as $method ) {
			if ( empty( $_SERVER[ sanitize_text_field( $method ) ] ) ) {
				continue;
			}
			$ip = sanitize_text_field( wp_unslash( $_SERVER[ sanitize_text_field( $method ) ] ) );
			break;
		}
		return $ip;
	}
}

if ( ! function_exists( 'wcj_get_shortcodes_list' ) ) {
	/**
	 * wcj_get_shortcodes_list.
	 */
	function wcj_get_shortcodes_list() {
		$the_array = apply_filters( 'wcj_shortcodes_list', array() );
		return implode( ', ', $the_array )/*  . ' (' . count( $the_array ) . ')' */;
	}
}

if ( ! function_exists( 'wcj_get_cart_item_quantities' ) ) {
	/**
	 * Gets cart items quantities, with correct variation id, where native function from WooCommerce fails getting only the parent id if Manage Stock option is enabled.
	 *
	 * @see WC_Cart::get_cart_item_quantities();
	 *
	 * @version 4.4.0
	 * @since   4.4.0
	 * @return  array
	 */
	function wcj_get_cart_item_quantities() {
		$quantities = array();

		foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
			$product = $values['data'];
			if ( 'variation' == $product->get_type() ) {
				$product_id = ( 0 != $values['variation_id'] ? $values['variation_id'] : $values['parent_id'] );
			} else {
				$product_id = $values['product_id'];
			}
			$quantities[ $product_id ] = isset( $quantities[ $product->get_stock_managed_by_id() ] ) ? $quantities[ $product->get_stock_managed_by_id() ] + $values['quantity'] : $values['quantity'];
		}

		return $quantities;
	}
}