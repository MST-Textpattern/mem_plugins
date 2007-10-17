<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_moderation_article';

$plugin['version'] = '0.4.9';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Moderation plugin that allows articles to be submitted to the moderation queue.';
$plugin['type'] = 1; // 0 for regular plugin; 1 if it includes admin-side code

@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h1. Article Moderation

p. This moderation plugin allows users to submit articles in to the moderation queue.

h1. Installation

p. This plugin requires plugins mem_moderation and mem_admin_parse to be installed and enabled to function.

p. "Start Install Wizard":./index.php?event=article_moderate&step=preinstall

h1. Tag List (with arguments)

p. *mod_article_form* - This client side tag will place a form for submitting and re-editing submitted articles.
* form - The txp form that holds the layout tags.
* successform - The txp form that holds the template to show to the user on a successful form submission (for either save, edit, or delete).

p. These tags may be used within either the form or successform. All of these tags support the attributes 'class', which is the css class.

p. *mod_custom_input* - Display a text input field for custom input fields.
* id - The field id this tag represents. E.g. id="1" matches field custom_1
* isize - The size of the input field.

p. *mod_title_input* - Display a text input field for the title.
* isize - The size of the input field.

p. *mod_image_input* - Display a text input field for the image.
* isize - The size of the input field.

p. *mod_keywords_input* - Display a text input field for the keywords.
* isize - The size of the input field.

p. *mod_body_input* - Display a textarea field for the body.
* style - The css style to apply to the textarea.

p. *mod_body_html_input* - Display a textarea field for the body_html.
* style - The css style to apply to the textarea.

p. *mod_excerpt_input* - Display a textarea field for the excerpt.
* style - The css style to apply to the textarea.

p. *mod_excerpt_html_input* - Display a textarea field for the excerpt_html.
* style - The css style to apply to the textarea.

p. *mod_note_input* - Display a textarea field for the note (to the moderators)
* style - The css style to apply to the textarea.


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
global $event;

$article_vars = array('note','user','email','modid','id',
		'title','title_html','body','body_html','excerpt','excerpt_html','textile_excerpt','image',
		'textile_body','keywords','status','section','category1','category2',
		'annotate','annotateinvite','override_form',
		'custom_1','custom_2','custom_3','custom_4','custom_5',
		'custom_6','custom_7','custom_8','custom_9','custom_10');

require_plugin('mem_moderation');

// -------------------------------------------------------------
if ((@txpinterface=='admin' and ($event=='moderate' or $event=='article_moderate')) or @txpinterface!='admin')
	require_once(txpath.'/include/txp_article.php');

	function mem_category_popup($name,$val) 
	{
		$rs = getTree("root",'article');
		if ($rs) {
			return treeSelectInput($name,$rs,$val);
		}
		return false;
	}
	
// -------------------------------------------------------------
if (@txpinterface == 'admin') {
	register_callback('article_moderate','article_moderate','', 1);
	register_moderation_type('article',$article_vars,'article_presenter','article_approver','article_rejecter');

	if ($event=='article_moderate') {
		function article_moderate($event, $step) {
			$msg='';

			if ($step=='article_save' or $step=='article_post') {
				// save changes
				$msg = article_save($step);
			} else if ($step=='preinstall' or $step=='install') {
				register_tab('extensions','article_moderate','article_moderate');
				echo pageTop('Article Moderation','');

				if ($step=='preinstall')
					echo article_preinstall();
				else
					echo article_install();
				
				return;
			} else {
				if ($step) {
					$msg = "Step is '$step'";
				}
			}

			pageTop('article Moderation',$msg);

			echo '';//article_moderate_form();
		}
		
		function article_preinstall() {
			return article_install();
		}
		
		function article_install() {
			$log = array();

			$form = fetch('Form','txp_form','name','mod_article_form');
			if (!$form) {
				$form_html = <<<EOF
<div class="submit_label">Title</div>
<txp:mod_title_input />

<div class="submit_label">Category</div>
<txp:mod_category1_select />

<div class="submit_label">Body</div>
<txp:mod_body_input />

<div class="submit_label">Notes for the Moderator (optional)</div>
<txp:mod_note_input />
<!-- this will put the submitting username in to custom field 1 -->
<input type="hidden" name="custom_1" value='<txp:mem_profile var="name" />' />

<div><txp:mod_submit /></div>
EOF;
				$form_html = doSlash($form_html);
				if (safe_insert('txp_form',"name='mod_article_form',type='misc',Form='{$form_html}'"))
					$log[] = "Created form 'mod_article_form'";
				else
					$log[] = "Failed to create form 'mod_article_form'. " . mysql_error();
			} else
				$log[] = "Found form 'mod_article_form'. Skipping installation of default form.";

			$form = fetch('Form','txp_form','name','mod_article_success');
			if (!$form) {
				$form_html = <<<EOF
<txp:mem_if_step name="article_delete">
	<h2>Article deleted</h2>
	<p>Your article has been deleted and will not be posted on this site.</p>
<txp:else />
	<h2>News submitted</h2>
	<p>Thank you for submitting your article. It will be reviewed by a moderator before being posted to the site.</p>
</txp:mem_if_step>
EOF;
				$form_html = doSlash($form_html);
				if (safe_insert('txp_form',"name='mod_article_success',type='misc',Form='{$form_html}'"))
					$log[] = "Created form 'mod_article_form'";
				else
					$log[] = "Failed to create form 'mod_article_success'. " . mysql_error();
			} else
				$log[] = "Found form 'mod_article_success'. Skipping installation of default form.";

			return tag("Install Log",'h2').doWrap($log,'ul','li');
		}

	}
}

