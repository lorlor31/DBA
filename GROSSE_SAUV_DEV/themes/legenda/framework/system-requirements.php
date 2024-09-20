<?php if ( ! defined( 'ABSPATH' ) ) exit( 'No direct script access allowed' );

// **********************************************************************// 
// ! System Requirements
// **********************************************************************//

class Etheme_System_Requirements{

	// ! Requirements
	public $requirements = array(		 // ! Defaults
		'php' 			  	=> '7.4', 	 // ! 5.6
		'wp' 			  	=> '3.9', 	 // ! 3.9
		'ssl_version' 	  	=> '1.0', 	 // ! 1.0
		'wp_uploads' 	  	=> true, 	 // ! true
		'memory_limit'    	=> '128M', 	 // ! 128M
		'time_limit' 	  	=> array(
			'min' => 30,				 // ! 30
			'req' => 60					 // ! 60
		), 	 
		'max_input_vars'  	=> array(
			'min' => 1000,				 // ! 1000
			'req' => 2000				 // ! 2000
		), 	 
		'upload_filesize' 	=> '15M', 	 // ! 10M
		'filesystem'	  	=> 'direct', // ! direct
		'wp_remote_get'	  	=> true, 	 // ! true
		'gzip' 				=> true 	 // ! true
	);

	// ! Let's think that all alright
	public $result = true;

	// ! Just leave it here
	function __construct(){
	}

	// ! Return requirements
	public function get_requirements() {
		return $this->requirements;
	}

	// ! Return icon class, set result
	public function check($type) {
		if ( $type ) {
			return 'yes';
		} else {
			$this->result = false;
			return 'warning';
		}
		return $type;
	}

	// ! Return result. Note call it only after "html" function !
	public function result() {
		return $this->result;
	}

	// ! Return system information
	public function get_system() {
		global $wp_version;

		$system = array(
			'php' => 			PHP_VERSION,
			'wp' => 			$wp_version,
			'curl_version' 		=> ( function_exists( 'curl_version' ) ) ? curl_version() : false,
			'ssl_version'		=> '',
			'wp_uploads' 		=> wp_get_upload_dir(),
			'upload_filesize' 	=> ini_get( 'upload_max_filesize' ),
			'memory_limit' 		=> ini_get( 'memory_limit' ),
			'time_limit' 		=> ini_get( 'max_execution_time' ),
			'max_input_vars' 	=> ini_get( 'max_input_vars' ),
			'filesystem' 		=> get_filesystem_method(),
			'wp_remote_get' 	=> function_exists( 'wp_remote_get' ),
			'gzip' 				=> is_callable( 'gzopen' )
		);

		if ( isset( $system['curl_version']['ssl_version'] ) ) {
			$system['ssl_version'] = $system['curl_version']['ssl_version'];
			$system['ssl_version'] = preg_replace( '/[^.0-9]/', '', $system['ssl_version'] );
		} else {
			$system['ssl_version'] = 'undefined';
		}

		return $system;
	}

