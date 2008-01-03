<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_openid_comments';

// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.1';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Allows users to restrict comments to OpenID authenticated users.';

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
h1(title). mem_openid_comments plugin

h2(section summary). Summary

p. This plugin allows a site owner to require all commenters to authenticate using OpenID.

h2(section contact). Author Contact

"Michael Manfre":mailto:mmanfre@gmail.com?subject=Textpattern%20mem_openid_comments%20plugin
"http://manfre.net":http://manfre.net

h2(section license). License

p. This plugin is licensed under the "GPLv2":http://www.fsf.org/licensing/licenses/info/GPLv2.html.

h2(section tags). Tags

h3(tag#mem_). mem_

p(tag-summary).

h2(section). Exposed Functions

h2(section). Global Variables

h2(section). Plugin Events

h3(event). 

p(event-summary).

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

require_plugin('mem_openid');

if (txpinterface == 'public')
{
	register_callback('mem_openid_comments_form','comment.form');
	register_callback('mem_openid_comments_save','comment.save');
}

if (isset($_SESSION['mem_openid_authenticated']))
{
	$mem_c = unserialize($_SESSION['mem_openid_comment']);
	$_POST['message'] = $mem_c['message'];
	
	unset($_SESSION['mem_openid_authenticated'], $_SESSION['mem_openid_comment']);
}

function mem_openid_comments_form($event, $step='')
{	
	$openid = gps('openid_url');
	
		$return_to = PROTOCOL.serverSet('HTTP_HOST').serverSet('REDIRECT_URL');
	
		$id = new memOpenID($openid, hu, $return_to);
		$id->add_policy_uri( PAPE_AUTH_MULTI_FACTOR_PHYSICAL, PAPE_AUTH_MULTI_FACTOR, PAPE_AUTH_PHISHING_RESISTANT );

	if (!isset($_SESSION['mem_openid_url']) and !empty($openid) and ps('preview') and !$id->is_response())
	{
		// need to temp store input in db to pull back out after the redirect
		$_SESSION['mem_openid_comment'] = serialize(getComment());
		$_SESSION['mem_openid_authenticate'] = true;

		$id->authenticate();
	}
	elseif ($id->is_response())
	{
		$info = $id->complete();
		
		$_SESSION['mem_openid_url'] = $openid;
		$_SESSION['mem_openid_info'] = serialize($info);
	}
	
	return '</td></tr><tr><td><label for="openid_url">OpenID Identity</label></td><td><input type="text" name="openid_url" id="openid_url" value="'.htmlspecialchars($openid).'" style="background-image: url(http://www.openidenabled.com/favicon.ico);background-repeat: no-repeat;padding-left:16px;" />';
}

function mem_openid_comments_save()
{	
	if (!isset($_SESSION['mem_openid_url']))
	{
		$evaluator =& get_comment_evaluator();

		$evaluator -> add_estimate(RELOAD, 1, 'OpenID error.');
	}

}

# --- END PLUGIN CODE ---

?>
