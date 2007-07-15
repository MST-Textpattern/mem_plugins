<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_glinklist';

$plugin['version'] = '0.8.5';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Functions the same as txp_linklist, except groups all links under their category heading. This plugin supports breadcrumb style category headings.';
$plugin['type'] = 0; // 0 for regular plugin; 1 if it includes admin-side code

@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h2. Group Link List

h3. Client Plug-in Tags:

* *mem_glinklist*
* *mem_if_category_empty*
* *mem_categories*

h4. mem_glinklist

p. The tag mem_glinklist functions the same as txp_linklist, except groups all links under their category heading. The main purpose for this plugin is to allow for a page containing organized lists of links. This plugin supports breadcrumb style category headings.

p. Tag Attributes:

* *category* -- (optional) A comma separated list of categories that are to be group listed. If this value is not specified, or empty, then all categories will be
			displayed (the argname attribute overrides this behavior). To include the uncategorized links, then add a comma to the end. E.g. category="Blogs I Read,Comics," will output links for the
			categories "Blogs I Read", "Comics", and all uncategorized links.
* *columnclass* -- (optional) The CSS class that will be set for each column's wraptag. Default is "mem_list_column".
* *columns* -- (optional) This specifies how the links with in a category will be outputted. There will be up to _columns_ sets of links, where each set is wrapped in its own div that is tagged with the class attribute of "mem_list_column". Default is "1".
* *columnwraptag* -- (optional) The html tag that will wrap each column with in a category list. Default is "div".
* *form* -- (optional) The form that will be used. Default is "plainlinks".
* *headless* -- (optional) If enabled (set to "1"), then the category heading will be supressed. Default is "0" (disabled).
* *headclass* -- (optional) The CSS class that will be set for each head's wraptag. Default is "mem_list_head".
* *headwraptag* -- (optional) The html tag that will wrap each category heading. Default is "div".
* *limit* -- (optional) The max number of links to show per category. Default is 0 (no limit).
* *listclass* -- (optional) The CSS class that will be set for each category's list. Default is "mem_list".
* *listwraptag* -- (optional) The html tag that will wrap each category list. This contains the columns and head for a single category. Default is "div".
* *sort* -- (optional) Specifies how the links will be sorted within their category groupings. This accepts the same sorts as txp_linklist. Default is "linksort".
* *sortbycat* -- (optional) Specifies whether the categories will be sorted. Default is "0" (disabled).
* *uncategorized* -- (optional) Specifies the category name to use for links that are not linked to a category. Default is "Uncategorized".
* *argname* -- (optional) The url query argument used for category links. If the category attribute is not specified, then it will obtain the list of categories from the url query params (&quot;index.php?s=Section&lc=Cat1,Cat+2&quot;). Default is none.
* *catform* -- (optional) The name of the form to use to generate the category heading for a list. Use template tags defined for plugin "mem_categories". Default will use <code>'<txp:mem_cat_title />'</code>.


p. Template tags that can be used within the form for mem_glinklist are similar to those used by <code><txp:linklist /></code>. This plugin sets $GLOBALS['thislink'].

* <code><txp:link /></code> -- linklist tag
* <code><txp:linkdesctitle /></code> -- linklist tag
* <code><txp:link_description /></code> -- linklist tag
* <code><txp:mem_link_date /></code> -- This outputs the date the link was entered. This tag can be used by linklist.
** *format* -- (optional) Any strftime format string that will be used for displaying of the date


&lt;div class="mem_list"&gt;
&lt;div class="mem_list_head"&gt;Category Name</div&gt;
&lt;div class="mem_list_column"&gt;...&lt;/div&gt;
&lt;div class="mem_list_column"&gt;...&lt;/div&gt;
&lt;/div&gt;


p. Examples:

* <code><txp:mem_glinklist form="mylinks" category="Blogs,News" class="linklist" sort="linksort" /></code>


