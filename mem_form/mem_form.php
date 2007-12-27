<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
# $plugin['name'] = 'abc_plugin';

// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

// $LastChangedRevision$
// $Revision$
// $Id$
// $LastChangedDate:$
// $LastChangedRevision::  $:

$plugin['version'] = '0.2';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'A library plugin that provides support for html forms.';

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

h1. mem_form plugin

h2. Form Tag

*mem_form*
Attributes: _form, thanks_form, thanks, label, type, redirect, show_error, show_input_
* *form* _string_ Name of a form that will be parsed.
* *thanks_form* _string_ Name of a form that will be parsed upon successful form submission.
* *thanks* _string_ Message to display to user upon successful form submission.
* *type* _string_ Name of the form to identify itself to bound plugin.
* *redirect* _url_ URL to redirect upon successful form submission. Overrides "thanks" and "thanks_form"

h2. Input Tag Attribute Descriptions:
* *break* _string_ Separator between label tag and input tag.
* *checked* _int_ Is this box checked. Default "0".
* *name* _string_ Input field name.
* *required* _int_ Specifies if input is required.
* *default* _string_ The default value.
* *max* _int_ Max character length.
* *min* _int_ Min character length.
* *size* _int_ Size of input field.
* *label* _string_ Friendly name for the input.
* *value* _string_ The input value.
* *class* _string_ CSS class name.
* *delimiter* _string_ List separator. Default ","
* *items* _string_ Delimited list containing a select list display values.
* *values* _string_ Delimited list containing a select list item values.
* *selected* _string_ The value of the selected item.
* *exclude* _string_ List of item values that will not be included.
* *first* _string_ Display value of the first item in the list. E.g. "Select a Section".
* *type* _string_ Category type name. E.g. "article"
* *sort* _string_  How will the list values be sorted.
* *button* _int_ If "1", an html button tag will be used instead of an input tag. 
* *cols* _int_ Number of columns in the textarea.
* *rows* _int_ Number of rows in the textarea.
* *password* _int_ Specifies if the input field is a password field.
* *format* _string_ A regex pattern that will be matched against the input value. You must escape all backslashes '\'. E.g "/\\d/" is a single digit.
* *example* _string_ An example of a correctly formatted input value.

h2. Input Tags:

*mem_form_checkbox*
Attributes: _break, checked, isError, label, name, required_

*mem_form_email*
Attributes: _default, label, max, min, name, required, break, size_

*mem_form_hidden*
Attributes: _name, label, value, required, class_

*mem_form_radio*
Attributes: _break, checked, group, label, name_

*mem_form_secret*
Attributes: _name, label, value_

*mem_form_select*
Attributes: _name, break, delimiter, label, items, values, required, selected_

*mem_form_select_category*
Attributes: _name, break, delimiter, label, items, values, required, selected, exclude, first, sort, type_

*mem_form_select_section*
Attributes: _name, break, delimiter, label, items, values, required, selected, exclude, first, sort_

*mem_form_serverinfo*
Attributes: _label, name_

*mem_form_submit*
Attributes: _button, label, name_

*mem_form_text*
Attributes: _break, default, label, max, min, name, required, size, password, format, example_

*mem_form_textarea*
Attributes: _break, cols, default, label, max, min, name, required, rows_


h3. Helper Functions

_bool_ *mem_form_mail*( string $from, string $reply, string $to, string $subject, string $msg )

This will send an email message.

mixed *mem_form_error*( [mixed $err] )

If $err == NULL, then it will return an array of set errors.
If $err != NULL, then it will set $err as the error.

mixed *mem_form_default*( string $key [, $val] )

If $val is NULL, it will return the default value set for the input matching $key. FALSE if it doesn't exist.
If $val is not NULL, it will set the default value for the input name matching $key.


h2. Plugin Events

This library allows other plugins to hook in to events with the @register_callback@ function.

h3. Globals

* $mem_form_type: A text value that helps a plugin determine if it should process the current form.
* $mem_form_submit: Set to true on the form postback.
* $mem_form_default: An array containing the default values to use when displaying the form.
* $mem_form: An array mapping all input labels to their values.
* $mem_form_labels: An array mapping all input names to their labels.
* $mem_form_values: An array mapping all input names to their values.
* $mem_form_thanks_form: Contains the message that will be shown to the user after a successful submission. Either the "thanks_form" or the "thanks" attribute. A plugin can modify this value or return a string to over

h3. Events

* "*mem_form.defaults*": Allows a plugin to alter the default values for a form prior to being displayed.
* "*mem_form.display*": Allows a plugin to insert additional html in the rendered html form tag.
* "*mem_form.submit*": Allows a plugin to act upon a successful form submission.
* "*mem_form.spam*": Allows a plugin to test a submission as spam. The function get_mem_form_evaluator() returns the evaluator.

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---


$mem_glz_custom_fields_plugin = load_plugin('glz_custom_fields');

// needed for MLP
define( 'MEM_FORM_PREFIX' , 'mem_form' );

