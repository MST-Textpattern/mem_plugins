<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_self_register';

$plugin['version'] = '0.8.3';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'User self registration. Read the help to install.';
$plugin['type'] = 1; // 0 for regular plugin; 1 if it includes admin-side code

@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h2. User Self Registration

h3. Installation

p. "Start Install Wizard":./index.php?event=self-reg&step=preinstall&area=admin

h3. Summary

p. This plugin is designed to enable community driven sites. Users will be able to register themselves for an account to access the system. This plugin is compatible with the ign_user_password plugin alternate user table. This plugin provides user side tags for generating an html form where users can edit their account information.

h3. Client Plug-in Tags:

* *mem_self_register_form*
* *self_register_email_message*
* *self_register_status_message*
* *if_self_registered*
* *mem_change_password_form*
* *mem_user_edit_form*
* *mem_profile*

<hr />

h4. mem_self_register_form

p. This will output an HTML form that will allow a user to register themselves with an account.

p. Tag Attributes:

* *form* -- Specifies which form contains the HTML form template. Default is "self_register_form".
* *email_form* -- Specifies which form contains the email message template that will be sent to a user upon registering. Default is "self_register_email".
* *wraptag* -- The HTML tag that will wrap the generated form. Default is none.
* *isize* -- The size of the input fields. Default is 25.
* *label* -- The text to place on the submit button. Default is the localized "submit".
* *namewarn* -- An error message that will be displayed to the user when the name field is left blank.
* *userwarn* -- An error message that will be displayed to the user when the user field is left blank.
* *emailwarn* -- An error message that will be displayed to the user when the email field is left blank.
* *class* -- The css style class to add to the wraptag. Default is "mem_self_register_form".

p. Template tags that can be used within the HTML template form.

* <code><txp:mem_name_warn /></code> -- This will display the contents of namewarn when the name_input field is left blank.
* <code><txp:mem_user_warn /></code> -- This will display the contents of userwarn when the user_input field is left blank.
* <code><txp:mem_email_warn /></code> -- This will display the contents of emailwarn when the email_input field is left blank.
* <code><txp:mem_name_input /></code> -- This will display the name field.
* <code><txp:mem_user_input /></code> -- This will display the user field.
* <code><txp:mem_email_input /></code> -- This will display the email field.
* <code><txp:mem_submit /></code> -- This will display the submit button.

p. Starter Template:

<code>
Name: <txp:mem_name_input /><br />
User Name: <txp:mem_user_input /><br />
Email: <txp:mem_email_input /><br />
<txp:mem_submit />
</code>

p. Template tags that can be used within the email template form. In addition to the tags listed below, any other Textpattern tag can be used.

* <code><txp:mem_name /></code> -- The name submitted by the user.
* <code><txp:mem_username /></code> -- The username submitted by the user.
* <code><txp:mem_email /></code> -- The email address submitted by the user.
* <code><txp:mem_password /></code> -- The password generated for the user.
* <code><txp:mem_sitename /></code> -- The site name as specified in the Site Configuration (prefs tab).
* <code><txp:mem_siteurl /></code> -- The url to the front page of this site.
* <code><txp:mem_loginurl /></code> -- The url to the Textpattern log in page.
* <code><txp:mem_admin_name /></code> -- The name of the administrative contact. This will automatically be in the email from field.
* <code><txp:mem_admin_email /></code> -- The email address of the administrative contact. This will automatically be in the email from field.

p. Starter Template:

<code>
Dear <txp:mem_name />,
  Thank you for registering for <txp:mem_sitename />. Below are you user account details.

Username: <txp:mem_username />
Password: <txp:mem_password />
Site URL: <txp:mem_siteurl />
Login URL: <txp:mem_loginurl />

Sincerely,
<txp:mem_admin_name />
Email: <txp:mem_admin_email />
</code>


p. Example:

==<code>
<txp:mem_self_register_form wraptag="div" isize="20" label="Register" namewarn="Name is required" userwarn="Username is required" emailwarn="Email address is required" />
</code>==

<hr />

h4. self_register_status_message

p. This will output the status message generated after submitting the self registration form.

<hr />

h4. self_register_email_message

p. After the form is submitted, this will output the entire email message that was sent to the user.

<hr />

h4. if_self_registered