h4. mem_if_category_empty

p. This will conditionally parse the enclosed tags if the specified category is empty. Support <code><txp:else /></code>.

p. Tag Attributes:

* *name* -- The name of the category.
* *argname* -- (optional) The url query argument used for determining the category. The name attribute should not be specified. Default is none.
* *type* -- (optional) The type of category specified by the "name" attribute. Default is 'link'.

p. Examples:

* <code><txp:mem_if_category_empty name="stuff">The category has is empty.<txp:else />The category is not empty.</txp:mem_if_category_empty></code>

h4. mem_categories

p. This will output a list of categories.

p. Tag Attributes:

* *form* -- Specifies which form contains the list template.
* *type* -- (optional) If specified, only categories of the specified type will be listed. Default is all categories.
* *sort* -- (optional) Specifies how the category list will be sorted. Default is 'name'.
* *break* -- (optional) The tag that will break the items in the list. Default is none.
* *limit* -- (optional) The max number of categories to show. Default is 0 (no limit).
* *wraptag* -- (optional) The html tag that will wrap the category list. Default is none.
* *argname* -- (optional) The url query argument used for category links. Default is 'lc' for categories of type 'link', otherwise 'c' is default. 


p. Template tags that can be used within the form for mem_categories. This plugin sets $GLOBALS['thiscategory'] with the current category that is being processed.

* <code><txp:mem_cat_id /></code> -- The numeric id of the category.
* <code><txp:mem_cat_name /></code> -- The name of the category.
* <code><txp:mem_cat_title /></code> -- The title of the category.
* <code><txp:mem_cat_type /></code> -- The category type name. E.g. "link", "article", "image", etc.
* <code><txp:mem_cat_parent_id /></code> -- The numeric id of the current categories parent.
* The remaining tags can be used by any plugin that sets $GLOBALS['thiscategory'].
* <code><txp:mem_cat_count /></code> -- The number of items (article,image,link,or file) in the category.
* <code><txp:mem_cat_link /> or <txp:mem_cat_link><txp:mem_cat_name /></txp:mem_cat_link></code> -- An html link tag that points to the specified category with the proper query parameter. Default link text is the category title.
** *argname* -- (optional) The url query argument used for category links. Default is 'lc' for categories of type 'link', otherwise 'c' is default.
** *baseurl* -- (optional) The base url that will be prepended to all generated links. Defaults to site root.
** *class* -- (optional) This specifies the CSS class name. Default is "mem_cat_link".
* <code><txp:mem_cat_crumbtrail /></code> -- The c
** *crumbtrail* -- (optional) The string that will be placed between each level of the category heirarchy. If this attribute is not specified, then the category heirarchy will not be shown.
** *crumbroot* -- (optional) Specifies whether the root (empty) category will be shown. To enable, set attribute to "1".

p. Examples:

* <code><txp:mem_categories form="cat_list" type="link" /></code>
* <code><txp:mem_categories type="link"><txp:mem_cat_id /> - <txp:mem_cat_link /></txp:mem_categories></code>

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

////////////////////////////////////////////////////////////
// Plugin mem_glinklist
// Author: Michael Manfre (http://manfre.net/)
// Revisions: 
//		2005.05.31
//			Added argname attribute to txp:mem_if_category_empty.
//			Fixed txp:mem_link_date.
//
////////////////////////////////////////////////////////////

// -------------------------------------------------------------
// This makes mem_glinklist compatible with Txp v1.0rc1
if (!function_exists('lAtts')) {
		function lAtts($pairs, $atts) { // trying this out as well
			foreach($pairs as $name => $default) {
				$out[$name] = isset($atts[$name]) ? $atts[$name] : $default;
			}
			return ($out) ? $out : false;
		}
}

if (!function_exists('fetch_form')) {
	function fetch_form($name)
	{
		return (empty($name) ? '' : fetch('Form','txp_form','name',$name));
	}
}