global $mem_form_lang;

if (!is_array($mem_form_lang))
{
	$mem_form_lang = array(
		'error_file_extension'	=> 'File upload failed for field {label}.',
		'error_file_failed'	=> 'Failed to upload file for field {label}.',
		'error_file_size'	=> 'Failed to upload File for field {label}. File is to large.',
		'field_missing'	=> 'The field {label} is required.',
		'form_expired'	=>	'The form has expired.',
		'form_misconfigured'	=> 'The mem_form is misconfigured. You must specify the "form" attribute.',
		'form_sorry'	=> 'The form is currently unavailable.',
		'form_used'	=>	'This form has already been used to submit.',
		'general_inquiry'	=> '',
		'invalid_email'	=> 'The email address {email} is invalid.',
		'invalid_host'	=> 'The host {domain} is invalid.',
		'invalid_utf8'	=> 'Invalid UTF8 string for field {label}.',
		'invalid_value'	=> 'The value "{value}" is invalid for the input field {label}.',
		'invalid_format'	=>	'The input field {label} must match the format "{example}".',
		'max_warning'	=> 'The input field {label} must be smaller than {max} characters long.',
		'min_warning'	=> 'The input field {label} must be at least {min} characters long.',
		'refresh'	=> 'Refresh',
		'spam'	=> 'Your submission was blocked by a spam filter.',
		'submitted_thanks'	=>	'You have successfully submitted the form. Thank you.',
	);
}

register_callback( 'mem_form_enumerate_strings' , 'l10n.enumerate_strings' );
function mem_form_enumerate_strings($event , $step='' , $pre=0)
{
	global $mem_form_lang;
	$r = array	(
				'owner'		=> 'mem_form',			#	Change to your plugin's name
				'prefix'	=> MEM_FORM_PREFIX,		#	Its unique string prefix
				'lang'		=> 'en-gb',				#	The language of the initial strings.
				'event'		=> 'public',			#	public/admin/common = which interface the strings will be loaded into
				'strings'	=> $mem_form_lang,		#	The strings themselves.
				);
	return $r;
}


function mem_form_gTxt($what,$args = array())
{
	global $mem_form_lang, $textarray;

	$key = strtolower( MEM_FORM_PREFIX . '-' . $what );
	
	if (isset($textarray[$key]))
	{
		$str = $textarray[$key];
	}
	else
	{
		$key = strtolower($what);
		
		if (isset($mem_form_lang[$key]))
			$str = $mem_form_lang[$key];
		elseif (isset($textarray[$key]))
			$str = $textarray[$key];
		else
			$str = $what;
	}

	if( !empty($args) )
		$str = strtr( $str , $args );

	return $str;
}


