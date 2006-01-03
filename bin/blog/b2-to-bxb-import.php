<?php

include_once("../inc/bx/init.php");
bx_init::start('conf/config.xml',"../");

$b2['dbName']            = 'b2';
$b2['dbPrefix']          = 'b2';
$b2['dbHost']            = '';
$b2['dbUser']            = 'root';
$b2['dbPass']            = '';
$b2['dbType']            = 'mysql';

$serendipity['dbPrefix'] = "";
$b2db = MDB2::Connect($b2['dbType']."://".$b2['dbUser'].":".$b2['dbPass']."@".$b2['dbHost']."/".$b2['dbName']);
$bxbdb = $GLOBALS['POOL']->db;
$bxbdb->loadModule('extended');

   
     $tidyOptions = array(
            "output-xhtml" => true,
            "show-body-only" => true,
            
            "clean" => true,
            "wrap" => "350",
            "indent" => true,
            "indent-spaces" => 1,
            "ascii-chars" => false,
            "wrap-attributes" => false,
            "alt-text" => "",
            "doctype" => "loose",
            "numeric-entities" => true,
            "drop-proprietary-attributes" => true
            );
 $tidy = new tidy();
//import authors


$res = $b2db->query("select * from ". $b2['dbPrefix'] ."users");
$useridmap = array();
while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $newid = $bxbdb->extended->getOne( "select id from users where user_login = ".$bxbdb->quote($row['user_login']));
    if ($newid) {
        $useridmap[$row['id']] = $newid;
        
        print $row['user_login'] . "already in DB\n";
    }
    else {
        $newid = $bxbdb->extended->getOne("select max(id) from users") + 1;
        $query = "insert into users (user_login, id, user_pass, user_email) VALUES 
        (".$bxbdb->quote($row['user_login']).",".($newid).",".$bxbdb->quote(md5($row['user_pass'])).",".$bxbdb->quote($row['user_email']).")";
        $bxbdb->query($query);
        $useridmap[$row['id']] = $newid;
    }
     $usernamemap[$row['id']] = $row['user_login'];
    
}  

//import categories
$bxbdb->query("delete from blogcategories");


// insert root cat 
$query = "insert into blogcategories(id, name, uri,parentid) VALUES (1,'All','root',0)";
    $bxbdb->query($query);

$res = $b2db->query("select * from ". $b2['dbPrefix'] ."categories");

while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $query = "insert into blogcategories(id, name, uri,parentid) VALUES (".$bxbdb->quote(($row['cat_id'] + 1)).",".$bxbdb->quote($row['cat_name']).",".$bxbdb->quote(makeUri($row['cat_name'])).",1)";
    $bxbdb->query($query);
    print $query . "\n";
}  

//nested set fun
include("../forms/blogcategories/updatetree.php");


//import posts

$bxbdb->query("delete from blogposts");
$bxbdb->query("delete from blogposts2categories");
$res = $b2db->query("select * from ". $b2['dbPrefix'] ."posts ");
while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    
    
    $query = "insert into blogposts 
            (id, post_title, post_date, post_content,  post_uri, post_author, changed) VALUES 
            (".
            $row['id'].",".
            $bxbdb->quote(tidyUp(stripslashes($row['post_title']))).",".
            $bxbdb->quote($row['post_date']).",".
            $bxbdb->quote(tidyUp(stripslashes($row['post_content']))).",".
            $bxbdb->quote(makeUri(stripslashes($row['id'].'-'.$row['post_title']))).",".
            $bxbdb->quote( $usernamemap[$row['post_author']]). ",".
            $bxbdb->quote($row['post_date'])."
            )";
            
            
    $res2 = $bxbdb->query($query);
	
    if (MDB2::isError($res2)) {
        print $res2->getMessage();
        print $res2->getUserinfo();
        
        die();
    }
    $query = "insert into blogposts2categories( id, blogposts_id,blogcategories_id) VALUES(".
        $row['id'].",".
        $row['id'].",".
        ($row['post_category'] + 1) .")";
      $res2 = $bxbdb->query($query);
    if (MDB2::isError($res2)) {
        print $res2->getMessage();
        print $res2->getUserinfo();
        die();
    }   
        
    
    print "import post ".$row['id']."\n";
}  
//import comments
$bxbdb->query("delete from blogcomments");

$res = $b2db->query("select * from ". $b2['dbPrefix'] ."comments ");
while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    if (strpos($row['comment_content'],"<trackback") !== false ) {
        $type = "TRACKBACK";
    } else if (strpos($row['comment_content'],"<pingback") !== false) {
        $type = "PINGBACK";
    } else {
        $type = "NORMAL";
    }
       
    $query = "insert into blogcomments 
            (id, comment_posts_id, comment_date, comment_author, comment_author_email, comment_author_url,
            comment_author_ip, comment_content, comment_type)
             VALUES  (". 
  
            $bxbdb->quote(($row['comment_id'])).",".
            $bxbdb->quote(($row['comment_post_id'])).",".
            $bxbdb->quote(($row['comment_date'])).",".
            $bxbdb->quote(tidyUp(stripslashes($row['comment_author']))).",".
            $bxbdb->quote(stripslashes($row['comment_author_email'])).",".
            $bxbdb->quote(tidyUp(stripslashes($row['comment_author_url']))).",".
            $bxbdb->quote(stripslashes($row['comment_author_ip'])).",".
            $bxbdb->quote(tidyUp(stripslashes($row['comment_content']))).",".
            $bxbdb->quote($type)."".
            ")";
            
           
    $res2 = $bxbdb->query($query);
    if (MDB2::isError($res2)) {
        print $res2->getMessage();
        print $res2->getUserinfo();
        die();
    }
     print "import comment ".$row['comment_id']." on post " . $row['comment_post_id'] ." with type $type\n";
    
}

 function makeUri($title,$id = 0) {
	if (!$title) {
		$title = "notitle".$id;
	}
        
       $newValue= strtolower(utf8_encode(preg_replace("/[-;: \.!,?+'$£\"*ç%&\/\(\)=]/","-",$title)));
		$newValue= preg_replace("/-{2,}/","-",$newValue);
		$newValue= preg_replace("/[öÖ]/u","oe",$newValue);
		$newValue= preg_replace("/[üÜ]/u","ue",$newValue);
		$newValue= preg_replace("/[äÄ]/u","ae",$newValue);
		$newValue= preg_replace("/[éè]/u","e",$newValue);
		$newValue= preg_replace("/[ï]/u","i",$newValue);
		$newValue= preg_replace("/[ñ]/u","n",$newValue);
		$newValue= preg_replace("/[à]/u","a",$newValue);
//numbers at the end are nasty for bxcms
		$newValue= preg_replace("/—+$/","",$newValue);
		$newValue= preg_replace("/^-/","",$newValue);
        return $newValue;
    }
    
    
    
function    tidyUp ($string) {
        global $tidy, $tidyOptions;
        $tidy->parseString($string,$tidyOptions,"latin1");
        $tidy->cleanRepair();

        return bx_helpers_string::utf2entities(utf8_encode((string) $tidy));
    }
                
?> 
