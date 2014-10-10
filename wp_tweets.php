<?php 
/*
Plugin Name: WP Tweets w/ API v1.1
Plugin URI: https://github.com/eintnohick/wp_tweets
Description: Small, single file plugin using Twitter API v1.1 to fetch recent tweets by username.
Author: Justin Ross
Version: 1.0
Author URI: http://merkent.com/
*/
function wp_tweets_register_admin_menu_page() {
	add_menu_page('WP Tweets', 'WP Tweets', 'manage_options', 'wp_tweets_widget', 'wp_tweets_admin_menu_page');
}
add_action('admin_menu', 'wp_tweets_register_admin_menu_page');

function wp_tweets_admin_menu_page() {
	if (!empty($_POST['wp_tweets_save_settings'])) {
		$consumer_key = trim($_POST['wp_tweets_consumer_key']);
		$consumer_secret = trim($_POST['wp_tweets_consumer_secret']);
		$access_token = trim($_POST['wp_tweets_access_token']);
		$access_token_secret = trim($_POST['wp_tweets_access_token_secret']);
		
		update_option('wp_tweets_settings', array(
			'consumer_key' => $consumer_key,
			'consumer_secret' => $consumer_secret,
			'access_token' => $access_token,
			'access_token_secret' => $access_token_secret
		));
	} else {
		$config = get_option('wp_tweets_settings');

		$consumer_key = $config['consumer_key'];
		$consumer_secret = $config['consumer_secret'];
		$access_token = $config['access_token'];
		$access_token_secret = $config['access_token_secret'];
	}
?>
<h3>Twitter API settings</h3>
<ol>
	<li>To use, a Twitter app must be created. To create an app, go to <a href="https://dev.twitter.com/apps" target="_blank">dev.twitter.com/apps</a>.</li>
	<li>Once you have logged in fill in the form fields, name your API and generate the Keys needed.</li>
	<li>When you have an app, go to the <a href="<?php echo site_url(); ?>/wp-admin/widgets.php">Widget page</a> to configure twitter widget.</li>
</ol>
<form id="wp_tweets_account_config" method="post" action="">
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="wp_tweets_consumer_key">Consumer Key:</label></th>
				<td><input type="text" name="wp_tweets_consumer_key" id="wp_tweets_consumer_key" value="<?php echo $consumer_key; ?>" autofocus="" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><label for="wp_tweets_consumer_secret">Consumer Secret:</label></th>
				<td><input type="text" name="wp_tweets_consumer_secret" id="wp_tweets_consumer_secret" value="<?php echo $consumer_secret; ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><label for="wp_tweets_access_token">Access Token:</label></th>
				<td><input type="text" name="wp_tweets_access_token" id="wp_tweets_access_token" value="<?php echo $access_token; ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><label for="wp_tweets_access_token_secret">Access Token Secret:</label></th>
				<td><input type="text" name="wp_tweets_access_token_secret" id="wp_tweets_access_token_secret" value="<?php echo $access_token_secret; ?>" class="regular-text"></td>
			</tr>
		</tbody>
	</table>
	<p class="submit"><input type="submit" name="wp_tweets_save_settings" id="wp_tweets_save_settings" class="button button-primary" value="Save Changes"></p>
</form>
<?php }

class WP_Tweets_Widget extends WP_Widget{
	function __construct() {
		parent::WP_Widget('wp_tweets_widget', 'WP Tweets w/ API v1.1', array('description' => 'Super simple custom tweets widget'));
	}
	public function widget($args, $instance) {
		extract($args);
		$title = apply_filters('widget_title', $instance['wp_tweets_title']);
		echo $before_widget;
		if ($title) {
			echo $before_title . $title . $after_title;
		}

		if (count(get_option('wp_tweets_settings')) == 4) {
			$tweets = wp_tweets_output($instance['wp_tweets_username'], $instance['wp_tweets_number']);

			if ($tweets) { ?>
			<div class="wp_tweets">
				<ul>
				<?php foreach ($tweets as $tweet) { ?>
					<li class="tweet">
						<p><a href="<?php echo $tweet['user']['url']; ?>"><?php echo $tweet['user']['screen_name']; ?></a> <?php echo format_tweet($tweet['text']) ?></p>
						<p class="tweet_meta"><a target="_blank" href="<?php echo $tweet['user']['url']; ?>"><?php echo time_since($tweet['created_at']); ?></a></p>
					</li>
				<?php } ?>
				</ul>
			</div>
			<?php }
		} else { ?>
			<p>Please configure your twitter API settings</p>
		<?php }
		echo $after_widget;
	}
	
