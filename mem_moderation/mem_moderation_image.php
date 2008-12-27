<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_moderation_image';

$plugin['version'] = '0.7';
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

h2(section license). License

p. This plugin is licensed under the "GPLv2":http://www.fsf.org/licensing/licenses/info/GPLv2.html.

h2(section installation). Installation

p. This plugin requires the plugins mem_form version 0.5.2+, mem_admin_parse and mem_moderation to function properly.

p. "Start Install Wizard":./index.php?event=image_moderate&step=preinstall

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

////////////////////////////////////////////////////////////
// Plugin 
// Author: Michael Manfre (http://manfre.net/)
////////////////////////////////////////////////////////////

define('MEM_MOD_ARTICLE_TMP_FILE_PREFIX', 'txpmem_');

global $event, $extensions, $path_to_site, $img_dir, $prefs, $image_vars;

require_plugin('mem_moderation');
require_plugin('mem_form');

$image_vars = array('id','name','category','ext','w','h','alt','caption','date','author','tmpfile','tmpfilename','date_taken','keywords','note');


if (@$prefs['mod_image_wrap_with_article']) {
	require_plugin('mem_moderation_article');
}

$image_vars = array_merge($image_vars, array('article_section', 'article_title',  'article_title_html', 'article_body', 'article_body_html', 'article_excerpt', 'article_excerpt_html', 'article_textile_excerpt', 'article_image', 'article_textile_body', 'article_keywords', 'article_status', 'article_category1', 'article_category2', 'article_annotate', 'article_annotateinvite', 'article_override_form', 'article_custom_1', 'article_custom_2', 'article_custom_3', 'article_custom_4', 'article_custom_5', 'article_custom_6', 'article_custom_7', 'article_custom_8', 'article_custom_9', 'article_custom_10', 'article_wrap_enabled') );


//--------------------------------------------------------------------
if (@txpinterface!='admin' || (@txpinterface=='admin' and ($event=='moderate' or $event=='image_moderate')))
{
	register_moderation_type('image',$image_vars,'mem_moderation_image_presenter','mem_moderation_image_approver','mem_moderation_image_rejecter');

	require_once txpath.'/include/txp_image.php';
	include_once txpath.'/lib/class.thumb.php';
}

