<?php

	// plugin name must be url-friendly, with no spaces
	// please prepend the plugin name with a 3-letter developer identifier

$plugin['name'] = 'mem_cpg';

$plugin['author'] = 'Michael Manfre';

$plugin['author_uri'] = 'http://manfre.net/';

$plugin['version'] = '0.1';

	// short description of the plugin

$plugin['description'] = 'Link to images from <a href="http://coppermine.sourceforge.net/">Coppermine</a>, a PHP photo gallery.';

	// short helpfile (xhtml) - please be explicit in describing how the plugin
	// is called, and any parameters that may be edited by the site publisher

$plugin['help'] = '

	<p>Extended help</p>
	
	<p>This plugin allows you to directly access the <a href="http://coppermine.sourceforge.net">Coppermine</a> database and link
	to images. You must modify the first 5 lines of the plugin code and specify the coppermine database configuration.</p>

	<p>Form tags:
		<table cellspacing="0" cellpadding="5" border="1" width="75%">
		<tr>
			<th>Tag</th>
			<th>Description</th>
		</tr>
		<tr>
			<td>
			<code>&lt;txp:mem_cpg_title &gt;</code>
			</td>
			<td>
			This tag is replaced with the image\'s title.
			</td>
		</tr>
		<tr>
			<td>
			<code>&lt;txp:mem_cpg_imageurl &gt;</code>
			</td>
			<td>
			This tag is replaced with the url to the fullsized image.
			</td>
		</tr>
		<tr>
			<td>
			<code>&lt;txp:mem_cpg_imageurl &gt;</code>
			</td>
			<td>
			This tag is replaced with the url to the fullsized image.
			</td>
		</tr>
		<tr>
			<td>
			<code>&lt;txp:mem_cpg_normalurl &gt;</code>
			</td>
			<td>
			This tag is replaced with the url to the medium (normal) image.
			</td>
		</tr>
		<tr>
			<td>
			<code>&lt;txp:mem_cpg_thumburl &gt;</code>
			</td>
			<td>
			This tag is replaced with the url to the thumbnail image.
			</td>
		</tr>
		<tr>
			<td>
			<code>&lt;txp:mem_cpg_image &gt;</code>
			</td>
			<td>
			This tag is replaced with an html img tag of the fullsized image. The "alt" and "title" attributes are filled with the image\'s title.
			</td>
		</tr>
		<tr>
			<td>
			<code>&lt;txp:mem_cpg_normal &gt;</code>
			</td>
			<td>
			This tag is replaced with an html img tag of the medium (normal) image. The "alt" and "title" attributes are filled with the image\'s title.
			</td>
		</tr>
		<tr>
			<td>
			<code>&lt;txp:mem_cpg_thumb &gt;</code>
			</td>
			<td>
			This tag is replaced with an html img tag of the thumbnail image. The "alt" and "title" attributes are filled with the image\'s title.
			</td>
		</tr>
		</table>
	</p>
	
	<p>Attributes:
		<table cellspacing="0" cellpadding="5" border="1" width="75%">
		<tr>
			<th>Attribute Name</th>
			<th>Description</th>
			<th>Default</th>
		</tr>
		<tr>
			<td>form</td>
			<td>The form that should be used to output each image. If form is empty or omitted, then the plugin returns nothing.</td>
			<td>""</td>
		</tr>
		<tr>
			<td>class</td>
			<td>Specifies the stylesheet class attribute that will be put in the wraptag. This is not used if wraptag is empty.</td>
			<td>""</td>
		</tr>
		<tr>
			<td>wraptag</td>
			<td>When specified, this attribute will wrap the output of the plugin with the specified tag.</td>
			<td>""</td>
		</tr>
		<tr>
			<td>limit</td>
			<td>When doing an album display, this limits the number of images to display. All are displayed if this is not specified.</td>
			<td>""</td>
		</tr>
		<tr>
			<td>album</td>
			<td>This specifies the index of the album that will be used. This attribute must be specified.</td>
			<td>*Required*</td>
		</tr>
		<tr>
			<td>image</td>
			<td>When specified, this is the image position within the album. 1 is the first picture within an album. If not specified, then
			up to limit images will be displayed.</td>
			<td>""</td>
		</tr>
		</table>
	</p>
	
	
';

// The plugin code, as a string. NO PHP open/close tags please
// Be sure to escape any single quotes

$plugin['code'] = '

