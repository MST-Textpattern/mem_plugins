<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_moderation_image';

$plugin['version'] = '0.4.9';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Moderation plugin that allows user to submit images.';
$plugin['type'] = 1; // 0 for regular plugin; 1 if it includes admin-side code

@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h1. Image Moderation

p. This moderation plugin allows users to submit images in to the moderation queue. If the plugin mem_moderation_article is installed, then these images can be wrapped in a new article.

h1. Installation

p. This plugin requires plugins mem_moderation and mem_admin_parse to be installed and enabled to function.

p. "Start Install Wizard":index.php?event=image_moderate&step=preinstall


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
global $event,$extensions;
global $path_to_site,$img_dir;

require_plugin('mem_moderation');
require_plugin('mem_admin_parse');

global $image_vars;
$image_vars = array('id','name','category','ext','w','h','alt','caption','date','author','tmpfile','tmpfilename','date_taken','keywords','note');

if (mem_get_pref('mod_image_wrap_with_article','val')) {
	require_plugin('mem_moderation_article');
}

$image_vars = array_merge($image_vars, array('article_section', 'article_title',  'article_title_html', 'article_body', 'article_body_html', 'article_excerpt', 'article_excerpt_html', 'article_textile_excerpt', 'article_image', 'article_textile_body', 'article_keywords', 'article_status', 'article_category1', 'article_category2', 'article_annotate', 'article_annotateinvite', 'article_override_form', 'article_custom_1', 'article_custom_2', 'article_custom_3', 'article_custom_4', 'article_custom_5', 'article_custom_6', 'article_custom_7', 'article_custom_8', 'article_custom_9', 'article_custom_10', 'article_wrap_enabled') );


register_moderation_type('image',$image_vars,'image_presenter','image_approver','image_rejecter');

//--------------------------------------------------------------------
if (@txpinterface!='admin' || (@txpinterface=='admin' and ($event=='moderate' or $event=='image_moderate')))
{
	require_once txpath.'/include/txp_image.php';
	include_once txpath.'/lib/class.thumb.php';
}

//--------------------------------------------------------------------
if (@txpinterface == 'admin') {

	register_callback('image_moderate','image_moderate','', 1);
	add_privs('moderation.image','1,2,3,4,5,6');
	
	if ($event == 'image_moderate' or $event == 'moderate') {

		function image_uninstall() {
			$log = array();
			
			if (safe_delete('txp_prefs',"name LIKE 'mod_image_%'"))
				$log[] = 'Deleted all image moderation preferences';
			else
				$log[] = 'Failed to delete image moderation preferences';
			
			return tag("UnInstall Log",'h2').doWrap($log,'ul','li');
		}

		function image_preinstall() {
			return image_install();
		}
		
		function image_install() {
			$log = array();
			
			// remove old values
			safe_delete('txp_prefs',"name LIKE 'modimg_%'");
			
			if (mem_get_pref('mod_image_wrap_with_article','val')===false) {
				mem_set_pref('mod_image_wrap_with_article','0','Moderation','1',0,'yesnoradio');
				$log[] = 'Added preference mod_image_wrap_with_article';
			}
			if (mem_get_pref('mod_image_size_full','val')===false) {
				mem_set_pref('mod_image_size_full','640x480','Moderation','1');
				$log[] = 'Added preference mod_image_size_full';
			}
			if (mem_get_pref('mod_image_wrap_default','val')===false) {
				mem_set_pref('mod_image_wrap_default','0','Moderation','1',0,'yesnoradio');
				$log[] = 'Added preference mod_image_wrap_default';
			}
			if (mem_get_pref('mod_image_size_thumbnail','val')===false) {
				mem_set_pref('mod_image_size_thumbnail','160x120','Moderation','1');
				$log[] = 'Added preference mod_image_size_thumbnail';
			}
			if (mem_get_pref('mod_image_thumbnail_crop','val')===false) {
				mem_set_pref('mod_image_thumbnail_crop','0','Moderation','1',0,'yesnoradio');
				$log[] = 'Added preference mod_image_thumbnail_crop';
			}

			if (mem_get_pref('mod_image_sharpen','val')===false) {
				mem_set_pref('mod_image_sharpen','0','Moderation','1',0,'yesnoradio');
				$log[] = 'Added preference mod_image_sharpen';
			}

			if (mem_get_pref('mod_image_quality','val')===false) {
				mem_set_pref('mod_image_quality','80','Moderation','1');
				$log[] = 'Added preference mod_image_quality';
			}

			
			return tag("Install Log",'h2').doWrap($log,'ul','li');
		}

		function image_moderate($event, $step) {
			$msg='';

			if ($step=='image_save' or $step=='image_update') {
				// save changes
				$msg = mem_image_save($step);
			} else if ($step=='preinstall' || $step=='install') {
				register_tab('extensions','image_moderate','image_moderate');
				echo pageTop('Image Moderation','');
				
				if ($step=='preinstall')
					echo image_preinstall();
				else
					echo image_install();
				
				return;
			} else if ($step=='uninstall') {
				register_tab('extensions','image_moderate','image_moderate');
				echo pageTop('Image Moderation','');
				echo image_uninstall();
				return;
			} else {
				register_tab('extensions','image_moderate','image_moderate');
				if ($step) {
					$msg = "Step is '$step'";
				}
			}
	
			pageTop('Image Moderation',$msg);
		}
	}
}


