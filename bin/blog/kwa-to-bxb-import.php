#!/usr/local/bin/php5
<?php
/**
* vars - please adjust to your needs.
*/

/* docroot of BXB */
define("BXB_DOCROOT", '');

/* tidy */
define("TIDY_BIN", '/usr/bin/tidy -q');

/* unimportant ;) */
define("MSG_PREFX", '[kwa2bxb]: ');

/* tmp-dir */
define("TMP_DIR", "/tmp");


/* dsn for kaywa-blog db (thatdb) */
$dsn_kwa = 'mysql://user:pass@localhost/kwa';

/* dsn for bxb-db (thisdb) */
$dsn_bxb = 'mysql://user:pass@localhost/etoy';


/* you shouldn't need to change something below this line */




if (is_dir(BXB_DOCROOT)) {
    ini_set("include_path", ini_get("include_path") . ":" . BXB_DOCROOT."/inc");
}


include_once 'MDB2.php';
include_once '../conf/config.inc.php';
include_once 'SQL/Tree.php';

/* KWA db */
$thatdb = MDB2::connect($dsn_kwa);
if (MDB2::isError($thatdb)) {
    print MSG_PREFIX." db connect with kwa-dsn: $dsn_kwa failed!\n";
    exit(1);
}

/* BXB db */
$thisdb = MDB2::connect($dsn_bxb);
if (MDB2::isError($thisdb)) {
    print MSG_PREFIX." db connect with bxb-dsn: $dsn_bxb failed!\n";
    exit(1);
}
$moblogTable = "image";

$tables = array(/* Posts */
                'posts' => array('thistablename'    =>  'blogposts',
                                 'thattablename'    =>  'posts',
                                 
                                 'fields'           =>  array('id'              => 'id', // mdb2 lowercases ID
                                                             'post_author'      => 'post_author',
                                                             'post_date'        => 'post_date',
                                                             'post_content'     => 'post_content',
                                                             'post_title'       => 'post_title',
                                                             'post_category'    => 'post_category',
                                                             'post_karma'       =>  '',
                                                             'post_uri'         => 'post_uri',
                                                             'changed'          => 'post_modified', 
                                                             'post_comment_mode'=>  '',
                                                             'post_status'      => 'status'
                                                             ),

                                 'callbacks'        =>  array('post_content'    => array('utf2entities', 'tidy'),
                                                              'post_title'      => array('utf2entities', 'tidy'),
                                                              'post_uri'        => array('makePostUri')
                                                              )
                                
                                ),

                /* Users */
                'users' => array('thistablename'    =>  'users',
                                 'thattablename'    =>  'users',
                                 'fields'           =>  array('id'              => 'id',
                                                             'user_login'       => 'user_login',
                                                             'user_pass'        => 'user_pass',
                                                             'user_fullname'    => '',
//                                                             'user_nick'        => 'user_nickname',
                                                             'user_email'       => 'user_email'
                                                            ),
                                
                                 'callbacks'        =>  array()
			
                                ),
                
                /* Categories */
                'cats'  => array('thistablename'    =>  'blogcategories',
                                 'thattablename'    =>  'categories',
                                 'fields'           =>  array('id'              => 'cat_id',
                                                              'name'            => 'cat_name',
                                                              'uri'             => 'cat_name'),
                                                              
                                                              
                                 'callbacks'        =>  array('uri'             => array('makeUri'),
							       'id'             => array('plusOne')),
                                 'defaults'         =>  array(array('id'        => 1,
                                                                    'name'      => 'All',
                                                                    'uri'       => 'root',
                                                                    'parentid'  => 0)
                                                              )
                                ),
                
                /* Blogroll */
                'links' => array('thistablename'    =>  'bloglinks',
                                 'thattablename'    =>  'blogroll',
                                 'fields'           =>  array('id'              => 'id',
                                                              'link'            => 'url',
                                                              'text'            => 'name',
                                                              'bloglinkscategories' => 'cat'
                                                              ),
                                 'callbacks'        =>  array()
                                ),
                
                /* Blogrollcats */
                'linkcat'=>array('thistablename'    =>  'bloglinkscategories',
                                 'thattablename'    =>  'blogroll_cats',
                                 'fields'           =>  array('id'              => 'id',
                                                              'name'            => 'name',
                                                              'rang'            => 'rang'
                                                             ),
                                 'callbacks'        =>  array()
                                ),
                
                /* comments */
                'comms' => array('thistablename'    =>  'blogcomments',
                                 'thattablename'    =>  'comments',
                                 'fields'           =>  array('id'              => 'comment_id',
                                                              'comment_posts_id' => 'comment_post_id',
                                                              'comment_author'  => 'comment_author',
                                                              'comment_author_email' => 'comment_author_email',
                                                              'comment_author_url' => 'comment_author_url',
                                                              'comment_author_ip' => 'comment_author_ip',
                                                              'comment_date'    => 'comment_date',
                                                              'comment_content' => 'comment_content'
                                                             ),
                                  'callbacks'       =>  array()
                                
                                )
                    
                    );



