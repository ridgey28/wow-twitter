<?php if (!defined('ABSPATH')) die ('No direct access allowed');
/*
 Plugin Name: WOW-Twitter
 Plugin URI: http://www.worldoweb.co.uk/
 Description: Display your latest tweets via a widget and/or shortcode using SSL. Optional badge for single pages. Automatic backup included with debugging feature in admin.
 Version: 1.0
 Author: Tracy Ridge
 Author URI: http://www.worldoweb.co.uk/
 License: GPL2
 */
 
 /***
 *Global Paths and variables
 ***/
$options = get_option('wow_twitter'); 
 
define('WOW_TWITTER_VERS', '1.0');
define('WOW_TWITTER', plugin_dir_path(__FILE__));
include WOW_TWITTER . 'display_tweets.php';
include WOW_TWITTER . 'widgets/widget.php';
include WOW_TWITTER . 'admin/admin_functions.php';

/***
* Check plugin dependencies to see if compatible - used in activation
***/
function wow_chk_depen() {	
	global $wp_version;
	$plugin_data = get_plugin_data( __FILE__, false );
	$php_min_version = '5.3';
	$require_wp = "3.1";
	$curl = 'curl_init';
	$errors = array ();
	$name = $plugin_data['Name'];

	$php_current_version = phpversion();

	if ( version_compare( $php_min_version, $php_current_version, '>' ) )
		$errors[] = "Your server is running PHP version $php_current_version but
            this plugin requires at least PHP $php_min_version. Please run an upgrade.";

	if ( !function_exists( $curl ) )
		$errors[] = "Please install $curl to run this plugin.";
	
	if ( version_compare( $wp_version, $require_wp, "<" ) )
		$errors[] = "$name requires WordPress $require_wp or higher.  Please upgrade WordPress and try again";	

	return $errors;
}

/***
* Display error message if wow_chk_dependencies called in activation failed.  Only called if error occurs
***/

function wow_twitter_failed_activation()
{
	$errors = get_option('wow_twitter_failed_activation');
	if ( empty ( $errors ) )
		return;

	// Suppress "Plugin activated" notice.
	unset( $_GET['activate'] );

	// this plugin's name
	$name = get_file_data( __FILE__, array ( 'Plugin Name' ), 'plugin' );

	printf(
		'<div class="error"><p>%1$s</p>
        <p>Please deactivate this plugin to remove this message!</p></div>',
		join( '</p><p>', $errors ),
		$name[0]
	);


}
/***
 * Show admin notice after theme activation if no error has ocurred
***/

function wow_twitter_admin_notice() {	
	global $current_user;
	$user_id = $current_user->ID;
	
	/* Check that the user hasn't already clicked to ignore the message */
	if ( ! get_user_meta($user_id, 'wow_twitter_ignore_notice') ) {
			echo '<div class="updated"><p>';
			printf(__('Remember to add your Twitter App tokens!  <a href="' . get_admin_url() . 'options-general.php?page=wow_twitter">Go to WOW-Twitter Setings</a> | <a href="%1$s">Hide Notice</a>'), '?wow_twitter_nag_ignore=0');
			echo "</p></div>";
		}
}

/***
* Checks to see if hide notice message has been clicked in admin notices
**/

function wow_twitter_nag_ignore() {
	global $current_user;
	$user_id = $current_user->ID;
	/* If user clicks to ignore the notice, add that to their user meta */
	if ( isset($_GET['wow_twitter_nag_ignore']) && '0' == $_GET['wow_twitter_nag_ignore'] ) {
		add_user_meta($user_id, 'wow_twitter_ignore_notice', 'true', true);
	}
}

/***
 * Register Activation Hook
 ***/

function wow_twitter_activation() {
	if ( ! current_user_can( 'activate_plugins' ) )
		return;
	$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
	check_admin_referer( "activate-plugin_{$plugin}" );
	delete_option('wow_twitter_failed_activation');//delete existing errors from options database first
	$check_errors = wow_chk_depen();//check for errors
	if (empty($check_errors)){//if no errors exist add data to options database
		$arr = array("wow_user_name" => "", "wow_cons_key" => "", "wow_cons_secret" => "",
			"wow_user_token" => "", "wow_user_secret" => "", "wow_max_tweets" => 20,
			"wow_json_update" => 20, "wow_date_box" => 'none', "wow_twitter_badge" => false,
			"wow_include_rts" => true, "wow_exclude_replies" => false, "wow_twitter_css" => true);
			add_option('wow_twitter', $arr);
	}
	else {//if error exists add errors to the options database
		add_option('wow_twitter_failed_activation', $check_errors);
	}
}

/***
 * Function used by Uninstall and Deactivation Hooks
 ***/

function wow_twitter_remove_feature() {
	remove_action('admin_init', 'wow_twitter_register_options');
	remove_action('admin_menu', 'wow_twitter_register_page');
	remove_action('the_content', 'wow_twitter_badge');
	remove_action('widgets_init', create_function('', 'register_widget( "WOW_Twitter_Stream" );'));
	wp_clear_scheduled_hook('wow_backup_event');
}

/***
 * Register Deactivation Hook
 ***/
 
function wow_twitter_deactivate() {
	if ( ! current_user_can( 'activate_plugins' ) )
		return;
	$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
	check_admin_referer( "deactivate-plugin_{$plugin}" );
	wow_twitter_remove_feature();
}

/***
 * Register Uninstall Hook
 ***/
 
function wow_twitter_uninstall() {
	if ( ! current_user_can( 'activate_plugins' ) )
		return;
	check_admin_referer( 'bulk-plugins' );

	// Important: Check if the file is the one
	// that was registered during the uninstall hook.
	if ( __FILE__ != WP_UNINSTALL_PLUGIN )
		return;
	wow_twitter_remove_feature();
}

/***
 * Display a Settings link on the main Plugins page
 ***/
function wow_twitter_action_links($links, $file) {
	static $this_plugin;

	if (!$this_plugin) {
		$this_plugin = plugin_basename(__FILE__);
	}

	if ($file == $this_plugin) {
		$tweet_links = '<a href="' . get_admin_url() . 'options-general.php?page=wow_twitter">Settings</a>';
		// make the 'Settings' link appear first
		array_unshift($links, $tweet_links);
	}

	return $links;
}

/*Hooks, actions & filters*/
register_uninstall_hook(__FILE__, 'wow_twitter_uninstall');
register_deactivation_hook(__FILE__, 'wow_twitter_deactivate');
register_activation_hook(__FILE__, 'wow_twitter_activation');

/*The following only loads on admin plugin page*/

if ( ! empty ( $GLOBALS['pagenow'] ) && 'plugins.php' === $GLOBALS['pagenow'] ) {
	//get error messages, if any
	$errors = get_option('wow_twitter_failed_activation');
	
	//if errors is empty display admin notice, checks only one field as this wouldn't have any values set on activation
	if(empty($errors)){ 
		if (empty($options['wow_cons_key'])){
				add_action('admin_notices', 'wow_twitter_admin_notice', 0);//add the admin notice
			}
	}
	else{
			//if an error exists display an admin notice with error
			add_action('admin_notices','wow_twitter_failed_activation',0);
		}	
}//end if
add_action('admin_init', 'wow_twitter_nag_ignore');
add_filter('plugin_action_links', 'wow_twitter_action_links', 10, 2);