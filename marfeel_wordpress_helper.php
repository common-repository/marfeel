<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class MarfeelWordpressHelper
{
	const MARFEEL_OPTIONS = 'marfeel_options';

	static $instance = false;

	private function __construct() {

	}

	public static function getInstance() {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}

	public function request( $method, $url, $data = array() ) {
		$function = 'wp_remote_' . strtolower( $method );

		return $function( $url, $data );
	}

	function is_article() {
		$post = get_post();
		return ( is_singular('post') || is_page() ) && $post !== null && !empty(strip_tags(trim($post->post_content)));
	}

	function get_current_uri() {
		global $wp;
		$current_url = home_url( $wp->request );
		$structure = get_option( 'permalink_structure' );

		if ( ! empty( $structure ) ) {
			$current_url = user_trailingslashit( $current_url );
		}

		return $current_url;
	}

	function get_option_value($option_name) {
		$options = get_option(MARFEEL_OPTIONS, array());
		$option = isset($options[$option_name]) ? $options[$option_name] : null;

		if ($option_name === 'marfeel_domain') {
			return str_replace("marfeel.com", "marfeelcache.com", $option);
		}

		return $option;
	}

	function save_option($options_name, $option_value) {
		$options = get_option(MARFEEL_OPTIONS, array());
		$options[$options_name] = $option_value;
		update_option(MARFEEL_OPTIONS, $options);
	}

	function remove_protocol($uri) {
		return str_replace(array('http://', 'https://'), array('', ''), $uri);
	}

	function remove_protocol_and_domain($uri) {
		$uri = parse_url($uri);
		return $uri['path'];
	}

	function crop_amp_endpoint($amp_url) {
		preg_match_all('/(^.*?\/amp\/.*?)(\/amp\/?)$/', $amp_url, $matches);

		if ( ! empty($matches[2]) ) {
			return array_shift($matches[1]);
		} else {
			return $amp_url;
		}
	}

	function delete_all_options() {
		delete_option(MARFEEL_OPTIONS);
	}

	function is_curl_installed(){
		return function_exists('curl_version');
	}
}
