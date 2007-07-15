<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_online_users';

$plugin['version'] = '0.2';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Display the current online user count.';
$plugin['type'] = 0; // 0 for regular plugin; 1 if it includes admin-side code

@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h3. mem_online_users

This tag will output the number of distinct users who have accessed your site in the past "timeout" seconds. An attribute "path" can be specified to limit the results to users who have accessed the specified sub path. E.g. "/article" will match all articles "/article/1", "/article/2005/01/02".

p. Attributes


|_. Name|_. Description|_. Default Value|_. Notes|
|_. timeout|The number of seconds a user is still deemed online. 600=10 minutes, 1800=30 minutes, 3600=1 hour.|600|optional|
|_. path|The subpath that will be used to limit the results to. Only users who have accessed the site in a url below "path" in the past "timeout" seconds will be counted. If path is "/article", then user visits to "/article", "/article/10/A-nifty-article", and "/article/2005/12/22" would be counted, but "/about" would not be counted.|none|optional|
|_. wraptag|The html tag that will wrap the output value| none|optional|
|_. class|The CSS class that will be applied to the "wraptag"|mem_online_users|optional|

p. Examples

<code><txp:mem_online_users /></code> will display a count of users who have accessed your site in the past 10 minutes.

<code><txp:mem_online_users path="/article" timeout="300" /></code> will display a count of users who have accessed either the section listing or individual article for section "article" in the past 5 minutes.


# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

////////////////////////////////////////////////////////////
// Plugin mem_online_users
// Author: Michael Manfre (http://manfre.net/)
// Revisions: 
//		2005.06.02	- Fixed bugs related to the various 
//			versions of doTag.
////////////////////////////////////////////////////////////


function mem_online_users($atts,$thing='')
{
	extract(lAtts(array(
			'timeout'	=>	'600',
			'path'		=>	'',
			'wraptag'	=>	'',
			'class'		=>	__FUNCTION__
		),$atts));
	
	if (!is_numeric($timeout) or intval($timeout) <= 0)
		return '<!-- mem_online_users: invalid timeout -->';

	$time = time() - intval($timeout);
	
	$where = "(UNIX_TIMESTAMP(time) >= $time)";
	
	if (!empty($path))
		$where .= " AND page LIKE '".doSlash($path)."%'";
	
	$count = safe_field('COUNT(DISTINCT `ip`) as OnlineUsers','txp_log',$where);

	// fix up doTag and how it treats string '0'
	if ($count=='0') $count = '&#48;';

	$out = (empty($wraptag)) ? $count : doTag($count,$wraptag,$class);

	return $out;
}

# --- END PLUGIN CODE ---

?>
