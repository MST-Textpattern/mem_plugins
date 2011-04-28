<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_popup_cat';

$plugin['version'] = '0.1';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://forum.textpattern.com/viewtopic.php?id=2862';
$plugin['description'] = 'Context sensitive category list';
$plugin['type'] = 1; // 0 for regular plugin; 1 if it includes admin-side code

@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

p. This is a modified version of zem_popup_cat that will create proper clean urls.

<p>&lt;txp:mem_popup_cat /&gt; creates a popup/dropdown list similar to &lt;txp:popup type="c" /&gt;.  It shows categories in a context sensitive manner.  On the front page, only categories which contain active articles will be included in the list.  On a section page, only categories which contain active articles from the current section will be included.</p>

<p>Supported attributes:
</p><dl>
<dt>section</dt><dd>Include only categories associated with the specified section.  The form will jump to that section when the user selects an item.  <i>section=""</i> will show categories from all sections, almost exactly like txp:popup.</dd>

<dt>default</dt><dd>Display this name in the list as the default (empty) selection</dd>
<dt>label</dt>
<dt>wraptag</dt><dd>As per txp:popup</dd>
</dl>

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

////////////////////////////////////////////////////////////
// Plugin 
// Author: Michael Manfre (http://manfre.net/)
// Revisions: 
//
////////////////////////////////////////////////////////////
	function mem_popup_cat($atts) {
		global $pretext, $url_mode, $pfr, $permlink_mode;
		
		$section = (!isset($atts["section"]) ? $pretext["s"] : $atts["section"]);
		$default = (empty($atts["default"]) ? "" : $atts["default"]);
		$label = (empty($atts["label"]) ? "" : $atts["label"]);
		$wraptag = (empty($atts["wraptag"]) ? "" : $atts["wraptag"]);

		$rs = getRows("SELECT DISTINCT c.name, c.title FROM ".PFX."txp_category c, ".PFX."textpattern t WHERE (t.Category1=c.name OR t.Category2=c.name) AND t.Status=4 ORDER BY c.name ASC");

		if ($permlink_mode=='messy') {
			$js = 'submit(this.form);';
		} else {
			$js = 'location.href=this.options[this.selectedIndex].value;';
		}

		$out = "<select name=\"c\" onchange=\"{$js}\">".n.
		t."<option value=\"\">".htmlspecialchars($default)."</option>".n;
		if ($rs) {
			foreach ($rs as $c) {
				$sel = ($pretext["c"]==$c["name"] ? " selected=\"selected\"" : "");
				$out .= t.t."<option value=\"{$pfr}category/".urlencode($c["name"])."\"$sel>".
					htmlspecialchars($c["title"])."</option>".n;
			}
			$out .= "</select>";
         $out = ($label) ? $label.br.$out : $out;
         $out = ($wraptag) ? tag($out,$wraptag) : $out;
         $out.= "<noscript><input type=\"submit\" value=\"go\" /></noscript>";
      if ($permlink_mode=='messy') {
				$hidden = ($section ? "<input type=hidden name=\"s\" value=\"$section\" />" : "");
				return "<form action=\"{$pfr}\" method=\"get\">".n.$out.$hidden."</form>";
      } else {
				$s = ($section ? "$section/" : "");
				return "<form action=\"{$pfr}{$s}\" method=\"get\">".n.$out."</form>";
      }
		}

	}

# --- END PLUGIN CODE ---

?>
