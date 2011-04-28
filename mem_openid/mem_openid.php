<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_openid';

// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.1';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'OpenID class';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = 2; 

if (!defined('txpinterface'))
	@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---
h1(title). OpenID Library Plugin

p. This plugin functions as a wrapper to the JanRain OpenID PHP library.

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---
define('Auth_OpenID_BUGGY_GMP', false);
define('Auth_OpenID_RAND_SOURCE', NULL);

if (session_id() == "") session_start();

global $mem_openid_lang, $sitepath, $prefs;


// Path to the JanRain OpenID Auth folder
if (!isset($prefs['mem_openid_lib_path'])) {
	$path = txpath.DS.'Auth';
	set_pref('mem_openid_lib_path', doSlash($path), 'openid', 1);
	$prefs['mem_openid_lib_path'] = $mem_openid_lib_path = $path;
	unset($path);
}
// Path to the JanRain OpenID File Store folder
if (!isset($prefs['mem_openid_file_store_path'])) {
	$path = txpath.DS.'tmp'.DS.'openid_store';
	set_pref('mem_openid_file_store_path', doSlash($path), 'openid', 1);
	$prefs['mem_openid_file_store_path'] = $mem_openid_file_store_path = $path;
	unset($path);
}

if (file_exists($prefs['mem_openid_lib_path']))
{
	$path = ini_get('include_path');
	$path = $prefs['mem_openid_lib_path'] . PATH_SEPARATOR . $path;
	ini_set('include_path', $path);
	
	require_once "Auth/OpenID/Consumer.php";
	require_once "Auth/OpenID/FileStore.php";
	require_once "Auth/OpenID/SReg.php";
	require_once "Auth/OpenID/PAPE.php";
}

if (!is_array($mem_openid_lang))
{
	$mem_openid_lang = array(
		'no_consumer'		=> 'No consumer',
		'redirect_failed'	=> 'Failed to redirect. {msg}',
		'invalid_openid'	=> 'OpenID "{id}" is invalid.',
		'auth_cancel'		=> 'Authentication attempt was cancelled.',
		'auth_failure'		=> 'Authentication request failed.',
		'no_response'		=> 'No response',
		'mkdir_failed'		=> 'Failed to create OpenID file store "{path}"',
	);
}

register_callback( 'mem_openid_enumerate_strings' , 'l10n.enumerate_strings' );
function mem_openid_enumerate_strings($event , $step='' , $pre=0)
{
	global $mem_openid_lang;
	$r = array	(
				'owner'		=> 'mem_openid',		#	Change to your plugin's name
				'prefix'	=> 'mem_openid',		#	Its unique string prefix
				'lang'		=> 'en-gb',				#	The language of the initial strings.
				'event'		=> 'public',			#	public/admin/common = which interface the strings will be loaded into
				'strings'	=> $mem_openid_lang,	#	The strings themselves.
				);
	return $r;
}

function mem_openid_gTxt($what,$args = array())
{
	global $mem_openid_lang, $textarray;

	$key = strtolower( 'mem_openid' . '-' . $what );
	
	if (isset($textarray[$key]))
		$str = $textarray[$key];
	else
	{
		$key = strtolower($what);
		
		if (isset($mem_openid_lang[$key]))
			$str = $mem_openid_lang[$key];
		elseif (isset($textarray[$key]))
			$str = $textarray[$key];
		else
			$str = $what;
	}

	if( !empty($args) )
		$str = strtr( $str , $args );

	return $str;
}


class memOpenID
{
	static $store = NULL;
	static $consumer = NULL;
	
	var $error;
	
	var $openid;
	var $trust_root;
	var $return_url;
	
	var $sreg_required	= array();
	var $sreg_optional	= array();
	var $policy_uris	= array();
	var $auth_request	= NULL;
	
	function memOpenID($openid, $trust_root = '', $return_url = '')
	{
		// preload the consumer
		self::get_consumer();

		$this->openid = $openid;
		$this->trust_root = $trust_root;
		$this->return_url = $return_url;
		
		$this->error = false;
	}

	function add_required()
	{
		$args = func_get_args();		
		$this->sreg_required = array_merge($this->sreg_required, array_values($args));
	}
	
