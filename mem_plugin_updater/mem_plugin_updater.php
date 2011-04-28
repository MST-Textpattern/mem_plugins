<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_plugin_updater';

// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.1';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Plugin Update RPC Server';

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
h1(title). Plugin Repository Client and Server

h2(section). Summary

p. This plugin is both a Plugin Repository Server and Client. The Plugin Repository Server allows a Textpattern site to maintain a collection of plugins that other Textpattern servers can query against. The Client allows a Textpattern site admin to quickly check for a new version of a plugin, or an entirely new plugin, and then install it.

p. This plugin is MLP compatible.

h2(section). Author Contact

"Michael Manfre":mailto:mmanfre@gmail.com?subject=Textpattern%20Plugin%20mem_plugin_updater
"http://manfre.net":http://manfre.net

h2(section license). License

p. This plugin is licensed under the "GPLv2":http://www.fsf.org/licensing/licenses/info/GPLv2.html.


h2(section). Installation steps

h3. Client

p. To install the client, "click here":./index.php?event=mem_plugin_updater&step=client_install and then modify the repository URI through the Textpattern advanced preferenced.

h3. Server

p. To install the server, "click here":./index.php?event=mem_plugin_updater&step=server_install and then enable the server through the Textpattern advanced preferences.

h2(section). Uninstallation steps

h3. Client

p. To remove all client preferences, "click here":./index.php?event=mem_plugin_updater&step=uninstall_client

h3. Server

p. To remove all server preferences, "click here":./index.php?event=mem_plugin_updater&step=uninstall_server

p. The server installation created a table {prefix}mem_plugin_repo. If you wish to remove this table, you must do so manually.

h2(section). Tags