function article_moderate_form()
{
	global $step;
	
	$data = gpsa(array('note','user','email','modid','id',
		'title','title_html','body','body_html','excerpt','excerpt_html','textile_excerpt','image',
		'textile_body','keywords','status','section','category1','category2',
		'annotate','annotateinvite','override_form',
		'custom_1','custom_2','custom_3','custom_4','custom_5',
		'custom_6','custom_7','custom_8','custom_9','custom_10'));
	
	return article_presenter('article',$data);
}

// -------------------------------------------------------------
// -------------------------------------------------------------
function mod_article_form($atts,$thing='')
{
	global $step,$txp_user,$ign_user,$mem_mod_info,$mem_modarticle_info;
	extract(gpsa(array('modid','action','event','step','id')));
	$vars=array('note','user','email',
	'title','title_html','body','body_html','excerpt','excerpt_html','textile_excerpt','image',
	'textile_body','keywords','status','section','category1','category2',
	'annotate','annotateinvite','override_form',
	'custom_1','custom_2','custom_3','custom_4','custom_5',
	'custom_6','custom_7','custom_8','custom_9','custom_10'	);

	if (!empty($modid)) $id = $modid;

	if (isset($ign_user)) $txp_user = $ign_user;
	
	extract(lAtts(array(
		'form'		=> 'mod_article_form',
		'successform'	=> 'mod_article_success'
	),$atts));

	$mem_modarticle_info = gpsa($vars);
	if (isset($id))
		$mem_modarticle_info['id'] = $id;
	extract($mem_modarticle_info);
	$out = '';

	if (isset($action) and $action==gTxt( 'delete' ))
		$step = 'article_delete';

	if (isset($step) and ($step=='article_save' or $step=='article_update' or $step=='article_delete')) {
		if (!empty($successform))
			$Form = fetch_form($successform);

		if ($step=='article_delete') {
			if (remove_moderated_content($modid)) {
				$msg = 'Deleted article';
			} else {
				$msg = 'Failed to delete article image';
			}
		} else if ($step=='article_update') {
			if (update_moderated_content($id,$note,$mem_modarticle_info)) {
				$res = 'Updated Article';
			} else {
				$res = 'Failed to update Article';
			}
		} else {
			if (!isset($user))
				$mem_modarticle_info['user'] = $txp_user;
			
			$res = submit_moderated_content('article','',$mem_modarticle_info['note'],$mem_modarticle_info);
		}

		$out = admin_parse($Form);

		unset($mem_modarticle_info);
	} else {

		if (empty($form))
			$Form = $thing;
		else
			$Form = fetch_form($form);


		$action_url = $_SERVER['REQUEST_URI'];
		$qs = strpos($action_url,'?');
		if ($qs) $action_url = substr($action_url, 0, $qs);

		$out =	n.n."<form enctype='multipart/form-data' action='{$action_url}' method='post'>" .
				eInput('article_moderate') .
				sInput( $step=='article_edit' ? 'article_update' : 'article_save' );

		if (isset($modid) and !empty($modid)) {
			$out .= hInput('modid',$modid);

			$mem_mod_info = safe_row('*','txp_moderation',"`id`='".doSlash($modid)."'");

			extract($mem_modarticle_info=decode_content($mem_mod_info['data']));
		}

		if (isset($user) and !empty($user))
			$out .= hInput('user',$user);
		if (isset($email) and !empty($email))
			$out .= hInput('email',$email);
		else if ($email=safe_field('email','txp_users',"name='".doSlash($user)."'"))
			$out .= hInput('email',$email);

		$cattree = getTree("root",'article');
		$rs = safe_column("name", "txp_section", "name!='default'");
		if (!isset($section) || empty($section))
			$section = getDefaultSection();
		$section_select = ($rs) ? selectInput("section", $rs, $section) : '';

		$vals = array(
			'category1_select'	=> treeSelectInput("category1", $cattree, $category1),
			'category2_select'	=> treeSelectInput("category2", $cattree, $category2),
			'section_select'	=> $section_select,
			'submit'			=> fInput( "submit", 'action', gTxt( 'save' ), "publish" ),
			'delete'			=> (!empty($modid) ? fInput( "submit", 'action', gTxt( 'delete' ), "delete", '', "return confirm('". gTxt('are_you_sure') ."')" ) : '')
		);

		foreach ($vals as $a=>$b) {
			$a = '<txp:mod_'.$a.' />';
			$Form = str_replace($a,$b,$Form);
		}
		
		$out .= parse($Form);
		$out .= '</form>'.n;
		
		unset($mem_mod_info);
		unset($mem_modarticle_info);
	}

	return $out;		
}
function mod_custom_input($atts) {
	global $mem_modarticle_info;
	extract(lAtts(array(
		'id'	=>	'1',
		'isize'	=>	25,
		'class'	=>	__FUNCTION__
	),$atts));
	$title = $value = $mem_modarticle_info["custom_{$id}"];
	return fInput('text',"custom_{$id}",$value,$class,"custom_{$id}",'',$isize);
}
function mod_title_input($atts) {
	global $mem_mod_info,$mem_modarticle_info;
	extract(lAtts(array(
		'isize'	=>	25,
		'class'	=>	__FUNCTION__
	),$atts));
	$title = $value = $mem_modarticle_info["title"];
	return fInput('text',"title",$value,$class,'title','',$isize);
}
function mod_image_input($atts) {
	global $mem_mod_info,$mem_modarticle_info;
	extract(lAtts(array(
		'isize'	=>	25,
		'class'	=>	__FUNCTION__
	),$atts));
	$title = $value = $mem_modarticle_info["image"];
	return fInput('text',"image",$value,$class,'image','',$isize);
}
function mod_keywords_input($atts) {
	global $mem_mod_info,$mem_modarticle_info;
	extract(lAtts(array(
		'isize'	=>	25,
		'style'	=>	'',
		'class'	=>	__FUNCTION__
	),$atts));
	$title = $value = $mem_modarticle_info["keywords"];
	return '<textarea name="keywords"'.(!empty($style)?' style="'.$style.'"':'').
			(!empty($class)?' class="'.$class.'"':'').'>'.
			htmlspecialchars($mem_modarticle_info['keywords']).'</textarea>';

}
function mod_body_input($atts) {
	global $mem_mod_info,$mem_modarticle_info;
	extract(lAtts(array(
		'style'	=>	'',
		'class'	=>	__FUNCTION__
	),$atts));
	return '<textarea name="body"'.(!empty($style)?' style="'.$style.'"':'').
			(!empty($class)?' class="'.$class.'"':'').'>'.
			htmlspecialchars($mem_modarticle_info['body']).'</textarea>';
}
function mod_body_html_input($atts) {
	global $mem_mod_info,$mem_modarticle_info;
	extract(lAtts(array(
		'style'	=>	'',
		'class'	=>	__FUNCTION__
	),$atts));
	return '<textarea name="body_html"'.(!empty($style)?' style="'.$style.'"':'').
			(!empty($class)?' class="'.$class.'"':'').'>'.
			htmlspecialchars($mem_modarticle_info['body_html']).'</textarea>';
}
function mod_excerpt_input($atts) {
	global $mem_mod_info,$mem_modarticle_info;
	extract(lAtts(array(
		'style'	=>	'',
		'class'	=>	__FUNCTION__
	),$atts));
	return '<textarea name="excerpt"'.(!empty($style)?' style="'.$style.'"':'').
			(!empty($class)?' class="'.$class.'"':'').'>'.
			htmlspecialchars($mem_modarticle_info['excerpt']).'</textarea>';
}
function mod_excerpt_html_input($atts) {
	global $mem_mod_info,$mem_modarticle_info;
	extract(lAtts(array(
		'style'	=>	'',
		'class'	=>	__FUNCTION__
	),$atts));
	return '<textarea name="excerpt_html"'.(!empty($style)?' style="'.$style.'"':'').
			(!empty($class)?' class="'.$class.'"':'').'>'.
			htmlspecialchars($mem_modarticle_info['excerpt_html']).'</textarea>';
}

