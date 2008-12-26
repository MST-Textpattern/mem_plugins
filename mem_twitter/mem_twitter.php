<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_twitter';

// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.1';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'This plugin will post to twitter whenever an article is published.';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = 1; 

if (!defined('txpinterface'))
	@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h1(title). mem_twitter plugin

h2(section summary). Summary

p. This plugin will ping twitter whenever you post an article. This will only notify twitter when in site is in live mode.

h2(section contact). Author Contact

"Michael Manfre":mailto:mmanfre@gmail.com?subject=Textpattern%20mem_twitter%20plugin
"http://manfre.net":http://manfre.net

h2(section license). License

p. This plugin is licensed under the "GPLv2":http://www.fsf.org/licensing/licenses/info/GPLv2.html.

h2(section installation). Installation

p. Go to advanced preferences and enter your twitter username, password and post message.

h2(section). Preferences

h3. mem_twitter_user

p. This is your twitter username.

h3. mem_twitter_pass

p. This is your twitter password.

h3. mem_twitter_msg

p. This is the message format that will be posted to twitter. The string will be parsed and all instances of "{url}" will be replaced with the url to the article. All instances of "{title}" will be replaced with the article title.


# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

global $prefs;

if (!isset($prefs['mem_twitter_user']))
	set_pref('mem_twitter_user', '', 'mem_twitter', 1, 'text_input');
if (!isset($prefs['mem_twitter_pass']))
	set_pref('mem_twitter_pass','', 'mem_twitter', 1, 'mem_twitter_password_input');
if (!isset($prefs['mem_twitter_msg']))
	set_pref('mem_twitter_msg', 'Blog Post: {title} - {url}', 'mem_twitter', 1, 'text_input');