h3(tag#mem_repo_plugin). mem_repo_plugin

p(tag-summary). This tag will allow the contents of the repository to be shown to a public user. The tag outputs nothing by itself, but is used in conjunction with %(tag-name)mem_repo%.

*(atts) %(atts-name)name% %(atts-type)mixed% Either the plugin ID (locally unique to a site) or the plugin name.
* %(atts-name)form% %(atts-type)string% Name of a form that will be parsed. If set, the text enclosed by this tag will be ignored.
* %(atts-name)wraptag% %(atts-type)string% The name of an HTML tag to wrap around the output of this plugin.
* %(atts-name)class% %(atts-type)string% The CSS class name to apply to %(atts-name)wraptag%.
* %(atts-name)wrapid% %(atts-type)string% The HTML tag id to apply to the %(atts-name)wraptag%.
* %(atts-name)show_help% %(atts-type)int% Set to 1 if you would like to fetch and output the plugin help.
* %(atts-name)show_code% %(atts-type)int% Set to 1 if you would like to fetch and output the plugin code.

h3(tag#mem_repo). mem_repo

p(tag-summary). This tag must be used in the form referenced by %(tag-name)mem_repo_plugin%. It is responsible for outputting the results.

*(atts) %(atts-name)name% %(atts-type)string% The name of the plugin field to output. Valid values are name, description, version, type, author, author_uri, plugin, plugin_md5, help.
* %(atts-name)wraptag% %(atts-type)string% The name of an HTML tag to wrap around the output of this plugin.
* %(atts-name)class% %(atts-type)string% The CSS class name to apply to %(atts-name)wraptag%.
* %(atts-name)wrapid% %(atts-type)string% The HTML tag id to apply to the %(atts-name)wraptag%.


h2(section). Admin page help

p. Pending...

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

define('MEM_PLUGIN_XMLRPC_PATH', 'memxmlrpc');
define('MEM_PLUGIN_XMLRPC_HOST', 'manfre.net');
define('MEM_PLUGIN_XMLRPC_PORT', 80);


global $mem_plugin_lang, $prefs, $event;


if (!is_array($mem_plugin_lang))
{
	$mem_plugin_lang = array(
		'bad_plugin_code'		=> 'Plugin code has an invalid format.',
		'check_version'			=> 'Check Version',
		'db_create_fail'		=> 'Failed to create DB table {table}.',
		'db_create_success'		=> 'Created DB table {table}.',
		'db_table_exists'		=> 'Table {table} already exists.',
		'install_client'		=> 'mem_plugin_updater Client Install',
		'install_server'		=> 'mem_plugin_updater Server Install',
		'install_update'		=> 'Install update',
		'new_version'			=> 'A newer version of plugin {name} v{old_version} was detected on the repository server. New Version is v{new_version}.',
		'no_plugin_specified'	=> 'No plugin specified.',
		'no_plugin_help'		=> 'The plugin "{name}" does not contain any help documentation.',
		'plugin_conflict'		=> 'The plugin {name} already exists.',
		'plugin_details'		=> 'Plugin Details',
		'plugin_installed'		=> 'Plugin {name} v{version} has been installed.',
		'plugin_install_failed'	=> 'Plugin {name} v{version} failed to install.',
		'plugin_name_label'		=> 'Plugin Name:',
		'plugin_repo'			=> 'Plugin Repository',
		'plugin_updater'		=> 'Plugin Updater',
		'plugin_upload_success'	=> 'Plugin {name} was uploaded.',
		'plugin_upload_fail'	=> 'Plugin {name} failed to uploaded.',
		'pref_exists'			=> 'The preference "{pref}" already exists with value "{val}"',
		'replace_plugin'		=> 'The plugin {name} v{old_version} already exists. Continuing will replace the plugin with the version {new_version}.',
		'rpc_error'				=> 'RPC error ({code}) {msg}',
		'set_pref_fail'			=> 'Failed to set preference "{pref}".',
		'set_pref_success'		=> 'Set preference "{pref}" to "{val}".',
		'up_to_date'			=> 'The plugin {name} v{version} is up to date.',
		'upload_plugin'			=> 'Upload Plugin:',
		'upload_new_plugin'		=> 'Submit this form if you would like to add the plugin {name} (version {version}) to the repository.',
		'upload_plugin_verify'	=> 'Verify Plugin Upload',
	);
}


register_callback( 'mem_plugin_updater_enumerate_strings' , 'l10n.enumerate_strings' );
function mem_plugin_updater_enumerate_strings($event, $step='', $pre=0) {
	global $mem_plugin_lang;
	$r = array	(
				'owner'		=> 'mem_plugin_updater',			#	Change to your plugin's name
				'prefix'	=> 'mem_plugin_updater',		#	Its unique string prefix
				'lang'		=> 'en-gb',				#	The language of the initial strings.
				'event'		=> 'common',			#	public/admin/common = which interface the strings will be loaded into
				'strings'	=> $mem_plugin_lang,		#	The strings themselves.
				);
	return $r;
}
function mem_plugin_gTxt($what,$args = array())
{
	global $mem_plugin_lang, $textarray;

	$key = strtolower( 'mem_plugin_updater' . '-' . $what );
	
	if (isset($textarray[$key]))
		$str = $textarray[$key];
	else
	{
		$key = strtolower($what);
		
		if (isset($mem_plugin_lang[$key]))
			$str = $mem_plugin_lang[$key];
		else
			$str = $what;
	}

	if( !empty($args) )
		$str = strtr( $str , $args );

	return $str;
}


if (txpinterface == 'public') {
	if (isset($prefs['mem_plugin_repo_enabled']) and $prefs['mem_plugin_repo_enabled'] == '1')
		register_callback('mem_plugin_updater', 'pretext');
}
else {
	register_tab('admin', 'mem_plugin_updater', mem_plugin_gTxt('plugin_updater'));
	register_callback('mem_plugin_updater', 'mem_plugin_updater');

	if (isset($prefs['mem_plugin_repo_enabled']) and $prefs['mem_plugin_repo_enabled'] == '1')
	{
		register_tab('admin', 'mem_plugin_repo', mem_plugin_gTxt('plugin_repo'));
		register_callback('mem_plugin_updater', 'mem_plugin_repo');
	}

	if ($event == 'mem_plugin_updater') {
		include_once txpath.'/include/txp_plugin.php';
	}
	
	if ($event == 'mem_plugin_updater' || $event == 'mem_plugin_repo') {
		include_once txpath.'/publish/taghandlers.php';
		require_once txpath.'/lib/IXRClass.php';
	}
}

if (txpinterface == 'public')
{
	require_once txpath.'/lib/IXRClass.php';
	
	class mem_plugin_Server extends IXR_Server {
	    function mem_plugin_Server() {
	        $this->IXR_Server(array(
	            'mem_plugin.check_version'		=> 'this:check_version',
	            'mem_plugin.get_plugin' 		=> 'this:get_plugin',
	            'mem_plugin.get_plugin_details'	=> 'this:get_plugin_details',
	        ));
	    }
	    function check_version($args) 
	    {    	
	    	if (!is_string($args))
	    		return new IXR_Error(-34000, 'Invalid argument in function check_version');
	    	
	    	$plugin_name = doSlash($args);
	    	
	    	$version = safe_field('version', 'mem_plugin_repo', "name = '$plugin_name'");
	    	
	    	if ($version)
		        return array('plugin_name'=>$args, 'plugin_version'=> $version);
		    
		    return new IXR_Error(-34001, 'Plugin not found');
	    }
	    
	    // Returns the encoded plugin
	    function get_plugin($args) 
	    {
	    	if (!is_string($args))
	    		return new IXR_Error(-34000, 'Invalid argument in function get_plugin');
	
	    	$rs = safe_row('plugin, version', 'mem_plugin_repo', "name = '".doSlash($args)."'");
	    	
	    	if (!$rs)
	    		return new IXR_Error(-34001, 'Plugin not found');
	
	    	
	        return array(
	        	'plugin_name'	=> $args,
	        	'plugin_version'=> $rs['version'],
	        	'plugin_file'	=> $rs['plugin']
	        );
	    }
	    
	    function get_plugin_details($args)
	    {
	    	if (!is_string($args))
	    		return new IXR_Error(-34000, 'Invalid argument in function get_plugin');
	
	    	$rs = safe_row('type, version, description, author, author_uri, plugin_md5', 'mem_plugin_repo', "name = '".doSlash($args)."'");
	    	
	    	if (!$rs)
	    		return new IXR_Error(-34001, 'Plugin not found');
	
	        return array(
	        	'plugin_name'	=> $args,
	        	'plugin_type'	=> $rs['type'],
	        	'plugin_version'=> $rs['version'],
	        	'plugin_author'=> $rs['author'],
	        	'plugin_author_uri'=> $rs['author_uri'],
	        	'plugin_md5'=> $rs['plugin_md5'],
	        	'plugin_description'=> $rs['description'],
	        );
	    }
	}
}

function mem_plugin_updater_client_install()
{
	global $prefs;
	
	$log = array();
	
	$pref_name = 'mem_plugin_updater_url';
	
	if (!isset($prefs[$pref_name]))
	{
		$val = 'http://manfre.net/'.MEM_PLUGIN_XMLRPC_PATH.'/';

		$r = set_pref($pref_name, $val, 'mem_plugin_updater', 1);
		
		$prefs[$pref_name] = $val;

		if ($r !== false)
			$log[] = mem_plugin_gTxt('set_pref_success', array('{pref}' => $pref_name, '{val}'=> $val));
		else
			$log[] = mem_plugin_gTxt('set_pref_fail', array('{pref}' => $pref_name));
	}
	else
		$log[] = mem_plugin_gTxt('pref_exists', array('{pref}' => $pref_name, '{val}'=> $prefs[$pref_name]));
	
	return $log;
}

function mem_plugin_updater_server_install()
{
	global $prefs;
	
	$log = array();
	
	$pref_name = 'mem_plugin_repo_enabled';
	
	if (!isset($prefs[$pref_name]))
	{
		$val = '0';

		$r = set_pref($pref_name, $val, 'mem_plugin_updater', 1, 'yesnoradio');
		
		if ($r !== false)
			$log[] = mem_plugin_gTxt('set_pref_success', array('{pref}' => $pref_name, '{val}'=> $val));
		else
			$log[] = mem_plugin_gTxt('set_pref_fail', array('{pref}' => $pref_name));
		
		$prefs[$pref_name] = $val == '1';
	}
	else
		$log[] = mem_plugin_gTxt('pref_exists', array('{pref}' => $pref_name, '{val}'=> $prefs[$pref_name]));

	$table_prefix = PFX;
	
	ob_start();
	$rs = safe_row("id", "mem_plugin_repo", "1=1 LIMIT 1");
	ob_end_flush();
	
	$table_name = PFX.'mem_plugin_repo';
	
	if (!$rs && mysql_errno() != 0)
	{
		$table_sql = <<<EOF
CREATE TABLE `{$table_name}` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 64 ) NOT NULL ,
`description` VARCHAR( 255 ) NOT NULL,
`version` VARCHAR( 10 ) NOT NULL ,
`type` INT( 2 ) NOT NULL ,
`author` VARCHAR( 128 ) NOT NULL ,
`author_uri` VARCHAR( 128 ) NOT NULL ,
`plugin` TEXT NOT NULL ,
`plugin_md5` VARCHAR( 32 ) NOT NULL ,
`help` TEXT NOT NULL ,
INDEX ( `name` )
) ENGINE = MYISAM ;
EOF;
	
		$rs = safe_query($table_sql);
		
		if ($rs)
			$log[] = mem_plugin_gTxt('db_create_success', array('{table}'=> $table_name));
		else
			// failed to create table
			$log[] = mem_plugin_gTxt('db_create_fail', array('{table}'=> $table_name));
	}
	else
		$log[] = mem_plugin_gTxt('db_table_exists', array('{table}' => $table_name));
		

	return $log;
}

