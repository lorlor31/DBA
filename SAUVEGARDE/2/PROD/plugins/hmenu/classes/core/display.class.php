<?php
	
	#PLUGIN DISPLAY MANAGEMENT
	class hmenu_display{
		
		#CLASS VARS
		private $plugin_dir;
		
		#CONSTRUCT
		public function __construct($plugin_dir){
			//define plugin directory path
			$this->plugin_dir = $plugin_dir;
		}
		
		#GET DIRECTORY
		public function hmenu_get_directory(){
			return $this->plugin_dir;
		}
		
		#OUTPUT ADMIN PAGE
		public function hmenu_output_admin($plugin_helper,$plugin_name,$plugin_friendly_name,$plugin_version,$plugin_url,$first_release,$last_updated,$plugin_friendly_description){
			//load global helper
			global $hmenu_helper;
			//load the plugin core
			include($this->hmenu_get_directory() .'/panels/panel.core.php');
		}
		
		#OUTPUT FRONT-END PAGE
		public function hmenu_output_frontend($content){
			//load global helper
			global $hmenu_helper;
			//start output buffer
			$this->hmenu_start_output_buffer();
			//write content
			echo $content;
			//stop buffering and return content
			return $hmenu_helper->hmenu_minify($this->hmenu_stop_output_buffer()); //output is minified
		}
		
		#START OUTPUT BUFFER
		private function hmenu_start_output_buffer(){
			ob_start();
		}
		
		#STOP OUTPUT BUFFER
		private function hmenu_stop_output_buffer(){
			$output = ob_get_clean();
			return $output;
			ob_end_flush();
		}
		
	}