p. This will output the contents of the tag if the user has already been registered. To work properly, this requires browser cookies after the initial form submission. This tag supports <code><txp:else /></code>

p. Example:

==<code>
<txp:if_self_registered>
You already have an account.
<txp:else />
<txp:mem_self_register_form />
</txp:if_self_registered>
</code>==

<hr />

h4. mem_change_password_form

p. This tag allows a change password form to be displayed on the user portion of the website. This is meant to be used with the ign_password_protect plugin. This tag supports the tag txp:mem_profile (see below).

p. Tag Attributes:

* *form* -- Specifies which form contains the HTML form template. This form will be reparsed by Txp. Default is none. If not specified, a default form will be used.
* *form_mail* -- Specifies which form contains the message template that will be used to create the message that will be emailed to the user. Default is none. If not specified, a default message template will be used.
* *wraptag* -- The HTML tag that will wrap the generated form. Default is none.
* *class* -- The css style class to add to the wraptag. Default is "mem_password_form".

p. Template tags that can be used within the 'form' template form.

* <code><txp:mem_password_input /></code> -- This will display a password form field
* <code><txp:mem_submit /></code> -- This will display a form submit button
	
p. Template tags that can be used within the 'form_mail' template form.

* <code><txp:mem_realname /></code> -- This will be replaced with the user's real name.
* <code><txp:mem_password /></code> -- This will be replaced with the user's password.

<hr />

h4. mem_user_edit_form

p. This tag allows a form to modify user information to be displayed on the user portion of the website. This is meant to be used with the ign_password_protect plugin. This tag supports the tag txp:mem_profile.

p. Tag Attributes:

* *form* -- Specifies which form contains the HTML form template. This form will be reparsed by Txp. Default is none.
* *wraptag* -- The HTML tag that will wrap the generated form. Default is none.
* *class* -- The css style class to add to the wraptag. Default is "mem_uedit_form".

p. Template tags that can be used within the HTML template form.

* <code><txp:mem_message /></code> -- This will be replaced with the message generated from submitting the form.
* <code><txp:mem_realname_input /></code> -- This will display a form field for changing the user's Real Name.
* <code><txp:mem_email_input /></code> -- This will display a form field for changing the user's email address.
* <code><txp:mem_submit /></code> -- This will display a form submit button

h4. mem_profile

p. This tag will output the values of the user's profile.

p. Tag Attributes:

* *var* -- Specifies the profile value name to output. Supported values are "RealName","email", and "new_pass".


# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

////////////////////////////////////////////////////////////
// Plugin mem_self_register
// Author: Michael Manfre (http://manfre.net/)
////////////////////////////////////////////////////////////