function mem_cat_name($atts)
{
	global $thiscategory;
	return @$thiscategory['name'];
}

function mem_cat_type($atts)
{
	global $thiscategory;
	return @$thiscategory['type'];
}

function mem_cat_title($atts)
{
	global $thiscategory;
	return @$thiscategory['title'];
}

function mem_glinklist($atts,$body='')
{
	extract(lAtts(array(
			'category'		=>	'',
			'columns'		=>	'1',
			'form'			=>	'',
			'limit'			=>	'0',
			'sort'			=>	'linksort',
			'headless'		=>	false,
			'uncategorized'	=>	'Uncategorized',
			'listwraptag'	=>	'div',
			'headwraptag'	=>	'div',
			'columnwraptag'	=>	'div',
			'listclass'		=>	'mem_list',
			'headclass'		=>	'mem_list_head',
			'columnclass'	=>	'mem_list_column',
			'sortbycat'		=>	false,
			'argname'		=>	'',
			'catform'		=>	''
		),$atts));


	if (empty($category)) {
		if (!empty($argname))
			$category = gps($argname);
		else
			$category = '*';
	}
	
	if ($category == '*') {
		$cats = mem_get_all_categories('link');
	} else if (!empty($category)) {
		$cats = explode(",",$category);
	} else {
		return false;
	}

	if (!empty($catform))
		$catform = fetch_form($catform);
	if (empty($catform))
		$catform = '<txp:mem_cat_title />';

	if (!empty($form))
		$Form = fetch_form($form);
	else
		$Form = '';
	
	if (empty($Form)) {
		if (!empty($body))
			$Form = $body;
		else
			$Form = '<txp:link />';
	}
	
	if (is_numeric($limit)) $limit = intval($limit);
	if (is_numeric($columns)) $columns = intval($columns);
	if ($columns < 1) $columns = 1;
	if ($sortbycat == '1') $sortbycat  = true;

	$list = array();

	foreach ($cats as $c) {
		$links = safe_rows("*","txp_link"," category LIKE '{$c}' order by category, " . $sort . ($limit<=0?'':" LIMIT {$limit}"));
		
		if ($links === false) continue;
		
		$offset = 0;
		$count = count($links);
		$rowsize = $count / $columns;		
		
		if ($offset >= $count) continue;

		$c_out = '';

		
		if (empty($c)) 
		{
			$c = $uncategorized;
			$cdata = safe_row('*', 'txp_category', " name = 'root'");
			
			$cdata = array(
				'id'	=> 0,
				'name'	=> $c,
				'type'	=> 'link',
				'lft'	=> 0,
				'rgt'	=> 0,
				'title'	=> $c,
				'parent'	=> ''
			);
		}
		else
		{
			$cdata = safe_row("*","txp_category"," name LIKE '{$c}'");
		}
		

		$GLOBALS['thiscategory'] = $cdata;

		$hd = mem_parse_cat($cdata,$catform,$argname);

		if (!$headless) {
			$c_out .= doTag($hd,$headwraptag,$headclass).n;
		}
			

		while ($offset < $count) {
			$slice = array_slice( $links, $offset, min($rowsize,$count-$offset) );
			
			$offset += $rowsize;
			
			$col_out = '';
			
			foreach ($slice as $l) {
				extract($l);
				
				$surl = htmlspecialchars($url);
				$link = '<a href="'.$surl.'">'.$linkname.'</a>';
				$linkdesctitle = '<a href="'.$surl.
				    '" title="'.$description.'">'.$linkname.'</a>';

				$cl = $Form;
				
				$cl = str_replace("<txp:link />", $link, $Form);
				$cl = str_replace("<txp:linkdesctitle />", $linkdesctitle, $cl);
				$cl = str_replace("<txp:link_description />", $description, $cl);
				
				$GLOBALS['thislink'] = $l;
				$col_out .= parse($cl);
				unset($GLOBALS['thislink']);
			}
			
			$c_out .= doTag($col_out,$columnwraptag,$columnclass).n;
		}
		
		
		$list[strtolower($hd)] = doTag($c_out,$listwraptag,$listclass).n;
		
		unset($GLOBALS['thiscategory']);
	}
	
	$output = '';

	if ($sortbycat)
		sort($list);
	
	foreach ($list as $key => $val) {
		$output .= $val;
	}
	
	return $output;
}