function mem_form($atts, $thing='')
{
	global $sitename, $prefs, $file_max_upload_size, $mem_form_error, $mem_form_submit,
		$mem_form, $mem_form_labels, $mem_form_values, 
		$mem_form_default, $mem_form_type, $mem_form_thanks_form,
		$mem_glz_custom_fields_plugin;
	
	extract(mem_form_lAtts(array(
		'form'		=> '',
		'thanks_form'	=> '',
		'thanks'	=> graf(mem_form_gTxt('submitted_thanks')),
		'label'		=> '',
		'type'		=> '',
		'redirect'	=> '',
		'redirect_form'	=> '',
		'file_accept'	=> '',
		'max_file_size'	=> $file_max_upload_size,
		'show_error'	=> 1,
		'show_input'	=> 1,
	), $atts));
	
	if (empty($type) or (empty($form) && empty($thing))) {
		trigger_error('Argument not specified for mem_form tag', E_USER_WARNING);
		
		return '';
	}
	$out = '';


	$mem_form_type = $type;
	
	$mem_form_default = array();
	callback_event('mem_form.defaults');
	
	unset($atts['show_error'], $atts['show_input']);
	$mem_form_id = md5(serialize($atts).preg_replace('/[\t\s\r\n]/','',$thing));
	$mem_form_submit = (ps('mem_form_id') == $mem_form_id);
	
	$nonce   = doSlash(ps('mem_form_nonce'));
	$renonce = false;

	if ($mem_form_submit) {
		safe_delete('txp_discuss_nonce', 'issue_time < date_sub(now(), interval 10 minute)');
		if ($rs = safe_row('used', 'txp_discuss_nonce', "nonce = '$nonce'"))
		{
			if ($rs['used'])
			{
				unset($mem_form_error);
				$mem_form_error[] = mem_form_gTxt('form_used');
				$renonce = true;
				$_POST = array();
				$_POST['mem_form_submit'] = TRUE;
				$_POST['mem_form_id'] = $mem_form_id;
				$_POST['mem_form_nonce'] = $nonce;
			}
		}
		else
		{
			$mem_form_error[] = mem_form_gTxt('form_expired');
			$renonce = true;
		}
	}
	
	if ($mem_form_submit and $nonce and !$renonce)
	{
		$mem_form_nonce = $nonce;
	}

	elseif (!$show_error or $show_input)
	{
		$mem_form_nonce = md5(uniqid(rand(), true));
		safe_insert('txp_discuss_nonce', "issue_time = now(), nonce = '$mem_form_nonce'");
	}

	$form = ($form) ? fetch_form($form) : $thing;
	$form = parse($form);
	
	if (!$mem_form_submit) {
	  # don't show errors or send mail
	}
	elseif (mem_form_error())
	{
		
		if ($show_error or !$show_input)
		{
			$out .= mem_form_display_error();

			if (!$show_input) return $out;
		}
	}
	elseif ($show_input and is_array($mem_form))
	{
		if ($mem_glz_custom_fields_plugin) {
			// prep the values
			glz_custom_fields_before_save();
		}
		
		callback_event('mem_form.spam');

		/// load and check spam plugins/
		$evaluator =& get_mem_form_evaluator();
		$is_spam = $evaluator->is_spam();

		if ($is_spam) {
			return mem_form_gTxt('spam');
		}

		$mem_form_thanks_form = ($thanks_form ? fetch_form($thanks_form) : $thanks);

		safe_update('txp_discuss_nonce', "used = '1', issue_time = now()", "nonce = '$nonce'");
		
		$result = callback_event('mem_form.submit');

		if (mem_form_error()) {
			$out .= mem_form_display_error();
			$redirect = false;
		}

		$thanks_form = $mem_form_thanks_form;
		unset($mem_form_thanks_form);

		if (!empty($result))
			return $result;
		
		if (mem_form_error() and $show_input) 
		{
			// no-op, reshow form with errors
		}
		else if ($redirect)
		{
			$_POST = array();

			while (@ob_end_clean());
			$uri = hu.ltrim($redirect,'/');
			if (empty($_SERVER['FCGI_ROLE']) and empty($_ENV['FCGI_ROLE']))
			{
				txp_status_header('303 See Other');
				header('Location: '.$uri);
				header('Connection: close');
				header('Content-Length: 0');
			}
			else
			{
				$uri = htmlspecialchars($uri);
				$refresh = mem_form_gTxt('refresh');
				
				if (!empty($redirect_form))
				{
					$redirect_form = fetch_form($redirect_form);
					
					echo str_replace('{uri}', $uri, $redirect_form);					
				}
				
				if (empty($redirect_form))
				{
					echo <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>$sitename</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="refresh" content="0;url=$uri" />
</head>
<body>
<a href="$uri">$refresh</a>
</body>
</html>
END;
				}
			}
			exit;
		}
		else {
			return '<div class="memThanks" id="mem'.$mem_form_id.'">' .
				$thanks_form . '</div>';			
		}
	}

	if ($show_input)
	{
		$file_accept = (!empty($file_accept) ? ' accept="'.$file_accept.'"' : '');
		
		return '<form method="post"'.((!$show_error and $mem_form_error) ? '' : ' id="mem'.$mem_form_id.'"').' class="mem_formForm" action="'.htmlspecialchars(serverSet('REQUEST_URI')).'#mem'.$mem_form_id.'"'.$file_accept.'>'.
			( $label ? n.'<fieldset>' : n.'<div>' ).
			( $label ? n.'<legend>'.htmlspecialchars($label).'</legend>' : '' ).
			$out.
			n.'<input type="hidden" name="mem_form_nonce" value="'.$mem_form_nonce.'" />'.
			n.'<input type="hidden" name="mem_form_id" value="'.$mem_form_id.'" />'.
			(!empty($max_file_size) ? n.'<input type="hidden" name="MAX_FILE_SIZE" value="'.$max_file_size.'" />' : '' ).
			callback_event('mem_form.display','',1).
			$form.
			callback_event('mem_form.display').
			( $label ? (n.'</fieldset>') : (n.'</div>') ).
			n.'</form>';
	}

	return '';
}

