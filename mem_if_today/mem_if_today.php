<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_if_today';

$plugin['version'] = '0.1.1';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Conditionally parses the enclosed tags if today is a specific day of the week, weekend, or weekday.';
$plugin['type'] = 0; // 0 for regular plugin; 1 if it includes admin-side code

@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h3. mem_if_today

p. This tag will conditionally parse and display the enclosed text data if today is or isnot one of the specified days.

p. Attributes


|_. Name|_. Description|_. Default Value|
|_. is|This is a comma separated list of names to test today against. A name can be a weekday (should be locale friendly), "weekday" (M-F), "weekend" (Sa-Su), or a month.| none|
|_. isnot|Functions the same as the "is" attribute, but the enclosed text will be parsed if today does not match any of the days.| none|
|_. wraptag|The html tag that will wrap the output value| none|
|_. class|The CSS class that will be applied to the "wraptag"|mem_if_today|

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

////////////////////////////////////////////////////////////
// Plugin mem_if_today
// Author: Michael Manfre (http://manfre.net/)
// Revisions: 
//
////////////////////////////////////////////////////////////

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
			$tests = explode(',',strtolower($is));
			$condition = true;
		} else if (!empty($isnot)) {
			$tests = explode(',',strtolower($isnot));
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
		
		if ($pass === $condition)
			return (empty($wraptag)) ? parse($thing) : doTag(parse($thing),$wraptag,$class);

		return '';
}

# --- END PLUGIN CODE ---

?>
