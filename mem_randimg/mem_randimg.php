<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_randimg';

$plugin['version'] = '0.4';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Displays a random image from the specified categories.';
$plugin['type'] = 0; // 0 for regular plugin; 1 if it includes admin-side code

@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

<p>Displays a random image from the specified categories and populates the given template.</p>

<table>
<tr>
	<th>Tag</th>
	<th>Result</th>
</tr>
<tr>
	<td>&lt;txp:mem_img /&gt;</td>
	<td>An html img tag that contains alt, height and width for the image.</td>
</tr>
<tr>
	<td>&lt;txp:mem_img_url /&gt;</td>
	<td>A url path from the root to the image.</td>
</tr>
<tr>
	<td>&lt;txp:mem_img_thumbnail /&gt;</td>
	<td>An html img tag that contains alt, height and width for the image's thumbnail.</td>
</tr>
<tr>
	<td>&lt;txp:mem_img_thumbnail_url /&gt;</td>
	<td>A url path from the root to the thumbnail.</td>
</tr>
<tr>
	<td>&lt;txp:mem_img_caption /&gt;</td>
	<td>The image's caption.</td>
</tr>
<tr>
	<td>&lt;txp:mem_img_alt /&gt;</td>
	<td>The specified ALT value for the image..</td>
</tr>
<tr>
	<td>&lt;txp:mem_img_category /&gt;</td>
	<td>The image's category.</td>
</tr>
<tr>
	<td>&lt;txp:mem_img_width /&gt;</td>
	<td>The image's width.</td>
</tr>
<tr>
	<td>&lt;txp:mem_img_height /&gt;</td>
	<td>The image's height.</td>
</tr>
<tr>
	<td>&lt;txp:mem_img_author /&gt;</td>
	<td>The name of the image uploader.</td>
</tr>
<tr>
	<td>&lt;txp:mem_img_date /&gt;</td>
	<td>The date the image was uploaded.</td>
</tr>
</table>

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

////////////////////////////////////////////////////////////
// Plugin mem_randimg
// Author: Michael Manfre (http://manfre.net/)
////////////////////////////////////////////////////////////

function mem_randimg($atts, $thing='') {
	global $txpcfg,$path_from_root,$img_dir;
	extract($txpcfg);

	if(is_array($atts)) extract($atts);

	if (!empty($thing))
	{
		$Form = $thing;
	}
	else if (!empty($form))
	{
		$Form = fetch('Form','txp_form','name',$form);
	}
	else
	{
		$Form = '<txp:mem_img />';
	}
	
	$categories = '';
	
	if (!empty($category)) {
		$cats = explode(",",$category);
		for( $i=0; $i < count($cats); $i++) {
			$categories .= ("category='$cats[$i]' OR ");
		}
		$categories = rtrim($categories,"OR ");
	}
			
	$qparts = array(
		(!empty($categories)) ? $categories : '1',
		"order by",
		"rand()",
		"limit 1"
	);
	
	$out = '';
		
	$rs = safe_row("*","txp_image",join(' ',$qparts));

	if ($rs) {
		extract($rs);
		
		$img_url = $path_from_root.$img_dir.'/'.$id.$ext;
		$img_thumb_url = $path_from_root.$img_dir.'/'.$id.'t'.$ext;
		
		$pairs = array(
			"<txp:mem_img />"	=>	'<img src="'.$img_url.'" alt="'.$alt.'" style="height:'.$h.'px;width:'.$w.'px" />',
			"<txp:mem_img_url />"	=>	$img_url,
			"<txp:mem_img_thumbnail />"	=>	'<img src="'.$img_thumb_url.'" alt="'.$alt.'" />',
			"<txp:mem_img_thumbnail_url />"	=>	$img_thumb_url,
			"<txp:mem_img_caption />"	=>	$caption,
			"<txp:mem_img_alt />"	=>	$alt,
			"<txp:mem_img_category />"	=>	$category,
			"<txp:mem_img_width />"	=>	$w,
			"<txp:mem_img_height />"	=>	$h,
			"<txp:mem_img_author />"	=>	$author,
			"<txp:mem_img_date />"	=>	$date,			
		);
		
		$out = str_replace( array_keys($pairs), array_values($pairs), $Form);
	}
	
	return $out;
}

# --- END PLUGIN CODE ---

?>
