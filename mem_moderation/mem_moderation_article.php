<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_moderation_article';

$plugin['version'] = '0.7.1';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Moderation plugin that allows articles to be submitted to the moderation queue.';
$plugin['type'] = 1; // 0 for regular plugin; 1 if it includes admin-side code

@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h1(title). mem_moderation_article plugin

h2(section summary). Summary

p. This moderation plugin allows users to submit articles in to the moderation queue.

h2(section contact). Author Contact

"Michael Manfre":mailto:mmanfre@gmail.com?subject=Textpattern%20mem_moderation_article%20plugin
"http://manfre.net":http://manfre.net

h2(section license). License

p. This plugin is licensed under the "GPLv2":http://www.fsf.org/licensing/licenses/info/GPLv2.html.

h2(section installation). Installation

p. "Start Install Wizard":./index.php?event=article_moderate&step=preinstall

h2(section tags). Tags


* "mem_if_owns_article":#mem_if_owns_article
* "mem_article_action_link":#mem_article_action_link
* "mem_article_delete_sentry":#mem_article_delete_sentry
* "mem_custom_user_article_list":#mem_custom_user_article_list
* "mem_moderation_article_form":#mem_moderation_article_form
* "mem_gps_category":#mem_gps_category
* "mem_gps_section":#mem_gps_section
* "mod_article_form":#mod_article_form _(deprecated)_
* "mod_custom_input":#mod_custom_input _(deprecated)_
* "mod_title_input":#mod_title_input _(deprecated)_
* "mod_image_input":#mod_image_input _(deprecated)_
* "mod_keywords_input":#mod_keywords_input _(deprecated)_
* "mod_body_input":#mod_body_input _(deprecated)_
* "mod_body_html_input":#mod_body_html_input _(deprecated)_
* "mod_excerpt_input":#mod_excerpt_input _(deprecated)_
* "mod_excerpt_html_input":#mod_excerpt_html_input _(deprecated)_


