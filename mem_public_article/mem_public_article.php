<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_public_article';

// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
// $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.1';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Create/edit articles from the public side of a site.';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = 0; 

if (!defined('txpinterface'))
	@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---
h1(title). mem_public_article plugin

h2(section summary). Summary

p. This plugin allows for a site owner to create an article edit form on the public side of their site.

p. Note: There are no security checks. Anyone who can see the form will be able to submit an article. I strongly suggest that you only let the user modify fields that you want them to modify. All other import fields should be passed through with mem_form_secret. It is recommended that you use ign_password_protect to reduce spam.

p. This plugin requires mem_form.

p. This plugin has MLP support.

p. This plugin has been tested with 4.0.7 and 4.0.8

h2(section contact). Author Contact

"Michael Manfre":mailto:mmanfre@gmail.com?subject=Textpattern%20mem_public_article%20plugin
"http://manfre.net":http://manfre.net

h2(section license). License

p. This plugin is licensed under the "GPLv2":http://www.fsf.org/licensing/licenses/info/GPLv2.html.

h2(section installation). Installation

p. No extra installation steps

h2(section tags). Tags

* "mem_public_article":#mem_
* "mem_public_article_if_ps":#mem_public_article_if_ps
* "mem_public_article_ps":#mem_public_article_ps

