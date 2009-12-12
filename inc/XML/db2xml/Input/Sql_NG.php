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
include_once("XML/db2xml/Input/Dbresult_NG.php");

function addTestBefore_Sql_NG($resultset)  {
    return (is_string($resultset) &&  preg_match("/.*select.*from.*/i" ,  $resultset));
}


class XML_db2xml_Input_Sql_NG extends XML_db2xml_Input_Dbresult_NG {


    function XML_db2xml_Input_Sql_NG (&$parent)
    {
        $this->parent = &$parent;
    }


    /**
    * Adds an aditional resultset generated from an sql-statement
    *  to $this->xmldoc
    *
    * @param    string sql a string containing an sql-statement.
    * @access   public
    * @see      doSql2Xml()
    */

    function add($sql,$dsn = Null)
    {

        if (! is_null($dsn))
        {
            // if the dsn is different to the parent one, we maybe want a new db-connection...
            if ($this->parent->dsn != $dsn)
            {
                unset($this->parent->db);
            }
            $this->parent->dsn = $dsn;
        }
		//ZE2 compa
        if (!(get_parent_class($this->parent->db) == "db_common" || get_parent_class(get_parent_class($this->parent->db)) == "db_common"))
        {
            // if it's a string, then it must be a dsn-identifier;
            if (is_string($this->parent->dsn))
            {
                include_once ("DB.php");
                $this->parent->db = DB::Connect($this->parent->dsn);
                if (DB::isError($this->parent->db))
                {
                    print "The given dsn for XML_db2xml was not valid in file ".__FILE__." at line ".__LINE__."<br>\n";
                    return new DB_Error($this->parent->db->code,PEAR_ERROR_DIE);
                }

            }

            elseif (is_object($this->parent->dsn) && DB::isError($this->parent->dsn))
            {
                print "The given param for XML_db2xml was not valid in file ".__FILE__." at line ".__LINE__."<br>\n";
                return new DB_Error($dsn->code,PEAR_ERROR_DIE);
            }

            // if parent class is db_common, then it's already a connected identifier
            elseif (get_parent_class($this->parent->dsn) == "db_common" || get_parent_class(get_parent_class($this->parent->dsn)) == "db_common")
            {
            	
                $this->parent->db = $this->parent->dsn;
            }
        }




        /* if there are {} expressions in the sql query, we assume it's an xpath expression to
        *   be evaluated.
        */

        if (preg_match_all ("/\{([^\}]+)\}/i",$sql,$matches))
//        if (preg_match_all ("/\{(.+)\}/i",$sql,$matches))        
        {

           foreach ($matches[1] as $match)
            {
               
                $sql = preg_replace("#\{".preg_quote($match)."\}#  ", $this->parent->getXpathValue($match),$sql);
            }
        }

        $result = $this->parent->db->query($sql);

        //very strange
        if (PEAR::isError($result->result)) {
            print "You have an SQL-Error:<br>".$result->result->userinfo;
            print "<br>";
            new DB_Error($result->result->code,PEAR_ERROR_DIE);
        }

        $this->doSql2Xml($result);
    }
}