function mem_form_text($atts)
{
	global $mem_form_error, $mem_form_submit, $mem_form_default;

	extract(mem_form_lAtts(array(
		'break'		=> br,
		'default'	=> '',
		'isError'	=> '',
		'label'		=> mem_form_gTxt('text'),
		'max'		=> 100,
		'min'		=> 0,
		'name'		=> '',
		'required'	=> 1,
		'size'		=> '',
		'password'	=> 0,
		'format'	=> '',
		'example'	=> ''
	), $atts));

	$min = intval($min);
	$max = intval($max);
	$size = intval($size);

	if (empty($name)) $name = mem_form_label2name($label);

	if ($mem_form_submit)
	{
		$value = trim(ps($name));
		$utf8len = preg_match_all("/./su", $value, $utf8ar);
		$hlabel = empty($label) ? htmlspecialchars($name) : htmlspecialchars($label);
		

		if (strlen($value) == 0 && $required)
		{
			$mem_form_error[] = mem_form_gTxt('field_missing', array('{label}'=>$hlabel));
			$isError = "errorElement";
		}
		elseif (!empty($format) && !preg_match($format, $value))
		{
			echo "format=$format<br />value=$value<br />";
			$mem_form_error[] = mem_form_gTxt('invalid_format', array('{label}'=>$hlabel, '{example}'=>$example));
			$isError = "errorElement";
		}
		elseif (strlen($value))
		{
			if (!$utf8len)
			{
				$mem_form_error[] = mem_form_gTxt('invalid_utf8', array('{label}'=>$hlabel));
				$isError = "errorElement";
			}

			elseif ($min and $utf8len < $min)
			{
				$mem_form_error[] = mem_form_gTxt('min_warning', array('{label}'=>$hlabel, '{min}'=>$min));
				$isError = "errorElement";
			}

			elseif ($max and $utf8len > $max)
			{
				$mem_form_error[] = mem_form_gTxt('max_warning', array('{label}'=>$hlabel, '{max}'=>$max));
				$isError = "errorElement";
			}

			else
			{
				mem_form_store($name, $label, $value);
			}
		}
	}

	else
	{
		if (isset($mem_form_default[$name]))
			$value = $mem_form_default[$name];
		else
			$value = $default;
	}

	$size = ($size) ? ' size="'.$size.'"' : '';
	$maxlength = ($max) ? ' maxlength="'.$max.'"' : '';

	$memRequired = $required ? 'memRequired' : '';
	
    return '<label for="'.$name.'" class="memText '.$memRequired.$isError.' '.$name.'">'.htmlspecialchars($label).'</label>'.$break.
		'<input type="'.($password ? 'password' : 'text').'" id="'.$name.'" class="memText '.$memRequired.$isError.'" name="'.$name.'" value="'.htmlspecialchars($value).'"'.$size.$maxlength.' />';
}


function mem_form_file($atts)
{
	global $mem_form_submit, $mem_form_error, $mem_form_default, $file_max_upload_size;
	
	extract(mem_form_lAtts(array(
		'break'		=> ' ',
		'isError'	=> '',
		'label'		=> mem_form_gTxt('file'),
		'name'		=> '',
		'default'	=> '',
		'size'		=> '',
		'accept'	=> '',
		'max_file_size'	=> $file_max_upload_size,
		'required'	=> 1
	), $atts));
	
	if (empty($name)) $name = mem_form_label2name($label);
		
	if ($mem_form_submit)
	{
		$hlabel = empty($label) ? htmlspecialchars($name) : htmlspecialchars($label);

		$fname = $_FILES[$name]['tmp_name'];

		switch ($_FILES[$name]['error']) {
			case UPLOAD_ERR_OK:
				if (is_uploaded_file($fname) and $max_file_size >= filesize($fname))
					mem_form_store($name, $label, $_FILES[$name]);
				elseif (!is_uploaded_file($fname)) {
					$mem_form_error[] = mem_form_gTxt('error_file_failed', array('{label}'=>$hlabel));
					$err = 1;
				}
				else {
					$mem_form_error[] = mem_form_gTxt('error_file_size', array('{label}'=>$hlabel));
					$err = 1;
				}
					
				break;

			case UPLOAD_ERR_NO_FILE:
				if ($required) {
					$mem_form_error[] = mem_form_gTxt('field_missing', array('{label}'=>$hlabel));
					$err = 1;
				}
				break;

			case UPLOAD_ERR_EXTENSION:
				$mem_form_error[] = mem_form_gTxt('error_file_extension', array('{label}'=>$hlabel));
				$err = 1;
				break;

			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				$mem_form_error[] = mem_form_gTxt('error_file_size', array('{label}'=>$hlabel));
				$err = 1;
				break;
				
			default:
				$mem_form_error[] = mem_form_gTxt('error_file_failed', array('{label}'=>$hlabel));
				$err = 1;
				break;
		}
		
		if ($err)
			$isError = 'errorElement';
	}
	else
	{
		if (isset($mem_form_default[$name]))
			$value = $mem_form_default[$name];
		else
			$value = $default;
	}
	
	$memRequired = $required ? 'memRequired' : '';
	
	$size = ($size) ? ' size="'.$size.'"' : '';
	$accept = (!empty($accept) ? ' accept="'.$accept.'"' : '');
	
    return '<label for="'.$name.'" class="memFile '.$memRequired.$isError.' '.$name.'">'.htmlspecialchars($label).'</label>'.$break.
		'<input type="'.($password ? 'password' : 'text').'" id="'.$name.'" class="memText '.$memRequired.$isError.'" name="'.$name.'" value="'.htmlspecialchars($value).'"'.$size.' />';
}