// -------------------------------------------------------------
function mem_plugin_form($event='mem_plugin_repo') 
{
	return n.n.form(
		graf(
		tag(mem_plugin_gTxt('upload_plugin'), 'span', ' style="vertical-align: 10px;"').sp.

		'<textarea name="plugin" cols="1" rows="1" style="width:40px; height: 20px;"></textarea>'.sp.

		tag(
			fInput('submit', 'upload_new', gTxt('upload'), 'smallerbox')
	   , 'span', ' style="vertical-align: 10px;"').

			eInput($event).
			sInput('plugin_verify')
		)
	, 'text-align: center;');
}
function mem_plugin64_to_array($plugin64)
{
	$plugin = base64_decode($plugin64);

	if (strncmp($plugin, "\x1F\x8B", 2) === 0)
	{
		if (function_exists('gzinflate'))
			$plugin = gzinflate(substr($plugin, 10));
		else
			return false;
	}

	$plugin = @unserialize($plugin);
	
	if (is_array($plugin))
		return $plugin;
	return false;
}

function mem_plugin_verify()
{
	// make sure the plugin is valid and prompt for conflicts/updates
	$plugin = ps('plugin');
	$plugin = preg_replace('@.*\$plugin=\'([\w=+/]+)\'.*@s', '$1', $plugin);
	$plugin = preg_replace('/^#.*$/m', '', $plugin);

	if(isset($plugin)) {
		$plugin_encoded = $plugin;

		$plugin = mem_plugin64_to_array($plugin_encoded);
		
		if ($plugin)
		{ 
			if(is_array($plugin)){
				extract($plugin);
				
				$old_version = safe_field('version', 'mem_plugin_repo', "name = '{$plugin['name']}'");

				$txt = '';
				
				if ($old_version)
					$txt = 	tag(mem_plugin_gTxt('replace_plugin', array('{name}'=> $name, '{old_version}'=> $old_version, '{new_version}'=> $version)), 'div');
				else
					$txt = tag(mem_plugin_gTxt('upload_new_plugin', array('{name}'=> $name, '{version}'=> $version)), 'div');

				$out = 
					form(
						hed(mem_plugin_gTxt('upload_plugin_verify'),3).
						$txt.
						fInput('submit','', mem_plugin_gTxt('submit'),'publish').
						hInput('replace_version', $old_version).
						eInput('mem_plugin_repo').
						sInput('plugin_upload').
						hInput('plugin64', $plugin_encoded)
					, 'margin: 0 autio; width: 65%;');

				return $out;							
			}
		}
	}
}