function mem_link_date($atts)
{
	global $thislink;

	extract(lAtts(array(
		'format'	=>	'%B %d, %Y'
	),$atts));

	$dt = explode(' ',$thislink['date']);
	$ddate = explode('-',$dt[0]);
	$dtime = explode(':',$dt[1]);
	
	$date = mktime($dtime[0],$dtime[1],$dtime[2],$ddate[1],$ddate[2],$ddate[0]);

	if (!empty($format)) {
		$date = strftime($format,$date);
	}

	return $date;	
}

function mem_get_cat_count($type,$name)
{
	$count = 0;

	if (!empty($name)) {
		if ($type == 'link') {
			$count = safe_count('txp_link',"category = '{$name}'");
		} else if ($type == 'article') {
			$count = safe_count('textpattern',"Category1 = '{$name}' OR Category2 = '{$name}'");
		} else if ($type == 'file') {
			$count = safe_count('txp_file',"name= '{$name}'");
		} else if ($type == 'image') {
			$count = safe_count('txp_image',"category = '{$name}'");
		}
	}

	return $count;
}

function mem_cat_count($atts,$thing='')
{
	global $thiscategory;
	extract($thiscategory);
	return mem_get_cat_count($type,$name);
}

function mem_cat_crumbtrail($atts)
{
	global $thiscategory;
	
	extract(lAtts(array(
		'crumbroot'		=> '',
		'crumbtrail'	=> '::'
	),$atts));

	return $crumbroot.implode( $crumbtrail, mem_get_breadcrumb($thiscategory['name']) );
}

function mem_cat_parent_id($atts)
{
	global $thiscategory;
	
	extract($thiscategory);
	
	$field = safe_field("id", "txp_category", "parent = ${name}");
	
	return ($field===false ? '' : $field);
}

function mem_cat_parent_title($atts)
{
	global $thiscategory;
	
	extract($thiscategory);
	
	$field = safe_field("title", "txp_category", "parent = ${name}");
	
	return ($field===false ? '' : $field);
}

function mem_cat_parent_link($atts,$thing='')
{
	global $permlink_mode,$thiscategory;
	
	extract(lAtts(array(
		'class'		=>	__FUNCTION__,
		'argname'	=>	'',
		'baseurl'	=>	hu
	),$atts));
	extract($thiscategory);
	
	if (!isset($argname) or empty($argname)) {
		$argname = ($type=='link') ? 'lc' : 'c';
	}

	$parent = safe_row("name,title", "txp_category", "parent = ${name}");
	
	if ($parent===false)
		return '';

// Remove the next three '//' to enable clean urls
//	if ($permlink_mode=='messy' or $argname=='lc')
		$href = $baseurl . '?'.$argname.'='.urlencode($parent['name']);
//	else
//		$href = $baseurl . 'category/'.urlencode($name);
		
	if (empty($thing)) $thing = $parent['title'];

	return tag(str_replace("& ","&#38; ", $thing),'a',' href="'.$href.'" title="'.$parent['title'].'"');
}

