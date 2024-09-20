<?php
	
	#PLUGIN HELPER
	class hmenu_helper{
		
		#CLASS VARS
		private $plugin_prefix;
		
		#CONSTRUCT
		public function __construct($plugin_prefix){
			$this->plugin_prefix = $plugin_prefix;
		}
		
		#GEN GUID
		public function hmenu_genGUID(){
			return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
				mt_rand(0, 0x0fff) | 0x4000,
				mt_rand(0, 0x3fff) | 0x8000,
				mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
			);
		}
		
		#ADMIN PANEL VIEW PLUGIN CHECK
		public function hmenu_onAdmin(){
			if(isset($_GET['page']) && stristr($_GET['page'],substr($this->plugin_prefix,0,-1))){ //test if currently on plugin admin page
				return true;
			}
			return false;
		}
		
		#MINIFY CODE
		public function hmenu_minify($string){
			$string = preg_replace('!/\*.*?\*/!s','', $string);
			$string = preg_replace('/\n\s*\n/',"\n", $string);
			$string = preg_replace('/[\n\r \t]/',' ', $string);
			$string = preg_replace('/ +/',' ', $string);
			$string = preg_replace('/;}/','}',$string);
			return $string;
		}
		
		#GET FRIENDLY DATE
		public function hmenu_friendly_date($date){
			return date('dMy',strtotime($date));
		}
		
		#FORCE INT
		public function hmenu_strtoint($string){
			return $string + 0;
		}
		
		#CHECK VERSION
		public function hmenu_checkVersion($string){
			if(preg_match('/\d+(\.\d+)+/', $string)){
				return true;
			}
			return false;
		}
		
		#CHECK STRING
		public function hmenu_checkString($string){
			if(strlen(trim($string)) >= 1){
				return true;
			}
			return false;
		}
		
		#CHECK EMAIL
		public function hmenu_checkEmail($string){
			if(preg_match('/.+@.+\.[a-z]/', $string)){
				return true;
			}  
			return false;
		}
		
		#CHECK INT
		public function hmenu_checkInt($string){
			if(is_numeric($string)){
				return true;
			}
			return false;
		}
		
		#CHECK CONTACT NUMBER
		public function hmenu_checkContactNumber($string){
			if(preg_match('/\d/', $string) && strlen($string) > 8){
				return true;
			}
			return false;
		}
		
		#CHECK PASSWORD
		public function hmenu_checkPassword($string){
			if(preg_match('/[A-Z]/',$string) && preg_match('/[a-z]/',$string) && preg_match('/[0-9]/',$string) && strlen($string) >= 8){ //uppercase, lowercase, number, length
				return true;
			}
			return false;
		}
		
		#CHECK DATE
		public function hmenu_checkDate($string){
			if(strtotime($string) !== false){
				return true;
			}
			return false;
		}
		
		#GET BLOG URL
		public function hmenu_get_blog_domain(){
			if(isset($_SERVER['HTTP_HOST'])){
				return $_SERVER['HTTP_HOST'];
			}
			return $_SERVER['SERVER_NAME'];
		}
		
	}