define (\'CPG_DBSERVER\', \'localhost\');
define (\'CPG_DBNAME\', \'coppermine\');
define (\'CPG_TABLE_PREFIX\', \'cpg130_\');
define (\'CPG_DBUSER\', \'user\');
define (\'CPG_DBPASS\', \'password\');

/////////////////////////////////////////////////
// Do not modify below this line
/////////////////////////////////////////////////

define("txpath",$txpcfg[\'txpath\']);
include txpath.\'/lib/txplib_gdb.php\';

//ini_set("display_errors",E_ALL ^ E_NOTICE);


function mem_cpg($atts) {
	global $DB;
	
	$ret = \'\';
	if(is_array($atts)) extract($atts);
	if(!isset($form)) $form = \'\';
	if(!isset($album)) $album = \'\';
	if(!is_numeric($image)) $image = \'\';
	if(!is_numeric($limit)) $limit = -1;
	if(!isset($class)) $class = \'\';
	if(!isset($wraptag)) $wraptag = \'\';
	 
	if ((empty($album) && empty($image)) || empty($form))
		return \'\';

	if ( !empty($class) ) {
		$class = \' class="\' . $class . \'"\';
	}

	$Form = fetch(\'Form\',\'txp_form\',\'name\',$form);

	$cpgDB = new gDB(CPG_DBSERVER, CPG_DBNAME, CPG_DBUSER, CPG_DBPASS, CPG_TABLE_PREFIX);
	
	$rs = $cpgDB->safe_rows(\'*\', \'config\', \'name="ecards_more_pic_target" OR name="fullpath" OR name="normal_pfx" OR name="thumb_pfx"\');

	if ($rs) {
		$url_base = \'\';
		$url_path = \'\';
		$normal_prefix = \'\';
		$thumb_prefix = \'\';
		foreach ($rs as $a) {
			extract($a);
			if ($name=="fullpath") {
				$url_path = $value;
			} elseif ($name=="normal_pfx") {
				$normal_prefix = $value;
			} elseif ($name=="thumb_pfx") {
				$thumb_prefix = $value;
			} elseif ($name=="ecards_more_pic_target") {
				$url_base = $value;
			}
		}
	} else {
		$ret = \'<!-- mem_cpg: config fetch failed. -->\';
	}

	if (empty($image))
		$rs = $cpgDB->safe_rows(\'filepath, filename,title\', \'pictures\', \'aid=\'.$album.($limit<0?\'\':\' LIMIT \'.$limit));
	else {
		if (is_numeric($image) && intval($image) > 0)
			$rs = $cpgDB->safe_rows(\'filepath, filename, title\', \'pictures\', \'aid=\'.$album.\' LIMIT 1 OFFSET \'.$image);
	}

	// reset to txp db
	mysql_select_db($DB->link);

	if ($rs) {
		foreach ($rs as $a) {
			extract($a);
			
			if ( empty($title) && empty($filepath) && empty($filename) )
				continue;
			
			$uri = $url_base.$url_path.$filepath;
			
			$uri=str_replace("http://","",$uri);
			$uri=preg_replace("/^([^\/].+)$/","http://$1",trim($uri));
			
			$f = str_replace("<txp:mem_cpg_title />", $title, $Form);
			$f = str_replace("<txp:mem_cpg_imageurl />", $uri.$filename, $f);
			$f = str_replace("<txp:mem_cpg_normalurl />", $uri.$normal_prefix.$filename, $f);
			$f = str_replace("<txp:mem_cpg_thumburl />", $uri.$thumb_prefix.$filename, $f);
			$f = str_replace("<txp:mem_cpg_image />", \'<img src="\'.$uri.$filename.\'" alt="\'.$title.\'" title="\'.$title.\'" />\', $f);
			$f = str_replace("<txp:mem_cpg_thumb />", \'<img src="\'.$uri.$thumb_prefix.$filename.\'" alt="\'.$title.\'" title="\'.$title.\'" />\', $f);
			
			$ret .= $f;
		}
		
	}
	
	return (!empty($wraptag)) ? tag($ret,$wraptag,$class) : $ret;
}

';

$plugin['md5'] = md5( $plugin['code'] );

// to produce a copy of the plugin for distribution, load this file in a browser. 

echo chr(60)."?php\n\n"."/*\n * Plugin: ".$plugin['name']." v".$plugin['version']."\n * Author: ".$plugin['author']."\n * Generated: ".date("m.d.Y G:i")."\n */\n\n".'$'."plugin='" . base64_encode(serialize($plugin)) . "'\n?".chr(62);

?>