function image_moderate_form()
{
	global $step;

	$data = gpsa(array('alt','category','caption','author','name','ext','tmpfile','keywords','photographer','date_taken','title','note'));

	return image_presenter('image',$data);
}

function modimg_form($atts,$thing='')
{
	global $step,$txp_user,$mem_mod_info,$mem_modimg_info,$image_vars;


	extract(lAtts(array(
		'isize'		=> 25,
		'form'		=> '',
		'successform'	=> '',
		'maxfilesize' => '',
		'accept' => ''
	),$atts));

	extract(gpsa(array('modid','action','event','step')));

	extract(gpsa($image_vars));

	if (empty($author))
		$author = $txp_user;

	$out = '';

	if (isset($action) and $action==gTxt( 'delete' ))
		$step = 'image_delete';

	if (isset($step) and ($step=='image_save' or $step=='image_update' or $step=='image_delete')) {
		if (!empty($successform))
			$Form = fetch_form($successform);

		if ($step=='image_delete') {
			if (remove_moderated_content($modid)) {
				// delete the file
				image_rejecter('image',array('tmpfile'=>$tmpfile));
				$msg = 'Deleted image';
			} else {
				$msg = 'Failed to delete image';
			}
		} else {
			$msg = mem_image_save($step);
		}

		$out = admin_parse($Form);
		
		unset($mem_modimg_info);
	} else {

		if (empty($form))
			$Form = $thing;
		else
			$Form = fetch_form($form);


		$action_url = $_SERVER['REQUEST_URI'];
		$qs = strpos($action_url,'?');
		if ($qs) $action_url = substr($action_url, 0, $qs);

		$accept = (!empty($accept) ? ' accept="'.$accept.'"' : '');

		$out =	n.n."<form enctype='multipart/form-data' action='{$action_url}' method='post'{$accept}>" .
				(!empty($maxfilesize) && is_integer($maxfilesize) ? hInput('MAX_FILE_SIZE', $maxfilesize) : '') .
				eInput('image_moderate') .
				sInput( $step=='image_edit' ? 'image_update' : 'image_save' );
		
		if (isset($modid) and !empty($modid)) {
			$out .= hInput('modid',$modid);

			$mem_mod_info = safe_row('*','txp_moderation',"`id`='".doSlash($modid)."'");

			extract(decode_content($mem_mod_info['data']));

			$out .= @hInput('tmpfilename',$tmpfilename);
			
			$out .= @hInput('mem_modimg_info_data',$mem_mod_info['data']);
		}

		$caption_area = '<textarea name="caption" cols="40" rows="7">'.$caption.'</textarea>';
		$note_area = '<textarea name="note" cols="40" rows="4">'.@$mem_mod_info['desc'].'</textarea>';
		$selects = event_category_popup("image", $category);
		$selects_cat1 = category_popup("article_category1", @$article_category1);
		$selects_cat2 = category_popup("article_category2", @$article_category2);
		$keywords_area = '<textarea name="article_keywords" class="mod_keywords_input">' . @$article_keywords . '</textarea>';
		
		$vals = array(
			'file_input'		=> (empty($tmpfilename)?fInput('file','thefile','','edit') : hInput('tmpfile',$tmpfile) . $tmpfilename ),
			'title_input'		=> fInput('text','alt',$alt, $isize,'modimg_title_input',"0"),
			'category_input'	=> $selects,
			'caption_input'		=> $caption_area,
			'note_input'		=> $note_area,
			'author_input'		=> fInput('text','author',@$author,$isize,'modimg_author_input',"0"),
			'article_author'	=> fInput('text','article_author',@$articl_author,$isize,'modimg_author_input',"0"),
			'article_category1'	=> $selects_cat1,
			'article_category2'	=> $selects_cat2,
			'article_title'		=> fInput('text','article_title',@$article_title,$isize,'modimg_article_title',"0"),
			'article_keywords'	=> $keywords_area,
			'submit'			=> fInput( "submit", 'action', gTxt( 'save' ), "publish" ),
			'delete'			=> (!empty($modid) ? fInput( "submit", 'action', gTxt( 'delete' ), "delete", '', "return confirm('". gTxt('are_you_sure') ."')" ) : ''),
		);
		for($i=1;$i<=10;$i++) {
			$k = "article_custom_{$i}";
			$vals[$k] = fInput('text', $k, @$$k, $isize,'modimg_'.$k,"0");
		}		
	
		foreach ($vals as $a=>$b) {
			$a = '<txp:modimg_'.$a.' />';
			$Form = str_replace($a,$b,$Form);
		}
		
		$out .= $Form;
	
		$out .= '</form>'.n;
		
		unset($mem_mod_info);
	}

	return $out;		
}


