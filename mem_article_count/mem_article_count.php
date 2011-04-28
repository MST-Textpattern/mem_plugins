<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
# $plugin['name'] = 'abc_plugin';

// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.2';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Returns the article count for the number of articles in a specific section and/or a category.';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = 1; 

if (!defined('txpinterface'))
	@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h1. Summary

p. This plugin will return the count of all 

Example using all possible attributes:
@<txp:mem_article_count section="textpattern" category="plugin" wraptag="div" />@

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

// Plugin code goes here.  No need to escape quotes.

function mem_article_count ($atts)
{
    extract(lAtts(array(
        'wraptag'   => '',
        'section'   => '',
        'category'  => '',
        'status'	=> '4'
    ),$atts));

    $where = array();
    
    $where[] = 'posted <= now()';
    
    if (!empty($status) || $status==='0') $where[] = "status = ". assert_int($status);
    if ($section) $where[] = "section = '".doSlash($section)."'";
    if ($category) $where[] = "(Category1 = '".doSlash($category)."' or Category2 = '".doSlash($category)."')";

    $total = safe_field('count(*)','textpattern', join(' and ', $where));

    return ($wraptag) ? tag($total,$wraptag) : $total;
}


# --- END PLUGIN CODE ---

?>
