<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current file name.
// Uncomment and edit this line to override:
$plugin['name'] = 'mem_moderation';

$plugin['version'] = '0.5';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'This plugin adds a generic moderation queue to Textpattern. A plugin can extend the moderation queue to support any type of content.';
$plugin['type'] = 1; // 0 for regular plugin; 1 if it includes admin-side code


@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h1. Moderation Queue

p. This plugin adds a generic moderation queue to Textpattern. A plugin can extend the moderation queue to support any type of content.

h1. Installation

p. "Start Install Wizard":./index.php?event=moderate&step=preinstall

h1. Tag List (with arguments)

*moderate_submission_list* - displays a list of content the current user submitted for moderation, which has yet to be approved.

p. These tags may be used within any moderation form tag that supports $mem_mod_info. This applies for plugins too.

*mod_edit_link* - parses the enclosed text and creates a link to edit the content in the moderation queue.
* baseurl - should be set to the url where pending content can be edited by a user. Defaults to the current url.

*mod_id* - displays the id

*mod_user* - displays the submitting user

*mod_desc* - displays desc

*mod_type* - displays type

*mod_email* - displays email

*mod_if_type* - conditionally parses enclosed tags. Supports @<txp:else />@
* name - name of type to compare

*mod_if_data*
* name - the field name stored within the embedded data.

*mod_submitted*
* format - a time format string. "see strftime":http://www.php.net/strftime for format specifiers

h1. How to create a moderation plugin.

p. All plugins must include the following line of code to guarantee that they are loaded after the moderation queue.

<code>require_plugin('mem_moderation');</code>

h2. Registering a Moderation Plugin

p. The moderation queue will need to know how to deal with a specific  type of content. Therefore, all plugins must call the below function once for each type that it will support.

p. *register_moderation_type($type,$vars,$presenter,$approver,$rejecter)*

* $type - This type name that is being registered. E.g. 'article'
* $vars - An array of variable names that this plugin will use. This is needed to allow moderators to edit submitted content.
* $presenter - The name of the function that will construct a form to edit the plugin specific content. This is needed to allow moderators to edit submitted content.
* $approver - The name of the function

h3. Moderation Event Callbacks

p. All callback functions have the structure of callback($type,$data), where $type is the type name that is being processed and $data is the type specific data appended to the plugin.

p. *presenter*
A submitted item is being viewed by a moderator and the plugin's presenter handler should return html formatted form fields to enable the moderator to edit the data (no form tag or buttons).

p. *approver*
A submitted item has been approved and the plugin's approver handler should submit $data to the appropriate place where live content resides.

p. *rejecter*
A submitted item has been rejected and is being removed from the queue.

h3. Moderation Actions

p. These functions should be used to modify the contents of the moderation queue. Using other methods to access the moderation table is not recommended, otherwise the data may not be properly encoded/decoded.

p. *remove_moderated_content($id)*
This will delete the specified submission from the moderation queue.

p. *submit_moderated_content($type,$email,$desc,$data)*
This will make a new entry in the moderation queue. It will return the id of the new entry on success, otherwise false.

p. *update_moderated_content($id,$desc,$data)*
This will update the encoded data and the description of an existing item in the moderation queue.


h3. Helper functions

p. These functions are not required by moderation plugins, but have been provided to make life easier.

p. *mem_get_pref($name,$field='')*
This will fetch a preference from the txp_prefs table and return an array containing all of the values. If $field is specified, then only the desired field will be returned. All fetches are cached.

p. *mem_set_pref($name, $val, $event,  $type, $position=0, $html='text_input')*
This will insert or update a preference from the txp_prefs table and return the result of the db call. All values are cached.

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
define('QUEUE_SUBMISSION_DELAY', "0");

// Specify whether users with Publisher privs will have their
// submitted content appear immediatly in the list without 
// waiting for the QUEUE_SUBMISSION_DELAY
define('PUBLISHERS_BYPASS_QUEUE_DELAY', true);


// By default, the ability to approve from the moderation queue
// is disabled. Set to true to enable this behavior
define('ALLOW_APPROVE_FROM_LIST', false);


global $mod_event;
$mod_event = 'moderate';

require_plugin('mem_admin_parse');
// make sure ign is loaded first, if it exists
@load_plugin('ign_password_protect');
// might need access to ign user table
@load_plugin('mem_self_register');

// needed for MLP
define( 'MEM_MODERATION_PREFIX' , 'mem_moderation' );

global $mem_moderation_lang;

if (!is_array($mem_moderation_lang))
{
	$mem_moderation_lang = array(
		'form_used'	=>	'This form has already been used to submit.',
		'form_expired'	=>	'The form has expired.',
		'spam'	=> 'Your submission was blocked by a spam filter.',
		'invalid_utf8'	=> 'Invalid UTF8 string for field {label}.',
		'min_warning'	=> 'The input field {label} must be at least {min} characters long.',
		'max_warning'	=> 'The input field {label} must be smaller than {max} characters long.',
		'field_missing'	=> 'The field {label} is required.',
		'invalid_email'	=> 'The email address {email} is invalid.',
		'invalid_host'	=> 'The host {domain} is invalid.',
		
	);
}

register_callback( 'mem_moderation_enumerate_strings' , 'l10n.enumerate_strings' );
function mem_moderation_enumerate_strings($event , $step='' , $pre=0)
{
	global $mem_self_lang;
	$r = array	(
				'owner'		=> 'mem_moderation',			#	Change to your plugin's name
				'prefix'	=> MEM_MODERATION_PREFIX,				#	Its unique string prefix
				'lang'		=> 'en-gb',						#	The language of the initial strings.
				'event'		=> 'public',					#	public/admin/common = which interface the strings will be loaded into
				'strings'	=> $mem_moderation_lang,		#	The strings themselves.
				);
	return $r;
}


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
	function mem_get_pref($name,$field='')
	{
		global $pref_cache;
		
		if ($pref_cache==null)
			$pref_cache = array();

		if (!in_array($name,$pref_cache) or $pref_cache[$name] === false) {
			$name = doSlash($name);
			$pref_cache[$name] = safe_row('*','txp_prefs',"name='$name'");
		} else {
			$pref_cache[$name] = false;			
		}
		
		$pref = $pref_cache[$name];

		if (empty($field))
			return $pref;
		
		if (array_key_exists($field,$pref))
			return $pref[$field];

		return false;
	}
}


