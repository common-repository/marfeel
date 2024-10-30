<?php
/*
    Plugin Name: Mrf amp
    Plugin URI:  http://www.marfeel.com
    Description: Marfeel configuration for Wordpress sites.
    Version:     1.8.7
    Author:      Marfeel Team
    Author URI:  http://www.marfeel.com
    License:     GPL2
	License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once('marfeel_troy.php');
require_once('marfeel_wordpress_helper.php');
require_once('amp_endpoint_support.php');
require_once('marfeel_troy_notice.php');

define('MARFEEL_OPTIONS', 'marfeel_options');
define( 'DOMAIN', MarfeelWordpressHelper::getInstance()->remove_protocol( get_site_url() ) );

register_activation_hook(__FILE__, 'activate_marfeel_plugin');
register_deactivation_hook(__FILE__, 'deactivate_marfeel_plugin');

add_action( 'upgrader_process_complete', 'upgrade_marfeel_plugin', 10, 2 );
add_action('admin_init', 'register_marfeel_options');
add_action('admin_menu', 'register_marfeel_settings_page' );
add_action('admin_post_marfeel_refresh_options', 'marfeel_refresh_options' );
add_action('wp_head', 'render_marfeel_amp_link' );
add_action('wp', 'render_marfeel_amp_content' );
add_action( 'init', 'init_marfeel' );
add_action('admin_notices', 'display_marfeel_notices' );

function init_marfeel() {
	AmpEndpointSupport::getInstance()->add_endpoints();
}

function display_marfeel_notices() {
	MarfeelTroyNotice::getInstance()->display_marfeel_notices();
}

function activate_marfeel_plugin() {
	$marfeel_server_uris = save_marfeel_server_resources();
	if ( isset($marfeel_server_uris[0]) ) {
		$marfeel_activation_method = MarfeelTroy::getInstance()->get_default_marfeel_activation_method();
		MarfeelWordpressHelper::getInstance()->save_option('marfeel_activation_method', $marfeel_activation_method);
	} else {
		MarfeelWordpressHelper::getInstance()->delete_all_options();
	}
	AmpEndpointSupport::getInstance()->activate_rewrite_strategy();
}

function deactivate_marfeel_plugin() {
	MarfeelWordpressHelper::getInstance()->delete_all_options();
	AmpEndpointSupport::getInstance()->deactivate_rewrite_strategy();
}

function save_marfeel_server_resources() {
	$marfeel_domain = MarfeelTroy::getInstance()->get_marfeel_domain_uri();
	$marfeel_cname = MarfeelTroy::getInstance()->get_amp_cname_uri();

	if( isset($marfeel_domain) ) {
		MarfeelWordpressHelper::getInstance()->save_option('marfeel_domain', $marfeel_domain);
	}

	MarfeelWordpressHelper::getInstance()->save_option('marfeel_cname', $marfeel_cname);
	return array($marfeel_domain, $marfeel_cname);
}

function register_marfeel_options() {
	register_setting(MARFEEL_OPTIONS, MARFEEL_OPTIONS, 'validate_marfeel_options');
}

function upgrade_marfeel_plugin( $upgrader_object, $options ) {
	$mrf_plugin = plugin_basename( __FILE__ );
	if( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
		foreach( $options['plugins'] as $plugin ) {
			if( $plugin == $mrf_plugin && ! AmpEndpointSupport::getInstance()->check_rewrite_rules_active() ) {
				AmpEndpointSupport::getInstance()->activate_rewrite_strategy();
			}
		}
	}
}

function marfeel_refresh_options() {
	save_marfeel_server_resources();
	//TODO: feedback of success
	wp_safe_redirect( urldecode( $_POST['_wp_http_referer'] ) );
}

function validate_marfeel_options($options) {
	if(isset($options['marfeel_domain']) && $options['marfeel_domain'] !== '') {
		$sanitized_domain = filter_var(trim($options['marfeel_domain']), FILTER_SANITIZE_SPECIAL_CHARS);
		if ( strpos($sanitized_domain, '.marfeelcache.com') === false ) {
			add_settings_error(
				'marfeel_domain',
				'marfeeldomain_texterror',
				'Invalid domain. Please contact support@marfeel.com',
				'error'
			);
			$options['marfeel_domain'] = '';
		} else {
			$options['marfeel_domain'] = $sanitized_domain;
		}
	}
	return $options;
}

function register_marfeel_settings_page() {
	add_options_page(
		'Marfeel',
		'Marfeel',
		'manage_options',
		MARFEEL_OPTIONS,
		'render_marfeel_settings_page'
	);
}

function render_marfeel_settings_page() {
	$marfeel_domain = MarfeelWordpressHelper::getInstance()->get_option_value('marfeel_domain');
	$marfeel_cname = MarfeelWordpressHelper::getInstance()->get_option_value('marfeel_cname');
	$marfeel_activation_method = MarfeelWordpressHelper::getInstance()->get_option_value('marfeel_activation_method');

	echo MarfeelTroy::getInstance()->get_settings_page($marfeel_domain, $marfeel_cname, $marfeel_activation_method);
}

function render_marfeel_amp_link() {
	if ( MarfeelWordpressHelper::getInstance()->is_article() ) {
		echo MarfeelTroy::getInstance()->get_amp_link_for_uri();
	}
}

function render_marfeel_amp_content() {
	$data = get_marfeel_amp_content();
	// Disable newrelic to not broke AMP pages: https://github.com/ampproject/amphtml/issues/2380
	if (extension_loaded('newrelic')) {
		newrelic_disable_autorum();
	}
	if(!empty($data) && $data !== false) {
		echo $data;
		exit;
	}
}


function get_marfeel_amp_content() {
	$query_value = get_query_var( AMP_QUERY_VAR, false );
	if ( AmpEndpointSupport::getInstance()->mrf_is_amp_endpoint( $query_value ) ) {
		if( $query_value == 1 ) {
			$amp_url = MarfeelTroy::getInstance()->get_amp_uri();
			$mrf_helper = MarfeelWordpressHelper::getInstance();
			$clean_amp_url = $mrf_helper->crop_amp_endpoint($amp_url);

			if(!empty($clean_amp_url)) {
				$data = $mrf_helper->request('GET', $clean_amp_url, array(
					'timeout' => 5,
					'followlocation' => true,
				));

				if ( ! is_wp_error( $data ) ) {
					status_header($data['response']['code']);
					return $data['body'];
				} else {
					status_header(503);
					exit;
				}
			}
			return '';
		} else {
			global $wp_query;
			$wp_query->set_404();
			status_header(410);
			include(get_404_template());
			exit;
		}
	}
}