	public function form($instance) { 
		if (count(get_option('wp_tweets_settings')) < 4) { ?>
			<p>Please configure your twitter API settings first.</p>
			<p><a class="button-secondary" href="<?php echo site_url(); ?>'/wp-admin/admin.php?page=wp_tweets_widget">Configure app settings</a></p>
		<?php return;
		} else { 
			$title = isset($instance['wp_tweets_title']) ? esc_attr($instance['wp_tweets_title']) : '';
			$username = isset($instance['wp_tweets_username']) ? esc_attr($instance['wp_tweets_username']) : '';
			$number = isset($instance['wp_tweets_number']) ? esc_attr($instance['wp_tweets_number']) : 4; ?>
		<div class="widget-content">
			<p>Twitter API settings must be configured before your widget will work. <a href="/wp-admin/admin.php?page=wp_tweets_widget">Click here</a> to configure settings.</p>
			<p><label for="<?php echo $this->get_field_id('wp_tweets_title'); ?>"><strong><?php _e('Title:'); ?></strong></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('wp_tweets_title'); ?>" name="<?php echo $this->get_field_name('wp_tweets_title'); ?>" value="<?php echo $title; ?>"></p>
			<p><label for="<?php echo $this->get_field_id('wp_tweets_username'); ?>"><strong><?php _e('Twitter Handle:'); ?></strong></label>
			<input type="text" class="widefat"	id="<?php echo $this->get_field_id('wp_tweets_username'); ?>" name="<?php echo $this->get_field_name('wp_tweets_username'); ?>" value="<?php echo $username; ?>"></p>
			<p><label for="<?php echo $this->get_field_id('wp_tweets_number'); ?>"><strong><?php _e('Tweet Count:'); ?></strong></label>
			<input type="number" min="1" max="22" class="regular-text" id="<?php echo $this->get_field_id('wp_tweets_number'); ?>" name="<?php echo $this->get_field_name('wp_tweets_number'); ?>" value="<?php echo $number; ?>"></p>
		</div>
		<?php }
	}
}
function register_wp_tweets_widget() {
    register_widget('WP_Tweets_Widget');
}
add_action('widgets_init', 'register_wp_tweets_widget');

function time_since($time) {
	$since = time() - strtotime($time);

	$chunks = array(
		array(60 * 60 * 24 * 365, 'year'),
		array(60 * 60 * 24 * 30, 'month'),
		array(60 * 60 * 24 * 7, 'week'),
		array(60 * 60 * 24, 'day'),
		array(60 * 60, 'hour'),
		array(60, 'minute'),
		array(1, 'second')
	);

	for ($i = 0, $j = count($chunks);$i < $j;$i++) {
		$seconds = $chunks[$i][0];
		$name = $chunks[$i][1];
		if (($count = floor($since / $seconds)) != 0) {
			break;
		}
	}

	return ($count == 1) ? '1 ' . $name . ' ago' :$count . ' ' . $name . 's ago';
}

function format_tweet($string) {
	$string = preg_replace('/(http:\/\/[^\s]+)/', '<a href="$1" target="_blank">$1</a>', $string);
	$string = preg_replace('/\B\@([a-zA-Z0-9_]{1,20})/', '<a href="http://twitter.com/$1" target="_blank">$0</a>', $string);
	$string = preg_replace('/(^|\s)#(\w*[a-zA-Z_]+\w*)/', '\1<a href="http://twitter.com/search?q=%23\2" target="_blank">#\2</a>', $string);
	
	return $string;
}

function buildBaseString($baseURI, $method, $params) {
	$r = array();
	ksort($params);
	foreach($params as $key=>$value){
		$r[] = "$key=" . rawurlencode($value);
	}
	return $method."&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
}

function buildAuthorizationHeader($oauth) {
	$r = 'Authorization: OAuth ';
	$values = array();
	foreach($oauth as $key=>$value)
		$values[] = "$key=\"" . rawurlencode($value) . "\"";
	$r .= implode(', ', $values);
	return $r;
}

function wp_tweets_output($screen_name, $count) {
	$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";

	$config = get_option('wp_tweets_settings');

	$oauth = array(
		'screen_name' => $screen_name,
		'count' => $count,
		'oauth_consumer_key' => $config['consumer_key'],
		'oauth_nonce' => time(),
		'oauth_signature_method' => 'HMAC-SHA1',
		'oauth_token' => $config['access_token'],
		'oauth_timestamp' => time(),
		'oauth_version' => '1.0'
	);

	$base_info = buildBaseString($url, 'GET', $oauth);
	$composite_key = rawurlencode($config['consumer_secret']) . '&' . rawurlencode($config['access_token_secret']);
	$oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
	$oauth['oauth_signature'] = $oauth_signature;

	$header = array(buildAuthorizationHeader($oauth), 'Expect:');
	$options = array(
		CURLOPT_HTTPHEADER => $header,
		CURLOPT_HEADER => false,
		CURLOPT_URL => $url . '?screen_name='.$screen_name.'&count='.$count,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 10,
		CURLOPT_SSL_VERIFYPEER => false
	);

	$feed = curl_init();
	curl_setopt_array($feed, $options);
	$json = curl_exec($feed);
	curl_close($feed);
	
	return json_decode($json, true);
}

/* This should be customizable */
function wp_tweets_css() { 
	echo '<style>
	.wp_tweets{word-wrap:break-word;}
	.wp_tweets li{list-style:none;padding:7px 0;border-bottom:1px dotted #ddd;}
	.wp_tweets li a{text-decoration:none;color:#497DB5;}
	.wp_tweets li p{font-size:12px;margin:0;color:#81667D;line-height:1.5;}
	.wp_tweets li p.tweet_meta a{font-size:11px;line-height:16px;}
	</style>';
}		
add_action('wp_head', 'wp_tweets_css');




