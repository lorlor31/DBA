<?php


class WCCT_Rule_Single_Product_Cat_Tax extends WCCT_Rule_Base {

	public function __construct() {

		parent::__construct( 'single_product_cat_tax' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'notin' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result = array();

		$terms = get_terms( 'product_cat', array(
			'hide_empty' => false,
		) );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$result[ $term->term_id ] = $term->name;
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $productID ) {
		$result   = $rule_data['operator'] == 'in' ? false : true;
		$wp_query = WCCT_Common::$wcct_query;
		if ( ! is_object( $wp_query ) ) {
			return $this->return_is_match( $result, $rule_data );
		}

		$get_tax = $wp_query->get_queried_object();

		if ( ! is_object( $get_tax ) || ! $get_tax instanceof WP_Term ) {
			return $this->return_is_match( $result, $rule_data );
		}
		$term_id = $get_tax->term_id;

		if ( isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {
			$in     = (bool) ( in_array( $term_id, $rule_data['condition'] ) );
			$result = $rule_data['operator'] == 'in' ? $in : ! $in;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}


class WCCT_Rule_Single_Product_Tags_Tax extends WCCT_Rule_Base {


	public function __construct() {

		parent::__construct( 'single_product_tags_tax' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'notin' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result = array();

		$terms = get_terms( 'product_tag', array(
			'hide_empty' => false,
		) );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$result[ $term->term_id ] = $term->name;
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $productID ) {

		$result = false;

		$wp_query = WCCT_Common::$wcct_query;

		if ( ! is_object( $wp_query ) ) {
			return $this->return_is_match( $result, $rule_data );
		}
		$get_tax = $wp_query->get_queried_object();

		$term_id = $get_tax->term_id;

		if ( isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {
			$in     = (bool) ( in_array( $term_id, $rule_data['condition'] ) );
			$result = $rule_data['operator'] == 'in' ? $in : ! $in;
		}

		return $this->return_is_match( $result, $rule_data );
	}


}

class WCCT_Rule_Single_Posts_Tags_Tax extends WCCT_Rule_Base {


	public function __construct() {

		parent::__construct( 'single_posts_tags_tax' );
	}

	public function get_possible_rule_operators() {

		$operators = array(
			'in'    => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'notin' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result = array();
		$tags   = get_terms( 'post_tag', array( 'hide_empty' => false ) );

		if ( $tags && ! is_wp_error( $tags ) ) {
			foreach ( $tags as $tag ) {
				$result[ $tag->term_id ] = $tag->name;
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $post_id ) {

		$result = false;

		$wp_query = WCCT_Common::$wcct_query;

		if ( ! is_object( $wp_query ) ) {
			return $this->return_is_match( $result, $rule_data );
		}
		$get_tax = $wp_query->get_queried_object();

		$term_id = $get_tax->term_id;

		if ( isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {
			$in     = (bool) ( in_array( $term_id, $rule_data['condition'] ) );
			$result = $rule_data['operator'] == 'in' ? $in : ! $in;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class WCCT_Rule_Selected_Posts_Cats_Pages extends WCCT_Rule_Base {


	public function __construct() {

		parent::__construct( 'selected_posts_cats_pages' );
	}

	public function get_possible_rule_operators() {

		$operators = array(
			'in'    => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'notin' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result = array();
		$terms  = get_terms( 'category', array( 'hide_empty' => false ) );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$result[ $term->term_id ] = $term->name;
			}

			return $result;
		}
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $post_id = 0 ) {
		$result = false;
		if ( empty( $post_id ) && is_singular() ) {
			$post_id = get_the_ID();
		}

		if ( empty( $post_id ) || empty( $rule_data['condition'] ) ) {
			return $this->return_is_match( $result, $rule_data );
		}
		$post_cats     = wp_get_post_categories( $post_id, array( 'fields' => 'ids' ) );
		$selected_cats = $rule_data['condition'];
		$intersection  = array_intersect( $post_cats, $selected_cats );

		if ( empty( $intersection ) ) {
			return $this->return_is_match( $result, $rule_data );

		}

		return $this->return_is_match( true, $rule_data );
	}


}

class WCCT_Rule_Selected_Posts_Tags_Pages extends WCCT_Rule_Base {


	public function __construct() {

		parent::__construct( 'selected_posts_tags_pages' );
	}

	public function get_possible_rule_operators() {

		$operators = array(
			'in'    => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'notin' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result = array();
		$tags   = get_terms( 'post_tag', array( 'hide_empty' => false ) );

		if ( $tags && ! is_wp_error( $tags ) ) {
			foreach ( $tags as $tag ) {
				$result[ $tag->term_id ] = $tag->name;
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $post_id = 0 ) {
		$result = false;

		if ( empty( $post_id ) && is_singular() ) {
			$post_id = get_the_ID();
		}

		if ( empty( $post_id ) || empty( $rule_data['condition'] ) ) {
			return $this->return_is_match( $result, $rule_data );
		}

		$post_tags     = wp_get_post_tags( $post_id, array( 'fields' => 'ids' ) );
		$selected_tags = $rule_data['condition'];
		$intersection  = array_intersect( $post_tags, $selected_tags );

		if ( empty( $intersection ) ) {
			return $this->return_is_match( $result, $rule_data );
		}

		return $this->return_is_match( true, $rule_data );

	}


}

class WCCT_Rule_Single_Posts_Cat_Tax extends WCCT_Rule_Base {


	public function __construct() {

		parent::__construct( 'single_posts_cat_tax' );
	}

	public function get_possible_rule_operators() {

		$operators = array(
			'in'    => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'notin' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result = array();

		$terms = get_terms( 'category', array( 'hide_empty' => false ) );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$result[ $term->term_id ] = $term->name;
			}

			return $result;
		}
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $post_id ) {

		$result = false;

		$wp_query = WCCT_Common::$wcct_query;

		if ( ! is_object( $wp_query ) ) {
			return $this->return_is_match( $result, $rule_data );
		}
		$get_tax = $wp_query->get_queried_object();

		$term_id = $get_tax->term_id;

		if ( isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {
			$in     = (bool) ( in_array( $term_id, $rule_data['condition'] ) );
			$result = $rule_data['operator'] == 'in' ? $in : ! $in;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class WCCT_Rule_Single_Specific_Post extends WCCT_Rule_Base {


	public function __construct() {

		parent::__construct( 'single_specific_post' );
	}

	public function get_possible_rule_operators() {

		$operators = array(
			'in'    => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'notin' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result      = array();
		$args        = array(
			'numberposts' => - 1,
			'post_type'   => 'post',
			'post_status' => 'publish',
		);
		$postr_types = get_posts( $args );

		foreach ( $postr_types as $post ) {
			$result[ $post->ID ] = $post->post_title;
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $post_id ) {
		global $post;
		if ( ! $post instanceof WP_Post ) {
			return false;
		}
		$result = false;

		if ( $post->ID && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {

			$in     = in_array( $post->ID, $rule_data['condition'] );
			$result = 'in' === $rule_data['operator'] ? $in : ! $in;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}


