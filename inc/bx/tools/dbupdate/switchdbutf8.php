<?php

chdir(dirname(__FILE__));

//include_once("../../../../inc/bx/init.php");
//bx_init::start('conf/config.xml', "../../../..");

$db = $GLOBALS['POOL']->db;


$db->query("ALTER DATABASE `".$db->database_name."` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");

$prefix = $GLOBALS['POOL']->config->getTablePrefix();

foreach ($db->queryCol("show tables like '$prefix%'") as $tbl) {
    
    $query = "ALTER TABLE `".$tbl."` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
    $db->query($query);
    $query ="ALTER TABLE `".$tbl. "`";
    $hasFields = false;
    foreach ($db->queryAll(" show full columns from  $tbl;",null,MDB2_FETCHMODE_ASSOC) as $field) {
        if ($field['collation'] != "NULL" && $field['collation'] != NULL) {
            $query .= " CHANGE `".$field['field']."` `".$field['field']."` ".$field['type']." CHARACTER SET utf8 COLLATE utf8_general_ci";
            if ($field['null'] == 'YES') {
                $query .= " NULL ";
            } else {
                $query .= " NOT NULL ";
            }
            if ($field['default'] === null && $field['null'] == 'YES') {
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