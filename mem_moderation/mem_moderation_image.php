<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_moderation_image';

$plugin['version'] = '0.7.6';
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

h2(section examples). Examples

p. See form mod_image_form for an example using normal image upload.

p. See form mod_image_article_form for an example that will upload an image and associate it with a newly created article.

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

////////////////////////////////////////////////////////////
// Plugin 
// Author: Michael Manfre (http://manfre.net/)
////////////////////////////////////////////////////////////

define('MEM_MOD_ARTICLE_TMP_FILE_PREFIX', 'txpmem_');

global $event, $extensions, $path_to_site, $img_dir, $prefs, $image_vars, $mem_moderation_lang;

require_plugin('mem_moderation');
require_plugin('mem_form');

$image_vars = array('id','name','category','ext','w','h','alt','caption','date','author','tmpfile','tmpfilename','date_taken','keywords','note');


if (@$prefs['mem_mod_img_wrap_with_article']) {
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

$mem_mod_img_lang = array(
	'image_moderate' => 'Image Moderation',
	'mem_mod_img' => 'Image Moderation',
	
	// prefs
	'mem_mod_img_wrap_with_article'	=> 'Wrap with Article?',
	'mem_mod_img_size_full'	=> 'Image size (WxH):',
	'mem_mod_img_size_thumbnail'	=> 'Thumbnail size (WxH):',
	'mem_mod_img_wrap_default'	=> 'Wrap Default?',
	'mem_mod_img_thumbnail_crop'	=> 'Crop Thumbnail?',
	'mem_mod_img_sharpen'	=> 'Sharpen Image?',
	'mem_mod_img_quality'	=> 'JPEG Image Quality',
);

$mem_moderation_lang = array_merge($mem_moderation_lang, $mem_mod_img_lang);

//--------------------------------------------------------------------
if (@txpinterface == 'admin')
{
	register_callback('mem_image_moderate','image_moderate','', 1);
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


			$p = array(
				'wrap_with_article' => array('0', 200, 'yesnoradio'),
				'size_full' => array('640x480', 205, 'text_input'),
				'size_thumbnail' => array('160x120', 206, 'text_input'),
				'wrap_default' => array('0', 208, 'yesnoradio'),
				'thumbnail_crop' => array('0', 210, 'yesnoradio'),
				'sharpen' => array('0', 211, 'yesnoradio'),
				'quality' => array('80', 212, 'text_input'),
			);


			foreach($p as $pref => $v)
			{
				if (!isset($prefs[$pref]))
				{
					if (set_pref('mem_mod_img_' . $pref, $v[0], 'mem_mod_img', PREF_ADVANCED, $v[2], $v[1]))
					{
						$log[] = mem_moderation_gTxt('set_pref', array('{name}' => $pref));
					}
					else
					{
						$log[] = mem_moderation_gTxt('set_pref_failed', array('{name}' => $pref));
					}
				}
				else
				{
					$log[] = mem_moderation_gTxt('set_pref_exists', array('{name}' => $pref));
				}
			}


			$forms = array();
			$forms['mod_image_form'] = <<<EOF
<txp:mem_moderation_image_form>
	<txp:mem_form_file name="thefile" label="Image" break="" />
	<txp:mem_form_select_category type="image" name="category" label="Category" break="" />
	<txp:mem_form_text name="caption" label="Caption" break="" />
	<txp:mem_form_text name="alt" label="Alt" break="" />
	<txp:mem_form_text name="note" label="Notes" break="" />
	<txp:mem_form_submit name="mem_moderation_save" label="Save" />
</txp:mem_moderation_image_form>
EOF;

			$forms['mod_image_article_form'] = <<<EOF
<txp:mem_moderation_image_form>
	<txp:mem_form_text name="article_title" label="Title" break=""/>
	<txp:mem_form_select_category name="article_category1" label="Category" break=""/>
	<txp:mem_form_textarea name="article_body" label="Body" break=""/>

	<txp:mem_form_file name="thefile" label="Image" break="" />
	<txp:mem_form_select_category type="image" name="category" label="Category" break="" />
	<txp:mem_form_text name="caption" label="Caption" break="" />
	<txp:mem_form_text name="alt" label="Alt" break="" />

	<txp:mem_form_textarea name="note" label="Notes for the Moderator (optional)" required="0" />
	<txp:mem_form_submit name="mem_moderation_save" label="Save" />
</txp:mem_moderation_image_form>
EOF;

			foreach($forms as $name => $html)
			{
				$form = fetch('Form', 'txp_form', 'name', $name);
				if (!$form)
				{
					$form_html = doSlash($html);
					if (safe_insert('txp_form', "name='{$name}', type='misc', Form='{$form_html}'"))
					{
						$log[] = mem_moderation_gTxt('form_created', array('{form}' => 'mod_image_form'));
					}
					else
					{
						$log[] = mem_moderation_gTxt('form_create_failed', array('{form}' => 'mod_image_form', '{mysql_error}' => mysql_error()));
					}
				}
				else
				{
					$log[] = mem_moderation_gTxt('form_found', array('{form}' => $name));
				}
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
				register_tab('extensions','image_moderate', mem_moderation_gTxt('image_moderate'));
				echo pageTop('Image Moderation','');
				
				if ($step=='preinstall')
					echo mem_moderation_image_preinstall();
				else
					echo mem_moderation_image_install();
				
				return;
			}
			else if ($step=='uninstall')
			{
				register_tab('extensions', 'image_moderate', mem_moderation_gTxt('image_moderate'));
				echo pageTop('Image Moderation','');
				echo mem_image_uninstall();
				return;
			}
			else if ($step == 'img_display')
			{
				mem_moderation_image_presenter_img();
			}
			else
			{
				register_tab('extensions', 'image_moderate', mem_moderation_gTxt('image_moderate'));
				
				$msg = '';
			}
	
			pageTop('Image Moderation',$msg);
		}
	}
	
	function mem_moderation_image_tempurl($filename)
	{
		return '?event=image_moderate&#38;step=img_display&#38;f=' . urlencode($filename);
	}
}


