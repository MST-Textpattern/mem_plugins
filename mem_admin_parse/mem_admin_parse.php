<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_admin_parse';

$plugin['version'] = '0.2.3';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Provides functions to parse txp tags in the admin interface.';
$plugin['type'] = 1; // 0 for regular plugin; 1 if it includes admin-side code

@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h2. mem_admin_parse

p. This plugin allows other plugins to parse text for txp tags, in a fashion similar to public side plugins. This plugin is designed to only be used by other plugins.

h3. Function: admin_parse(string text, bool use_textile)

p. Param text is the text string that will be parsed for tags.
p. Param use_textile specifies whether or not text is Textile formatted and should be converted to html before being parsed. Default is not to parse.


h3. Admin Tag: mem_gps

p. This tag will output the POST or GET parameter value, and is designed to be a generic postback tag for relaying information to the user after submitting a form.

p. <code><txp:mem_gps name="_argname_" /></code>

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

////////////////////////////////////////////////////////////
// Plugin mem_admin_parse
// Author: Michael Manfre (http://manfre.net/)
////////////////////////////////////////////////////////////

if (@txpinterface == 'admin') {
	
	$no_tag = array( 'admin', 'article', 'auth', 'category', 'css', 'diag', 'discuss',
					'file', 'form', 'image', 'import', 'link', 'list', 'log', 'page',
					'plugin', 'prefs', 'preview', 'section', 'tag' );
	
	global $event;
	
	if (!in_array($event, $no_tag))
		include_once txpath.'/publish/taghandlers.php';

	function admin_parse($text,$use_textile=false)
	{
		if ($use_textile===true) {
			include_once txpath.'/lib/classTextile.php';
			$textile = new Textile();
			$text = $textile->TextileThis($text);
		}
		
		$f = '/<txp:(\S+)\b(.*)(?:(?<!br )(\/))?'.chr(62).'(?(3)|(.+)<\/txp:\1>)/sU';
		return preg_replace_callback($f, 'admin_processTags', $text);
	}

// -------------------------------------------------------------
	function admin_processTags($matches)
	{
		global $pretext, $production_status, $txptrace;
		$tag = $matches[1];

		$atts = (isset($matches[2])) ? splat($matches[2]) : '';
		$thing = (isset($matches[4])) ? $matches[4] : '';

		if ($production_status == 'debug')
		{
			@$txptrace[] = trim($matches[0]);
			maxMemUsage(trim($matches[0]));
		}

		if ($thing) {
			if (function_exists($tag)) return $tag($atts,$thing,$matches[0]);
			if (isset($pretext[$tag])) return $pretext[$tag];
		} else {
			if (function_exists($tag)) return $tag($atts);
			if (isset($pretext[$tag])) return $pretext[$tag];
		}
		if ($production_status == 'debug') // return unknown Tag with removed attributes
			return htmlspecialchars(preg_replace('#\"[^"]*\"#i','"***"',$matches[0]));
	}
} else {
	if (function_exists('parse')) {
		function admin_parse($text,$use_textile=false) {
			return parse($text);
		}
	}
}

// -------------------------------------------------------------
function mem_gps($atts) {
	extract(lAtts(array(
		'name'		=> '',
		'class'		=> '',
		'wraptag'	=> 'div',
		'formatting'	=> 'none'
	),$atts));

	$val = gps($name);

	if ($formatting=='textile') {
		include_once txpath.'/lib/classTextile.php';
		$t = new Textile();
		
		$val = $t->TextileThis($val);
	} else if ($formatting=='linebreaks') {
		$val = nl2br(trim($val));
	} else if ($formatting=='category_title') {
		$val = fetch_category_title($val);
	} else if ($formatting=='section_title') {
		$val = fetch_section_title($val);
	}

	if (isset($name))
		return doTag( $val, $wraptag, $class);
}

// -------------------------------------------------------------
function mem_if_step($atts,$thing) {
	global $step;
	$cond = $step==$atts['name'];
	return admin_parse(EvalElse($thing,$cond));
}

// -------------------------------------------------------------
function mem_if_query($atts,$thing) {
	extract(lAtts(array(
		'name'		=> '',
		'value'		=> ''
	),$atts));
	$name = gps($name);
	$cond = $name==$value;
	return admin_parse(EvalElse($thing,$cond));
}


# --- END PLUGIN CODE ---

?>

