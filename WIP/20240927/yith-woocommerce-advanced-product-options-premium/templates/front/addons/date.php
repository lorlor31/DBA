<?php
/**
 * WAPO Template
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductAddOns
 * @version 2.0.0
 *
 * @var YITH_WAPO_Addon $addon
 * @var int    $x
 * @var string $setting_hide_images
 * @var string $required_message
 * @var array  $settings
 * @var string $image_replacement
 * @var string $option_description
 * @var string $option_image
 * @var string $price
 * @var string $price_method
 * @var string $price_sale
 * @var string $price_type
 * @var string $currency
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

//Settings configuration.

extract($settings );

$hide_options_prices = apply_filters( 'yith_wapo_hide_option_prices', $hide_option_prices, $addon );

$show_in_a_grid      = wc_string_to_bool( $show_in_a_grid );
$options_width_css   = $show_in_a_grid && 1 == $options_per_row ? 'width: ' . $options_width . '%' : 'width: 100%';

$hide_option_images  = wc_string_to_bool( $hide_option_images );
$hide_option_label   = wc_string_to_bool( $hide_option_label );
$hide_option_prices  = wc_string_to_bool( $hide_option_prices );
$hide_product_prices = wc_string_to_bool( $hide_product_prices );

$image_replacement = $addon->get_image_replacement( $addon, $x );

// Options configuration.

$date_format       = $addon->get_option( 'date_format', $x );

$date_format       = ! empty( $date_format ) ? $date_format : 'yy-mm-dd';
$date_format_js    = str_replace( 'd', 'dd', $date_format );
$date_format_js    = str_replace( 'm', 'mm', $date_format_js );
$date_format_js    = str_replace( 'Y', 'yy', $date_format_js );
$default_date      = '';
$default_date_type = $addon->get_option( 'date_default', $x );
// phpcs:disable WordPress.DateTime.RestrictedFunctions.date_date
if ( 'today' === $default_date_type ) {
	$default_date = date( $date_format );
} elseif ( 'tomorrow' === $default_date_type ) {
	$default_date = date( $date_format, strtotime( '+1 day' ) );
} elseif ( 'specific' === $default_date_type ) {
	$default_specific_day = $addon->get_option( 'date_default_day', $x );
	$default_date         = date( $date_format, strtotime( $default_specific_day ) );
} elseif ( 'interval' === $default_date_type ) {
	$default_calculate_num  = $addon->get_option( 'date_default_calculate_num', $x );
	$default_calculate_type = $addon->get_option( 'date_default_calculate_type', $x );
	$default_date           = date( $date_format, strtotime( '+' . $default_calculate_num . ' ' . $default_calculate_type ) );
} elseif ( 'firstavl' === $default_date_type ) {
	$default_date = $default_date_type;
}

$required         = $addon->get_option( 'required', $x, 'no', false ) === 'yes';

$start_year       = $addon->get_option( 'start_year', $x, date("Y" ), false );
$end_year         = $addon->get_option( 'end_year', $x, date("Y" ), false );

$selectable_dates = $addon->get_option( 'selectable_dates', $x, '', false );

$days_min         = $addon->get_option( 'days_min', $x, '', false );
$days_max         = $addon->get_option( 'days_max', $x, '', false );

$days_min = 'days' === $selectable_dates && ! empty( $days_min ) ? $days_min : 0;
$days_max = 'days' === $selectable_dates && ! empty( $days_max ) ? $days_max : 365;

$date_min         = $addon->get_option( 'date_min', $x, '', false );
$date_max         = $addon->get_option( 'date_max', $x, '', false );

$date_min         = 'date' === $selectable_dates ? $date_min : '';
$date_max         = 'date' === $selectable_dates ? $date_max : '';

$show_time_selector  = $addon->get_option( 'show_time_selector', $x );
$enable_time_slots   = $addon->get_option( 'enable_time_slots', $x );
$time_slots_type     = $addon->get_option( 'time_slots_type', $x );
$time_slot_from      = $addon->get_option( 'time_slot_from', $x );
$time_slot_from_min  = $addon->get_option( 'time_slot_from_min', $x );
$time_slot_from_type = $addon->get_option( 'time_slot_from_type', $x );
$time_slot_to        = $addon->get_option( 'time_slot_to', $x );
$time_slot_to_min    = $addon->get_option( 'time_slot_to_min', $x );
$time_slot_to_type   = $addon->get_option( 'time_slot_to_type', $x );
$time_interval       = $addon->get_option( 'time_interval', $x );
$time_interval_type  = $addon->get_option( 'time_interval_type', $x );

$enable_disable_days       = $addon->get_option( 'enable_disable_days', $x );
$enable_disable_date_rules = 'disable';

$selectable_days = array();
$selected_items  = array();

$datepicker_args = apply_filters( 'yith_wapo_datepicker_options', array() );

if ( 'days' === $selectable_dates && $days_min >= -365 && $days_max > $days_min ) {
	for ( $z = $days_min; $z < $days_max; $z++ ) {
		$selectable_days[] = date( 'j-n-Y', strtotime( '+' . $z . ' day' ) );
	}
} elseif ( 'date' === $selectable_dates && '' !== $date_min && '' !== $date_max ) {

	$z                   = 0;
	$selectable_date_min = date( 'j-n-Y', strtotime( $date_min ) );
	$selectable_date_max = date( 'j-n-Y', strtotime( $date_max ) );
    $selectable_days[] = $selectable_date_min;

    if ( $selectable_date_min !== $selectable_date_max ) { // If min and max dates are not the same dates.
        while ( ++$z ) {
            $calculated_date  = date( 'j-n-Y', strtotime( $date_min . ' +' . $z . ' day' ) );
            $selectable_days[] = $calculated_date;
            if ( $calculated_date === $selectable_date_max ) {
                break;
            }
        }
    }
}

if ( 'yes' === $enable_disable_days ) {
	// rules.
	$enable_disable_date_rules = $addon->get_option( 'enable_disable_date_rules', $x, 'enable' );
	$date_rules_count          = count( (array) $addon->get_option( 'date_rule_what', $x ) );
	for ( $y = 0; $y < $date_rules_count; $y++ ) {
		$date_rule_what     = isset( $addon->get_option( 'date_rule_what', $x )[ $y ] ) ? $addon->get_option( 'date_rule_what', $x )[ $y ] : '';
		$date_rule_days     = isset( $addon->get_option( 'date_rule_value_days', $x, '' )[ $y ] ) ? $addon->get_option( 'date_rule_value_days', $x, '' )[ $y ] : '';
		$date_rule_daysweek = isset( $addon->get_option( 'date_rule_value_daysweek', $x, '' )[ $y ] ) ? $addon->get_option( 'date_rule_value_daysweek', $x, '' )[ $y ] : array();
		$date_rule_months   = isset( $addon->get_option( 'date_rule_value_months', $x, '' )[ $y ] ) ? $addon->get_option( 'date_rule_value_months', $x, '' )[ $y ] : array();
		$date_rule_years    = isset( $addon->get_option( 'date_rule_value_years', $x, '' )[ $y ] ) ? $addon->get_option( 'date_rule_value_years', $x, '' )[ $y ] : array();

		if ( 'days' === $date_rule_what ) {
			$selected_items[ $date_rule_what ][] = $date_rule_days;
		} elseif ( 'daysweek' === $date_rule_what ) {
			$selected_items[ $date_rule_what ][] = $date_rule_daysweek;
		} elseif ( 'months' === $date_rule_what ) {
			$selected_items[ $date_rule_what ][] = $date_rule_months;
		} elseif ( 'years' === $date_rule_what ) {
			$selected_items[ $date_rule_what ][] = $date_rule_years;
		}
	}
}

if( isset( $selected_items ) && 'firstavl' === $default_date ) {

    $default_selected_items_args = array(
        'daysweek' => array(),
        'days'     => array(),
        'months'   => array(),
        'years'    => array(),
    );

    $date_rules_default_date = apply_filters( 'yith_wapo_date_rules_default_date', array_merge( $default_selected_items_args, $selected_items ) );

    // Combine all rules - start
    if( ! empty( $date_rules_default_date['daysweek'] )  ) {
        $combine = array();
        foreach ( $date_rules_default_date['daysweek'] as $key => $value ) {
            $combine = array_merge( $combine, $value );
        }
        if( ! empty( $combine ) ) {
            $date_rules_default_date['daysweek'] = $combine;
        }
    }
    if( ! empty( $date_rules_default_date['months'] )  ) {
        $combine = array();
        foreach ( $date_rules_default_date['months'] as $key => $value ) {
            $combine = array_merge( $combine, $value );
        }
        if( ! empty( $combine ) ) {
            $date_rules_default_date['months'] = $combine;
        }
    }
    if( ! empty( $date_rules_default_date['years'] )  ) {
        $combine = array();
        foreach ( $date_rules_default_date['years'] as $key => $value ) {
            $combine = array_merge( $combine, $value );
        }
        if( ! empty( $combine ) ) {
            $date_rules_default_date['years'] = $combine;
        }
    }
    // Combine all rules - end

    $lat_day_end_year = strtotime($end_year . '-12-31' );
    $current_day      = strtotime('+' . $days_min . 'days' );

    if ( 'date' === $selectable_dates && ! empty( $date_min ) ) {
        $current_day = strtotime( $date_min );
    }

    if ( 'disable' === $enable_disable_date_rules ) {
        do {
            $current_date = gmdate( 'Y-m-d', $current_day );
            if ( ! in_array( $current_date, $date_rules_default_date['days'], true ) && ( ! in_array( absint( gmdate( 'N', $current_day ) ),  $date_rules_default_date['daysweek']  ) ) && ! in_array( absint( gmdate( 'n', $current_day ) ),  $date_rules_default_date['months']  ) && ! in_array( absint( gmdate( 'Y', $current_day ) ),  $date_rules_default_date['years']  ) ) {
                $default_date = date( $date_format, strtotime( $current_date ) );
                break;
            } else {
                $current_day = strtotime( '+1 day', $current_day );
            }
        } while ( $current_day < $lat_day_end_year );
    } else {
        do {
            $current_date = gmdate( 'Y-m-d', $current_day );
            if ( in_array( $current_date, $date_rules_default_date['days'], true ) || ( in_array( absint( gmdate( 'N', $current_day ) ),  $date_rules_default_date['daysweek']  ) ) || in_array( absint( gmdate( 'n', $current_day ) ),  $date_rules_default_date['months']  ) || in_array( absint( gmdate( 'Y', $current_day ) ),  $date_rules_default_date['years']  ) ) {
                $default_date = date( $date_format, strtotime( $current_date ) );
                break;
            } else {
                $current_day = strtotime( '+1 day', $current_day );
            }
        } while ( $current_day < $lat_day_end_year );
    }
}

if ( ! empty( $selected_items ) ) {
	$selected_items = wp_json_encode( $selected_items );
}

$datepicker_timeout = apply_filters( 'yith_wapo_allow_timeout_for_datepickers', false );

$time_data = wc_string_to_bool( $show_time_selector ) ? $addon->create_availability_time_array( $x ) : array();

/** NEW */

