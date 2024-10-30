<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
define('PRETTY', 'pretty');
define('QUERY_PARAM', 'query_param');
define('CNAME', 'cname');


class MarfeelTroy {

	static $instance = false;

	private function __construct() {}

	public static function getInstance() {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}

	public function get_marfeel_domain_uri() {
		if ($this->uri_exists('https://bc.marfeelcache.com/'.DOMAIN.'/')) {
			return 'https://bc.marfeelcache.com/amp/';
		} else if ($this->uri_exists('https://b.marfeelcache.com/'.DOMAIN.'/')) {
			return 'https://b.marfeelcache.com/amp/';
		}
		return null;
	}

	public function get_amp_cname_uri() {
		$domain_without_www = str_replace('www.', '', DOMAIN);
		if ($this->uri_exists('https://amp.'.$domain_without_www.'/mrf-amp-enabled')) {
			return 'https://amp.'.$domain_without_www;
		} else if($this->uri_exists('http://amp.'.$domain_without_www.'/mrf-amp-enabled')) {
			return 'http://amp.'.$domain_without_www;
		}
		return null;
	}

	public function get_default_marfeel_activation_method() {
		return PRETTY;
	}

	private function uri_exists($uri) {
		$response = MarfeelWordpressHelper::getInstance()->request('GET', $uri);

		return ! is_wp_error( $response ) && $response['response']['code'] == 200;
	}

	public function get_settings_page($marfeel_domain, $marfeel_cname, $marfeel_activation_method) {
		ob_start();
		$redirect = urlencode( remove_query_arg( 'msg', $_SERVER['REQUEST_URI'] ) );
		$redirect = urlencode( $_SERVER['REQUEST_URI'] );

		function is_variable_setted($var) {
			return isset($var) && $var !== '';
		}

		function get_admin_path() {
			return str_replace( get_bloginfo( 'url' ) . '/', ABSPATH, get_admin_url() );
		}

		function get_cname_example($marfeel_cname) {
			if( is_variable_setted($marfeel_cname) ) {
				return $marfeel_cname . '/an-example-article/';
			}
			return 'ERROR: CNAME not detected!';
		}

		?>
		<div class="wrap">
			<style>
				.header__logo {
					padding: 15px 0px 15px 0px;
				}

				#refresh__form {
					position: relative;
					top: -78px;
					left: 130px;
				}

				.input__elem {
					padding-bottom: 10px;
				}

				.input__elem:first-child {
					padding-top: 5px;
				}

				.input__elem small {
					padding-left: 10px;
				}
			</style>
			<object class="header__logo" data="https://www.marfeel.com/wp-content/themes/guile/assets/marfeel_logo_rgb.svg" type="image/svg+xml"></object>
			<form method="post" action="options.php">
				<?php settings_fields('marfeel_options'); ?>
				<?php do_settings_sections('marfeel_options'); ?>
				<table class="form-table">

					<tr valign="top">
						<th scope="row">Domain</th>
						<td>
						<?php
							if ( is_variable_setted($marfeel_domain) ) {
								echo $marfeel_domain;
							}
						?>
						<input type="<?php echo is_variable_setted($marfeel_domain) ? 'hidden' : 'text'; ?>" name="<?=MARFEEL_OPTIONS?>[marfeel_domain]" value="<?php echo $marfeel_domain; ?>" />
					</tr>

					<tr valign="top">
						<th scope="row">AMP</th>
						<td><?php echo is_variable_setted($marfeel_domain) ? 'Activated' : 'Deactivated'; ?></td>
					</tr>

					<tr valign="top">
						<th scope="row">Settings</th>
						<td>
							<div class="input__elem">
								<input type="radio" name="<?=MARFEEL_OPTIONS?>[marfeel_activation_method]" id="pretty" value="pretty" <?php checked($marfeel_activation_method, PRETTY ); ?> checked />
								<label for="pretty">Pretty</label>
								<small>( ie: https://<?php echo DOMAIN ?>/an-example-article/amp )</small>
							</div>
							<div class="input__elem">
								<input type="radio" name="<?=MARFEEL_OPTIONS?>[marfeel_activation_method]" id="query_param" value="query_param" <?php checked( $marfeel_activation_method, QUERY_PARAM ); ?> />
								<label for="query_param">Query Param</label>
								<small>( ie: https://<?php echo DOMAIN ?>/an-example-article/?amp=1 )</small>
							</div>
							<div class="input__elem">
								<input type="radio" name="<?=MARFEEL_OPTIONS?>[marfeel_activation_method]" id="cname" value="cname" <?php checked( $marfeel_activation_method, CNAME ); ?> <?php echo disabled( empty($marfeel_cname), true ); ?> />
								<label for="cname">CNAME</label>

								<small>( ie: <?php echo get_cname_example($marfeel_cname) ?> )</small>
								<input type="hidden" name="<?=MARFEEL_OPTIONS?>[marfeel_cname]" value="<?php echo $marfeel_cname; ?>" />
							</div>

						</td>
					</tr>

				</table>
				<?php submit_button(); ?>
			</form>
			<form id="refresh__form" action="<?php echo admin_url( 'admin-post.php' ) ?>" method="post">
				<input type="hidden" name="action" value="marfeel_refresh_options">
				<input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
				<?php submit_button('Refresh AMP Domain', 'secondary') ?>
			</form>
		</div>
		<?php

		return ob_get_clean();
	}

	public function get_amp_uri() {
		$is_article = MarfeelWordpressHelper::getInstance()->is_article();

		if ($is_article) {
			$current_uri = MarfeelWordpressHelper::getInstance()->get_current_uri();
			$marfeel_domain = MarfeelWordpressHelper::getInstance()->get_option_value('marfeel_domain');

			if(isset($current_uri) && isset($marfeel_domain)) {
				return $marfeel_domain.MarfeelWordpressHelper::getInstance()->remove_protocol($current_uri);
			}
		}

		return '';
	}

	public function get_amp_link_for_uri() {
		$current_uri = MarfeelWordpressHelper::getInstance()->get_current_uri();
		$current_uri = rtrim($current_uri, '/') . '/';

		$marfeel_domain = MarfeelWordpressHelper::getInstance()->get_option_value('marfeel_domain');
		$marfeel_activation_method = MarfeelWordpressHelper::getInstance()->get_option_value('marfeel_activation_method');
		$base_link_tag = '<link rel="amphtml" href="%s" >';

		if(isset($current_uri) && isset($marfeel_domain) && $marfeel_domain !== '') {
			switch($marfeel_activation_method) {
				case QUERY_PARAM:
					$href_value = add_query_arg( AMP_QUERY_VAR, 1, $current_uri );
					break;
				case CNAME:
					$cname = MarfeelWordpressHelper::getInstance()->get_option_value('marfeel_cname');
					$current_uri_cname = user_trailingslashit(MarfeelWordpressHelper::getInstance()->remove_protocol_and_domain($current_uri), '');
					$href_value = $cname.$current_uri_cname;
					break;
				default:
					$amp_endpoint = user_trailingslashit(AMP_QUERY_VAR, '');
					$href_value = $current_uri.$amp_endpoint;
			}
			if ( isset($href_value) && $href_value !== '' ) {
				return sprintf($base_link_tag, $href_value);
			}
		}

		return '';
	}
}
