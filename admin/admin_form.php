<div class="wrap">
	<div class="icon32" id="icon-options-general"><br></div>
	<h2>WOW-Twitter Settings</h2>

	<form method="post" action="options.php" class="wowt">
	<?php settings_fields('wow_twitter');
		  $options = get_option('wow_twitter');?>
		  
		<table class="form-table">
			<h3>Main Twitter Settings</h3>
			<p>To use this plugin you need to request some keys from the Twitter API. Here is a handy <a href="http://www.worldoweb.co.uk/2012/create-a-twitter-app-for-the-new-api" title="Create a Twitter App" target="_blank">tutorial</a>.</p>
			<tr>
				<th scope="row">Twitter Username</th>
				<td>
					<input type="text" id="username"  size="20" name="wow_twitter[wow_user_name]"  value="<?php
if (isset($options['wow_user_name'])) {echo sanitize_text_field($options['wow_user_name']);}?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">Consumer Key</th>
				<td>
					<input type="text" size="57" id="cons_key" name="wow_twitter[wow_cons_key]"  value="<?php
if (isset($options['wow_cons_key'])) {echo sanitize_text_field($options['wow_cons_key']);}?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">Consumer Secret</th>
				<td>
					<input type="text" size="57" id="cons_secret" name="wow_twitter[wow_cons_secret]"  value="<?php
if (isset($options['wow_cons_secret'])) {echo sanitize_text_field($options['wow_cons_secret']);}?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">User Token</th>
				<td>
					<input type="text" size="57" id="user_token" name="wow_twitter[wow_user_token]"  value="<?php
if (isset($options['wow_user_token'])) {echo sanitize_text_field($options['wow_user_token']);}?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">User Secret</th>
				<td>
					<input type="text" size="57" id="user_secret" name="wow_twitter[wow_user_secret]"  value="<?php
if (isset($options['wow_user_secret'])) {echo sanitize_text_field($options['wow_user_secret']);}?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">Maximum Tweets</th>
				<td>
					<input type="text" size="5" id="max_tweets" name="wow_twitter[wow_max_tweets]" value="<?php
if (isset($options['wow_max_tweets'])) {echo sanitize_text_field($options['wow_max_tweets']);}?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">Display Retweets</th>
				<td>
					<input name="wow_twitter[wow_include_rts]" type="checkbox" value="1" <?php
if (isset($options['wow_include_rts'])) { checked('1', $options['wow_include_rts']);}?> />
						<em class="default">Default: True</em><br />
				</td>
			</tr>
			<tr>
				<th scope="row">Exclude Replies</th>
				<td>
					<input name="wow_twitter[wow_exclude_replies]" type="checkbox" value="1" <?php
if (isset($options['wow_exclude_replies'])) { checked('1', $options['wow_exclude_replies']);}?> />
						<em class="default">Default: False</em><br />
				</td>
			</tr>
			<tr>
				<th scope="row">Synchronise with Twitter API every...</th>
				<td>
					<input type="text" size="5" id="json_update" name="wow_twitter[wow_json_update]" value="<?php
if (isset($options['wow_json_update'])) {echo sanitize_text_field($options['wow_json_update']);}?>" /><?php echo '&nbsp;<span>Minutes</span>'; ?>
						<em class="default">Default:  20</em>
				</td>
			</tr>
				<!-- Select Drop-Down Style Control -->
			<tr>
				<th scope="row">Style</th>
				<td>
					<select name='wow_twitter[wow_date_box]'>
						<option value='none' <?php selected('none', $options['wow_date_box']); ?>><?php echo "No Date Format"; ?></option>
						<option value='default' <?php selected('default', $options['wow_date_box']); ?>><?php echo date('F d Y');//Default November 06 2012?></option>
						<option value='eng_suff' <?php selected('eng_suff', $options['wow_date_box']); ?>><?php echo date('jS F');//6th November?></option>
						<option value='ddmm' <?php selected('ddmm', $options['wow_date_box']); ?>><?php echo date('d M');//06 Nov?></option>
						<option value='ddmmyy' <?php selected('ddmmyy', $options['wow_date_box']); ?>><?php echo date('d M Y');//06 Nov 2012?></option>
						<option value='full_date' <?php selected('full_date', $options['wow_date_box']); ?>><?php echo date('D d M Y');//Tues 06 Nov 2012?></option>
						<option value='time_since' <?php selected('time_since', $options['wow_date_box']); ?>><?php echo "about 1 Day Ago"; ?></option>
					</select>
					<span style="color:#666666;margin-left:2px;">If you wish to display a date format </span>
				</td>
			</tr>
			<tr>
				<th scope="row">Use WOW-Twitter CSS? </th>
				<td>
					<input name="wow_twitter[wow_twitter_css]" type="checkbox" value="1" <?php
if (isset($options['wow_twitter_css'])) { checked('1', $options['wow_twitter_css']);}?> />
						<em class="default">Default: True</em><br />
				</td>
			</tr>
			</table>
			<table class="form-table wow_anchor">
					<h3>Single Page Settings</h3>
					<p>Here are the options to display a twitter badge at the end of your single post page.  The badge displays your twitter info, image and follow me button.</p>
			<tr>
				<th scope="row">Display Twitter Badge</th>
				<td>
					<input name="wow_twitter[wow_twitter_badge]" type="checkbox" value="1" <?php
if (isset($options['wow_twitter_badge'])) { checked('1', $options['wow_twitter_badge']);}?> /><br />
				</td>
			</tr>
			</table>
			<table class="form-table">
				<h3>Debugging</h3>
				<p>If you are having any problems connecting to the Twitter API it will appear in your logs with a simplified error message for easy understanding. </p>
			<tr>
				<th scope="row">Log Files</th>
				<td><span id="wowt_debug" class="button-primary">Open Log</span><span id="wowt_check_status" class="button-primary">Check Status</span><span id="wowt_hide_debug" class="button-secondary">Hide Log</span><span id="wowt_clear_log" class="button-secondary">Empty Log</span></td>
			</tr>
			<tr><th scope="row"></th><td><div id="wowt_log"></div></td></tr>
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
			</p>
		</form>
			<p class= "wow_twitr" >If you have found this plugin at all useful, please consider making a <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=HTDDUX2FL4DS6" target="_blank" style="color:#72a1c6;">donation</a>. Thanks.</p>
		<p class="wowt social">
			<a href="https://www.facebook.com/ridgey28" title="Facebook page"  target="_blank"><span class="fbk_icon"></span></a>
			<a href="http://www.twitter.com/worldoweb" title="Follow on Twitter"  target="_blank"><span class="twitr"></span></a>
			<a href="https://plus.google.com/u/0/101259923588931701292/posts"  title="Follow on Google+" target="_blank"><span class="google"></span></a>
			<a href="http://www.worldoweb.co.uk/contact" title="Need Help"  target="_blank"><span class="help"></span></a>
		</p>
</div>