h3(tag#mem_if_owns_article). mem_if_owns_article

p(tag-summary). Conditionally parse enclosed text based upon if the current user is the creater of the displayed article.

*(atts) %(atts-name)useridfield% %(atts-type)string% Name of the table field holding the user id. Default is "AuthorID".

h3(tag#mem_article_action_link). mem_article_action_link

p(tag-summary). Output a url (or link) for the current article that can be used by the delete sentry. Works with any tag supporting $thisarticle.

*(atts) %(atts-name)action% %(atts-type)string% Action that is encoded in to the url. Default is delete.
* %(atts-name)text% %(atts-type)string% Link text value.
* %(atts-name)url% %(atts-type)string% URL to sentry or other handler, relative to site root.
* %(atts-name)mode% %(atts-type)string% Mode for generating output. url_only = raw URL, url_encoded = encoded URL, url_escaped = html escaped URL, link = HTML a tag. Default is link.
* %(atts-name)prompt% %(atts-type)string% Javascript prompt text.

h3(tag#mem_article_delete_sentry). mem_article_delete_sentry

p(tag-summary). Listens for and validates delete links and adds moderation item to queue.

*(atts) %(atts-name)successmsg% %(atts-type)string% Message to show on success.
* %(atts-name)failuremsg% %(atts-type)string% Message to show on failure.
* %(atts-name)wraptag% %(atts-type)string% HTML tag to wrap message.
* %(atts-name)class% %(atts-type)string% CSS class for wraptag.

h3(tag#mem_custom_user_article_list). mem_custom_user_article_list

p(tag-summary). Article list (same as article_custom), except has the ability to filters based upon a user id being stored in another database field.

*(atts) %(atts-name)useridfield% %(atts-type)string% DB Field that holds the user id.
* %(atts-name)form% %(atts-type)string% Form containing article tags.
* %(atts-name)limit% %(atts-type)int% Max number of articles in list.
* %(atts-name)offset% %(atts-type)int% Number of articles to skip.
* %(atts-name)pageby% %(atts-type)int% Number of articles per page.
* %(atts-name)category% %(atts-type)string% Restricts articles to those that have category1 or category2 matching value.
* %(atts-name)section% %(atts-type)string% Restricts to articles matching section.
* %(atts-name)author% %(atts-type)string% Restricts to articles matching author.
* %(atts-name)sort% %(atts-type)string% SQL sort field and direction.
* %(atts-name)time% %(atts-type)string% Article timeframe. any = include all articles, future = Posted > now(), default = Posted < now()
* %(atts-name)month% %(atts-type)string% Restrict to articles posted during the month of.
* %(atts-name)status% %(atts-type)string% Restrict to articles matching status

h3(tag#mem_moderation_article_form). mem_moderation_article_form

p(tag-summary). mem_form helper tag for article edits. See mem_form for tag attributes and usage.

h3(tag#mod_article_form). mod_article_form _(deprecated)_

p(tag-summary). This client side tag will place a form for submitting and re-editing submitted articles.

*(atts) %(atts-name)form% %(atts-type)string% The txp form that holds the layout tags.
* %(atts-name)successform% %(atts-type)string% The txp form that holds the template to show to the user on a successful form submission (for either save, edit, or delete).


h3(tag#mod_custom_input). mod_custom_input _(deprecated)_

p(tag-summary). Display a text input field for custom input fields.

*(atts) %(atts-name)class% %(atts-type)string% The CSS class name to associate with the outputted HTML tag
* %(atts-name)isize% %(atts-type)int% The size of the input field.
* %(atts-name)id% %(atts-type)int% The field id this tag represents. E.g. id="1" matches field custom_1


h3(tag#mod_title_input). mod_title_input _(deprecated)_

p(tag-summary). Display a text input field for the title.

*(atts) %(atts-name)class% %(atts-type)string% The CSS class name to associate with the outputted HTML tag
* %(atts-name)isize% %(atts-type)int% The size of the input field.


h3(tag#mod_image_input). mod_image_input _(deprecated)_

p(tag-summary). Display a text input field for the article image field.

*(atts) %(atts-name)class% %(atts-type)string% The CSS class name to associate with the outputted HTML tag
* %(atts-name)isize% %(atts-type)int% The size of the input field.

h3(tag#mod_keywords_input). mod_keywords_input _(deprecated)_

p(tag-summary). Display a text input field for the keywords.

*(atts) %(atts-name)class% %(atts-type)string% The CSS class name to associate with the outputted HTML tag
* %(atts-name)isize% %(atts-type)int% The size of the input field.

h3(tag#mod_body_input). mod_body_input _(deprecated)_

p(tag-summary). Display a textarea field for the body.

*(atts) %(atts-name)class% %(atts-type)string% The CSS class name to associate with the outputted HTML tag
* %(atts-name)style% %(atts-type)string% The CSS style attribute to apply to the input field.

h3(tag#mod_body_html_input). mod_body_html_input _(deprecated)_

p(tag-summary). Display a textarea field for the body_html.

*(atts) %(atts-name)class% %(atts-type)string% The CSS class name to associate with the outputted HTML tag
* %(atts-name)style% %(atts-type)string% The CSS style attribute to apply to the input field.

h3(tag#mod_excerpt_input). mod_excerpt_input _(deprecated)_

p(tag-summary). Display a textarea field for the excerpt.

*(atts) %(atts-name)class% %(atts-type)string% The CSS class name to associate with the outputted HTML tag
* %(atts-name)style% %(atts-type)string% The CSS style attribute to apply to the input field.

h3(tag#mod_excerpt_html_input). mod_excerpt_html_input _(deprecated)_

p(tag-summary). Display a textarea field for the excerpt_html.

*(atts) %(atts-name)class% %(atts-type)string% The CSS class name to associate with the outputted HTML tag
* %(atts-name)style% %(atts-type)string% The CSS style attribute to apply to the input field.

h3(tag#mod_note_input). mod_note_input _(deprecated)_

p(tag-summary). Display a textarea field for the note (to the moderators)

*(atts) %(atts-name)class% %(atts-type)string% The CSS class name to associate with the outputted HTML tag
* %(atts-name)style% %(atts-type)string% The CSS style attribute to apply to the input field.

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

////////////////////////////////////////////////////////////
// Plugin 
// Author: Michael Manfre (http://manfre.net/)
////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////
// Do not modify below this line

require_plugin('mem_moderation');


global $event, $step, $prefs, $mem_article_vars, $mem_article_delete_vars, $mem_glz_custom_fields_plugin, $mem_moderation_lang;

$mem_moderation_lang = array_merge($mem_moderation_lang, array(
	'mem_mod_pub_bypass_queue'	=>	'Publishers bypass queue delay?',
	'mem_mod_article_edit_resets_time'	=>	'Edit resets article time?',
	'mem_mod_article_submit_with_moderator_id'	=>	'Substitute approver as article editor?',
));

// article/article-edit vars
$mem_article_vars = array('note','user','email','modid','id','articleid',
		'title','title_html','body','body_html','excerpt','excerpt_html','textile_excerpt','image',
		'textile_body','keywords','status','section','category1','category2',
		'annotate','annotateinvite','override_form',
		'custom_1','custom_2','custom_3','custom_4','custom_5',
		'custom_6','custom_7','custom_8','custom_9','custom_10');
// article-delete vars
$mem_article_delete_vars = array('user','email','articleid','title');

// load glz_custom_fields if installed
$mem_glz_custom_fields_plugin = @load_plugin('glz_custom_fields');

if ($mem_glz_custom_fields_plugin && ($event == "moderate" or txpinterface == 'public'))
{
	// glz may add custom fields. Add var names to known list
	$g_fields = array_filter(glz_custom_fields_MySQL("all"), "glz_check_custom_set");
	
	foreach ( $g_fields as $custom_field )
	{
		$mem_article_vars[] = @glz_custom_number($custom_field['custom_set']);
	}
	
	$mem_article_vars = array_unique($mem_article_vars);	

	// should css/js get inlined?
	if (@$prefs['mem_mod_article_use_glz_custom_css'])
	{
		ob_start("glz_custom_fields_css_js");
	}
	
	// process custom fields
	glz_custom_fields_before_save();
	
	// step not auto-set on public side
	if (!isset($step))
	{
		$step = gps('step');
	}
	
	if ( $step == "details" || $step == "article_save" || $step == 'article_post') 
	{
		// we need to make sure that all custom field values will be converted to strings first - think checkboxes
		register_callback("glz_custom_fields_before_save", "moderate", '', 1);
	}
}

// Include the article page when safe to do so
if ((@txpinterface=='admin' and ($event=='moderate' or $event=='article_moderate')) or @txpinterface!='admin')
{
	require_once(txpath.'/include/txp_article.php');
}

/** Show a category select list for presenter */
function mem_category_popup($name,$val) 
{
	$rs = getTree("root",'article');
	if ($rs)
	{
		return treeSelectInput($name,$rs,$val);
	}
	return false;
}


if (@txpinterface == 'admin') 
{
	// give publisher install access
	add_privs('article_moderate','1');
	
	// register event with txp
	register_callback('article_moderate','article_moderate','', 1);
	// wire up moderation types
	mem_moderation_register('article',$mem_article_vars,'mem_article_presenter','mem_article_approver','mem_article_rejecter');
	mem_moderation_register('article-edit',$mem_article_vars,'mem_article_presenter','mem_article_approver','mem_article_rejecter');
	mem_moderation_register('article-delete',$mem_article_delete_vars,'mem_article_presenter','mem_article_approver','mem_article_rejecter');

	if ($event=='article_moderate') 
	{
		/** Event handler */
		function article_moderate($event, $step) 
		{
			$msg='';

			if ($step=='article_save' or $step=='article_post') 
			{
				// save changes
				$msg = article_save($step);
			}
			else if ($step=='preinstall' or $step=='install') 
			{
				// need tab during install
				register_tab('extensions','article_moderate','article_moderate');
				echo pageTop('Article Moderation','');

				echo article_install();
				
				return;
			}
			else
			{
				trigger_error(gTxt('unknown_step'));
			}

			pageTop('article Moderation',$msg);
		}
		
		/** Install Default Forms, prefs, etc. */
		function article_install() 
		{
			global $prefs;

			$log = array();

			// default article edit form
			$form = fetch('Form','txp_form','name','mod_article_form');
			if (!$form) 
			{
				$form_html = <<<EOF
<txp:mem_form_text name="title" label="Title" />

<txp:mem_form_select_category name="category1" label="Category" />

<txp:mem_form_textarea name="body" label="Body" />

<txp:mem_form_textarea name="note" label="Notes for the Moderator (optional)" required="0" />

<!-- this will put the submitting username in to custom field 1 -->
<txp:mem_form_hidden name="custom_1" value='<txp:mem_profile var="name" />' />

<div><txp:mem_form_submit /></div>
EOF;
				$form_html = doSlash($form_html);
				if (safe_insert('txp_form',"name='mod_article_form',type='misc',Form='{$form_html}'"))
					$log[] = "Created form 'mod_article_form'";
				else
					$log[] = "Failed to create form 'mod_article_form'. " . mysql_error();
			}
			else
			{
				$log[] = "Found form 'mod_article_form'. Skipping installation of default form.";
			}

			// default article success form
			$form = fetch('Form','txp_form','name','mod_article_success');
			if (!$form) 
			{
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
			}
			else
			{
				$log[] = "Found form 'mod_article_success'. Skipping installation of default form.";
			}

			if (!isset($prefs['mem_mod_article_edit_resets_time']))
			{
				set_pref('mem_mod_article_edit_resets_time', '0', 'mem_moderation', 1, 'yesnoradio');
				$log[] = "Set preference mem_mod_article_edit_resets_time";
			}
			if (!isset($prefs['mem_mod_article_use_glz_custom_css']))
			{
				set_pref('mem_mod_article_use_glz_custom_css', '0', 'mem_moderation', 1, 'yesnoradio');
				$log[] = "Set preference mem_mod_article_use_glz_custom_css";
			}
			if (!isset($prefs['mem_mod_article_submit_with_moderator_id']))
			{
				set_pref('mem_mod_article_submit_with_moderator_id', '0', 'mem_moderation', 1, 'yesnoradio');
				$log[] = "Set preference mem_mod_article_submit_with_moderator_id";
			}


			return tag("Install Log",'h2').doWrap($log,'ul','li');
		}
	} // if article_moderate
} // if admin

/** The article form tag. Handles all logic. Deprecated */
function mod_article_form($atts,$thing='')
{
	trigger_error(gTxt('deprecated_function_with', array('{name}' => __FUNCTION__, '{with}' => 'mem_moderation_article_form')), E_USER_NOTICE);

	global $step,$txp_user,$ign_user,$mem_mod_info,$mem_modarticle_info, $mem_article_vars;
	
	extract(gpsa(array('modid','action','event','step','id')));
	

	if (!empty($modid)) $id = $modid;

	if (isset($ign_user)) $txp_user = $ign_user;
	
	extract(lAtts(array(
		'form'		=> 'mod_article_form',
		'successform'	=> 'mod_article_success'
	),$atts));

	$mem_modarticle_info = gpsa($mem_article_vars);
	if (isset($id))
		$mem_modarticle_info['id'] = $id;
	extract($mem_modarticle_info);
	$out = '';

	if (isset($action) and $action==gTxt( 'delete' ))
		$step = 'article_delete';

	if (isset($step) and ($step=='article_save' or $step=='article_update' or $step=='article_delete')) 
	{
		if (!empty($successform))
			$Form = fetch_form($successform);

		if ($step=='article_delete') 
		{
			if (remove_moderated_content($modid)) {
				$msg = 'Deleted article';
			} else {
				$msg = 'Failed to delete article image';
			}
		}
		else if ($step=='article_update') 
		{
			if (update_moderated_content($id,$note,$mem_modarticle_info)) {
				$res = 'Updated Article';
			} else {
				$res = 'Failed to update Article';
			}
		}
		else 
		{
			if (!isset($user))
				$mem_modarticle_info['user'] = $txp_user;
			
			$res = submit_moderated_content('article','',$mem_modarticle_info['note'],$mem_modarticle_info);
		}

		$out = admin_parse($Form);

		unset($mem_modarticle_info);
	}
	else
	{
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

		if (isset($modid) and !empty($modid))
		{
			$out .= hInput('modid',$modid);

			$mem_mod_info = safe_row('*','txp_moderation',"`id`='".doSlash($modid)."'");

			extract($mem_modarticle_info=mem_moderation_decode($mem_mod_info['data']));
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

		foreach ($vals as $a=>$b)
		{
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

function mod_custom_input($atts) 
{
	trigger_error(gTxt('deprecated_function_with', array('{name}' => __FUNCTION__, '{with}' => 'mem_form_text')), E_USER_NOTICE);
	global $mem_modarticle_info;
	extract(lAtts(array(
		'id'	=>	'1',
		'isize'	=>	25,
		'class'	=>	__FUNCTION__
	),$atts));
	$title = $value = $mem_modarticle_info["custom_{$id}"];
	return fInput('text',"custom_{$id}",$value,$class,"custom_{$id}",'',$isize,'',"customer-{$id}");
}
function mod_title_input($atts) 
{
	trigger_error(gTxt('deprecated_function_with', array('{name}' => __FUNCTION__, '{with}' => 'mem_form_text')), E_USER_NOTICE);
	global $mem_mod_info,$mem_modarticle_info;
	extract(lAtts(array(
		'isize'	=>	25,
		'class'	=>	__FUNCTION__
	),$atts));
	$title = $value = $mem_modarticle_info["title"];
	return fInput('text',"title",$value,$class,'title','',$isize);
}
function mod_image_input($atts) 
{
	trigger_error(gTxt('deprecated_function_with', array('{name}' => __FUNCTION__, '{with}' => 'mem_form_text')), E_USER_NOTICE);
	global $mem_mod_info,$mem_modarticle_info;
	extract(lAtts(array(
		'isize'	=>	25,
		'class'	=>	__FUNCTION__
	),$atts));
	$title = $value = $mem_modarticle_info["image"];
	return fInput('text',"image",$value,$class,'image','',$isize);
}
function mod_keywords_input($atts) 
{
	trigger_error(gTxt('deprecated_function_with', array('{name}' => __FUNCTION__, '{with}' => 'mem_form_text')), E_USER_NOTICE);
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
function mod_body_input($atts) 
{
	trigger_error(gTxt('deprecated_function_with', array('{name}' => __FUNCTION__, '{with}' => 'mem_form_textarea')), E_USER_NOTICE);
	global $mem_mod_info,$mem_modarticle_info;
	extract(lAtts(array(
		'style'	=>	'',
		'class'	=>	__FUNCTION__
	),$atts));
	return '<textarea name="body"'.(!empty($style)?' style="'.$style.'"':'').
			(!empty($class)?' class="'.$class.'"':'').'>'.
			htmlspecialchars($mem_modarticle_info['body']).'</textarea>';
}
function mod_body_html_input($atts) 
{
	trigger_error(gTxt('deprecated_function_with', array('{name}' => __FUNCTION__, '{with}' => 'mem_form_textarea')), E_USER_NOTICE);
	global $mem_mod_info,$mem_modarticle_info;
	extract(lAtts(array(
		'style'	=>	'',
		'class'	=>	__FUNCTION__
	),$atts));
	return '<textarea name="body_html"'.(!empty($style)?' style="'.$style.'"':'').
			(!empty($class)?' class="'.$class.'"':'').'>'.
			htmlspecialchars($mem_modarticle_info['body_html']).'</textarea>';
}
function mod_excerpt_input($atts) 
{
	trigger_error(gTxt('deprecated_function_with', array('{name}' => __FUNCTION__, '{with}' => 'mem_form_textarea')), E_USER_NOTICE);
	global $mem_mod_info,$mem_modarticle_info;
	extract(lAtts(array(
		'style'	=>	'',
		'class'	=>	__FUNCTION__
	),$atts));
	return '<textarea name="excerpt"'.(!empty($style)?' style="'.$style.'"':'').
			(!empty($class)?' class="'.$class.'"':'').'>'.
			htmlspecialchars($mem_modarticle_info['excerpt']).'</textarea>';
}
function mod_excerpt_html_input($atts) 
{
	trigger_error(gTxt('deprecated_function_with', array('{name}' => __FUNCTION__, '{with}' => 'mem_form_textarea')), E_USER_NOTICE);
	global $mem_mod_info,$mem_modarticle_info;
	extract(lAtts(array(
		'style'	=>	'',
		'class'	=>	__FUNCTION__
	),$atts));
	return '<textarea name="excerpt_html"'.(!empty($style)?' style="'.$style.'"':'').
			(!empty($class)?' class="'.$class.'"':'').'>'.
			htmlspecialchars($mem_modarticle_info['excerpt_html']).'</textarea>';
}

/** Article presenter for article* moderation types */	
function mem_article_presenter($type,$data) 
{
	global $mem_article_delete_vars, $mem_glz_custom_fields_plugin, $mem_glz_custom_fields_plugin;

	$out = '';

	if (!is_array($data))
	{
		// no data, no form
		return '';
	}

	extract(get_prefs());

	// article and edit actions
	if ($type == 'article' || $type == 'article-edit') 
	{
		// defaults
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
			'section'	=> getDefaultSection(),
			'category1'	=> '',
			'category2'	=> '',
			'annotate'	=> ($comments_on_default==1 ? 1 : 0),
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
			'custom_11'	=> '',
			'custom_12'	=> '',
			'custom_13'	=> '',
			'custom_14'	=> '',
			'custom_15'	=> '',
			'articleid'	=> 0
		),$data));

		// populate section select
		$rs = safe_rows("name, title", "txp_section", "name!='default'");
		$section_values = array();
		foreach($rs as $r)
		{
			$section_values[$r['name']] = $r['title'];
		}
		$section_select = selectInput("section", $section_values, $section);

		// show article edit table
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

		// add up to 15 custom field forms
		for($i=1;$i<=15;$i++) {
			$k = "custom_{$i}";
			$kset = "custom_{$i}_set";
			if (isset($$kset) and !empty($$kset))
			{
				$out .= tr(	fLabelCell( $$kset, '', "customer-{$i}" ) . 
							fInputCell( $k, $$k, 1, 30,'', "custom-{$i}" ) );
			}
		}
		$out .= endTable();

		// hidden form fields
		$out .= hInput('email',$email);
		$out .= hInput('excerpt_html',$excerpt_html);
		$out .= hInput('body_html',$body_html);
		$out .= hInput('title_html',$title_html);

		if ($type == 'article-edit')
		{
			$out .= hInput('articleid', $articleid);
		}

	} 
	else if ($type=='article-delete') 
	{
		extract($data);

		// pass through data
		foreach ($mem_article_delete_vars as $v) 
		{
			$out .= @hInput($v, $$v);
		}

		// link to article on write tab
		$out .= '<div style="text-align:center;margin-bottom:1em;">The user "'. htmlspecialchars($user) .'" has requested the deletion of article <a href="./index.php?event=article&amp;step=edit&amp;ID='
				. $articleid.'">#'. $articleid .': "'. $title .'"</a>.</div>';
	}

	// allow glz to replace the custom fields
	if ($mem_glz_custom_fields_plugin)
	{
		mem_glz_custom_fields_replace($data);
	}

	return $out;
}

/** Moderation Approver. 
 * article, article-edit = create/update article
 * article-delete = delete article
 * Returns '' on success, otherwise error string.
 */
function mem_article_approver($type,$data)
{
	global $txpcfg, $txp_user, $mem_glz_custom_fields_plugin, $prefs;

	if (!is_array($data))
	{
		// cannot approve
		return 'invalid data';
	}

	if ($type=='article') 
	{
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
		if (!isset($incoming['url_title']) )
		{
			$incoming['url_title'] = '';
		}
		
		$incoming = textile_main_fields($incoming, $use_textile);

		extract(doSlash($incoming));
		
		if ( @$mem_mod_article_submit_with_moderator_id || !isset($user) ) {
			$user = $txp_user;
		}

		// article time
		if ($publish_now==1)
		{
			$when = 'now()';
		}
		else
		{
			$when = strtotime($year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.":00")-tz_offset();
			$when = "from_unixtime($when)";
		}

		if ($Title or $Body or $Excerpt)
		{
			// enforce article publishing privs
			if (!has_privs('article.publish') && $Status>=4) $Status = 3;
			if (@empty($url_title)) $url_title = stripSpace($Title_plain, 1);  	
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
				// ID needed by glz
				$GLOBALS['ID'] = mysql_insert_id();

				// process custom fields
				if ($mem_glz_custom_fields_plugin) 
				{
					glz_custom_fields_save();
				}
				
				// ping on publish
				if ($Status>=4) 
				{
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
	else if ($type=='article-edit') 
	{
		$incoming = $data;

		if (!isset($incoming['articleid']) || empty($incoming['articleid']))
		{
			return 'Article id not provided.';
		}

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
		{
			return 'Article '. $incoming['articleid'].' not found.';
		}

		// authorize
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

		// override submitter with moderator?
		if (@$mem_mod_article_submit_with_moderator_id || !isset($user) )
		{
			$user = $txp_user;
		}

		$Annotate = (int) $Annotate;

		if (!has_privs('article.publish') && $Status>=4) $Status = 3;

		if (@$mem_mod_article_edit_resets_time)
		{
			$whenposted = ",Posted=now()"; 
		}
		else
		{
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

		if ($rs and $mem_glz_custom_fields_plugin)
		{
			// glz needs ID
			$GLOBALS['ID'] = $ID;

			glz_custom_fields_save();
		}

		if($Status >= 4) 
		{
			// only ping on first publish
			if ($oldArticle['Status'] < 4) {
				do_pings();	
			}
			update_lastmod();
		}
		
		return ($rs ? '' : get_status_message($Status).check_url_title($url_title));
	}
	else if ($type=='article-delete') 
	{
		// make sure the mod is allowed to delete
		if (!has_privs('article.delete'))
		{
			return 'You lack the required privileges to moderate this article.';
		}

		extract($data);
		
		if (isset($articleid))
		{
			$id = assert_int($articleid);

			// delete article
			if (safe_delete('textpattern', "ID = $id")) 
			{
				// hide comments associated with deleted article
				safe_update('txp_discuss', "visible = ".MODERATE, "parentid = {$id}");
			}
			else
			{
				return 'failed to delete article';
			}
		}
		else
		{
			return 'invalid article id';
		}
	}	
	else
	{
		return "invalid type {$type}";
	}
}

function mem_article_rejecter($type,$data)
{
//	if ($type=='article' and is_array($data)) {
//		// do nothing
//	}
}

/** HTML select populated with categories. Default section is
 * pulled from GET/POST.
 */
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

/** HTML select populated with sections. Default section is
 * pulled from GET/POST.
 */
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

/** Put delete listener on a page. Links from
 * mem_article_action_link should point here.
 */
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
	{
		// user not set, try ign plugin
		$txp_user = @$ign_user;
	}
	
	$hash = gps('h');
	$articleid = gps('articleid');
	$request = gps('request_article_deletion');
	
	if (empty($hash) || empty($articleid) || empty($request))
	{
		// not a delete request
		return '';
	}
	
	// get the article title for the hash
	$title = safe_field('Title','textpattern',"ID = " . assert_int($articleid));	
	$nhash = md5($articleid.$title);
	
	if ( $nhash == $hash && $request == '1' ) 
	{
		// set flag to hide delete links
		$mem_mod_article_delete_requested = true;
		$data = array(
			'articleid'	=> $articleid,
			'title'	=> $title,
			'user'	=> $txp_user
			);

		// push delete action to queue
		$res = submit_moderated_content('article-delete','','article-delete: #'.$articleid.' - '.$title,$data, $articleid);
		
		if ($res)
		{
			// delete all other pending article moderation actions by this user for the same item_id
			safe_delete('txp_moderation', "`type` LIKE 'article%' and user = '$txp_user' and item_id = $articleid and id != $res and item_id != 0");
			
			return doTag($successmsg, $wraptag, $class);
		}
		else
		{
			// failed to create mod action
			return doTag($failuremsg, $wraptag, $class);
		}
	}

	// no delete
	return '';
}

/** Tag shows delete link. Delete handled by sentry */
function mem_article_action_link($atts,$thing='')
{
	global $thisarticle,$mem_mod_article_delete_requested;

	extract(lAtts(array(
		'action'	=> 'delete',
		'text'	=> mem_moderation_gTxt($action),
		'url'	=> '',
		'mode'	=> '',
		'prompt'	=> '',
	),$atts));

	$action = strtolower($action);

	if (empty($url)) 
	{
		// use current
		$url = $_SERVER['REQUEST_URI'];
	}
	else 
	{
		// make absolute
		$url = hu.ltrim($url, '/');
	}

	// build up GET
	$url .= (strpos($url,'?') === FALSE ? '?' : '&');

	if ($action == 'delete')
	{
		if (empty($prompt))
		{
			$prompt = 'Are you sure that you want to delete this article?';
		}
		
		if ($mem_mod_article_delete_requested === true)
		{
			// sentry is acting
			return '';
		}

		$url .= 'articleid='.$thisarticle['thisid'].'&request_article_deletion=1&h=' . md5($thisarticle['thisid'].$thisarticle['title']);
	}
	else if ($action == 'edit')
	{
		$url .= 'articleid='.$thisarticle['thisid'];
	}

	// output mode
	if ($mode == 'url_only')
	{
		$out = $url;
	}
	else if ($mode == 'url_encoded')
	{
		$out = urlencode($url);
	}
	else if ($mode == 'url_escaped')
	{
		$out = htmlspecialchars($url);
	}
	else
	{
		$out = '<a href="'.htmlspecialchars($url).
			(!empty($prompt) ? "\" onclick=\"javascript:return confirm('{$prompt}');\"" : '').
			'">'. htmlspecialchars($text).'</a>';
	}

	return $out;
}

/** Conditional tag. Supports $thisarticle */
function mem_if_owns_article($atts,$thing='')
{
	global $thisarticle, $txp_user, $ign_user;
	
	extract(lAtts(array(
		'useridfield'	=> 'AuthorID',
	),$atts));
	
	// use ign if needed
	if (empty($txp_user)) $txp_user = $ign_user;

	$cond = is_array($thisarticle) and isset($thisarticle[$articlefield]) and $thisarticle[$articlefield] == $txp_user;
	return EvalElse($thing,$cond);
}

/** custom_article that filters based upon current user (txp or ign) */
function mem_custom_user_article_list($atts, $thing='')
{
	global $pretext, $prefs, $txpcfg,$txp_user,$ign_user;
	
	// need url info and txp prefs
	extract($pretext);
	extract($prefs);
	// custom fields and titles
	$customFields = getCustomFields();
	$customlAtts = array_null(array_flip($customFields));

	// need a user
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
		'sort'      => 'Posted desc',
		'time'      => 'past',
		'month'		=> '',
		'status'    => '4',
	)+$customlAtts,$atts);

	extract($theAtts);

	$pageby = (empty($pageby) ? $limit : $pageby);

	// treat sticky articles differently wrt search filtering, etc
	if (!is_numeric($status))
	{
		$status = getStatusNum($status);
	}

	$issticky = ($status == 5);

	$match = $search = '';

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
		foreach($customFields as $cField)
		{
			if (isset($atts[$cField]))
			{
				$customPairs[$cField] = $atts[$cField];
			}
		}
		if(!empty($customPairs))
		{
			$custom = buildCustomSql($customFields,$customPairs);
		}
	}

	if ($useridfield)
	{
		$custom .= ' and '. doSlash($useridfield) ."='{$userid}' ";
	}

	$statusq = ' and Status = '.intval($status);

	// composit parts
	$where = "1=1" . $statusq. $time.
		$category . $section . $month . $author . $custom ;

	$rs = safe_rows_start("*, unix_timestamp(Posted) as uPosted".$match, 'textpattern',
			$where.' order by '.doSlash($sort).' limit '.intval($offset).', '.intval($limit));

	if ($rs)
	{
		$count = 0;

		$articles = array();
		while($a = nextRow($rs))
		{
			++$count;
			populateArticleData($a);
			global $thisarticle, $uPosted, $limit;
			$thisarticle['is_first'] = ($count == 1);
			$thisarticle['is_last'] = ($count == numRows($rs));
			
			// uncomment for article_entry patch
			//callback_event('article_entry','list');

			if (isset($GLOBALS['thisarticle']))
			{
				// enclosed > form
				$articles[] = !empty($thing) ? parse($thing) : parse_form($form);

				// sending these to paging_link(); Required?
				$uPosted = $a['uPosted'];

				// clean up
				unset($GLOBALS['thisarticle']);
			}
		}

		return join('',$articles);
	}
}

//////////////////////////////////////////////////////
// Public form methods

/** Helper tag for mem_form */
function mem_moderation_article_form($atts, $thing='')
{
	$atts['type'] = 'mem_moderation_article';
	
	return mem_form($atts, $thing);
}

// register functions with mem_form
register_callback('mem_mod_article_form_defaults', 'mem_form.defaults');
register_callback('mem_mod_article_form_display', 'mem_form.display');
register_callback('mem_mod_article_form_submitted', 'mem_form.submit');

/** Set form defaults */
function mem_mod_article_form_defaults()
{
	global $mem_form, $mem_form_type, $mem_form_default, $mem_mod_info, $mem_modarticle_info;

	// type check
	if ($mem_form_type!='mem_moderation_article')
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
			$mem_modarticle_info[strtolower($k)] = $v;
		}
	}

	if (is_array($mem_modarticle_info))
	{
		// set defaults
		foreach($mem_modarticle_info as $key => $val)
		{
			mem_form_default($key, $val);
		}
	}
}

function mem_mod_article_form_display()
{
	global $mem_form_type, $mem_form_labels, $mem_form_values, $mem_mod_info, $mem_modarticle_info,
		$mem_glz_custom_fields_plugin;

	// type check
	if ($mem_form_type!='mem_moderation_article')
	{
		return;
	}
	
	$out = '';

	// glz integration
	if ($mem_glz_custom_fields_plugin) 
	{
		ob_start();
		mem_glz_custom_fields_replace($mem_modarticle_info);
		$out .= ob_get_contents();
		ob_end_clean();
	}
	
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
		$out .= n.'<input type="hidden" name="articleid" value="'.htmlspecialchars($mem_modarticle_info['articleid']).'" />'.
			n.'<input type="hidden" name="type" value="article" />';
	}
	
	return $out;
}

/** Form submit handler */
function mem_mod_article_form_submitted()
{
	global $prefs, $mem_article_vars, $mem_form_type, $txp_user, $ign_user, $mem_mod_info, $mem_modarticle_info, $mem_form_thanks_form;
	
	if ($mem_form_type!='mem_moderation_article')
	{
		return;
	}
		
	extract(gpsa(array('modid','step','id','articleid')));

	$mem_modarticle_info = gpsa($mem_article_vars);
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
			$res = mem_moderation_gTxt('article_deleted');
		}
		else
		{
			$res = mem_moderation_gTxt('article_delete_failed');
		}
	} 
	elseif (!empty($articleid))
	{
		$articleid = doSlash($articleid);
		$rs = safe_rows("*, unix_timestamp(Posted) as uPosted","textpattern","`ID` = $articleid");

		if ($rs)
		{
			// merge the passed in values to the existing
			foreach($mem_modarticle_info as $key => $val)
			{
				$rs[$key] = $val;
			}
			$rs['articleid'] = $articleid;
			
			if ($is_update) 
			{
				$res = update_moderated_content($id, $mem_modarticle_info['note'], $mem_modarticle_info);
			}
			else
			{
				$res = submit_moderated_content('article-edit', $user_email, $mem_modarticle_info['note'], $mem_modarticle_info, $articleid);
				
				if ($res)
				{
					// delete all other pending article moderation actions by this user for the same item_id
					safe_delete('txp_moderation', "`type` LIKE 'article%' and user = '$txp_user' and item_id = $articleid and id != $res and item_id != 0");
				}
			}
		} // if $rs
	}
	else 
	{		
		if (isset($id)) $mem_modarticle_info['id'] = $id;

		if ($is_update)
		{	
			if (isset($mem_modarticle_info['note']))
			{
				$note = $mem_modarticle_info['note'];
			}
			else
			{
				$note = $mem_mod_info['desc'];
			}
	
			if (update_moderated_content($id,$note,$mem_modarticle_info))
			{
				$res = mem_moderation_gTxt('article_updated');
			}
			else
			{
				$res = mem_moderation_gTxt('article_update_failed');
			}
		}
		else
		{
			if (!isset($user))
			{
				$mem_modarticle_info['user'] = $txp_user;
			}
				
			$res = submit_moderated_content('article', '', $mem_modarticle_info['note'], $mem_modarticle_info);
		}
	}
	
	$mem_modarticle_info['result'] = $res;
	
	$thanks_form = @fetch_form($mem_form_thanks_form);
	
	if (!empty($thanks_form))
	{
		$out = parse($thanks_form);
	}
	
	// cleanup global
	unset($mem_modarticle_info);

	return $out;
}


// Integrate with glz_custom_fields plugin if detected
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
				$arr_custom_field_values = @glz_custom_fields_MySQL("values", $custom_field['custom_set'], '', array('custom_set_name' => $custom_field['custom_set_name']));
	
				//DEBUG
				//dmp($arr_custom_field_values);
	
				//custom_set without "_set" e.g. custom_1_set => custom_1
				$custom_set = @glz_custom_number($custom_field['custom_set']);

				// if current article holds no value for this custom field, make it empty
				$custom_value = ( !empty($$custom_set) ) ? $$custom_set : '';
	
				// the way our custom field value is going to look like
				switch ( @$custom_field['custom_set_type'] )
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
			
			$i = 0;
			foreach($out as $k=>$v) {
				echo <<<EOJS
					$(":input[name=$k]").each(function() {
						if ( !$(this).hasClass('memNoGLZ') ) {
							var oldcss = $(this).attr('class');
							var oldval = $(this).val();
							
							$(this).replaceWith(
								$('$v').val(oldval).attr("class", oldcss)
							);
						}
					});
EOJS;
			}
			
			echo '
			});
			//--><!]]>
			</script>';
		}
	}

	/** fetch the custom field values from txp_moderation table */
	function mem_glz_article_custom_fields($name, $extra)
	{
		global $mem_modarticle_info;
		
		if ( is_array($extra) )
		{
			$arr_article_customs = array();

			// see what custom fields we need to query for
			foreach ( $extra as $custom_set )
			{
				$select = @glz_custom_number($custom_set['custom_set']);
				
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
