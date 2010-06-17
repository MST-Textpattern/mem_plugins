<?php
// This is a PLUGIN TEMPLATE.
// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_form_test';

// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.1';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net';
$plugin['description'] = 'Plugin to test mem_form';

// Plugin load order:
// The default value of 5 would fit most plugins, while for instance comment spam evaluators or URL redirectors
// would probably want to run earlier (1...4) to prepare the environment for everything else that follows.
// Orders 6...9 should be considered for plugins which would work late. This order is user-overrideable.
$plugin['order'] = '5';

// Plugin 'type' defines where the plugin is loaded
// 0 = public       : only on the public side of the website (default)
// 1 = public+admin : on both the public and admin side
// 2 = library      : only when include_plugin() or require_plugin() is called
// 3 = admin        : only on the admin side
$plugin['type'] = '0';

if (!defined('txpinterface'))
        @include_once('../zem_tpl.php');

# --- BEGIN PLUGIN CODE ---
define('FORM_TYPE', 'test');

register_callback('mem_form_test', 'mem_form.submit');
function mem_form_test()
{
	global $mem_form_submit, $mem_form_values, $mem_form_errors;
	
	if (!$mem_form_submit || $mem_form_type != FORM_TYPE)
		return;
	
	// process successful form submission	
}

register_callback('mem_form_validate', 'mem_form.validate');
function mem_form_validate()
{
	global $mem_form_submit, $mem_form_values, $mem_form_type;
	
	if (!$mem_form_submit || $mem_form_type != FORM_TYPE)
		return;	

	// dump form values
	dmp($mem_form_values);

	if ($mem_form_values['textfield'] != $mem_form_values['textfield2'])
	{
		mem_form_error('Fields do not match');
	}
}

// use same function to validate every field. field name will be passed in as second arg
register_callback('mem_form_textfield_validate', 'mem_form.store_value');
// function to only validate the 'textfield' field.
//register_callback('mem_form_textfield_validate', 'mem_form.store_value', 'textfield');
function mem_form_textfield_validate($event, $step)
{
	global $mem_form_submit, $mem_form_type, $mem_form_values, $mem_form_error;
	
	if (!$mem_form_submit || $mem_form_type != FORM_TYPE)
		return true;

	// echo field name
	dmp($step);

	if ($mem_form_values[$step] == 'field')
	{
		mem_form_error("You typed 'field' in field '$step'");
			return false;
	}
	
	return true;
}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---

p. This plugin does nothing more than demonstrate how a plugin can customize mem_form validation.

p. Using:
<blockquote><code>
<txp:mem_form type="test">
<hr>
<txp:mem_form_text name='textfield' label="Field 1:" /><br>
<txp:mem_form_text name='textfield2' label="Field 2:" /><br>
<hr>
<txp:mem_form_submit />
</code>
<blockquote>

p. Typing "field" in to either of the text fields will cause them to be invalid.
p. Both fields must match each other to pass form level validation.
</txp:mem_form>

# --- END PLUGIN HELP ---
-->
<?php
}
?>