// -------------------------------------------------------------
// -------------------------------------------------------------
	
function article_presenter($type,$data) {
	
	$out = '';
	
	if ($type=='article' and is_array($data)) {
		extract(get_prefs());

		extract(lAtts(array(
			'user'		=> '',
			'email'		=> '',
			'title'		=> '',
			'title_html'	=> '',
			'body'		=> '',
			'body_html'	=> '',
			'excerpt'	=> '',
			'excerpt_html'	=> '',
			'image'		=> '',
			'keywords'	=> '',
			'status'	=> '',
			'section'	=> '',
			'category1'	=> '',
			'category2'	=> '',
			'annotate'	=> '',
			'annotateinvite'	=> $comments_default_invite,
			'override_form'	=> '',
			'custom_1'	=> '',
			'custom_2'	=> '',
			'custom_3'	=> '',
			'custom_4'	=> '',
			'custom_5'	=> '',
			'custom_6'	=> '',
			'custom_7'	=> '',
			'custom_8'	=> '',
			'custom_9'	=> '',
			'custom_10'	=> ''
		),$data));

		if (empty($annotateinvite)) $annotateinvite = $comments_default_invite;
		if (empty($section)) $section = getDefaultSection();
		if (empty($annotate)) $annotate = ($comments_on_default==1 ? 1 : 0);

		$rs = safe_column("name", "txp_section", "name!='default'");
		$section_select = ($rs) ? selectInput("section", $rs, $section) : '';

		$out = startTable( 'edit' ) .
				tr( fLabelCell( 'title' ) . fInputCell( 'title', $title, 1, 30 )) .
				(empty($section_select) ? '' : tr( fLabelCell( 'section' ) . tda( $section_select ) )) .
				tr( fLabelCell( 'categorize' ) . tda( mem_category_popup("category1", $category1) .br. mem_category_popup("category2", $category2) ) ) .
				tr( fLabelCell( 'excerpt' ) . tda( '<textarea name="excerpt" cols="40" rows="4">'.$excerpt.'</textarea>', ' valign="top"' ) ) .
				tr( fLabelCell( 'body' ) . tda( '<textarea name="body" cols="40" rows="7">'.$body.'</textarea>', ' valign="top"' ) ) .
				tr( fLabelCell( 'status' ) . tda( selectInput('status', array(1=>'Draft',2=>'Hidden',3=>'Pending',4=>'Live',5=>'Sticky'),4) ) ) .
				tr( fLabelCell( 'image' ) . fInputCell( 'image', $image, 1, 30 )) .
				tr( fLabelCell( 'annotate' ) . tda( yesnoRadio('annotate',$annotate) ) ) .
				tr( fLabelCell( 'annotateinvite' ) . fInputCell( 'annotateinvite', $annotateinvite, 1, 30 )) .
				tr( fLabelCell( 'keywords' ) . fInputCell( 'keywords', $keywords, 1, 30 )) .
				tr( fLabelCell( 'override_default_form' ) . tda(form_pop($override_form).popHelp('override_form'))) .
				tr( fLabelCell( 'author' ) . fInputCell( 'user', $user, 1, 30 ));

		for($i=1;$i<=10;$i++) {
			$k = "custom_{$i}";
			$kset = "custom_{$i}_set";
			if (isset($$kset) and !empty($$kset))
				$out .= tr(	fLabelCell( $$kset ) . 
							fInputCell( $k, $$k, 1, 30 ) );
		}
		$out .= endTable();

		$out .= hInput('email',$email);
		$out .= hInput('excerpt_html',$excerpt_html);
		$out .= hInput('body_html',$body_html);
		$out .= hInput('title_html',$title_html);

	}

	return $out;
}

