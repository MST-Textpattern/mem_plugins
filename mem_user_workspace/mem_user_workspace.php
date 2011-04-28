<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_user_workspace';

$plugin['version'] = '0.1';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Adds a user workspace for users.';
$plugin['type'] = 1; // 0 for regular plugin; 1 if it includes admin-side code

@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---



# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

////////////////////////////////////////////////////////////
// Plugin 
// Author: Michael Manfre (http://manfre.net/)
// Revisions: 
//
////////////////////////////////////////////////////////////

if (@txpinterface=='admin') {
	register_tab('extensions','workspace','workspace');
	
	register_callback('mem_user_workspace', 'workspace', '', 1);
	add_privs('workspace','1,2,3,4,5,6,7');
	
}

require_plugin('mem_admin_parse');

function mem_user_workspace($event,$step)
{
	if ($step=='install')
		set_pref('mem_user_workspace_form','mem_user_workspace','workspace');
	
	if ($event == 'workspace') {
		$pref = get_pref('mem_user_workspace_form');

		pageTop('User Workspace');
		
		if ($pref) {
			$form = fetch_form($pref['val']);

			echo admin_parse($form);
		}
	}
}

$mem_postback = gps('mem_postback');

if (isset($mem_postback)) {
	switch (strtolower($mem_postback)) {
		case 'uedit':
			mem_save_user_info();
			break;
	}
}

// -------------------------------------------------------------
function mem_change_pass_form($atts,$thing='') 
{
	global $ign_user,$txp_user,$sitename,$mem_profile;
	
	extract(lAtts(array(
		'class'		=> 'mem_password_form',
		'wraptag'	=> '',
		'form'		=> '',
		'form_mail'	=> ''
	),$atts));
	
	$out = '';
	
	if (isset($ign_user)) $txp_user = $ign_user;

	$step = gps('step');
	
	if (isset($step) && $step=='mem_change_pass') {
		$new_pass = gps('new_pass');
		
		$rs = safe_update(
			"txp_users", 
			"pass = password(lower('$new_pass'))",
			"name='$txp_user'"
		);

		if ($rs) {

			// successful
			$mem_profile = safe_row('*','txp_users',"name = '{$txp_user}'");

			if ($mem_profile) {
				if (!empty($form_mail))
					$message = fetch_form($form_mail);
				if (empty($message)) {
					$message = gTxt('greeting').' <txp:mem_realname />,'."\r\n".
						gTxt('your_password_is').": <txp:mem_password />\r\n"."\r\n".
						gTxt('log_in_at').' '.hu.'textpattern/index.php';
				}

				$message = admin_parse($message);

				// email password
				if (txpMail($mem_profile['email'], "[$sitename] ".gTxt('your_new_password'), $message))
					$out = gTxt('password_mailed');
				else
					$out = gTxt('mail_password_failed');
			} else return mysql_error();
		} else {
			return mysql_error();
		}
	} else {
		$Form = $thing;
		if (!empty($form))
			$Form = fetch_form($form);
		if (empty($Form)) {
			$Form = "<h3>Change Password</h3><div><txp:mem_password_input /><txp:mem_submit /></div>";
		}
		$out = 	"<form action='{$_SERVER['REQUEST_URI']}' method='post'>".
				admin_parse($Form).
				eInput('mem_user_workspace').sInput('mem_change_pass').
				"</form>";

//				graf(gTxt('new_password').' '.fInput('password','new_pass','','edit','','','20','1').
					
	}

	return $out;
}