	function add_optional()
	{
		$args = func_get_args();
		$this->sreg_optional = array_merge($this->sreg_optional, array_values($args));
	}
	
	function add_policy_uri()
	{
		$args = func_get_args();
		$this->policy_uris = array_merge($this->policy_uris, array_values($args));
	}

	function is_response()
	{
		$nonce = gps('janrain_nonce');

		return !empty($nonce);
	}
	
	function authenticate()
	{
		if (!($c = self::get_consumer()))
		{
			$this->error = mem_openid_gTxt('no_consumer');
			return false;
		}

		$this->auth_request = $c->begin($this->openid);
		
		if ($this->auth_request)
		{
			$this->sreg_request = Auth_OpenID_SRegRequest::build($this->sreg_required, $this->sreg_optional);
			
			if ($this->sreg_request)
				$this->auth_request->addExtension($this->sreg_request);
			
			if (!empty($this->policy_uris))
			{
				if ( ($pape = new Auth_OpenID_PAPE_Request($this->policy_uris)) )
					$this->auth_request->addExtension($pape);
			}
			
			if ($this->auth_request->shouldSendRedirect())
			{
				$redirect_url = $this->auth_request->redirectURL( $this->trust_root, $this->return_url );
				
				if (Auth_OpenID::isFailure($redirect_url))
				{
					$this->error = mem_openid_gTxt('redirect_failed', array('{msg}'=> $redirect_url->message));
				}
				else
				{
					while (@ob_end_clean());
					header("Location: ".$redirect_url);
					
					return true;
				}
			}
			else
			{
		        $form_id = 'openid_message';
		        $form_html = $this->auth_request->formMarkup($this->trust_root, $this->return_url,
		                                               false, array('id' => $form_id));
				
		        // Display an error if the form markup couldn't be generated;
		        // otherwise, render the HTML.
		        if (Auth_OpenID::isFailure($form_html)) {
		        	$this->error = mem_openid_gTxt('redirect_failed', array('{msg}'=> $form_html->message));
		        } else {
		            $page_contents = array(
		               "<html><head><title>",
		               "OpenID transaction in progress",
		               "</title></head>",
		               "<body onload='document.getElementById(\"".$form_id."\").submit()'>",
		               $form_html,
		               "</body></html>");
		
		            print implode("\n", $page_contents);
		            
		            return true;
		        }
			}			
		}
		else
		{
			// invalid openid
			$this->error = mem_openid_gTxt('invalid_openid', array('{id}'=> $this->openid));
		}
		
		return false;
	}
	
	function complete()
	{
		if (!($c = self::get_consumer()))
		{
			$this->error = mem_openid_gTxt('no_consumer');
			return false;
		}

		if ($this->is_response())
		{
			$resp = $c->complete($this->return_url);
			
			switch ($resp->status)
			{
				case Auth_OpenID_CANCEL:
					$this->error = mem_openid_gTxt('auth_cancel');
					break;
				case Auth_OpenID_FAILURE:
					$this->error = mem_openid_gTxt('auth_failure');
					break;
				case Auth_OpenID_SUCCESS:
					$sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($resp);
					
					return $sreg_resp->contents();

					break;
				default:
					break;
			}
		}
		else
		{
			$this->error = mem_openid_gTxt('no_response');
		}

		return false;
	}
	
	static function &get_store()
	{
		global $mem_openid_file_store_path;

		if (self::$store == NULL)
		{
			if (!file_exists($mem_openid_file_store_path) && !mkdir($mem_openid_file_store_path)) 
			{
				trigger_error( mem_openid_gTxt('mkdir_failed', array('{path}'=> $mem_openid_file_store_path)), E_USER_WARNING );
			}
			else 
			{
				self::$store = new Auth_OpenID_FileStore($mem_openid_file_store_path);
			}
		}
		
		return self::$store;
	}
	
	static function &get_consumer()
	{
		$s = self::get_store();

		if ($s != NULL)
		{
			self::$consumer = new Auth_OpenID_Consumer($s);
		}
		
		return self::$consumer;
	}
}

# --- END PLUGIN CODE ---

?>
