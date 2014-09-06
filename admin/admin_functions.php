<?php 
/***
 *  Register our Options. Add Javascript and CSS to admin
 ***/
 
function wow_twitter_register_options() {
	register_setting('wow_twitter', 'wow_twitter', 'wow_twitter_validate');
	wp_register_script('wow_twitter', plugins_url('../js/wow_twitter_admin.js', __FILE__), array('jquery'), WOW_TWITTER_VERS);
	wp_register_style('wow_twitter', plugins_url('../css/wow_twitter_admin.css', __FILE__), false, WOW_TWITTER_VERS);
}

/***
 *  Add sub page to the Settings Menu.
 ***/
 
function wow_twitter_register_page() {
	global $page_hook_suffix;
	$page_hook_suffix = add_options_page('WOW-Twitter', 'WOW-Twitter', 'manage_options', 'wow_twitter', 'wow_twitter_display_form');
}

/***
 *  EnqueueJavascripts and CSS for Admin, registered in wow_twitter_register_options
 ***/
 
function wow_twitter_admin_scripts($hook) {
	global $page_hook_suffix;
	/* Link our already registered script to a page */
	if ($hook != $page_hook_suffix)return;
	wp_enqueue_style('wow_twitter');
	wp_enqueue_script('wow_twitter');

	$wow_data = array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'show_logs' => true,
		'options_show_nonce' => wp_create_nonce('options_show_nonce'),
		'delete_logs' => true,
		'options_delete_nonce' => wp_create_nonce('options_delete_nonce'),
		'check_status' => true,
		'options_check_nonce' => wp_create_nonce('options_check_nonce')
	);

	wp_localize_script('wow_twitter', 'ajax_object', $wow_data );
}

/***
 * Fetch log contents used by wow_twitter_show_logs & wow_twitter_check_server
 ***/
 
function wow_twiiter_fetch_log() {
	$options = get_option('wow_twitters_log');
	if ($options) {
		foreach ($options as $option) {
			echo $option;
		}
	} else {
		echo "There are no logs to show";
	}
}

/***
 * Debugging area in admin section - show logs
 ***/

function wow_twitter_show_logs() {
	$show_logs = $_POST['info'];
	$nonce     = $_POST['nonce'];
	if (wp_verify_nonce($nonce, 'options_show_nonce') && current_user_can('manage_options')) {
		if ($show_logs) {
			wow_twiiter_fetch_log();
		}
	}
	die();
}

/***
 * Debugging area in admin section - remove logs
 ***/

function wow_twitter_remove_logs() {
	$remove_logs = $_POST['info'];
	$nonce       = $_POST['nonce'];
	if (wp_verify_nonce($nonce, 'options_delete_nonce') && current_user_can('manage_options')) {
		if ($remove_logs) {
			delete_option('wow_twitters_log');
			if (!get_option('wow_twitters_log')) {
				echo "There are no logs to show";
			}
		}
	}
	die();
}

/***
 * Check that Your twiter credentials are correct
 ***/

function wow_twitter_check_status() {
	$check_status = $_POST['info'];
	$nonce       = $_POST['nonce'];
	if (wp_verify_nonce($nonce, 'options_check_nonce') && current_user_can('manage_options')) {
		if ($check_status) {
			$check = true;
			wow_twitter_get_auth($check);
			wow_twiiter_fetch_log();

		}
	}
	die();
}

/***
 *  Display the form for the admin options page
 ***/
function wow_twitter_display_form() {

	include_once WOW_TWITTER . 'admin/admin_form.php';

}

/***
 * Validate user data for some/all of your input fields
 ***/
 
function wow_twitter_validate($input) {
	$output = array();
	$error = array();

	function wow_cln($in) {
		return $in = esc_attr(strip_tags(stripslashes($in)));
	}
	//Add textboxes to an array
	$textbox = array( 'wow_cons_key'   => $input['wow_cons_key'],
		'wow_cons_secret'=> $input['wow_cons_secret'],
		'wow_user_token' => $input['wow_user_token'],
		'wow_user_secret' => $input['wow_user_secret']
	);
	//check textboxes for errors
	foreach ($textbox as $key =>$val) {
		if (preg_match("/[^a-zA-Z0-9\-]/", $val)) {
			$error[$key] = "Your token keys can only contain letters numbers and hyphens";
		}
			$val = preg_replace('/[^a-zA-Z0-9\-]/', '', $val);
			$output[$key]=wow_cln(wp_filter_nohtml_kses($val));
	}
	//Add number inputs into an array
	$ints = array( 'wow_json_update' => $input['wow_json_update'], 'wow_max_tweets' => $input['wow_max_tweets']);
	//check for anything other than numerical input and only a max of 180, reset to default if invalid
	foreach ($ints as $key => $val) {
		if (!is_numeric($val)) { $error[$key] = "You can only use numbers in the fields marked red"; $val = 20;}
		else if ($val >= 181) {  $error[$key] = "You are only allowed a maximum of 180 Tweets, limited by the Twitter API"; $val =20;}
		$output[$key]=wow_cln($val);
	}
	//add checkboxes to an array
	$ckbox = array(  'wow_include_rts' => $input['wow_include_rts'],
		'wow_exclude_replies' => $input['wow_exclude_replies'],
		'wow_twitter_css'    => $input['wow_twitter_css'],
		'wow_twitter_badge'  => $input['wow_twitter_badge']
	);
	// check checkboxes and reset them to false 
	foreach ($ckbox as $key =>$val) {
		($val == 1 ? 1 : 0);
		$output[$key]=wow_cln($val);
	}
	//check drop down select and reset it none if wrong value exists
	$date_bx = wow_cln($input['wow_date_box']);
	$ar = array("none", "default", "eng_suff", "ddmm", "ddmmyy", "full_date", "time_since");
	(in_array($date_bx, $ar) ? $output['wow_date_box'] = $date_bx : $output['wow_date_box'] = 'none');
	
	//assign username and check for illegal characters and length too long
	$user_name = wow_cln(wp_filter_nohtml_kses($input['wow_user_name']));
	if (preg_match("/[^a-zA-Z0-9\_]/", $user_name)) {
		$error['wow_user_name'] = "Your Username contains illegal characters!";
	}
	else if (strlen($user_name) > 20) {
		$error['wow_name_long'] = "Your username is too long!";
	}
	$output['wow_user_name'] = preg_replace('/[^a-zA-Z0-9\_]/', '', $user_name);

	if (isset($error)) {
		foreach ($error as $err =>$val) {
			add_settings_error('wow_twitter', $err, $val , 'error');
		}
		set_transient('settings_errors', get_settings_errors(), 30);
	}

	return apply_filters('wow_twitter_validate', $output, $input);
}

/***
*Action Hooks
***/

if (is_admin()) {
	add_action('admin_init', 'wow_twitter_register_options');
	add_action('admin_menu', 'wow_twitter_register_page');
}

add_action('wp_ajax_remove_logs', 'wow_twitter_remove_logs');
add_action('wp_ajax_show_logs', 'wow_twitter_show_logs');
add_action('wp_ajax_check_status', 'wow_twitter_check_status');
add_action('admin_enqueue_scripts', 'wow_twitter_admin_scripts');