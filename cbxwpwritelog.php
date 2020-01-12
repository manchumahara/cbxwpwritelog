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
	 * Version:           1.0.2
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
	defined( 'CBXWPWRITELOG_PLUGIN_VERSION' ) or define( 'CBXWPWRITELOG_PLUGIN_VERSION', '1.0.2' );
	defined( 'CBXWPWRITELOG_BASE_NAME' ) or define( 'CBXWPWRITELOG_BASE_NAME', plugin_basename( __FILE__ ) );
	defined( 'CBXWPWRITELOG_ROOT_PATH' ) or define( 'CBXWPWRITELOG_ROOT_PATH', plugin_dir_path( __FILE__ ) );
	defined( 'CBXWPWRITELOG_ROOT_URL' ) or define( 'CBXWPWRITELOG_ROOT_URL', plugin_dir_url( __FILE__ ) );

	//specific constant
	defined( 'CBXWPWRITELOG_EMAIL_FAILED' ) or define( 'CBXWPWRITELOG_EMAIL_FAILED', true );
	defined( 'CBXWPWRITELOG_EMAIL_SENT' ) or define( 'CBXWPWRITELOG_EMAIL_SENT', true );



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
	function cbxwpwritelog_action_wp_mail_failed($wp_error)
	{
		if(function_exists('write_log')){
			return write_log($wp_error);
		}
		
		return error_log(print_r($wp_error, true));
	}

	// add the action
	if(CBXWPWRITELOG_EMAIL_FAILED){
		add_action('wp_mail_failed', 'cbxwpwritelog_action_wp_mail_failed', 10, 1);
	}

	function cbxwpwritelog_log_email($mail_info){
		write_log($mail_info);

		return $mail_info;
	}

	if(CBXWPWRITELOG_EMAIL_SENT){
		add_filter( 'wp_mail', 'cbxwpwritelog_log_email');
	}


	function cbxwpwritelog_set_post_order_in_admin( $wp_query ) {

		global $pagenow;

		if ( is_admin() && 'edit.php' == $pagenow && !isset($_GET['orderby'])) {

			$wp_query->set( 'orderby', 'ID' );
			$wp_query->set( 'order', 'DESC' );
		}
	}

	add_filter('pre_get_posts', 'cbxwpwritelog_set_post_order_in_admin', 5 );