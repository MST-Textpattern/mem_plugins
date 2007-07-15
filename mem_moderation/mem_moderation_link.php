<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_moderation_link';

$plugin['version'] = '0.1';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = '';
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
// Plugin mem_moderation_link
// Author: Michael Manfre (http://manfre.net/)
// Revisions: 
//
////////////////////////////////////////////////////////////
global $event;

require_plugin('mem_moderation');

$link_vars = array('id','date','category','url','linkname','linksort','description');
register_moderation_type('link',$link_vars,'link_moderate_presenter','link_moderate_approver','');

if (@txpinterface == 'admin') {
	register_callback('link_moderate','link_moderate','', 1);
	add_privs('moderate.link','1,2,3,4,5,6');

	if ($event=='link_moderate') {
		function link_moderate($event, $step) {
			$msg='';
	
			if ($step=='link_save' or $step=='link_post') {
				// save changes
				$msg = link_moderate_save($step);
			} else if ($step=='preinstall' or $step=='install') {

			} else {
				if ($step) {
					$msg = "Step is '$step'";
				}
			}
	
			pageTop('Link Moderation',$msg);
	
			echo link_moderate_form();
		}
	}
}

function link_moderate_form()
{
	global $step,$link_vars;

	if ($link_vars) extract(gpsa($link_vars));

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
	global $txpcfg,$txpac,$link_vars,$txp_user;
	$varray = gpsa($link_vars);

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


# --- END PLUGIN CODE ---

?>