importTables($tables, $thisdb, $thatdb, 'id');
importTableTree('blogcategories', 'id', 'parentid', 'fulluri', 'fullname', 'uri', 'name',
                array('name','uri','fulluri'), $thisdb
                );


function importTables($tables, &$thisdb, &$thatdb, $thisIdField=NULL) {
    foreach($tables as $table) {
        importTable($table, $thisdb, $thatdb);
    }
}

// import blogposts2categories
$thisdb->query( "delete from blogposts2categories");

$thisdb->query( "insert into blogposts2categories  (blogposts_id, blogcategories_id) select id, post_category + 1 from blogposts;");

// update authorid 2 authorname

$thisdb->query("ALTER TABLE `blogposts` ADD `post_authorid` INT NOT NULL AFTER `post_author` ;");
$thisdb->query("update blogposts set post_authorid = post_author ;");
$thisdb->query("ALTER TABLE `blogposts` CHANGE `post_author` `post_author` VARCHAR( 40 ) DEFAULT '' NOT NULL ;");
$thisdb->query("ALTER TABLE `blogposts` ADD INDEX ( `post_author` ) ;");
$thisdb->query("update blogposts, users set post_author = users.user_login where users.ID  = blogposts.post_authorid ;");
$thisdb->query("ALTER TABLE `blogposts` DROP `post_authorid` ;");

importMoblogs($moblogTable,$tables,$thisdb,$thatdb);

function importTable($table, &$thisdb, &$thatdb, $thisIdField=NULL) {
   
    
    $thatquery = sprintf("SELECT * FROM %s", $table['thattablename']); 
    if (is_object($thatdb) && is_object($thisdb)) {

        if (importDeleteTable($table['thistablename'], $thisdb)) {        
            $thatres = $thatdb->query($thatquery);
            if (!MDB2::isError($thatres)) {
                print MSG_PREFX."start importing from ".$table['thattablename']." to ". $table['thistablename']."\n";
                
                if (isset($table['defaults'])) {
                    foreach($table['defaults'] as $i => $values) {
                        $thisquery = importPrepareQuery($table['thistablename'], $values);
                        if ($thisquery) {
                            $thisinsert = $thisdb->query($thisquery);
                            
                        }
                    }
                }
                
                while($thatrow = $thatres->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                    $thisrow = importPrepareValues($table['fields'], $thatrow, $table['callbacks'], $thisdb);
                    if (is_array($thisrow)) {
                        $thisquery = importPrepareQuery($table['thistablename'], $thisrow);
                        if ($thisquery) {
                            $thisinsert = $thisdb->query($thisquery);
                            
                            if (MDB2::isError($thisinsert)) {
                                echo $thisinsert->getMessage()."\n";
                                echo $thisquery."\n\n";
                            }
                        }
                    }
                } 
            
            } else {
                echo MSG_PREFX.$thatres->getMessage()."\n";
            }
    
        }
    } 
}


