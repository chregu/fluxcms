<?php
// +----------------------------------------------------------------------+
// | Bitflux CMS                                                          |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001,2002,2003 Bitflux GmbH                            |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Christian Stocker <chregu@bitflux.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id$

class XML_xml2db {

    public $idField = "id";
    public $primaryField = Null;
    public $tagname = "tagname";
    public $_html_trans =False;
    public $sequencesTable = "_sequences";
    public $sequencesVersioningTable = "_versioning";
    function __construct ($dsn = Null,$tablePrefix= "") {
        $this->tablePrefix = $tablePrefix;
        if ($dsn )
        {
            $this->setDB($dsn);
        }

    }

    function setDB  ($dsn) {
        $this->db = $GLOBALS['POOL']->dbwrite;
    }
    function insert ($xml,$fieldInfo=array(),$debug=False)
    {
        return $this->insertObject(xmldoc($xml),$fieldInfo,$debug);
    }
    function insertObject ($xml,$fieldInfo=array(),$debug=False) {
        //                bx_editors_dbform_common::write_file($xml,"../../../tmp/debug/db.xml");

        $sqlVersioning = array();
        $root = $xml->documentElement;

        $first = False;
        if (!isset($GLOBALS["_html_trans"]))
        {
            _htmlTranslationInit();
        }

        foreach ($root->childNodes as $table) {
            //debug::print_rp($table);

            if ( $table->nodeType == XML_ELEMENT_NODE)
            {
                unset($this->fields );
                unset($tmp);
                unset($sql);
                $mode = "insert";
                if (@$table->getAttribute("delete"))
                {
                    $mode="delete";
                    $foreigntable2delete = array();

                    foreach ($table->childNodes as $_child)
                    {
                        if (isset($_child->localName  ) && $_child->localName  == "foreigntable")
                        {
                            $foreigntable2delete[] = array("tablename"=>$_child->getAttribute("tablename"), "fieldname"=>$_child->getAttribute("fieldname"));
                        }
                    }

                }
                if (@$table->getAttribute("donothing"))
                {
                    continue;
                }
                foreach ($table->childNodes as $field)
                {
                    
                    if (isset($field->localName) && strstr($field->localName,"_notindb")) {
                        unset($this->fields[$field->localName]);
                        continue;
                    }
                    //php-4.0.7 gets the content differently than 4.0.6 (the second one in the elseif)
                    //php 4.2 does it another way again, it has finally the method get_content
                    // but we want the whole xml source from the nodes, so we use dump_node...


                    $field_content="";
                    if (($field->childNodes)) {
                    foreach($field->childNodes as $child) {
                        $field_content .= $xml->saveXML($child);   
                    }
                    }
                    $field_content = bx_helpers_string::utf2entities(html_entity_decode($field_content,ENT_COMPAT,"UTF-8"));
                    if ( $field->nodeType == XML_ELEMENT_NODE)
                    {
                        if ($field->localName == $this->idField || $field->localName == $this->primaryField) {
                            if ($field->localName == $this->idField)
                            {
                                $query = "select $this->idField from ".$this->tablePrefix.$table->localName." where $this->idField  = '$field_content'";
                                $this->db->loadModule('extended');
                                $exists =  $this->db->extended->GetOne($query);
                            }
                            else
                            {
                                $query = "select $this->idField from ".$this->tablePrefix.$table->localName." where $this->primaryField  = '".$field_content."'";
                                $exists =  $this->db->GetOne($query);
                                if ($exists) {
                                    $this->fields[$this->idField] = $exists;
                                }
                            }

                            //ERROR DETECTION PLEASE
                            if ($exists && $mode != "delete")
                            {
                                $mode = "update";
                            }
                        }
                        $this->fields[$field->localName] = strtr($field_content,$GLOBALS['_html_trans']);
                        if (!ini_get("magic_quotes_gpc"))
                        {
                            $this->fields[$field->localName] = addslashes($this->fields[$field->localName]);
                        }

                    }
                }
                if ($mode == "insert")
                {
                    /* if there is no sequence table, there will be an error, which we can use to create the sequence */
                    $this->db->expectError(MDB2_ERROR_NOSUCHTABLE);
                    $insertedID = $this->db->nextId($this->tablePrefix.$this->sequencesTable);

                    if (MDB2::isError($insertedID))
                    {
                        print ($insertedID->message);
                        print "<br/>";
                        print $insertedID->userinfo;
                        print "<p/>";
                        /* if there is no sequence table, we assume that we still use the old ID-Format, so let's change
                           it now :)
                        	CAUTION: Only Article, Imageobject and Mediaobject "Object-Tables" are updated, you have
                        	          to add more, if you have more...
                        */
                        include_once("bitlib/xml/update2sequences.php");
                        die("Table Sequences Update, new Entry not inserted, please to it again. (This has only to be done once...)");
                    }
                    $this->db->popExpect();
                    $this->fields[$this->idField] = $insertedID;

                    if (!$first) {$first = True;}
                    $_fieldKeys = implode (", ",array_keys($this->fields));
                    $_fieldValues = preg_replace("/'sql:([^']+)'/","$1",implode("', '", $this->fields));
                    $_sql = " (".$_fieldKeys.") values ('".$_fieldValues."')";

                    $sql[] = "insert into ".$this->tablePrefix.$table->localName. $_sql;
                    //$sql[] = "insert into Sequences2Table (Sequence, Tablename) values ($insertedID,'".$table->localName. "')";
                    /* for versioning */
                    if (isset($GLOBALS["BX_config"]["versioning"]["do"]) && $GLOBALS["BX_config"]["versioning"]["do"] )
                    {
                        $sqlVersioning[] = $insertedID;
                    }


                }
                elseif ($mode == "update")
                {
                    $_sql = "";

                    foreach ($this->fields as $field => $value)

                    {

                        if ($this->idField != $field)
                        {
                            if (preg_match("/^sql:(.*)/",$value,$match))
                            {
                                $_sql .= "$field = ".$match[1].", ";
                            }
                            else
                            {
                                $_sql .= "$field = '$value', ";
                            }
                        }

                    }
                    $_sql = preg_replace   ("/, $/","",$_sql);
                    $sql[] = "update ".$this->tablePrefix.$table->localName." set ".$_sql." where $this->idField = '".$this->fields[$this->idField]."'";
                    if (isset($GLOBALS["BX_config"]["versioning"]["do"]) && $GLOBALS["BX_config"]["versioning"]["do"] )
                    {
                        $sqlVersioning[] = $this->fields[$this->idField];
                    }
                    

                }
                elseif ($mode == "delete")
                {
                    $sql[] = "delete from ".$this->tablePrefix.$table->localName." where $this->idField = '".$this->fields[$this->idField]."'";
                    $sqlVersioning[] = $this->fields[$this->idField];
                    foreach ($foreigntable2delete as $_foreigntable)
                    {
                        $sql[] = "delete from ".$this->tablePrefix.$_foreigntable["tablename"]. " where ". $_foreigntable["fieldname"] ." = ".  $this->fields[$this->idField];
                    }
                    
                }
                if ($debug)
                {
                    print "$sql;<br>";
                    // phpinfo();
                }
                else
                {
                    if (!(is_array($sql)))
                    {
                        $sql[] = $sql;
                    }
                    
                    foreach ($sql as $query)
                    {
                        $res = $this->db->query($query);
                        if (MDB2::isError($res)) {
                            print "The given query was not valid in file ".__FILE__." at line ".__LINE__."<br>\n";
                            print $res->userinfo."<br>";
                            return $this->db->raiseError($res->code,PEAR_ERROR_DIE);
                        }
                    }
                 

                    if ($mode == "insert")
                    {
                        /*	                    if (!$first)
                            	                {
                                	                $first = True;
                                    	            $insertedID = mysql_insert_id($this->db->connection);
                                        	        $this->fields[$this->idField] = $insertedID;
                                            	}
                        */

                        if (@$table->getAttribute("afterinsertupdate"))
                        {

                            if (! @$table->getAttribute("afterinsertupdateifempty")  or  ( @$table->getAttribute("afterinsertupdateifempty") and !(preg_replace("/{this:([a-zA-Z0-9_]*)}/e","\$this->fields[\"$1\"]",$table->getAttribute("afterinsertupdateifempty")))))
                            {
                                $setupdate = preg_replace("/\{this:([a-zA-Z0-9_]*)\}/e","\$this->fields[\"$1\"]",$table->getAttribute("afterinsertupdate"));
                                $query = "update ".$this->tablePrefix.$table->localName ." set ".$setupdate ." where ".$this->idField." =  $insertedID";;
                                $res = $this->db->query($query);
                                if (MDB2::isError($res)) {
                                    print "The given query was not valid in file ".__FILE__." at line ".__LINE__."<br>\n";
                                    print $res->userinfo."<br>";
                                    return new DB_Error($res->code,PEAR_ERROR_DIE);
                                }
                            }
                        }

                    }
                    /* delete caching entries in cacheinfo and the appropriate cache data*/
                    if ($mode == 'delete'  || $table->localName == "Section"  || isset($GLOBALS["BX_config"]["caching"]["do"]) && $GLOBALS["BX_config"]["caching"]["do"] )

                    {
                        if (isset($GLOBALS["BX_config"]["popoon"]["cacheContainer"])) {
                        require_once("Cache/Output.php");
                        
                        $cache = new Cache_Output($GLOBALS["BX_config"]["popoon"]["cacheContainer"], $GLOBALS["BX_config"]["popoon"]["cacheParams"] );
                        // for the time being, just flush everything...
                        @$cache->flush('outputcache');
                        if ($mode == "delete" || $table->localName == "Section" ) {
                            @$cache->flush('st2xml_data');
                            @$cache->flush('st2xml_queries');
                        }
                        }

                    }

                }



            }
        }
        if (isset($insertedID))
        {
            $this->fields[$this->idField] = $insertedID;
        }
        else
        {
            $insertedID = 0;
        }
        return $insertedID;
    }



}

function _htmlTranslationInit()
{

    $GLOBALS['_html_trans']['&amp;'] = '&';
    $GLOBALS['_html_trans']['&quot;'] = '"';
    $GLOBALS['_html_trans']['&lt;'] = '<';
    $GLOBALS['_html_trans']['&gt;'] = '>';

    //for some strange reasons, mozilla sends an 128 for the euro symbol, change that to unicode
    $GLOBALS['_html_trans']['&#128;'] = '&#8364;';
    //replace non-bounding space with a normal space. what if we want  nbsp? no idea now..
    //    $GLOBALS['_html_trans']['&#160;'] = ' ';
    $GLOBALS['_html_trans'][chr(0)] = '';
}

?>
