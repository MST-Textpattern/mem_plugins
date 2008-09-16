<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_simple_form';

// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.1';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Store a form to a table.';

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
h1. Textile-formatted help goes here

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

require_plugin('mem_form');

global $mem_simple_form;

if (!is_array($mem_simple_form))
    $mem_simple_form = array();


function mem_simple_form($atts, $thing='')
{
    global $mem_simple_forms;

    $atts = lAtts(array(
        'type'  =>  'mem_simple_form',
        'table' =>  false,
        'id_field'  => false,
    ),$atts);

    if (empty($table))
        return '<b>mem_simple_form: Table required</b>';

    // save for later
    $mem_simple_form[$type] = array('table' => $atts['table'], 'id_field' => $atts['id_field']);

    return mem_form($atts, $thing);
}


register_callback('mem_simple_form_submitted', 'mem_form.submit');
function mem_simple_form_submitted()
{
	global $mem_simple_form, $mem_form_type, $mem_form_values;

    $form = @$mem_simple_form[$mem_form_type];
    $table = @$form['table'];
    $id_field = @$form['id_field'];

    if ( !(is_array($form) && array_key_exists($form, 'table') )
    {
        // internal error
        return;
    }

    if (empty($table)
    {
        // table name required
        return;
    }


    $fields = array();

    $id = false;

	//dmp($mem_form_values);
	foreach($mem_form_values as $k=>$v)
	{
	    // split the type from name. int_number_value ==> int, number_value
	    list($type, $name) = split('_', $k, 2);

	    $format = $type == 'int' ? "%s = %d" : "%s = '%s'";

        $s = sprintf($format, doSlash($name), doSlash($v));

	    if ($form['id_field'] == $name)
	    {
	        $id = $s;
	        // don't update id field
	        continue;
	    }

        $fields[] = $s;
	}

	if (!empty($fields))
	{
        $insert = !empty($id_field) && !empty($fields[$id_field]);

   	    if ($insert)
   	    {
   	        $rs = safe_insert( doSlash($table), join(', ',$fields) );
   	    }
   	    else
   	    {
   	        $rs = safe_update( doSlash($table), join(', ',$fields), $id );
   	    }

   	    if ($rs)
   	    {
   	        // yay
   	    }
   	    else
   	    {
   	        // boo
   	    }
	}
	else
	{
	    // no fields?
	}
}

# --- END PLUGIN CODE ---

?>
