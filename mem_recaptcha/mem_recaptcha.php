<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_recaptcha';

// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.1.1';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Provides reCaptcha support to TXP';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = 0;

if (!defined('txpinterface'))
	@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---
h1. reCaptcha ("http://recaptcha.net":http://recaptcha.net)

p. You will need to obtain public and private security keys to use "reCaptcha":http://recaptcha.net/api/getkey 
and to use the mailhide functionality. This plugin is a standalone recaptcha solution that includes the 
recaptchalib.php (without needing to upload anything).

p. This implementation of reCaptcha breaks away from the traditional inline CAPTCHA validation. Instead, you should
first have the user validate the CAPTCHA and use the conditional tag to display the intended input form.

p. Tag *mem_recaptcha*

This tag will output the html needed for the reCaptcha display.

p. Tag *mem_if_recaptcha_valid*

This is a conditional to use with mem_recaptcha. After the mem_recaptcha prompt is validated, this will return true for the
user. This supports <txp:else />.

p. Tag *mem_mailhide*

Attributes:
# _email_ -- The email address that will be protected with mailhide.
# _output_ -- This value should be (default) 'html' or 'url' and determines what this tag will generate.


# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

/*
 * This is a PHP library that handles calling reCAPTCHA.
 *    - Documentation and latest version
 *          http://recaptcha.net/plugins/php/
 *    - Get a reCAPTCHA API Key
 *          http://recaptcha.net/api/getkey
 *    - Discussion group
 *          http://groups.google.com/group/recaptcha
 *
 * Copyright (c) 2007 reCAPTCHA -- http://recaptcha.net
 * AUTHORS:
 *   Mike Crawford
 *   Ben Maurer
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
global $recaptcha_api_server, $recaptcha_api_secure_server, $recaptcha_verify_server;
/**
 * The reCAPTCHA server URL's
 */
$recaptcha_api_server = 'http://api.recaptcha.net';
$recaptcha_api_secure_server = 'https://api-secure.recaptcha.net';
$recaptcha_verify_server = 'api-verify.recaptcha.net';


/**
 * Encodes the given data into a query string format
 * @param $data - array of string elements to be encoded
 * @return string - encoded request
 */
function _recaptcha_qsencode ($data) {
        $req = "";
        foreach ( $data as $key => $value )
                $req .= $key . '=' . urlencode( stripslashes($value) ) . '&';

        // Cut the last '&'
        $req=substr($req,0,strlen($req)-1);
        return $req;
}



/**
 * Submits an HTTP POST to a reCAPTCHA server
 * @param string $host
 * @param string $path
 * @param array $data
 * @param int port
 * @return array response
 */
function _recaptcha_http_post($host, $path, $data, $port = 80) {

        $req = _recaptcha_qsencode ($data);

        $http_request  = "POST $path HTTP/1.0\r\n";
        $http_request .= "Host: $host\r\n";
        $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
        $http_request .= "Content-Length: " . strlen($req) . "\r\n";
        $http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
        $http_request .= "\r\n";
        $http_request .= $req;

        $response = '';
        if( false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
                die ('Could not open socket');
        }

        fwrite($fs, $http_request);

        while ( !feof($fs) )
                $response .= fgets($fs, 1160); // One TCP-IP packet
        fclose($fs);
        $response = explode("\r\n\r\n", $response, 2);

        return $response;
}



/**
 * Gets the challenge HTML (javascript and non-javascript version).
 * This is called from the browser, and the resulting reCAPTCHA HTML widget
 * is embedded within the HTML form it was called from.
 * @param string $pubkey A public key for reCAPTCHA
 * @param string $error The error given by reCAPTCHA (optional, default is null)
 * @param boolean $use_ssl Should the request be made over ssl? (optional, default is false)

 * @return string - The HTML to be embedded in the user's form.
 */
function recaptcha_get_html ($pubkey, $error = null, $use_ssl = false)
{
        global $recaptcha_api_server, $recaptcha_api_secure_server;

	if ($pubkey == null || $pubkey == '') {
		die ("To use reCAPTCHA you must get an API key from <a href='http://recaptcha.net/api/getkey'>http://recaptcha.net/api/getkey</a>");
	}
	
	if ($use_ssl) {
           $server = $recaptcha_api_secure_server;
        } else {
           $server = $recaptcha_api_server;
        }
        $errorpart = "";
        if ($error) {
           $errorpart = "&amp;error=" . $error;
        }
        return '<script type="text/javascript" src="'. $server . '/challenge?k=' . $pubkey . $errorpart . '"></script>

	<noscript>
  		<iframe src="'. $server . '/noscript?k=' . $pubkey . $errorpart . '" height="300" width="500" frameborder="0"></iframe><br>
  		<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
  		<input type="hidden" name="recaptcha_response_field" value="manual_challenge">
	</noscript>';
}




/**
 * A ReCaptchaResponse is returned from recaptcha_check_answer()
 */
class ReCaptchaResponse {
        var $is_valid;
        var $error;
}


/**
  * Calls an HTTP POST function to verify if the user's guess was correct
  * @param string $privkey
  * @param string $remoteip
  * @param string $challenge
  * @param string $response
  * @return ReCaptchaResponse
  */
function recaptcha_check_answer ($privkey, $remoteip, $challenge, $response)
{
	if ($privkey == null || $privkey == '') {
		die ("To use reCAPTCHA you must get an API key from <a href='http://recaptcha.net/api/getkey'>http://recaptcha.net/api/getkey</a>");
	}

	if ($remoteip == null || $remoteip == '') {
		die ("For security reasons, you must pass the remote ip to reCAPTCHA");
	}

	
	
        //discard spam submissions
        if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0) {
                $recaptcha_response = new ReCaptchaResponse();
                $recaptcha_response->is_valid = false;
                $recaptcha_response->error = 'incorrect-captcha-sol';
                return $recaptcha_response;
        }

        global $recaptcha_verify_server;
        $response = _recaptcha_http_post ($recaptcha_verify_server, "/verify",
                                          array (
                                                 'privatekey' => $privkey,
                                                 'remoteip' => $remoteip,
                                                 'challenge' => $challenge,
                                                 'response' => $response
                                                 )
                                          );

        $answers = explode ("\n", $response [1]);
        $recaptcha_response = new ReCaptchaResponse();

        if (trim ($answers [0]) == 'true') {
                $recaptcha_response->is_valid = true;
        }
        else {
                $recaptcha_response->is_valid = false;
                $recaptcha_response->error = $answers [1];
        }
        return $recaptcha_response;

}

