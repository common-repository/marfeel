<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
define( 'AMP_QUERY_VAR', apply_filters( 'amp_query_var', 'amp' ) );
define( 'AMP_PLUGIN', 'amp/amp.php' );
function add_query_vars_filter( $vars ){
  $vars[] = AMP_QUERY_VAR;
  return $vars;
}
add_filter( 'query_vars', 'add_query_vars_filter' );

class AmpEndpointSupport {



    static $instance = false;

    private function __construct() {

    }

    public static function getInstance() {
        if ( !self::$instance )
            self::$instance = new self;
        return self::$instance;
	}

	function is_any_blacklisted_plugin_active() {
		$blacklisted_plugins = array(
			'nextgen-gallery/nggallery.php',
			'marfeel-press/marfeel-press.php',
		);

		foreach( $blacklisted_plugins as $plugin_slug) {
			if ( is_plugin_active( $plugin_slug ) ) {
				return true;
			}
		}
		return false;
	}

    function activate_rewrite_strategy() {
        if(!is_plugin_active(AMP_PLUGIN)) {
            $this->add_endpoints();

            flush_rewrite_rules();

            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }
	}

    function add_endpoints() {
        add_rewrite_endpoint( AMP_QUERY_VAR, EP_PAGES | EP_PERMALINK );
        add_post_type_support( 'post', AMP_QUERY_VAR );
        add_post_type_support( 'page', AMP_QUERY_VAR );

        add_filter( 'request', array( $this, 'mrf_amp_force_query_var_value' ) );
    }

    function check_rewrite_rules_active() {
        global $wp_rewrite;
        foreach ( $wp_rewrite->endpoints as $index => $endpoint ) {
            if ( AMP_QUERY_VAR === $endpoint[1] ) {
                return true;
            }
        }
        return false;
    }

    function deactivate_rewrite_strategy() {
        global $wp_rewrite;
        foreach ( $wp_rewrite->endpoints as $index => $endpoint ) {
            if ( AMP_QUERY_VAR === $endpoint[1] ) {
                unset( $wp_rewrite->endpoints[ $index ] );
                break;
            }
        }
        flush_rewrite_rules();
    }

    function mrf_amp_force_query_var_value( $query_vars ) {
        if ( isset( $query_vars[ AMP_QUERY_VAR ] ) && '' === $query_vars[ AMP_QUERY_VAR ] ) {
            $query_vars[ AMP_QUERY_VAR ] = 1;
        }
        return $query_vars;
    }

    function mrf_is_amp_endpoint( $query_value ) {
        if ( !is_singular() || is_feed() ) {
            return;
        }

        return $query_value !== false;
    }

    function mrf_post_supports_amp( $post ) {
        if ( ! post_type_supports( $post->post_type, AMP_QUERY_VAR ) ) {
            return false;
        }

        if ( post_password_required( $post ) ) {
            return false;
        }

        return true;
    }
}