function mem_plugin_upload()
{
	extract(gpsa(array('plugin64','replace_version')));

	$plugin = mem_plugin64_to_array($plugin64);
	
	if ($plugin)
	{
		extract($plugin);
		
		if ($replace_version)
		{
			$rs = safe_update("mem_plugin_repo",
					"name			= '".doSlash($name)."',
					 description	= '".doSlash($description)."',
					 version		= '".doSlash($version)."',
					 type			= ".doSlash($type).",
					 author			= '".doSlash($author)."',
					 author_uri   	= '".doSlash($author_uri)."',
					 plugin			= '".doSlash($plugin64)."',
					 plugin_md5		= '".doSlash($md5)."',
					 help         	= '".doSlash($help)."'"
					, "name = '".doSlash($name)."' and version = '".doSlash($replace_version)."'");
		}
		else
		{
			$rs = safe_insert("mem_plugin_repo",
					"name			= '".doSlash($name)."',
					 description	= '".doSlash($description)."',
					 version		= '".doSlash($version)."',
					 type			= ".doSlash($type).",
					 author			= '".doSlash($author)."',
					 author_uri   	= '".doSlash($author_uri)."',
					 plugin			= '".doSlash($plugin64)."',
					 plugin_md5		= '".doSlash($md5)."',
					 help         	= '".doSlash($help)."'"
					 );
		}
		
		if ($rs)
			$message = mem_plugin_gTxt('plugin_upload_success', array('{name}'=> $name));
		else
			$message = mem_plugin_gTxt('plugin_upload_fail', array('{name}'=> $name));
			
		pagetop(mem_plugin_gTxt('plugin_updater'), $message);
		echo mem_repo_admin_list();
	}
	
	// notify we handled output
	return false;
}