function image_moderate_form()
{
	global $step;

	$data = gpsa(array('alt','category','caption','author','name','ext','tmpfile','keywords','photographer','date_taken','title','note'));

	return image_presenter('image',$data);
}

register_callback('mem_moderation_image_form_defaults', 'mem_form.defaults');
register_callback('mem_moderation_image_form_display', 'mem_form.display','');
register_callback('mem_moderation_image_form_submitted', 'mem_form.submit');

function mem_moderation_image_form_defaults()
{
	global $mem_form, $mem_form_type, $mem_form_default, $mem_mod_info, $mem_modarticle_info, $img_dir;

	// type check
	if ($mem_form_type!='mem_moderation_image')
	{
		return;
	}
	
	extract(gpsa(array('modid','articleid')));
	
	// editing mod item
	if (!empty($modid))
	{
		// get mod data	
		$mem_mod_info = safe_row('*','txp_moderation',"`id`='".doSlash($modid)."'");
	
		if ($mem_mod_info)
		{
			// set decoded
			$mem_modarticle_info = mem_moderation_decode($mem_mod_info['data']);
			
			$thefile = $mem_modarticle_info['thefile'];
			if (is_array($thefile))
			{
				if (empty($mem_modarticle_info['tmpfilename']))
				{
					$mem_modarticle_info['tmpfilename'] = MEM_MOD_ARTICLE_TMP_FILE_PREFIX . basename($thefile['tmp_name']) . $thefile['name'];
				}
			}
		}
	}
	// editing publish article
	else if (!empty($articleid))
	{
		$rs = safe_row('*', 'textpattern',"`id`='".doSlash($articleid)."'");

		// set mod data
		$mem_modarticle_info = array();
		foreach($rs as $k => $v)
		{
			$mem_modarticle_info['article_'.strtolower($k)] = $v;
		}
		
		$mem_modarticle_info['articleid'] = $rs['ID'];
	}

	if (is_array($mem_modarticle_info))
	{
		// set defaults
		foreach($mem_modarticle_info as $key => $val)
		{
			mem_form_default($key, $val);
		}
		
		if (!empty($mem_modarticle_info['article_image']))
		{
			$image_id = $mem_modarticle_info['article_image'];
			$rs = safe_row('*', 'txp_image', "id = $image_id");
			
			if ($rs)
			{
				$file = IMPATH . $rs['id'] . $rs['ext'];
				
				if (file_exists($file))
				{
					$type = '';
					if (function_exists('mime_content_type'))
						$type = @mime_content_type($file);
						
					if (empty($type))
					{
						switch($rs['ext'])
						{
							case '.jpg':
								$type = 'image/jpeg';
								break;
							case '.gif':
								$type = 'image/gif';
								break;
						}
					}
					
					mem_form_default('thefile', array(
						'tmp_name' => $file,
						'name' => $rs['name'],
						'type' => $type
					));
					$mem_modarticle_info['image_url'] = hu.$img_dir.'/'.$rs['id'].'t'.$rs['ext'];
				}
			}
		}
	}
}

