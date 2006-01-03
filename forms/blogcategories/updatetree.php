<?php



  $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();

$tree = new SQL_Tree($GLOBALS['POOL']->db);
$tree->idField = "id";
$tree->referenceField = "parentid";
$tree->tablename = $tablePrefix."blogcategories";
$tree->FullPath = "fulluri";
$tree->FullTitlePath  = "fullname";
$tree->Path = "uri";
$tree->Title = "name";
$tree->fullnameSeparator = " :: ";
$data = array("name","uri","fulluri");

$rootQuery = "select id from ".$tablePrefix."blogcategories where parentid = 0";

$rootid = $GLOBALS['POOL']->db->queryOne($rootQuery);
if (!$rootid) {
    print '<font color="red">You don\'t have a root collection, please define one</font><br/>
            Otherwise the category output will not be correct<br/><br/>';
} else {
    $tree->importTree($rootid,true,"name");
}
