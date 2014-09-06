<?php if (!defined ('ABSPATH')) die ('No direct access allowed');
 

/***
 * Add stylesheet
 ***/

function wow_twitter_stylesheet() {

	wp_register_style('wow_twitter', plugins_url('/css/wow_twitter.css', __FILE__), false, WOW_TWITTER_VERS);
	wp_enqueue_style('wow_twitter');
}

if ($options['wow_twitter_css']) {add_action('wp_enqueue_scripts', 'wow_twitter_stylesheet');}


/***
 * The function below is used to calculate the time since you last tweeted, used with 'time_since' parameter
 ***/
 
function wow_twitter_date_diff($time1, $time2, $precision = 6)
{
	if (!is_int($time1)) {
		$time1 = strtotime($time1);
	}
	if (!is_int($time2)) {
		$time2 = strtotime($time2);
	}
	if ($time1 > $time2) {
		$ttime = $time1;
		$time1 = $time2;
		$time2 = $ttime;
	}
	$intervals = array(
		'year',
		'month',
		'day',
		'hour',
		'minute',
		'second'
	);
	$diffs     = array();
	foreach ($intervals as $interval) {
		$diffs[$interval] = 0;
		$ttime            = strtotime("+1 " . $interval, $time1);
		while ($time2 >= $ttime) {
			$time1 = $ttime;
			$diffs[$interval]++;
			$ttime = strtotime("+1 " . $interval, $time1);
		}
	}
	$count = 0;
	$times = array();
	foreach ($diffs as $interval => $value) {
		if ($count >= $precision) {
			break;
		}
		if ($value > 0) {
			if ($value != 1) {
				$interval .= "s";
			}
			$times[] = $value . " " . $interval;
			$count++;
		}
	}
	return implode(", ", $times);
}

/***
 * Gets json from the server and returns either a json file or an error to be logged
 ***/
 
function wow_twitter_get_auth($check = false)
{
	include_once WOW_TWITTER . 'classes/tmhOAuth.php';
	$options  = get_option('wow_twitter');
	$tmhOAuth = new tmhOAuth(array(

			'consumer_key' => $options['wow_cons_key'],
			'consumer_secret' => $options['wow_cons_secret'],
			'user_token' => $options['wow_user_token'],
			'user_secret' => $options['wow_user_secret']
		));

	$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/statuses/user_timeline'), array(
			'screen_name' => $options['wow_user_name'],
			'count' => $options['wow_max_tweets'],
			'include_rts' => $options['wow_include_rts'],
			'exclude_replies' => isset($options['wow_exclude_replies'])
		));
	$res_code = array(
		'200',
		'304'
	);
	if (!$check && in_array($code, $res_code)) {
		return array(
			"error" => false,
			"auth" => $tmhOAuth->response['response']
		);
	} else {
		wow_twitter_log_code($code);
		return array(
			"error" => true,
			"auth" => $code
		);
	}
}
/***
*Logs error code messages for admin
***/
function wow_twitter_log_code($code)
{
	$options_backup = get_option('wow_twitters_log');
	switch ($code) {
	case '200':
		$error = "Twitter was accessed correctly";
		break;
	case '304';
		$error = "No new data to return";
		break;
	case '400':
		$error = "The request was invalid";
		break;

	case '401':
		$error = "Unauthorized - Please check your credentials";
		break;

	case '403':
		$error = "Forbidden - Possibly due to exceeding limit";
		break;

	case '404':
		$error = "Not Found - That user does not exist";
		break;

	case '406':
		$error = "Not acceptable - Possibly Invalid format specified";
		break;

	case '429':
		$error = "Too many requests - You have maxed out your API limit";
		break;

	case '500':
		$error = "Something is broken - Please contact Twitter";
		break;

	case '502':
		$error = "Bad Gateway - Twitter is currently down or being upgraded";
		break;

	case '503':
		$error = "Service Unavailable - Twitter service is being overloaded, please try again later!";
		break;

	case '504':
		$error = "Gateway Timeout - Twitter services are up but your request couldn't be serviced, please try again later";
		break;

	case '0';
		$error = "There is a problem with cacert.pem";
		break;

	case 'backup';
		$error = "Twitter Backup Completed";
		break;
	}

		$date  = date('D d M Y G:i:s');
		$error = array($date . "\t" . $error . "<br/>");
		
	if (!($options_backup)) {
		add_option('wow_twitters_log', $error, '', 'no');
	} else {
		$args = array_merge($error,$options_backup);
		update_option('wow_twitters_log', $args);
	}
}