function mem_moderation_image_form_display()
{
	global $mem_form_type, $mem_mod_info, $mem_modarticle_info;

	// type check
	if ($mem_form_type!='mem_moderation_image')
	{
		return;
	}

	$out = '';
	if (isset($mem_mod_info))
	{
		$out .= n.'<input type="hidden" name="modid" value="'.htmlspecialchars($mem_mod_info['id']).'" />'.
			n.'<input type="hidden" name="type" value="'. htmlspecialchars($mem_mod_info['type']).'" />';

		if ($mem_mod_info['type'] == 'article-edit' && isset($mem_modarticle_info['articleid']))
			$out .= n.'<input type="hidden" name="articleid" value="'.$mem_modarticle_info['articleid'].'" />';
		
		mem_form_store('modid', 'modid', $mem_mod_info['id']);
		mem_form_store('type', 'type', $mem_mod_info['type']);
	}
	else if (isset($mem_modarticle_info))
	{
		if (empty($mem_modarticle_info['articleid']))
			$mem_modarticle_info['articleid'] = $mem_modarticle_info['id'];

		$out .= n.'<input type="hidden" name="articleid" value="'.htmlspecialchars($mem_modarticle_info['articleid']).'" />'.
			n.'<input type="hidden" name="type" value="article" />';
		
		if (!empty($mem_modarticle_info['image_url']))
		{
			$out .= n.'<input type="hidden" name="image_url" value="'.htmlspecialchars($mem_modarticle_info['image_url']).'" />';
		}
	}
	
	return $out;
}