// -------------------------------------------------------------
function modimg_get_image_dimensions($thumbnail=false)
{
	if ($thumbnail) {
		$dim = mem_get_pref('mod_image_size_thumbnail','val');
	
		$w = 160;
		$h = 120;
	} else {
		$dim = mem_get_pref('mod_image_size_full','val');
	
		$w = 640;
		$h = 480;
	}	
	
	if ($dim)
	{
		$dim = explode('x',$dim);
		
		if (count($dim) == 2)
		{
			$w = $dim[0];
			$h = $dim[1];
		}
	}
	
	$z = array($w,$h);

	return $z;
}

// -------------------------------------------------------------
function mem_image_save($step)
{
	ini_set('memory_limit', '32M');
	global $txpcfg,$txpac,$extensions,$path_to_site,$txp_user,$ign_user,$mem_modimg_info, $image_vars;

	$args = gpsa($image_vars);
	
	$inf=gps('mem_modimg_info_data');
	
	if (!empty($inf))
	{
		$varray = lAtts(decode_content($inf), $args);
	}
	else
	{
		$varray = $args;
	}

	$modid = gps('modid');
	if (!empty($modid)) $varray['id'] = $id = $modid;

	if (isset($ign_user)) $txp_user = $ign_user;

	if($txpac['textile_links']) {

		include_once $txpcfg['txpath'].'/lib/classTextile.php';
		$textile = new Textile();

		$varray['caption'] = $textile->TextileThis($varray['caption'],1);
		
		unset($textile);
	}

	$res = '';

	if ($step=='image_update') {
		if (update_moderated_content($id,$varray['note'],$varray)) 
		{
			$res = "Update link '$modid'";
		} else {
			$res = "Failed to update link '$modid'";
		}
	} 
	else 
	{
		$file = $_FILES['thefile']['tmp_name'];
		$name = $_FILES['thefile']['name'];

		$file = get_uploaded_file($file);

		$varray['tmpfilename'] = 'mod_' . basename($file) . $_FILES['thefile']['name'];

		$pending_file = $path_to_site . '/images/' . 'mod_' . basename($file) . $_FILES['thefile']['name'];

		list($w,$h,$extension) = getimagesize($file);

		if ($extensions[$extension]) 
		{
			list($width,$height) = modimg_get_image_dimensions();

			if (class_exists('wet_thumb'))
			{
				$sharpen = mem_get_pref('mod_image_sharpen','val');
				if ($sharpen == 1 || $sharpen === true)
					$sharpen = true;
				else
					$sharpen = false;
				
				$quality = mem_get_pref('mod_image_quality','val');
				if ($quality === false || empty($quality) || !is_numeric($quality) || ($quality < 0 || $quality > 100))
					$quality = 80;

				$t = new wet_thumb();
				
				// remove icon
				$t->hint = false;
				$t->crop = false;
				$t->addgreytohint = false;
				$t->quality = $quality;
				$t->sharpen = false; //$sharpen;
				
				if ($w > $h)
					$t->width = $width;
				else
					$t->height = $height;

				if ($t->write($file,$file.'.resized'))
				{
					unlink($file);
					$file = $file.'.resized';
					
					$w = $t->width;
					$h = $t->height;
				}

				unset($t);
			}
			
			if (shift_uploaded_file($file, $pending_file) != false) {
				$varray['w'] = $w;
				$varray['h'] = $h;

				$varray['ext'] = $extensions[$extension];
				$varray['name'] = substr($name,0,strrpos($name,'.')) . $extension;
				$varray['tmpfile'] = $pending_file;
				$varray['user'] = $txp_user;
				$email = safe_field('email','txp_users',"name='".doSlash($txp_user)."'");

				$res = submit_moderated_content('image',$email,$varray['note'],$varray);
				
				if ($res) {
					$res = ''; //"Submitted Image '$res'";
				} else {
					$res = "Failed to submit image";
				}
			} else {
				$res = "Failed to upload image";
			}
		} else {
			$res = "'{$extension}' is an unapproved extension";
		}
	}

	
	if (empty($res))
		$mem_modimg_info = $varray;

	return $res;
}