function mem_form_textarea($atts, $thing='')
{
	global $mem_form_error, $mem_form_submit, $mem_form_default;

	extract(mem_form_lAtts(array(
		'break'		=> br,
		'cols'		=> 58,
		'default'	=> '',
		'isError'	=> '',
		'label'		=> mem_form_gTxt('textarea'),
		'max'		=> 10000,
		'min'		=> 0,
		'name'		=> '',
		'required'	=> 1,
		'rows'		=> 8
	), $atts));

	$min = intval($min);
	$max = intval($max);
	$cols = intval($cols);
	$rows = intval($rows);

	if (empty($name)) $name = mem_form_label2name($label);

	if ($mem_form_submit)
	{
		$value = preg_replace('/^\s*[\r\n]/', '', rtrim(ps($name)));
		$utf8len = preg_match_all("/./su", ltrim($value), $utf8ar);
		$hlabel = htmlspecialchars($label);

		if (strlen(ltrim($value)))
		{
			if (!$utf8len)
			{
				$mem_form_error[] = mem_form_gTxt('invalid_utf8', array('{label}'=>$hlabel));
				$isError = "errorElement";
			}

			elseif ($min and $utf8len < $min)
			{
				$mem_form_error[] = mem_form_gTxt('min_warning', array('{label}'=>$hlabel, '{min}'=>$min));
				$isError = "errorElement";
			}

			elseif ($max and $utf8len > $max)
			{
				$mem_form_error[] = mem_form_gTxt('max_warning', array('{label}'=>$hlabel, '{max}'=>$max));
				$isError = "errorElement";
			}

			else
			{
				mem_form_store($name, $label, $value);
			}
		}

		elseif ($required)
		{
			$mem_form_error[] = mem_form_gTxt('field_missing', array('{label}'=>$hlabel));
			$isError = "errorElement";
		}
	}

	else
	{
		if (isset($mem_form_default[$name]))
			$value = $mem_form_default[$name];
		else if (!empty($default))
			$value = $default;
		else
			$value = parse($thing);
	}

	$memRequired = $required ? 'memRequired' : '';

	return '<label for="'.$name.'" class="memTextarea '.$memRequired.$isError.' '.$name.'">'.htmlspecialchars($label).'</label>'.$break.
		'<textarea id="'.$name.'" class="memTextarea '.$memRequired.$isError.'" name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'">'.htmlspecialchars($value).'</textarea>';
}

function mem_form_email($atts)
{
	global $mem_form_error, $mem_form_submit, $mem_form_from, $mem_form_default;

	extract(mem_form_lAtts(array(
		'default'	=> '',
		'isError'	=> '',
		'label'		=> mem_form_gTxt('email'),
		'max'		=> 100,
		'min'		=> 0,
		'name'		=> '',
		'required'	=> 1,
		'break'		=> br,
		'size'		=> '',
	), $atts));

	if (empty($name)) $name = mem_form_label2name($label);

	$email = $mem_form_submit ? trim(ps($name)) : $default;

	if (isset($mem_form_default[$name]))
		$email = $mem_form_default[$name];

	if ($mem_form_submit and strlen($email))
	{
		if (!is_valid_email($email))
		{
			$mem_form_error[] = mem_form_gTxt('invalid_email', array('{email}'=>htmlspecialchars($email)));
			$isError = "errorElement";
		}

		else
		{
			preg_match("/@(.+)$/", $email, $match);
			$domain = $match[1];

			if (is_callable('checkdnsrr') and checkdnsrr('textpattern.com.','A') and !checkdnsrr($domain.'.','MX') and !checkdnsrr($domain.'.','A'))
			{
				$mem_form_error[] = mem_form_gTxt('invalid_host', array('{domain}'=>htmlspecialchars($domain)));
				$isError = "errorElement";
			}
			else
			{
				$mem_form_from = $email;
			}
		}
	}

	return mem_form_text(array(
		'default'	=> $email,
		'isError'	=> $isError,
		'label'		=> $label,
		'max'		=> $max,
		'min'		=> $min,
		'name'		=> $name,
		'required'	=> $required,
		'break'		=> $break,
		'size'		=> $size
	));
}

function mem_form_select_section($atts)
{
	extract(mem_form_lAtts(array(
		'exclude'	=> '',
		'sort'		=> 'name ASC',
		'first'		=> '',
		'delimiter'	=> ',',
	),$atts,false));
	
	if (!empty($exclude)) {
		$exclusion = array_map('trim', split($delimiter, preg_replace('/[\r\n\t\s]+/', ' ',$exclude)));
		$exclusion = array_map('strtolower', $exclusion);

		if (count($exclusion))
			$exclusion = join($delimiter, quote_list($exclusion));
	}

	$where = empty($exclusion) ? '1=1' : 'LOWER(name) NOT IN ('.$exclusion.')';
	
	$sort = empty($sort) ? '' : ' ORDER BY '. doSlash($sort);
	
	$rs = safe_rows('name, title','txp_section',$where . $sort);
	
	if ($rs) {
		foreach($rs as $r) {
			$items[] = $r['title'];
			$values[] = $r['name'];
		}	
	}
	
	if (!empty($first)) {
		array_unshift($items, $first);
		array_unshift($values, ' ');
	}
	
	unset($atts['exclude']);
	unset($atts['sort']);
	unset($atts['first']);

	$atts['items'] = join(',', $items);
	$atts['values'] = join(',', $values);
	
	return mem_form_select($atts);
}