function importPrepareValues($fields, $values, $callbacks=array(), &$db=null) {
     
    $thisresults = array();
    foreach($values as $field => $value) {
            
        // thiskeys: keys of thisresults to which value of values[field] applies
        $thiskeys = array_keys( $fields, $field, 0);
        foreach($thiskeys as $thiskey) { 
            
            if (is_array($callbacks[$thiskey])) {
                foreach($callbacks[$thiskey] as $c => $callback) {
                        
                    if (function_exists($callback)) {
                        $value = call_user_func($callback, $value, $values);
                    }
                }
            }    
            
            $thisresults[$thiskey]= addslashes(trim($value));
        
        }
    }
    
    return $thisresults;
}

function importPrepareQuery($table, $input) {
    
    $fields = sprintf("(%s)", implode(",", array_keys($input)) );
    $values = sprintf("('%s')", implode('\',\'', array_values($input)));
    
    return sprintf("INSERT INTO %s%s VALUES%s", $table, $fields, $values); 
    
    
}


function importDeleteTable($table, &$db) {
    print (sprintf("DELETE FROM %s", $table));
    if (!MDB2::isError($db->query(sprintf("DELETE FROM %s", $table)))) {
        if (!MDB2::isError($db->query(sprintf("ALTER TABLE %s auto_increment=0", $table)))) {
            return true;
        }
    }
    return false;
}


function tidy($input) {
    if (is_executable(TIDY_BIN)) {
        
        $tmpfile = sprintf("%s/%s", TMP_DIR, uniqid('BXB'));
        if (file_put_contents($tmpfile, $input) > 0) {
            $tidycmd = escapeshellcmd(TIDY_BIN." -wrap 0 --indent-spaces 0 --indent 1  --force-output y -q -asxhtml --numeric-entities yes --show-body-only y ".$tmpfile);
            $out = `$tidycmd`;
            unlink($tmpfile);
            return $out;
        }         
    }
    
    return $input;

}


function utf2entities($input) {
    return $input;
}

function plusOne($input = 0) {
print $input."\n";

return $input + 1;
}
function makeUri($title) {
    
    $title = trim($title);
    
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

function makePostUri($uri, $fields){
    if (trim($uri)) {
       return $uri;
    } else if (trim($fields['post_title']) && $fields['post_title'] != "..." ) {
	        return makeUri($fields['post_title']);
    } else {
		return 'p'.$fields['id'];
    }
}
        
function importTableTree($tablename, $idField, $refField, $fullPath, $fullTitlePath, $path, $title, $data, &$db ) {
    
    $tree = new SQL_Tree($db);
    $tree->idField = $idField;
    $tree->referenceField = $refField;
    $tree->tablename = $tablename;
    $tree->FullPath = $fullPath;
    $tree->FullTitlePath  = $fullTitlePath;
    $tree->Path = $path;
    $tree->Title = $title;
    $tree->fullnameSeparator = " :: ";
    $query = $tree->children_query(1,$data,True);
    $tree->importTree(1,true,"name");
    return null;
}

function importMoblogs ($moblogTable, $tables, &$thisdb, &$thatdb) {
    
    $query = "select image_posts_ID, image_name, image_alt from $moblogTable where  image_inline = 0";
    
    $res = $thatdb->query($query);
    
    while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
        $query = " update blogposts set post_content = concat(post_content, '\n<p><a href=\"/files/images/moblogs/";
        $query .= $row['image_name']."\"><img src=\"/dynimages/180/files/images/moblogs/".$row['image_name']."\"/></a><br/>\n";
        $query .= "</p>\n') where id = ".$row['image_posts_id'];
        print $query ."\n";
        $res2 = $thisdb->query($query);
        if (MDB2::isError($res2)) {
            
            echo $res2->getMessage()."\n";
            echo $res2."\n\n";
        }
        
    }
}


?>