//--------------------------------------------------------------------
//--------------------------------------------------------------------
function modimg_img($atts) {
	global $mem_modimg_info,$img_dir,$mem_mod_info;

	if (is_array($mem_modimg_info))
		extract($mem_modimg_info);
	else if (is_array($mem_mod_info) and array_key_exists('data',$mem_mod_info))
		extract(decode_content($mem_mod_info['data']));

	if (isset($tmpfilename) and !empty($tmpfilename))
		return '<img src="'.hu.$img_dir.'/'.$tmpfilename.'"'.
				(!empty($w) ? ' width="'.$w.'"':'') . 
				(!empty($h) ? ' height="'.$h.'"':'') . ' />';
}


function image_presenter($type,$data) {
	global $img_dir,$image_vars;
	
	$create_article = mem_get_pref('mod_image_wrap_with_article','val');
	$wrap_default = mem_get_pref('mod_image_wrap_default','val');

	$out = '';

	if ($type=='image' and is_array($data)) {
		extract(get_prefs());
		
		if (empty($data['article_annotate'])) $data['article_annotate'] = $comments_on_default;

		if (empty($data['article_title'])) $data['article_title'] = @$data['alt'];
		if (empty($data['article_body'])) $data['article_body'] = @$data['caption'];
		if (empty($data['article_user'])) $data['article_user'] = @$data['author'];
		if (empty($data['article_warp_enabled'])) $data['article_wrap_enabled'] = $wrap_default;

		extract($data);

		if (!$article_annotate) {
			$article_annotate = $comments_on_default;
		}
		
		if (empty($article_annotateinvite))
			$article_annotateinvite = $comments_default_invite;

		$selects = event_category_popup("image", @$category);
		$textarea = '<textarea name="caption" cols="40" rows="7" tabindex="4">'.htmlspecialchars(@$caption).'</textarea>';

		$imgtag = '<img src="'.hu.$img_dir.'/'.$tmpfilename.'"'.' width="'.$w.'" height="'.$h.'" />';

		$out = startTable( 'edit' ) .
				tr( tdcs( $imgtag, 2) ) .
				tr( fLabelCell( 'name' ) . fInputCell( 'name', $name, 1, 30 )) .
				tr( fLabelCell( 'alt') .fInputCell( 'alt', @$alt, 2, 15 )) .
				tr( fLabelCell( 'image_category', 'category' ) . td( $selects ) ) .
				tr( fLabelCell( 'image_dimensions' ) . td( $w . 'x' . $h ) ) .
				tr( fLabelCell( 'caption' ) . tda( $textarea, ' valign="top"' ) ) .
				($create_article ? tr( fLabelCell( 'article_wrap' ) . tda( yesnoRadio('article_wrap_enabled', $article_wrap_enabled) )) : '') .				
				endTable();


			if (empty($section)) $section = getDefaultSection();
			$rs = safe_column("name", "txp_section", "name!='default'");

			$section_select = ($rs) ? selectInput("article_section", $rs, @$article_section) : '';
			
			$out .= '<h3><a href="#"'.gTxt('article').'</h3>';

			$out .= '<table cellpadding="3" cellspacing="0" border="0" id="edit2" align="center" '.
					(!($create_article && $article_wrap_enabled) ? 'style="display:none;visibility:hidden;"':'').'>' .
					tr( tdcs( '<h3>'.gTxt('article') .'</h3>', 2) ) .
					tr( fLabelCell( 'title' ) . fInputCell( 'article_title', $article_title, 1, 30 )) .
					(empty($section_select) ? '' : tr( fLabelCell( 'section' ) . tda( $section_select ) )) .
					tr( fLabelCell( 'categorize' ) . tda( category_popup("article_category1", @$article_category1) .br. category_popup("article_category2", @$article_category2) ) ) .
					tr( fLabelCell( 'status' ) . tda( selectInput('article_status', array(1=>'Draft',2=>'Hidden',3=>'Pending',4=>'Live',5=>'Sticky'),4) ) ) .
					tr( fLabelCell( 'body' ) . tda( '<textarea name="article_body" cols="40" rows="7">'.htmlspecialchars($article_body).'</textarea>', ' valign="top"' ) ) .
					tr( fLabelCell( 'annotate' ) . tda( yesnoRadio('article_annotate',$article_annotate) ) ) .
					tr( fLabelCell( 'annotateinvite' ) . fInputCell( 'article_annotateinvite', $article_annotateinvite, 1, 30 )) .
					tr( fLabelCell( 'keywords' ) . tda( '<textarea name="article_keywords" cols="40" rows="3">'.htmlspecialchars(@$article_keywords).'</textarea>', ' valign="top"' ) ) .
					tr( fLabelCell( 'override_default_form' ) . tda(form_pop(@$article_override_form).popHelp('override_form'))) .
					tr( fLabelCell( 'author' ) . fInputCell( 'article_user', $article_user, 1, 30 ));

					'';

			for($i=1;$i<=10;$i++) {
				$k = "article_custom_{$i}";
				$kset = "custom_{$i}_set";
				if (isset($$kset) and !empty($$kset))
					$out .= tr(	fLabelCell( @$$kset ) . fInputCell( $k, @$$k, 1, 30 ) );
			}

		$out .= endTable();
		$out .= hInput('tmpfilename',$tmpfilename);
		$out .= hInput('author', $user);
		$out .= hInput('w',$w);
		$out .= hInput('h',$h);

	}

	return $out;
}

