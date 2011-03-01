<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current file name.
// Uncomment and edit this line to override:
$plugin['name'] = 'mem_moderation';

$plugin['version'] = '0.7.5';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'This plugin adds a generic moderation queue to Textpattern. A plugin can extend the moderation queue to support any type of content.';
$plugin['type'] = 1; // 0 for regular plugin; 1 if it includes admin-side code


@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h1(title). mem_moderation plugin

h2(section summary). Summary

p. This plugin adds a generic moderation queue to Textpattern. A plugin can extend the moderation queue to support any type of content.

h2(section contact). Author Contact

"Michael Manfre":mailto:mmanfre@gmail.com?subject=Textpattern%20mem_moderation%20plugin
"http://manfre.net":http://manfre.net

h2(section license). License

p. This plugin is licensed under the "GPLv2":http://www.fsf.org/licensing/licenses/info/GPLv2.html.

h2(section installation). Installation

p. "Start Install Wizard":./index.php?event=moderate&step=preinstall

h2(section tags). Tags

* "mem_if_moderation":#mem_if_moderation
* "mem_moderation_info":#mem_moderation_info
* "moderate_submission_list":#moderate_submission_list
* "mem_if_type":#mem_if_type
* "mem_if_data":#mem_if_data
* "mem_moderation_if_gps":#mem_moderation_if_gps
* "mod_id":#mod_id
* "mod_item_id":#mod_item_id
* "mod_submitted":#mod_submitted
* "mod_user":#mod_user
* "mod_desc":#mod_desc
* "mod_type":#mod_type
* "mod_email":#mod_email
* "mod_data":#mod_data
* "mod_note_input":#mod_note_input

