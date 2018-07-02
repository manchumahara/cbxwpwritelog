<?php

	/**
	 *
	 * @link              https://codeboxr.com
	 * @since             1.0.0
	 * @package           cbxwpwritelog
	 *
	 * @wordpress-plugin
	 * Plugin Name:       CBX WP Write Log
	 * Plugin URI:        https://github.com/manchumahara/cbxwpwritelog
	 * Description:       This plugin adds a helper function to write log in wordpress debug file. This plugin also writes email send fail logs
	 * Version:           1.0.1
	 * Author:            Codeboxr
	 * Author URI:        https://codeboxr.com
	 * License:           GPL-2.0+
	 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
	 * Text Domain:       cbxwpwritelog
	 * Domain Path:       /languages
	 */

	// If this file is called directly, abort.
	if ( ! defined( 'WPINC' ) ) {
		die;
	}


	defined( 'CBXWPWRITELOG_PLUGIN_NAME' ) or define( 'CBXWPWRITELOG_PLUGIN_NAME', 'cbxwpwritelog' );
	defined( 'CBXWPWRITELOG_PLUGIN_VERSION' ) or define( 'CBXWPWRITELOG_PLUGIN_VERSION', '1.0.0' );
	defined( 'CBXWPWRITELOG_BASE_NAME' ) or define( 'CBXWPWRITELOG_BASE_NAME', plugin_basename( __FILE__ ) );
	defined( 'CBXWPWRITELOG_ROOT_PATH' ) or define( 'CBXWPWRITELOG_ROOT_PATH', plugin_dir_path( __FILE__ ) );
	defined( 'CBXWPWRITELOG_ROOT_URL' ) or define( 'CBXWPWRITELOG_ROOT_URL', plugin_dir_url( __FILE__ ) );

	if ( ! function_exists( 'write_log' ) ) {
		/**
		 * Write log to log file
		 *
		 * @param string|array|object $log
		 */
		function write_log( $log ) {
			if ( true === WP_DEBUG ) {
				if ( is_array( $log ) || is_object( $log ) ) {
					error_log( print_r( $log, true ) );
				} else {
					error_log( $log );
				}
			}
		}
	}


	/**
	 * Log email send errors
	 *
	 * @param $wp_error
	 *
	 * @return bool|void
	 */
	function cbx_action_wp_mail_failed($wp_error)
	{
		if(function_exists('write_log')){
			return write_log($wp_error);
		}
		
		return error_log(print_r($wp_error, true));
	}

	// add the action
	add_action('wp_mail_failed', 'cbx_action_wp_mail_failed', 10, 1);