function image_approver($type,$data)
{
	if ($type=='image' && is_array($data)) {
		extract($data);
		
		$rs_trans = safe_query("START TRANSACTION");
		
		// insert record
		$q = safe_insert("txp_image",
		   "name	    = '".doSlash($name)."',
			category    = '".doSlash($category)."',
			ext			= '$ext',"
			.(!empty($w) ?"w			= $w," : '')
			.(!empty($h) ?"h			= $h," : '')
			."
			alt         = '".doSlash($alt)."',
			caption     = '".doSlash($caption)."',
			date		= now(),
			author		= '".doSlash($author)."'"
		);
		
		// get id
		$id = mysql_insert_id();

		if ($q===false) {
			safe_query("ROLLBACK");
			return 'failed to insert image';
		}
		
		if ($article_wrap_enabled) {
			// link image to wrapper article
			$data['article_image'] = $id;

			$result = modimg_wrap_image($data);
			
			if (!empty($result)) {
				safe_query("ROLLBACK");
				
				return 'failed to insert image wrapper';
			}
		}
		
		// write file to images folder
		$path = IMPATH.$id.$ext;
		$tmpfile = IMPATH.$tmpfilename;

		if (file_exists($tmpfile)) {
			if (shift_uploaded_file($tmpfile,$path)) {
				$failed = false;
				chmod($path,0755);
			} else {
//				unlink($tmpfile);
				safe_query("ROLLBACK");
				return 'Failed to move image file';
			}
		} else {
			safe_query("ROLLBACK");
			return "Image file '{$tmpfile}' does not exist.";
		}

		// commit it all. thumbnail is not critical
		safe_query("COMMIT");

		// get thumbnail dimenions
		list($t_width,$t_height) = modimg_get_image_dimensions(true);
		$t_crop = mem_get_pref('mod_image_thumbnail_crop','val');

		$t = new txp_thumb( $id );
		$t->crop = ($t_crop == '1');
		$t->hint = '0';
		if ( is_numeric($t_width) && is_numeric($t_height) ) {
			if ($w > $h)
				$t->width = $t_width;
			else
				$t->height = $t_height;
		}
		
		if (!$t->write()) {
			return 'Failed to create thumbnail';
		}
	}
}

function modimg_wrap_image($data)
{
	$article_data = array();
	
	foreach ($data as $key => $val)
	{
		if (strncmp('article_',$key,8)==0)
			$article_data[substr($key,8)] = $val;
	}
	
	return article_approver('article',$article_data);
}

function image_rejecter($type,$data)
{
	if ($type=='image' and is_array($data)) {
		extract($data);
		if (@file_exists($tmpfile)) {
			// remove the rejected image file
			unlink($tmpfile);
		}
	}
}

# --- END PLUGIN CODE ---

?>
