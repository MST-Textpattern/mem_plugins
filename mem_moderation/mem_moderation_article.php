<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_moderation_article';

$plugin['version'] = '0.6';
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
////////////////////////////////////////////////////////////

// If true, the article submits with the moderator as the Author.
// If false, the submitting user id is passed along as the author.
define('ARTICLE_SUBMITS_WITH_MODERATOR_USER_ID', false);

define('ARTICLE_EDIT_RESETS_TIME', false);


////////////////////////////////////////////////////////////
// Do not modify below this line


global $event, $step, $article_delete_vars, $mem_glz_custom_fields_plugin;

$article_vars = array('note','user','email','modid','id','articleid',
		'title','title_html','body','body_html','excerpt','excerpt_html','textile_excerpt','image',
		'textile_body','keywords','status','section','category1','category2',
		'annotate','annotateinvite','override_form',
		'custom_1','custom_2','custom_3','custom_4','custom_5',
		'custom_6','custom_7','custom_8','custom_9','custom_10');
$article_delete_vars = array('user','email','articleid','title');

require_plugin('mem_moderation');

$mem_glz_custom_fields_plugin = @load_plugin('glz_custom_fields');


if (($mem_glz_custom_fields_plugin && $event == "moderate") or constant('txpinterface') == 'public')
{
	ob_start("glz_custom_fields_css_js");
	
	glz_custom_fields_before_save();
	
	if (!isset($step))
		$step = gps('step');
	
	if ( $step == "details" || $step == "article_save" || $step == 'article_post') 
	{
		// we need to make sure that all custom field values will be converted to strings first - think checkboxes
		register_callback("glz_custom_fields_before_save", "moderate", '', 1);
	}
}

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
	register_moderation_type('article-edit',$article_vars,'article_presenter','article_approver','article_rejecter');
	register_moderation_type('article-delete',$article_delete_vars,'article_presenter','article_approver','article_rejecter');

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
	return fInput('text',"custom_{$id}",$value,$class,"custom_{$id}",'',$isize,'',"customer-{$id}");
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
	global $article_delete_vars;

	$out = '';

	if (!is_array($data))
		return $out;

	extract(get_prefs());

	if ($type == 'article' || $type == 'article-edit') {

		extract(@lAtts(array(
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
			'custom_10'	=> '',
			'articleid'	=> 0
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
				tr( fLabelCell( 'override_default_form', '', 'override-form' ) . tda(form_pop($override_form,'override-form').popHelp('override_form'))) .
				tr( fLabelCell( 'author' ) . fInputCell( 'user', $user, 1, 30 ));

		for($i=1;$i<=10;$i++) {
			$k = "custom_{$i}";
			$kset = "custom_{$i}_set";
			if (isset($$kset) and !empty($$kset))
				$out .= tr(	fLabelCell( $$kset, '', "customer-{$i}" ) . 
							fInputCell( $k, $$k, 1, 30,'', "custom-{$i}" ) );
		}
		$out .= endTable();

		$out .= hInput('email',$email);
		$out .= hInput('excerpt_html',$excerpt_html);
		$out .= hInput('body_html',$body_html);
		$out .= hInput('title_html',$title_html);

		if ($type == 'article-edit')
			$out .= hInput('articleid', $articleid);

	} else if ($type=='article-delete') {
		extract($data);

		foreach ($article_delete_vars as $v) {
			$out .= @hInput($v, $$v);
		}

		$out .= '<div style="text-align:center;margin-bottom:1em;">The user "'. htmlspecialchars($user) .'" has requested the deletion of article <a href="./index.php?event=article&amp;step=edit&amp;ID='
				. $articleid.'">#'. $articleid .': "'. $title .'"</a>.</div>';
	}


	mem_glz_custom_fields_replace($data);

	return $out;
}

function article_approver($type,$data)
{
	global $txpcfg,$txp_user;

	if (!is_array($data))
		return 'invalid data';

	if ($type=='article') {
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
		
		if ( ARTICLE_SUBMITS_WITH_MODERATOR_USER_ID || !isset($user) ) {
			$user = $txp_user;
		}

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
				AuthorID        = '$user',
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
	else if ($type=='article-edit') {
		$incoming = $data;

		if (!isset($incoming['articleid']) || empty($incoming['articleid']))
			return 'Article id not provided.';

		extract(get_prefs());

		$message='';

		// remap field values
		$incoming['textile_body'] = USE_TEXTILE;
		$incoming['textile_excerpt'] = USE_TEXTILE;
		$incoming['Status'] = $incoming['status'];
		$incoming['Title'] = $incoming['title'];
		$incoming['Body'] = $incoming['body'];
		$incoming['Excerpt'] = $incoming['excerpt'];

		$oldArticle = safe_row('*','textpattern','ID = '.(int)$incoming['articleid']);

		if ($oldArticle === false)
			return 'Article '. $incoming['articleid'].' not found.';

		if (! (    ($oldArticle['Status'] >= 4 and has_privs('article.edit.published'))
				or ($oldArticle['Status'] >= 4 and $incoming['AuthorID']==$txp_user and has_privs('article.edit.own.published'))
		    	or ($oldArticle['Status'] < 4 and has_privs('article.edit'))
				or ($oldArticle['Status'] < 4 and $incoming['AuthorID']==$txp_user and has_privs('article.edit.own'))))
		{
				// Not allowed, you silly rabbit, you shouldn't even be here. 
				// Show default editing screen.
			return 'Access denied.';
		}

		$incoming = textile_main_fields($incoming, $use_textile);

		extract(doSlash($incoming));
		// use existing for defaults
		extract(doSlash($oldArticle), EXTR_SKIP);

		$Status = assert_int($Status);
		$ID = assert_int($articleid);

		if ( ARTICLE_SUBMITS_WITH_MODERATOR_USER_ID || !isset($user) ) {
			$user = $txp_user;
		}

		$Annotate = (int) $Annotate;

		if (!has_privs('article.publish') && $Status>=4) $Status = 3;

		if(ARTICLE_EDIT_RESETS_TIME) {
			$whenposted = ",Posted=now()"; 
		} else {
			$whenposted = '';
		}

		//Auto-Update custom-titles according to Title, as long as unpublished and NOT customized
		if ( empty($url_title)
			  || ( ($oldArticle['Status'] < 4) 
					&& ($oldArticle['url_title'] == $url_title ) 
					&& ($oldArticle['url_title'] == stripSpace($oldArticle['Title'],1))
					&& ($oldArticle['Title'] != $Title)
				 )
		   )
		{
			$url_title = stripSpace($Title_plain, 1);
		}

		$Keywords = doSlash(trim(preg_replace('/( ?[\r\n\t,])+ ?/s', ',', preg_replace('/ +/', ' ', $keywords)), ', '));

		$rs = safe_update("textpattern", 
		   "Title           = '$Title',
			Body            = '$Body',
			Body_html       = '$Body_html',
			Excerpt         = '$Excerpt',
			Excerpt_html    = '$Excerpt_html',
			Keywords        = '$Keywords',
			Image           = '$Image',
			Status          =  $Status,
			LastMod         =  now(),
			LastModID       = '$user',
			Section         = '$Section',
			Category1       = '$Category1',
			Category2       = '$Category2',
			Annotate        =  $Annotate,
			textile_body    =  $textile_body,
			textile_excerpt =  $textile_excerpt,
			override_form   = '$override_form',
			url_title       = '$url_title',
			AnnotateInvite  = '$AnnotateInvite',
			custom_1        = '$custom_1',
			custom_2        = '$custom_2',
			custom_3        = '$custom_3',
			custom_4        = '$custom_4',
			custom_5        = '$custom_5',
			custom_6        = '$custom_6',
			custom_7        = '$custom_7',
			custom_8        = '$custom_8',
			custom_9        = '$custom_9',
			custom_10       = '$custom_10'
			$whenposted",
			"ID = $ID"
		);

		if($Status >= 4) {
			if ($oldArticle['Status'] < 4) {
				do_pings();	
			}
			update_lastmod();
		}
		
		return ($rs ? '' : get_status_message($Status).check_url_title($url_title));
	}
	else if ($type=='article-delete') {
		// make sure the mod is allowed to delete
		if (!has_privs('article.delete'))
			return 'You lack the required privileges to moderate this article.';

		extract($data);
		
		if (isset($articleid)) {
			$id = assert_int($articleid);
			
			if (safe_delete('textpattern', "ID = $id")) {
				safe_update('txp_discuss', "visible = ".MODERATE, "parentid = {$id}");
			} else {
				return 'failed to delete article';
			}
		} else {
			return 'invalid article id';
		}
	}	
	else {
		return "invalid type {$type}";
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

function mem_article_delete_sentry($atts,$thing='')
{
	global $txp_user,$ign_user,$mem_mod_article_delete_requested;
	
	extract(lAtts(array(
		'successmsg'	=> 'Submitted request',
		'failuremsg'	=> 'Failed to submit request',
		'wraptag'	=> '',
		'class'		=> ''
	),$atts));
	
	if (empty($txp_user))
		$txp_user = @$ign_user;
	
	$hash = gps('h');
	$articleid = gps('articleid');
	$request = gps('request_article_deletion');
	
	if (empty($hash) || empty($articleid) || empty($request))
		return '';
	
	// get the article title for the hash
	$title = safe_field('Title','textpattern',"ID = " . assert_int($articleid));	
	$nhash = md5($articleid.$title);
	
	if ( $nhash == $hash && $request == '1' ) {
		$mem_mod_article_delete_requested = true;
		$data = array(
			'articleid'	=> $articleid,
			'title'	=> $title,
			'user'	=> $txp_user
			);

		// prevent duplicates
		if (!safe_count('txp_moderation',"data = '{$encoded_data}'"))
			$res = submit_moderated_content('article-delete','','article-delete: #'.$articleid.' - '.$title,$data);
		
		if ($res)
			return doTag($successmsg, $wraptag, $class);
		else
			return doTag($failuremsg, $wraptag, $class);
	}

	return '';
}

function mem_article_action_link($atts,$thing='')
{
	global $thisarticle,$mem_mod_article_delete_requested;

	extract(lAtts(array(
		'action'	=> 'delete',
		'text'	=> '',
		'url'	=> '',
		'mode'	=> '',
		'prompt'	=> '',
	),$atts));

	if (empty($text)) $text = mem_moderation_gTxt($action);
	$action = strtolower($action);

	if (empty($url)) 
		$url = $_SERVER['REQUEST_URI'];
	else 
		$url = hu.ltrim($url, '/');

	$url .= (strpos($url,'?') === FALSE ? '?' : '&');

	if ($action == 'delete') {
		if (empty($prompt)) $prompt = 'Are you sure that you want to delete this article?';

		if ($mem_mod_article_delete_requested === true)
			return '';

		$url .= 'articleid='.$thisarticle['thisid'].'&request_article_deletion=1&h=' . md5($thisarticle['thisid'].$thisarticle['title']);
	}
	else if ($action == 'edit') {
		$url .= 'articleid='.$thisarticle['thisid'];
	}

	if ($mode == 'url_only')
		$out = $url;
	else if ($mode == 'url_encoded')
		$out = urlencode($url);
	else if ($mode == 'url_escaped')
		$out = htmlspecialchars($url);
	else {
		$out = '<a href="'.htmlspecialchars($url).
			(!empty($prompt) ? "\" onclick=\"javascript:return confirm('{$prompt}');\"" : '').
			'">'. htmlspecialchars($text).'</a>';
	}

	return $out;
}

function mem_if_owns_article($atts,$thing='')
{
	global $thisarticle, $txp_user, $ign_user;
	
	extract(lAtts(array(
		'useridfield'	=> 'AuthorID',
	),$atts));
	
	if (empty($txp_user))
		$txp_user = $ign_user;

	$cond = is_array($thisarticle) and isset($thisarticle[$articlefield]) and $thisarticle[$articlefield] == $txp_user;
	return EvalElse($thing,$cond);
}


function mem_custom_user_article_list($atts, $thing='')
{
	global $pretext, $prefs, $txpcfg,$txp_user,$ign_user;
	
	extract($pretext);
	extract($prefs);
	$customFields = getCustomFields();
	$customlAtts = array_null(array_flip($customFields));

	$userid = isset($txp_user) ? @$txp_user : @$ign_user;

	//getting attributes
	$theAtts = lAtts(array(
		'useridfield'	=> '',
		'form'      => 'default',
		'limit'     => 10,
		'offset'	=> 0,
		'pageby'    => '',
		'category'  => '',
		'section'   => '',
		'author'    => '',
		'sort'      => '',
		'time'      => 'past',
		'month'		=> '',
		'status'    => '4',
	)+$customlAtts,$atts);

	extract($theAtts);

	$pageby = (empty($pageby) ? $limit : $pageby);

	// treat sticky articles differently wrt search filtering, etc
	if (!is_numeric($status))
		$status = getStatusNum($status);
	$issticky = ($status == 5);


	$match = $search = '';
	if (!$sort) $sort='Posted desc';


	//Building query parts
	$category  = join("','", doSlash(do_list($category)));
	$category  = (!$category)  ? '' : " and (Category1 IN ('".$category."') or Category2 IN ('".$category."'))";
	$section   = (!$section)   ? '' : " and Section IN ('".join("','", doSlash(do_list($section)))."')";
	$author    = (!$author)    ? '' : " and AuthorID IN ('".join("','", doSlash(do_list($author)))."')";
	$month     = (!$month)     ? '' : " and Posted like '".doSlash($month)."%'";

	switch ($time) {
		case 'any':
			$time = ""; break;
		case 'future':
			$time = " and Posted > now()"; break;
		default:
			$time = " and Posted <= now()";
	}

	$custom = '';

	if ($customFields) {
		foreach($customFields as $cField) {
			if (isset($atts[$cField]))
				$customPairs[$cField] = $atts[$cField];
		}
		if(!empty($customPairs)) {
			$custom = buildCustomSql($customFields,$customPairs);
		}
	}

	if ($useridfield)
	{
		$custom .= ' and '. $useridfield ."='{$userid}' ";
	}

	$statusq = ' and Status = '.intval($status);

	$where = "1=1" . $statusq. $time.
		$category . $section . $month . $author . $custom ;

	$pgoffset = $offset;

	$rs = safe_rows_start("*, unix_timestamp(Posted) as uPosted".$match, 'textpattern',
	$where.' order by '.doSlash($sort).' limit '.intval($pgoffset).', '.intval($limit));

	$fname = $form;

	if ($rs) {
		$count = 0;

		$articles = array();
		while($a = nextRow($rs)) {
			++$count;
			populateArticleData($a);
			global $thisarticle, $uPosted, $limit;
			$thisarticle['is_first'] = ($count == 1);
			$thisarticle['is_last'] = ($count == numRows($rs));
			
			//callback_event('article_entry','list');

			if (isset($GLOBALS['thisarticle'])) {
				$articles[] = !empty($thing) ? parse($thing) : parse_form($fname);

				// sending these to paging_link(); Required?
				$uPosted = $a['uPosted'];
			}

			unset($GLOBALS['thisarticle']);
		}

		return join('',$articles);
	}
}

//////////////////////////////////////////////////////
// Public form methods



register_callback('mem_mod_article_form_defaults', 'mem_form.defaults');
register_callback('mem_mod_article_form_display', 'mem_form.display');
register_callback('mem_mod_article_form_submitted', 'mem_form.submit');

function mem_mod_article_form_defaults()
{
	global $mem_form_type, $mem_form_default, $mem_mod_info, $mem_modarticle_info;
	
	if ($mem_form_type!='mem_moderation_article')
		return;
	
	extract(gpsa(array('modid','articleid')));
	
	if (!empty($modid)) {
	
		$mem_mod_info = safe_row('*','txp_moderation',"`id`='".doSlash($modid)."'");
	
		if ($mem_mod_info) {
			$mem_modarticle_info = decode_content($mem_mod_info['data']);
		}
	}
	else if (!empty($articleid)) {
		$rs = safe_row('*', 'textpattern',"`id`='".doSlash($articleid)."'");
	
		$mem_modarticle_info = array();
		
		foreach($rs as $k => $v) {
			$mem_modarticle_info[strtolower($k)] = $v;
		}
	}
	
	if (is_array($mem_modarticle_info)) {
		foreach($mem_modarticle_info as $key => $val) {
			$key = mem_form_label2name($key);
			$mem_form_default[$key] = $val;
		}
	}
}

function mem_mod_article_form_display()
{
	global $mem_form_type, $mem_form_labels, $mem_form_values, $mem_mod_info, $mem_modarticle_info,
		$mem_glz_custom_fields_plugin;

	if ($mem_form_type!='mem_moderation_article')
		return;
	
	$out = '';

	if ($mem_glz_custom_fields_plugin) {
		ob_start();
		mem_glz_custom_fields_replace($mem_modarticle_info);
		$out .= ob_get_contents();
		ob_end_clean();
	}
	

	if (isset($mem_mod_info)) {
		$out .= n.'<input type="hidden" name="modid" value="'.htmlspecialchars($mem_mod_info['id']).'" />'.
			n.'<input type="hidden" name="type" value="'. htmlspecialchars($mem_mod_info['type']).'" />';

		if ($mem_mod_info['type'] == 'article-edit' && isset($mem_modarticle_info['articleid']))
			$out .= n.'<input type="hidden" name="articleid" value="'.$mem_modarticle_info['articleid'].'" />';
		
		mem_form_store('modid', 'modid', $mem_mod_info['id']);
		mem_form_store('type', 'type', $mem_mod_info['type']);
	}
	else if (isset($mem_modarticle_info)) {
		$out .= n.'<input type="hidden" name="articleid" value="'.htmlspecialchars($mem_modarticle_info['articleid']).'" />'.
			n.'<input type="hidden" name="type" value="article" />';
	}
	
	return $out;
}

function mem_mod_article_form_submitted()
{
	global $mem_form_type, $txp_user, $ign_user, $mem_mod_info, $mem_modarticle_info, $mem_form_thanks_form;
	
	if ($mem_form_type!='mem_moderation_article')
		return;
		
	extract(gpsa(array('modid','step','id','articleid')));
	$vars=array('note','user','email','articleid',
		'title','title_html','body','body_html','excerpt','excerpt_html','textile_excerpt','image',
		'textile_body','keywords','status','section','category1','category2',
		'annotate','annotateinvite','override_form',
		'custom_1','custom_2','custom_3','custom_4','custom_5',
		'custom_6','custom_7','custom_8','custom_9','custom_10'	);

	$mem_modarticle_info = gpsa($vars);

	$out = '';
	
	$is_save = ps('mem_moderation_save');
	$is_delete = ps('mem_moderation_delete');
	$is_update = ps('mem_moderation_update') || ($is_save && ps('modid'));

	if (isset($ign_user)) $txp_user = $ign_user;

	if (!empty($articleid)) {
		$articleid = doSlash($articleid);
		$rs = safe_rows("*, unix_timestamp(Posted) as uPosted","textpattern","`ID` = $articleid");

		if ($rs) {
			// merge the passed in values to the existing
			foreach($mem_modarticle_info as $key => $val) {
				$rs[$key] = $val;
			}
			$rs['articleid'] = $articleid;
			$res = submit_moderated_content('article-edit', $user_email, $mem_modarticle_info['note'], $mem_modarticle_info);
		}
	}
	else {

		if (!empty($modid)) $id = $modid;
		
		if (isset($id)) $mem_modarticle_info['id'] = $id;
		
		
		if ($is_delete) {
			if (remove_moderated_content($modid))
				$res = mem_moderation_gTxt('article_deleted');
			else
				$res = mem_moderation_gTxt('article_delete_failed');
		} 
		else if ($is_update) {
	
			if (isset($mem_modarticle_info['note'])) 
				$note = $mem_modarticle_info['note'];
			else
				$note = $mem_mod_info['desc'];
	
			if (update_moderated_content($id,$note,$mem_modarticle_info))
				$res = mem_moderation_gTxt('article_updated');
			else
				$res = mem_moderation_gTxt('article_update_failed');
		}
		else {
			if (!isset($user))
				$mem_modarticle_info['user'] = $txp_user;
				
			$res = submit_moderated_content('article', '', $mem_modarticle_info['note'], $mem_modarticle_info);
		}
	}
	
	$mem_modarticle_info['result'] = $res;
	
	
	$thanks_form = @fetch_form($mem_form_thanks_form);
	
	if (!empty($thanks_form))
		$out = parse($thanks_form);
	
	unset($mem_modarticle_info);

	return $out;
}



if ($mem_glz_custom_fields_plugin)
{
	// -------------------------------------------------------------
	// replaces the default custom fields under write tab
	function mem_glz_custom_fields_replace($data=array())
	{
		if(is_array($data))
			extract($data);

		// get all custom fields & keep only the ones which are set
		$arr_custom_fields = array_filter(glz_custom_fields_MySQL("all"), "glz_check_custom_set");
	
		//DEBUG
		//dmp($arr_custom_fields);
	
		if ( is_array($arr_custom_fields) && !empty($arr_custom_fields) )
		{
			// get all custom fields values for this article
			$arr_article_customs = array();mem_glz_article_custom_fields( glz_get_article_id(), $arr_custom_fields);
			if ( is_array($arr_article_customs) ) extract($arr_article_customs);
	
			// let's initialize our output
			$out = array();
	
			// let's see which custom fields are set
			foreach ( $arr_custom_fields as $custom_field )
			{
				// get all possible/default value(s) for this custom field from custom_fields table
				$arr_custom_field_values = glz_custom_fields_MySQL("values", $custom_field['custom_set'], '', array('custom_set_name' => $custom_field['custom_set_name']));
	
				//DEBUG
				//dmp($arr_custom_field_values);
	
				//custom_set without "_set" e.g. custom_1_set => custom_1
				$custom_set = glz_custom_number($custom_field['custom_set']);

				// if current article holds no value for this custom field, make it empty
				$custom_value = ( !empty($$custom_set) ) ? $$custom_set : '';
	
				// the way our custom field value is going to look like
				switch ( $custom_field['custom_set_type'] )
				{
					case "text_input":
						$custom_set_value = fInput("text", $custom_set, $custom_value, "edit", "", "", "22", "", $custom_set);
						break;
	
					case "select":
						$custom_set_value = glz_selectInput($custom_set, $arr_custom_field_values, $custom_value, 1);
						break;
	
					case "checkbox":
							$custom_set_value = glz_checkbox($custom_set, $arr_custom_field_values, $custom_value);
						break;
	
					case "radio":
						$custom_set_value = glz_radio($custom_set, $arr_custom_field_values, $custom_value);
						break;
	
					// if none of the custom_set_types fit - WHICH HINTS TO A BUG - text input is default
					default:
						//$custom_set_value = fInput("text", $custom_set, $$custom_set, "edit", "", "", "22", "", $custom_set);
						$custom_set_value = 'Type not supported yet.';
						break;
				}
	
				/*
				$out .= graf(
					"<label for=\"$custom_set\">{$custom_field['custom_set_name']}</label><br />$custom_set_value"
				);*/
				
				if (constant('txpinterface')=='admin')
					$out[str_replace('_','-',$custom_set)] = $custom_set_value;
				else
					$out[$custom_set] = $custom_set_value;
			}
			//DEBUG
			//dmp($out);
	
			echo
			'<script type="text/javascript">
			<!--//--><![CDATA[//><!--
	
			$(document).ready(function() {
			';
			
			foreach($out as $k=>$v) {
				echo '
					$("#'.$k.'").after(\''.$v.'\').remove();
					';
			}
			
			echo '
			});
			//--><!]]>
			</script>';
			
			
		}
	}

	// fetch the custom field values from txp_moderation table
	function mem_glz_article_custom_fields($name, $extra)
	{
		global $mem_modarticle_info;
		
		if ( is_array($extra) )
		{
			$arr_article_customs = array();

			// see what custom fields we need to query for
			foreach ( $extra as $custom_set )
			{
				$select = glz_custom_number($custom_set['custom_set']);
				
				$arr_article_customs[$select] = $mem_modarticle_info[$select];
			}
	
			return $arr_article_customs;
		}
		else
		{
			trigger_error(glz_custom_fields_gTxt('not_specified', array('{what}' => "extra attributes")));
		}
}

} // if mem_glz_custom_fields

# --- END PLUGIN CODE ---

?>
