<?php
/*
The most simple twoday imported imaginable. It works like this: 
Log in to your twoday.net account and do a full export using the default template, 
so you'll get a file that's called like 2006-05-03-export-full-txt.txt. Upload this 
file to your Flux CMS server, adjust the variables right below and run it. 

Note that the importer doesn't handle comments, and it ignores categories as well.
Timestamps aren't fully parsed, we use but the date. Adding those features is trivial,
but I didn't need them.
*/


# CONFIGURATION
$exportFile = './2006-05-03-export-full-txt.txt';
$dsn = 'mysql://user:password@localhost/database';
$tablePrefix = 'fluxcms_';
$idOffset = 1;
$categoryId = 7; 


##################################################################

require_once('DB.php');

$db = DB::connect($dsn);
if (DB::isError($db)) {
    die($db->getMessage());
}

preg_match_all("#--------\nTITLE:([^\n]+)\n.+\nDATE: (.+)\n.*AUTHOR: (.+)\n.*\n-----\nBODY:\n(.*)\n-----\n#Usi", "--------\n" .file_get_contents($exportFile) , $matches);

foreach ($matches[1] as $id => $title){

    $post = array('id' => $id + $idOffset);


    // title
    $title = html_entity_decode(trim($title));
    $post['post_title'] = $title;

    //  uri
    $post['post_uri'] = makeUri($post['post_title']);

    //  date - ignoring time, too lazy today
    preg_match('#(\d\d)/(\d\d)/(\d\d\d\d)#', $matches[2][$id], $parts);
    $post['post_date'] = $parts[3].'-'.$parts[1].'-'.$parts[2];

    // author
    $post['post_author'] = trim($matches[3][$id]);

    // content
    $post['post_content']  = tidy_repair_string($matches[4][$id], array('output-xhtml' => true, 'numeric-entities' => true, 'show-body-only' => true,));

    $in = $db->autoexecute($tablePrefix . 'blogposts', $post, DB_AUTOQUERY_INSERT);
    if ($in != DB_OK) {
        die("\n*".$in->getDebugInfo()."*\n");
    }

    else {
        echo $post['post_title'] . " -> ok ..\n";
    }
    
    $db->autoexecute($tablePrefix . 'blogposts2categories' , array('id' => $post['id'], 'blogposts_id' => $post['id'], 'blogcategories_id' => $categoryId), DB_AUTOQUERY_INSERT);    

}



function makeUri($title) {

    $newValue= strtolower(utf8_encode(preg_replace("/[-;: \.!,?+'$Â£\"*Ã§%&\/\(\)=]/","-",$title)));
    $newValue= preg_replace("/-{2,}/","-",$newValue);
    $newValue= preg_replace("/[Ã¶Ã–]/u","oe",$newValue);
    $newValue= preg_replace("/[Ã¼Ãœ]/u","ue",$newValue);
    $newValue= preg_replace("/[Ã¤Ã„]/u","ae",$newValue);
    $newValue= preg_replace("/[Ã©Ã¨]/u","e",$newValue);
    $newValue= preg_replace("/[Ã¯]/u","i",$newValue);
    $newValue= preg_replace("/[Ã±]/u","n",$newValue);
    $newValue= preg_replace("/[Ã ]/u","a",$newValue);
    //numbers at the end are nasty for bxcms
    $newValue= preg_replace("/â€”+$/","",$newValue);
    $newValue= preg_replace("/^-/","",$newValue);
    return $newValue;

}