if (!function_exists('mem_get_user_table_name')) {
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
}

if (@txpinterface == 'admin') {
	register_tab('extensions','moderate',$mod_event);
	register_callback('mem_moderate', $mod_event, '', 1);

	// who can access this tab
	add_privs('moderate','1,2,3');
	// who can view submission list
	add_privs('moderate.list','1,2,3');
	// who can edit
	add_privs('moderate.edit','1,2,3');
	// who can approve
	add_privs('moderate.approve','1,2,3');

	// -------------------------------------------------------------
	function mem_moderate($event, $step) {
		global $txp_permissions,$mod_event;
	
		$mod_event = $event;
	
		$msg = '';
	
		pagetop('');
	
		if ($step=='details') {
			moderate_details();
		} else if ($step==$mod_event.'_update') {
			$action = gps('action');
			if ($action==gTxt('save')) {
				// save
				moderate_save();
				// show details
				moderate_details();
			} else {
				if ($action==gTxt('approve')) {
					$msg = moderate_approve();
				} else if ($action==gTxt('reject')) {
					$msg = moderate_reject();
				}
				moderate_list($msg);
			}
		} else if ($step=='preinstall') {
			echo moderate_preinstall();
		} else if ($step=='install') {
			echo moderate_install();
		} else if ($step==$mod_event.'_multi_edit') {
			
			$selected = gps('selected');
			$method = gps('edit_method');

			$success = array();
			$failed = array();
			
			if (count($selected) > 0) {
				$selected_ids = join(',',$selected);
			
				$rs = safe_rows("id,type",'txp_moderation',"`id` IN (". doSlash($selected_ids) .")");

			} else {
				$rs = false;
			}

			if ($rs) {
				foreach($rs as $r) {
					extract($r);
					$result = '';

					if ($method=='reject')
						$result = moderate_reject($type,$id);
					else if ($method=='approve')
						$result = moderate_approve($type,$id);	
					else
						$result = 'unsupported method "'.$method."'";

					if (empty($result))
						$success[] = array('id'=>$id,'type'=>$type);
					else
						$failed[] = array('id'=>$id,'type'=>$type);
				}
			}
			
			$msg = '';
			
			if (count($success)>0)
			{
				$msg .= "{$method} successful for: ";
				$slist = array();
				foreach($success as $s)
					$slist[] = sprintf('%s %d',$s['type'],$s['id']);
				
				$msg .= join(', ',$slist) . '; ';
			}

			if (count($failed)>0)
			{
				$msg .= '{$method} failed for: ';
				$flist = array();
				foreach($failed as $f)
					$flist[] = sprintf('%s %d',$f['type'],$f['id']);
				
				$msg .= join(', ',$flist) . '; ';
			}

			moderate_list($msg);
		} else {
			moderate_list($msg);
		}
	}

	function moderate_preinstall()
	{
		return moderate_install();
	}

	function moderate_install()
	{
		$create_default_forms = true;
		
		$log = array();
		
		// create prefs
		$pref = mem_get_pref('mem_moderation_email_enabled');
		if ($pref === false) {
			mem_set_pref('mem_moderation_admin_email','0','mem_moderation',1,0,'yesnoRadio');
		}
		
		$pref = mem_get_pref('mem_moderation_admin_email');
		if ($pref === false) {
			mem_set_pref('mem_moderation_admin_email','','mem_moderation',1,1);
		}

		$pref = mem_get_pref('mem_moderation_email_form');
		if ($pref === false) {
			mem_set_pref('mem_moderation_email_form','','mem_moderation',1,2);
		}

     	if (!($rs=safe_query("SELECT 1 FROM `".PFX."txp_moderation` LIMIT 0"))) {
			$sql = "CREATE TABLE `".PFX."txp_moderation` (
				  `id` int(10) unsigned NOT NULL auto_increment,
				  `submitted` datetime NOT NULL default '0000-00-00 00:00:00',
				  `type` varchar(32) NOT NULL default '',
				  `user` varchar(64) NOT NULL default '',
				  `email` varchar(100) NOT NULL default '',
				  `ip` varchar(16) NOT NULL default '',
				  `desc` text NOT NULL,
				  `data` longtext NOT NULL,
				  PRIMARY KEY  (`id`),
				  KEY `type` (`type`)
				) Type=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ";
			
			if (($rs=safe_query($sql))) {
				$log[] = "Created moderation table ". PFX."txp_moderation";
			}
		}
     	
     	if (mysql_errno() != 0)
			$log[] = "Failed to create moderation table. " . mysql_error();
		else
			$log[] = "Moderation table already exists";

		if ($create_default_forms) {
			$form = fetch('Form','txp_form','name','mod_submission_list');
			if (!$form) {
				$form_html = <<<EOF
<txp:mod_edit_link><txp:mod_type /> #<txp:mod_id /></txp:mod_edit_link> - <txp:mod_submitted />
<div>
	<txp:mod_desc />
</div>
EOF;
				$form_html = doSlash($form_html);
				if (safe_insert('txp_form',"name='mod_submission_list',type='misc',Form='{$form_html}'"))
					$log[] = "Created form 'mod_submission_list'";
				else
					$log[] = "Failed to create form 'mod_submission_list'. " . mysql_error();
			} else
				$log[] = "Found form 'mod_submission_list'. Skipping installation of default form.";
		} else
			$log[] = "Skipping installation of default forms";

		return tag("Install Log",'h2').doWrap($log,'ul','li');
	}
}