function mem_form_select_category($atts)
{
	extract(mem_form_lAtts(array(
		'root'	=> 'root',
		'exclude'	=> '',
		'delimiter'	=> ',',
		'type'	=> 'article',
		'first'	=> ''
	),$atts,false));
	
	$rs = getTree($root, $type);

	if (!empty($exclude)) {
		$exclusion = array_map('trim', split($delimiter, preg_replace('/[\r\n\t\s]+/', ' ',$exclude)));
		$exclusion = array_map('strtolower', $exclusion);
	}
	else
		$exclusion = array();

	$items = array();
	$values = array();

	if ($rs) {
		foreach ($rs as $cat) {
			if (count($exclusion) && in_array(strtolower($cat['name']), $exclusion))
				continue;

			$items[] = $cat['title'];
			$values[] = $cat['name'];			
		}
	}
	
	unset($atts['root']);
	unset($atts['type']);
	unset($atts['first']);
	
	if (!empty($first)) {
		array_unshift($items, $first);
		array_unshift($values, ' ');
	}
	
	$atts['items'] = join(',', $items);
	$atts['values'] = join(',', $values);

	return mem_form_select($atts);
}

function mem_form_select($atts)
{
	global $mem_form_error, $mem_form_submit, $mem_form_default;

	extract(mem_form_lAtts(array(
		'name'		=> '',
		'break'		=> ' ',
		'delimiter'	=> ',',
		'isError'	=> '',
		'label'		=> mem_form_gTxt('option'),
		'items'		=> mem_form_gTxt('general_inquiry'),
		'values'	=> '',
		'required'	=> 1,
		'selected'	=> ''
	), $atts, false));

	if (empty($name)) $name = mem_form_label2name($label);
	
	if (!empty($items) && $items[0] == '<') $items = parse($items);
	if (!empty($values) && $values[0] == '<') $values = parse($values);

	$items = array_map('trim', split($delimiter, preg_replace('/[\r\n\t\s]+/', ' ',$items)));
	$values = array_map('trim', split($delimiter, preg_replace('/[\r\n\t\s]+/', ' ',$values)));

	$use_values_array = (count($items) == count($values));

	if ($mem_form_submit)
	{
		$value = trim(ps($name));

		if (strlen($value))
		{
			if ($use_values_array && in_array($value, $values) or !$use_values_array && in_array($value, $items))
			{
				mem_form_store($name, $label, $value);
			}

			else
			{
				$mem_form_error[] = mem_form_gTxt('invalid_value', array('{label}'=> htmlspecialchars($label), '{value}'=> htmlspecialchars($value)));
				$isError = "errorElement";
			}
		}

		elseif ($required)
		{
			$mem_form_error[] = mem_form_gTxt('field_missing', array('{label}'=> htmlspecialchars($label)));
			$isError = "errorElement";
		}
	}
	else
	{
		if (isset($mem_form_default[$name]))
			$value = $mem_form_default[$name];
		else
			$value = $selected;
	}

	$out = '';

	foreach ($items as $item)
	{
		$v = $use_values_array ? array_shift($values) : $item;
		
		$out .= n.t.'<option'.($use_values_array ? ' value="'.$v.'"' : '').($v == $value ? ' selected="selected">' : '>').
				(strlen($item) ? htmlspecialchars($item) : ' ').'</option>';
	}

	$memRequired = $required ? 'memRequired' : '';

	return '<label for="'.$name.'" class="memSelect '.$memRequired.$isError.' '.$name.'">'.htmlspecialchars($label).'</label>'.$break.
		n.'<select id="'.$name.'" name="'.$name.'" class="memSelect '.$memRequired.$isError.'">'.
			$out.
		n.'</select>';
}

function mem_form_checkbox($atts)
{
	global $mem_form_error, $mem_form_submit, $mem_form_default;

	extract(mem_form_lAtts(array(
		'break'		=> ' ',
		'checked'	=> 0,
		'isError'	=> '',
		'label'		=> mem_form_gTxt('checkbox'),
		'name'		=> '',
		'required'	=> 1
	), $atts));

	if (empty($name)) $name = mem_form_label2name($label);

	if ($mem_form_submit)
	{
		$value = (bool) ps($name);

		if ($required and !$value)
		{
			$mem_form_error[] = mem_form_gTxt('field_missing', array('{label}'=> htmlspecialchars($label)));
			$isError = "errorElement";
		}

		else
		{
			mem_form_store($name, $label, $value ? gTxt('yes') : gTxt('no'));
		}
	}

	else {
		if (isset($mem_form_default[$name]))
			$value = $mem_form_default[$name];
		else
			$value = $checked;
	}

	$memRequired = $required ? 'memRequired' : '';

	return '<input type="checkbox" id="'.$name.'" class="memCheckbox '.$memRequired.$isError.'" name="'.$name.'"'.
		($value ? ' checked="checked"' : '').' />'.$break.
		'<label for="'.$name.'" class="memCheckbox '.$memRequired.$isError.' '.$name.'">'.htmlspecialchars($label).'</label>';
}


