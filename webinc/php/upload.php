<?php

include_once("../../inc/bx/init.php");
bx_init::start('./conf/config.xml', '../../');
error_reporting(E_ALL);
$state=0;

$db = $GLOBALS['POOL']->db;
if (isset($_FILES['userfile']['tmp_name']) && is_uploaded_file($_FILES['userfile']['tmp_name'])) {
    $name = $_FILES['userfile']['name'];
    $dest = BX_PROJECT_DIR."files/forum/".$name;
    if (move_uploaded_file($_FILES['userfile']['tmp_name'], $dest)) {
        @chmod($dest, 0755);
        
        if (isset($_POST['tags']) && !empty($_POST['tags']) && $db) {
            
            $dbt = $db->quote(stripslashes($_POST['tags']));
            if($db->exec("INSERT INTO fluxcms_properties(path,name,ns,value) VALUES('/files/forum/".$name."','subject','http://purl.org/dc/elements/1.1/',".$dbt.")")) {
                $state=1;
            }
            
        } 
            
    } else {
        echo "Something went wrong while saving<br/>$dest";
        exit();
    }

}


$tags = null;

if ($db instanceof MDB2_Driver_mysql) {
    $res = $db->query("SELECT * FROM fluxcms_tags WHERE tag!=''");
    if (!MDB2::isError($res)) {
        $tags = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
    }
}

echo '<html>';
echo '<head><title>Upload File</title>';
echo '<link type="text/css" rel="stylesheet" href="/themes/berggebiete/css/main.css" media="screen"/>';
echo <<<SCRPT
 <script type="text/javascript">
                    function appendTag() {
                        s = document.getElementById('taglist');
                        if (s) {
                            tag = s.options[s.options.selectedIndex].value;
                            if (tag!="" && s.options.selectedIndex!=0) {
                                tag = (tag.indexOf(' ') > 0) ? '"'+tag+'"' : tag;
                                tagf = document.getElementById('tags');
                                if (tagf) {
                                    tagf.value = (tagf.value=="") ? tag : tagf.value + " " + tag;
                                }
                            }
                        } 
                    }
                </script>
SCRPT;
echo '<style type="text/css">body { background:#ffffff; }';
echo <<<STYLE

fieldset {
margin: 0px;
padding:  20px;
border: #555555 solid 2px;
border-left: #555555 solid 1px;
border-top: #555555 solid 1px;
}

legend {
text-transform: uppercase;
padding: 0px 4px;
font-size: 15px;
font-weight: bold;
letter-spacing: 0.1em;
}

label   {
width: 130px;
float: left;
line-height: 200%;
}

input {
margin:2px 0px;
}

input.longtext {
width:540px;
}

p {
padding:20px;
border:1px solid #555555;
}

STYLE;
echo '</style>';
echo '</head>';
echo '<body>';
if ($state==1) {
    echo '<p>Das file wurde hochgeladen!<br/><input type="button" name="link" value="File verlinken"';
    echo ' onclick="window.opener.insertTag(window.opener.document.post_form.msg_body,\'[url]http://www.berggebiete.ch/files/forum/'.$name.'\',\'[/url]\');window.close()"/>';
    
    echo '&#160;<input type="button" onclick="window.close()" value="Schliessen"></p>';
} else  {
    echo '<form name="uplform" action="./upload.php" method="POST" enctype="multipart/form-data">';
    echo '<fieldset>';
    echo '<legend>Upload file</legend>';
    echo '<label>Select:</label>';
    echo '<input type="file" name="userfile" value=""/><br/>';
    echo '<label>Tags</label>';
    echo '<input type="text" id="tags" name="tags" class="longtext" value=""><br/>';
    echo '<label>&#160;</label><select id="taglist" onchange="appendTag()">';
    echo '<option>---------------------------------------------------------</option>';
    if (sizeof($tags) > 0) {
        foreach($tags as $tag) {
            echo '<option value="'.$tag['tag'].'">'.$tag['parent1'].' &#187; '.$tag['parent2'].' &#187; '.$tag['tag'].'</option>';
        }
    }

    echo '</select><br/><br/>';
    echo '<label>&#160;</label><input type="submit" name="submit" value="Submit"/>';
    echo '</fieldset>';
    echo '</form></body>';
    echo '</html>';
}
?>