/***
 * Cache's json Files to the server using WP Transients
 * Used by display_tweets() & widget.php
 * returns json decoded array
 ***/
 
function wow_twitter_cache_json($name)
{
    $options = get_option('wow_twitter');
    if ($name == 'wow_twitter_backup'){$time = 10080; $backup = true; } else {$time = $options['wow_json_update'];}
    if (false === ($tweets = get_transient($name))) {
        $auth  = wow_twitter_get_auth();
        $error = $auth['error'];
        if (!$error) { //if not true
            $tweets = json_decode($auth['auth'], true);
            set_transient($name, $tweets, 60 * $time); //set the file to be cached at the interval you added in admin
            	if(isset($backup)) wow_twitter_log_code('backup');	
        } else { //get from backup
            $tweets = get_transient('wow_twitter_backup');
        }
    }
    return $tweets;
}

/***
 * Converts URLs, Replies and Hashtags into Links
 ***/

function wow_twitter_convert($tweet)
{
	# Turn URLs into links
	$tweet = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\./-]*(\?\S+)?)?)?)@', '<a target="blank" title="$1" href="$1">$1</a>', $tweet);

	#Turn hashtags into links
	$tweet = preg_replace('/#([0-9a-zA-Z_-]+)/', "<a target='blank' title='$1' href=\"http://twitter.com/search?q=%23$1\">#$1</a>", $tweet);

	#Turn @replies into links
	$tweet = preg_replace("/@([0-9a-zA-Z_-]+)/", "<a target='blank' title='$1' href=\"http://twitter.com/$1\">@$1</a>", $tweet);

	return $tweet;
}

/***
 * Parses json feed and outputs to HTML
 * Uses Date_Diff
 * returns HTML with tweets
 ***/
 