// -------------------------------------------------------------
function moderate_submission_list($atts,$thing='')
{
	global $step,$link_list_pageby,$mod_event,$txp_user,$ign_user,$mem_mod_info;
	
	// try looking for user from ign_password_protect plugin
	if ((!isset($txp_user) or empty($txp_user))) $txp_user = $ign_user;

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

	if (!empty($form))
		$Form = fetch_form($form);
	else
		$Form = $thing;

	extract(get_prefs());

	$out = array();
	
	$user = doSlash($user);
	$type = doSlash($type);
	
	$where = "`user` LIKE '{$user}'";
	if (!empty($type))
		$where .= " AND `type` LIKE '{$type}'";
	$where .= " ORDER BY `submitted`";

	$rs = safe_rows_start('*','txp_moderation',$where);

	if ($rs) {

		while ($a = nextRow($rs)) {
			$mem_mod_info = $a;

			$out[] = admin_parse($Form);
		}
		unset($mem_mod_info);
	}

	$items = '';

	if (count($out) > 0 && !empty($label))
		$items = doTag($label,$labelwraptag,$labelclass);

	$items .= doWrap($out,$wraptag,$break,$class,$breakclass);

	return $items;
}

// -------------------------------------------------------------
function mod_note_input($atts) {
	global $mem_mod_info;
	extract(lAtts(array(
		'style'	=>	'',
		'class'	=>	__FUNCTION__
	),$atts));
	return '<textarea name="note"'.(!empty($style)?' style="'.$style.'"':'').
			(!empty($class)?' class="'.$class.'"':'').'>'.
			htmlspecialchars(@$mem_mod_info['note']).'</textarea>';
}

function mod_edit_link($atts,$thing) {
	global $mod_event,$mem_mod_info;
	extract(lAtts(array(
		'baseurl'	=> $_SERVER['REQUEST_URI']
	),$atts));

	$elink = '<a href="'.$baseurl.'?event='.$mod_event.'&step='.$mem_mod_info['type'].'_edit&modid='.$mem_mod_info['id'].'">'.admin_parse($thing).'</a>';
	return $elink;
}
function mod_id($atts) { 
	global $mem_mod_info;
	return $mem_mod_info['id'];
}
function mod_submitted($atts) { 
	global $mem_mod_info;
	
	extract(get_prefs());
	
	extract(lAtts(array(
		'format'	=> $archive_dateformat
	),$atts));
	
	$time = strtotime($mem_mod_info['submitted']);

	return safe_strftime($format,$time);
}
function mod_user($atts) {
	global $mem_mod_info;
	
	return $mem_mod_info['user'];
}
function mod_desc($atts) {
	global $mem_mod_info;
	return $mem_mod_info['desc'];
}
function mod_type($atts) {
	global $mem_mod_info;
	return $mem_mod_info['type'];
}
function mod_email($atts) {
	global $mem_mod_info;
	return $mem_mod_info['email'];
}
function mod_if_type($atts,$thing) {
	global $mem_mod_info;
	extract($atts);
	if (isset($name)) {
		$cond = $name==$mem_mod_info['type'];
		return admin_parse(EvalElse($thing,$cond));
	}
}
function mod_if_data($atts,$thing) {
	global $mem_mod_info;
	extract($atts);
	if (isset($name)) {
		$data = decode_content($mem_mod_info['data']);
		
		$cond = is_array($data) and array_key_exists($name,$data) and !empty($data[$name]);
		return admin_parse(EvalElse($thing,$cond));
	}
}

function mem_moderation_if_gps($atts,$thing)
{
	extract(lAtts(array(
		'name'	=> '',
		'value'	=> '',
	),$atts));

	$v = gps($name);
	
	$cond = false;
	
	if (empty($value))
		$cond = !empty($v);
	else 
		$cond = ($v == $value);

	return admin_parse(EvalElse($thing,$cond));
}

function mod_data($atts) {
	global $mem_mod_info;
	extract($atts);
	if (isset($name)) {
		$data = decode_content($mem_mod_info['data']);
		
		if (is_array($data) and array_key_exists($name,$data)) {
			return $data[$name];
		}
	}
	return '';
}

