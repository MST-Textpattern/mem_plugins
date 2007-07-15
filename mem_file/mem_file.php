<?php

// Either copy classTextile.php to your plugin directory, or uncomment the following
// line and edit it to give the location where classTextile.php can be found
ini_set('include_path', ini_get('include_path') . ';r:\\projects\\textpattern\\plugins');

// Plugin name is optional.  If unset, it will be extracted from the current file name.
// Uncomment and edit this line to override:
$plugin['name'] = 'mem_file';

$plugin['version'] = '0.3';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'A plugin to added helper tags for use with the the File Upload mod';

compile_plugin();
exit;

?>
# --- BEGIN PLUGIN HELP ---

h1. Plugin Tags

# "mem_file":#mem_file
# "mem_file_link":#mem_file_link
# "mem_file_id":#mem_file_id
# "mem_file_name":#mem_file_name
# "mem_file_category":#mem_file_category
# "mem_file_downloads":#mem_file_downloads
# "mem_file_size":#mem_file_size
# "mem_file_transfered":#mem_file_transfered
# "mem_file_created":#mem_file_created
# "mem_file_modified":#mem_file_modified
# "mem_file_accessed":#mem_file_accessed

p. Common Tag Attributes (can be used for all tags):

* *id* -- (optional) The id of a file.
* *filename* -- (optional) The filename of a file.

h2. <a name="mem_file">mem_file</a>

p. This tag is used as a wrapper tag for other mem_file tags. Either _id_ or _filename_ must be specified, and all enclosed tags will use the same value if none is specified. This tag is not needed for other tags to work.

p. Tag Attributes:

* *form* -- (optional) The form that contains the tags that will be parsed. If not specified, it will use the tag's enclosed text.

h2. <a name="mem_file_link">mem_file_link</a>

p. This tag will generate a download link to the specified file. The enclosed text of this tag will be used as the link text. It will be parsed for other textpattern tags. Enclosed mem_file_* tags do not need to specify an _id_ or _filename_ attribute, unless you wish to override the one specified for mem_file_link.

p. Tag Attributes:

* *form* -- (optional) The form that contains the tags that will be parsed. If not specified, it will use the tag's enclosed text.

h2. <a name="mem_file_id">mem_file_id</a>

p. This tag will output the id of the specified file.

h2. <a name="mem_file_name">mem_file_name</a>

p. This tag will output the name of the specified file.

h2. <a name="mem_file_category">mem_file_category</a>

p. This tag will output the category associated with file.

h2. <a name="mem_file_downloads">mem_file_downloads</a>

p. This tag will output the number of times that the specified file was downloaded.

h2. <a name="mem_file_size">mem_file_size</a>

p. This tag will output the size of the specified file.

p. Tag Attributes:

* *format* -- (optional) The size format that is desired. Defined values are 'b', 'kb', 'mb', 'gb', 'pb', 'auto'. The size marker will be appended to the number. If not specified, it will output the number of bytes without any formatting.
* *decimals* -- (optional) The number of decimal places to use when formatting the number.

h2. <a name="mem_file_transfered">mem_file_transfered</a>

p. This tag will output the amount of data transfered from downloads of the specified file. This does not account for incomplete downloads. It is the file size x download count.

p. Tag Attributes:

* *format* -- (optional) The size format that is desired. Defined values are 'b', 'kb', 'mb', 'gb', 'pb', 'auto'. The size marker will be appended to the number. If not specified, it will output the number of bytes without any formatting.
* *decimals* -- (optional) The number of decimal places to use when formatting the number.

h2. <a name="mem_file_created">mem_file_created</a>

p. This tag will output the time the file was created.

p. Tag Attributes:

* *format* -- (optional) A format time format string that is compatible with the php function "strftime":http://www.php.net/strftime/.

h2. <a name="mem_file_modified">mem_file_modified</a>

p. This tag will output the time the file was modified.

p. Tag Attributes:

* *format* -- (optional) A format time format string that is compatible with the php function "strftime":http://www.php.net/strftime/.

h2. <a name="mem_file_accessed">mem_file_accessed</a>

p. This tag will output the time the file was accessed.

p. Tag Attributes:

* *format* -- (optional) A format time format string that is compatible with the php function "strftime":http://www.php.net/strftime/.

h4. Examples