/**
 * gets a URL where the user can sign up for reCAPTCHA. If your application
 * has a configuration page where you enter a key, you should provide a link
 * using this function.
 * @param string $domain The domain where the page is hosted
 * @param string $appname The name of your application
 */
function recaptcha_get_signup_url ($domain = null, $appname = null) {
	return "http://recaptcha.net/api/getkey?" .  _recaptcha_qsencode (array ('domain' => $domain, 'app' => $appname));
}



/* Mailhide related code */

function _recaptcha_aes_encrypt($val,$ky) {
	if (! function_exists ("mcrypt_encrypt")) {
		die ("To use reCAPTCHA Mailhide, you need to have the mcrypt php module installed.");
	}
	$mode=MCRYPT_MODE_CBC;   
	$enc=MCRYPT_RIJNDAEL_128;
	$val=str_pad($val, (16*(floor(strlen($val) / 16)+(strlen($val) % 16==0?2:1))), chr(16-(strlen($val) % 16)));
	return mcrypt_encrypt($enc, $ky, $val, $mode, "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0");
}


function _recaptcha_mailhide_urlbase64 ($x) {
	return strtr(base64_encode ($x), '+/', '-_');
}

/* gets the reCAPTCHA Mailhide url for a given email, public key and private key */
function recaptcha_mailhide_url($pubkey, $privkey, $email) {
	if ($pubkey == '' || $pubkey == null || $privkey == "" || $privkey == null) {
		die ("To use reCAPTCHA Mailhide, you have to sign up for a public and private key, " .
		     "you can do so at <a href='http://mailhide.recaptcha.net/apikey'>http://mailhide.recaptcha.net/apikey</a>");
	}
	

	$ky = pack('H*', $privkey);
	$cryptmail = _recaptcha_aes_encrypt ($email, $ky);
	
	return "http://mailhide.recaptcha.net/d?k=" . $pubkey . "&c=" . _recaptcha_mailhide_urlbase64 ($cryptmail);
}

/**
 * gets the parts of the email to expose to the user.
 * eg, given johndoe@example,com return ["john", "example.com"].
 * the email is then displayed as john...@example.com
 */
function _recaptcha_mailhide_email_parts ($email) {
	$arr = preg_split("/@/", $email );

	if (strlen ($arr[0]) <= 4) {
		$arr[0] = substr ($arr[0], 0, 1);
	} else if (strlen ($arr[0]) <= 6) {
		$arr[0] = substr ($arr[0], 0, 3);
	} else {
		$arr[0] = substr ($arr[0], 0, 4);
	}
	return $arr;
}

/**
 * Gets html to display an email address given a public an private key.
 * to get a key, go to:
 *
 * http://mailhide.recaptcha.net/apikey
 */
function recaptcha_mailhide_html($pubkey, $privkey, $email) {
	$emailparts = _recaptcha_mailhide_email_parts ($email);
	$url = recaptcha_mailhide_url ($pubkey, $privkey, $email);
	
	return htmlentities($emailparts[0]) . "<a href='" . htmlentities ($url) .
		"' onclick=\"window.open('" . htmlentities ($url) . "', '', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=500,height=300'); return false;\" title=\"Reveal this e-mail address\">...</a>@" . htmlentities ($emailparts [1]);
}