// -------------------------------------------------------------
function mem_user_edit_form($atts, $thing)
{
	global $txpcfg,$txp_user,$ign_user,$mem_profile;

	if (isset($ign_user)) $txp_user = $ign_user;

	extract(lAtts(array(
		'class'		=> 'mem_uedit_form',
		'wraptag'	=> '',
		'form'		=> ''
		), $atts));

	$out = '';
	$message = '';

	$step = gps('step');
	$new_pass = gps('new_pass');
	
	if (isset($step) && $step=='save_user_profile') {
		extract(gpsa(array('email','name','RealName','address','phone')));
		$rs = safe_update('txp_users',
			"email = '{$email}', RealName = '{$RealName}'",
			"name = '{$txp_user}'");
		
		if ($rs) {
			$message = gTxt('saved_user_profile');
		} else {
			$message = mysql_error();
		}
	}

	$rs = safe_row('*','txp_users',"name = '{$txp_user}'");
	
	if ($rs) {
		$mem_profile = $rs;
		
		$Form = $thing;
		if (!empty($form))
			$Form = fetch_form($form);
		
		$Form = str_ireplace('<txp:mem_message />',$message,$Form);

		$out = 	"<form action='{$_SERVER['REQUEST_URI']}' method='post'>".
				admin_parse($Form).
				eInput('mem_user_workspace').sInput('save_user_profile').
				"</form>";
	}

	return doTag($out,$wraptag,$class);
}
function mem_profile($atts)
{
	global $mem_profile;
	extract($atts);
	return in_array($mem_profile,$var) ? $mem_profile[$var] : '';
}
function mem_submit($atts)
{
	extract($atts);
	$class = isset($class) ? $class : 'smallerbox';
	$value = isset($value) ? $value : gTxt('save');
	return fInput("submit",'save',htmlspecialchars($value),$class);
}
function mem_password_input($atts)
{
	global $mem_profile;
	extract($atts);
	$isize = isset($isize) ? $isize : 20;
	$class = isset($class) ? $class : 'edit';

	return fInput('password','new_pass','',$class);
}
function mem_realname_input($atts)
{
	global $mem_profile;
	extract($atts);
	$isize = isset($isize) ? $isize : 20;
	$class = isset($class) ? $class : 'edit';

	return fInput('text','RealName',htmlspecialchars($mem_profile['RealName']),$class);
}
function mem_email_input($atts)
{
	global $mem_profile;
	extract($atts);
	$isize = isset($isize) ? $isize : 20;
	$class = isset($class) ? $class : 'edit';

	return fInput('text','email',htmlspecialchars($mem_profile['email']),$class);
}
function mem_phone_input($atts)
{
	global $mem_profile;
	extract($atts);
	$isize = isset($isize) ? $isize : 20;
	$class = isset($class) ? $class : 'edit';

	return fInput('text','phone',htmlspecialchars($mem_profile['phone']),$class);
}
function mem_address_input($atts)
{
	global $mem_profile;
	extract($atts);
	$isize = isset($isize) ? $isize : 20;
	$class = isset($class) ? $class : 'edit';

	return '<textarea class="'.$class.'" name="address">'.htmlspecialchars($mem_profile['address']).'</textarea>';
}

if (!function_exists('set_pref')) {
	function set_pref($name, $val, $event,  $type, $position=0, $html='text_input') 
	{
		global $pref_cache;
		
		if ($pref_cache==null)
			$pref_cache = array();

		$args = func_get_args();
		$args['html'] = $html;
		$args['position'] = $position;
		$args['prefs_id'] = 1;

		$pref_cache[$name] = $args;

		extract(doSlash($args));
		
    	if (!safe_row("*", 'txp_prefs', "name = '$name'") ) {
        	return safe_insert('txp_prefs', "
				name  = '$name',
				val   = '$val',
				event = '$event',
				html  = '$html',
				type  = '$type',
				position = '$position',
				prefs_id = 1"
			);
    	} else {
        	return safe_update(	'txp_prefs', "
	        						val   = '$val', 
	        						event = '$event', 
	        						html  = '$html',
	        						type  = '$type',
	        						position = '$position'",
        						"name like '$name'");
    	}
    	return false;
	}
}

if (!function_exists('get_pref')) {
	function get_pref($name)
	{
		global $pref_cache;
		
		if ($pref_cache==null)
			$pref_cache = array();

		if (!in_array($name,$pref_cache) or $pref_cache[$name] === false) {
			$pref_cache[$name] = safe_row('*','txp_prefs',"name='$name'");
		} else {
			$pref_cache[$name] = false;			
		}

		return $pref_cache[$name];
	}
}

# --- END PLUGIN CODE ---

?>
