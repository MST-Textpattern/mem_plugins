<?php

// Either copy classTextile.php to your plugin directory, or uncomment the following
// line and edit it to give the location where classTextile.php can be found
#ini_set('include_path', ini_get('include_path') . ':/full/path/to/textile');

// Plugin name is optional.  If unset, it will be extracted from the current file name.
// Uncomment and edit this line to override:
#$plugin['name'] = 'mem_article_count';

$plugin['version'] = '0.1';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Returns the article count for the number of articles in a specific section and/or a category.';

compile_plugin();
exit;

?>
# --- BEGIN PLUGIN HELP ---

<code>
<pre>
Example using all possible attributes:
<txp:mem_article_count section="textpattern" category="plugin" wraptag="div" />
</pre>
</code>

# --- END PLUGIN HELP ---
<?php


# --- BEGIN PLUGIN CODE ---


function mem_article_count ($atts)
{
    extract(lAtts(array(
        'wraptag'   => '',
        'section'   => '',
        'category'  => ''
    ),$atts));

    $where = "status=4 and Posted < now()";
    
    if ($section) $where .= " and section = '".$section."'";
    if ($category) $where .= " and (Category1 = '".$category."' or Category2 = '".$category."')";

    $total = safe_field('count(*)','textpattern',$where);

    return ($wraptag) ? tag($total,$wraptag) : $total;
}



# --- END PLUGIN CODE ---


// -----------------------------------------------------

function extract_section($lines, $section) {
	$result = "";
	
	$start_delim = "# --- BEGIN PLUGIN $section ---";
	$end_delim = "# --- END PLUGIN $section ---";

	$start = array_search($start_delim, $lines) + 1;
	$end = array_search($end_delim, $lines);

	$content = array_slice($lines, $start, $end-$start);

	return join("\n", $content);

}

function compile_plugin() {
	global $plugin;

	if (!isset($plugin['name'])) {
		$plugin['name'] = basename(__FILE__, '.php');
	}

	# Read the contents of this file, and strip line ends
	$content = file(__FILE__);
	for ($i=0; $i < count($content); $i++) {
		$content[$i] = rtrim($content[$i]);
	}

	$plugin['help'] = extract_section($content, 'HELP');
	$plugin['code'] = extract_section($content, 'CODE');

	@include('classTextile.php');
	if (class_exists('Textile')) {
		$textile = new Textile();
		$plugin['help'] = $textile->TextileThis($plugin['help']);
	}

	$plugin['md5'] = md5( $plugin['code'] );

	// to produce a copy of the plugin for distribution, load this file in a browser. 

echo chr(60)."?php\n\n"."/*\n * Plugin: ".$plugin['name']." v".$plugin['version']."\n * Author: ".$plugin['author']."\n * Generated: ".date("m.d.Y G:i")."\n */\n\n".'$'."plugin='" . base64_encode(serialize($plugin)) . "'\n?".chr(62);

}

?>