//--------------------------------------------------------------------
if (@txpinterface == 'admin')
{
	register_callback('mem_moderation_image_moderate','image_moderate','', 1);
	add_privs('image_moderate','1,2,3');
	
	if ($event == 'image_moderate' or $event == 'moderate') {

		function mem_moderation_image_uninstall()
		{
			$log = array();
			
			if (safe_delete('txp_prefs',"name LIKE 'mod_image_%'"))
				$log[] = 'Deleted all image moderation preferences';
			else
				$log[] = 'Failed to delete image moderation preferences';
			
			return tag("UnInstall Log",'h2').doWrap($log,'ul','li');
		}

		function mem_moderation_image_preinstall()
		{
			return mem_moderation_image_install();
		}
		
		function mem_moderation_image_install()
		{
			global $prefs;
			$log = array();
			
			// remove old values
			safe_delete('txp_prefs',"name LIKE 'modimg_%'");
			
			if (!isset($prefs['mod_image_wrap_with_article'])) {
				mem_set_pref('mod_image_wrap_with_article','0','Moderation','1',0,'yesnoradio');
				$log[] = 'Added preference mod_image_wrap_with_article';
			}
			if (!isset($prefs['mod_image_size_full'])) {
				mem_set_pref('mod_image_size_full','640x480','Moderation','1');
				$log[] = 'Added preference mod_image_size_full';
			}
			if (!isset($prefs['mod_image_wrap_default'])) {
				mem_set_pref('mod_image_wrap_default','0','Moderation','1',0,'yesnoradio');
				$log[] = 'Added preference mod_image_wrap_default';
			}
			if (!isset($prefs['mod_image_size_thumbnail'])) {
				mem_set_pref('mod_image_size_thumbnail','160x120','Moderation','1');
				$log[] = 'Added preference mod_image_size_thumbnail';
			}
			if (!isset($prefs['mod_image_thumbnail_crop'])) {
				mem_set_pref('mod_image_thumbnail_crop','0','Moderation','1',0,'yesnoradio');
				$log[] = 'Added preference mod_image_thumbnail_crop';
			}

			if (!isset($prefs['mod_image_sharpen'])) {
				mem_set_pref('mod_image_sharpen','0','Moderation','1',0,'yesnoradio');
				$log[] = 'Added preference mod_image_sharpen';
			}

			if (!isset($prefs['mod_image_quality'])) {
				mem_set_pref('mod_image_quality','80','Moderation','1');
				$log[] = 'Added preference mod_image_quality';
			}
			
			return tag("Install Log",'h2').doWrap($log,'ul','li');
		}

		function mem_image_moderate($event, $step)
		{
			$msg='';

			if ($step=='image_save' or $step=='image_update')
			{
				// save changes
				$msg = mem_image_save($step);
			}
			else if ($step=='preinstall' || $step=='install')
			{
				register_tab('extensions','image_moderate', mem_modimg_gTxt('image_moderate'));
				echo pageTop('Image Moderation','');
				
				if ($step=='preinstall')
					echo mem_moderation_image_preinstall();
				else
					echo mem_moderation_image_install();
				
				return;
			}
			else if ($step=='uninstall')
			{
				register_tab('extensions', 'image_moderate', mem_modimg_gTxt('image_moderate'));
				echo pageTop('Image Moderation','');
				echo mem_image_uninstall();
				return;
			}
			else
			{
				register_tab('extensions', 'image_moderate', mem_modimg_gTxt('image_moderate'));
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

//register_callback('mem_mod_article_form_defaults', 'mem_form.defaults');
register_callback('mem_moderation_image_form_display', 'mem_form.display','',1);
register_callback('mem_moderation_image_form_submitted', 'mem_form.submit');

function mem_moderation_image_form($atts, $thing='')
{
	$atts = lAtts(array(
		'form'	=>	'',
	),$atts,0);
	
	$atts['type'] = 'mem_moderation_image';
	$atts['enctype'] = 'multipart/form-data';

	if (!empty($form))
	{
		$thing = fetch_form($form);
		unset($atts['form']);
	}

	$secrets = array();

	foreach($secrets as $a)
	{
		$thing .= '<txp:mem_form_secret name="'.$a.'" value="'.$$a.'" />';
		unset($atts[$a]);
	}

	return mem_form($atts, $thing);
}

function mem_moderation_image_form_submitted()
{
	global $mem_form_type, $mem_form_values, $txp_user;
	
	if ($mem_form_type != 'mem_moderation_image')
		return;

	extract($mem_form_values);

	$is_save = $action == 'mem_moderation_save';
	$is_delete = $action == 'mem_moderation_delete';
	$is_update = ($action == 'mem_moderation_update') || ($is_save && $modid);
	
	if ($is_update) 
		$is_save = false;
	
	if ($is_delete)
	{
		if (remove_moderated_content($modid))
		{
			mem_moderation_image_rejector('image', array('tmpfile'=>$tmpfilename));
		}
	}
	elseif ($is_save || $is_update)
	{
		// get args
		if ($is_update)
		{
			$msg = mem_image_save('image_update');
		}
		else
		{
			$msg = mem_image_save('');			
		}
	}
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
				mem_moderation_image_rejecter('image',array('tmpfile'=>$tmpfile));
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

			extract(mem_moderation_decode($mem_mod_info['data']));

			$out .= @hInput('tmpfilename',$tmpfilename);
			
			$out .= @hInput('mem_modimg_info_data',$mem_mod_info['data']);
		}

		$caption_area = '<textarea name="caption" cols="40" rows="7">'.$caption.'</textarea>';
		$note_area = '<textarea name="note" cols="40" rows="4">'.@$mem_mod_info['desc'].'</textarea>';
		$selects = event_category_popup("image", $category, '');
		$selects_cat1 = category_popup("article_category1", @$article_category1,'');
		$selects_cat2 = category_popup("article_category2", @$article_category2,'');
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
	global $prefs;
	if ($thumbnail) {
		$dim = $prefs['mod_image_size_thumbnail'];
	
		$w = 160;
		$h = 120;
	} else {
		$dim = $prefs['mod_image_size_full'];
	
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
	global $txpcfg,$txpac,$extensions,$path_to_site,$txp_user,$ign_user,$mem_modimg_info, $image_vars, $mem_form_values;

	if (isset($ign_user)) $txp_user = $ign_user;

	$is_mem_form = false;
	
	// determine caller
	if (!empty($mem_form_values))
	{
		// public mem_form
		$varray = $mem_form_values;
		$is_mem_form = true;
	}
	else
	{
		$args = gpsa($image_vars);
		
		$inf=gps('mem_modimg_info_data');
		
		if (!empty($inf))
		{
			// admin mod form
			$varray = lAtts(mem_moderation_decode($inf), $args);
		}
		else
		{
			// legacy
			$varray = $args;
		}
	
		$modid = gps('modid');
	}

	if (!empty($modid)) $varray['id'] = $id = $modid;

	if($txpac['textile_links']) 
	{
		include_once $txpcfg['txpath'].'/lib/classTextile.php';
		$textile = new Textile();

		$varray['caption'] = $textile->TextileThis($varray['caption'],1);
		
		unset($textile);
	}

	$res = '';

	if ($step=='image_update') 
	{
		if (update_moderated_content($id,$varray['note'],$varray)) 
		{
			$res = "Update link '$id'";
		}
		else
		{
			$res = "Failed to update link '$id'";
		}
	} 
	else 
	{
		if ($is_mem_form)
		{
			$file	= $mem_form_values['thefile']['tmp_name'];
			$name	= $mem_form_values['thefile']['name'];
		}
		else
		{
			$file = $_FILES['thefile']['tmp_name'];
			$name = $_FILES['thefile']['name'];
	
			$file = get_uploaded_file($file);
	
		}

		$varray['tmpfilename'] = MEM_MOD_ARTICLE_TMP_FILE_PREFIX . basename($file) . $name;
		$pending_file = $path_to_site . '/images/' . $varray['tmpfilename'];
		list($w,$h,$extension) = getimagesize($file);

		if ($extensions[$extension]) 
		{
			list($width,$height) = modimg_get_image_dimensions();

			if (class_exists('wet_thumb'))
			{
				$sharpen = ($prefs['mod_image_sharpen'] == 1 || $prefs['mod_image_sharpen'] === true);
				
				$quality = $prefs['mod_image_quality'];
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
			
			if (shift_uploaded_file($file, $pending_file) != false)
			{
				$varray['w'] = $w;
				$varray['h'] = $h;

				$varray['ext'] = $extensions[$extension];
				$varray['name'] = substr($name,0,strrpos($name,'.')) . $varray['ext'];
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
		extract(mem_moderation_decode($mem_mod_info['data']));

	if (isset($tmpfilename) and !empty($tmpfilename))
		return '<img src="'.hu.$img_dir.'/'.$tmpfilename.'"'.
				(!empty($w) ? ' width="'.$w.'"':'') . 
				(!empty($h) ? ' height="'.$h.'"':'') . ' />';
}


function mem_moderation_image_presenter($type,$data) {
	global $img_dir,$image_vars,$prefs;
	
	$create_article = $prefs['mod_image_wrap_with_article'];
	$wrap_default = $prefs['mod_image_wrap_default'];

	$out = '';

	if ($type=='image' and is_array($data)) 
	{
		extract(get_prefs());
		
		if (empty($data['article_annotate'])) $data['article_annotate'] = $comments_on_default;

		if (empty($data['article_title'])) $data['article_title'] = @$data['alt'];
		if (empty($data['article_body'])) $data['article_body'] = @$data['caption'];
		if (empty($data['article_user'])) $data['article_user'] = @$data['author'];
		if (empty($data['article_warp_enabled'])) $data['article_wrap_enabled'] = $wrap_default;

		$dimensions = getimagesize($data['tmpfile']);
		
		$data['w'] = $dimensions[0];
		$data['h'] = $dimensions[1];
		
		extract($data);

		if (!$article_annotate) {
			$article_annotate = $comments_on_default;
		}
		
		if (empty($article_annotateinvite))
			$article_annotateinvite = $comments_default_invite;

		$selects = event_category_popup("image", @$category, '');
		$textarea = '<textarea name="caption" cols="40" rows="7" tabindex="4">'.htmlspecialchars(@$caption).'</textarea>';

		$imgtag = '<img src="'.hu.$img_dir.'/'.$tmpfilename.'" width="'.$data['w'].'" height="'.$data['h'].'" />';

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
					tr( fLabelCell( 'categorize' ) . tda( category_popup("article_category1", @$article_category1, '') .br. category_popup("article_category2", @$article_category2, '') ) ) .
					tr( fLabelCell( 'status' ) . tda( selectInput('article_status', array(1=>'Draft',2=>'Hidden',3=>'Pending',4=>'Live',5=>'Sticky'),4) ) ) .
					tr( fLabelCell( 'body' ) . tda( '<textarea name="article_body" cols="40" rows="7">'.htmlspecialchars($article_body).'</textarea>', ' valign="top"' ) ) .
					tr( fLabelCell( 'annotate' ) . tda( yesnoRadio('article_annotate',$article_annotate) ) ) .
					tr( fLabelCell( 'annotateinvite' ) . fInputCell( 'article_annotateinvite', $article_annotateinvite, 1, 30 )) .
					tr( fLabelCell( 'keywords' ) . tda( '<textarea name="article_keywords" cols="40" rows="3">'.htmlspecialchars(@$article_keywords).'</textarea>', ' valign="top"' ) ) .
					tr( fLabelCell( 'override_default_form' ) . tda(form_pop(@$article_override_form, '').popHelp('override_form'))) .
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

function mem_moderation_image_approver($type,$data)
{
	global $prefs;

	if ($type=='image' && is_array($data))
	{
		
		if (@empty($data['ext']))
		{
			$tmpname = $data['tmpfilename'];
			$pos = strrpos($tmpname, '.');
			
			if ($pos !== false)
			{
				$data['ext'] = substr($tmpname, $pos, strlen($tmpname)-$pos);
			}
		}
		
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
		$t_crop = $prefs['mod_image_thumbnail_crop'];

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
	
	return mem_article_approver('article',$article_data);
}

function mem_moderation_image_rejecter($type,$data)
{
	if ($type=='image' and is_array($data)) {
		extract($data);
		
		$prefix = MEM_MOD_ARTICLE_TMP_FILE_PREFIX;
		$len = strlen(MEM_MOD_ARTICLE_TMP_FILE_PREFIX);
		
		if (@file_exists($tmpfile) and strncmp($tmpfile, $prefix, $len) == 0) {
			// remove the rejected image file
			unlink($tmpfile);
		}
	}
}

# --- END PLUGIN CODE ---

?>
