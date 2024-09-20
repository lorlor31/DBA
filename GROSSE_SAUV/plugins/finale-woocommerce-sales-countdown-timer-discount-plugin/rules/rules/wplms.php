<?php
defined( 'ABSPATH' ) || exit;

class WCCT_Rule_WPLMS_Course extends WCCT_Rule_Base {

	public function __construct() {

		parent::__construct( 'wplms_course' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => 'is',
			'notin' => 'is not',
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result = array();
		$args   = array(
			'numberposts' => - 1,
			'post_type'   => 'course',
			'fields'      => 'ids',
		);

		$post_ids = get_posts( $args );

		if ( empty( $post_ids ) ) {
			return $result;
		}

		foreach ( $post_ids as $post ) {
			$result[ $post ] = get_the_title( $post );
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

		$in     = is_array( $rule_data['condition'] ) ? in_array( $post->ID, $rule_data['condition'] ) : false;
		$result = 'in' === $rule_data['operator'] ? $in : ! $in;

		return $this->return_is_match( $result, $rule_data );
	}

}

class WCCT_Rule_WPLMS_Quiz extends WCCT_Rule_Base {

	public function __construct() {

		parent::__construct( 'wplms_quiz' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => 'is',
			'notin' => 'is not',
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result   = array();
		$args     = array(
			'numberposts' => - 1,
			'post_type'   => 'quiz',
			'fields'      => 'ids',
		);
		$post_ids = get_posts( $args );

		if ( empty( $post_ids ) ) {
			return $result;
		}

		foreach ( $post_ids as $post ) {
			$result[ $post ] = get_the_title( $post );
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

		$in     = is_array( $rule_data['condition'] ) ? in_array( $post->ID, $rule_data['condition'] ) : false;
		$result = 'in' === $rule_data['operator'] ? $in : ! $in;

		return $this->return_is_match( $result, $rule_data );
	}

}

class WCCT_Rule_WPLMS_Unit extends WCCT_Rule_Base {

	public function __construct() {

		parent::__construct( 'wplms_unit' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => 'is',
			'notin' => 'is not',
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result   = array();
		$args     = array(
			'numberposts' => - 1,
			'post_type'   => 'unit',
			'fields'      => 'ids',
		);
		$post_ids = get_posts( $args );

		if ( empty( $post_ids ) ) {
			return $result;
		}

		foreach ( $post_ids as $post ) {
			$result[ $post ] = get_the_title( $post );
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

		$in     = is_array( $rule_data['condition'] ) ? in_array( $post->ID, $rule_data['condition'] ) : false;
		$result = 'in' === $rule_data['operator'] ? $in : ! $in;

		return $this->return_is_match( $result, $rule_data );
	}

}

class WCCT_Rule_WPLMS_Question extends WCCT_Rule_Base {

	public function __construct() {

		parent::__construct( 'wplms_question' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => 'is',
			'notin' => 'is not',
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result   = array();
		$args     = array(
			'numberposts' => - 1,
			'post_type'   => 'question',
			'fields'      => 'ids',
		);
		$post_ids = get_posts( $args );

		if ( empty( $post_ids ) ) {
			return $result;
		}

		foreach ( $post_ids as $post ) {
			$result[ $post ] = get_the_title( $post );
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

		$in     = is_array( $rule_data['condition'] ) ? in_array( $post->ID, $rule_data['condition'] ) : false;
		$result = 'in' === $rule_data['operator'] ? $in : ! $in;

		return $this->return_is_match( $result, $rule_data );
	}

}

class WCCT_Rule_WPLMS_Assignment extends WCCT_Rule_Base {

	public function __construct() {

		parent::__construct( 'wplms_assignment' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => 'is',
			'notin' => 'is not',
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result   = array();
		$args     = array(
			'numberposts' => - 1,
			'post_type'   => 'wplms-assignment',
			'fields'      => 'ids',
		);
		$post_ids = get_posts( $args );

		if ( empty( $post_ids ) ) {
			return $result;
		}

		foreach ( $post_ids as $post ) {
			$result[ $post ] = get_the_title( $post );
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

		$in     = is_array( $rule_data['condition'] ) ? in_array( $post->ID, $rule_data['condition'] ) : false;
		$result = 'in' === $rule_data['operator'] ? $in : ! $in;

		return $this->return_is_match( $result, $rule_data );
	}

}