if (txpinterface == 'admin')
{
	function mem_twitter_password_input($name, $val)
	{
		return fInput('password', $name, $val, 'edit', '', '', '', '', $name);
	}

	register_callback('mem_twitter_ping','ping');

	function mem_twitter_ping()
	{
		global $mem_twitter_user, $mem_twitter_pass, $mem_twitter_msg;

		// do nothing without a provided user/pass		
		if (empty($mem_twitter_user) || empty($mem_twitter_pass))
			return;
		
		$article_id = empty($GLOBALS['ID']) ? ps('ID') : $GLOBALS['ID'];
		
		if (!empty($article_id))
		{
			$article = safe_row('ID, title, url_title, Section, unix_timestamp(Posted) as Posted', 'textpattern', "ID={$article_id}");

			if ($article)
			{
				if (!function_exists('permlinkurl'))
				{
					require_once(txpath.'/publish/taghandlers.php');
				}
				
				$t = new Twitter($mem_twitter_user, $mem_twitter_pass, 'mem_twitter');
				$msg = empty($mem_twitter_msg) ? '{title} - {url}' : $mem_twitter_msg;
				$msg = str_replace('{title}', $article['title'], $msg);

				
				if (strpos($msg, '{url}') !== false)
				{
					$url = permlinkurl($article);
					// get tinyurl for txp article
					if ($url)
						$url = mem_tinyurl( $url );
					else
						$url = hu;

					$msg = str_replace('{url}', $url, $msg);
				}

				$t->updateStatus($msg);
			}
		}
	}
	

	/** Return a tinyurl of the passed url */
	function mem_tinyurl($url)
	{
		$u = 'http://tinyurl.com/create.php?url=' . urlencode($url);
		$page = file_get_contents($u);
		if (preg_match('/<blockquote><b>(http:.*)<\/b>/i', $page, $m))
		{
			return $m[1];
		}
		
		return false;
	}
		
	/*
	* Copyright (c) <2008> Justin Poliey <jdp34@njit.edu>
	*
	* Permission is hereby granted, free of charge, to any person
	* obtaining a copy of this software and associated documentation
	* files (the "Software"), to deal in the Software without
	* restriction, including without limitation the rights to use,
	* copy, modify, merge, publish, distribute, sublicense, and/or sell
	* copies of the Software, and to permit persons to whom the
	* Software is furnished to do so, subject to the following
	* conditions:
	*
	* The above copyright notice and this permission notice shall be
	* included in all copies or substantial portions of the Software.
	*
	* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
	* EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
	* OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
	* NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
	* HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
	* WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
	* FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
	* OTHER DEALINGS IN THE SOFTWARE.
	*/
	 
	class Twitter {
	  /* Username:password format string */
	  private $credentials;
	  
	  /* Contains the last HTTP status code returned */
	  private $http_status;
	  
	  /* Contains the last API call */
	  private $last_api_call;
	  
	  /* Contains the application calling the API */
	  private $application_source;
	 
	  /* Twitter class constructor */
	  function Twitter($username, $password, $source=false) {
	    $this->credentials = sprintf("%s:%s", $username, $password);
	    $this->application_source = $source;
	  }
	  
	  function getPublicTimeline($format, $since_id = 0) {
	    $api_call = sprintf("http://twitter.com/statuses/public_timeline.%s", $format);
	    if ($since_id > 0) {
	      $api_call .= sprintf("?since_id=%d", $since_id);
	    }
	    return $this->APICall($api_call);
	  }
	  
	  function getFriendsTimeline($format, $id = NULL, $since = NULL) {
	    if ($id != NULL) {
	      $api_call = sprintf("http://twitter.com/statuses/friends_timeline/%s.%s", $id, $format);
	    }
	    else {
	      $api_call = sprintf("http://twitter.com/statuses/friends_timeline.%s", $format);
	    }
	    if ($since != NULL) {
	      $api_call .= sprintf("?since=%s", urlencode($since));
	    }
	    return $this->APICall($api_call, true);
	  }
	  
	  function getUserTimeline($format, $id = NULL, $count = 20, $since = NULL) {
	    if ($id != NULL) {
	      $api_call = sprintf("http://twitter.com/statuses/user_timeline/%s.%s", $id, $format);
	    }
	    else {
	      $api_call = sprintf("http://twitter.com/statuses/user_timeline.%s", $format);
	    }
	    if ($count != 20) {
	      $api_call .= sprintf("?count=%d", $count);
	    }
	    if ($since != NULL) {
	      $api_call .= sprintf("%ssince=%s", (strpos($api_call, "?count=") === false) ? "?" : "&", urlencode($since));
	    }
	    return $this->APICall($api_call, true);
	  }
	  
	  function showStatus($format, $id) {
	    $api_call = sprintf("http://twitter.com/statuses/show/%d.%s", $id, $format);
	    return $this->APICall($api_call);
	  }
	  
	  function updateStatus($status) {
	    $status = urlencode(stripslashes(urldecode($status)));
	    $api_call = sprintf("http://twitter.com/statuses/update.xml?status=%s", $status);
	    return $this->APICall($api_call, true, true);
	  }
	  
	  function getReplies($format, $page = 0) {
	    $api_call = sprintf("http://twitter.com/statuses/replies.%s", $format);
	    if ($page) {
	      $api_call .= sprintf("?page=%d", $page);
	    }
	    return $this->APICall($api_call, true);
	  }
	  
	  function destroyStatus($format, $id) {
	    $api_call = sprintf("http://twitter.com/statuses/destroy/%d.%s", $id, $format);
	    return $this->APICall($api_call, true, true);
	  }
	  
	  function getFriends($format, $id = NULL) {
	    // take care of the id parameter
	    if ($id != NULL) {
	      $api_call = sprintf("http://twitter.com/statuses/friends/%s.%s", $id, $format);
	    }
	    else {
	      $api_call = sprintf("http://twitter.com/statuses/friends.%s", $format);
	    }
	    return $this->APICall($api_call, true);
	  }
	  
	  function getFollowers($format, $lite = NULL) {
	    $api_call = sprintf("http://twitter.com/statuses/followers.%s%s", $format, ($lite) ? "?lite=true" : NULL);
	    return $this->APICall($api_call, true);
	  }
	  
	  function getFeatured($format) {
	    $api_call = sprintf("http://twitter.com/statuses/featured.%s", $format);
	    return $this->APICall($api_call);
	  }
	  
	  function showUser($format, $id, $email = NULL) {
	    if ($email == NULL) {
	      $api_call = sprintf("http://twitter.com/users/show/%s.%s", $id, $format);
	    }
	    else {
	      $api_call = sprintf("http://twitter.com/users/show.xml?email=%s", $email);
	    }
	    return $this->APICall($api_call, true);
	  }
	  
	  function getMessages($format, $since = NULL, $since_id = 0, $page = 1) {
	    $api_call = sprintf("http://twitter.com/direct_messages.%s", $format);
	    if ($since != NULL) {
	      $api_call .= sprintf("?since=%s", urlencode($since));
	    }
	    if ($since_id > 0) {
	      $api_call .= sprintf("%ssince_id=%d", (strpos($api_call, "?since") === false) ? "?" : "&", $since_id);
	    }
	    if ($page > 1) {
	      $api_call .= sprintf("%spage=%d", (strpos($api_call, "?since") === false) ? "?" : "&", $page);
	    }
	    return $this->APICall($api_call, true);
	  }
	  
	  function getSentMessages($format, $since = NULL, $since_id = 0, $page = 1) {
	    $api_call = sprintf("http://twitter.com/direct_messages/sent.%s", $format);
	    if ($since != NULL) {
	      $api_call .= sprintf("?since=%s", urlencode($since));
	    }
	    if ($since_id > 0) {
	      $api_call .= sprintf("%ssince_id=%d", (strpos($api_call, "?since") === false) ? "?" : "&", $since_id);
	    }
	    if ($page > 1) {
	      $api_call .= sprintf("%spage=%d", (strpos($api_call, "?since") === false) ? "?" : "&", $page);
	    }
	    return $this->APICall($api_call, true);
	  }
	  
	  function newMessage($format, $user, $text) {
	    $text = urlencode(stripslashes(urldecode($text)));
	    $api_call = sprintf("http://twitter.com/direct_messages/new.%s?user=%s&text=%s", $format, $user, $text);
	    return $this->APICall($api_call, true, true);
	  }
	  
	  function destroyMessage($format, $id) {
	    $api_call = sprintf("http://twitter.com/direct_messages/destroy/%s.%s", $id, $format);
	    return $this->APICall($api_call, true, true);
	  }
	  
	  function createFriendship($format, $id) {
	    $api_call = sprintf("http://twitter.com/friendships/create/%s.%s", $id, $format);
	    return $this->APICall($api_call, true, true);
	  }
	  
	  function destroyFriendship($format, $id) {
	    $api_call = sprintf("http://twitter.com/friendships/destroy/%s.%s", $id, $format);
	    return $this->APICall($api_call, true, true);
	  }
	  
	  function friendshipExists($format, $user_a, $user_b) {
	    $api_call = sprintf("http://twitter.com/friendships/exists.%s?user_a=%s&user_b=%s", $format, $user_a, $user_b);
	    return $this->APICall($api_call, true);
	  }
	  
	  function verifyCredentials($format = NULL) {
	    $api_call = sprintf("http://twitter.com/account/verify_credentials%s", ($format != NULL) ? sprintf(".%s", $format) : NULL);
	    return $this->APICall($api_call, true);
	  }
	  
	  function endSession() {
	    $api_call = "http://twitter.com/account/end_session";
	    return $this->APICall($api_call, true);
	  }
	  
	  function updateLocation($format, $location) {
	    $api_call = sprintf("http://twitter.com/account/update_location.%s?location=%s", $format, $location);
	    return $this->APICall($api_call, true, true);
	  }
	  
	  function updateDeliveryDevice($format, $device) {
	    $api_call = sprintf("http://twitter.com/account/update_delivery_device.%s?device=%s", $format, $device);
	    return $this->APICall($api_call, true, true);
	  }
	  
	  function rateLimitStatus($format) {
	    $api_call = sprintf("http://twitter.com/account/rate_limit_status.%s", $format);
	    return $this->APICall($api_call, true);
	  }
	  
	  function getArchive($format, $page = 1) {
	    $api_call = sprintf("http://twitter.com/account/archive.%s", $format);
	    if ($page > 1) {
	      $api_call .= sprintf("?page=%d", $page);
	    }
	    return $this->APICall($api_call, true);
	  }
	  
	  function getFavorites($format, $id = NULL, $page = 1) {
	    if ($id == NULL) {
	      $api_call = sprintf("http://twitter.com/favorites.%s", $format);
	    }
	    else {
	      $api_call = sprintf("http://twitter.com/favorites/%s.%s", $id, $format);
	    }
	    if ($page > 1) {
	      $api_call .= sprintf("?page=%d", $page);
	    }
	    return $this->APICall($api_call, true);
	  }
	  
	  function createFavorite($format, $id) {
	    $api_call = sprintf("http://twitter.com/favorites/create/%d.%s", $id, $format);
	    return $this->APICall($api_call, true, true);
	  }
	  
	  function destroyFavorite($format, $id) {
	    $api_call = sprintf("http://twitter.com/favorites/destroy/%d.%s", $id, $format);
	    return $this->APICall($api_call, true, true);
	  }
	  
	  function follow($format, $id) {
	    $api_call = sprintf("http://twitter.com/notifications/follow/%d.%s", $id, $format);
	    return $this->APICall($api_call, true, true);
	  }
	  
	  function leave($format, $id) {
	    $api_call = sprintf("http://twitter.com/notifications/leave/%d.%s", $id, $format);
	    return $this->APICall($api_call, true, true);
	  }
	  
	  function createBlock($format, $id) {
	    $api_call = sprintf("http://twitter.com/blocks/create/%d.%s", $id, $format);
	    return $this->APICall($api_call, true, true);
	  }
	  
	  function destroyBlock($format, $id) {
	    $api_call = sprintf("http://twitter.com/blocks/destroy/%d.%s", $id, $format);
	    return $this->APICall($api_call, true, true);
	  }
	  
	  function test($format) {
	    $api_call = sprintf("http://twitter.com/help/test.%s", $format);
	    return $this->APICall($api_call, true);
	  }
	  
	  function downtimeSchedule($format) {
	    $api_call = sprintf("http://twitter.com/help/downtime_schedule.%s", $format);
	    return $this->APICall($api_call, true);
	  }
	  
	  private function APICall($api_url, $require_credentials = false, $http_post = false) {
	    $curl_handle = curl_init();
	    if($this->application_source){
	      $api_url .= "&source=" . $this->application_source;
	    }
	    curl_setopt($curl_handle, CURLOPT_URL, $api_url);
	    if ($require_credentials) {
	      curl_setopt($curl_handle, CURLOPT_USERPWD, $this->credentials);
	    }
	    if ($http_post) {
	      curl_setopt($curl_handle, CURLOPT_POST, true);
	    }
	    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
	    $twitter_data = curl_exec($curl_handle);
	    $this->http_status = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
	    $this->last_api_call = $api_url;
	    curl_close($curl_handle);
	    return $twitter_data;
	  }
	  
	  function lastStatusCode() {
	    return $this->http_status;
	  }
	  
	  function lastAPICall() {
	    return $this->last_api_call;
	  }
	}
}
# --- END PLUGIN CODE ---

?>
