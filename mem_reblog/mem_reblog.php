<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_reblog';

$plugin['version'] = '0.2';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'This is an admin and client side plugin that adds support for adding entries from a ReBlog feed.';
$plugin['type'] = 1; // 0 for regular plugin; 1 if it includes admin-side code

@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h2. Textpattern ReBlog Plug-in

p. This is an admin and client side plug-in that adds support for adding entries from a <a href="http://reblog.org">ReBlog</a> feed. This plugin was originally a port of the WordPress plugin created by Michal Migurski.

h3. Requirements:

* Zem's admin side plugin mod.
* Magpie RSS library version 0.5 or newer.

h3. Installation:

# Edit the plugin configuration located within the plugin code text box. The values in the Configuration Section will need to be modified for this plugin to work.
# If you enabled MAGPIE_CACHE_ON, then make sure that the cache folder exists and is writeable.
# Activate the plugin.
# After activating this plugin, you will need to navigate to the Admin-->ReBlog tab. This tab is not visible from the Admin-->Plugins tab.
# Click the "Install ReBlog" button. It should display a success message.

h3. Admin Interface:

p. Once installed, this plug-in will add a sub-tab titled "ReBlog" to the "admin" tab. This tab has a button that will add new and update existing entries from the configured ReBlog feed. This is the only time updates will happen.

h3. Client Plug-in Tags:

* *mem_reblog_guid*
* *mem_if_reblog_post* 

h4. mem_reblog_guid

p. This returns the ReBlog guid of an imported entry.

p. Attributes:

* *id* -- (optional) If specified, this is the id of the entry. If not specified, it will use the id of the current article (for use in article templates).

p. Examples:

* <code><txp:mem_reblog_guid id="5" /></code>
* <code><txp:mem_reblog_guid /></code>

h4. mem_if_reblog_post

p. This is a conditional tag that displays the text enclosed by the tag only if the specified entry is a ReBlog entry.

p. Attributes:

* *id* -- (optional) If specified, this is the id of the entry. If not specified, it will use the id of the current article (for use in article templates).

p. Examples:

* <code><txp:mem_if_reblog_post id="5">This is a reblog post</txp_mem_if_reblog_post></code>
* <code><txp:mem_if_reblog_post>This is a reblog post with in an article</txp:mem_if_reblog_post></code>


# --- END PLUGIN HELP ---
<?php


# --- BEGIN PLUGIN CODE ---
//////////////////////////////////////////////////////////////////////////////////////////
// Configuration Section
//////////////////////////////////////////////////////////////////////////////////////////

global $mem_status_list, $mem_reblog;

$mem_reblog = array();

$mem_status_list = array(
		1	=>	'Draft',
		2	=>	'Hidden',
		3	=>	'Pending',
		4	=>	'Published'
	);

function mem_article_status($item, $var) {
	global $mem_status_list;
	return selectInput($item, $mem_status_list, $var);
}