h3(tag#mem_if_moderation). mem_if_moderation

p(tag-summary). Conditionally parse a block of text based upon various checks.

*(atts) %(atts-name)check% %(atts-type)string% Type of check to perform. Either 'type', 'data' or 'gps'
* %(atts-name)name% %(atts-type)string% Name of the type, data field or GET/POST variable.
* %(atts-name)value% %(atts-type)string% Value to compare against for 'data' and 'gps' checks.
* %(atts-name delimiter)type% %(atts-type)string% Name of the form to identify itself to bound plugin.

h3(tag#mem_moderation_info). mem_moderation_info

p(tag-summary). Output information from a moderated item. Supported field names are 'id', 'item_id', 'submitted', 'user', 'desc', 'type', 'email' and 'data'.

*(atts) %(atts-name)field% %(atts-type)string% Field to output.
* %(atts-name)datafield% %(atts-type)string% The field from the encoded data. Field must be 'data'.
* %(atts-name)format% %(atts-type)string% A time format to used to display the submitted time.

h3(tag#moderate_submission_list). moderate_submission_list

p(tag-summary). This will output an HTML text input field and validates the submitted value as an email address.

*(atts) %(atts-name)form% %(atts-type)string% The form containing the formatting tags. If specified, tag enclosed text is ignored.
* %(atts-name)type% %(atts-type)string% When set, this will restrict the list to specific types of moderated content.
* %(atts-name)wraptag% %(atts-type)string% HTML tag to wrap around the output.
* %(atts-name)break% %(atts-type)string% Text/HTML to separate each item in the list.
* %(atts-name)breakclass% %(atts-type)string% CSS class name.
* %(atts-name)class% %(atts-type)string% CSS class name.
* %(atts-name)user% %(atts-type)string% Restrict the list to specified users. Defaults to logged in user.
* %(atts-name)label% %(atts-type)string% Text/HTML that preceeds the list.
* %(atts-name)labelwraptag% %(atts-type)string% HTML tag to wrap around the label.
* %(atts-name)labelclass% %(atts-type)string% CSS class name for labelwraptag.

h3(tag#mem_if_type). mem_if_type _(deprecated)_

p(tag-summary). Conditionally parse a block of text based upon the current moderation type.

*(atts) %(atts-name)name% %(atts-type)string% Name of the moderation to compare against.


h3(tag#mem_if_data). mem_if_data _(deprecated)_

p(tag-summary). Conditionally parse based upon specific moderation data.

*(atts) %(atts-name)name% %(atts-type)string% Name of the data field.
* %(atts-name)value% %(atts-type)string% Value to compare against. If blank, checks for existance.

h3(tag#mem_moderation_if_gps). mem_moderation_if_gps _(deprecated)_

p(tag-summary). Conditionally parse based upon a GET/POST variable.

*(atts) %(atts-name)name% %(atts-type)string% Name of the GET/POST variable.
* %(atts-name)value% %(atts-type)string% Value to compare against. If blank, checks for existance.

h3(tag#mod_id). mod_id _(deprecated)_

p(tag-summary). Output moderation id field.

h3(tag#mod_item_id). mod_item_id _(deprecated)_

p(tag-summary). Output moderation item_id field.

h3(tag#mod_submitted). mod_submitted _(deprecated)_

p(tag-summary). Output moderation submitted field.

*(atts) %(atts-name)format% %(atts-type)string% Time format string.

h3(tag#mod_user). mod_user _(deprecated)_

p(tag-summary). Output moderation user field.

h3(tag#mod_desc). mod_desc _(deprecated)_

p(tag-summary). Output moderation desc field.

h3(tag#mod_type). mod_type _(deprecated)_

p(tag-summary). Output moderation type field.

h3(tag#mod_email). mod_email _(deprecated)_

p(tag-summary). Output moderation email field.

h3(tag#mod_data). mod_data _(deprecated)_

p(tag-summary). Output moderation data field.

*(atts) %(atts-name)name% %(atts-type)string% Name of the moderation data field.


h3(tag#mod_note_input). mod_note_input _(deprecated)_

p(tag-summary). Outputs an HTML textarea to process the 'note' field.

h3(tag#mem_moderation_edit_link). mem_moderation_edit_link

p(tag-summary). Output a user side link to edit a specific moderation item.

*(atts) %(atts-name)label% %(atts-type)string% Friendly name for the input field. If set, this will output an HTML ==<label>== tag linked to the input field.
* %(atts-name)name% %(atts-type)string% Input field name.
* %(atts-name)break% %(atts-type)string% Separator between label tag and input tag.
* %(atts-name)delimiter% %(atts-type)string% List separator. Default ","
* %(atts-name)items% %(atts-type)string% Delimited list containing a select list display values.
* %(atts-name)values% %(atts-type)string% Delimited list containing a select list item values.
* %(atts-name)required% %(atts-type)int% Specifies if input is required.
* %(atts-name)selected% %(atts-type)string% The value of the selected item.
* %(atts-name)first% %(atts-type)string% Display value of the first item in the list. E.g. "Select a Section" or "" for a blank option.
* %(atts-name)class% %(atts-type)string% CSS class name.
* %(atts-name)exclude% %(atts-type)string% List of item values that will not be included.
* %(atts-name)sort% %(atts-type)string%  How will the list values be sorted.


h3(tag#mod_edit_link). mod_edit_link _(deprecated)_

p(tag-summary). See mem_moderation_edit_link.


h2(section). Exposed Functions

h3(tag). register_moderation_type _(deprecated)_

p(tag-summary). See mem_moderation_register.

h3(tag). mem_moderation_register

p(tag-summary). Registers a moderation type to be used in the moderation queue.

*(atts) %(atts-name)type% %(atts-type)string% Moderation type name.
* %(atts-name)vars% %(atts-type)array% Array of GET/POST variable used by this moderation type.
* %(atts-name)presenter% %(atts-type)string% Name of the function that will construct a form to edit the plugin specific content. This is needed to allow moderators to edit submitted content.
* %(atts-name)approver% %(atts-type)string% Name of callback function that will be called when an item is marked as approved.
* %(atts-name)rejecter% %(atts-type)string% Name of callback function that will be called when an item is marked as rejected.

h3(tag). submit_moderated_content

p(tag-summary). Adds an item to the moderation queue.

*(atts) %(atts-name)Return Value% %(atts-type)bool% Returns true or false, indicating whether the item was successfully added to the moderation queue.
* %(atts-name)type% %(atts-type)string% Moderation type name.
* %(atts-name)email% %(atts-type)string% Email of submitting user.
* %(atts-name)desc% %(atts-type)string% Description of moderated item (note to moderater).
* %(atts-name)data% %(atts-type)mixed% Moderation specific data to be encoded and stored with the item.
* %(atts-name)item_id% %(atts-type)int% (optional) The ID of a content item not in the moderation queue. Used for when moderating edits of published content.

h3(tag). update_moderated_content

p(tag-summary). Updates an item in the moderation queue.

*(atts) %(atts-name)Return Value% %(atts-type)bool% Returns true or false, indicating whether the item was successfully update in the moderation queue.
* %(atts-name)id% %(atts-type)int% The ID of the moderated item.
* %(atts-name)desc% %(atts-type)string% Description of moderated item (note to moderater).
* %(atts-name)data% %(atts-type)mixed% Moderation specific data to be encoded and stored with the item.
* %(atts-name)type% %(atts-type)string% (optional) Moderation type name. 
* %(atts-name)item_id% %(atts-type)int% (optional) The ID of a content item not in the moderation queue. Used for when moderating edits of published content.

h3(tag). remove_moderated_content

p(tag-summary). Remove an item from the moderation queue.

*(atts) %(atts-name)Return Value% %(atts-type)bool% Returns boolean result of remove operation.
* %(atts-name)id% %(atts-type%)int% ID of moderated item.

h3(tag). mem_moderation_encode

p(tag-summary). Serializes moderation data for storage.

*(atts) %(atts-name)Return Value% %(atts-type)string% Returns a serialized string.
* %(atts-name)content% %(atts-type%)mixed% The data to serialize.

h3(tag). mem_moderation_decode

p(tag-summary). Deserializes moderation data from storage.

*(atts) %(atts-name)Return Value% %(atts-type)mixed% Returns the data.
* %(atts-name)content% %(atts-type%)string% The serialized content.

h2(section). Global Variables

*(atts) %(atts-name)$mem_mod_info% %(atts-type)array% An array containing the moderation info. Can be used by tags inside moderate_submission_list.

h2(section). Plugin Events

p. This library allows other plugins to hook in to events with the @mem_moderation_register@ function. All callback functions have the structure of func($type, $data), where $type is the type name that is being processed and $data is the type specific data appended to the plugin.

h3(event). presenter

p(event-summary). A submitted item is being viewed by a moderator and the plugin's presenter handler should return HTML formatted form fields to enable the moderator to edit the data (no form tag or buttons).

h3(event). approver

p(event-summary).  submitted item has been approved and the plugin's approver handler should submit $data to the appropriate place where live content resides.

h3(event). rejecter

p(event-summary). A submitted item has been rejected and is being removed from the queue.



# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

////////////////////////////////////////////////////////////
// Plugin mem_moderation
// Author: Michael Manfre (http://manfre.net/)
////////////////////////////////////////////////////////////

// the number of days that a newly submitted item will wait 
// before appearing in the moderation queue. 0 will disable
//define('QUEUE_SUBMISSION_DELAY', "0");

// Specify whether users with Publisher privs will have their
// submitted content appear immediatly in the list without 
// waiting for the QUEUE_SUBMISSION_DELAY
//define('PUBLISHERS_BYPASS_QUEUE_DELAY', true);

///////////////////////////////////////////////////////////
// Do not modify below this line


// MLP support
define( 'MEM_MODERATION_PREFIX' , 'mem_moderation' );
global $mem_moderation_lang;
$mem_moderation_lang = array(
	'id_not_found'	=> "Moderation ID {id} not found.",
	'install_log'	=> "Install Log",
	'invalid_arg'	=> "The argument {arg} is invalid.",
	'missing_arg'	=> "The attribute {arg} is required and was not provided.",
	'remove_failed'	=> "Failed to remove ID {id} from queue.",
	'new_submission_email'	=> "The user {user} has submitted a request of type {type} to the moderation queue.",
	'new_submission_email_subject'	=> "Moderation Queue",
	'update_submission_email'	=> "The user {user} has updated their request of type {type} to the moderation queue.",
	'update_submission_email_subject'	=> "Moderation Queue",

	'table_access_failed'	=>	"Error accessing the database table. {err}",

	// installation
	'add_item_field'	=> 'Added item_id field to moderation table.',
	'add_item_field_failed'	=> 'Failed to add field "item_id" to moderation table.',
	'table_created'	=> "Created table {name}",
	'table_create_failed'	=> "Failed to create moderation table. {mysql_error}",
	'table_exists'	=> 'Moderation table already exists',
	'form_found'	=> "Found form '{form}'. Skipping installation of default form.",
	'form_created'	=> "Created default form '{form}'",
	'form_create_fail'	=> "Failed to create form '{form}'. {mysql_error}",
	'skipping_form_install'	=> "Skipping installation of default forms",
	'set_pref'	=> 'Set preference {name}',
	'set_pref_failed'	=> 'Failed to set preference {name}',
	'set_pref_exists'	=> 'Preference {name} already exists.',

	'mem_moderati'	=>	'Moderation Plugin',
	
	// prefs
	'mem_mod_email_on_new'	=>	'Email on new content?',
	'mem_mod_email_on_update'	=>	'Email on content update?',
	'mem_mod_notify_email'	=>	'Notification Email Address',
	'mem_mod_queue_delay'	=>	'Queue Delay (days)',
	'mem_mod_multiedit_approve'	=>	'Enable Multi-edit approval?',
);

register_callback('fix_lang', 'prefs', '', 1);
function fix_lang()
{
	global $mem_moderation_lang, $textarray, $locale;
	if (strncasecmp('en', $locale, 2) != 0)
		return;
	
	foreach ($mem_moderation_lang as $k => $v)
	{
		$textarray[$k] = $v;
	}
}

register_callback( 'mem_moderation_enumerate_strings' , 'l10n.enumerate_strings' );
function mem_moderation_enumerate_strings($event , $step='' , $pre=0)
{
	global $mem_moderation_lang;
	$r = array	(
				'owner'		=> 'mem_moderation',			#	Change to your plugin's name
				'prefix'	=> MEM_MODERATION_PREFIX,				#	Its unique string prefix
				'lang'		=> 'en-gb',						#	The language of the initial strings.
				'event'		=> 'public',					#	public/admin/common = which interface the strings will be loaded into
				'strings'	=> $mem_moderation_lang,		#	The strings themselves.
				);
	return $r;
}
function mem_moderation_gTxt($what,$args = array())
{
	global $mem_moderation_lang, $textarray;

	$key = strtolower( MEM_MODERATION_PREFIX . '-' . $what );
	
	if (isset($textarray[$key]))
	{
		$str = $textarray[$key];
	}
	else
	{
		$key = strtolower($what);
		
		if (isset($mem_moderation_lang[$key]))
			$str = $mem_moderation_lang[$key];
		elseif (isset($textarray[$key]))
			$str = $textarray[$key];
		else
			$str = $what;
	}

	if( !empty($args) )
		$str = strtr( $str , $args );

	return $str;
}



global $mod_event, $mem_moderation_lang, $prefs;
$mod_event = 'moderate';

require_plugin('mem_admin_parse');
// make sure ign is loaded first, if it exists
@load_plugin('ign_password_protect');
// might need access to ign user table
@load_plugin('mem_self_register');


// -------------------------------------------------------------
// Tags
// -------------------------------------------------------------

/** Show a submission list. User profile page. */
function moderate_submission_list($atts,$thing='')
{
	global $step,$link_list_pageby,$mod_event,$txp_user,$ign_user;
	
	// try looking for user from ign_password_protect plugin
	if (isset($ign_user) and empty($txp_user)) $txp_user = $ign_user;

	extract(lAtts(array(
			'limit'		=>	5,
			'form'		=>	'',
			'type'		=>	'',
			'wraptag'	=>	'div',
			'break'		=>	'br',
			'breakclass'	=>	'mod_listitem',
			'class'		=>	'mod_list',
			'user'		=>	$txp_user,
			'label'		=>	'',
			'labelwraptag'	=>	'',
			'labelclass'	=>	'moderate_list_label'
	),$atts));

	extract(get_prefs());
	$Form = !empty($form) ? fetch_form($form) : $thing;

	$user = doSlash($user);
	$type = doSlash($type);
	
	$where = "`user` LIKE '{$user}'";
	if (!empty($type))
		$where .= " AND `type` LIKE '{$type}'";
	$where .= " ORDER BY `submitted`";

	$rs = safe_rows_start('*','txp_moderation',$where);

	
	if ($rs) 
	{
		$out = array();

		while ($r = nextRow($rs)) 
		{
			$GLOBALS['mem_mod_info'] = $r;
			$out[] = admin_parse($Form);

			// clean up global			
			unset($GLOBALS['mem_mod_info']);
		}

		$items = '';
	
		if (count($out) > 0 && !empty($label))
		{
			$items = doTag($label,$labelwraptag,$labelclass);
		}

		return $items . doWrap($out,$wraptag,$break,$class,$breakclass);
	}

	return '';
}

function mod_note_input($atts) {
	trigger_error(gTxt('deprecated_function_with', array('{name}' => __FUNCTION__, '{with}' => 'mem_form_textarea')), E_USER_NOTICE);
	global $mem_mod_info;
	extract(lAtts(array(
		'style'	=>	'',
		'class'	=>	__FUNCTION__
	),$atts));
	return '<textarea name="note"'.(!empty($style)?' style="'.$style.'"':'').
			(!empty($class)?' class="'.$class.'"':'').'>'.
			htmlspecialchars(@$mem_mod_info['note']).'</textarea>';
}

function mem_moderation_edit_link($atts, $thing)
{
	global $mod_event,$mem_mod_info;
	extract(lAtts(array(
		'baseurl'	=> $_SERVER['REQUEST_URI']
	),$atts));

	$elink = '<a href="'.$baseurl.'?event='.$mod_event.'&step='.$mem_mod_info['type'].'_edit&modid='.$mem_mod_info['id'].'">'.admin_parse($thing).'</a>';
	return $elink;
}
function mod_edit_link($atts,$thing)
{
	trigger_error(gTxt('deprecated_function_with', array('{name}' => __FUNCTION__, '{with}' => 'mem_moderation_edit_link')), E_USER_NOTICE);
	return mem_moderation_edit_link($atts, $thing);
}

/** Tag to Display moderation info. */
function mem_moderation_info($atts)
{
	global $mem_mod_info, $prefs;
	
	extract(lAtts(array(
		'datafield'	=> '',
		'field'		=> '',
		'format'	=> $prefs['archive_dateformat']
	),$atts));
	
	if (empty($field))
	{
		trigger_error(mem_moderation_gTxt('missing_arg', array('{arg}' => 'field')), E_USER_NOTICE);
	}

	$out = '';

	if (isset($mem_mod_info[$field]))
	{
		if ($field == 'submitted')
		{
			$time = strtotime($mem_mod_info['submitted']);
			$out = safe_strftime($format, $time);
		}
		else if ($field == 'data')
		{
			$data = mem_moderation_decode($mem_mod_info['data']);
			
			if (is_array($data) and array_key_exists($datafield, $data))
			{
				$out = $data[$datafield];
			}
			else
			{
				trigger_error(mem_moderation_gTxt('invalid_arg', array('{arg}' => 'datafield')), E_USER_NOTICE);
			}
		}
		else
		{
			if (isset($mem_mod_info[$field]))
			{
				$out = $mem_mod_info[$field];
			}
			else
			{
				trigger_error(mem_moderation_gTxt('invalid_arg', array('{arg}' => 'field')), E_USER_NOTICE);
			}
		}
	}

	return $out;
}

function mem_if_moderation($atts, $thing)
{
	global $mem_mod_info;
	
	extract(lAtts(array(
		'check'	=> 'type',
		'name'	=> '',
		'value'	=> '',
		'delimiter'	=> ',',
	), $atts));

	if (empty($name))
	{
		trigger_error(mem_moderation_gTxt('missing_arg', array('{arg}' => 'name')), E_USER_NOTICE);
		return '';
	}

	switch (strtolower($check))
	{
		case 'type':
			// check if any of the provided types match.
			$types = array_flip(explode($delimiter, $name));
			
			$cond = array_key_exists($mem_mod_info['type'], $types);
			break;
		case 'gps':
			$g = gps($name);
			
			// true if matches value or if set when value is empty
			$cond = (empty($value) && !empty($g)) or (!empty($value) && strcasecmp($g, $value) == 0);
			break;
		case 'data':
			$data = mem_moderation_decode($mem_mod_info['data']);
			
			// exists?
			$cond = is_array($data) and array_key_exists($name, $data) and !empty($data[$name]);
			
			if ($cond && !empty($value))
			{
				$cond = strcasecmp($data[$name], $value) == 0;
			}
			break;
		default:
			trigger_error(mem_moderation_gTxt('invalid_check', array('{check}' => $check)), E_USER_NOTICE);
			return '';
	}
	
	return admin_parse(EvalElse($thing, $cond));
}

function mod_if_type($atts,$thing) {
	trigger_error(gTxt('deprecated_function_with', array('{name}' => __FUNCTION__, '{with}' => 'mem_if_moderation')), E_USER_NOTICE);
	return mem_if_moderation(array(
						'check'	=> 'type',
						'name' => @$atts['name']
					), $thing);
}
function mod_if_data($atts,$thing) {
	trigger_error(gTxt('deprecated_function_with', array('{name}' => __FUNCTION__, '{with}' => 'mem_if_moderation')), E_USER_NOTICE);
	return mem_if_moderation(array(
						'check'	=> 'data',
						'name'	=> @$atts['name'],
						'value'	=> @$atts['value']
					), $thing);
}
function mem_moderation_if_gps($atts,$thing) {
	trigger_error(gTxt('deprecated_function_with', array('{name}' => __FUNCTION__, '{with}' => 'mem_if_moderation')), E_USER_NOTICE);
	return mem_if_moderation(array(
						'check'	=> 'gps',
						'name'	=> @$atts['name'],
						'value'	=> @$atts['value']
					), $thing);
}
function mod_id($atts) {
	trigger_error(gTxt('deprecated_function_with', array('{name}' => __FUNCTION__, '{with}' => 'mem_moderation_info')), E_USER_NOTICE);
	return mem_moderation_info(array('field'=>'id'));
}
function mod_item_id($atts) {
	trigger_error(gTxt('deprecated_function_with', array('{name}' => __FUNCTION__, '{with}' => 'mem_moderation_info')), E_USER_NOTICE);
	return mem_moderation_info(array('field'=>'item_id'));
}
function mod_submitted($atts) {
	trigger_error(gTxt('deprecated_function_with', array('{name}' => __FUNCTION__, '{with}' => 'mem_moderation_info')), E_USER_NOTICE);
	$atts['field'] = 'submitted';
	return mem_moderation_info($atts);
}
function mod_user($atts) {
	trigger_error(gTxt('deprecated_function_with', array('{name}' => __FUNCTION__, '{with}' => 'mem_moderation_info')), E_USER_NOTICE);
	return mem_moderation_info(array('field'=>'user'));
}
function mod_desc($atts) {
	trigger_error(gTxt('deprecated_function_with', array('{name}' => __FUNCTION__, '{with}' => 'mem_moderation_info')), E_USER_NOTICE);
	return mem_moderation_info(array('field'=>'desc'));
}
function mod_type($atts) {
	trigger_error(gTxt('deprecated_function_with', array('{name}' => __FUNCTION__, '{with}' => 'mem_moderation_info')), E_USER_NOTICE);
	return mem_moderation_info(array('field'=>'type'));
}
function mod_email($atts) {
	trigger_error(gTxt('deprecated_function_with', array('{name}' => __FUNCTION__, '{with}' => 'mem_moderation_info')), E_USER_NOTICE);
	return mem_moderation_info(array('field'=>'email'));
}
function mod_data($atts) {
	trigger_error(gTxt('deprecated_function', array('{name}' => __FUNCTION__)), E_USER_NOTICE);
	$atts['field'] = 'data';
	$atts['datafield'] = @$atts['name'];
	return mem_moderation_info($atts);
}


/** Show the moderation queue */
function mem_moderate_list($message="")
{
	global $step, $link_list_pageby, $mod_event;

	// check privs
	if (!has_privs('moderate.list')) 
	{
		return;
	}

	extract(get_prefs());

	$page = gps('page');
	$total = getCount('txp_moderation',"1");
	$limit = empty($link_list_pageby) ? 20 : $link_list_pageby;
	$numPages = $total==0 ? 1 : ceil($total/$limit);
	$page = (!$page) ? 1 : $page;
	$offset = ($page - 1) * $limit;
	$sort = gps('sort');
	$dir = gps('dir');

	$sort = ($sort) ? doSlash($sort) : "submitted";
	$dir = ($dir) ? doSlash($dir) : 'asc';

	// navigation links
	$nav[] = ($page > 1)
			?	PrevNextLink($mod_event,$page-1,gTxt('prev'),'prev') : '';
	$nav[] = sp.small($page. '/'.$numPages).sp;
	$nav[] = ($page != $numPages && $numPages > 1)
			?	PrevNextLink($mod_event,$page+1,gTxt('next'),'next') : '';

	$delay = isset($mem_mod_queue_delay) ? assert_int($mem_mod_queue_delay) : 0;

	if($mem_mod_pub_bypass_queue) 
	{
		// check against user table
		$user_table = mem_get_user_table_name();
		if (empty($user_table)) $user_table = 'txp_users';
		
		$rs = safe_rows_start(
			"txp_moderation.*",
			"txp_moderation, {$user_table}",
			" txp_moderation.user={$user_table}.name AND (DATE_SUB( NOW(), INTERVAL ". $delay ." DAY ) > txp_moderation.submitted OR {$user_table}.privs = 1) order by txp_moderation.{$sort} $dir limit $offset,$limit"
		);
	}
	else
	{
		// get all queue items
		$rs = safe_rows_start(
			"*",
			"txp_moderation",
			" (DATE_SUB( NOW(), INTERVAL ". $delay ." DAY ) > submitted) order by {$sort} $dir limit $offset,$limit"
		);
	}
	
	if ($rs)
	{
		// reverse dir for links
		if ($dir == "desc") { $dir = "asc"; } else { $dir = "desc"; }

		echo '<form action="index.php" method="post" name="longform" onsubmit="return verify(\''.gTxt('are_you_sure').'\')">',
		startTable('list'),

		// column heading with sort links
		tr(
			column_head('id','id',$mod_event,1,$dir).
			column_head('submitted','submitted',$mod_event,1,$dir).
			column_head('type','type',$mod_event,1,$dir).
			column_head('user','user',$mod_event,1,$dir).
			column_head('description','description',$mod_event,0,$dir).
			td()
			);

		// each row
		$alt = false;
		while ($a = nextRow($rs))
		{
			extract($a);
			$elink = eLink($mod_event,'details','id',$id,$id);
			$cbox = fInput('checkbox','selected[]',$id);

			// no desc, use another field
			if (empty($desc))
			{
				$data = mem_moderation_decode($a['data']);
				
				$altfields = array('title', 'desc', 'description', 'alt');
				
				foreach ($altfields as $f)
				{
					if (isset($data[$f]) and !empty($data[$f]))
					{
						$desc = $data[$f];
						break;
					}
				}
				
				// ensure something is in desc column
				if (empty($desc))
				{
					$desc = mem_moderation_gTxt('no_desc');
				}
			}

			$desclink = eLink($mod_event,'details','id',$id,$desc);
			
			echo tr(
					td($elink,20).
					td($submitted,175).
					td($type,50).
					($user!='anonymous'?td($user,130):td($email,130)).
					td($desclink,200).
					td($cbox)
				);
		}

		$multi_edit_options = array('reject'=>gTxt('reject'));
		
		if ($mem_mod_multiedit_approve)
		{
			$multi_edit_options['approve'] = gTxt('approve');
		}

		// list action form
		echo tr(tda(select_buttons().
		event_multiedit_form($mod_event,$multi_edit_options, $page, $sort, $dir,'',''),' colspan="6" style="text-align:right;border:0px"'));

		// paging
		echo endTable(),'</form>';
		echo pageby_form($mod_event,$link_list_pageby);
		echo graf(join('',$nav),' align="center"');
	} 
	else 
	{
		echo mem_moderation_gTxt('table_access_failed', array('{err}' => mysql_error()));
	}
}

/** Update an item in the queue */
function mem_moderate_save()
{
	require_privs('moderate.edit');
	
	extract(gpsa(array('id','type','moderation_description')));
	
	$vars = gpsa(mem_get_moderation_variables($type));
	
	update_moderated_content($id,$moderation_description,$vars);
}


/** Approve an item in the moderation queue */
function mem_moderate_approve($type='',$id=false)
{
	require_privs('moderate.approve');

	if ($id===false || empty($type)) 
	{
		extract(gpsa(array('id','type')));
	
		// save it first
		mem_moderate_save();
	}
	else
	{
		// Approving from the listing
	}
	
	$rs = safe_row("*", "txp_moderation", "id = ". doSlash($id));
	
	if ($rs)
	{
		$vars = mem_get_moderation_variables($type);
	
		extract(array_merge($rs,$vars));
	
		$decoded_data = mem_moderation_decode($rs['data']);
		$decoded_data['id'] = $id;
	
		// notify plugins
		$res = mem_moderation_approver_callback($type,$decoded_data);
	
		if ($res == '')
		{
			if (remove_moderated_content($id)) 
			{
				// no return on success
			}
			else
			{
				return mem_moderation_gTxt('remove_failed', array('{id}' => $id));
			}
		}
			
		return $res;
	} 
	else 
	{
		return mem_moderation_gTxt('id_not_found');
	}
}

/** Reject a moderated item */
function mem_moderate_reject($type='', $id=false)
{
	require_privs('moderate.approve');

	if ($id===false || empty($type))
		extract(gpsa(array('id','type')));
	
	$rs = safe_row("*", "txp_moderation", "id = $id");
	
	if ($rs)
	{
		$decoded_data = mem_moderation_decode($rs['data']);
		$decoded_data['id'] = $id;
	
		// notify plugins
		$res = mem_moderation_rejecter_callback($type, $decoded_data);

		if ($res == '') 
		{
			if (remove_moderated_content($id)) 
			{
				// no return on success
			}
			else
			{
				return mem_moderation_gTxt('remove_failed', array('{id}' => $id));
			}
		}
		
		return $res;
	}
	else
	{
		return mem_moderation_gTxt('id_not_found');
	}
}


/** Add an item to the moderation queue */
function submit_moderated_content($type,$email,$desc,$data,$item_id='0') 
{
	global $txp_user,$ign_user, $mem_mod_email_on_new, $mem_mod_notify_email, $sitename;

	// ign_user
	$user = isset($ign_user) ? $ign_user : $txp_user;

	// can this be an update instead?
	if ($item_id != 0)
	{
		$existing_modid = safe_field('id, item_id', 'txp_moderation', "`type` = '".doSlash($type)."' and `user` = '".doSlash($user)."' and `item_id` = ".doSlash($item_id));
	
		if ($existing_modid)
		{
			// do an update instead
			if (update_moderated_content($existing_modid, $desc, $data, $type, $item_id))
			{
				return $existing_modid;
			}

			return false;
		}	
	}

	if (empty($email))
	{
		$email = safe_field('email','txp_users',"name = '".doSlash($user)."'");
	}

	// encode for insert
	$encoded_data = mem_moderation_encode($data);

	$r = safe_insert('txp_moderation',
			"`submitted` = now(),
			`type`	= '".doSlash($type)."',
			`item_id`	= '".doSlash($item_id)."',
			`user`	= '".doSlash($user)."',
			`email`	= '".doSlash($email)."',
			`ip`	= '".doSlash($_SERVER['REMOTE_ADDR'])."',
			`desc`	= '".doSlash($desc)."',
			`data`	= '".doSlash($encoded_data)."'"
			);

	// send email notification
	if ($mem_mod_email_on_new && !empty($mem_mod_notify_email))
	{
		$message = mem_moderation_gTxt('new_submission_email', array('{user}'=> $user, '{type}'=> $type));
		$reply = $from = $to = $mem_mod_notify_email;
		$subject = "[$sitename] " . mem_moderation_gTxt('new_submission_email_subject');
		
		$sent = @mem_form_mail($from, $reply, $to, $subject, $message);
	}

	return $r;
}

/** Update an entry in the moderation queue */
function update_moderated_content($id,$desc,$data,$type='',$item_id='') 
{
	global $mem_mod_email_on_update, $mem_mod_notify_email, $sitename;	

	$encoded_data = mem_moderation_encode($data);

	$set = "`desc` = '". doSlash($desc)."', `data` = '$encoded_data'";

	if (!empty($type))
	{
		$set .= ", `type` = '".doSlash($type)."'";
	}
	
	if (!empty($item_id) or $item_id == '0')
	{
		$set .= ", `item_id` = ".doSlash($item_id);
	}

	$r = safe_update('txp_moderation', $set, "id='".doSlash($id)."'");
	
	if (@txpinterface != 'admin')
	{
		if ($mem_mod_email_on_update && !empty($mem_mod_notify_email))
		{
			$rs = safe_row('user,type', 'txp_moderation', "id='".doSlash($id)."'");
			if ($rs)
				extract($rs);

			$message = mem_moderation_gTxt('update_submission_email', array('{user}'=> $user, '{type}'=> $type));
			$reply = $from = $to = $mem_mod_notify_email;
			$subject = "[$sitename] " . mem_moderation_gTxt('update_submission_email_subject');
			
			$sent = @mem_form_mail($from, $reply, $to, $subject, $message);
		}
	}
	
	return $r;
}

/** Remove an item from the moderation queue */
function remove_moderated_content($id) 
{
	return safe_delete('txp_moderation',"id='".doSlash($id)."'");
}

/** Serialize data for storage */
function mem_moderation_encode($content)
{
	return base64_encode(serialize($content));
}
/** Deserialize data from storage */
function mem_moderation_decode($content)
{
	return unserialize(base64_decode($content));
}

/** Register handlers for a content type */
function mem_moderation_register($type,$vars,$presenter,$approver,$rejecter) 
{
	global $moderation_types;

	$moderation_types[] = array(('type') => $type,
								('variables') => $vars,
								('func_presenter') => $presenter,
								('func_approver') => $approver,
								('func_rejecter') => $rejecter);
}
function register_moderation_type($type, $vars, $presenter, $approver, $rejecter){
	//trigger_error(gTxt('deprecated_tag'), E_USER_NOTICE);
	mem_moderation_register($type, $vars, $presenter, $approver, $rejecter);
}

/** Get the registered variable names */
function mem_get_moderation_variables($type) 
{
	global $moderation_types;

	if (!is_array($moderation_types))
	{
		return array();
	}

	$out = array();

	foreach ($moderation_types as $p) 
	{
		if ($p[('type')]==$type)
		{
			$out = array_merge($out, $p[('variables')]);
		}
	}

	return $out;
}

/** Moderation Callback */
function mem_moderation_callback($type,$callback,$data) 
{
	global $moderation_types;

	if (!is_array($moderation_types))
	{
		return '';
	}

	$out = '';

	foreach ($moderation_types as $p)
	{
		if ($p[('type')]==$type && is_callable($p[('func_'.$callback)])) 
		{
			$out[] = call_user_func($p[('func_'.$callback)],$p[('type')],$data);
		}
	}

	return (is_array($out) ? join(' ',$out) : $out);
}

/** Presenter Callback */
function mem_moderation_presenter_callback($type,$data) {
	return mem_moderation_callback($type,'presenter',$data);
}
/** Approver Callback */
function mem_moderation_approver_callback($type,$data) {
	return mem_moderation_callback($type,'approver',$data);
}
/** Rejecter Callback */
function mem_moderation_rejecter_callback($type,$data) {
	return mem_moderation_callback($type,'rejecter',$data);
}

if (!function_exists('mem_get_user_table_name')) {
	function mem_get_user_table_name() {
		global $prefs;
		
		extract($prefs);
		
		$table_name = 'txp_users';
		
		if (isset($mem_self_use_ign_db) && $mem_self_use_ign_db == '1') {
	
			if (isset($ign_use_custom) && $ign_use_custom=='1') {
	
				if (isset($ign_user_db) && !empty($ign_user_db))
					$table_name = $ign_user_db;
			}
		}
		return $table_name;
	}
}

// ----------------------------------------------------------------
if (@txpinterface == 'admin') 
{	
	// set up tab and event callback
	register_tab('extensions','moderate',$mod_event);
	register_callback('mem_moderate', $mod_event, '', 1);

	// who can access this tab
	add_privs($mod_event,'1,2,3');
	// who can view submission list
	add_privs($mod_event.'.list','1,2,3');
	// who can edit
	add_privs($mod_event.'.edit','1,2,3');
	// who can approve
	add_privs($mod_event.'.approve','1,2,3');

	/** Main step handler */
	function mem_moderate($event, $step)
	{
		global $txp_permissions, $mod_event;
	
		$mod_event = $event;
	
		$msg = '';
	
		pagetop('');
	
		if ($step=='details')
		{
			mem_moderate_details();
		}
		else if ($step==$mod_event.'_update') 
		{
			$action = gps('action');
			if ($action==gTxt('save')) 
			{
				mem_moderate_save();
				mem_moderate_details();
			}
			else 
			{
				if ($action==gTxt('approve')) 
				{
					$msg = mem_moderate_approve();
				}
				else if ($action==gTxt('reject'))
				{
					$msg = mem_moderate_reject();
				}

				mem_moderate_list($msg);
			}
		}
		else if ($step=='preinstall' or $step=='install')
		{
			echo mem_moderation_install();
		}
		else if ($step==$mod_event.'_multi_edit')
		{
			// actions from the moderation list
			$selected = gps('selected');
			$method = gps('edit_method');

			$success = array();
			$failed = array();
			$rs = false;
			
			if (count($selected) > 0)
			{
				$selected_ids = join(',',$selected);
			
				$rs = safe_rows("id,type",'txp_moderation',"`id` IN (". doSlash($selected_ids) .")");
			}

			if ($rs) 
			{
				foreach($rs as $r)
				{
					extract($r);
					$result = '';

					if ($method=='reject')
					{
						$result = mem_moderate_reject($type,$id);
					}
					else if ($method=='approve')
					{
						$result = mem_moderate_approve($type,$id);
					}
					else
					{
						$result = 'unsupported method "'.$method."'";
					}

					if (empty($result))
					{
						$success[] = array('id'=>$id,'type'=>$type);
					}
					else
					{
						$failed[] = array('id'=>$id,'type'=>$type);
					}
				}
			}
			
			$msg = '';
			
			// build success list
			if (count($success)>0)
			{
				$msg .= "{$method} successful for: ";
				$slist = array();
				foreach($success as $s)
				{
					$slist[] = sprintf('%s %d',$s['type'],$s['id']);
				}
				
				$msg .= join(', ',$slist) . '; ';
			}

			// build failure list
			if (count($failed)>0)
			{
				$msg .= '{$method} failed for: ';
				$flist = array();
				foreach($failed as $f)
				{
					$flist[] = sprintf('%s %d',$f['type'],$f['id']);
				}
				
				$msg .= join(', ',$flist) . '; ';
			}

			mem_moderate_list($msg);
		}
		else
		{
			mem_moderate_list($msg);
		}
	}

	/** Plugin installer */
	function mem_moderation_install()
	{
		global $prefs;

		$create_default_forms = true;
		
		$log = array();

		ob_start();

		// set prefs
		if (!isset($prefs['mem_mod_notify_email']))
		{
			set_pref('mem_mod_notify_email', '', 'mem_moderation', 1);
			$log[] = mem_moderation_gTxt('set_pref', array('{name}' => 'mem_mod_notify_email'));
		}
		if (!isset($prefs['mem_mod_email_on_new'])) 
		{
			set_pref('mem_mod_email_on_new', '', 'mem_moderation', 1, 'yesnoradio');
			$log[] = mem_moderation_gTxt('set_pref', array('{name}' => 'mem_mod_email_on_new'));
		}
		if (!isset($prefs['mem_mod_email_on_update'])) 
		{
			set_pref('mem_mod_email_on_update', '', 'mem_moderation', 1, 'yesnoradio');
			$log[] = mem_moderation_gTxt('set_pref', array('{name}' => 'mem_mod_email_on_update'));
		}
		if (!isset($prefs['mem_mod_queue_delay']))
		{
			set_pref('mem_mod_queue_delay', '0', 'mem_moderation', 1);
			$log[] = mem_moderation_gTxt('set_pref', array('{name}' => 'mem_mod_queue_delay'));
		}
		if (!isset($prefs['mem_mod_multiedit_approve']))
		{
			set_pref('mem_mod_multiedit_approve', '0', 'mem_moderation', 1, 'yesnoradio');
			$log[] = mem_moderation_gTxt('set_pref', array('{name}' => 'mem_mod_multiedit_approve'));
		}
		if (!isset($prefs['mem_mod_pub_bypass_queue']))
		{
			set_pref('mem_mod_pub_bypass_queue', '0', 'mem_moderation', 1, 'yesnoradio');
			$log[] = mem_moderation_gTxt('set_pref', array('{name}' => 'mem_mod_pub_bypass_queue'));
		}
		// check for table
		$rs = safe_row("id", "txp_moderation", "1=1 LIMIT 1");

     	if (!$rs && mysql_errno() != 0) 
     	{
     		// add table
			$sql = "CREATE TABLE `".PFX."txp_moderation` (
				  `id` int(10) unsigned NOT NULL auto_increment,
				  `submitted` datetime NOT NULL default '0000-00-00 00:00:00',
				  `type` varchar(32) NOT NULL default '',
				  `item_id` int(10) unsigned NOT NULL default '0',
				  `user` varchar(64) NOT NULL default '',
				  `email` varchar(100) NOT NULL default '',
				  `ip` varchar(16) NOT NULL default '',
				  `desc` text NOT NULL,
				  `data` longtext NOT NULL,
				  PRIMARY KEY  (`id`),
				  KEY `type` (`type`)
				) Type=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ";
			
			if (($rs=safe_query($sql))) 
			{
				$log[] = mem_moderation_gTxt('table_created', array('{name}' => PFX."txp_moderation"));
			}
			else
			{
				$log[] = mem_moderation_gTxt('table_create_failed', array('{mysql_error}' => mysql_error()));
			}
		}
		else
		{
			$log[] = mem_moderation_gTxt('table_exists');

			$rs = safe_row("item_id", "txp_moderation", "1=1 LIMIT 1");
	
			if (!$rs && mysql_errno() != 0)
			{
		     	if (safe_alter('txp_moderation', "ADD `item_id` INT NOT NULL DEFAULT '0' AFTER `type`"))
		     	{
		     		$log[] = mem_moderation_gTxt('add_item_field');
		     	}
		     	else
		     	{
		     		$log[] = mem_moderation_gTxt('add_item_field_failed');
		     	}
			}
		}

		if ($create_default_forms) 
		{
			$form = fetch('Form','txp_form','name','mod_submission_list');
			if (!$form)
			{
				$form_html = <<<EOF
<txp:mem_moderation_edit_link><txp:mem_moderation_info field="type" /> 
#<txp:mem_moderation_info field="id" /></txp:mem_moderation_edit_link> - <txp:mem_moderation_info field="submitted" />
<div>
	<txp:mem_moderation_info field="desc" />
</div>
EOF;
				$form_html = doSlash($form_html);
				if (safe_insert('txp_form',"name='mod_submission_list',type='misc',Form='{$form_html}'"))
				{
					$log[] = mem_moderation_gTxt('form_created', array('{form}' => 'mod_submission_list'));
				}
				else
				{
					$log[] = mem_moderation_gTxt('form_create_failed', array('{form}' => 'mod_submission_list', 'mysql_error' => mysql_error()));
				}
			}
			else
			{
				$log[] = mem_moderation_gTxt('form_found', array('{form}' => 'mod_submission_list'));
			}


		} 
		else
		{
			$log[] = mem_moderation_gTxt('skipping_form_install');
		}

		ob_end_clean();

		return tag(mem_moderation_gTxt('install_log'),'h2').doWrap($log,'ul','li');
	}
	
	/** Show item moderation details */
	function mem_moderate_details()
	{
		global $step,$mod_event,$txp_user;
	
		$can_approve = has_privs('moderate.approve');
		$can_edit = has_privs('moderate.edit');
	
		$id = gps('id');
		if($id) 
		{
			// fetch item
			extract(safe_row("*", "txp_moderation", "id = " . doSlash($id)));
	
			$can_edit = $can_edit or $user==$txp_user;
	
			$textarea = '<textarea name="moderation_description" cols="40" rows="7" tabindex="4">'.
						htmlspecialchars($desc).
						'</textarea>';
	
			$out = StartTable('listing');
			$out .= tr( td(
						hed("$type #{$id} - ".href(($user?$user:$email),'mailto:'.$email) . " ({$email})", 2) .
						tag( gTxt('submitted') .' '. $submitted , 'div') .
						tag( gTxt('notes'), 'div') . $textarea
						)
					) .
					tr( td('<hr />') );
			$out .= EndTable();
	
			$decoded_data = mem_moderation_decode($data);

			$decoded_data['id'] = $id;
			$decoded_data['type'] = $type;
			$decoded_data['user'] = $user;
			$decoded_data['email'] = $email;
	
			$out .= mem_moderation_presenter_callback($type, $decoded_data);
	
			$out .= eInput( $mod_event ) . sInput( $mod_event .'_update' ) . hInput( 'id', $id );
			$out .= hInput('type',$type);
			if ($can_edit or $can_approve)
			{
				$out .= StartTable('') .
					tr(
						($can_edit ?
								td( fInput( "submit", 'action', gTxt( 'save' ), "publish" ) )
							: td()
						) .
						($can_approve ?
								td( fInput( "submit", 'action', gTxt( 'approve' ), "publish" ) ) .
								td( fInput( "submit", 'action', gTxt( 'reject' ), "publish",'',"return confirm('". gTxt('are_you_sure')."')" ) )
							: td() . td()
						)
					) .
					EndTable();
			}
	
			echo form( $out );
		}
	}
}
else 
{
	$type = gps($mod_event);
	
	if (!empty($type)) {
		echo tag('Doing a moderation postback','div');

		$vars = mem_get_moderation_variables($type);
		
		$data = gpsa($vars);

		exit();
	}
}

# --- END PLUGIN CODE ---

?>