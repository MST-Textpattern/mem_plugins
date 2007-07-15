<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
# $plugin['name'] = 'abc_plugin';
# $plugin['allow_html_help'] = 1 // Allow raw HTML help, as opposed to Textile.  Not recommended.

$plugin['version'] = '0.1';
$plugin['author'] = 'Threshold State';
$plugin['author_uri'] = 'http://thresholdstate.com/';
$plugin['description'] = 'Simple plugin examples';
$plugin['type'] = 1; // 0 for regular plugin; 1 if it includes admin-side code

if (!defined('txpinterface'))
	@include_once('zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h1. Textile-formatted help goes here

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

// ----------------------------------------------------
// Example public side tags

	// A simple self-closing tag
	// <txp:zem_hello_world name="Bob" />

	function zem_hello_world($atts) {
		extract(lAtts(array(
			'name'  => 'Alice',
		),$atts));

		// The returned value will replace the tag on the page
		return 'Hello, '.$name;
	}

	// A simple enclosing tag
	// <txp:zem_lowercase>I LIKE SPAM</txp:lowercase>

	function zem_lowercase($atts, $thing='') {
		return strtolower(parse($thing));
	}

	// A simple conditional tag
	// <txp:zem_if_alice name="Alice">
	// Alice!
	// <txp:else />
	// Not alice.
	// </txp:zem_if_alice>

	function zem_if_alice($atts, $thing) {
		extract(lAtts(array(
			'name'  => 'Bob',
		),$atts));

		return parse(EvalElse($thing, ($name == 'Alice')));
	}

// ----------------------------------------------------
// Example admin side plugin

	// Add a new tab to the Content area.
	// "test" is the name of the associated event; "testing" is the displayed title
	if (@txpinterface == 'admin') {
		$myevent = 'test';
		$mytab = 'testing';

		// Set the privilege levels for our new event
		add_privs($myevent, '1,2');

		// Add a new tab under 'extensions' associated with our event
		register_tab("extensions", $myevent, $mytab);

		// 'zem_admin_test' will be called to handle the new event
		register_callback("zem_admin_test", $myevent);
	}

	function zem_admin_test($event, $step) {

		// ps() returns the contents of POST vars, if any
		$something = ps("something");
		pagetop("Testing", (ps("do_something") ? "you typed: $something" : ""));

		// The eInput/sInput part of the form is important, setting the event and step respectively

		echo "<div align=\"center\" style=\"margin-top:3em\">";
		echo form(
			tag("Test Form", "h3").
			graf("Type something: ".
				fInput("text", "something", $something, "edit", "", "", "20", "1").
				fInput("submit", "do_something", "Go", "smallerbox").
				eInput("test").sInput("step_a")
			," style=\"text-align:center\"")
		);
		echo "</div>";
	}


# --- END PLUGIN CODE ---

?>
