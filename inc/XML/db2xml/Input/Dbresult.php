<?php
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997, 1998, 1999, 2000, 2001 The PHP Group             |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Christian Stocker <chregu@phant.ch>                         |
// +----------------------------------------------------------------------+
//
// $Id$

function addTestBefore_Dbresult ($resultset) {
    if (is_object($resultset)) {
        return is_subclass_of($resultset, 'MDB2_Result');
    } else {
        return false;
    }
}


Class XML_db2xml_Input_Dbresult {


    function XML_db2xml_Input_Dbresult (&$parent)
    {
        $this->parent = &$parent;
    }

    /**
    * Adds an additional pear::db_result resultset to $this->xmldoc
    *
    * @param    Object db_result result from a DB-query
    * @see      doSql2Xml()
    * @access   public
    */
    function add($result)
    {
        $this->doSql2Xml($result);
    }


    /**
    * For adding db_result-'trees' to $this->xmldoc
    * @param    Object db_result
    * @access   private
    * @see      addResult(),addSql()
    */
    function doSql2Xml($result)
    {

        if (MDB2::isError($result)) {
            throw new PopoonDBException($result);
            //new MDB2_Error($result->code,PEAR_ERROR_DIE);
        }
        // the method_exists is here, cause tableInfo is only in the cvs at the moment
        // BE CAREFUL: if you have fields with the same name in different tables, you will get errors
        // later, since MDB2_FETCHMODE_ASSOC doesn't differentiate that stuff.
        $this->LastResult = &$result;
        $result->db->loadModule('Reverse');
  
        if ( ! ($tableInfo = $result->db->reverse->tableInfo($result, False)))
        {
            //emulate tableInfo. this can go away, if every db supports tableInfo
            $fetchmode = MDB2_FETCHMODE_ASSOC;
            $res = $result->FetchRow($fetchmode);
            $this->parent->nested = False;
            $i = 0;

            foreach ($res as $key => $val)
            {
                $tableInfo[$i]['table']= $this->parent->tagNameResult;
                $tableInfo[$i]['name'] = $key;
                $resFirstRow[$i] = $val;
                $i++;
            }
            $res  = $resFirstRow;
            $FirstFetchDone = True;
            $fetchmode = MDB2_FETCHMODE_ORDERED;
        }
        else
        {
            
            $FirstFetchDone = False;
            $fetchmode = MDB2_FETCHMODE_ORDERED;
        }

        // initialize db hierarchy...
        $parenttable = 'root';
        $tableInfo['parent_key']['root'] = 0;

        foreach ($tableInfo as $key => $value)
        {
            if (is_int($key))
            {
                // if the sql-query had a function the table starts with a # (only in mysql i think....), then give the field the name of the table before...
                if (preg_match ('/^#/',$value['table']) || strlen($value['table']) == 0) {
                     $value['table'] = $tableInfo[($key - 1)]['table'] ;
                    $tableInfo[$key]['table'] = $value['table'];
                }


                if (!isset($tableInfo['parent_table']) || !isset($tableInfo['parent_table'][$value['table']]) || is_null($tableInfo['parent_table'][$value['table']]))
                {
                    $tableInfo['parent_key'][$value['table']] = $key;
                    $tableInfo['parent_table'][$value['table']] = $parenttable;
                    $parenttable = $value['table'] ;
                }

            }
            //if you need more tableInfo for later use you can write a function addTableInfo..
            $this->parent->Format->addTableInfo($key, $value, $tableInfo);
        }

        // end initialize

        // if user made some own tableInfo data, merge them here.
        if ($this->parent->user_tableInfo)
        {
            $tableInfo = $this->parent->array_merge_clobber($tableInfo,$this->parent->user_tableInfo);
        }

        $parent['root'] = $this->parent->Format->insertNewResult($tableInfo);

        //initialize $resold to get rid of warning messages;
        $resold[0] = 'ThisValueIsImpossibleForTheFirstFieldInTheFirstRow';

        while ($FirstFetchDone == True || $res = $result->FetchRow($fetchmode))
        {

            //FirstFetchDone is only for emulating tableInfo, as long as not all dbs support tableInfo. can go away later
            $FirstFetchDone = False;

            foreach ($res as $key => $val) 
            {
                if ($resold[$tableInfo['parent_key'][$tableInfo[$key]['table']]] != $res[$tableInfo['parent_key'][$tableInfo[$key]['table']]] || !$this->parent->nested)
                {
                    if ($tableInfo['parent_key'][$tableInfo[$key]['table']] == $key )
                    {
                        if ($this->parent->nested || $key == 0)
                        {
                            $parent[$tableInfo[$key]['table']] =  $this->parent->Format->insertNewRow($parent[strtolower($tableInfo['parent_table'][$tableInfo[$key]['table']])], $res, $key, $tableInfo);
                        }
                        else
                        {
                            
                            $parent[$tableInfo[$key]['table']]= $parent[strtolower($tableInfo['parent_table'][$tableInfo[$key]['table']])];
                        }

                        //set all children entries to somethin stupid
                        foreach($tableInfo['parent_table'] as $pkey => $pvalue)
                        {
                            if ($pvalue == $tableInfo[$key]['table'])
                            {
                                $resold[$tableInfo['parent_key'][$pkey]]= 'ThisIsJustAPlaceHolder';
                            }
                        }

                    }
                    if ( $parent[$tableInfo[$key]['table']] !== Null)
                    {
                        $this->parent->Format->insertNewElement($parent[$tableInfo[$key]['table']], $res, $key, $tableInfo, $subrow);
                    }

                }
            }

            $resold = $res;
            unset ($subrow);
        }

    }
}
