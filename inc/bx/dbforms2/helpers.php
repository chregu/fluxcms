<?php
// +----------------------------------------------------------------------+
// | BxCMS                                                                |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <flux@bitflux.ch>                               |
// +----------------------------------------------------------------------+
//
// $Id: field.php 4831 2005-06-30 14:41:20Z philipp $

/**
 * DOCUMENT_ME
 *
 * @package bx_dbforms2
 * @category 
 * @author Bitflux GmbH <flux@bitflux.ch>
 */
class bx_dbforms2_helpers {
    
   /**
    *  DOCUMENT_ME
    *
    *  @param  type  $var descr
    *  @access public
    *  @return type descr
    */
   static public function updateCategoriesTree($form) {
       $tablePrefix = $form->tablePrefix;
       
       $tableName = $tablePrefix.$form->__get('tableName');
       
       $tree = new SQL_Tree($GLOBALS['POOL']->db);
       $tree->idField = "id";
       $tree->referenceField = "parentid";
       $tree->tablename = $tableName;
       $tree->FullPath = "fulluri";
       $tree->FullTitlePath  = "fullname";
       $tree->Path = "uri";
       $tree->Title = "name";
       $tree->fullnameSeparator = " :: ";
       $data = array("name","uri","fulluri");
       
       $rootQuery = "select id from ".$tableName." where parentid = 0";
       
       $rootid = $GLOBALS['POOL']->db->queryOne($rootQuery);
       if (!$rootid) {
           print '<font color="red">You don\'t have a root collection, please define one</font><br/>
           Otherwise the category output will not be correct<br/><br/>';
       } else {
           $tree->importTree($rootid,true,"name");
       }
       
   }
}