if (!function_exists('mem_set_pref')) {
	function mem_set_pref($name, $val, $event,  $type, $position=0, $html='text_input') 
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

if (!function_exists('mem_get_pref')) {
	function mem_get_pref($name,$val='')
	{
		global $pref_cache;
		
		if ($pref_cache==null)
			$pref_cache = array();

		if (!in_array($name,$pref_cache) or $pref_cache[$name] === false) {
			$pref_cache[$name] = safe_row('*','txp_prefs',"name='$name'");
		} else {
			$pref_cache[$name] = false;			
		}

		if (!empty($val) && array_key_exists($val,$pref_cache[$name])) {
			return $pref_cache[$name][$val];
		} else {
			return $pref_cache[$name];
		}
	}
}

global $mem_self_lang;

if (!is_array($mem_self_lang))
{
	$mem_self_lang = array(
		'your_login_info'	=>	'Your Login Info',
		'admin_name'		=>	'Admin Name',
		'admin_email'		=>	'Admin Email',
		'password_sent_to'	=>	'Password Sent To',
		'error_adding_new_author'	=>	'Error adding new author',
		'greeting'			=>	'Hello',
		'your_password_is'	=>	'Your password is',
		'your_new_password'	=>	'Your new password',
		'password_changed'	=>	'Password changed',
		'password_change_failed'	=>	'Failed to change password',
		'log_in_at'			=>	'Log in at',
		'saved_user_profile'	=>	'Saved User Profile',
		'user_exists'		=>	'Username already exists. Please try another name',
	);
}

function mem_self_gTxt($name)
{
	global $mem_self_lang;
	
	$gtxt = gTxt($name);
	
	if ( strcmp($gtxt,$name) == 0 )
	{
		if ( array_key_exists($name,$mem_self_lang) )
			$gtxt = $mem_self_lang[$name];
	}
	
	return $gtxt;
}

global $event;

if (!isset($event)) $event = '';

if ($event != 'admin') {
	require_once txpath.'/include/txp_admin.php';

	global $levels;

	// copied from txp_admin.php
	$levels = array(
		0 => mem_self_gTxt('none'),
		6 => mem_self_gTxt('designer'),
		5 => mem_self_gTxt('freelancer'),
		4 => mem_self_gTxt('staff_writer'),
		3 => mem_self_gTxt('copy_editor'),
		2 => mem_self_gTxt('managing_editor'),
		1 => mem_self_gTxt('publisher')
	);
	
//-------------------------------------------------------------
	if (!function_exists('priv_levels')) {
		function priv_levels($item, $var) {
			global $levels;
	
			return selectInput($item, $levels, $var);
		}
	}	
}


global $mem_self;

$mem_self = array(
				'admin_email'		=>	'',
				'admin_name'		=>	'',
				'new_user_priv'		=>	'0',
				'status'			=>	false,
				'status_message'	=>	'You are already registered.',
				'email_message'		=>	''				
			);

$mem_self['admin_email'] = mem_get_pref('mem_self_admin_email','val');
$mem_self['admin_name'] = mem_get_pref('mem_self_admin_name','val');
$mem_self['new_user_priv'] = mem_get_pref('mem_self_new_user_priv','val');

if ( @txpinterface == 'admin' ) {
	register_callback('mem_self_register','self-reg','', 1);
	if ($event=='self-reg') {
		// fake tabs when using them. Silences warnings from pageTop()
		register_tab('admin','self-reg','self-reg');

		include_once txpath.'/publish/taghandlers.php';
	}
	
	function mem_self_register($event, $step) {
		if ($event!='self-reg')
			return;

		pageTop('Self Registration','');

		if ($step == 'install') {
			echo mem_self_register_install();
		} else if ($step=='preinstall') {
			$mem_admin_name	= mem_get_pref('mem_self_admin_name');
			$mem_admin_email	= mem_get_pref('mem_self_admin_email');
			$mem_new_user_priv	= mem_get_pref('mem_self_new_user_priv');
			$mem_use_ign_db	= mem_get_pref('mem_self_use_ign_db');
			$mem_xtra_columns	= mem_get_extra_user_columns();

			$mem_admin_name = @$mem_admin_name ? $mem_admin_name['val'] : 'Admin';
			$mem_admin_email = @$mem_admin_email ? $mem_admin_email['val'] : 'Admin@yourdomain.com';
			$mem_new_use_priv = @$mem_new_use_priv ? $mem_new_use_priv['val'] : '0';

			$use_ign_input = '';
			// is ign_password_protect loaded on the system?
			if (load_plugin('ign_password_protect') || $mem_use_ign_db) {
				$mem_use_ign_db = $mem_use_ign_db ? $mem_use_ign_db['val'] : '0';
				$use_ign_input = yesnoradio('use_ign_db',$mem_use_ign_db);
			}
			
			$xtra_columns = mem_get_extra_user_columns();
			$has_address = in_array('address',$xtra_columns);
			$has_phone = in_array('phone',$xtra_columns);
			
			$add_address_input = yesnoradio('add_address','0');
			$add_phone_input = yesnoradio('add_phone','0');
			
			echo form(
				eInput('self-reg').sInput('install').
				startTable('edit') .
					tr( fLabelCell('admin_name')	. tda(fInput('text','admin_name',$mem_admin_name,'edit')) ) .
					tr( fLabelCell('admin_email')	. tda(fInput('text','admin_email',$mem_admin_email,'edit')) ) .
					tr( fLabelCell('new_user_priv')	. tda(priv_levels('new_user_priv',$mem_new_use_priv)) ) .
					tr( fLabelCell('use_ign_db')	. tda($use_ign_input) ) .
					($has_address ? '' : tr( fLabelCell('add_address_field') . tda($add_address_input) ) ) .
					($has_phone ? '' : tr( fLabelCell('add_phone_field') . tda($add_phone_input) ) ) .
					tr( td() . td( fInput("submit", 'submit',mem_self_gTxt('install'),"Publish"), 2 ) ) .
				endTable()
				);
		} else {
			echo '<div><a href="?event=self-reg&step=preinstall">'.mem_self_gTxt('install').'</a></div>';
		}
	}
	

	function mem_self_register_install() {
		global $mem_self;

		extract(doSlash(gpsa(array(
			'admin_email',
			'admin_name',
			'new_user_priv',
			'use_ign_db',
			'add_address',
			'add_phone'
		))));

		if (!isset($new_user_priv) || empty($new_user_priv)) $new_user_priv = '0';

		$log = array();

		if (!($rs=safe_field('val,html','txp_prefs',"name='mem_self_use_ign_db'"))) {
			if ( mem_set_pref('mem_self_use_ign_db',$use_ign_db,'self_reg',1,0,'yesnoradio')) {
				$log[] = "Added pref 'mem_self_use_ign_db'";
			} else {
				$log[] = "Failed to add pref 'mem_self_use_ign_db'. " . mysql_error();
			}
		} else {
			if ($rs['html'] != 'yesnoradio') {
				safe_update('txp_prefs',"html='yesnoradio'","name='mem_self_use_ign_db'");
			}
			$log[] = "Pref 'mem_self_use_ign_db' is already installed. Current value is '{$rs}'.";
		}
		
		$user_table = mem_get_user_table_name();
		
		$xtra_columns = mem_get_extra_user_columns();
		if ($add_address) {
			if (!in_array('address',$xtra_columns)) {
				if (safe_alter($user_table,"ADD `address` VARCHAR( 128 )")) {
					$log[] = "Added column 'address' to user table '{$user_table}'";
				} else {
					$log[] = "Failed to add column 'address' to user table '{$user_table}'. " . mysql_error();
				}
			} else {
				$log[] = "Table {$user_table} already has column 'address'";
			}
		}
		if ($add_phone) {
			if (!in_array('phone',$xtra_columns)) {
				if (safe_alter($user_table,"ADD `phone` VARCHAR( 32 )")) {
					$log[] = "Added column 'phone' to user table '{$user_table}'";
				} else {
					$log[] = "Failed to add column 'phone' to user table '{$user_table}'. " . mysql_error();
				}
			} else {
				$log[] = "Table {$user_table} already has column 'phone'";
			}
		}

		if (!($rs=safe_field('val','txp_prefs',"name='mem_self_admin_email'"))) {
			if ( mem_set_pref('mem_self_admin_email',$admin_email,'self_reg',1)) {
				$log[] = "Added pref 'mem_self_admin_email'";
			} else {
				$log[] = "Failed to add pref 'mem_self_admin_email'. " . mysql_error();
			}
		} else {
			$log[] = "Pref 'mem_self_admin_email' is already installed. Current value is '{$rs}'.";
		}
		if (!($rs=safe_field('val','txp_prefs',"name='mem_self_admin_name'"))) {
			if ( mem_set_pref('mem_self_admin_name',$admin_name,'self_reg',1)) {
				$log[] = "Added pref 'mem_self_admin_name'";
			} else {
				$log[] = "Failed to add pref 'mem_self_admin_name'. " . mysql_error();
			}
		} else {
			$log[] = "Pref 'mem_self_admin_name' is already installed. Current value is '{$rs}'.";
		}
		if (($rs=safe_field('val,html','txp_prefs',"name='mem_self_new_user_priv'")) === false) {
			if ( mem_set_pref('mem_self_new_user_priv',$new_user_priv,'self_reg',1,0,'priv_levels')) {
				$log[] = "Added pref 'mem_self_new_user_priv' with value of '{$new_user_priv}'";
				$mem_self['new_user_priv'] = $new_user_priv;
			} else {
				$log[] = "Failed to add pref 'mem_self_newuser_priv'. " . mysql_error();
			}
		} else {
			if ($rs['html'] != 'priv_levels')
				safe_update('txp_prefs',"html='priv_levels'","name='mem_self_new_user_priv'");
			
			$log[] = "Pref 'mem_self_new_user_priv' is already installed. Current value is '{$rs}'.";
		}

		// create default registration form
		$form_html = <<<EOF
<table>
<tr>
	<td>Full Name</td>
	<td><txp:mem_name_input /></td>
	<td><txp:mem_name_warn /></td>
</tr>
<tr>
	<td>Username</td>
	<td><txp:mem_user_input /></td>
	<td><txp:mem_user_warn /></td>
</tr>
<tr>
	<td>Email</td>
	<td><txp:mem_email_input /></td>
	<td><txp:mem_email_warn /></td>
</tr>
<tr>
	<td colspan="3"><txp:mem_submit /></td>
</tr>
</table>
EOF;

		$form = fetch('Form','txp_form','name','self_register_form');
		if (!$form) {
			if (safe_insert('txp_form',"name='self_register_form',type='misc',Form='{$form_html}'")) {
				$log[] = "Added form 'self_register_form'";
			} else {
				$log[] = "Failed to add form 'self_register_form'. " . mysql_error().br.
					"You need to manually create a form template. Here is an example.".br.
					'<textpattern style="width:300px;height:150px;">'.htmlspecialchars($form_html).'</textarea>';
			}
		} else {
			$log[] = "Found form 'self_register_form'. Skipping installation of default form.";
		}

		// create default successful registration form to show the user
		$form_html = <<<EOF
<h3>Account Created</h3>
<p>An email containing your password has been sent to <txp:mem_profile var="email" />.</p>
EOF;

		$form = fetch('Form','txp_form','name','self_register_success');
		if (!$form) {
			if (safe_insert('txp_form',"name='self_register_success',type='misc',Form='{$form_html}'")) {
				$log[] = "Added form 'self_register_success'";
			} else {
				$log[] = "Failed to add form 'self_register_success'. " . mysql_error().br.
					"You need to manually create a form template. Here is an example.".br.
					'<textpattern style="width:300px;height:150px;">'.htmlspecialchars($form_html).'</textarea>';
			}
		} else {
			$log[] = "Found form 'self_register_success'. Skipping installation of default form.";
		}
		
		// create default successful registration email form
		$form_html = <<<EOF
Dear <txp:mem_name />, 

Thank you for registering at <txp:mem_siteurl />. 

Your login name: <txp:mem_username />
Your password: <txp:mem_password />

If you have any questions please reply to this email address.

Sincerely,
<txp:mem_admin_name />
<txp:mem_admin_email />
EOF;

		$form = fetch('Form','txp_form','name','self_register_email');
		if (!$form) {
			if (safe_insert('txp_form',"name='self_register_email',type='misc',Form='{$form_html}'")) {
				$log[] = "Added form 'self_register_email'";
			} else {
				$log[] = "Failed to add form 'self_register_email'. " . mysql_error().br.
					"You need to manually create a form template. Here is an example.".br.
					'<textpattern style="width:300px;height:150px;">'.htmlspecialchars($form_html).'</textarea>';
			}
		} else {
			$log[] = "Found form 'self_register_form'. Skipping installation of default form.";
		}
		
		$tag_help = '<txp:mem_self_register_form form="self_register_form" />';
		$log[] = 'Example tag to use in your page template.'.br.
			'<textarea style="width:400px;height:40px;">'.htmlspecialchars($tag_help).'</textarea>';
		
		return doWrap($log,'ul','li');
	}
}

function mem_get_user_table_name() {
	$use_ign_db = mem_get_pref('mem_self_use_ign_db');
	
	$table_name = 'txp_users';
	
	if ($use_ign_db) {
		$ign_use_custom = mem_get_pref('ign_use_custom');
		if ($ign_use_custom && $ign_use_custom['val']=='1') {
			$ign_user_db = mem_get_pref('ign_user_db');
			if ($ign_user_db && !empty($ign_user_db['val']))
				$table_name = $ign_user_db['val'];
		}
	}
	return $table_name;
}

// -------------------------------------------------------------
function mem_self_register_form($atts,$thing='')
{
	global $txpac;

	$namewarn = $userwarn = $emailwarn = '';
	
	extract(doSlash(psa(array('event','step','name','email','username','address','phone','mem_self_register'))));

	extract(lAtts(array(
		'class'		=> __FUNCTION__,
		'form'		=> 'self_register_form',
		'success_form'	=> 'self_register_success',
		'email_form'	=> 'self_register_email',
		'wraptag'	=> '',
		'isize'		=> '25',
		'label'		=> mem_self_gTxt('submit'),
		'namewarn'	=> mem_self_gTxt('name_required'),
		'userwarn'	=> mem_self_gTxt('user_required'),
		'emailwarn'	=> mem_self_gTxt('email_required'),
	),$atts));

	$name = trim($name);
	$username = trim($username);
	$email = trim($email);
	$phone = trim($phone);
	$address = trim($address);

	$saved = false;

	if ($event=='self-reg' && $step=='register') {
		if (!is_valid_form()) {
			$namewarn = empty($name) ? $namewarn : '';
			$userwarn = empty($username) ? $userwarn : '';
			// if all fields are non empty, then the email address failed for some reason.
			$emailwarn = empty($email) ? $emailwarn : (empty($namewarn) and empty($userwarn) ? $emailwarn : '');
		} else {
			
			$rs = safe_field('name',mem_get_user_table_name(),"name='{$username}'");

			if (empty($rs)) {
				mem_self_register_save();
				$saved = true;
			} else {
				// don't change $userwarn
				$namewarn = $emailwarn = '';
			}
		}
	} else {
		// don't show the warnings
		$namewarn = '';
		$userwarn = '';
		$emailwarn = '';
	}

	if (@$saved) {
		$Form = fetch_form($success_form);
		$out = parse($Form);
	} else {
	
		$Form = fetch_form($form);
	
		$vals = array(
			'name_warn'		=> $namewarn,
			'user_warn'		=> $userwarn,
			'email_warn'	=> $emailwarn,
			'name_input'	=> fInput('text','name',  $name, 'register_name_input','','',$isize,"0", 'mem_name_input'),
			'user_input'	=> fInput('text','username', $username, 'register_user_input', '', '', $isize, "0", 'mem_user_input'),
			'email_input'	=> fInput('text','email', $email,'register_email_input','','',$isize,"0", 'mem_email_input'),
			'submit'		=> fInput('submit','submit',mem_self_gTxt($label),'button'),
			'phone_input'	=> fInput('text','phone', $phone, 'register_phone_input', '', '', $isize, "0", 'mem_phone_input'),
			'address_input'	=> fInput('text','address', $address, 'register_address_input', '', '', $isize, "0", 'mem_address_input'),
		);
	
		foreach ($vals as $a=>$b) {
			$Form = str_replace('<txp:mem_'.$a.' />',$b,$Form);
		}
	
		$action_url = @$_SERVER['REQUEST_URI'];
		$qs = strpos($action_url,'?');
		if ($qs) $action_url = substr($action_url, 0, $qs);
	
		$out =	n.n."<form enctype='multipart/form-data' action='{$action_url}' method='post'>" .
				eInput('self-reg') . sInput('register') . hInput('email_form',$email_form) . hInput('mem_self_register','register') .
				parse($Form) .
				"</form>".n;
	}

	return doTag($out,$wraptag,$class);

}

function mem_get_extra_user_columns_insert_string() {
	$xtra_columns = mem_get_extra_user_columns();

	$xtra = '';
	
	foreach ($xtra_columns as $xcol) {
		$name = $xcol['Field'];
		$type = strtolower($xcol['Type']);
		$val = gps($name);
		
		if ( strstr($type,'int') 
				|| $type=='float' 
				|| $type=='decimal' 
				|| $type=='double' 
				|| $type=='bool' ) {
			// don't quote value
			$xtra .= ", {$name}=" . doSlash($val);
		} else {
			// quote value
			$xtra .= ", {$name}='" . doSlash($val) ."'";
		}
	}
	
	return $xtra;
}

function mem_get_extra_user_columns()
{
	static $default_columns = array('user_id','name','pass','RealName','email','privs','last_access','nonce');
	static $xtra_columns = false;

	if (is_array($xtra_columns)) 
		return $xtra_columns;

	$table_name = mem_get_user_table_name();
	$txpdesc = getRows('describe '.PFX. $table_name);

	$xtra_cols = array();
	
	$dcols = $default_columns;

	foreach($txpdesc as $r) {
		if ( !in_array($r['Field'], $default_columns) )
			$xtra_cols[] = $r;
	}

	return $xtra_cols;
}

// -------------------------------------------------------------
function mem_self_register_save()
{
	global $mem_self,$sitename,$mem_profile;

	extract(doSlash(psa(array('name','email','username','email_form','address','phone','mem_self_register','login_url'))));
	extract($mem_self);
	
	if ($mem_self_register != 'register') return '';
	
	$pw = generate_password(6);
	
	if (!$mem_profile) $mem_profile = array();

	$mem_profile['nonce'] = $nonce = md5( uniqid( rand(), true ) );

	$mem_profile['RealName'] = $name = trim($name);
	$mem_profile['email'] = $email = trim($email);
	$mem_profile['name'] = $username = trim($username);
	$mem_profile['privs'] = $new_user_priv;
	
	$xtra_columns = mem_get_extra_user_columns();
	
	foreach($xtra_columns as $c) {
		$c_name = trim( $c['Field'] );
		
		$mem_profile[$c_name] = gps($c_name);
	}

	$rs = false;

	$xtra = mem_get_extra_user_columns_insert_string();

	$rs = safe_insert(
		mem_get_user_table_name(),
		"privs    = '$new_user_priv',
		 name     = '$username',
		 email    = '$email',
		 RealName = '$name',
		 pass     =  password(lower('$pw')),
		 nonce    = '$nonce'" . $xtra
	);

	if ($rs) {
		$mem_profile['user_id'] = $rs;
		$mem_profile['last_access'] = 0;

		if ($email_form) {
			$message = fetch('Form','txp_form','name',$email_form);

			if (is_array($admin_name))
				$admin_name = $admin_name['val'];
			if (is_array($admin_email))
				$admin_email = $admin_email['val'];

			if (empty($login_url))
				$login_url = rtrim(hu,'/').'/textpattern/index.php';
			
			$vals = array(
				'admin_name'	=>	$admin_name,
				'admin_email'	=>	$admin_email,
				'name'		=>	$name,
				'username'	=>	$username,
				'email'		=>	$email,
				'password'	=>	$pw,
				'sitename'	=>	$sitename,
				'loginurl'	=>	$login_url,
				'siteurl'	=>	hu,
				'address'	=>	$address,
				'phone'		=>	$phone
			);
			
			foreach ($vals as $a=>$b) {
				@$message = str_replace('<txp:mem_'.$a.' />',$b,$message);
			}
			
			$message = parse($message);
			
			$emailbody = "From: {$admin_name} <{$admin_email}>\r\n"
				."Bcc: {$admin_email}\r\n"
				."Reply-To: {$admin_email}\r\n"
				."Content-Transfer-Encoding: 8bit\r\n"
				."Content-Type: text/plain; charset=\"UTF-8\"\r\n";

			$sent = mail($email, "[$sitename] ".mem_self_gTxt('your_login_info'), $message, $emailbody);

			$mem_self['email_status'] = $sent;
			$mem_self['email_message'] = $message;

			if ($sent) {
				$cookietime = time() + (365*24*3600);
				setcookie("txp_self_registered", "1",  $cookietime, "/");
				
				$mem_self['status_message'] = mem_self_gTxt('password_sent_to').sp.$email;
			} else {
				// failed to send email
			}
		}
	} else {
		$mem_self['status_message'] = mem_self_gTxt('error_adding_new_author');
	}
}

// -------------------------------------------------------------
function self_register_email_message($atts)
{
	global $mem_self;
	return $mem_self['email_message'];
}

// -------------------------------------------------------------
function self_register_status_message($atts)
{
	global $mem_self;
	return $mem_self['status_message'];
}


// -------------------------------------------------------------
function if_message_sent($atts,$thing)
{
	global $mem_self;
	$condition = ($mem_self['email_status']);
	return parse(EvalElse($thing, $condition));
}

// -------------------------------------------------------------
function if_self_registered($atts,$thing)
{
	global $mem_self,$txp_user;
	$condition = ($mem_self['status'] or $mem_self['form_valid'] or !empty($_COOKIE['txp_self_registered']) or (isset($txp_user) and !empty($txp_user)) );
	return parse(EvalElse($thing, $condition));
}

// added to txplib_misc
if (!function_exists('is_valid_email')) {
function is_valid_email($email)
{
	$addr = explode('@',$email);
	
	$host = $addr[1];
	
	if ($host != gethostbyname($host) and eregi("^[0-9a-z]([-_.~]?[0-9a-z])*$",$addr[0]))
		return true;

	return false;
}
}

function is_valid_form()
{
	extract(doSlash(psa(array('name','email','username','mem_self_register'))));

	$name = trim($name);
	$email = trim($email);
	$username = trim($username);

	if ($mem_self_register=='register') {
		if (!(empty($name) || empty($username) || empty($email)) && is_valid_email($email)) {
			return true;
		}
	}
	
	return false;
}



////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////
// User Side Profile and Password Change Forms
// -------------------------------------------------------------
function mem_change_password_form($atts,$thing='')
{
	return mem_change_pass_form($atts,$thing);
}
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
			mem_get_user_table_name(), 
			"pass = password(lower('$new_pass'))",
			"name='$txp_user'"
		);

		if ($rs) {

			// successful
			$mem_profile = safe_row('*',mem_get_user_table_name(),"name = '{$txp_user}'");

			if ($mem_profile) {
				$mem_profile['new_pass'] = $new_pass;
				
				if (!empty($form_mail))
					$message = fetch_form($form_mail);
				if (empty($message)) {
					$message = mem_self_gTxt('greeting').' <txp:mem_realname />,'."\r\n".
						mem_self_gTxt('your_password_is').": <txp:mem_password />\r\n"."\r\n".
						mem_self_gTxt('log_in_at').' '.hu.'textpattern/index.php';
				}

				$vals = array(
					'realname'	=>	$mem_profile['RealName'],
					'password'	=>	$mem_profile['new_pass'],
				);
				
				foreach ($vals as $a=>$b) {
					$message = str_replace('<txp:mem_'.$a.' />',$b,$message);
				}

				$message = parse($message);

				// email password
				if (txpMail($mem_profile['email'], "[$sitename] ".mem_self_gTxt('your_new_password'), $message))
					$out = mem_self_gTxt('password_changed');
				else
					$out = mem_self_gTxt('password_change_failed');
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
				parse($Form).
				eInput('mem_user_workspace').sInput('mem_change_pass').
				"</form>";

//				graf(mem_self_gTxt('new_password').' '.fInput('password','new_pass','','edit','','','20','1').
					
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
		
		$xtra = mem_get_extra_user_columns_insert_string();

		$rs = safe_update(mem_get_user_table_name(),
			"email = '{$email}', RealName = '{$RealName}'" . $xtra,
			"name = '{$txp_user}'");
		
		if ($rs) {
			$message = mem_self_gTxt('saved_user_profile');
		} else {
			$message = mysql_error();
		}
	}

	$mem_profile = safe_row('*',mem_get_user_table_name(),"name = '{$txp_user}'");
	
	if ($mem_profile) {
		$Form = $thing;
		if (!empty($form))
			$Form = fetch_form($form);
		
		$Form = eregi_replace('<txp:mem_message />',$message,$Form);

		$out = 	"<form action='{$_SERVER['REQUEST_URI']}' method='post'>".
				parse($Form).
				eInput('mem_user_workspace').sInput('save_user_profile').
				"</form>";
	}

	return doTag($out,$wraptag,$class);
}