function mem_form_serverinfo($atts)
{
	global $mem_form_submit;

	extract(mem_form_lAtts(array(
		'label'		=> '',
		'name'		=> ''
	), $atts));

	if (empty($name)) $name = mem_form_label2name($label);

	if (strlen($name) and $mem_form_submit)
	{
		if (!$label) $label = $name;
		mem_form_store($name, $label, serverSet($name));
	}
}

function mem_form_secret($atts, $thing = '')
{
	global $mem_form_submit;

	extract(mem_form_lAtts(array(
		'name'	=> '',
		'label'	=> mem_form_gTxt('secret'),
		'value'	=> ''
	), $atts));


	$name = mem_form_label2name($name ? $name : $label);

	if ($mem_form_submit)
	{
		if ($thing) $value = trim(parse($thing));
		mem_form_store($name, $label, $value);
	}

	return '';
}

function mem_form_hidden($atts, $thing='')
{
	global $mem_form_submit;
	
	extract(mem_form_lAtts(array(
		'name'		=> '',
		'label'		=> mem_form_gTxt('hidden'),
		'value'		=> '',
		'isError'	=> '',
		'required'	=> 1,
		'class'		=> 'memHidden'
	), $atts));
	
	$name = mem_form_label2name($name ? $name : $label);
	
	if ($mem_form_submit)
	{
		$value = preg_replace('/^\s*[\r\n]/', '', rtrim(ps($name)));
		$utf8len = preg_match_all("/./su", ltrim($value), $utf8ar);
		$hlabel = htmlspecialchars($label);

		if (strlen($value))
		{
			if (!$utf8len)
			{
				$mem_form_error[] = mem_form_gTxt('invalid_utf8', $hlabel);
				$isError = "errorElement";
			}
			else
			{
				mem_form_store($name, $label, $value);
			}
		}
	}
	else
	{
		if (isset($mem_form_default[$name]))
			$value = $mem_form_default[$name];
		else if ($thing) 
			$value = trim(parse($thing));
	}
	
	return '<input type="hidden" class="'.$class.' '.$memRequired.$isError.' '.$name 
			. '" name="'.$name.'" value="'.htmlspecialchars($value).'" />';
}

function mem_form_radio($atts)
{
	global $mem_form_error, $mem_form_submit, $mem_form_values, $mem_form_default;

	extract(mem_form_lAtts(array(
		'break'		=> ' ',
		'checked'	=> 0,
		'group'		=> '',
		'label'		=> mem_form_gTxt('option'),
		'name'		=> ''
	), $atts));

	static $cur_name = '';
	static $cur_group = '';

	if (!$name and !$group and !$cur_name and !$cur_group) {
		$cur_group = mem_form_gTxt('radio');
		$cur_name = $cur_group;
	}
	if ($group and !$name and $group != $cur_group) $name = $group;

	if ($name) $cur_name = $name;
	else $name = $cur_name;

	if ($group) $cur_group = $group;
	else $group = $cur_group;

	$id   = 'q'.md5($name.'=>'.$label);
	$name = mem_form_label2name($name);

	if ($mem_form_submit)
	{
		$is_checked = (ps($name) == $id);

		if ($is_checked or $checked and !isset($mem_form_values[$name]))
		{
			mem_form_store($name, $group, $label);
		}
	}

	else
	{
		if (isset($mem_form_default[$name]))
			$is_checked = $mem_form_default[$name];
		else
			$is_checked = $checked;
	}

	return '<input value="'.$id.'" type="radio" id="'.$id.'" class="memRadio '.$name.'" name="'.$name.'"'.
		( $is_checked ? ' checked="checked" />' : ' />').$break.
		'<label for="'.$id.'" class="memRadio '.$name.'">'.htmlspecialchars($label).'</label>';
}

function mem_form_submit($atts, $thing='')
{
	extract(mem_form_lAtts(array(
		'button'	=> 0,
		'label'		=> mem_form_gTxt('save'),
		'name'		=> 'mem_form_submit',
	), $atts));

	$label = htmlspecialchars($label);
	$name = htmlspecialchars($name);

	if ($button or strlen($thing))
	{
		return '<button type="submit" class="memSubmit" name="'.$name.'" value="'.$label.'">'.($thing ? trim(parse($thing)) : $label).'</button>';
	}
	else
	{
		return '<input type="submit" class="memSubmit" name="'.$name.'" value="'.$label.'" />';
	}
}

function mem_form_lAtts($arr, $atts, $warn=true)
{
	foreach(array('button', 'checked', 'required', 'show_input', 'show_error') as $key)
	{
		if (isset($atts[$key]))
		{
			$atts[$key] = ($atts[$key] === 'yes' or intval($atts[$key])) ? 1 : 0;
		}
	}
	if (isset($atts['break']) and $atts['break'] == 'br') $atts['break'] = '<br />';
	return lAtts($arr, $atts, $warn);
}