// -------------------------------------------------------------
function moderate_list($message="")
{
	global $step,$link_list_pageby,$mod_event;

	if (!has_privs('moderate.list')) {
		return;
	}

	extract(get_prefs());

	$page = gps('page');
	$total = getCount('txp_moderation',"1");
	$limit = $link_list_pageby;
	$numPages = ceil($total/$limit);
	$page = (!$page) ? 1 : $page;
	$offset = ($page - 1) * $limit;

	$sort = gps('sort');
	$dir = gps('dir');

	$sort = ($sort) ? $sort : "submitted";
	$dir = ($dir) ? $dir : 'desc';
	if ($dir == "desc") { $dir = "asc"; } else { $dir = "desc"; }

	$nav[] = ($page > 1)
	?	PrevNextLink($mod_event,$page-1,gTxt('prev'),'prev') : '';

	$nav[] = sp.small($page. '/'.$numPages).sp;

	$nav[] = ($page != $numPages)
	?	PrevNextLink($mod_event,$page+1,gTxt('next'),'next') : '';

	if(PUBLISHERS_BYPASS_QUEUE_DELAY) {
		$user_table = mem_get_user_table_name();
		if (empty($user_table)) $user_table = 'txp_users';
		
		$rs = safe_rows_start(
			"txp_moderation.*",
			"txp_moderation, {$user_table}",
			" txp_moderation.user={$user_table}.name AND (DATE_SUB( NOW(), INTERVAL ". QUEUE_SUBMISSION_DELAY ." DAY ) > txp_moderation.submitted OR {$user_table}.privs = 1) order by txp_moderation.{$sort} $dir limit $offset,$limit"
		);
	} else {
		$rs = safe_rows_start(
			"*",
			"txp_moderation",
			" (DATE_SUB( NOW(), INTERVAL ". QUEUE_SUBMISSION_DELAY ." DAY ) > submitted) order by {$sort} $dir limit $offset,$limit"
		);
	}
	
	if ($rs) {

		echo '<form action="index.php" method="post" name="longform" onsubmit="return verify(\''.gTxt('are_you_sure').'\')">',
		startTable('list'),

		tr(
			column_head('id','id',$mod_event,1,$dir).
			column_head('submitted','submitted',$mod_event,1,$dir).
			column_head('type','type',$mod_event,1,$dir).
			column_head('user','user',$mod_event,1,$dir).
			column_head('description','description',$mod_event,0,$dir).
			td()
			);

		$alt = false;
		while ($a = nextRow($rs)) {
			extract($a);
			$elink = eLink($mod_event,'details','id',$id,$id);
			$cbox = fInput('checkbox','selected[]',$id);

			if (empty($desc)) {
				$data = decode_content($a['data']);
				
				// no desc, use another field
				if ($type=='article')
					$desc = $data['title'];
				else if ($type=='image') {
					if (!empty($data['alt']))
						$desc = $data['alt'];
					else
						$desc = $data['name'];
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
		if (ALLOW_APPROVE_FROM_LIST)
			$multi_edit_options['approve'] = gTxt('approve');

		echo tr(tda(select_buttons().
		event_multiedit_form($mod_event,$multi_edit_options, $page, $sort, $dir,'',''),' colspan="6" style="text-align:right;border:0px"'));

		echo endTable(),'</form>';
		echo pageby_form($mod_event,$link_list_pageby);
		echo graf(join('',$nav),' align="center"');
	} else {
		echo 'Failed to access table. ' . mysql_error();
	}
}

// -------------------------------------------------------------
function moderate_save()
{
	require_privs('moderate.edit');
	
	extract(gpsa(array('id','type','moderation_description')));
	
	$vars = gpsa(get_moderation_variables($type));
	
	update_moderated_content($id,$moderation_description,$vars);
}


// -------------------------------------------------------------
function moderate_approve($type='',$id=false)
{
	require_privs('moderate.approve');

	if ($id===false || empty($type)) {
		extract(gpsa(array('id','type')));
	
		// save it first
		moderate_save();
	} else {
		// Approving from the listing
	}
	
	$rs = safe_row("*", "txp_moderation", "id = $id");
	
	if ($rs) {
		$vars = get_moderation_variables($type);
	
		extract(array_merge($rs,$vars));
	
		$decoded_data = decode_content($rs['data']);
		$decoded_data['id'] = $id;
	
		$res = approver_callback($type,$decoded_data);
	
		if ($res == '')
			remove_moderated_content($id);
			
		return $res;
	} else {
		return 'Moderation ID not found';
	}
}

// -------------------------------------------------------------
function moderate_reject($type='',$id=false)
{
	require_privs('moderate.approve');

	if ($id===false || empty($type))
		extract(gpsa(array('id','type')));
	
	$rs = safe_row("*", "txp_moderation", "id = $id");
	
	if ($rs) {
		$decoded_data = decode_content($rs['data']);
		$decoded_data['id'] = $id;
	
		$res = rejecter_callback($type,$decoded_data);

		if ($res == '') {
			if (remove_moderated_content($id)) {
			} else {
				return 'Failed to remove ID '. $id;
			}
		}
		
		return $res;
	} else {
		return 'Moderation ID not found';
	}
}

// -------------------------------------------------------------
function moderate_details()
{
	global $step,$mod_event,$txp_user;

	$can_approve = has_privs('moderate.approve');
	$can_edit = has_privs('moderate.edit');

	$id = gps('id');
	if($id) {
		extract($z=safe_row("*", "txp_moderation", "id = $id"));

		$can_edit = $can_edit or $user==$txp_user;			

		$textarea = '<textarea name="moderation_description" cols="40" rows="7" tabindex="4">'.$desc.'</textarea>';

		$out = StartTable('listing');
		$out .= tr( td(
					hed("$type #{$id} - ".href(($user?$user:$email),'mailto:'.$email) . " ({$email})", 2) .
					tag( gTxt('submitted') .' '. $submitted , 'div') .
					tag( gTxt('notes'), 'div') . $textarea
					)
				) .
				tr( td('<hr />') );
		$out .= EndTable();

		$decoded_data = decode_content($data);

		$decoded_data['id'] = $id;
		$decoded_data['type'] = $type;
		$decoded_data['user'] = $user;
		$decoded_data['email'] = $email;

		$out .= presenter_callback($type, $decoded_data);

		$out .= eInput( $mod_event ) . sInput( $mod_event .'_update' ) . hInput( 'id', $id );
		$out .= hInput('type',$type);
		if ($can_edit or $can_approve) {
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

// -------------------------------------------------------------
function submit_moderated_content($type,$email,$desc,$data) 
{
	global $txp_user,$ign_user;

	if (isset($ign_user)) $txp_user = $ign_user;
	$type = doSlash($type);
	$email = doSlash($email);
	$desc = doSlash($desc);

	if (empty($email))
		$email = safe_field('email','txp_users',"name LIKE '{$txp_user}'");

	$ip = $_SERVER['REMOTE_ADDR'];
	$encoded_data = encode_content($data);

	// prevent duplicate submissions for a minute
	if (safe_count('txp_moderation', "`type` = '$type' and `user` = '$txp_user' and `desc` = '$desc' and `submitted` > (now() - INTERVAL 1 MINUTE)"))
		return false;

	$set = "`submitted` = now(),
			`type`	= '$type',
			`user`	= '$txp_user',
			`email`	= '$email',
			`ip`	= '$ip',
			`desc`	= '$desc',
			`data`	= '$encoded_data'";

	$r = safe_insert('txp_moderation',$set);

	// email moderators about new submission
//	txpMail($mod_email,'[MEM_MODERATION] New '.strtoupper($type).' Submitted',$body);

	return $r;
}

// -------------------------------------------------------------
function update_moderated_content($id,$desc,$data) 
{
	$encoded_data = encode_content($data);
	$id = doSlash($id);
	$desc = doSlash($desc);

	$set = "`desc`	= '$desc',
			`data`	= '$encoded_data'";

	return safe_update('txp_moderation',$set, "id='{$id}'");
}

// -------------------------------------------------------------
function remove_moderated_content($id) 
{
	$id = assert_int($id);
	return safe_delete('txp_moderation',"id='$id'");
}

// -------------------------------------------------------------
function encode_content($content) 
{
	return base64_encode(serialize($content));
}

// -------------------------------------------------------------
function decode_content($content) 
{
	return unserialize(base64_decode($content));
}

// -------------------------------------------------------------
function gMid($name) 
{
	$mid = 'moderate_';
	$a = array('type','variables','func_presenter','func_approver','func_rejecter');

	if (in_array($name,$a))
		return $mid.$name;
	return $name;
}


// -------------------------------------------------------------
function register_moderation_type($type,$vars,$presenter,$approver,$rejecter) 
{
	global $moderation_types;

	$moderation_types[] = array(('type') => $type,
								('variables') => $vars,
								('func_presenter') => $presenter,
								('func_approver') => $approver,
								('func_rejecter') => $rejecter);
}

// -------------------------------------------------------------
function get_moderation_variables($type) 
{
	global $moderation_types;

	if (!is_array($moderation_types)) return array();

	$out = array();

	foreach ($moderation_types as $p) {
		if ($p[('type')]==$type)
			$out = array_merge($out, $p[('variables')]);
	}

	return $out;
}

// -------------------------------------------------------------
function moderation_callback($type,$callback,$data) 
{
	global $moderation_types;

	if (!is_array($moderation_types)) return '';

	$out = '';

	foreach ($moderation_types as $p) {
		if ($p[('type')]==$type && is_callable($p[('func_'.$callback)])) {
			$out[] = call_user_func($p[('func_'.$callback)],$p[('type')],$data);
		}
	}

	return (is_array($out) ? join(' ',$out) : $out);
}

// -------------------------------------------------------------
function presenter_callback($type,$data) 
{
	return moderation_callback($type,'presenter',$data);
}

// -------------------------------------------------------------
function approver_callback($type,$data) 
{
	return moderation_callback($type,'approver',$data);
}

// -------------------------------------------------------------
function rejecter_callback($type,$data) 
{
	return moderation_callback($type,'rejecter',$data);
}


function mem_moderation_form($atts, $thing='')
{
	global $sitename, $prefs, $production_status, $mem_moderation_error, $mem_moderation_submit,
		$mem_moderation_form, $mem_moderation_labels, $mem_moderation_values, $mem_moderation_successform, 
		$mem_moderation_default, $mem_moderation_form_type;
	
	extract(mem_moderation_lAtts(array(
		'form'		=> '',
		'successform'	=> '',
		'label'		=> '',
		'type'		=> '',
		'redirect'	=> '',
		'show_error'	=> 1,
		'show_input'	=> 1,
	), $atts));
	
	if (empty($type)) {
		trigger_error('Type argument not specified for mem_moderation_form tag', E_USER_WARNING);
		
		return '';
	}
	$mem_moderation_form_type = $type;
	
	$mem_moderation_default = array('test'=>'test');
	callback_event('mem_moderation_form.defaults');
	
	unset($atts['show_error'], $atts['show_input']);
	$mem_moderation_form_id = md5(serialize($atts).preg_replace('/[\t\s\r\n]/','',$thing));
	$mem_moderation_submit = (ps('mem_moderation_form_id') == $mem_moderation_form_id);
	
	$nonce   = doSlash(ps('mem_moderation_nonce'));
	$renonce = false;

	if ($mem_moderation_submit) {
		safe_delete('txp_discuss_nonce', 'issue_time < date_sub(now(), interval 10 minute)');
		if ($rs = safe_row('used', 'txp_discuss_nonce', "nonce = '$nonce'"))
		{
			if ($rs['used'])
			{
				unset($mem_moderation_error);
				$mem_moderation_error[] = mem_moderation_gTxt('form_used');
				$renonce = true;
				$_POST = array();
				$_POST['mem_moderation_submit'] = TRUE;
				$_POST['mem_moderation_form_id'] = $mem_moderation_form_id;
				$_POST['mem_moderation_nonce'] = $nonce;
			}
		}
		else
		{
			$mem_moderation_error[] = mem_moderation_gTxt('form_expired');
			$renonce = true;
		}
	}
	
	if ($mem_moderation_submit and $nonce and !$renonce)
	{
		$mem_moderation_nonce = $nonce;
	}

	elseif (!$show_error or $show_input)
	{
		$mem_moderation_nonce = md5(uniqid(rand(), true));
		safe_insert('txp_discuss_nonce', "issue_time = now(), nonce = '$mem_moderation_nonce'");
	}

	$form = ($form) ? fetch_form($form) : $thing;


	if (empty($form))
	{
		$form = '
<txp:mem_moderation_text label="'.mem_moderation_gTxt('name').'" /><br />
<txp:mem_moderation_email /><br />'.
'<txp:mem_moderation_textarea /><br />
<txp:mem_moderation_submit />
';
	}

	$form = parse($form);
	
	if (!$mem_moderation_submit) {
	  # don't show errors or send mail
	}
	elseif (!empty($mem_moderation_error))
	{
		if ($show_error or !$show_input)
		{
			$out .= n.'<ul class="memError">';

			foreach (array_unique($mem_moderation_error) as $error)
			{
				$out .= n.t.'<li>'.$error.'</li>';
			}

			$out .= n.'</ul>';

			if (!$show_input) return $out;
		}
	}
	elseif ($show_input and is_array($mem_moderation_form))
	{
		/// load and check spam plugins/
//		$evaluation =& get_mem_moderation_evaluator();
//		$clean = $evaluation->get_mem_moderation_status();

		if ($clean != 0) {
			return mem_moderation_gTxt('spam');
		}

		$result = callback_event('mem_moderation_form.submit');

		safe_update('txp_discuss_nonce', "used = '1', issue_time = now()", "nonce = '$nonce'");
		
		if (!empty($result))
			return $result;
			
		if ($redirect)
		{
			while (@ob_end_clean());
			$uri = hu.ltrim($redirect,'/');
			if (empty($_SERVER['FCGI_ROLE']) and empty($_ENV['FCGI_ROLE']))
			{
				txp_status_header('303 See Other');
				header('Location: '.$uri);
				header('Connection: close');
				header('Content-Length: 0');
			}
			else
			{
				$uri = htmlspecialchars($uri);
				$refresh = mem_moderation_gTxt('refresh');
				echo <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>$sitename</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="refresh" content="0;url=$uri" />
</head>
<body>
<a href="$uri">$refresh</a>
</body>
</html>
END;
			}
			exit;
		}			
	}

	if ($show_input or gps('mem_moderation_send_article'))
	{
		return '<form method="post"'.((!$show_error and $mem_moderation_error) ? '' : ' id="mem'.$mem_moderation_form_id.'"').' class="mem_moderationForm" action="'.htmlspecialchars(serverSet('REQUEST_URI')).'#mem'.$mem_moderation_form_id.'">'.
			( $label ? n.'<fieldset>' : n.'<div>' ).
			( $label ? n.'<legend>'.htmlspecialchars($label).'</legend>' : '' ).
			$out.
			n.'<input type="hidden" name="mem_moderation_nonce" value="'.$mem_moderation_nonce.'" />'.
			n.'<input type="hidden" name="mem_moderation_form_id" value="'.$mem_moderation_form_id.'" />'.
			$form.
			callback_event('mem_moderation_form.display').
			( $label ? (n.'</fieldset>') : (n.'</div>') ).
			n.'</form>';
	}

	return '';
}

function mem_moderation_text($atts)
{
	global $mem_moderation_error, $mem_moderation_submit, $mem_moderation_default;

	extract(mem_moderation_lAtts(array(
		'break'		=> br,
		'default'	=> '',
		'isError'	=> '',
		'label'		=> mem_moderation_gTxt('text'),
		'max'		=> 100,
		'min'		=> 0,
		'name'		=> '',
		'required'	=> 1,
		'size'		=> ''
	), $atts));

	$min = intval($min);
	$max = intval($max);
	$size = intval($size);

	if (empty($name)) $name = mem_moderation_label2name($label);

	if ($mem_moderation_submit)
	{
		$value = trim(ps($name));
		$utf8len = preg_match_all("/./su", $value, $utf8ar);
		$hlabel = htmlspecialchars($label);

		if (strlen($value))
		{
			if (!$utf8len)
			{
				$mem_moderation_error[] = mem_moderation_gTxt('invalid_utf8', array('{label}'=>$hlabel));
				$isError = "errorElement";
			}

			elseif ($min and $utf8len < $min)
			{
				$mem_moderation_error[] = mem_moderation_gTxt('min_warning', array('{label}'=>$hlabel, '{min}'=>$min));
				$isError = "errorElement";
			}

			elseif ($max and $utf8len > $max)
			{
				$mem_moderation_error[] = mem_moderation_gTxt('max_warning', array('{label}'=>$hlabel, '{max}'=>$max));
				$isError = "errorElement";
			}

			else
			{
				mem_moderation_store($name, $label, $value);
			}
		}
		elseif ($required)
		{
			$mem_moderation_error[] = mem_moderation_gTxt('field_missing', array('{label}'=>$hlabel));
			$isError = "errorElement";
		}
	}

	else
	{
		if (isset($mem_moderation_default[$name]))
			$value = $mem_moderation_default[$name];
		else
			$value = $default;
	}

	$size = ($size) ? ' size="'.$size.'"' : '';
	$maxlength = ($max) ? ' maxlength="'.$max.'"' : '';

	$memRequired = $required ? 'memRequired' : '';

        return '<label for="'.$name.'" class="memText '.$memRequired.$isError.' '.$name.'">'.htmlspecialchars($label).'</label>'.$break.
		'<input type="text" id="'.$name.'" class="memText '.$memRequired.$isError.'" name="'.$name.'" value="'.htmlspecialchars($value).'"'.$size.$maxlength.' />';
}

function mem_moderation_textarea($atts)
{
	global $mem_moderation_error, $mem_moderation_submit, $mem_moderation_default;

	extract(mem_moderation_lAtts(array(
		'break'		=> br,
		'cols'		=> 58,
		'default'	=> '',
		'isError'	=> '',
		'label'		=> mem_moderation_gTxt('message'),
		'max'		=> 10000,
		'min'		=> 0,
		'name'		=> '',
		'required'	=> 1,
		'rows'		=> 8
	), $atts));

	$min = intval($min);
	$max = intval($max);
	$cols = intval($cols);
	$rows = intval($rows);

	if (empty($name)) $name = mem_moderation_label2name($label);

	if ($mem_moderation_submit)
	{
		$value = preg_replace('/^\s*[\r\n]/', '', rtrim(ps($name)));
		$utf8len = preg_match_all("/./su", ltrim($value), $utf8ar);
		$hlabel = htmlspecialchars($label);

		if (strlen(ltrim($value)))
		{
			if (!$utf8len)
			{
				$mem_moderation_error[] = mem_moderation_gTxt('invalid_utf8', array('{label}'=>$hlabel));
				$isError = "errorElement";
			}

			elseif ($min and $utf8len < $min)
			{
				$mem_moderation_error[] = mem_moderation_gTxt('min_warning', array('{label}'=>$hlabel, '{min}'=>$min));
				$isError = "errorElement";
			}

			elseif ($max and $utf8len > $max)
			{
				$mem_moderation_error[] = mem_moderation_gTxt('max_warning', array('{label}'=>$hlabel, '{max}'=>$max));
				$isError = "errorElement";
			}

			else
			{
				mem_moderation_store($name, $label, $value);
			}
		}

		elseif ($required)
		{
			$mem_moderation_error[] = mem_moderation_gTxt('field_missing', array('{label}'=>$hlabel));
			$isError = "errorElement";
		}
	}

	else
	{
		if (isset($mem_moderation_default[$name]))
			$value = $mem_moderation_default[$name];
		else
			$value = $default;
	}

	$memRequired = $required ? 'memRequired' : '';

	return '<label for="'.$name.'" class="memTextarea '.$memRequired.$isError.' '.$name.'">'.htmlspecialchars($label).'</label>'.$break.
		'<textarea id="'.$name.'" class="memTextarea '.$memRequired.$isError.'" name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'">'.htmlspecialchars($value).'</textarea>';
}

function mem_moderation_email($atts)
{
	global $mem_moderation_error, $mem_moderation_submit, $mem_moderation_from, $mem_moderation_recipient, $mem_moderation_default;

	extract(mem_moderation_lAtts(array(
		'default'	=> '',
		'isError'	=> '',
		'label'		=> mem_moderation_gTxt('email'),
		'max'		=> 100,
		'min'		=> 0,
		'name'		=> '',
		'required'	=> 1,
		'break'		=> br,
		'size'		=> '',
		'send_article'	=> 0
	), $atts));

	if (empty($name)) $name = mem_moderation_label2name($label);

	$email = $mem_moderation_submit ? trim(ps($name)) : $default;

	if (isset($mem_moderation_default[$name]))
		$email = $mem_moderation_default[$name];

	if ($mem_moderation_submit and strlen($email))
	{
		if (!is_valid_email($email))
		{
			$mem_moderation_error[] = mem_moderation_gTxt('invalid_email', array('{email}'=>htmlspecialchars($email)));
			$isError = "errorElement";
		}

		else
		{
			preg_match("/@(.+)$/", $email, $match);
			$domain = $match[1];

			if (is_callable('checkdnsrr') and checkdnsrr('textpattern.com.','A') and !checkdnsrr($domain.'.','MX') and !checkdnsrr($domain.'.','A'))
			{
				$mem_moderation_error[] = mem_moderation_gTxt('invalid_host', array('{domain}'=>htmlspecialchars($domain)));
				$isError = "errorElement";
			}

			else
			{
				if ($send_article) {
					$mem_moderation_recipient = $email;
				}
				else {
					$mem_moderation_from = $email;
				}
			}
		}
	}

	return mem_moderation_text(array(
		'default'	=> $email,
		'isError'	=> $isError,
		'label'		=> $label,
		'max'		=> $max,
		'min'		=> $min,
		'name'		=> $name,
		'required'	=> $required,
		'break'		=> $break,
		'size'		=> $size
	));
}

function mem_moderation_select_section($atts)
{
	extract(lAtts(array(
		'exclude'	=> '',
		'sort'		=> 'name ASC',
	),$atts,0));
	
	if (!empty($exclude)) {
		$exclusion = array_map('trim', split(',', preg_replace('/[\r\n\t\s]+/', ' ',$exclude)));

		if (count($exclusion))
			$exclusion = join(',', quote_list($exclusion));
	}

	$where = empty($exclusion) ? '1=1' : 'name NOT IN ('.$exclusion.')';
	
	$sort = empty($sort) ? '' : ' ORDER BY '. doSlash($sort);
	
	$rs = safe_rows('name, title','txp_section',$where . $sort);
	
	if ($rs) {
		foreach($rs as $r) {
			$items[] = $r['name'];
			$values[] = $r['title'];
		}	
	}
	
	unset($atts['exclude']);
	unset($atts['sort']);

	$atts['items'] = join(',', $items);
	$atts['values'] = join(',', $values);
	
	return mem_moderation_select($atts);
}

function mem_moderation_select_category($atts)
{
	extract(lAtts(array(
		'root'	=> 'root',
		'type'	=> 'article',
	),$atts,0));
	
	$rs = getTree($root, $type);
	
	$items = array();
	$values = array();

	if ($rs) {
		foreach ($rs as $cat) {
			$items[] = $cat['title'];
			$values[] = $cat['name'];			
		}
	}
	
	unset($atts['root']);
	unset($atts['type']);
	
	$atts['items'] = join(',', $items);
	$atts['values'] = join(',', $values);
	
	return mem_moderation_select($atts);
}

function mem_moderation_select($atts)
{
	global $mem_moderation_error, $mem_moderation_submit, $mem_moderation_default;

	extract(mem_moderation_lAtts(array(
		'name'		=> '',
		'break'		=> ' ',
		'delimiter'	=> ',',
		'isError'	=> '',
		'label'		=> mem_moderation_gTxt('option'),
		'items'		=> mem_moderation_gTxt('general_inquiry'),
		'values'	=> '',
		'required'	=> 1,
		'selected'	=> ''
	), $atts));

	if (empty($name)) $name = mem_moderation_label2name($label);
	
	if (!empty($items) && $items[0] == '<') $items = parse($items);
	if (!empty($values) && $values[0] == '<') $values = parse($values);

	$items = array_map('trim', split($delimiter, preg_replace('/[\r\n\t\s]+/', ' ',$items)));
	$values = array_map('trim', split($delimiter, preg_replace('/[\r\n\t\s]+/', ' ',$values)));

	if ($mem_moderation_submit)
	{
		$value = trim(ps($name));

		if (strlen($value))
		{
			if (in_array($value, $items))
			{
				mem_moderation_store($name, $label, $value);
			}

			else
			{
				$mem_moderation_error[] = mem_moderation_gTxt('invalid_value', array('{label}'=> htmlspecialchars($label), '{value}'=> htmlspecialchars($value)));
				$isError = "errorElement";
			}
		}

		elseif ($required)
		{
			$mem_moderation_error[] = mem_moderation_gTxt('field_missing', array('{label}'=> htmlspecialchars($label)));
			$isError = "errorElement";
		}
	}
	else
	{
		if (isset($mem_moderation_default[$name]))
			$value = $mem_moderation_default[$name];
		else
			$value = $selected;
	}

	$use_values_array = (count($items) == count($values));

	$out = '';

	foreach ($items as $item)
	{
		$v = $use_values_array ? array_shift($values) : $item;
		
		$out .= n.t.'<option'.($v == $value ? ' selected="selected">' : '>').(strlen($item) ? htmlspecialchars($item) : ' ').'</option>';
	}

	$memRequired = $required ? 'memRequired' : '';

	return '<label for="'.$name.'" class="memSelect '.$memRequired.$isError.' '.$name.'">'.htmlspecialchars($label).'</label>'.$break.
		n.'<select id="'.$name.'" name="'.$name.'" class="memSelect '.$memRequired.$isError.'">'.
			$out.
		n.'</select>';
}

function mem_moderation_checkbox($atts)
{
	global $mem_moderation_error, $mem_moderation_submit, $mem_moderation_default;

	extract(mem_moderation_lAtts(array(
		'break'		=> ' ',
		'checked'	=> 0,
		'isError'	=> '',
		'label'		=> mem_moderation_gTxt('checkbox'),
		'name'		=> '',
		'required'	=> 1
	), $atts));

	if (empty($name)) $name = mem_moderation_label2name($label);

	if ($mem_moderation_submit)
	{
		$value = (bool) ps($name);

		if ($required and !$value)
		{
			$mem_moderation_error[] = mem_moderation_gTxt('field_missing', array('{label}'=> htmlspecialchars($label)));
			$isError = "errorElement";
		}

		else
		{
			mem_moderation_store($name, $label, $value ? gTxt('yes') : gTxt('no'));
		}
	}

	else {
		if (isset($mem_moderation_default[$name]))
			$value = $mem_moderation_default[$name];
		else
			$value = $checked;
	}

	$memRequired = $required ? 'memRequired' : '';

	return '<input type="checkbox" id="'.$name.'" class="memCheckbox '.$memRequired.$isError.'" name="'.$name.'"'.
		($value ? ' checked="checked"' : '').' />'.$break.
		'<label for="'.$name.'" class="memCheckbox '.$memRequired.$isError.' '.$name.'">'.htmlspecialchars($label).'</label>';
}

function mem_moderation_serverinfo($atts)
{
	global $mem_moderation_submit;

	extract(mem_moderation_lAtts(array(
		'label'		=> '',
		'name'		=> ''
	), $atts));

	if (empty($name)) $name = mem_moderation_label2name($label);

	if (strlen($name) and $mem_moderation_submit)
	{
		if (!$label) $label = $name;
		mem_moderation_store($name, $label, serverSet($name));
	}
}

function mem_moderation_secret($atts, $thing = '')
{
	global $mem_moderation_submit;

	extract(mem_moderation_lAtts(array(
		'name'	=> '',
		'label'	=> mem_moderation_gTxt('secret'),
		'value'	=> ''
	), $atts));

	$name = mem_moderation_label2name($name ? $name : $label);

	if ($mem_moderation_submit)
	{
		if ($thing) $value = trim(parse($thing));
		mem_moderation_store($name, $label, $value);
	}

	return '';
}

function mem_moderation_radio($atts)
{
	global $mem_moderation_error, $mem_moderation_submit, $mem_moderation_values, $mem_moderation_default;

	extract(mem_moderation_lAtts(array(
		'break'		=> ' ',
		'checked'	=> 0,
		'group'		=> '',
		'label'		=> mem_moderation_gTxt('option'),
		'name'		=> ''
	), $atts));

	static $cur_name = '';
	static $cur_group = '';

	if (!$name and !$group and !$cur_name and !$cur_group) {
		$cur_group = mem_moderation_gTxt('radio');
		$cur_name = $cur_group;
	}
	if ($group and !$name and $group != $cur_group) $name = $group;

	if ($name) $cur_name = $name;
	else $name = $cur_name;

	if ($group) $cur_group = $group;
	else $group = $cur_group;

	$id   = 'q'.md5($name.'=>'.$label);
	$name = mem_moderation_label2name($name);

	if ($mem_moderation_submit)
	{
		$is_checked = (ps($name) == $id);

		if ($is_checked or $checked and !isset($mem_moderation_values[$name]))
		{
			mem_moderation_store($name, $group, $label);
		}
	}

	else
	{
		if (isset($mem_moderation_default[$name]))
			$is_checked = $mem_moderation_default[$name];
		else
			$is_checked = $checked;
	}

	return '<input value="'.$id.'" type="radio" id="'.$id.'" class="memRadio '.$name.'" name="'.$name.'"'.
		( $is_checked ? ' checked="checked" />' : ' />').$break.
		'<label for="'.$id.'" class="memRadio '.$name.'">'.htmlspecialchars($label).'</label>';
}

function mem_moderation_send_article($atts)
{
	if (!isset($_REQUEST['mem_moderation_send_article'])) {
		$linktext = (empty($atts['linktext'])) ? mem_moderation_gTxt('send_article') : $atts['linktext'];
		$join = (empty($_SERVER['QUERY_STRING'])) ? '?' : '&';
		$href = $_SERVER['REQUEST_URI'].$join.'mem_moderation_send_article=yes';
		return '<a href="'.htmlspecialchars($href).'">'.htmlspecialchars($linktext).'</a>';
	}
	return;
}

function mem_moderation_submit($atts, $thing)
{
	extract(mem_moderation_lAtts(array(
		'button'	=> 0,
		'label'		=> mem_moderation_gTxt('save'),
		'name'		=> 'mem_moderation_submit',
	), $atts));

	$label = htmlspecialchars($label);
	$name = htmlspecialchars($name);

	if ($button or strlen($thing))
	{
		return '<button type="submit" class="memSubmit" name="'.$name.'" value="'.$label.'">'.($thing ? trim(parse($thing)) : $label).'</button>';
	}
	else
	{
		return '<input type="submit" class="memSubmit" name="'.$name.'" value="'.$label.'" />';
	}
}

function mem_moderation_lAtts($arr, $atts)
{
	foreach(array('button', 'checked', 'required', 'show_input', 'show_error') as $key)
	{
		if (isset($atts[$key]))
		{
			$atts[$key] = ($atts[$key] === 'yes' or intval($atts[$key])) ? 1 : 0;
		}
	}
	if (isset($atts['break']) and $atts['break'] == 'br') $atts['break'] = '<br />';
	return lAtts($arr, $atts);
}

function mem_moderation_label2name($label)
{
	$label = trim($label);
	if (strlen($label) == 0) return 'invalid';
	if (strlen($label) <= 32 and preg_match('/^[a-zA-Z][A-Za-z0-9:_-]*$/', $label)) return $label;
	else return 'q'.md5($label);
}

function mem_moderation_store($name, $label, $value)
{
	global $mem_moderation_form, $mem_moderation_labels, $mem_moderation_values;
	$mem_moderation_form[$label] = $value;
	$mem_moderation_labels[$name] = $label;
	$mem_moderation_values[$name] = $value;
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


// -------------------------------------------------------------
// -------------------------------------------------------------
if (@txpinterface != 'admin') {
	$type = gps($mod_event);
	
	if (!empty($type)) {
		echo tag('Doing a moderation postback','div');

		$vars = get_moderation_variables($type);
		
		$data = gpsa($vars);

		exit();
	}
}


# --- END PLUGIN CODE ---

?>