$params = array(
    'start_year'                 => $start_year,
    'end_year'                   => $end_year,
    'default_date'               => $default_date,
    'date_format'                => $date_format_js,
    'selectable_days_opt'        => $selectable_dates,
    'selectable_days'            => $selectable_days,
    'selected_items'             => $selected_items,
    'enable_disable_date_rules'  => $enable_disable_date_rules,
    'show_time_selector'         => wc_string_to_bool( $show_time_selector ),
    'time_data'                  => $time_data,
    'additional_opts'            => $datepicker_args
);

$params = wp_json_encode( $params );

$default_date = ! empty( $default_date ) && ! empty( $time_data ) && isset( $time_data[0] )
    ? $default_date . ' ' . $time_data[0] : $default_date;

?>

<div id="yith-wapo-option-<?php echo esc_attr( $addon->id ); ?>-<?php echo esc_attr( $x ); ?>" class="yith-wapo-option">

	<div class="label <?php echo wp_kses_post( ! empty( $addon_image_position ) ? 'position-' . $addon_image_position : '' ); ?>">
		<div class="option-container">
			<!-- ABOVE / LEFT IMAGE -->
			<?php
			if ( 'above' === $addon_options_images_position || 'left' === $addon_options_images_position ) {
                $addon->print_option_image( compact( 'addon', 'x', 'option_image', 'addon_image_position' ) );
            }
			?>
			<label for="yith-wapo-<?php echo esc_attr( $addon->id ); ?>-<?php echo esc_attr( $x ); ?>">
				<span class="option-label">
					<?php echo ! $hide_option_label ? wp_kses_post( $addon->get_option( 'label', $x ) ) : ''; ?>
				</span>
				<span class="option-price">
					<?php echo ! $hide_option_prices ? wp_kses_post( $addon->get_option_price_html( $x, $currency, $product ) ) : ''; ?>
				</span>
				<?php echo $required && ( ! $hide_option_label && ! empty( $addon->get_option( 'label', $x ) ) ) ? '<span class="required">*</span>' : ''; ?>
			</label>
			<!-- UNDER / RIGHT IMAGE -->
			<?php
			if ( 'under' === $addon_options_images_position || 'right' === $addon_options_images_position ) {
                $addon->print_option_image( compact( 'addon', 'x', 'option_image', 'addon_image_position' ) );
            }
			?>
		</div>

		<div class="date-container">
			<span id="temp-time" class="temp-time" style="display: none;"></span>

			<input type="text"
			       id="yith-wapo-<?php echo esc_attr( $addon->id ); ?>-<?php echo esc_attr( $x ); ?>"
			       class="yith_wapo_date datepicker yith-wapo-option-value"
			       name="yith_wapo[][<?php echo esc_attr( $addon->id . '-' . $x ); ?>]"
			       value="<?php echo esc_attr( $default_date ); ?>"
                   data-default-price="<?php echo esc_attr( $default_price ); ?>"
                    <?php
                    if ( $default_price > 0 ) {
                        ?>
                        data-default-sale-price="<?php echo esc_attr( $default_sale_price ); ?>"
                        <?php
                    }
                    ?>
			       data-price="<?php echo esc_attr( $price ); ?>"
				<?php
				if ( $price > 0 ) {
					?>
					data-price-sale="<?php echo esc_attr( $price_sale ); ?>"
					<?php
				}
				?>
				   data-price-type="<?php echo esc_attr( $price_type ); ?>"
				   data-price-method="<?php echo esc_attr( $price_method ); ?>"
				   data-first-free-enabled="<?php echo esc_attr( $first_options_selected ); ?>"
				   data-first-free-options="<?php echo esc_attr( $first_free_options ); ?>"
				   data-addon-id="<?php echo esc_attr( $addon->id ); ?>"
                   data-params="<?php echo esc_attr( $params ) ?>"
				<?php echo $required ? 'required' : ''; ?>
				   style="<?php echo esc_attr( $options_width_css ); ?>"
				   readonly
			>
		</div>

	</div>

	<?php if ( $addon->get_option( 'tooltip', $x ) !== '' ) : ?>
		<span class="tooltip position-<?php echo esc_attr( get_option( 'yith_wapo_tooltip_position' ) ); ?>">
			<span><?php echo wp_kses_post( $addon->get_option( 'tooltip', $x ) ); ?></span>
		</span>
	<?php endif; ?>

	<?php if ( '' !== $option_description ) : ?>
		<p class="description">
			<?php echo wp_kses_post( $option_description ); ?>
		</p>
	<?php endif; ?>
	<!-- Sold individually -->
	<?php if ( 'yes' === $sell_individually ) : ?>
		<input type="hidden" name="yith_wapo_sell_individually[<?php echo esc_attr( $addon->id . '-' . $x ); ?>]" value="yes">
	<?php endif; ?>

</div>