function mem_plugin_delete()
{
	$name = doSlash(ps('name'));

	safe_delete('mem_plugin_repo', "name = '$name'");
	
	return;
}

function mem_plugin_help() 
{
	$name = gps('name');
	
	if (empty($name))
		return '';
	
	$help = safe_field('help','mem_plugin_repo',"name = '".doSlash($name)."'");
	
	if (empty($help))
		return '';
		
	return startTable('edit')
			.	tr(tda($help,' width="600"'))
			.	endTable();
}


function mem_plugin_list()
{	
	$out = n.n.startTable('list');
	
	$out .= tr(
				tda(
					form(
						tag(mem_plugin_gTxt('plugin_name_label'),'label').sp.
						fInput('text', 'plugin_name', '', 'edit', '', '', 22, '', '').sp.
						sInput('get_plugin_details').
						eInput('mem_plugin_updater').
						fInput('submit','',gTxt('details'),'publish')
					, 'margin: 0 auto; width: 65%;')
				,' colspan="8" style="height: 30px; border: none;"')
			);

	$rs = safe_rows_start('name, status, author, author_uri, version, description, code_md5, type, md5(code) as md5, LENGTH(help) as help', 'txp_plugin', "1 order by name");

	if ($rs and numRows($rs) > 0)
	{
		$out .= assHead('plugin', 'author', 'version', 'plugin_modified', 'description', 'active', 'help', '', '');

		while ($a = nextRow($rs))
		{
			foreach ($a as $key => $value) {
				$$key = htmlspecialchars($value);
			}
			// Fix up the description for clean cases
			$description = preg_replace(array('#&lt;br /&gt;#',
											  '#&lt;(/?(a|b|i|em|strong))&gt;#',
											  '#&lt;a href=&quot;(https?|\.|\/|ftp)([A-Za-z0-9:/?.=_]+?)&quot;&gt;#'),
										array('<br />','<$1>','<a href="$1$2">'),
										$description);

			$help = !empty($help) ?
				'<a href="?event=plugin'.a.'step=plugin_help'.a.'name='.$name.'">'.gTxt('view').'</a>' :
				gTxt('none');

			// modified?
			$modified = (strtolower($md5) != strtolower($code_md5));

			$out .= tr(

				n.td($name).

				td(
					href($author, $author_uri)
				).

				td($version, 10).
				td($modified ? gTxt('yes') : '').
				td($description, 260).

				td(
					status_link($status, $name, yes_no($status))
				,30).

				td($help).

				td(
					eLink('plugin', 'plugin_edit', 'name', $name, gTxt('edit'))
				).
				
				td(
					eLink('mem_plugin_updater', 'check_version', 'plugin_name', $name, mem_plugin_gTxt('check_version'))
				).

				td(
					dLink('plugin', 'plugin_delete', 'name', $name)
				,30)
			);

			unset($name, $page, $deletelink);
		}
	}

	$out .= endTable();
	
	return $out;
}