*Ex1*: 
==<txp:mem_file id="1">==
==File: <txp:mem_file_link><txp:mem_file_name /> (<txp:mem_file_size format="auto" decimals="2" />)</txp:mem_file_link><br />==
==Category: <txp:mem_file_category /><br />==
==Downloads: <txp:mem_file_downloads /><br />==
==</txp:mem_file>==

*html output*:
==File: <a href="/textpattern/download.php?id=1" title="Download File myfile.zip">myfile.zip (123.45kb)</a><br />==
==Category: MyCategory<br />==
==Downloads: 45<br />==

*Ex2*:
==<txp:mem_file_link filename="myfile.zip" />==

*html output*:
==<a href="/textpattern/download.php?id=1" title="Download File myfile.zip">myfile.zip</a>==

*Ex3*:
==myfile.zip is <txp:mem_file_size filename="myfile.zip" format="auto" decimals="2" />==

*html output*:
myfile.zip is 123.45kb


# --- END PLUGIN HELP ---
<?php


# --- BEGIN PLUGIN CODE ---

	function mem_file_fetch_info($where)
	{
		global $file_base_path;

		// prevent being used as a tag
		if (is_array($where)) return;

		$result[] = array(
				'id' => 0,
				'filename' => '',
				'category' => '',
				'description' => '',
				'downloads' => 0,
				'size' => 0,
				'created' => 0,
				'modified' => 0,
				'accessed' => 0,
				'transfered' => 0
			);

		$rs = safe_row('*','txp_file',$where);

		if ($rs) {
			extract($rs);

			$result['id'] = $id;
			$result['filename'] = $filename;
			$result['category'] = $category;
			$result['description'] = $description;
			$result['downloads'] = $downloads;

			// get filesystem info
			$filepath = build_path($file_base_path, $filename);

			if (file_exists($filepath)) {
				$filesize = filesize($filepath);
				if ($filesize !== false)
					$result['size'] = $filesize;

				$created = filectime($filepath);
				if ($created !== false)
					$result['created'] = $created;

				$modified = filemtime($filepath);
				if ($modified !== false)
					$result['modified'] = $modified;

				$accessed = fileatime($filepath);
				if ($accessed !== false)
					$result['accessed'] = $accessed;
			}

			$result['transfered'] = $result['downloads'] * $result['size'];
		}

		return $result;
	}

	function mem_file_load($atts)
	{
		if (is_array($atts)) extract($atts);

		if (!isset($id)) $id = '';
		if (!isset($filename)) $filename = '';

		// set the global['mem_file'] value
		if (!empty($id) && $id != 0) {
			$GLOBALS['mem_file'] = mem_file_fetch_info("id='$id'");
		} elseif (!empty($filename)) {
			$GLOBALS['mem_file'] = mem_file_fetch_info("filename='$filename'");
		} elseif (isset($GLOBALS['mem_file']) && is_array($GLOBALS['mem_file'])) {
			//$GLOBALS['mem_file'] = $GLOBALS['mem_file'];
		} else {
			$GLOBALS['mem_file'] = false;
		}
	}

	function mem_file($atts,$body)
	{
		global $pfr;

		mem_file_load($atts);

		extract($atts);
		
		if (isset($form)) $body = fetch('Form','txp_form','name',$form);

		$finfo = $GLOBALS['mem_file'];

		// do it
		return parse($body);
	}

	function mem_file_link($atts,$body)
	{
		global $pfr;

		mem_file_load($atts);

		extract($atts);
		
		if (isset($form)) $body = fetch('Form','txp_form','name',$form);

		$finfo = $GLOBALS['mem_file'];

		$out = '';

		if (is_array($finfo)) {
			extract($finfo);

			if (!empty($body))
				$body = parse($body);
			else
				$body = $filename;

			$out .= make_download_link($id,$filename,$body);
		}

		return $out;
	}