function mem_moderation_image_form($atts, $thing='')
{
	$atts['type'] = 'mem_moderation_image';
	$atts['enctype'] = 'multipart/form-data';
	

	if (!empty($atts['form']))
	{
		$thing = fetch_form($form);
		unset($atts['form']);
	}

	$secrets = array();
	
	$modid = gps('modid');
	if (!empty($modid)) $secrets[] = 'modid';

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

	if (isset($action))
	{
		$is_save = $action == 'mem_moderation_save';
		$is_delete = $action == 'mem_moderation_delete';
		$is_update = ($action == 'mem_moderation_update') || ($is_save && $modid);
	}
	else
	{
		$is_save = ps('mem_moderation_save');
		$is_delete = ps('mem_moderation_delete');
		$is_update = ps('mem_moderation_update') || ($is_save && ps('modid'));
	}

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
		$dim = $prefs['mem_mod_img_size_thumbnail'];
	
		$w = 160;
		$h = 120;
	} else {
		$dim = @$prefs['mem_mod_img_size_full'];
	
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
	global $txpcfg, $txpac, $prefs, $extensions, $path_to_site, $txp_user, $ign_user, $mem_mod_info, $mem_modimg_info, $image_vars, $mem_form_values;

	if (isset($ign_user)) $txp_user = $ign_user;

	$is_mem_form = false;
	
	// determine caller
	if (!empty($mem_form_values))
	{
		// public mem_form
		$varray = $mem_form_values;
		$is_mem_form = true;

		$varray['id'] = $id = @$varray['modid'];
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

	if ($is_mem_form)
	{
		$file = $mem_form_values['thefile']['tmp_name'];
		$name = $mem_form_values['thefile']['name'];
	}
	else
	{
		$file = $_FILES['thefile']['tmp_name'];
		$name = $_FILES['thefile']['name'];

		$file = get_uploaded_file($file);
	}


	$varray['user'] = $txp_user;
	$email = safe_field('email', 'txp_users', "name='".doSlash($txp_user)."'");

	if (!empty($file))
	{	
		$varray['tmpfilename'] = MEM_MOD_ARTICLE_TMP_FILE_PREFIX . basename($file) . $name;

		$pending_file = $prefs['tempdir'] . DS . $varray['tmpfilename'];

		list($w,$h,$extension) = getimagesize($file);

		if ($extensions[$extension]) 
		{
			list($width,$height) = modimg_get_image_dimensions();

			if (class_exists('wet_thumb'))
			{
				$sharpen = (@$prefs['mem_mod_img_sharpen'] == 1 || @$prefs['mem_mod_img_sharpen'] === true);
				
				$quality = @$prefs['mem_mod_img_quality'];
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

				
				if (!isset($varray['note']))
				{
					$varray['note'] = $varray['name'];
				}

			} else {
				$res = "Failed to upload image";
			}
		} else {
			$res = "'{$extension}' is an unapproved extension";
		}
	} // if !$file
	else
	{
		if ($prefs['mem_mod_img_wrap_with_article'])
		{
			if (empty($varray['note']) && !empty($varray['article_title']))
			{
				$varray['note'] = $varray['article_title'];
			}
		}
	}


	if (empty($res))
	{
		if ($step=='image_update') 
		{
			if (update_moderated_content($id, @$varray['note'], $varray)) {
				$res = "Update link '$id'";
				
				// remove previously uploaded file
				$data = mem_moderation_decode($mem_mod_info['data']);
				$tmpfile = @$data['tmpfile'];

				if (file_exists($tmpfile) 
					&& strncmp(basename($tmpfile), MEM_MOD_ARTICLE_TMP_FILE_PREFIX, strlen(MEM_MOD_ARTICLE_TMP_FILE_PREFIX)) == 0 )
				{
					@unlink($tmpfile);
				}
			} else {
				$res = "Failed to update link '$id'";
			}
		} 
		else 
		{
			$res = submit_moderated_content('image', $email, @$varray['note'], $varray);
	
			if ($res) {
				$res = ''; //"Submitted Image '$res'";
			} else {
				$res = "Failed to submit image";
			}
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


function mem_moderation_image_presenter_img()
{
	global $tempdir;
	
	$tempfile = gps('f');

	// restrict file serving
	if (strncmp($tempfile, MEM_MOD_ARTICLE_TMP_FILE_PREFIX, strlen(MEM_MOD_ARTICLE_TMP_FILE_PREFIX)) != 0)
	{
		txp_die('Not Found', 404);
	}
	
	$file = build_file_path($tempdir, sanitizeForFile($tempfile));
	
	if (file_exists($file))
	{
		$mime = 'application/octet-stream';
		
		header('Content-Type: ' . $mime);
		header('Content-Disposition: attachment; filename=' . $tempfile);
		readfile($file);
	}
	else
	{
		txp_die('Not Found', 404);
	}
}

function mem_moderation_image_presenter($type,$data) {
	global $img_dir,$image_vars,$prefs;
	
	$create_article = @$prefs['mem_mod_img_wrap_with_article'];
	$wrap_default = @$prefs['mem_mod_img_wrap_default'];

	$out = '';

	if ($type=='image' and is_array($data)) 
	{
		extract(get_prefs());
		
		if (empty($data['article_annotate'])) $data['article_annotate'] = $comments_on_default;

		if (empty($data['article_title'])) $data['article_title'] = @$data['alt'];
		if (empty($data['article_body'])) $data['article_body'] = @$data['caption'];
		if (empty($data['article_excerpt'])) $data['article_excerpt'] = '';
		if (empty($data['article_user'])) $data['article_user'] = @$data['author'];
		if (empty($data['article_wrap_enabled'])) $data['article_wrap_enabled'] = $wrap_default;

		if (!empty($data['thefile']['tmp_name']))
		{
			if (empty($data['tmpfilename']))
			{
				$data['tmpfilename'] = MEM_MOD_ARTICLE_TMP_FILE_PREFIX . basename($data['thefile']['tmp_name']) . $data['thefile']['name'];
			}
			
			if (empty($data['name']))
			{
				$data['name'] = $data['thefile']['name'];
			}
	
			$filename = (empty($data['tmpfile']) ? $tempdir . DS . $data['tmpfilename'] : $data['tmpfile']);
	
			$dimensions = getimagesize($filename);
			
			$data['w'] = $dimensions[0];
			$data['h'] = $dimensions[1];
		}
		
		extract($data);

		if (!$article_annotate) {
			$article_annotate = $comments_on_default;
		}
		
		if (empty($article_annotateinvite))
			$article_annotateinvite = $comments_default_invite;

		$selects = event_category_popup("image", @$category, '');
		$textarea = '<textarea name="caption" cols="40" rows="7" tabindex="4">'.htmlspecialchars(@$caption).'</textarea>';

		if (isset($tmpfilename))
			$imgtag = '<img src="'.mem_moderation_image_tempurl($tmpfilename).'" width="'.$data['w'].'" height="'.$data['h'].'" />';

		$out = startTable( 'edit' ) .
				(isset($imgtag) ? tr( tdcs( @$imgtag, 2) ) : '').
				tr( fLabelCell( 'name' ) . fInputCell( 'name', @$name, 1, 30 )) .
				tr( fLabelCell( 'alt') .fInputCell( 'alt', @$alt, 2, 15 )) .
				tr( fLabelCell( 'image_category', 'category' ) . td( $selects ) ) .
				(isset($w) ? tr( fLabelCell( 'image_dimensions' ) . td( @$w . 'x' . @$h ) ) : '').
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
				tr( fLabelCell( 'excerpt' ) . tda( '<textarea name="article_excerpt" cols="40" rows="3">'.htmlspecialchars($article_excerpt).'</textarea>', ' valign="top"' ) ) .
				tr( fLabelCell( 'annotate' ) . tda( yesnoRadio('article_annotate',$article_annotate) ) ) .
				tr( fLabelCell( 'annotateinvite' ) . fInputCell( 'article_annotateinvite', $article_annotateinvite, 1, 30 )) .
				tr( fLabelCell( 'keywords' ) . tda( '<textarea name="article_keywords" cols="40" rows="3">'.htmlspecialchars(@$article_keywords).'</textarea>', ' valign="top"' ) ) .
				tr( fLabelCell( 'override_default_form' ) . tda(form_pop(@$article_override_form, '').popHelp('override_form'))) .
				tr( fLabelCell( 'author' ) . fInputCell( 'article_user', $article_user, 1, 30 ));

				'';

		$custom_fields = safe_rows('name, val, html', 'txp_prefs', "name like 'custom_%_set' and event = 'custom'");

		if ($custom_fields)
		{
			foreach($custom_fields as $field)
			{
				extract($field);
				
				$tr = fLabelCell( $val );
				
				$k = 'article_' . implode('_', explode('_', $name, -1));
				
				switch ($html)
				{
					case 'select':
						// get values
						$items = safe_column('value', 'custom_fields', "name = '{$name}'");
						
						$tr .= tda(
							selectInput($k, $items, @$$k),
							' class="noline"'
						);
						break;
					default:
						$tr .= fInputCell( $k, @$$k, 1, 30 );
						break;
				}
				
				$out .= tr($tr);
			}
		}
		else
		{
			for($i=1;$i<=20;$i++) {
				$k = "article_custom_{$i}";
				$kset = "custom_{$i}_set";
				if (isset($$kset) and !empty($$kset))
				{
					$out .= tr(	fLabelCell( @$$kset ) . fInputCell( $k, @$$k, 1, 30 ) );
				}
			}
		}


		$out .= endTable();
		$out .= hInput('tmpfilename',@$tmpfilename);
		$out .= hInput('author', $user);
		$out .= hInput('w',@$w);
		$out .= hInput('h',@$h);

	}

	return $out;
}

function mem_moderation_image_approver($type, $data)
{
	global $prefs;

	if ($type=='image' && is_array($data))
	{
		if (empty($data['tmpfilename']) && !empty($data['tmpfilename']))
		{
			$data['tmpfilename'] = MEM_MOD_ARTICLE_TMP_FILE_PREFIX . basename($data['thefile']['tmp_name']) . $data['thefile']['name'];
		}

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
		
		if (!empty($tmpfilename))
		{
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
			
			// link image to wrapper article
			$data['article_image'] = $id;
		}
		else
		{
			$id = 0;
		}
		
		if ($article_wrap_enabled)
		{
			$result = modimg_wrap_image($data);
			
			if (!empty($result)) {
				safe_query("ROLLBACK");
				
				return 'failed to insert image wrapper';
			}
		}

		if ($id)
		{
			// write file to images folder
			$path = IMPATH . $id . $ext;
			$tmpfile = $prefs['tempdir'] . DS . $tmpfilename;
	
			if (file_exists($tmpfile)) {
				if (shift_uploaded_file($tmpfile, $path)) {
					$failed = false;
					chmod($path, 0755);
				} else {
	//				unlink($tmpfile);
					safe_query("ROLLBACK");
					return 'Failed to move image file';
				}
			} else {
				safe_query("ROLLBACK");
				return "Image file '{$tmpfile}' does not exist.";
			}
		}

		// commit it all. thumbnail is not critical
		safe_query("COMMIT");

		if ($id)
		{
			// get thumbnail dimenions
			list($t_width,$t_height) = modimg_get_image_dimensions(true);
			$t_crop = $prefs['mem_mod_img_thumbnail_crop'];
	
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

if (@txpinterface=='public')
{
	function mem_moderation_image_url_preview()
	{
		global $pretext;
		
		if (stripos($pretext['request_uri'], '/temp-image-preview/') !== FALSE)
		{
			mem_moderation_image_presenter_img();
		}
	}

	register_callback('mem_moderation_image_url_preview', 'pretext_end');
	
	function mem_image_preview_url($atts)
	{
		global $mem_modarticle_info, $img_dir;

		$thumbnail = !empty($atts['thumbnail']) && $atts['thumbnail'] == '0';
			
		if (!empty($mem_modarticle_info['tmpfilename']))
			return '<img src="./temp-image-preview/?f=' . $mem_modarticle_info['tmpfilename']. '" />';
		elseif (!empty($mem_modarticle_info['article_image']))
		{
			$id = assert_int($mem_modarticle_info['article_image']);
			
			$ext = safe_field('ext', 'txp_image', "id = $id");
			
			$thumb = $thumbnail ? 't' : '';
			return '<img src="'.hu.$img_dir.'/'.$id.$thumb.$ext.'" />';
		}
		return '';
	}
}

# --- END PLUGIN CODE ---

?>