function article_approver($type,$data)
{
	global $txpcfg,$txp_user;

	if ($type=='article' && is_array($data)) {
		$incoming = $data;
		
		extract(get_prefs());

		$message='';
		
		// remap field values
		$incoming['textile_body'] = USE_TEXTILE;
		$incoming['textile_excerpt'] = USE_TEXTILE;
		$incoming['Status'] = $incoming['status'];
		$incoming['Title'] = $incoming['title'];
		$incoming['Body'] = $incoming['body'];
		$incoming['Excerpt'] = $incoming['excerpt'];
		$incoming['publish_now'] = 1;
		
		$incoming = textile_main_fields($incoming, $use_textile);

		extract(doSlash($incoming));

		if ($publish_now==1) {
			$when = 'now()';
		} else {
			$when = strtotime($year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.":00")-tz_offset();
			$when = "from_unixtime($when)";
		}

		if ($Title or $Body or $Excerpt) {
			
			if (!has_privs('article.publish') && $Status>=4) $Status = 3;
			if (empty($url_title)) $url_title = stripSpace($Title_plain, 1);  	
			if (!$annotate) {
				$annotate = $comments_on_default;
			}
			
			if (empty($annotateinvite))
				$annotateinvite = $comments_default_invite;

			$rs = safe_insert(
			   "textpattern",
			   "Title           = '$Title',
				Body            = '$Body',
				Body_html       = '$Body_html',
				Excerpt         = '$Excerpt',
				Excerpt_html    = '$Excerpt_html',
				Image           = '$image',
				Keywords        = '$keywords',
				Status          = '$Status',
				Posted          = $when,
				LastMod         = now(),
				AuthorID        = '$txp_user',
				Section         = '$section',
				Category1       = '$category1',
				Category2       = '$category2',
				textile_body    =  $textile_body,
				textile_excerpt =  $textile_excerpt,
				Annotate        = '$annotate',
				override_form   = '$override_form',
				url_title       = '$url_title',
				AnnotateInvite  = '$annotateinvite',
				custom_1        = '$custom_1',
				custom_2        = '$custom_2',
				custom_3        = '$custom_3',
				custom_4        = '$custom_4',
				custom_5        = '$custom_5',
				custom_6        = '$custom_6',
				custom_7        = '$custom_7',
				custom_8        = '$custom_8',
				custom_9        = '$custom_9',
				custom_10       = '$custom_10',
				uid				= '".md5(uniqid(rand(),true))."',
				feed_time		= now()"
			);
			
			if ($rs !== false)
			{
				$GLOBALS['ID'] = mysql_insert_id();
					
				if ($Status>=4) {
					
					do_pings();
					
					safe_update("txp_prefs", "val = now()", "name = 'lastmod'");
				}
			}
			else
			{
				return 'error ('.mysql_errno().') - '. mysql_error();
			}
		}
		else
		{
			return 'missing title, body, and excerpt';
		}
	}
	else
	{
		return 'invalid data or type';
	}
}

function article_rejecter($type,$data)
{
//	if ($type=='article' and is_array($data)) {
//		// do nothing
//	}
}

function mem_gps_category($atts)
{
	extract(lAtts(array(
		'arg'	=> 'category1',
		'link'	=> 0,
		'title' => 1,
		'section'	=> gps('section'),
		'wraptag'	=> '',		
	),$atts));

	
	return category(array(
					'link' => $link,
					'title' => $title,
					'name' => gps($arg),
					'wraptag' => $wraptag,
					'section' => $section
					));
}

function mem_gps_section($atts)
{
	extract(lAtts(array(
		'arg'	=> 'section',
		'link'	=> 0,
		'title' => 1,
		'wraptag'	=> '',		
	),$atts));

	
	return section(array(
					'link' => $link,
					'title' => $title,
					'name' => gps($arg),
					'wraptag' => $wraptag
					));
}


# --- END PLUGIN CODE ---

?>