function mem_reblog_install() {
	global $mem_reblog;

	if (!safe_field('val','txp_prefs',"name='mem_reblog_author'")) {
		set_pref('mem_reblog_author','ReBlog','mem_reblog',1);
	}
	if (!safe_field('val','txp_prefs',"name='mem_reblog_post_status'")) {
		set_pref('mem_reblog_post_status','
		safe_insert('txp_prefs',"prefs_id=1,name='mem_reblog_post_status',val='4',type='1',event='mem_reblog',html='mem_article_status'");
	}
	if (!safe_field('val','txp_prefs',"name='mem_reblog_post_section'")) {
		safe_insert('txp_prefs',"prefs_id=1,name='mem_reblog_post_section',val='article',type='1',event='self_reg',html='priv_levels'");
		$mem_self['new_user_priv'] = '0';
	}
}

function mem_reblog_init() {
	global $mem_reblog;
	
	$mem_reblog['post_status'] = safe_field('val','txp_prefs'
}
	
    // what will be the status of the imported entries?
    //	1 == draft
    //	2 == hidden
    //	3 == pending
    //	4 == published    
    define('REBLOG_POST_STATUS', 4);
    
    // what will be the section for the imported entries?
    // make sure this section exists, otherwise it will cause display problems
    define('REBLOG_POST_SECTION', 'article');
    
    // what categories will be assigned to the imported entries?
    // these should exist (but it is not as critical)
    define('REBLOG_POST_CATEGORY1', 'ReBlog');
    define('REBLOG_POST_CATEGORY2', '');
    
    // Complete URL to RSS 2.0 output from reBlog, typically ends with "rss.php?v=2".
    define('REBLOG_URL', 'http://some.domain.com/refeed/out/rss.php?v=2');

    // set value to 1 to enable debugging. e
    // extra information will be displayed when fetching entries.
    define('REBLOG_DEBUG', 1);
    

    // this specifies the directory where magpie is located.
    // default is './textpattern/lib/magpierss/'
    define('MAGPIE_DIR', dirname( __FILE__ ). '/magpierss/');
    
    // set value to 1
    //		magpie will cache the fetched rss data for a period of time
    // set value to 0
    //		magpie will not cache the fetched rss data
    define('MAGPIE_CACHE_ON', 1);
    
    // How long to store cached RSS objects? In seconds.
    define('MAGPIE_CACHE_AGE', 60*60);

    // where can magpie use for cache files. this must be writeable.
    // default is './textpattern/cache/'
    define('MAGPIE_CACHE_DIR', dirname( __FILE__ ).'/../cache/' );
    
    // what name should be used for the table that keeps track of what entries have
    // already been imported?
    $GLOBALS['tablereblog'] = "txp_reblog";


	// what is the table name that holds the textpattern entries?
	$GLOBALS['tableposts'] = "textpattern";


//////////////////////////////////////////////////////////////////////////////////////////
// DO NOT MODIFY BELOW THIS LINE
//////////////////////////////////////////////////////////////////////////////////////////

	/** mem_reblog_guid
	 * This is a client side tag. <txp:mem_reblog_guid id='##' />
	 * 
	 * @param	array	atts	An array of attributes that were specified in the tag.
	 *							'id' is the only recognized (and optional) attribute.
	 *							If 'id' is not specified, then the current article id
	 *							will be used by default.
	 *
	 * @return	string	The guid for the specified post will be returned, or an
	 *					empty string if not found.
	 */
	function mem_reblog_guid ($atts)
	{
		global $thisarticle;
		
		extract(lAtts(array(
			'id'    => $thisarticle['thisid']
		),$atts));
		
		$found = false;
		
		if ($id != '') {
			$q = sprintf("SELECT `reblog_guid` AS guid from `{$GLOBALS['tablereblog']}` WHERE `reblog_post_id` = '%s'",$id);
			
			$found = getThing($q);
		}
		
		return ($found===false?'':$found);
	}

	/** mem_if_reblog_post
	 * This is a client side tag. <txp:mem_if_reblog_post id='##'>Body</txp:mem_if_reblog_post>
	 * 
	 * @param	array	atts	An array of attributes that were specified in the tag.
	 *							'id' is the only recognized (and optional) attribute.
	 *							If 'id' is not specified, then the current article id
	 *							will be used by default.
	 *
	 * @param	string	body	This is the text enclosed by the <txp:mem_if_reblog_post>
	 *							tags. If the specified article is a reblog entry, then
	 *							body will be parsed by textpattern for output.
	 *
	 * @return	string	The guid for the specified post will be returned, or an
	 *					empty string if not found.
	 */
	function mem_if_reblog_post ($atts,$body)
	{
		return (mem_reblog_guid($atts) == ''?'': parse($body));
	}
    
   /** reblog_is_installed
    * Check whether Textpattern reBlog plug-in is installed by examining posts table.
    *
    * @return   bool    true iff plug-in is installed.
    */
    function reblog_is_installed()
    {
    	return (getRow("SHOW TABLES LIKE '{$GLOBALS['tablereblog']}'") !== false) ? true : false;
    }

   
   /** reblog_install
    * Attempt to install reBlog plug-in by creating reblog posts table.
    *
    * @return   bool    true iff plug-in is installed.
    *                   (see reblog_is_installed())
    */
    function reblog_install()
    {
        safe_query("CREATE TABLE `{$GLOBALS['tablereblog']}` (
                                     ID     INT(10) UNSIGNED NOT NULL auto_increment,
                                     reblog_post_id     INT(10) UNSIGNED NOT NULL,
                                     reblog_guid   VARCHAR(240) NOT NULL,
                                     
                                     PRIMARY KEY (ID),
                                     UNIQUE (reblog_post_id),
                                     UNIQUE (reblog_guid)
                                 )");
		
        return reblog_is_installed();
    }
    
   /** reblog_fetch
    * Attempt to fetch posts from reBlog using included MagpieRSS library.
    *
    * @param    string  reblog_URL  complete URL to RSS 2.0 output from reBlog,
    *                               typically ends with "rss.php?v=2".
    *
    * @return   mixed   boolean false if fetch failed.
    *                   otherwise, array with number of new items fetched,
    *                   number of older items updated, and debug information.
    */
    function reblog_fetch($reblog_URL)
    {
        // require the MagpieRSS library, for pulling in the code.
        require_once(MAGPIE_DIR.'rss_fetch.inc');
        require_once(MAGPIE_DIR.'rss_utils.inc');
    
        $rss = fetch_rss($reblog_URL);
        $added = $updated = 0;

        // ensure that the reblog feed is RSS 2.0
        if(($rss->feed_type != 'RSS') || ($rss->feed_version != '2.0')) {
            return false;
        
        } else {
        
            // perform most of the fetch inside an output buffer. facilitates the debugging logfile.
            ob_start(); {
            
                print_r($rss);
                print_r($_POST);
                print_r(debug_backtrace());
            
                // iterate over each item in the fetched RSS
                foreach($rss->items as $i => $item) {
            
                    print("---- item {$i} ----\n");
                    print(sprintf("item[dc][date]: %s -> %s -> %s (%s)\n", $item['dc']['date'], parse_w3cdtf($item['dc']['date']), date('r', parse_w3cdtf($item['dc']['date'])), gmdate('r', parse_w3cdtf($item['dc']['date']))));

		    $content = $item['description'];
		    $content .= sprintf('<p><a href="%s">Originally</a> %s from <a href="%s">%s</a></p>',
					$item['rb']['via_url'],
					(isset($item['rb']['source_author']) ? "posted by " . $item['rb']['source_author'] : ""),
					$item['rb']['source_url'],
					$item['rb']['source']
					);


                    // determine whether the item's GUID has been encountered before
                    $post = getRow(sprintf("SELECT p.id AS id
                                                                   FROM `{$GLOBALS['tablereblog']}` AS r
                                                                   LEFT JOIN `{$GLOBALS['tableposts']}` AS p
                                                                   ON r.reblog_post_id = p.id
                                                                   WHERE r.reblog_guid = '%s'",
                                                               mysql_real_escape_string($item['rb']['guid'])));
                    
                    // update existing post if the GUID was found, otherwise insert a new one.
                    if($post !== false) {
                    	extract($post);
                    
                        $q = sprintf("UPDATE `{$GLOBALS['tableposts']}` SET Body = '%s', Body_html = '%s', Title = '%s', Excerpt = '%s', LastMod = '%s' WHERE ID = '%s'",
                                     mysql_real_escape_string((($content))),
                                     mysql_real_escape_string((($content))),
                                     mysql_real_escape_string($item['title']),
                                     mysql_real_escape_string((($item['summary']))),
                                     date('Y-m-d H:i:s'),
                                     mysql_real_escape_string($id)
                                     );
                        print("{$q}\n");
                        
                        $updated++;
    
                    } else {
                    
                        $q = sprintf("INSERT INTO `{$GLOBALS['tableposts']}`
                                         (AuthorID, Posted, LastMod, LastModID, Body, Body_html, Title, Excerpt, Status, Section, Category1, Category2)
                                         VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
                                     REBLOG_AUTHOR_ID,
                                     date('Y-m-d H:i:s', parse_w3cdtf($item['dc']['date'])),
                                     date('Y-m-d H:i:s', parse_w3cdtf($item['dc']['date'])),
                                     REBLOG_AUTHOR_ID,
                                     mysql_real_escape_string((($content))),
                                     mysql_real_escape_string((($content))),
                                     mysql_real_escape_string($item['title']),
                                     mysql_real_escape_string((($item['summary']))),
                                     REBLOG_POST_STATUS,
                                     REBLOG_POST_SECTION,
                                     REBLOG_POST_CATEGORY1,
                                     REBLOG_POST_CATEGORY2
                                     );
                        safe_query($q); print("{$q}\n");
                        
                        $post = getRow("SELECT LAST_INSERT_ID() AS id FROM `{$GLOBALS['tableposts']}`");
        
        				if ($post !== false) {
        					extract($post);
        					
	                        $added++;
	                        
	                        // make sure that we're not about to conflict with an existing GUID in the reblog table
	                        $q = sprintf("DELETE FROM `{$GLOBALS['tablereblog']}`
	                                         WHERE reblog_guid = '%s'",
	                                     mysql_real_escape_string($item['rb']['guid']));
	                                     
	                        safe_query($q); print("{$q}\n");
	                        
	                        $q = sprintf("INSERT INTO `{$GLOBALS['tablereblog']}`
	                                         (reblog_post_id, reblog_guid)
	                                         VALUES ('%s', '%s')",
	                                     mysql_real_escape_string($id),
	                                     mysql_real_escape_string($item['rb']['guid']));
	                                     
	                        safe_query($q); print("{$q}\n");
	                        
	                        // pick a category, any category
	                        $q = sprintf("INSERT INTO `{$GLOBALS['tablepost2cat']}`
	                                         (post_id, category_id)
	                                         VALUES ('%s', '1')",
	                                     mysql_real_escape_string($id));
	                                     
	                        safe_query($q); print("{$q}\n");
                    	}
                    }
                        
                    unset($item['description']);
                    unset($item['summary']);
                    print_r($item);
                }
            
            } $effluvia = ob_get_contents();
            ob_end_clean();
        }
    
        return array($added, $updated, $effluvia);
    }
    

   /** mem_reblog
    * This is called from the Admin Side Plugin when the "reblog" event is encountered.
    * This is what makes the plugin work.
    *
    * @param	string	event	This is the event name passed from the callback function.
    *							It should always be 'reblog'.
    *
    * @param	string	step	This is the current step passed from the callback function.
    */
	function mem_reblog($event,$step) 
	{
		// standard security check
		check_privs(1,2,3);
	
		$message = '';
	
		ob_start(); {
			echo '<form action="index.php" method="post" id="reblog">';
		
			// check to see if reblog is installed
		    if(reblog_is_installed()) {
		    
		    	// was a fetch requested?
		        if($step == 'fetch') {
		        	// do the fetch
		            $fetched = reblog_fetch(REBLOG_URL);
		            
		            if ($fetched === false) {
		            	$message = "<span style=\"color: red;\">Please use RSS 2.0 from reBlog.</span>";
		            } else {
		            	$message = (is_array($fetched)?"(Added {$fetched[0]} items, Updated {$fetched[1]} items)" : '');
		            }
		        }

				// show a fetch button		
				echo 
					sInput('fetch'),
					fInput('submit',"submit",'ReBlog Fetch');
		
		    } elseif($step == 'install') {
				// install the plugin
				if (reblog_install()) {
					// success, notify and show fetch button
					$message = "ReBlog Plugin Installed";
					echo 
						sInput('fetch'),
						fInput('submit','submit','ReBlog Fetch');
				} else {
					// failed, notify
					$message = '<span style=\"color: red;\">reBlog Plugin Not Installed.</span>';
					// show install button
			        echo 
			        	sInput('install'),
			        	fInput('submit',"submit",'Install ReBlog');
				}
		    } else {
		    	// not installed, show button
		        echo 
		        	sInput('install'),
		        	fInput('submit',"submit",'Install ReBlog');
		    }
			// embed the event for postback
		    echo 
		    	eInput('reblog'),
		    	'</form>';
		    	
		} $content = ob_get_contents();
		ob_end_clean();
	
		// output the top of the admin section (tabs and such)
		pagetop("ReBlog", $message);
		
		echo 
			'<div style="margin:10px;">',
			$content;
			
		// do we wish to show debug information?
		if (REBLOG_DEBUG && isset($fetched) && is_array($fetched)) {
			echo
				"<hr />",
				"<form action='' method='post' name='debug_form'>",
				"<p>Debug Info:</p>",
				"<textarea name='debug_info' style='float:center;width:600px;height:300px;'>{$fetched[2]}</textarea",
				"</form";
		}
	
		echo '</div>';
		
	}
	
	// Create the "ReBlog" tab in the admin section
	if (function_exists('register_tab'))
	    register_tab('admin','reblog','ReBlog');

	// Register the callback function
	if (function_exists('register_callback'))
	    register_callback('mem_reblog','reblog');

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

if (!headers_sent())
	header('Content-Type: text/plain');

echo chr(60)."?php\n\n"."/*\n * Plugin: ".$plugin['name']." v".$plugin['version']."\n * Author: ".$plugin['author']."\n * Generated: ".date("m.d.Y G:i")."\n */\n\n".'$'."plugin='" . base64_encode(serialize($plugin)) . "'\n?".chr(62);

}

?>
