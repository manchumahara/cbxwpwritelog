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
 * Version:           1.0.4
 * Author:            Codeboxr
 * Author URI:        https://codeboxr.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cbxwpwritelog
 * Domain Path:       /languages
 */

//namespace Php_Error_Log_Viewer;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}



defined( 'CBXWPWRITELOG_PLUGIN_NAME' ) or define( 'CBXWPWRITELOG_PLUGIN_NAME', 'cbxwpwritelog' );
defined( 'CBXWPWRITELOG_PLUGIN_VERSION' ) or define( 'CBXWPWRITELOG_PLUGIN_VERSION', '1.0.3' );
defined( 'CBXWPWRITELOG_BASE_NAME' ) or define( 'CBXWPWRITELOG_BASE_NAME', plugin_basename( __FILE__ ) );
defined( 'CBXWPWRITELOG_ROOT_PATH' ) or define( 'CBXWPWRITELOG_ROOT_PATH', plugin_dir_path( __FILE__ ) );
defined( 'CBXWPWRITELOG_ROOT_URL' ) or define( 'CBXWPWRITELOG_ROOT_URL', plugin_dir_url( __FILE__ ) );

//specific constant
defined( 'CBXWPWRITELOG_EMAIL_FAILED' ) or define( 'CBXWPWRITELOG_EMAIL_FAILED', false );
defined( 'CBXWPWRITELOG_EMAIL_SENT' ) or define( 'CBXWPWRITELOG_EMAIL_SENT', false );






if ( ! function_exists( 'write_log' ) ) {
	/**
	 * Write log to log file
	 *
	 * @param  string|array|object  $log
	 */
	function write_log( $log ) {
		//if (defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		if (defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} elseif ( is_bool( $log ) ) {
				error_log( ( $log == true ) ? 'true' : 'false' );
			} else {
				error_log( $log );
			}
		}
	}
}

//add_action( 'mu_plugin_loaded', 'cbxwpwritelog_plugins_loaded' );
function cbxwpwritelog_plugins_loaded() {
	//write_log(plugin_dir_path( __FILE__ ));
	//require_once plugin_dir_path( __FILE__ ) . 'cbxwpwritelog.php';
}//end function cbxwpwritelog_plugins_loaded


/**
 * Log email send errors
 *
 * @param $wp_error
 *
 * @return bool|void
 */
function cbxwpwritelog_action_wp_mail_failed( $wp_error ) {
	if ( function_exists( 'write_log' ) ) {
		return write_log( $wp_error );
	}

	return error_log( print_r( $wp_error, true ) );
}

// add the action
if ( CBXWPWRITELOG_EMAIL_FAILED ) {
	add_action( 'wp_mail_failed', 'cbxwpwritelog_action_wp_mail_failed', 10, 1 );
}

function cbxwpwritelog_log_email( $mail_info ) {
	write_log( $mail_info );

	return $mail_info;
}

if ( CBXWPWRITELOG_EMAIL_SENT ) {
	add_filter( 'wp_mail', 'cbxwpwritelog_log_email' );
}




add_filter('pre_get_posts', 'cbxwpwritelog_set_post_order_in_admin', 5 );

add_action( 'admin_bar_menu', 'admin_bar_menu_visitsite', 999 );

function cbxwpwritelog_set_post_order_in_admin( $wp_query ) {

	global $pagenow;

	if ( is_admin() && 'edit.php' == $pagenow && ! isset( $_GET['orderby'] ) ) {

		$wp_query->set( 'orderby', 'ID' );
		$wp_query->set( 'order', 'DESC' );
	}
}
function admin_bar_menu_visitsite( $wp_admin_bar ) {
	if ( current_user_can( 'manage_options' ) ) {

		$wp_admin_bar->add_node(
			[
				'id'    => 'visitsite',
				'title' => 'Visit Site',
				'href'  => site_url(),
				'meta'  => [ 'class' => 'visitsite_adminbar', 'target' => '_blank' ],

			]
		);

		$wp_admin_bar->add_node(
			[
				'id'    => 'cbxwpwritelog',
				'title' => 'View Error Log',
				'href'  => site_url('?cbxwpwritelog=1'),
				'meta'  => [ 'class' => 'cbxwpwritelog_adminbar', 'target' => '_blank' ],

			]
		);
	}
}

add_action( 'template_redirect', 'cbxwpwritelog_log_viewer');
function cbxwpwritelog_log_viewer() {

	if(defined( 'WP_DEBUG' ) && WP_DEBUG  && isset($_REQUEST['cbxwpwritelog']) && $_REQUEST['cbxwpwritelog'] == 1){
		require plugin_dir_path( __FILE__ )  . 'includes/cbxwpwritelog-tpl-loader.php';

		//require_once plugin_dir_path( __FILE__ ) . 'vendor/php-error-log-viewer/index.php';

		//namespace Php_Error_Log_Viewer;

		//write_log(plugin_dir_path( __FILE__ ) . 'vendor/php-error-log-viewer/src/LogHandler.php');

		require_once plugin_dir_path( __FILE__ ) . 'vendor/php-error-log-viewer/src/LogHandler.php';
		require_once plugin_dir_path( __FILE__ ) . 'vendor/php-error-log-viewer/src/AjaxHandler.php';



		$settings = [
			'file_path' => WP_CONTENT_DIR . '/debug.log',
		];

		$log_handler = new Php_Error_Log_Viewer\LogHandler($settings);
		$ajax_handler = new Php_Error_Log_Viewer\AjaxHandler($log_handler);
		$ajax_handler->handle_ajax_requests();


		echo cbxwpwritelog_get_template_html('log_view.php');
		//readfile(plugin_dir_path( __FILE__ ) . 'vendor/php-error-log-viewer/src/error-log-viewer-frontend.html');

		exit();
	}
}