// -------------------------------------------------------------
if (!function_exists('make_download_link')) {
	function make_download_link($id, $filename, $text)
	{
		global $pfr, $permlink_mode;
		
		if ($permlink_mode == 'messy') {
			return '<a href="'.$pfr.'/index.php?s=file_download&id='.$id.'" title="download file '.$filename.'">'.$text.'</a>';
		} else {
			return '<a href="'.$pfr.'/download/'.$id.'" title="download file '.$filename.'">'.$text.'</a>';
		}
	}
}

	function mem_file_name($atts)
	{
		mem_file_load($atts);

		$finfo = $GLOBALS['mem_file'];

		if (is_array($finfo))
			return $finfo['filename'];
		return '';
	}

	function mem_file_id($atts)
	{
		mem_file_load($atts);

		$finfo = $GLOBALS['mem_file'];

		if (is_array($finfo))
			return $finfo['id'];
		return '';
	}

	function mem_file_category($atts)
	{
		mem_file_load($atts);

		$finfo = $GLOBALS['mem_file'];

		if (is_array($finfo))
			return $finfo['category'];
		return '';
	}

	function mem_file_downloads($atts)
	{
		mem_file_load($atts);

		$finfo = $GLOBALS['mem_file'];

		if (is_array($finfo))
			return $finfo['downloads'];
		return '';
	}

	function mem_file_format_size($size,$format,$decimals)
	{
		if (is_numeric($decimals)) {
			$decimals = intval($decimals);

			if ($decimals < 0) $decimals = 2;
		} else
			$decimals = 2;

		$t = $size;

		switch(strtoupper(trim($format))) {
		default:
			$divs = 0;
			while ($t > 1024) {
				$t /= 1024;
				$divs++;
			}
			if ($divs==0) $format = ' b';
			elseif ($divs==1) $format = 'kb';
			elseif ($divs==2) $format = 'mb';
			elseif ($divs==3) $format = 'gb';
			elseif ($divs==4) $format = 'pb';
			break;
		case 'B':
			// do nothing
			break;
		case 'KB':
			$t /= 1024;
			break;
		case 'MB':
			$t /= (1024*1024);
			break;
		case 'GB':
			$t /= (1024*1024*1024);
			break;
		case 'PB':
			$t /= (1024*1024*1024*1024);
			break;
		}

		return number_format($t,$decimals) . $format;
	}

	function mem_file_size($atts)
	{
		mem_file_load($atts);

		extract($atts);
		if (!isset($decimals)) $decimals = 2;

		$out = '';

		$finfo = $GLOBALS['mem_file'];

		if (is_array($finfo)) {
			if (isset($format))
				$out = mem_file_format_size($finfo['size'],$format,$decimals);
			else
				$out = $finfo['size'];
		}
		return $out;
	}

	function mem_file_transfered($atts)
	{
		mem_file_load($atts);

		extract($atts);

		if (!isset($decimals)) $decimals = 2;

		$out = '';

		$finfo = $GLOBALS['mem_file'];

		if (is_array($finfo)) {
			if (isset($format)) {
				$out = mem_file_format_size($finfo['transfered'],$format,$decimals);
			} else {
				$out = $finfo['transfered'];
			}
		}
		return $out;
	}

	function mem_file_created($atts)
	{
		mem_file_load($atts);

		extract($atts);

		$out = '';

		$finfo = $GLOBALS['mem_file'];

		if (is_array($finfo)) {
			if (isset($format))
				$out = strftime($format,$finfo['created']);
			else
				$out = $finfo['created'];
		}
		return $out;
	}

	function mem_file_modified($atts)
	{
		mem_file_load($atts);

		extract($atts);

		$out = '';

		$finfo = $GLOBALS['mem_file'];

		if (is_array($finfo)) {
			if (isset($format))
				$out = strftime($format,$finfo['modified']);
			else
				$out = $finfo['modified'];
		}
		return $out;
	}
	
	function mem_file_accessed($atts)
	{
		mem_file_load($atts);

		extract($atts);

		$out = '';

		$finfo = $GLOBALS['mem_file'];

		if (is_array($finfo)) {
			if (isset($format))
				$out = strftime($format,$finfo['accessed']);
			else
				$out = $finfo['modified'];
		}
		return $out;
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

	include('classTextile.php');
	if (class_exists('Textile')) {
		$textile = new Textile();
		$plugin['help'] = $textile->TextileThis($plugin['help']);
	}

	$plugin['md5'] = md5( $plugin['code'] );

	// to produce a copy of the plugin for distribution, load this file in a browser. 

header('Content-type: text/plain');

echo chr(60)."?php\n\n"."/*\n * Plugin: ".$plugin['name']." v".$plugin['version']."\n * Author: ".$plugin['author']."\n * Generated: ".date("m.d.Y G:i")."\n */\n\n".'$'."plugin='" . base64_encode(serialize($plugin)) . "'\n?".chr(62);

}

?>