/////////////////////////////////////////
// Repo Public Tags
function mem_repo_plugin($atts, $thing='')
{
	global $mem_repo_repo;

	extract(lAtts(array(
		'form'		=> '',
		'name'		=> '',
		'show_help'	=> 0,
		'show_code'	=> 0,
		'wraptag'	=> '',
		'class'		=> '',
		'wrapid'	=> ''
	),$atts));
	
	if (empty($name)) {
		trigger_error(mem_plugin_gTxt('tag_error', array('{tag}'=> 'mem_repo_plugin', '{atts}'=> 'name')),E_USER_WARNING);
		return;
	}
	
	$sql_fields = 'id, name, description, version, type, author, author_uri, plugin_md5';
	
	if ($show_help) $sql_fields .= ', help';
	if ($show_code) $sql_fields .= ', code';
	
	if (is_numeric($name))
		$sql_where = "`id` = ". doSlash($name);
	else
		$sql_where = "name = '". doSlash($name) ."'";
	
	$mem_repo_plugin = safe_row($sql_fields, 'mem_plugin_repo', $sql_where);
	
	$out = '';
	
	if ($mem_repo_plugin) {
		if (!empty($form))
			$thing = fetch_form($form);
		
		$out = parse($thing);
		
		unset($mem_repo_plugin);
	}
	
	return doTag($out, $wraptag, $class, '', $wrapid);
}

function mem_repo($atts, $thing='')
{
	global $mem_plugin_repo;

	extract(lAtts(array(
		'name'		=> 'name',
		'wraptag'	=> '',
		'class'		=> '',
		'wrapid'	=> ''
	),$atts));
	
	$out = '';
	
	if (is_array($mem_plugin_repo) and isset($mem_plugin_repo[$name]))
		$out = $mem_plugin_repo[$name];
	
	return doTag($out, $wraptag, $class, '', $wrapid);	
}

/////////////////////////////////////////
// Repo functions
function mem_repo_admin_list()
{	
	$out = mem_plugin_form(). n.n.startTable('list');

	$rs = safe_rows_start('id, name, description, version, type, author, author_uri, LENGTH(help) as help', 'mem_plugin_repo', "1 order by name");

	if ($rs and numRows($rs) > 0)
	{
		$out .= assHead('plugin', 'author', 'version', 'type', 'description', 'help', '');

		while ($a = nextRow($rs))
		{
			foreach ($a as $key => $value) {
				$$key = htmlspecialchars($value);
			}
			// Fix up the description for clean cases
			$description = preg_replace(array('#&lt;br /&gt;#',
											  '#&lt;(/?(a|b|i|em|strong))&gt;#',
											  '#&lt;a href=&quot;(https?|\.|\/|ftp)([A-Za-z0-9:/?.=_]+?)&quot;&gt;#'),
										array('<br />','<$1>','<a href="$1$2">'),
										$description);

			$help = !empty($help) ?
				'<a href="?event=mem_plugin_repo'.a.'step=plugin_help'.a.'name='.$name.'">'.gTxt('view').'</a>' :
				gTxt('none');

			$out .= tr(
				n.td($name).
				td(
					href($author, $author_uri)
				).
				td($version, 10).
				td($type).
				td($description, 260).
				td($help).
				td(
					dLink('mem_plugin_repo', 'plugin_delete', 'name', $name)
				,30)
			);

			unset($name, $page, $deletelink);
		}
	}

	$out .= endTable();
	
	return $out;
}


function mem_plugin_install($plugin, $status=1)
{
	$plugin = preg_replace('@.*\$plugin=\'([\w=+/]+)\'.*@s', '$1', $plugin);
	$plugin = preg_replace('/^#.*$/m', '', $plugin);

	if(trim($plugin)) {
		$plugin = mem_plugin64_to_array($plugin);

		if ($plugin) 
		{
			extract($plugin);
			if (empty($type)) $type = 0;
			$type = assert_int($type);

			$exists = fetch('name','txp_plugin','name',$name);

			if (isset($help_raw) && empty($plugin['allow_html_help'])) {
					// default: help is in Textile format
					include_once txpath.'/lib/classTextile.php';
					$textile = new Textile();
					$help = $textile->TextileThis($help_raw, 0, 0, 1);
			}

			$code = doSlash($code);
			$status = doSlash($status);

			if ($exists) {
				// leave status unchanged on update
				$rs = safe_update(
				   "txp_plugin",
					"status      = $status,
					type         = $type,
					author       = '".doSlash($author)."',
					author_uri   = '".doSlash($author_uri)."',
					version      = '".doSlash($version)."',
					description  = '".doSlash($description)."',
					help         = '".doSlash($help)."',
					code         = '".$code."',
					code_restore = '".$code."',
					code_md5     = '".doSlash($md5)."'",
					"name        = '".doSlash($name)."'"
				);

			} else {

				$rs = safe_insert(
				   "txp_plugin",
				   "name         = '".doSlash($name)."',
					status       = $status,
					type         = $type,
					author       = '".doSlash($author)."',
					author_uri   = '".doSlash($author_uri)."',
					version      = '".doSlash($version)."',
					description  = '".doSlash($description)."',
					help         = '".doSlash($help)."',
					code         = '".$code."',
					code_restore = '".$code."',
					code_md5     = '".doSlash($md5)."'"
				);
			}

			if ($rs and $code)
			{
				return mem_plugin_gTxt('plugin_installed', array('{name}' => escape_output($name), '{version}'=> escape_output($version)));
			}
			else
			{
				return mem_plugin_gTxt('plugin_install_failed', array('{name}' => escape_output($name), '{version}'=> escape_output($version)));
			}
		}
		else
		{
			return mem_plugin_gTxt('bad_plugin_code');
		}
	}
	else
	{
		return mem_plugin_gTxt('no_plugin_specified');
	}
}