h3(tag#mem_public_article). mem_public_article

p(tag-summary). This tag will output an HTML form. You must specify each of the article input fields that you want to display to the author.

*(atts) %(atts-name)form% %(atts-type)string% Name of form that contains the mem_simple_form form tags.
*(atts) %(atts-name)success_form% %(atts-type)string% Name of the form that will be shown after a successful post.
*(atts) %(atts-name)do_pings% %(atts-type)boolean% Set to "1" if you want to ping the services that are normally pinged using the admin edit form.

h4. HTML Field Names

p. Title, Body, Body_html, Excerpt, Excerpt_html, Image, Keywords, Status, Author, Section, Category1, Category2, textile_body, textile_excerpt, Annotate, override_form, url_title, AnnotateInvite, custom_1, custom_2, ..., custom_10

p. Expires Time: expires, exp_year, exp_month, exp_day, exp_hour, exp_minute, exp_second

p. Posted Time: publish_now, year, month, day, hour, minute, exp_second


h3(tag#mem_simple_if_ps). mem_public_article_if_ps

p(tag-summary). Conditional tag that checks to see if a HTML field was posted, or if it has a specific value.

*(atts) %(atts-name)name% %(atts-type)string% HTML field name posted with the form.
*(atts) %(atts-name)equal% %(atts-type)string% Value to compare against the value of name. If not specified, tag checks to see if form posted variable HTML field name.

h3(tag#mem_simple_ps). mem_public_article_ps

p(tag-summary). This tag will output the value of the posted HTML form field.

*(atts) %(atts-name)name% %(atts-type)string% HTML field name posted with the form.


# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---
// needed for MLP
define( 'MEM_PUBLIC_ARTICLE_PREFIX' , 'mem_public_article' );

global $mem_public_article_lang;

if (!is_array($mem_public_article_lang))
{
	$mem_public_article_lang = array(
		'author_missing'	=>	'Author not provided.',
		'url_title_is_multiple'	=> 'The url_title must be unique.',
	);
}

register_callback( 'mem_public_article_enumerate_strings' , 'l10n.enumerate_strings' );
function mem_public_article_enumerate_strings($event , $step='' , $pre=0)
{
	global $mem_public_article_lang;
	$r = array	(
				'owner'		=> 'mem_public_article',			#	Change to your plugin's name
				'prefix'	=> MEM_PUBLIC_ARTICLE_PREFIX,		#	Its unique string prefix
				'lang'		=> 'en-gb',				#	The language of the initial strings.
				'event'		=> 'public',			#	public/admin/common = which interface the strings will be loaded into
				'strings'	=> $mem_public_article_lang,		#	The strings themselves.
				);
	return $r;
}


function mem_pa_gtxt($what,$args = array())
{
	global $mem_public_article_lang, $textarray;

	$key = strtolower( MEM_PUBLIC_ARTICLE_PREFIX . '-' . $what );
	
	if (isset($textarray[$key]))
	{
		$str = $textarray[$key];
	}
	else
	{
		$key = strtolower($what);
		
		if (isset($mem_public_article_lang[$key]))
			$str = $mem_public_article_lang[$key];
		elseif (isset($textarray[$key]))
			$str = $textarray[$key];
		else
			$str = $what;
	}

	if( !empty($args) )
		$str = strtr( $str , $args );

	return $str;
}


require_plugin('mem_form');

function mem_public_article($atts, $thing='')
{
	global $mem_public_article_id;

    $atts = lAtts(array(
        'form'	=> '',
        'success_form'	=> false,
        'do_pings'	=> false,
        'article_id'	=>	false,
    ),$atts,0);
    
    $atts['type'] = 'mem_article_public';

		if (!empty($atts['form'])) {
			$thing = fetch_form($atts['form']);
			unset($atts['form']);
		}
		
		$mem_public_article_id = $atts['article_id'];

		foreach(array('do_pings','success_form', 'article_id') as $a) {
			$thing .= '<txp:mem_form_secret name="mem_public_article_'.$a.'" value="'.$atts[$a].'" />';
			unset($atts[$a]);
		}
		
    return mem_form($atts, $thing);
}

register_callback('mem_public_article_defaults', 'mem_form.defaults');
function mem_public_article_defaults()
{
	global $mem_form_type, $mem_form_default, $thisarticle, $mem_public_article_id;
	
	if ($mem_form_type != 'mem_article_public')
		return;

	if (isset($mem_public_article_id))
	{
		if (!is_numeric($mem_public_article_id) && strpos($mem_public_article_id, '<txp:')==0 )
			$mem_public_article_id = parse($mem_public_article_id);
		
		if (is_numeric($mem_public_article_id))
			$article = safe_row('*', 'textpattern', "ID = ".doSlash($mem_public_article_id));
	}

	if (!$article && isset($thisarticle))
	{
		$article = $thisarticle;
	}
	
	if ($article)
	{
		foreach($article as $k => $v)
		{
			$mem_form_default[$k] = $v;
		}
	}
}

register_callback('mem_public_article_submit', 'mem_form.submit');
function mem_public_article_submit()
{
	global $mem_form_type, $mem_form_values, $prefs, $vars, $txp_user, $ign_user;
	
	if ($mem_form_type !== 'mem_article_public')
		return;
	
	$user = doSlash(!empty($ign_user) ? $ign_user : $txp_user);
	
	if (isset($mem_form_values['Author']))
		$user = doSlash($mem_form_values['Author']);
	
	if (empty($user))
	{
		return mem_form_error( mem_pa_gtxt('author_missing') );
	}
	
	require_once(txpath.'/include/txp_article.php');

	$int_vars = array('Status','textile_body','textile_excerpt');

	$incoming = textile_main_fields($mem_form_values, $prefs['use_textile']);

	if (empty($mem_form_values['ID']))
	{
		foreach ($vars as $v)
		{
			if (!isset($mem_form_values[$v]))
				$mem_form_values[$v] = in_array($v, $int_vars) ? 0 : '';
		}
	
		extract(doSlash($incoming));
		extract(array_map('assert_int', array( $incoming['Status'], $incoming['textile_body'], $incoming['textile_excerpt'])));
		$Annotate = (int) $Annotate;
		$Keywords = doSlash(trim(preg_replace('/( ?[\r\n\t,])+ ?/s', ',', preg_replace('/ +/', ' ', $Keywords)), ', '));


		if (!empty($expires_date))
		{
			$expires = strtotime($expires_date);
			$whenexpires = $expires_date;
		}
		else
		{
			if (empty($exp_year)) {
				$expires = 0;
				$whenexpires = NULLDATETIME;
			}
			else {			
				if(empty($exp_month)) $exp_month=1;
				if(empty($exp_day)) $exp_day=1;
				if(empty($exp_hour)) $exp_hour=0;
				if(empty($exp_minute)) $exp_minute=0;
				if(empty($exp_second)) $exp_second=0;
				
				$expires = strtotime($exp_year.'-'.$exp_month.'-'.$exp_day.' '.$exp_hour.':'.$exp_minute.':'.$exp_second)-tz_offset();
				$whenexpires = "from_unixtime($expires)";
			}
		}

		if ($publish_now==1) {
			$when = 'now()';
			$when_ts = time();
		} else {
			if(empty($month)) $month=1;
			if(empty($day)) $day=1;
			if(empty($hour)) $hour=0;
			if(empty($minute)) $minute=0;
			if(empty($second)) $second=0;

			$when = $when_ts = strtotime($year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':'.$second)-tz_offset();
			$when = "from_unixtime($when)";
		}
			
	
		if ($Title or $Body or $Excerpt)
		{
			if (empty($url_title)) $url_title = stripSpace($Title_plain, 1);

			$url_title_count = safe_count('textpattern', "url_title = '$url_title'");
			if ($url_title_count > 0)
				return mem_form_error( mem_pa_gtxt('url_title_is_multiple', array('{count}' => $url_title_count, '{value}' => $url_title)) );
	
			$rs = safe_insert(
			   "textpattern",
			   "Title           = '$Title',
				Body            = '$Body',
				Body_html       = '$Body_html',
				Excerpt         = '$Excerpt',
				Excerpt_html    = '$Excerpt_html',
				Image           = '$Image',
				Keywords        = '$Keywords',
				Status          =  $Status,
				Posted          =  $when,
				Expires         =  $whenexpires,
				LastMod         =  now(),
				AuthorID        = '$user',
				Section         = '$Section',
				Category1       = '$Category1',
				Category2       = '$Category2',
				textile_body    =  $textile_body,
				textile_excerpt =  $textile_excerpt,
				Annotate        =  $Annotate,
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
				custom_10       = '$custom_10',
				uid             = '".md5(uniqid(rand(),true))."',
				feed_time       = now()"
			);

			$GLOBALS['ID'] = mysql_insert_id();

			if ($Status>=4) {
				if (!empty($mem_form_values['mem_public_article_do_pings']))
					do_pings();

				update_lastmod();
			}
		}
	}
	else
	{
		// update
		$oldArticle = safe_row('*, unix_timestamp(LastMod) as sLastMod','textpattern','ID = '.(int)$incoming['ID']);

		$incoming = lAtts($oldArticle, $incoming, 0);
		$incoming['Annotate'] = empty($incoming['Annotate']) ? 0 : $incoming['Annotate'];
		
		if (!empty($incoming['sLastMod']) && $oldArticle['sLastMod'] != $incoming['sLastMod'])
		{
			//concurrent edit
			return mem_form_error( gTxt('concurrent_edit_by', array('{author}' => htmlspecialchars($oldArticle['LastModID']))) );
		}
		extract($incoming);
		
		$LastModID = $user;

		if (!empty($expires_date))
		{
			$expires = strtotime($expires_date);
			$whenexpires = "Expires=from_unixtime($expires)";
		}
		else
		{
			if (!empty($exp_year)) {
				if(empty($exp_month)) $exp_month=1;
				if(empty($exp_day)) $exp_day=1;
				if(empty($exp_hour)) $exp_hour=0;
				if(empty($exp_minute)) $exp_minute=0;
				if(empty($exp_second)) $exp_second=0;
				
				$expires = strtotime($exp_year.'-'.$exp_month.'-'.$exp_day.' '.$exp_hour.':'.$exp_minute.':'.$exp_second)-tz_offset();
				$whenexpires = "Expires=from_unixtime($expires)";
			}
			else
				$whenexpires = '';
		}

		if(@$reset_time) {
			$whenposted = ",Posted=now()";
			$when_ts = time();
		} else {
			if (!empty($year))
			{
				$when = $when_ts = strtotime($year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':'.$second)-tz_offset();
				$whenposted = ",Posted=from_unixtime($when)";
			}
			else
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

			$url_title_count = safe_count('textpattern', "url_title = '$url_title' and ID != $ID");
			if ($url_title_count > 0)
				return mem_form_error( mem_pa_gtxt('url_title_is_multiple', array('{count}' => $url_title_count, '{value}' => $url_title) ));
		}

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
			LastModID       = '$LastModID',
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
			$whenposted
			$whenexpires",
			"ID = $ID"
		);

		if($Status >= 4) {
			if ($oldArticle['Status'] < 4 && !empty($mem_form_values['mem_public_article_do_pings']) ) {
				do_pings();
			}

			update_lastmod();
		}
	}
	
	if (isset($rs) && $rs)
	{
		$success_form = @$mem_form_values['mem_public_article_success_form'];

		if (!empty($success_form))
		{
			return parse(fetch_form($success_form));
		}		
	}
}


function mem_public_article_ps($atts,$thing='')
{
	extract(lAtts(array(
		'name'	=>	false,
	),$atts));
	
	if (!empty($name))
	{
			$n = gps($name);
			if (!empty($n))
				return $n;
	}
	return '';
}

function mem_public_article_if_ps($atts, $thing='')
{
	extract(lAtts(array(
		'name'	=>	false,
		'equal'	=>	false,
	),$atts));
	
	if ($name === false)
		trigger_error(gTxt('attribute_missing', array('{name}' => $name)));

	if ($equal === false)
		$condition = isset($_POST[$name]);
	else
		$condition = (gps('name') == $equal);
	$thing = EvalElse($thing, $condition);

	return parse($thing);
}

# --- END PLUGIN CODE ---

?>
