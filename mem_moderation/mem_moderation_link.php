<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_moderation_link';

$plugin['version'] = '0.2';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Moderation plugin that allows links to be submitted to the moderation queue.';
$plugin['type'] = '1'; // 0 for regular plugin; 1 if it includes admin-side code

@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h1(title). mem_moderation_link plugin

h2(section summary). Summary

p. This moderation plugin allows users to submit links to the moderation queue.

h2(section contact). Author Contact

"Michael Manfre":mailto:mmanfre@gmail.com?subject=Textpattern%20mem_moderation_link%20plugin
"http://manfre.net":http://manfre.net

h2(section license). License

p. This plugin is licensed under the "GPLv2":http://www.fsf.org/licensing/licenses/info/GPLv2.html.

h2(section installation). Installation

p. "Start Install Wizard":./index.php?event=link_moderate&step=preinstall

p. Installs two forms: mod_link_submit_form and mod_link_success.

h2(section tags). Tags

h3(tag#mem_moderation_link_form). mem_moderation_link_form

p(tag-summary). mem_form helper tag for link submission/edit form. 

p. Takes the same attributes as mem_form. See mem_form for tag attributes and usage.

p. Example: @<txp:mem_moderation_link_form form="mod_link_submit_form" thanks_form="mod_link_success" label="Link Details" />@

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

////////////////////////////////////////////////////////////
// Plugin mem_moderation_link
// Author: Michael Manfre (http://manfre.net/)
// Revisions: 
//
////////////////////////////////////////////////////////////
global $event, $step, $mem_link_vars, $mem_link_delete_vars;

require_plugin('mem_moderation');

// link/link-edit vars
$mem_link_vars = array('note','user','email','modid','id','linkid','date','category','url','linkname','linksort','description');
// link-delete vars
$mem_link_delete_vars = array('user','email','linkid','title');



if (@txpinterface == 'admin') {

	// give publisher install access
	add_privs('moderate.link','1');
//	add_privs('moderate.link','1,2,3,4,5,6');

	// register event with txp
	register_callback('link_moderate','link_moderate','', 1);

	// wire up moderation types
	mem_moderation_register('link',$mem_link_vars,'link_moderate_presenter','link_moderate_approver','');
	mem_moderation_register('link-edit',$mem_link_vars,'link_moderate_presenter','link_moderate_approver','');
	mem_moderation_register('link-delete',$mem_link_vars,'link_moderate_presenter','link_moderate_approver','');


	if ($event=='link_moderate') 
	{
		/* Event handler */
		function link_moderate($event, $step) 
		{
			$msg='';
	
			if ($step=='link_save' or $step=='link_post') 
			{
				// save changes
				$msg = link_moderate_save($step);
			}
			else if ($step=='preinstall' or $step=='install') 
			{
				// need tab during install
				register_tab('extensions','link_moderate','link_moderate');
				echo pageTop('Link Moderation','');

				echo link_install();
				
				return;
			} 
			else 
			{
				if ($step) 
				{
					// $msg = "Step is '$step'";
					trigger_error(gTxt('unknown_step'));
				}
			}
	
			pageTop('Link Moderation',$msg);
	
			echo link_moderate_form();
		}

		/** Install default forms, prefs, etc. */
		function link_install() 
		{
			$log = array();

			// default article edit form
			$form = fetch('Form','txp_form','name','mod_link_submit_form');
			if (!$form) 
			{
				$form_html = <<<EOF
<div><txp:mem_form_text name="linkname" required="1" label="Link Title" break="" /></div>
<div><txp:mem_form_text name="url" required="1" label="URL (incl. http://)" break="" /></div>
<div><txp:mem_form_textarea name="description" required="1" label="Description" break="" /></div>

<div><txp:mem_form_textarea name="note" class="txtarea_medium" label="Notes for the Moderator (optional)" break="" required="0" /></div>

<txp:mem_form_submit button="1" name="action"><span>Submit Link suggestion</span></txp:mem_form_submit>
EOF;
				$form_html = doSlash($form_html);
				if (safe_insert('txp_form',"name='mod_link_submit_form',type='misc',Form='{$form_html}'"))
					$log[] = "Created form 'mod_link_submit_form'";
				else
					$log[] = "Failed to create form 'mod_link_submit_form'. " . mysql_error();
			}
			else
			{
				$log[] = "Form 'mod_link_submit_form' already exists. Skipping installation of default form.";
			}

			// default article success form
			$form = fetch('Form','txp_form','name','mod_link_success');
			if (!$form) 
			{
				$form_html = <<<EOF
<txp:mem_if_step name="link_delete">
	<h2>Link deleted</h2>
	<p>Your submission has been deleted and will not be posted on this site.</p>
<txp:else />
	<h2>Link submitted</h2>
	<p>Thank you for submitting your link. It will be reviewed by a moderator before being posted to the site.</p>
</txp:mem_if_step>
EOF;
				$form_html = doSlash($form_html);
				if (safe_insert('txp_form',"name='mod_link_success',type='misc',Form='{$form_html}'"))
					$log[] = "Created form 'mod_link_success'";
				else
					$log[] = "Failed to create form 'mod_link_success'. " . mysql_error();
			}
			else
			{
				$log[] = "Form 'mod_link_success' already exists. Skipping installation of default form.";
			}

			return tag("Install Log",'h2').doWrap($log,'ul','li');
		}
	} // if link_moderate
} // if admin

// -------------------------------------------------------------
function link_moderate_form()
{
	global $step,$mem_link_vars;

	if ($mem_link_vars) extract(gpsa($mem_link_vars));

	if($id && $step=='link_edit') {
		extract(safe_row("*", "txp_link", "id = $id"));
	}

	if ($step=='link_save' or $step=='link_post'){
		foreach($vars as $var) {
			$$var = '';
		}
	}

	$textarea = '<textarea name="description" cols="40" rows="7" tabindex="4">'.
		$description.'</textarea>';
	$selects = event_category_popup("link", $category);
	$editlink = ''; //' ['.eLink('category','list','','',gTxt('edit')).']';

	$out =
		startTable( 'edit' ) .
			tr( fLabelCell( 'title' ) . fInputCell( 'linkname', $linkname, 1, 30 )) .
			tr( fLabelCell( 'sort_value') .fInputCell( 'linksort', $linksort, 2, 15 )) .
			tr( fLabelCell( 'url','link_url') . fInputCell( 'url', $url, 3, 30) ) .
			tr( fLabelCell( 'link_category', 'link_category' ) . td( $selects . $editlink ) ) .
			tr( fLabelCell( 'description', 'link_description' ) . tda( $textarea, ' valign="top"' ) ) .
			tr( td() . td( fInput( "submit", '', gTxt( 'save' ), "publish" ) ) ) .
		endTable() .

		eInput( 'link_moderate' ) . sInput( ( $step=='link_edit' ) ? 'link_save' : 'link_post' ) .
		hInput( 'id', $id );

	return form( $out );
}

// -------------------------------------------------------------
function link_moderate_save($step)
{
	global $txpcfg,$txpac,$mem_link_vars,$txp_user;
	$varray = gpsa($mem_link_vars);

	if($txpac['textile_links']) {

		include_once $txpcfg['txpath'].'/lib/classTextile.php';
		$textile = new Textile();

		$varray['description'] = $textile->TextileThis($varray['description'],1);
	}

	if (!$varray['linksort']) $varray['linksort'] = $varray['linkname'];

	$res = '';

	if ($step=='link_edit') {
		if (update_moderated_content($id,$varray['description'],$varray)) {
			$res = "Updated link '$id'";
		} else {
			$res = "Failed to update link '$id'";
		}
	} else {
		$email = safe_field('email','txp_users',"name='$txp_user'");

		if ($email and ($res=submit_moderated_content('link',$email,$varray['description'],$varray))) {
			$res = "Submitted link '$res'";
		} else {
			$res = "Failed to submit link";
		}
	}

	return $res;
}

function link_moderate_presenter($type,$data) {
	if ($type=='link') {
		$description='';
		$linkname='';
		$linksort='';
		$category='';
		$url='';
		if (is_array($data)) extract($data);

		$selects = event_category_popup("link", $category);
		$textarea = '<textarea name="description" cols="40" rows="7" tabindex="4">'.$description.'</textarea>';

		return startTable( 'edit' ) .
				tr( fLabelCell( 'title' ) . fInputCell( 'linkname', $linkname, 1, 30 )) .
				tr( fLabelCell( 'sort_value') .fInputCell( 'linksort', $linksort, 2, 15 )) .
				tr( fLabelCell( 'url','link_url') . fInputCell( 'url', $url, 3, 30) ) .
				((!empty($url) and !empty($linkname)) ? tr( fLabelCell( 'url' ) . td( href($linkname,$url) ) ) : '' ).
				tr( fLabelCell( 'link_category', 'link_category' ) . td( $selects ) ) .
				tr( fLabelCell( 'description', 'link_description' ) . tda( $textarea, ' valign="top"' ) );
			endTable();
	}

	return '';
}

function link_moderate_approver($type,$data)
{
	if ($type=='link' && is_array($data)) {
		extract($data);

		$q = safe_insert("txp_link",
		   "category    = '$category',
			date        = now(),
			url         = '".trim($url)."',
			linkname    = '$linkname',
			linksort    = '$linksort',
			description = '$description'"
		);
		
		if (!$q)
			return 'Failed to approve link';
	}
}

//////////////////////////////////////////////////////
// Public form methods

/** Helper tag for mem_form */
function mem_moderation_link_form($atts, $thing='')
{
	$atts['type'] = 'mem_moderation_link';
	
	return mem_form($atts, $thing);
}

// register functions with mem_form
register_callback('mem_mod_link_form_defaults', 'mem_form.defaults');
register_callback('mem_mod_link_form_display', 'mem_form.display');
register_callback('mem_mod_link_form_submitted', 'mem_form.submit');

/** Set form defaults */
function mem_mod_link_form_defaults()
{
	global $mem_form, $mem_form_type, $mem_form_default, $mem_mod_info, $mem_modlink_info;

	// type check
	if ($mem_form_type!='mem_moderation_link')
	{
		return;
	}
	
	extract(gpsa(array('modid','linkid')));
	
	// editing mod item
	if (!empty($modid))
	{
		// get mod data	
		$mem_mod_info = safe_row('*','txp_moderation',"`id`='".doSlash($modid)."'");
	
		if ($mem_mod_info)
		{
			// set decoded
			$mem_modlink_info = mem_moderation_decode($mem_mod_info['data']);
		}
	}
	// editing link 
	else if (!empty($linkid))
	{
		$rs = safe_row('*', 'txp_link',"`id`='".doSlash($linkid)."'");

		// set mod data
		$mem_modlink_info = array();
		foreach($rs as $k => $v)
		{
			$mem_modlink_info[strtolower($k)] = $v;
		}
	}

	if (is_array($mem_modlink_info))
	{
		// set defaults
		foreach($mem_modlink_info as $key => $val)
		{
			mem_form_default($key, $val);
		}
	}
}

function mem_mod_link_form_display()
{
	global $mem_form_type, $mem_form_labels, $mem_form_values, $mem_mod_info, $mem_modlink_info;

	// type check
	if ($mem_form_type!='mem_moderation_link')
	{
		return;
	}
	
	$out = '';

	if (isset($mem_mod_info))
	{
		$out .= n.'<input type="hidden" name="modid" value="'.htmlspecialchars($mem_mod_info['id']).'" />'.
			n.'<input type="hidden" name="type" value="'. htmlspecialchars($mem_mod_info['type']).'" />';

		if ($mem_mod_info['type'] == 'link-edit' && isset($mem_modlink_info['linkid']))
			$out .= n.'<input type="hidden" name="linkid" value="'.$mem_modlink_info['linkid'].'" />';
		
		mem_form_store('modid', 'modid', $mem_mod_info['id']);
		mem_form_store('type', 'type', $mem_mod_info['type']);
	}
	else if (isset($mem_modlink_info))
	{
		$out .= n.'<input type="hidden" name="linkid" value="'.htmlspecialchars($mem_modlink_info['linkid']).'" />'.
			n.'<input type="hidden" name="type" value="link" />';
	}
	
	return $out;
}

/** Form submit handler */
function mem_mod_link_form_submitted()
{
	global $prefs, $mem_link_vars, $mem_form_type, $txp_user, $ign_user, $mem_mod_info, $mem_modlink_info, $mem_form_thanks_form;
	
	if ($mem_form_type!='mem_moderation_link')
	{
		return;
	}
		
	extract(gpsa(array('modid','step','id','linkid')));

	$mem_modlink_info = gpsa($mem_link_vars);
	$out = '';
	
	$is_save = ps('mem_moderation_save');
	$is_delete = ps('mem_moderation_delete');
	$is_update = ps('mem_moderation_update') || ($is_save && ps('modid'));

	if (isset($ign_user)) $txp_user = $ign_user;

	if (!empty($modid)) $id = $modid;

	
	if ($is_delete)
	{
		if (remove_moderated_content($modid))
		{
			$res = mem_moderation_gTxt('link_deleted');
		}
		else
		{
			$res = mem_moderation_gTxt('link_delete_failed');
		}
	} 
	elseif (!empty($linkid))
	{
		$linkid = doSlash($linkid);
		$rs = safe_rows("*, unix_timestamp(date) as udate","txp_link","`id` = $linkid");

		if ($rs)
		{
			// merge the passed in values to the existing
			foreach($mem_modlink_info as $key => $val)
			{
				$rs[$key] = $val;
			}
			$rs['linkid'] = $linkid;
			
			if ($is_update) 
			{
				$res = update_moderated_content($id, $mem_modlink_info['note'], $mem_modlink_info);
			}
			else
			{
				$res = submit_moderated_content('link-edit', $user_email, $mem_modlink_info['note'], $mem_modlink_info, $linkid);
				
				if ($res)
				{
					// delete all other pending link moderation actions by this user for the same item_id
					safe_delete('txp_moderation', "`type` LIKE 'link%' and user = '$txp_user' and item_id = $linkid and id != $res and item_id != 0");
				}
			}
		} // if $rs
	}
	else 
	{		
		if (isset($id)) $mem_modlink_info['id'] = $id;

		if ($is_update)
		{	
			if (isset($mem_modlink_info['note']))
			{
				$note = $mem_modlink_info['note'];
			}
			else
			{
				$note = $mem_mod_info['desc'];
			}
	
			if (update_moderated_content($id,$note,$mem_modlink_info))
			{
				$res = mem_moderation_gTxt('link_updated');
			}
			else
			{
				$res = mem_moderation_gTxt('link_update_failed');
			}
		}
		else
		{
			if (!isset($user))
			{
				$mem_modlink_info['user'] = $txp_user;
			}
				
			$res = submit_moderated_content('link', '', $mem_modlink_info['note'], $mem_modlink_info);
		}
	}
	
	$mem_modlink_info['result'] = $res;
	
	$thanks_form = @fetch_form($mem_form_thanks_form);
	
	if (!empty($thanks_form))
	{
		$out = parse($thanks_form);
	}
	
	// cleanup global
	unset($mem_modlink_info);

	return $out;
}


# --- END PLUGIN CODE ---

?>