function mem_plugin_updater($event, $step='')
{
	global $prefs;
	
	$show_head = 1;
	$head_message = $out = '';
	
	
	
	if ($event == 'pretext')
	{
		while (@ob_end_clean());

		
		$request_uri = preg_replace("|^https?://[^/]+|i","",serverSet('REQUEST_URI'));

		// IIS fix
		if (!$request_uri and serverSet('SCRIPT_NAME'))
			$request_uri = serverSet('SCRIPT_NAME').( (serverSet('QUERY_STRING')) ? '?'.serverSet('QUERY_STRING') : '');
		// another IIS fix
		if (!$request_uri and serverSet('argv'))
		{
			$argv = serverSet('argv');
			$request_uri = @substr($argv[0], strpos($argv[0], ';') + 1);
		}
		
		// define the useable url, minus any subdirectories.
		// this is pretty fugly, if anyone wants to have a go at it - dean
		$subpath = preg_quote(preg_replace("/https?:\/\/.*(\/.*)/Ui","$1",hu),"/");
		$req = preg_replace("/^$subpath/i","/",$request_uri);
		
		extract(chopUrl($req));
		
		if ($u1 == MEM_PLUGIN_XMLRPC_PATH)
		{
			$mem_plugin_server = new  mem_plugin_Server();
			exit();
		}
	}
	else if ($event == 'mem_plugin_updater') 
	{
		if (empty($mem_plugin_updater_url))
			$mem_plugin_updater_url = 'http://'.MEM_PLUGIN_XMLRPC_HOST.':'.MEM_PLUGIN_XMLRPC_PORT.'/'.MEM_PLUGIN_XMLRPC_PATH;

		$client = new IXR_Client($mem_plugin_updater_url);
		//$client->debug = 1;
		
		$plugin_name = gps('plugin_name');

		switch($step) {
			case 'get_plugin_details':
				if (!$client->query('mem_plugin.get_plugin_details', $plugin_name)) {
					$out = mem_plugin_gTxt('rpc_error', array('{code}'=>$client->getErrorCode(), '{msg}'=>$client->getErrorMessage()));
				}
				else {
					$resp = $client->getResponse();

					extract($resp);
				
					$current_version = safe_field('version', 'txp_plugin', "name = '".doSlash($plugin_name)."'");
					
					$is_new = (version_compare($current_version, $plugin_version) < 0);
				
					// Fix up the description for clean cases
					$plugin_description = preg_replace(array('#&lt;br /&gt;#',
												  '#&lt;(/?(a|b|i|em|strong))&gt;#',
												  '#&lt;a href=&quot;(https?|\.|\/|ftp)([A-Za-z0-9:/?.=_]+?)&quot;&gt;#'),
											array('<br />','<$1>','<a href="$1$2">'),
											$plugin_description);
					$out = tag(
								
								n.n.startTable('edit').
								tr(
									tda(
										hed(mem_plugin_gTxt('plugin_details'), 3)
									
									, ' colspan="2"')
								).
								tr(
									td(gTxt('name')).
									td($plugin_name)
								).
								tr(
									td(gTxt('version')).
									td($plugin_version)
								).
								tr(
									td(gTxt('md5')).
									td($plugin_md5)
								).
								tr(
									td(gTxt('author')).
									td(href($plugin_author, $plugin_author_uri))
								).
								tr(
									td(gTxt('description')).
									td($plugin_description, 300)
								).
								tr(
									tda(
										($is_new ?
											form(
												sInput('install_plugin').
												eInput('mem_plugin_updater').
												hInput('plugin_name', $plugin_name).
												fInput('submit','',gTxt('install'),'publish')
											, 'margin: 0 auto; width: 65%;')
											: '')
									, ' colspan="2"')
								).
								n.n.endTable()
							, 'div');
				}
				break;
			case 'check_version':
				if (!$client->query('mem_plugin.check_version', $plugin_name)) {
					$out = mem_plugin_gTxt('rpc_error', array('{code}'=>$client->getErrorCode(), '{msg}'=>$client->getErrorMessage()));
				}
				else {
					$current_version = safe_field('version', 'txp_plugin', "name = '".doSlash($plugin_name)."'");
				
					$resp = $client->getResponse();
					
					if (is_array($resp))
					{
						extract($resp);

						if (version_compare($current_version, $plugin_version) >= 0)
						{
							$out = tag( 
										hed(mem_plugin_gTxt('plugin_updater'), 3).
										tag(mem_plugin_gTxt('up_to_date', array('{name}'=> $plugin_name, '{version}'=> $current_version)), 'div')
									, 'div');
						}
						else
						{
							
							$out = tag( 
										hed(mem_plugin_gTxt('plugin_updater'), 3).
										tag(mem_plugin_gTxt('new_version', array('{name}'=> $plugin_name, '{old_version}'=> $current_version, '{new_version}'=> $plugin_version)), 'div')
									, 'div').
									br.
									form(
										sInput('install_plugin').
										eInput('mem_plugin_updater').
										hInput('plugin_name', $plugin_name).
										fInput('submit','',gTxt('install'),'publish')
									, 'margin: 0 auto; width: 65%;');
						}
					}
				}
				
				break;
			case 'install_plugin':
				if (!$client->query('mem_plugin.get_plugin', $plugin_name))
				{
					$out = mem_plugin_gTxt('rpc_error', array('{code}'=>$client->getErrorCode(), '{msg}'=>$client->getErrorMessage()));
				}
				else 
				{
					$resp = $client->getResponse();
					
					if (is_array($resp))
					{
						extract($resp);
						
						$head_message = mem_plugin_install($resp['plugin_file']);
					}
				}
				
				break;

			case 'install_client':
			case 'client_install':
				$log = mem_plugin_updater_client_install();
				break;
			case 'install_server':
			case 'server_install':
				$log = mem_plugin_updater_server_install();
				break;
			default:
				break;
		}
		

		if ($out !== false)
		{
			if ($show_head)
				pagetop(mem_plugin_gTxt('plugin_updater'), !empty($head_message) ? $head_message : '');
			
			if (isset($log))
				$out = hed(mem_plugin_gTxt($step),1) . doWrap($log, 'ul', '');
			
			if (!empty($out))
				echo $out;
			else
				echo mem_plugin_list();
				
			echo br;
		}
	}
	else if ($event == 'mem_plugin_repo') 
	{
		$plugin_name = gps('plugin_name');
		
		$func = 'mem_'.$step;
		//if (function_exists($func)) $out = call_user_func($func);

		switch($step) {
			case 'plugin_upload':
				$out = mem_plugin_upload();
				break;
			case 'plugin_delete':
				$out = mem_plugin_delete();
				break;
			case 'plugin_verify':
				$out = mem_plugin_verify();
				break;
			case 'plugin_help':
				$out = mem_plugin_help();
				break;

			default:
				// force showing of repo list
				$out = '';
				break;
		}

		if ($out !== false)
		{
			if ($show_head)
				pagetop(mem_plugin_gTxt('plugin_repo'), !empty($head_message) ? $head_message : '');
			
			if (isset($log))
				$out = hed(mem_plugin_gTxt($step),1) . doWrap($log, 'ul', '');
			
			if (!empty($out))
				echo $out;
			else
				echo mem_repo_admin_list();
		}
	}
	else
	{
		echo "shouldn't be here";
	}
}


# --- END PLUGIN CODE ---

?>
