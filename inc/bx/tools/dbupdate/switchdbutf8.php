<?php

chdir(dirname(__FILE__));

include_once("../../../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../../../..");

$db = $GLOBALS['POOL']->db;
bx_helpers_debug::webdump($db);
foreach ($db->queryCol("show tables ") as $tbl) {
    
    $query = "ALTER TABLE `".$tbl."` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
    $db->query($query);
    $query ="ALTER TABLE `".$tbl. "`";
    $hasFields = false;
    foreach ($db->queryAll(" show full columns from  $tbl;",null,MDB2_FETCHMODE_ASSOC) as $field) {
        if ($field['collation'] != "NULL") {
            $query .= " CHANGE `".$field['field']."` `".$field['field']."` ".$field['type']." CHARACTER SET utf8 COLLATE utf8_general_ci";
            if ($field['null'] == 'YES') {
                $query .= " NULL ";
            } else {
                $query .= " NOT NULL ";
            }
            if ($field['default'] === null) {
                $query .= " default NULL";   
            } else  {
                $query .= " default '" . $field['default']."'";
                
            }
            $query .= ",";
            
            $hasFields = true;
            
        }
        
        
        
    }
    
    
    if ($hasFields) {
        $query = substr($query,0,-1);
        print "$query\n";
        $res = $db->query($query); 
        
        if ($db->isError($res)) {
            print $res->getMessage();
            print $res->getUserInfo();
            die();
        }
    }
    
    
}