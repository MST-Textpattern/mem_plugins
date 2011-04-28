<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'mem_templates';

// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.2';
$plugin['author'] = 'Michael Manfre';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'This is a modified version of hcg_templates to support the importing and exporting of enabled plugins.';

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

p. This is a modified version of hcg_templates to support the importing/exporting of enabled plugins and sections.

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

/*
    PUBLIC PLUGIN CONFIG
    -------------------------------------------------------------------------
*/
    $mem_templates = array(
        "base_dir"				=>  "_templates",
        
        "subdir_section"	=>	"section",
        "subdir_plugins"	=>	"plugins",
        "subdir_pages"		=>  "pages",
        "subdir_forms"		=>	"forms",
        "subdir_css"			=>	"style",

				"ext_section"			=>	".sec",
        "ext_plugins"			=>	".plugin",
        "ext_pages"       =>	".page",
        "ext_forms"       =>	".form",
        "ext_css"         =>	".css"
    );

/*
    PLUGIN CODE (no editing below this line, please)
    -------------------------------------------------------------------------
*/
    define('_MEM_TEMPLATES_IMPORT', 1);
    define('_MEM_TEMPLATES_EXPORT', 2);
    $GLOBALS['_MEM_TEMPLATES'] = $mem_templates;

    /*
        PLUGIN CODE::INSTANTIATION
        -------------------------------------------------------------
    */
        if (@txpinterface == 'admin') {
            $import         = 'mem_templates';
            $import_tab     = 'Templates';

            add_privs($import, '1,2');
            register_tab("extensions", $import, $import_tab);
            register_callback("mem_templates", $import);
        }

    /*
        PLUGIN CODE::MAIN CALLBACK
        -------------------------------------------------------------
    */
        function mem_templates($event, $step='') {
            $GLOBALS['prefs'] = get_prefs();
            $template = new mem_template();

            pagetop("Process Templates", "");
            print "
            <style type='text/css'>
                .success { color: #009900; }
                .failure { color: #FF0000; }
            </style>

            <table cellpadding='0' cellspacing='0' border='0' id='list' align='center'>
                <tr>
                    <td>
            ";

            switch ($step) {
                case "import":
                    $template->import(ps('import_dir'));
                    break;

                case "export":
                    $dir = ps('export_dir');

                    $dir =  str_replace(
                                array(" "),
                                array("-"),
                                $dir
                            );
                    $template->export($dir);
                    break;

                default:
                    $importlist = $template->getTemplateList();

                    print "
                        <h1>Import Templates</h1>
                    ".form(
                          graf('Which template set would you like to import?'.
                            selectInput('import_dir', $importlist, '', 1).
                            fInput('submit', 'go', 'Go', 'smallerbox').
                            eInput('mem_templates').sInput('import')
                        )
                    );

                    print "
                        <h1>Export Templates</h1>
                    ".form(
                          graf('Name this export:'.
                            fInput('text', 'export_dir', '').
                            fInput('submit', 'go', 'Go', 'smallerbox').
                            eInput('mem_templates').sInput('export')
                        )
                    );

                    break;
            }
            print "
                    </td>
                </tr>
            </table>
            ";
        }

    class mem_template {
        function mem_template() {
            global $prefs;
            global $_MEM_TEMPLATES;

            $this->_config = $_MEM_TEMPLATES;

        /*
            PRIVATE CONFIG
            ------------------------------------------------------
        */
            $this->_config['root_path']         =   $prefs['path_to_site'];
            $this->_config['full_base_path']    =   sprintf(
                                                        "%s/%s",
                                                        $this->_config['root_path'],
                                                        $this->_config['base_dir']
                                                    );

            $this->_config['error_template']    =   "
                <h1 class='failure'>%s</h1>
                <p>%s</p>
            ";

            $missing_dir_head   = "Template Directory Missing";
            $missing_dir_text   = "The template directory `<strong>%1\$s</strong>` does not exist, and could not be automatically created.  Would you mind creating it yourself by running something like</p><pre><code>    mkdir %1\$s\n    chmod 777 %1\$s</code></pre><p>That should fix the issue.  You could also adjust the plugin's directory by modifying <code>\$mem_templates['base_dir']</code> in the plugin's code.";
            $cant_write_head    = "Template Directory Not Writable";
            $cant_write_text    = "I can't seem to write to the template directory `<strong>%1\$s</strong>`.  Would you mind running something like</p><pre><code>    chmod 777 %1\$s</code></pre><p>to fix the problem?";
            $cant_read_head     = "Template Directory Not Readable";
            $cant_read_text     = "I can't seem to read from the template directory `<strong>%1\$s</strong>`.  Would you mind running something like</p><pre><code>    chmod 777 %%1\$s</code></pre><p>to fix the problem?";


            $this->_config['error_missing_dir'] =   sprintf(
                                                        $this->_config['error_template'],
                                                        $missing_dir_head,
                                                        $missing_dir_text
                                                    );
            $this->_config['error_cant_write']  =   sprintf(
                                                        $this->_config['error_template'],
                                                        $cant_write_head,
                                                        $cant_write_text
                                                    );
            $this->_config['error_cant_read']   =   sprintf(
                                                        $this->_config['error_template'],
                                                        $cant_read_head,
                                                        $cant_read_text
                                                    );

            $this->exportTypes = array(
                "plugins" =>  array(
                                "ext"       =>  $this->_config['ext_plugins'],
                                "data"      =>  "code",
                                "fields"    =>  "name, status, author, author_uri, version, description, help, code, code_restore, code_md5, type",
                                "nice_name" =>  "Plugin Files",
                                "regex"     =>  "/(.+)\.(.+)".$this->_config['ext_plugins']."/",
                                "sql"       =>  "`status` = %d, `author` = '%s', `author_uri` = '%s', `version` = '%s', `description` = '%s', `help` = '%s', `code` = '%s', `code_restore` = '%s', `code_md5` = '%s', `type` = %d",
                                "subdir"    =>  $this->_config['subdir_plugins'],
                                "table"     =>  "txp_plugin",
                                "filter"    =>    "`status` = 1"
                            ),
                "pages" =>  array(
                                "ext"       =>  $this->_config['ext_pages'],
                                "data"      =>  "user_html",
                                "fields"    =>  "name, user_html",
                                "nice_name" =>  "Page Files",
                                "regex"     =>  "/(.+)".$this->_config['ext_pages']."/",
                                "sql"       =>  "`user_html` = '%s'",
                                "subdir"    =>  $this->_config['subdir_pages'],
                                "table"     =>  "txp_page",
                                "filter"    =>    "1=1"
                            ),
                "forms" =>  array(
                                "ext"       =>  $this->_config['ext_forms'],
                                "data"      =>  "Form",
                                "fields"    =>  "name, type, Form",
                                "nice_name" =>  "Form Files",
                                "regex"     =>  "/(.+)\.(.+)".$this->_config['ext_forms']."/",
                                "sql"       =>  "`Form` = '%s', `type` = '%s'",
                                "subdir"    =>  $this->_config['subdir_forms'],
                                "table"     =>  "txp_form",
                                "filter"    =>    "1=1"
                            ),
                "css"   =>  array(
                                "ext"       =>  $this->_config['ext_css'],
                                "data"      =>  "css",
                                "fields"    =>  "name, css",
                                "nice_name" =>  "CSS Rules",
                                "regex"     =>  "/(.+)".$this->_config['ext_css']."/",
                                "sql"       =>  "`css` = '%s'",
                                "subdir"    =>  $this->_config['subdir_css'],
                                "table"     =>  "txp_css",
                                "filter"    =>    "1=1"
                            ),
                "section" =>  array(
                                "ext"       =>  $this->_config['ext_section'],
                                "data"      =>  "title",
                                "fields"    =>  "name, page, css, is_default, in_rss, on_frontpage, searchable, title",
                                "nice_name" =>  "Sections",
                                "regex"     =>  "/(.+)".$this->_config['ext_section']."/",
                                "sql"       =>  "`name` = '%s', `page` = '%s', `is_default` = %d, `in_rss` = %d, `on_frontpage` = %d, `searchable` = %d, `title` = '%s'",
                                "subdir"    =>  $this->_config['subdir_section'],
                                "table"     =>  "txp_section",
                                "filter"    =>	"1=1"
                            )
            );
        }

        function checkdir($dir = '', $type = _MEM_TEMPLATES_EXPORT) {
            /*
                If $type == _EXPORT, then:
                    1.  Check to see that /base/path/$dir exists, and is
                        writable.  If not, create it.
                    2.  Check to see that /base/path/$dir/subdir_* exist,
                        and are writable.  If not, create them.

                If $type == _IMPORT, then:
                    1.  Check to see that /base/path/$dir exists, and is readable.
                    2.  Check to see that /base/path/$dir/subdir_* exist, and are readable.
            */
            $dir =  sprintf(
                        "%s/%s",
                        $this->_config['full_base_path'],
                        $dir
                    );

            $tocheck =  array(
                            $dir,
                            $dir.'/'.$this->_config['subdir_plugins'],
                            $dir.'/'.$this->_config['subdir_pages'],
                            $dir.'/'.$this->_config['subdir_css'],
                            $dir.'/'.$this->_config['subdir_forms'],
                            $dir.'/'.$this->_config['subdir_section']
                        );
            foreach ($tocheck as $curDir) {
                switch ($type) {
                    case _MEM_TEMPLATES_IMPORT:
                        if (!is_dir($curDir)) {
                            echo sprintf($this->_config['error_missing_dir'], $curDir);
                            return false;
                        }
                        if (!is_readable($curDir)) {
                            echo sprintf($this->_config['error_cant_read'], $curDir);
                            return false;
                        }
                        break;

                    case _MEM_TEMPLATES_EXPORT:
                        if (!is_dir($curDir)) {
                            if (!@mkdir($curDir, 0777)) {
                                echo sprintf($this->_config['error_missing_dir'], $curDir);
                                return false;
                            }
                        }
                        if (!is_writable($curDir)) {
                            echo sprintf($this->_config['error_cant_write'], $curDir);
                            return false;
                        }
                        break;
                }
            }
            return true;
        }

        /*
            EXPORT FUNCTIONS
            ----------------------------------------------------------
        */
        function export($dir = '') {
            if (!$this->checkdir($dir, _MEM_TEMPLATES_EXPORT)) {
                return;
            }

            foreach ($this->exportTypes as $type => $config) {
                print "
                    <h1>Exporting ".$config['nice_name']."</h1>
                    <ul class='results'>
                ";

                $rows = safe_rows($config['fields'], $config['table'], $config['filter']);

                foreach ($rows as $row) {
                    $filename       =   sprintf(
                                            "%s/%s/%s/%s%s",
                                            $this->_config['full_base_path'],
                                            $dir,
                                            $config['subdir'],
                                            $row['name'] . (isset($row['type'])?".".$row['type']:""),
                                            $config['ext']
                                        );
                    $nicefilename =     sprintf(
                                            ".../%s/%s/%s%s",
                                            $dir,
                                            $config['subdir'],
                                            $row['name'] . (isset($row['type'])?".".$row['type']:""),
                                            $config['ext']
                                        );

										$data = '';
                    if ($type=='plugins' || $type=='section') {
                        $data = base64_encode(serialize($row));
                    }
                    else if (isset($row['css'])) {
                        $data = base64_decode($row['css']);
                    } else {
                    	$data = $row[$config['data']];
                    }
                    
                    $f = @fopen($filename, "w+");
                    if ($f) {
                        fwrite($f,$data);
                        fclose($f);
                        print "
                        <li><span class='success'>Successfully exported</span> ".$config['nice_name']." '".$row['name']."' to '".$nicefilename."'</li>
                        ";
                    } else {
                        print "
                        <li><span class='failure'>Failure exporting</span> ".$config['nice_name']." '".$row['name']."' to '".$nicefilename."'</li>
                        ";
                    }
                }
                print "
                    </ul>
                ";
            }
        }

        /*
            IMPORT FUNCTIONS
            ----------------------------------------------------------
        */
        function getTemplateList() {
        	if (!is_readable($this->_config['full_base_path'])) {
        		return array();
        	}
            $dir = opendir($this->_config['full_base_path']);
            
            $list = array();
	
            while(false !== ($filename = readdir($dir))) {
                if (
                    is_dir(
                        sprintf(
                            "%s/%s",
                            $this->_config['full_base_path'],
                            $filename
                        )
                    ) && $filename != '.' && $filename != '..'
                ) {
                    $list[$filename] = $filename;
                }
            }
            return $list;
        }

        function import($dir) {
            if (!$this->checkdir($dir, _MEM_TEMPLATES_IMPORT)) {
                return;
            }

            /*
                Auto export into `preimport-data`
            */
            print "
                <h1>Backing up current template data</h1>
                <p>Your current template data will be available for re-import as `preimport-data`.</p>
            ";

            $this->export('preimport-data');

            $basedir =  sprintf(
                            "%s/%s",
                            $this->_config['full_base_path'],
                            $dir
                        );
            
            $first_section = false;
            $css = array();
            $page = array();

            foreach ($this->exportTypes as $type => $config) {
                print "
                    <h1>Importing ".$config['nice_name']."</h1>
                    <ul class='results'>
                ";

                $exportdir =    sprintf(
                                    "%s/%s",
                                    $basedir,
                                    $config['subdir']
                                );

                $dir = opendir($exportdir);
                while (false !== ($filename = readdir($dir))) {
                    if (preg_match($config['regex'], $filename, $filedata)) {
                        $templateName = addslashes($filedata[1]);
                        $templateType = (isset($filedata[2]))?$filedata[2]:'';

                        $f =    sprintf(
                                    "%s/%s",
                                    $exportdir,
                                    $filename
                                );
                        
                        $extra_message = '';

                        if ($data = file($f)) {
                            if ($type == 'css') {
                                $data = base64_encode(implode('', $data));
                            } else if ($type == 'plugins' || $type == 'section') {
                                $data = doSlash(unserialize(base64_decode(implode('', $data))));
                            } else {
                                $data = addslashes(implode('', $data));
                            }
                            
                            if ($type == 'section' && $first_section)
                            {
                            	safe_update($config['table'], "`is_default` = 0", '1=1');
                            	$first_section = true;
                            }
                            
                            if ($type == 'css')
                            	$css[] = $templateName;
                            if ($type == 'page')
                            	$page[] = $templateName;
                            
                            if ($type == 'plugins') {
                                $rs = safe_row('version, status', $config['table'], "name='".$templateName."'");
                               	$set = sprintf($config['sql'], $data['status'], $data['author'], $data['author_uri'], $data['version'], $data['description'], $data['help'], $data['code'], $data['code_restore'], $data['code_md5'], $data['type']);
                                if ($rs) {
                                    if ($rs['status'] == 0 || strcasecmp($data['version'], $rs['version']) < 0) {
                                        $result = safe_update($config['table'], $set, "`name` = '".$templateName."'");
                                    } else {
                                        $result = 1;
                                    }
                                    $success = ($result)?1:0;
                                } else {
                                    $result = safe_insert($config['table'], $set.", `name` = '".$templateName."'");
                                    $success = ($result)?1:0;
                                }
                            }
                            else if ($type == 'section') 
                            {
                            	$set = sprintf($config['sql'], $data['name'], $data['page'], $data['is_default'], $data['in_rss'], $data['on_frontpage'], $data['searchable'], $data['title']);
                            	if (safe_field('name', $config['table'], "name='".$templateName."'")) {
                              	$result = safe_update($config['table'], $set, "`name` = '".$templateName."'");
                              } else {
                              	$result = safe_insert($config['table'], $set);
                              }
															$success = ($result)?1:0;
															
															if (in_array($data['page'], $page))
																$extra_message .= '. Missing page "' . $data['page'] . '"';
															if (in_array($data['css'], $css))
																$extra_message .= '. Missing style "' . $data['css']. '"';
                            } else {
                              if (safe_field('name', $config['table'], "name='".$templateName."'")) {
                                $result = safe_update($config['table'], sprintf($config['sql'], $data, $templateType), "`name` = '".$templateName."'");
                              } else {
                              	$result = safe_insert($config['table'], sprintf($config['sql'], $data, $templateType).", `name` = '".$templateName."'");
                              }
															$success = ($result)?1:0;
                            }
                        }
                        
                        if (!empty($extra_message))
                        	$extra_message = '. ' . $extra_message;

                        $success = true;
                        if ($success) {
                            print "<li><span class='success'>Successfully imported</span> file '".$filename.$extra_message."'</li>";
                        } else {
                            print "<li><span class='failure'>Failed importing</span> file '".$filename.$extra_message."'</li>";
                        }
                    }
                }

                print "
                    </ul>
                ";
            }
        }
    }



# --- END PLUGIN CODE ---

?>
