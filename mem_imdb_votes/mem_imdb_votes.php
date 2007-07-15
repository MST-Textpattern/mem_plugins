<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_imdb_votes';

$plugin['version'] = '0.1';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Display your vote history from imdb';
$plugin['type'] = 0; // 0 for regular plugin; 1 if it includes admin-side code

@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h1. mem_imdb_votes

This plugin will fetch a public IMDB vote history and parse it for use by Textpattern style tags. This plugin will cache the listed for a specified number of seconds.

h3. Attributes

*l* - the value in the imdb public link. E.g. If http://www.imdb.com/mymovies/list?l=20712721 is the full url given by imdb, you would set l equal to "20712721".
*form* - defaults to _imdb_vote_history_
*expiration* - Number of seconds to cache listing from imdb. Default is 3600 (1 hour). Value cannot be less than 30 seconds.

_Standard Textpattern attributes_
*break* 
*breakclass*
*class*
*wraptag* 


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

function mem_imdb_fetch($url, $expiration=60) {
	global $prefs;
	
	$used_cache = true;
	
	$cache_file = build_file_path($prefs['tempdir'], 'mem_imdb_'. urlencode($url));
	
	if (file_exists($cache_file)) {
		$s = stat($cache_file);
		
		$now = time();
		$age = $now-$s['mtime'];
		
		if ($s['size'] < 1 || $age >= $expiration ) {
			unlink($cache_file);
			$used_cache = false;
		} else {
			$url = $cache_file;
			$used_cache = true;
		}
	}

	$votepage = file_get_contents($url);
	
	if ($used_cache===false)
		file_put_contents($cache_file, $votepage);
	
	$startpos = strpos($votepage, '<form name="list"');
	
	if ($startpos!==false) {
		$startpos = strpos($votepage, '<tr', $startpos);
		
		$endpos = strpos($votepage, '</form>', $startpos);
		
		$votepage = substr($votepage,$startpos,$endpos-$startpos);
	}

	preg_match_all("/<tr>.*<td.*>(.*)<(?:\/)td>.*<td.*>(.*)<(?:\/)td>.*<td.*>(.*)<(?:\/)td>.*<td.*>(.*)<(?:\/)td>/sU", $votepage, $matches, PREG_PATTERN_ORDER);

	return $matches;
}

// td columns in resulting page.
define('MATCH_TITLE',2);
define('MATCH_MY_VOTE',3);
define('MATCH_IMDB_AVG',4);

function mem_imdb_votes($atts,$thing='') {
	
	extract(lAtts(array(
			'l'			=> '20712721',
			'wraptag'	=> '',
			'class'		=> '',
			'break'		=> '',
			'breakclass'	=> '',
			'form'		=> 'imdb_vote_history',
			'expiration'	=> '3600'
	),$atts));
	
	if (is_numeric($expiration) && $expiration < 30)
		$expiration = 30;
	
	$matches = mem_imdb_fetch('http://www.imdb.com/mymovies/list?l=' . $l, $expiration);
	
	$out = array();

	if (count($matches) > 0) {
		$results = count($matches[0]);
		
		$Form = fetch_form($form);
		
		for($i=0; $i < $results; $i++) {
			$title = $matches[MATCH_TITLE][$i];
			$my_vote = $matches[MATCH_MY_VOTE][$i];
			$imdb_avg = $matches[MATCH_IMDB_AVG][$i];

			$title = preg_replace("/\/title\//i", "http://www.imdb.com/title/", $title);

			$cl = str_replace("<txp:mem_imdb_title />", $title, $Form);
			$cl = str_replace("<txp:mem_imdb_my_vote />", $my_vote, $cl);
			$cl = str_replace("<txp:mem_imdb_vote_average />", $imdb_avg, $cl);
			
			$out[] = $cl;
		}
	}


	return doWrap($out, $wraptag, $break, $class, $breakclass);
}

# --- END PLUGIN CODE ---

?>
