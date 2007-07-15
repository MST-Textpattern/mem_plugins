<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_note';

$plugin['version'] = '0.1';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'This prevents the contents of the tag from being displayed to the user.';
$plugin['type'] = 0; // 0 for regular plugin; 1 if it includes admin-side code

@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h2. Textpattern Author's Note Plug-in

p. This plugin can allow notes to be placed inline on an entry, page, form.

h3. Client Plug-in Tags:

* *mem_note*

h4. mem_note

p. This prevents the body of the tag from being displayed to the user.

p. Attributes:

p. Examples:

* <code><txp:mem_note>This text is not shown to the user.</txp:mem_note></code>


# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

////////////////////////////////////////////////////////////
// Plugin mem_note
// Author: Michael Manfre (http://manfre.net/)
// Revisions: 
//
////////////////////////////////////////////////////////////

function mem_note ($atts,$body)
{
	return '';
}

# --- END PLUGIN CODE ---

?>
