<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_article_custom';

$plugin['version'] = '0.2.1';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Functions the same as txp:article_custom, except it supports comma separated lists for the section and category attributes.';
$plugin['type'] = 0; // 0 for regular plugin; 1 if it includes admin-side code

@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

p. Refer to help for tag txp:article_custom.

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

function mem_article_custom($atts) 
{
	global $pretext,$prefs;
	extract($prefs);
	extract($pretext);
	if (is_array($atts)) extract($atts);

	$GLOBALS['is_article_list'] = true;
	
	$form      = (empty($form))      ? 'default' : $form;
	$form      = (empty($listform))  ? $form     : $listform;
	$limit     = (empty($limit))     ? '10'      : $limit;
	$category  = (empty($category))  ? ''        : doSlash($category);
	$section   = (empty($section))   ? ''        : doSlash($section);
	$excerpted = (empty($excerpted)) ? ''        : $excerpted;
	$author    = (empty($author))    ? ''        : doSlash($author);
	$sortby    = (empty($sortby))    ? 'Posted'  : $sortby;
	$sortdir   = (empty($sortdir))   ? 'desc'    : $sortdir;
	$month     = (empty($month))     ? ''        : $month;
	$keywords  = (empty($keywords))  ? ''        : doSlash($keywords);
	$frontpage = (empty($frontpage)) ? ''        : filterFrontPage();
	$futureposts = (empty($futureposts)) ? '' : $futureposts;
	
	$excerpted = ($excerpted=='y')  ? " and Excerpt !=''" : '';
	$author    = (!$author)    ? '' : " and AuthorID = '$author'";	
	$month     = (!$month)     ? '' : " and Posted like '{$month}%'";
	

	$categories = '';
	if (!empty($category)) {
		
		$cats = explode(",",$category);
		if (count($cats) > 0) {
			for( $i=0; $i < count($cats); $i++) {
				if ($i > 0)
					$categories .= " OR ";
				$categories .= "(Category1='".doSlash($cats[$i]).
								"' OR Category2='".doSlash($cats[$i]).
								"')";
			}
			$categories = " and (" . $categories . ") ";
		}
	}
	
	$sections = '';
	if (!empty($section)) {
		$secs = explode(",",$section);
		if (count($secs)>0) {
			$sections = ' and (';

			for( $i=0; $i < count($secs); $i++) {
				if ( $i > 0 )
					$sections .= " OR ";
				$sections .= "(section='".doSlash($secs[$i])."')";
			}
			$sections .= ')';
		}
	}

	
	
	if ($keywords) {
		$keys = explode(',',$keywords);
		foreach ($keys as $key) {
			$keyparts[] = " Keywords like '%".trim($key)."%'";
		}
		$keywords = " and (" . join(' or ',$keyparts) . ")"; 
	}
	
	
	$Form = fetch('Form','txp_form','name',$form);

	if ($futureposts==1) {
		$rs = safe_rows(
			"*, unix_timestamp(Posted) as uPosted",
			"textpattern",
			"1 and Status=4 ".
			$categories . $sections . $excerpted . $month . $author . $keywords . $frontpage .
			' order by ' . $sortby . ' ' . $sortdir . ' limit ' . $limit
		);
	} else {
		$rs = safe_rows(
			"*, unix_timestamp(Posted) as uPosted",
			"textpattern",
			"1 and Status=4 and Posted < now() ".
			$categories . $sections . $excerpted . $month . $author . $keywords . $frontpage .
			' order by ' . $sortby . ' ' . $sortdir . ' limit ' . $limit
		);
	}
	
	if ($rs) {
		foreach($rs as $a) {
			extract($a);

			$com_count = safe_field('count(*)','txp_discuss',"parentid='$ID'");
			
			$author = fetch('RealName','txp_users',"name",$AuthorID);
			$author = (!$author) ? $AuthorID : $author; 

			$out['thisid']          = $ID;
			$out['posted']          = $uPosted;
			$out['if_comments']     = ($Annotate or $com_count) ? true : false;
			$out['comments_invite'] = ($Annotate or $com_count)
									  ? formatCommentsInvite(
											$AnnotateInvite,$Section,$ID)
									  : '';
			$out['comments_count']  = $com_count;										  
			$out['author']          = $author;
			$out['permlink']        = formatPermLink($ID,$Section);
			$out['body']            = parse($Body_html);
			$out['excerpt']         = $Excerpt;
			$out['title']           = $Title;
			$out['url_title']       = $url_title;
			$out['category1']       = $Category1;
			$out['category2']       = $Category2;
			$out['section']         = $Section;
			$out['keywords']        = $Keywords;
			$out['article_image']   = $Image;

			$GLOBALS['thisarticle'] = $out;

			$article = $Form;
	
				// quick check for things not pulled from the db
			$article = doPermlink($article, $out['permlink'], $Title, $url_title);

			$articles[] = parse($article);
				
				// sending these to paging_link();
			$GLOBALS['uPosted'] = $uPosted;
			$GLOBALS['limit'] = $limit;

			unset($GLOBALS['thisarticle']);			
		}
		return join('',$articles);
	} else {
		return '';
	}
}


# --- END PLUGIN CODE ---

?>