	// ! Show html result
	public function html() {
		$system = $this->get_system();

		echo '<table class="system-requirements">';
			printf(
				'<thead><th class="requirement-headings environment">%s</th>
				<th>%s</th>
				<th>%s</th></thead>',
				esc_html__( 'Environment', 'legenda' ),
				esc_html__( 'Requirement', 'legenda' ),
				esc_html__( 'System', 'legenda' )
			);

			printf(
				'<tr class="wp-system %s">
					<td>' . esc_html__( 'WP File System:', 'legenda' ) . '</td>
					<td>%s</td>
					<td>%s<span class="dashicons dashicons-%s "></span></td>
				</tr>',
				( $system['filesystem'] != $this->requirements['filesystem'] ) ? 'warning' : '',
				$this->requirements['filesystem'],
				$system['filesystem'],
				( $system['filesystem'] === $this->requirements['filesystem'] ) ? $this->check(true) : $this->check(false)
			);

			printf(
				'<tr class="php-version">
					<td>' . esc_html__( 'PHP version:', 'legenda' ) . '</td>
					<td>%s</td>
					<td>%s<span class="dashicons dashicons-%s %s"></span></td>
				</tr>',
				$this->requirements['php'],
				$system['php'],
				( version_compare( $system['php'], $this->requirements['php'], '>=' ) ) ? $this->check(true) : $this->check(false),
				( version_compare( $system['php'], $this->requirements['php'], '==' ) ) ? 'min' : ''
			);

			printf(
				'<tr class="wp-version">
					<td>' . esc_html__( 'WordPress version:', 'legenda' ) . '</td>
					<td>%s</td>
					<td>%s<span class="dashicons dashicons-%s %s"></span></td>
				</tr>',
				$this->requirements['wp'],
				$system['wp'],
				( version_compare( $system['wp'], $this->requirements['wp'], '>=' ) ) ? $this->check(true) : $this->check(false),
				( version_compare( $system['wp'], $this->requirements['wp'], '==' ) ) ? 'min' : ''
			);

			printf(
				'<tr class="memory-limit">
					<td>' . esc_html__( 'Memory limit:', 'legenda' ) . '</td>
					<td>%s</td>
					<td>%s<span class="dashicons dashicons-%s %s"></span></td>
				</tr>',
				$this->requirements['memory_limit'],
				$system['memory_limit'],
				( wp_convert_hr_to_bytes( $system['memory_limit'] ) >= wp_convert_hr_to_bytes( $this->requirements['memory_limit'] ) ) ? $this->check(true) : $this->check(false),
				( wp_convert_hr_to_bytes( $system['memory_limit'] ) === wp_convert_hr_to_bytes( $this->requirements['memory_limit'] ) ) ? 'min' : ''
			);

			printf(
				'<tr class="execution-time %s %s">
					<td>' . esc_html__( 'Max execution time:', 'legenda' ) . '</td>
					<td>min (%s-%s)</td>
					<td>%s<span class="dashicons dashicons-%s %s"></td>
				</tr>',
				( $system['time_limit'] >= $this->requirements['time_limit']['req'] ) ? '' : 'warning',
				( (int)$system['time_limit'] === (int)$this->requirements['time_limit']['min'] ) ? 'min' : '',
				$this->requirements['time_limit']['min'],
				$this->requirements['time_limit']['req'],
				$system['time_limit'],
				( $system['time_limit'] >= $this->requirements['time_limit']['min'] ) ? $this->check(true) : $this->check(false),
				( $system['time_limit'] >= $this->requirements['time_limit']['req'] ) ? '' : 'dashicons-warning'
			);

			printf(
				'<tr class="input-vars %s %s">
					<td>' . esc_html__( 'Max input vars:', 'legenda' ) . '</td>
					<td>min (%s-%s)</td>
					<td>%s<span class="dashicons dashicons-%s %s"></span></td>
				</tr>',
				( $system['max_input_vars'] >= $this->requirements['max_input_vars']['req'] ) ? '' : 'warning',
				( (int)$system['max_input_vars'] === (int)$this->requirements['max_input_vars']['min'] ) ? 'min' : '',
				$this->requirements['max_input_vars']['min'],
				$this->requirements['max_input_vars']['req'],
				$system['max_input_vars'],
				( $system['max_input_vars'] >= ($this->requirements['max_input_vars']['min']) ) ? $this->check(true) : $this->check(false),
				( $system['max_input_vars'] >= $this->requirements['max_input_vars']['req'] ) ? '' : 'dashicons-warning'
			);

			printf(
				'<tr class="filesize">
					<td>' . esc_html__( 'Upload max filesize:', 'legenda' ) . '</td>
					<td>%s</td>
					<td>%s<span class="dashicons dashicons-%s %s"></span></td>
				</tr>',
				$this->requirements['upload_filesize'],
				$system['upload_filesize'],
				( wp_convert_hr_to_bytes( $system['upload_filesize'] ) >= wp_convert_hr_to_bytes( $this->requirements['upload_filesize'] ) ) ? $this->check(true) : $this->check(false),
				( (int)wp_convert_hr_to_bytes( $system['upload_filesize'] ) === (int)wp_convert_hr_to_bytes( $this->requirements['upload_filesize'] ) ) ? 'min' : ''
			);

			printf(
				'<tr class="uploads-folder">
					<td>' . esc_html__( '../Uploads folder:', 'legenda' ) . '</td>
					<td>%s</td>
					<td>%s<span class="dashicons dashicons-%s "></span></td>
				</tr>',
				'writable',
				( wp_is_writable( $system['wp_uploads']['basedir'] ) === $this->requirements['wp_uploads'] ) ? 'writable' : 'unwritable',
				( wp_is_writable( $system['wp_uploads']['basedir'] ) === $this->requirements['wp_uploads'] ) ? $this->check(true) : $this->check(false)
			);

			printf(
				'<tr class="ssl-version">
					<td>' . esc_html__( 'OpenSSL version:', 'legenda' ) . '</td>
					<td>%s</td>
					<td>%s<span class="dashicons dashicons-%s %s"></span></td>
				</tr>',
				$this->requirements['ssl_version'],
				$system['ssl_version'],
				( version_compare( $system['ssl_version'], $this->requirements['ssl_version'], '>=' ) ) ? $this->check(true) : $this->check(false),
				( version_compare( $system['ssl_version'], $this->requirements['ssl_version'], '==' ) ) ? 'min' : ''
			);

			printf(
				'<tr class="gzip-compression">
					<td>' . esc_html__( 'GZIP compression:', 'legenda' ) . '</td>
					<td>%s</td>
					<td>%s<span class="dashicons dashicons-%s "></span></td>
				</tr>',
				'enable',
				( $system['gzip'] == $this->requirements['gzip'] ) ? 'enable' : 'disable',
				( $system['gzip'] == $this->requirements['gzip'] ) ? $this->check(true) : $this->check(false)
			);

			printf(
				'<tr class="function-wp_remote_get">
					<td>wp_remote_get( ):</td>
					<td>%s</td>
					<td>%s<span class="dashicons dashicons-%s "></span></td>
				</tr>',
				( $this->requirements['wp_remote_get'] ) ? 'enable' : 'disable',
				( $system['wp_remote_get'] == $this->requirements['wp_remote_get'] ) ? 'enable' : 'disable',
				( $system['wp_remote_get'] == $this->requirements['wp_remote_get'] ) ? $this->check(true) : $this->check(false)
			);
		echo '</table>';
	}
}