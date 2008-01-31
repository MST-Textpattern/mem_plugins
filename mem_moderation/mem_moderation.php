<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current file name.
// Uncomment and edit this line to override:
$plugin['name'] = 'mem_moderation';

$plugin['version'] = '0.6.1';
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


global $mod_event, $mem_moderation_lang, $prefs;
$mod_event = 'moderate';

add_privs($mod_event, '1');

require_plugin('mem_admin_parse');
// make sure ign is loaded first, if it exists
@load_plugin('ign_password_protect');
// might need access to ign user table
@load_plugin('mem_self_register');

// needed for MLP
define( 'MEM_MODERATION_PREFIX' , 'mem_moderation' );


if (!isset($prefs['mem_mod_notify_email'])) {
	set_pref('mem_mod_notify_email', '', 'mem_moderation', 1);
}
if (!isset($prefs['mem_mod_email_on_new'])) {
	set_pref('mem_mod_email_on_new', '', 'mem_moderation', 1, 'yesnoradio');
}

if (!is_array($mem_moderation_lang))
{
	$mem_moderation_lang = array(
		'new_submission_email'	=> "The user {user} has submitted a request of type {type} to the moderation queue.",
		'new_submission_email_subject'	=> "Moderation Queue",
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

if (@txpinterface == 'admin') {
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
			echo mem_moderation_preinstall();
		} else if ($step=='install') {
			echo mem_moderation_install();
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

	function mem_moderation_preinstall()
	{
		return mem_moderation_install();
	}

	function mem_moderation_install()
	{
		global $prefs;

		$create_default_forms = true;
		
		$log = array();
		
		ob_start();
/*		
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
*/
		$rs = safe_row("id", "txp_moderation", "1=1 LIMIT 1");

     	if (!$rs && mysql_errno() != 0) {
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
			
			if (($rs=safe_query($sql))) {
				$log[] = "Created moderation table ". PFX."txp_moderation";
			}
			else {
				$log[] = "Failed to create moderation table. " . mysql_error();
			}
		}
		else {
			$log[] = "Moderation table already exists";

			$rs = safe_row("item_id", "txp_moderation", "1=1 LIMIT 1");
	
			if (!$rs && mysql_errno() != 0) {
		     	if (safe_alter('txp_moderation', "ADD `item_id` INT NOT NULL DEFAULT '0' AFTER `type`"))
		     		$log[] = "Added item_id field to moderation table.";
		     	else
		     		$log[] = "Failed to add field 'item_id' to moderation table.";
			}
		}

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

		ob_end_clean();

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
function mod_item_id($atts) {
	global $mem_mod_info;
	return $mem_mod_info['item_id'];
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
	
		$res = mem_moderation_approver_callback($type,$decoded_data);
	
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
	
		$res = mem_moderation_rejecter_callback($type,$decoded_data);

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

		$out .= mem_moderation_presenter_callback($type, $decoded_data);

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
function submit_moderated_content($type,$email,$desc,$data,$item_id='0') 
{
	global $txp_user,$ign_user, $mem_mod_email_on_new, $mem_mod_notify_email, $sitename;

	$user = isset($ign_user) ? $ign_user : $txp_user;

	$type = doSlash($type);
	$email = doSlash($email);
	$desc = doSlash($desc);
	$item_id = doSlash($item_id);

	if ($item_id != 0)
	{
		$existing_modid = safe_field('id, item_id', 'txp_moderation', "`type` = '$type' and `user` = '$txp_user' and `item_id` = $item_id");
	
		if ($existing_modid)
		{
			// do an update instead
			if (update_moderated_content($existing_modid, $desc, $data, $type, $item_id))
				return $existing_modid;
			return false;
		}	
	}

	if (empty($email))
		$email = safe_field('email','txp_users',"name = '{$user}'");

	$ip = $_SERVER['REMOTE_ADDR'];
	$encoded_data = encode_content($data);


	$set = "`submitted` = now(),
			`type`	= '$type',
			`item_id`	= '$item_id',
			`user`	= '$user',
			`email`	= '$email',
			`ip`	= '$ip',
			`desc`	= '$desc',
			`data`	= '$encoded_data'";

	$r = safe_insert('txp_moderation',$set);

//	if ($existing_modid) safe_delete('txp_moderation', "`id` = $existing_modid");

	$mem_mod_email_on_new = true;
	$mem_mod_email = 'mmanfre@gmail.com';
	
	if ($mem_mod_email_on_new && !empty($mem_mod_notify_email))
	{
		$message = mem_moderation_gTxt('new_submission_email', array('{user}'=> $user, '{type}'=> $type));
		$reply = $from = $to = $mem_mod_notify_email;
		$subject = "[$sitename] " . mem_moderation_gTxt('new_submission_email_subject');
		
		$sent = mem_form_mail($from,$reply,$to,$subject,$message);
	}

	return $r;
}

// -------------------------------------------------------------
function update_moderated_content($id,$desc,$data,$type='',$item_id='') 
{
	$encoded_data = encode_content($data);
	$id = doSlash($id);
	$desc = doSlash($desc);

	$set = "`desc`	= '$desc',
			`data`	= '$encoded_data'";

	if (!empty($type))
		$set .= ", `type` = '$type'";
	if (!empty($item_id) or $item_id == '0')
		$set .= ", `item_id` = $item_id";

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
function mem_moderation_callback($type,$callback,$data) 
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
function mem_moderation_presenter_callback($type,$data) 
{
	return mem_moderation_callback($type,'presenter',$data);
}

// -------------------------------------------------------------
function mem_moderation_approver_callback($type,$data) 
{
	return mem_moderation_callback($type,'approver',$data);
}

// -------------------------------------------------------------
function mem_moderation_rejecter_callback($type,$data) 
{
	return mem_moderation_callback($type,'rejecter',$data);
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