function mem_mailhide($atts) {
	global $prefs;
	
	extract(lAtts(array(
		'output'		=> 'html', // 'html' or 'url'
		'email'			=> ''
	),$atts));

	$pubkey = $prefs['mem_mailhide_publickey'];
	$privkey = $prefs['mem_mailhide_privatekey'];
	
	$out = '';
	
	if (!empty($email)) {
		if ($output == 'html')
			$out = recaptcha_mailhide_html($pubkey, $privkey, $email);
		else if ($output == 'url')
			$out = recaptcha_mailhide_url($pubkey, $privkey, $email);
	}
	return $out;
}

function mem_recaptcha($atts='') {
	global $prefs, $mem_recaptcha_error;
	
#	extract(lAtts(array(
#	),$atts));

	$pubkey = $prefs['mem_recaptcha_publickey'];
	$privkey = $prefs['mem_recaptcha_privatekey'];

	return recaptcha_get_html($pubkey, $error);		
}

global $mem_recaptcha_error;
if (!isset($mem_recaptcha_error)) $mem_recaptcha_error = null;

function mem_if_recaptcha_valid($atts,$body='') {
	global $prefs, $mem_recaptcha_error;
	
#	extract(lAtts(array(
#	),$atts));

	$pubkey = $prefs['mem_recaptcha_publickey'];
	$privkey = $prefs['mem_recaptcha_privatekey'];

	$resp = recaptcha_check_answer(	$privkey, 
																	$_SERVER['REMOTE_ADDR'], 
																	gps('recaptcha_challenge_field'), 
																	gps('recaptcha_response_field'));

	if (!$resp->is_valid)
		$mem_recaptcha_error = $resp->error;

	return parse(EvalElse($body, $resp->is_valid));	
}

if (!function_exists('mem_set_pref')) {
	function mem_set_pref($name, $val, $event,  $type, $position=0, $html='text_input') 
	{
		global $pref_cache;
		
		if ($pref_cache==null)
			$pref_cache = array();

		$args = func_get_args();
		$args['html'] = $html;
		$args['position'] = $position;
		$args['prefs_id'] = 1;

		$pref_cache[$name] = $args;

		extract(doSlash($args));
		
    	if (!safe_row("*", 'txp_prefs', "name = '$name'") ) {
        	return safe_insert('txp_prefs', "
				name  = '$name',
				val   = '$val',
				event = '$event',
				html  = '$html',
				type  = '$type',
				position = '$position',
				prefs_id = 1"
			);
    	} else {
        	return safe_update(	'txp_prefs', "
	        						val   = '$val', 
	        						event = '$event', 
	        						html  = '$html',
	        						type  = '$type',
	        						position = '$position'",
        						"name like '$name'");
    	}
    	return false;
	}
}

function mem_recaptcha_init() {
	global $prefs;
	
	if (!isset($prefs['mem_recaptcha_publickey'])) {
		mem_set_pref('mem_recaptcha_publickey','','mem_recaptcha', 1);
	}
	if (!isset($prefs['mem_recaptcha_privatekey'])) {
		mem_set_pref('mem_recaptcha_privatekey','','mem_recaptcha', 1);
	}

	if (!isset($prefs['mem_mailhide_publickey'])) {
		mem_set_pref('mem_mailhide_publickey','','mem_recaptcha', 1);
	}
	if (!isset($prefs['mem_mailhide_privatekey'])) {
		mem_set_pref('mem_mailhide_privatekey','','mem_recaptcha', 1);
	}
}
$ran_mem_recaptcha_init = false;

if (!$ran_mem_recaptcha_init) {
	$ran_mem_recaptcha_init = true;
	mem_recaptcha_init();
}

register_callback('mem_captcha_ask','comment.form');
register_callback('mem_captcha_check','comment.save');

function mem_recaptcha_ask($atts='')
{
		$nonce = getNextNonce(true);
		$secret = getNextSecret(true);
		if (!$nonce || !$secret)
			return;
		return mem_recaptcha();
}

function mem_recaptcha_check($atts='')
{
	global $prefs, $mem_recaptcha_error;
	
	$pubkey = $prefs['mem_recaptcha_publickey'];
	$privkey = $prefs['mem_recaptcha_privatekey'];

	$resp = recaptcha_check_answer(	$privkey, 
																	$_SERVER['REMOTE_ADDR'], 
																	gps('recaptcha_challenge_field'), 
																	gps('recaptcha_response_field'));

	if (!$resp->is_valid)
		$mem_recaptcha_error = $resp->error;

	$evaluator =& get_comment_evaluator();
	if ($resp->is_valid)
		$evaluator->add_estimate(VISIBLE, 1.0);
	else {
		$evaluator->add_estimate(RELOAD, 0.75);
	}
}
	
# --- END PLUGIN CODE ---

?>