function mem_form_label2name($label)
{
	$label = trim($label);
	if (strlen($label) == 0) return 'invalid';
	if (strlen($label) <= 32 and preg_match('/^[a-zA-Z][A-Za-z0-9:_-]*$/', $label)) return $label;
	else return 'q'.md5($label);
}

function mem_form_store($name, $label, $value)
{
	global $mem_form, $mem_form_labels, $mem_form_values;
	$mem_form[$label] = $value;
	$mem_form_labels[$name] = $label;
	$mem_form_values[$name] = $value;
}

function mem_form_display_error()
{
	global $mem_form_error;

	$out = n.'<ul class="memError">';

	foreach (array_unique($mem_form_error) as $error)
	{
		$out .= n.t.'<li>'.$error.'</li>';
	}

	$out .= n.'</ul>';
	
	return $out;
}	

function mem_form_error($err=NULL)
{
	global $mem_form_error;
	
	if (!is_array($mem_form_error))
		$mem_form_error = array();
		
	if ($err == NULL)
		return !empty($mem_form_error) ? $mem_form_error : false;

	$mem_form_error[] = $err;
}

function mem_form_default($key,$val=NULL)
{
	global $mem_form_default;
	
	if (is_array($key)) {
		foreach ($key as $k=>$v) {
			mem_form_default($k,$v);
		}
		return;
	}
	
	$name = mem_form_label2name($key);
	
	if ($val == NULL) {
		return (isset($mem_form_default[$name]) ? $mem_form_default[$name] : false);
	}
	
	$mem_form_default[$name] = $val;
	
	return $val;
}



function mem_form_mail($from,$reply,$to,$subject,$msg)
{
	global $prefs;
	
	if (!is_callable('mail'))
		return false;
	
	$to = mem_form_strip($to);
	$from = mem_form_strip($from);
	$reply = mem_form_strip($reply);
	$subject = mem_form_strip($subject);
	$msg = mem_form_strip($msg,FALSE);

	if ($prefs['override_emailcharset'] and is_callable('utf8_decode')) {
		$charset = 'ISO-8859-1';
		$subject = utf8_decode($subject);
		$msg     = utf8_decode($msg);
	}
	else {
		$charset = 'UTF-8';
	}
	
	$subject = mem_form_mailheader($subject,'text');

	$sep = !is_windows() ? "\n" : "\r\n";

	$headers = 'From: '.$from.
		($reply ? ($sep.'Reply-To: '.$reply) : '').
		$sep.'X-Mailer: Textpattern (mem_self_register)'.
		$sep.'X-Originating-IP: '.mem_form_strip((!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'].' via ' : '').$_SERVER['REMOTE_ADDR']).
		$sep.'Content-Transfer-Encoding: 8bit'.
		$sep.'Content-Type: text/plain; charset="'.$charset.'"';
	
	return mail($to, $subject, $msg, $headers);
}

function mem_form_mailheader($string, $type)
{
	global $prefs;

	if (!strstr($string,'=?') and !preg_match('/[\x00-\x1F\x7F-\xFF]/', $string)) {
		if ("phrase" == $type) {
			if (preg_match('/[][()<>@,;:".\x5C]/', $string)) {
				$string = '"'. strtr($string, array("\\" => "\\\\", '"' => '\"')) . '"';
			}
		}
		elseif ("text" != $type) {
			trigger_error('Unknown encode_mailheader type', E_USER_WARNING);
		}
		return $string;
	}
	if ($prefs['override_emailcharset']) {
		$start = '=?ISO-8859-1?B?';
		$pcre  = '/.{1,42}/s';
	}
	else {
		$start = '=?UTF-8?B?';
		$pcre  = '/.{1,45}(?=[\x00-\x7F\xC0-\xFF]|$)/s';
	}
	$end = '?=';
	$sep = is_windows() ? "\r\n" : "\n";
	preg_match_all($pcre, $string, $matches);
	return $start . join($end.$sep.' '.$start, array_map('base64_encode',$matches[0])) . $end;
}

function mem_form_strip($str, $header = TRUE) {
	if ($header) $str = strip_rn($str);
	return preg_replace('/[\x00]/', ' ', $str);
}

///////////////////////////////////////////////
// Spam Evaluator
class mem_form_evaluation
{
	var $status;

	function mem_form_evaluation() {
		$this->status = 0;
	}

	function add_status($rating=-1) {
		$this->status += $rating;
	}

	function get_status() {
		return $this->status;
	}
	
	function is_spam() {
		return ($this->status < 0);
	}
}

function &get_mem_form_evaluator()
{
	static $instance;

	if(!isset($instance)) {
		$instance = new mem_form_evaluation();
	}
	return $instance;
}


# --- END PLUGIN CODE ---

?>