function wow_twitter_parse_feed($name, $badge = false, $widget = false, array $shortcode = NULL)
{
	$widget_options = get_option('widget_wow_twitter_stream');
	$options = get_option('wow_twitter');
	
	if (isset($widget_options) && !empty($widget_options)){
	   if (array_key_exists(2,$widget_options)){
		  $values = $widget_options[2];
          $widget_count = $values['number'];
	}}
	
	$tweets = wow_twitter_cache_json($name);
	$style   = $options['wow_date_box'];
	
	$max_tweets = $shortcode['max_tweets'];
	$shortcode = $shortcode['shortcode'];

		

	$twitter = '';
	if($badge)
	{
		$user = $tweets[0]['user'];
		$name = $user['name'];
		$username = $user['screen_name'];
		$description = $user['description'];
		$profile_images = $user['profile_image_url'];

		$query = "https://twitter.com/".$username;
		$img = "<img src=".$profile_images." height='50' width='50' alt=".$username."Profile /> ";
		$link = "<a href='https://www.twitter.com/".$username."' title='Follow me on Twitter'>".'@'.$username." </a>";

		$wow_badge='';
		$wow_badge.=  "<div id='wow_twitter_badge'>";
		$wow_badge.=  "<div class='wow_col wow_one'>";
		$wow_badge.=     $img;
		$wow_badge.=  "</div>";
		$wow_badge.=  "<div class='wow_col wow_two'><h3>".$name."</h3><a href='".$query."' class='twitter-follow-button' data-show-count='false' data-show-screen-name='false'>Follow ".$username."</a>
									<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
										<h5>".$link."</h5>";
		$wow_badge.=  "</div>";
		$wow_badge.=    "<div class='tweet_col'><p>". $description."</p></div>";
		$wow_badge.= "</div>";

		return $twitter = $wow_badge;
	}
	else{
		($widget ? $twitter .= '<ul>' : $twitter .= '<ul id="wow_twitter">');
		$i =0;
		if (!empty($tweets)) {
			foreach ($tweets as $tweet) {
				if (isset($shortcode)){// if shortcode exists limit the amount of tweets parsed
					if(++$i > $max_tweets) {break;}
				}
				else if (isset($widget))
				{
					if(++$i > $widget_count) {break;}
				}	
				$pubDate        = $tweet['created_at'];
				$tweet          = wow_twitter_convert($tweet['text']);
				$today          = time();
				$time           = substr($pubDate, 11, 5);
				$day            = substr($pubDate, 0, 3);
				$date           = substr($pubDate, 7, 4);
				$month          = substr($pubDate, 4, 3);
				$year           = substr($pubDate, 25, 5);
				$english_suffix = date('jS', strtotime(preg_replace('/\s+/', ' ', $pubDate)));
				$full_month     = date('F', strtotime($pubDate));

				#pre-defined tags
				$default   = $full_month . $date . $year;
				$full_date = $day . $date . $month . $year;
				$ddmmyy    = $date . $month . $year;
				$mmyy      = $month . $year;
				$mmddyy    = $month . $date . $year;
				$ddmm      = $date . $month;

				#Time difference
				$timeDiff = wow_twitter_date_diff($today, $pubDate, 1);

				($widget ? $twitter .= "<li>" . $tweet . "<br />" : $twitter .= "<li class='tweet'>" . $tweet . "<br />");

				if (isset($style)) {
					if ($style != 'none') {
						$when = ($style == 'time_since' ? 'About' : 'On');
						$twitter .= "<strong>" . $when . "&nbsp;";

						switch ($style) {
						case 'eng_suff': {
								$twitter .= $english_suffix . '&nbsp;' . $full_month;
							}
							break;
						case 'time_since'; {
								$twitter .= $timeDiff . "&nbsp;ago";
							}
							break;
						case 'ddmmyy'; {
								$twitter .= $ddmmyy;
							}
							break;
						case 'ddmm'; {
								$twitter .= $ddmm;
							}
							break;
						case 'full_date'; {
								$twitter .= $full_date;
							}
							break;
						case 'default'; {
								$twitter .= $default;
							}
						} //end switch statement
						$twitter .= "</strong></li>"; //end of List
					}
				} //end if style
			} //end of foreach
		} else {
			$twitter .= '<li>No tweets</li>';
		} //end if statement
		$twitter .= '</ul>';
	}
	return $twitter;
}


/***
 * Display Badge in a single page after content
 **/

function wow_twitter_badge($content) {
	$options = get_option('wow_twitter');
	if (is_single()) {
		if ($options['wow_twitter_badge']) {
				$content .=  wow_twitter_parse_feed('wow_twitter_backup',true, false);;
			}}
	return $content;
}

/***
 * Create a cron job to automatically update the backup tweets function
 ***/
 
function wow_twitter_backup_activation()
{
	/*Weekly schedule*/
	function wow_backup_schedule($schedules)
	{
		// Adds once weekly to the existing schedules.
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display' => __('Once Weekly')
		);
		return $schedules;
	}
	add_filter('cron_schedules', 'wow_backup_schedule');

	if (!wp_next_scheduled('wow_backup_event')) {
		wp_schedule_event(time(), 'weekly', 'wow_backup_event');
	}
}

function wow_twitter_backup_weekly()
{
	$name = 'wow_twitter_backup';
	wow_twitter_cache_json($name);
}

/***
 * Add Shortcode
 ***/

function wow_twitter_shortcode($atts) { 
        extract( shortcode_atts( array(  
            'max_tweets' => '20'  
        ), $atts ) );  
	   $shortcode = array("max_tweets" => $max_tweets, "shortcode" => true);
       $twitter = wow_twitter_parse_feed('wow_twitter_shortcode', false, false, $shortcode);
	return $twitter;
}


/* List of actions*/
add_action('the_content', 'wow_twitter_badge');
add_action('wp', 'wow_twitter_backup_activation');
add_action('wow_backup_event', 'wow_twitter_backup_weekly');
add_shortcode( 'wow_twitter', 'wow_twitter_shortcode' );?>