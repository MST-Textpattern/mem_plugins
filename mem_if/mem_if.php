<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_if';

$plugin['version'] = '0.3';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Useful conditionals';
$plugin['type'] = 0; // 0 for regular plugin; 1 if it includes admin-side code

@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h1. Conditional Tags

This plugin provides several conditional tags that can be used to show or hide information.

Common attributes are _class_ and _wraptag_, which are used to wrap the output in an html tag with the specified css class. All attributes will be shown in _italic_.

h3. mem_if

h3. mem_if_empty

p. Thi 

h3. mem_isset

Conditionally show the enclosed text if the variable _name_ is globally set.

@<txp:mem_if_isset name="somevar">$GLOBALS["somevar"] is set</txp:mem_if_isset>@

h3. mem_switch

h3. mem_if_today

p. This tag will conditionally parse and display the enclosed text data if today _is_ or _isnot_ one of the specified days. Both _is_ and _isnot_ support the following values, a weekday (should be locale friendly), "weekday" (M-F), "weekend" (Sa-Su), or a month.

Examples:
* @I am at <txp:mem_if_today is="weekend">the beach<txp:else />work</txp:mem_if_today>@
* @<txp:mem_if_today is="Monday">I hate Mundays</txp:mem_if_today>@

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

function mem_if($atts,$thing='')
{
	extract(lAtts(array(
		'empty'		=> '',
		'isset'		=> '',
		'author'	=> '',
		'category'	=> '',
		'css'		=> '',
		'id'		=> '',
		'frontpage'	=> '',
		'month'		=> '',
		'negate'	=> '0',
		'page'		=> '',
		'section'	=> '',
		'class'		=> __FUNCTION__,
		'wraptag'	=> '',
	),$atts));

	$cond = true;
	
	if (!empty($empty))
		$cond &= (isset($GLOBALS[$empty]) && empty($GLOBALS[$empty]));
	if (!empty($isset))
		$cond &= (isset($$isset));
	if (!empty($author))
		$cond &= in_array($GLOBALS['author'],split(',',$author));
	if (!empty($category))
		$cond &= in_array($GLOBALS['c'],split(',',$category));
	if ($css=='1')
		$cond &= $GLOBALS['css'];
	if ($frontpage=='1')
		$cond &= ($GLOBALS['s'] && $GLOBALS['s']=='default');
	if (!empty($id))
		$cond &= in_array($GLOBALS['id'],split(',',$id));
	if (!empty($month))
		$cond &= in_array($GLOBALS['month'],split(',',$month));
	if (!empty($page))
		$cond &= in_array($GLOBALS['p'],split(',',$page));
	if (!empty($section))
		$cond &= in_array($GLOBALS['s'],split(',',$section));
	
	if ($negate=='1')
		$cond = !$cond;
	
	return doTag(parse(EvalElse($thing,$cond)),$wraptag,$class);
}

function mem_if_today($atts,$thing)
{
	extract(lAtts(array(
			'is'		=>	'',
			'isnot'		=>	'',
			'wraptag'	=>	'',
			'class'		=>	__FUNCTION__
		),$atts));
		$condition = true;
		$pass = false;

		if (!empty($is) and !empty($isnot)) {
			// not supported
			$tests = '';
		} else if (!empty($is)) {
			$tests = split(',',strtolower($is));
			$condition = true;
		} else if (!empty($isnot)) {
			$tests = split(',',strtolower($isnot));
			$condition = false;
		}
		
		if (empty($tests)) return '';

		$date = getdate();
		extract($date);

		
		foreach ($tests as $t) {
			switch ($t) {
				case 'weekend':
					if ($wday == 0 or $wday == 6)
						$pass = true;
					break;
				case 'weekday':
					if ($wday > 0 and $wday < 6)
						$pass = true;
					break;
				default:
					if (strcasecmp($weekday,$t)==0)
						$pass = true;
					else if (strcasecmp($month,$t)==0)
						$pass = true;
					break;
			}
		}
		
	$c = ($pass === $condition);

	return parse(EvalElse(doTag($thing,$wraptag,$class), $c ));
}

function mem_if_empty($atts,$thing='')
{
	extract(lAtts(array(
		'name'		=>	'',
		'wraptag'	=>	'',
		'class'		=>	'',
	),$atts));

	$cond = empty($GLOBALS[$name]);
	return doTag(parse(EvalElse($thing,$cond)),$wraptag,$class);
}

function mem_if_isset($atts,$thing='')
{
	extract(lAtts(array(
		'name'		=>	'',
		'wraptag'	=>	'',
		'class'		=>	'',
	),$atts));

	$cond = isset($GLOBALS[$name]);
	return doTag(parse(EvalElse($thing,$cond)),$wraptag,$class);
}


function mem_switch($atts,$thing='') {
	global $mem_switch;
	
	extract(lAtts(array(
		'class'		=> '',
		'wraptag'	=> '',
		'var'		=> ''
	),$atts));

	$mem_switch = array('var' => $var, 'matched'=>false);
	$out = parse($thing);
	$mem_switch = false;
	
	return $out;
}

function mem_case($atts,$thing='') {
	global $mem_switch;
	
	extract(lAtts(array(
		'class'		=> '',
		'wraptag'	=> '',
		'value'		=> false
	),$atts));

	if (!is_array($mem_switch)) return '';

	extract($mem_switch);
	
	$out = '';

	if (!$matched) {
		if ($value===false || ($value && $GLOBALS[$var]==$value) ) {
			$out = parse($thing);
			$mem_switch['matched'] = true;
		}
	}
	
	return $out;
}
# --- END PLUGIN CODE ---

?>
