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

$plugin['version'] = '0.1';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'This admin only plugin allows for multi file uploads in the file tab.';

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
h1. Installation

p. Drop "jQuery.MultiFile.js":http://www.fyneworks.com/jquery/multiple-file-upload/ in to the ~/textpattern/ folder. This works fine with Textpattern version 4.0.5, but there is no guarantee that it will work with newer versions.

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

	register_callback('mem_multifile_insert', 'file', 'file_insert', 1);

	global $event;
	
	if ($event == 'file')
		ob_start('mem_multifile_head');
	
	function mem_multifile_head($buffer) 
	{
    $find = '</head>';
    $replace = '';    
		$replace .= n.t.'<script type="text/javascript" src="'.hu.'/textpattern/jquery.MultiFile.js"></script>'.n;
    $replace .= <<<js
<script type="text/javascript">
<!--
	$.extend($, {
		MultiFile: function( o /* Object */ ){
			return $("#file-upload").MultiFile(o);
		}
	});
//-->
</script>
js;
		$replace .= $find;

    return str_replace($find, $replace, $buffer);
		
	}
	
	function mem_multifile_insert()
	{
		global $txpcfg,$extensions,$txp_user,$file_base_path,$file_max_upload_size,$event;

		// this will break other plugins that try to hook the file event...oh well...
		$event = '';
		$inc = txpath . '/include/txp_file.php';
		require_once($inc);
		$event = 'file';

		
		extract($txpcfg);
		extract(doSlash(gpsa(array('category','permissions','description'))));

		$failed_files = array();
		$ok_files = array();

		foreach (array_keys($_FILES) as $fkey) {
			if ( strncmp($fkey,"thefile", 7) == 0 ) {				
				$name = $_FILES[$fkey]['name'];
				$file = get_uploaded_file($_FILES[$fkey]['tmp_name']);
				$error = $_FILES[$fkey]['error'];
				
				if (empty($name)) continue;
				
				if ($file === false) {
					$failed_files[] = htmlspecialchars($name)." - " . upload_get_errormsg($error);
					continue;
				}
				
				$size = filesize($file);
				if ($file_max_upload_size < $size) {
					unlink($file);
					$failed_files[] = htmlspecialchars($name)." - ".upload_get_errormsg(UPLOAD_ERR_FORM_SIZE);
					continue;
				}
				
				if (!is_file(build_file_path($file_base_path,$name))) {
		
					$id = file_db_add($name,$category,$permissions,$description,$size);
					
					if(!$id){
						$failed_files[] = htmlspecialchars($name)." - (db_add)";
						continue;
					} else {		
						$id = assert_int($id);
						$newpath = build_file_path($file_base_path,trim($name));
						
						if(!shift_uploaded_file($file, $newpath)) {
							safe_delete("txp_file","id = $id");
							safe_alter("txp_file", "auto_increment=$id");
							if ( isset( $GLOBALS['ID'])) unset( $GLOBALS['ID']);
							$failed_files[] = htmlspecialchars($name)." - ". htmlspecialchars($newpath).' '.gTxt('upload_dir_perms');
							// clean up file
						} else {
							file_set_perm($newpath);
		
							$ok_files[] = htmlspecialchars($name). "($id)";

							// jquery.multifile adds a blank entry on all uploads
							if (count($_FILES) == 2) {
								$message = gTxt('file_uploaded', array('{name}' => htmlspecialchars($name)));
								file_edit($message, $id);
								return;
							}
						}
					}
				}
				else
				{
					$failed_files[] = gTxt('file_already_exists', array('{name}' => $name));
				}
			}
		}
		$message = '';
		
		if (count($failed_files) > 0) {
			// could not get uploaded file
			$message = '<b>'.gTxt('file_upload_failed') .'</b>: '. join(', ', $failed_files).' ';
		}
		
		if (count($ok_files) > 0 ) {
			$message .= '<b>'.gTxt('file_uploaded', array('{name}' => '')).'</b>';
			$message .= ': ' . join(', ', $ok_files);
		}
		
		file_list($message);
	}


# --- END PLUGIN CODE ---

?>
