<?php

if(!defined('ABSPATH')) {
	exit;
}

if(!class_exists('WXP_Updater')){
	class WXP_Updater {

		private $plugin_data = array();

		function __construct($plugin_slug,$plugin_file){
			$this->plugin_data = $this->wxp_plugin_data($plugin_slug,$plugin_file);
			add_filter('plugins_api_args', array($this,'wxp_plugins_api_args'),10,2);
			add_filter('plugins_api',array($this,'check_info'),10,3);
			add_filter('http_request_args',array($this,'http_request_args'),10,2);
			add_filter('site_transient_update_plugins',array($this,'wxp_update_check'),999,1);
			add_action('upgrader_process_complete',array($this,'wxp_plugin_updated'),10,2);
		}

		function wxp_plugin_data($slug,$file){
			if(false === ($data = get_transient($slug.'_data'))){
				$plug = $this->wxp_plugin_get($slug,$file);
				$data = array(
					'name'=> isset($plug['Name']) ? $plug['Name'] : '',
					'slug'=> $slug!='' ? $slug : '',
					'version'=> isset($plug['Version']) ? $plug['Version'] : '',
					'path'=> isset($plug['PluginURI']) ? $plug['PluginURI'].'/wc_update.php' : '',
					'licence' => get_option('_'.$slug.'_licence_key'),
					'email' => get_option('_'.$slug.'_email'),
					'url'=> site_url(),
					'file'=> $file,
					'plugin_url'=> isset($plug['PluginURI']) ? $plug['PluginURI'] : ''
				);
				set_transient($slug.'_data',$data,24*3600);
			}
			return $data;
		}

		function wxp_plugins_api_args($args,$action){
			if(isset($args->slug) && $args->slug === $this->plugin_data['slug'] && $action=='plugin_information'){
				$args->fields = array();
			}
			return $args;
		}

		function check_info($false, $action, $arg){
			if(isset($args->slug) && $arg->slug === $this->plugin_data['slug']){
				$information = $this->getRemote_information();
				return $information;
			}
			return false;
		}

		function http_request_args( $args, $url ) {
			if(strpos($url,'https://') !== false && strpos($url,'wc_update')){
				$args['sslverify'] = true;
			}
			return $args;
		}

		function wxp_update_check($transient){
			$transient = $this->check_update($transient);
			return $transient;
		}

		function wxp_plugin_updated($upgrader_object,$options){
			if(isset($options['action']) && $options['action'] == 'update' && $options['type']==='plugin'){
				delete_transient($this->plugin_data['slug'].'_data');
				delete_transient($this->plugin_data['slug']);
			}
		}

		function wxp_get_plugin_log(){
			if(false === ($obj = get_transient($this->plugin_data['slug']))){
				$remote_version = $this->getRemote_version();
				$license = $this->getRemote_license();
				$license = maybe_unserialize($license);
				$obj = new stdClass();
				$obj->id = $this->plugin_data['slug'];
				$obj->slug = $this->plugin_data['slug'];
				$obj->plugin = $this->plugin_data['file'];
				$obj->new_version = $remote_version;
				$obj->url = $this->plugin_data['plugin_url'];
				$obj->package = isset($license->file_url) ? $license->file_url : '';
				$obj->tested = isset($license->tested) ? $license->tested : '';
				$obj->compatibility = new stdClass();
				set_transient($this->plugin_data['slug'],$obj,12*3600);
			}
			return $obj;
		}

		function check_update($transient){
			$check =  $this->wxp_get_plugin_log();
			if(isset($check->new_version) && isset($this->plugin_data['version'])){
				if((int)version_compare($this->plugin_data['version'],$check->new_version,'<') && isset($transient->response)){
					$transient->response[$this->plugin_data['file']] = $check;
				}
			} 
			return $transient; 
		}

		function wxp_plugin_get($slug,$file,$key=''){
			if(!function_exists('get_plugins')){
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$plugin_folder = get_plugins('/'.plugin_basename(dirname($file)));
			$plugin_file   = basename($file);
			if($key != ''){
				return $plugin_folder[$plugin_file][$key];
			}
			return $plugin_folder[$plugin_file];
		}

		function getRemote_version(){
			$request = wp_remote_post($this->plugin_data['path'],array(
				'method'=>'POST',
				'timeout'=>30,
				'body'=>array(
					'action'=>'version',
					'slug'=>$this->plugin_data['slug'],
					'email'=>$this->plugin_data['email'],
					'key'=>$this->plugin_data['licence'],
					'url'=>$this->plugin_data['url']
				)
			));
			if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
				return $request['body'];
			}
			return false;
		}

		function getRemote_information(){
			$request = wp_remote_post($this->plugin_data['path'], array(
				'method'=>'POST',
				'timeout'=>30,
				'body' => array('action'=>'info','slug'=>$this->plugin_data['slug'])
			));
			if(!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200){
				return unserialize($request['body']);
			}
			return false;
		}

		function getRemote_license(){
			$request = wp_remote_post($this->plugin_data['path'], array(
				'method'=>'POST',
				'timeout'=>30,
				'body' => array(
					'action'=>'license',
					'email'=>$this->plugin_data['email'],
					'key'=>$this->plugin_data['licence'],
					'url'=>$this->plugin_data['url'],
					'slug'=>$this->plugin_data['slug'],
				)
			));
			if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
				return $request['body'];
			}
			return false;
		}

	}
}
