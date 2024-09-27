<?php

namespace Ademti\WoocommerceProductFeeds\Integrations;

use function explode;
use function is_array;

/**
 * Support class for AdvancedCustomFields integration.
 */
class AdvancedCustomFieldsFormatter {
	/**
	 * @param $field_object
	 * @param $default
	 * @param $prepopulate
	 *
	 * @return array
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 */
	public function get_value( $field_object, $default, $prepopulate ) {
		if ( empty( $field_object['type'] ) ) {
			return $default;
		}
		switch ( $field_object['type'] ) {
			case 'button_group':
			case 'select':
			case 'radio':
				return $this->get_optioned_value( $field_object, $default );
				break;
			case 'file':
			case 'image':
				return $this->get_file_value( $field_object, $default, $prepopulate );
				break;
			case 'link':
				return $this->get_link_value( $field_object, $default );
				break;
			case 'taxonomy':
				return $this->get_taxonomy_value( $field_object, $default );
				break;
			case 'true_false':
				return $this->get_true_false_value( $field_object, $default );
				break;
			case 'date_picker':
			case 'date_time_picker':
			case 'number':
			case 'page_link':
			case 'range':
			case 'text':
			case 'textarea':
			case 'url':
			case 'wysiwyg':
			default:
				return $this->get_raw_value( $field_object, $default );
				break;
		}
	}

	/**
	 * @param $field_object
	 * @param $default
	 *
	 * @return array
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	private function get_optioned_value( $field_object, $default ) {
		$results = [];
		$values  = ! is_array( $field_object['value'] ) ?
			[ $field_object['value'] ] :
			$field_object['value'];
		foreach ( $values as $value ) {
			$results[] = $field_object['choices'][ $value ] ?? $value;
		}

		return $results;
	}
	// phpcs:enable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

	/**
	 * @param $field_object
	 * @param $default
	 * @param $prepopulate
	 *
	 * @return array
	 */
	private function get_file_value( $field_object, $default, $prepopulate ) {
		$config = explode( ':', $prepopulate );
		if ( 'name' === $config[2] ) {
			return ! empty( $field_object['value']['filename'] ) ?
				[ $field_object['value']['filename'] ] :
				$default;
		}

		return ! empty( $field_object['value']['url'] ) ?
			[ $field_object['value']['url'] ] :
			$default;
	}

	/**
	 * @param $field_object
	 * @param $default
	 *
	 * @return array|mixed
	 */
	private function get_link_value( $field_object, $default ) {
		return ! empty( $field_object['value']['url'] ) ?
			[ $field_object['value']['url'] ] :
			$default;
	}

	/**
	 * @param $field_object
	 * @param $default
	 *
	 * @return array|mixed
	 */
	private function get_raw_value( $field_object, $default ) {
		return ! empty( $field_object['value'] ) ?
			[ $field_object['value'] ] :
			$default;
	}

	/**
	 * @param $field_object
	 * @param $default
	 *
	 * @return mixed
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	private function get_true_false_value( $field_object, $default ) {
		if ( $field_object['ui'] ) {
			if ( $field_object['value'] ) {
				return ! empty( $field_object['ui_on_text'] ) ? $field_object['ui_on_text'] : __( 'Yes', 'acf' );
			}

			return ! empty( $field_object['ui_off_text'] ) ? $field_object['ui_off_text'] : __( 'No', 'acf' );
		}

		return $field_object['value'];
	}
	// phpcs:enable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

	/**
	 * Get the value, and map the term IDs or term objects to term names.
	 *
	 * @param $field_object
	 * @param $default
	 *
	 * @return array
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	private function get_taxonomy_value( $field_object, $default ) {
		$values = $field_object['value'];
		// Handle fields that only contain a single value.
		if ( ! is_array( $values ) ) {
			$values = [ $values ];
		}
		$results = [];
		foreach ( $values as $value ) {
			if ( is_int( $value ) ) {
				$value = get_term( $value );
			}
			if ( isset( $value->name ) ) {
				$results[] = $value->name;
			}
		}

		return $results;
	}
	// phpcs:enable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
}
