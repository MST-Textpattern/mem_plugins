<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_if';

$plugin['version'] = '0.2';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Useful conditionals';
$plugin['type'] = 0; // 0 for regular plugin; 1 if it includes admin-side code

@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---



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