function mem_cat_link($atts,$thing='')
{
	global $permlink_mode,$thiscategory;
	
	extract(lAtts(array(
		'class'		=>	__FUNCTION__,
		'argname'	=>	'',
		'baseurl'	=>	hu
	),$atts));
	extract($thiscategory);
	
	if (!isset($argname) or empty($argname)) {
		$argname = ($type=='link') ? 'lc' : 'c';
	}

// Remove the next three '//' to enable clean urls
	if ($permlink_mode=='messy' or $argname=='lc')
		$href = $baseurl . '?'.$argname.'='.urlencode($name);
	else
		$href = $baseurl . 'category/'.urlencode($name);

	if (empty($thing)) $thing = $title;

	return tag(str_replace("& ","&#38; ", $thing),'a',' href="'.$href.'" title="'.$title.'"');
}

function mem_parse_cat($cdata,$form,$argname='')
{
	extract($cdata);

	if (empty($argname)) {
		if (@$type=='link')
			$argname = 'lc';
		else
			$argname = 'c';
	}
	$GLOBALS['thiscategory']['argname'] = $argname;

	$cl = str_replace("<txp:mem_cat_id />", $id, $form);
	$cl = str_replace("<txp:mem_cat_name />", $name, $cl);
	$cl = str_replace("<txp:mem_cat_title />", $title, $cl);
	$cl = str_replace("<txp:mem_cat_type />", $type, $cl);
	$cl = str_replace("<txp:mem_cat_parent_type />", $type, $cl);
	$cl = str_replace("<txp:mem_cat_parent_name />", $parent, $cl);

	$result = parse($cl);

	return $result;
}

function mem_categories($atts,$body='')
{
	extract(lAtts(array(
			'type'		=>	'%',
			'form'		=>	'',
			'sort'		=> 'name',
			'break'		=> '',
			'limit'		=> '0',
			'wraptag'	=> '',
			'argname'	=> '',
		),$atts));

	if (!empty($form))
		$Form = fetch_form($form);

	if (!isset($Form) and empty($Form)) {
		if (!empty($body))
			$Form = $body;
		else
			$Form = '<txp:mem_cat_name />';
	}

	$q = "type LIKE '{$type}'";
	if (!empty($sort))
		$q .= " ORDER BY {$sort}";
	if (is_numeric($limit) && intval($limit) > 0)
		$q .= " LIMIT {$limit}";

	$rs = safe_rows_start("*","txp_category", $q);

	$result = array();
	
	if ($rs) {

		while ($a = nextRow($rs)) {
			if ($a['name'] == 'root') continue;
			
			$GLOBALS['thiscategory'] = $a;

			$result[] = mem_parse_cat($a,$Form,$argname);
			
			unset($GLOBALS['thiscategory']);
		}

		if (!empty($result)) {
			if ($wraptag == 'ul' or $wraptag == 'ol') {
				return doWrap($result, $wraptag, $break);
			}	
			
			return ($wraptag) ? tag(join($break,$result),$wraptag) : join(n,$result);
		}
	}

	return false;
}

function mem_get_breadcrumb ($cat) {
	$parent = safe_field("parent", "txp_category", "name='$cat' and type='link'");
	if ($parent) {
		$res = mem_get_breadcrumb($parent);
		$res[] = $cat;
	} else {
		$res = array();
	}
	
	return $res;
}

function mem_get_all_categories($type)
{
	$out['name'] = array();
	
	$rs = getTree('root',$type);
	if ($rs) {
		foreach($rs as $r) {
			extract($r);
			
			$out['name'][] = ($name=='root'?'':$name);
		}
	}

	return $out['name'];
}

function mem_if_category_empty($atts,$thing)
{
	extract(lAtts(array(
			'type'		=>	'link',
			'name'		=>	'',
			'argname'	=>	'',
			'tolerance'	=>	0,
		),$atts));

	if (empty($name)) {
		if (!empty($argname))
			$name = gps($argname);
		else
			return;
	}

	if (!is_numeric($tolerance)) $tolerance = 0;

	$tolerance = abs(intval($tolerance));

	$count = mem_get_cat_count($type,$name) - $tolerance;

	$condition = $count<=0;
	
	return parse(EvalElse($thing, $condition));
}


# --- END PLUGIN CODE ---

?>
