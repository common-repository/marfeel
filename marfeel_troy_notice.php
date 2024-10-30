<?php

class MarfeelTroyNotice
{
	static $instance = false;

	public function __construct() {

	}

	public static function getInstance() {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}

	public function display_marfeel_notices( ) {
		$this->blackListedPlugin();
		$this->notCorrectlyConfigured();
	}

	private function blackListedPlugin() {
		$activation_method = MarfeelWordpressHelper::getInstance()->get_option_value('marfeel_activation_method');

		if ( AmpEndpointSupport::getInstance()->is_any_blacklisted_plugin_active() && $activation_method != 'cname' ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p><b>Marfeel using '/amp' endpoint it's incompatible with some plugins you have installed.</b></p>
				<p>Please make sure you have 'CNAME' as activation method in <a href="/wp-admin/options-general.php?page=marfeel_options">settings</a></p>
			</div>
			<?php
		}
	}

	private function notCorrectlyConfigured() {
		$marfeel_domain = MarfeelWordpressHelper::getInstance()->get_option_value('marfeel_domain');
		if( ! isset($marfeel_domain) ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p><b>Marfeel was not able to configure the domain correctly.</b></p>
				<p>Please contact us at <a href="mailto:support@marfeel.com?Subject=[Wordpress%20AMP]%20Configuration%20Issue" target="_top">support@marfeel.com</a></p>
			</div>
			<?php
		}
	}

}
