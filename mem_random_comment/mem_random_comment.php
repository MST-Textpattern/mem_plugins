<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_random_comment';

$plugin['version'] = '0.2.1';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Display a random selection of comment(s) from a specified group of articles.';
$plugin['type'] = 0; // 0 for regular plugin; 1 if it includes admin-side code

@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h1. Tag: mem_random_comment

p. This plugin will randomly select up to the specified number of comments from the specified section or group of articles.

h2. Attributes


*limit*

p. The maximum number of comments to display.

*section*

p. Only use comments from the specified comma separated list of sections. Cannot be used with *id* attribute.

*id*

p. Comma separated list of article ids to use as the selection pool. Cannot be used with *section* attribute.

*form*

p. The form that holds the comment display tags. Defaults to the "_comments_" form.

*wraptag*

p. Tag that will wrap the output.

*class*

p. CSS class attribute for wraptag.

*atts*

p. Other attributes to include with wraptag.

*break*

p. Standard txp style break tag for lists.

*breakclass*

p. CSS class attribute for break tag.



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


function mem_random_comment($atts) {
	extract(lAtts(array(
			'wraptag'	=> '',
			'class'		=> '',
			'limit'		=> '1',
			'section'	=> '',
			'id'		=> '',
			'form'		=> 'comments',
			'break'		=> '',
			'breakclass'	=> '',
			'atts'		=> ''
		),$atts));

	if (!is_numeric($limit) || $limit < 1)
		$limit = 1;

	$ids = explode(',',$id);
	
	$sql =	"SELECT D.*, T.Section, T.url_title, T.ID AS articleid, T.Title_html, T.Title, T.Category1, T.Category2, unix_timestamp(T.Posted) as uPosted FROM `txp_discuss` D INNER JOIN `textpattern` T ON D.parentID=T.ID WHERE D.visible=".VISIBLE;

	if (!empty($section)) {
		$sections = array();
		
		foreach (explode(',', $section ) AS $s) {
			$sections[] = sprintf("'%s'", doSlash($s));
		}

		$sql .= " AND T.section IN (" . join(', ',$sections) . ")";
	} else if (is_array($ids) && count($ids) > 0) {
		$sql .= " AND D.parentID IN (" . join(', ', $ids) . ")";
	}
	
	$sql .= " ORDER BY rand() LIMIT 0, $limit";

	$rs = getRows($sql);
	
	$comments = array();
	
	if ($rs) {
		foreach ($rs as $c) {
			$a = array();
			$a['ID'] = $c['articleid'];
			$a['title'] = $c['Title_html'];
			$a['category1'] = $c['Category1'];
			$a['category2'] = $c['Category2'];
			$a['Section'] = $c['Section'];
			$a['url_title'] = $c['url_title'];
			$a['Posted'] = $c['uPosted'];
			$GLOBALS['thisarticle'] = $a;
			$GLOBALS['thiscomment'] = $c;
			$comments[] = parse(fetch_form($form));
			unset($GLOBALS['thiscomment']);
			unset($GLOBALS['thisarticle']);
		}
	}

	return doWrap( $comments, $wraptag, $break, $class, $breakclass, $atts );
}
# --- END PLUGIN CODE ---

?>