function mem_profile($atts)
{
	global $mem_profile,$txp_user,$ign_user;
	
	if (isset($ign_user)) $txp_user = $ign_user;
	
	extract($atts);
	if (!is_array($mem_profile) && $txp_user)
		$mem_profile = safe_row('*',mem_get_user_table_name(),"name = '{$txp_user}'");
	
	if ($mem_profile)
		return array_key_exists($var,$mem_profile) ? $mem_profile[$var] : '';
	return '';
}
function mem_submit($atts)
{
	extract($atts);
	$class = isset($class) ? $class : 'smallerbox';
	$value = isset($value) ? $value : mem_self_gTxt('save');
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

function mem_self_user_count($atts)
{
	global $mem_self;
	
	extract(lAtts(array(
		'user_levels'	=> '0,1,2,3,4,5,6',
		'wraptag'	=> '',
		'class'		=> ''
	),$atts));

	if (!empty($user_levels) || $user_levels=='0')
		$user_levels = doSlash(split(',',$user_levels));
	else
		$user_levels = array($mem_self['new_user_priv']);

	$levels = join(',',$user_levels);
	$count = safe_field('COUNT(*)', mem_get_user_table_name(), "privs IN ({$levels})");
	
	return doTag($count,$wraptag,$class);
}

# --- END PLUGIN CODE ---

?>
