<?php
// +----------------------------------------------------------------------+
// | Bitflux CMS                                                          |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Christian Stocker <chregu@liip.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id$

//require_once("bitlib/functions/common.php");
/**
* This are some methods for the bx admin page.n
*
*  I'm not sure, if this really makes sense at all. but it makes it
*   it certainly more clean... or at least should
*
* @author   Christian Stocker <chregu@liip.ch>
* @version  $Id$
* @package  admin
*
*/

class bx_editors_dbform_manager {
    
    /**
    * Master ID field
    *
    * A field named like this should be in every table.
    * It has to be a unique key and preferably Primary.
    *
    * I'm not sure about case-sensitivity. sometimes it's
    *  an issue an sometimes not. didn't check that thoroughly
    *
    * @var      string
    * @access   public
    */
    
    public $idField = "id";
    
    /**
    * Root Tagname of the config files like config.xml and global.xml
    *
    * Not much to say about that. config is a reasonable default..
    *
    * @var      string
    * @access   public
    */
    
    public $configRoot = "config";
    
    /**
    * @var      Object Config
    * @access   private
    */
    
    public $config;
    
    /**
    * @var      array strings
    * @access   private
    */
    public $masterValues;
    
    /**
    * @var      array strings
    * @access   private
    */
    public $fields;
    
    /**
    * @var      object pear::DB
    * @access   private
    */
    public $db;
    
    /**
    * @var      array strings
    * @access   private
    */
    public $http_vars;
    
    /**
    * @var      object domDocument
    * @access   private
    */
    public $xml;
    
    /**
    * @var      object xml_xml2db
    * @access   private
    */
    public $xml2db;
    
    public $Seq_Table = "sequences";
    /**
    * Constructor
    *
    *  Does nothing yet..
    *
    */
    
    function __construct() {
        
    } //end func admin_common;
    
    function setTablePrefix($prefix) {
        $this->tablePrefix = $prefix;
    }
    function setConfigFile ($file)
    {
       
        //check if the data was posted with POST or GET
        /* maybe one could merge both values as well
        * but i'm not sure if we need that.
        */
        if ($_POST)
        {
            $this->http_vars = &$_POST;
        }
        elseif ($_GET)
        {
            $this->http_vars = &$_GET;
        }
        
        if ($_FILES)
        {
            $this->files_vars = &$_FILES;
        }
        $this->config = bx_helpers_db::getConfigClass ($file);
    } //end func setConfigFile
    
    function checkSensitive() {
        if (!isset($_GET['new']) && isset($this->masterValues['issensitive']) && $this->masterValues['issensitive'] == 'true' ) {
            if (isset($_POST['_issensitive_password'])) {
                $pass = $_POST['_issensitive_password'];
            } else {
                $pass ="";
            }
            
            $perm = bx_permm::getInstance();
            if (!$perm->checkPassword($pass)) {
                print "<font color='red'>Your password did not match, no changes were made. Please provide the right one.</font><p/>";
                return false;
            }
        }
        return true;
    }
    
    function getTableInfo ($path = Null )
    {
        
        if (!$path) { $path = $this->configRoot."/fields";}
        
        $this->masterValues = $this->config->getValues( $path);
        
        if (!(isset($this->masterValues["uploaddir"])))
        {
            if (isset($this->masterValues["downloaddir"])) {
                $this->masterValues["uploaddir"] = BX_PROJECT_DIR.$this->masterValues["downloaddir"];
            }
        }
        
        if (isset($this->masterValues['idfield'])) {
            $this->idField = $this->masterValues['idfield'];
        }
        
        return $this->masterValues;
    } //end func getMasterValues
    
    function getQueryString($path = Null )
    {
        if (!$path) { $path = $this->configRoot."/querystring";}
        $this->querystring = $this->config->getValues( $path);
        return $this->querysting;
    } //end func getMasterValues
    
    
    function getFieldsInfo ($path = Null,$masterValues = NULL)
    {
        if (! $path        ) { $path = $this->configRoot."/fields";}
        
        if (! $masterValues) { $masterValues  = $this->masterValues;}
        foreach ($masterValues["children"] as $child) {
            $childValues = $this->config->getValues( "$path/$child" ) ;
            if (PEAR::isError($childValues)) {
                continue;   
            }
            //set default to text
            
            if (!isset($childValues["type"]))
            {
                $childValues["type"] = "text";
            }
            if ("foreign" == $childValues["type"] || "12m" == $childValues["type"])
            {
                $foreignValues = $this->config->getValues( "$path/$child/foreign" ) ;
                if (isset($foreignValues['orderby']))
                {
                    $orderby = "order by ".$foreignValues['orderby'];
                }
                else
                {
                    $orderby = "";
                }
                if (isset($foreignValues['where']))
                {
                    $where = "where ".$foreignValues['where'];
                }
                else
                {
                    $where = "";
                }
                
                if (isset($foreignValues['leftjoin']))
                {
                    $leftjoin = "left join  ".$this->replacePrefix($foreignValues['leftjoin']);
                }
                else
                {
                    $leftjoin = "";
                }
                if (isset($foreignValues['idfield'])) {
			$idfield =$foreignValues['idfield'];
		} else {
			$idfield = $this->idField;
		}
                
                $AdditQuery = "select ".$foreignValues['table'] .".$idfield, ".$foreignValues['field']." as ChooserField from ".$this->tablePrefix.$foreignValues['table'] ." as " . $foreignValues['table']. " $leftjoin $where $orderby";
                if (!(isset($fields['additionalSql']) && is_array($fields['additionalSql']) && in_array($AdditQuery,$fields['additionalSql'])))
                {
                    $fields['additionalSql'][] = $AdditQuery;
                }
                
                unset ($orderby);
                unset ($where);
                unset ($leftjoin);
            }
            
            elseif ("n2m" == $childValues["type"] )
            {
                
                $foreignValues = $this->config->getValues( "$path/$child/foreign" ) ;
                $N2MValues = $this->config->getValues( "$path/$child/n2m" ) ;
                $N2MValues["tablename"] = $child;
                
                if (isset($N2MValues['thisidfield']))
                {
                    $thisidfield = $N2MValues['thisidfield'];
                }
                else
                {
                    $thisidfield = $this->idField ;
                }
                
                foreach ($foreignValues["children"] as $foreignChild)
                {
                    
                    $foreignChildValues = $this->config->getValues( "$path/$child/foreign/$foreignChild" ) ;
                    if (isset($foreignChildValues['orderby']))
                    {
                        $orderby = "order by ".$foreignChildValues['orderby'];
                    }
                    else
                    {
                        $orderby ="";
                    }
                    if (isset($foreignChildValues['leftjoin']))
                    {
                        $leftjoin = "left join ".$this->tablePrefix.$foreignChildValues['leftjoin'] ." as ".$foreignChildValues['leftjoin'];
                    }
                    else
                    {
                        $leftjoin  = "";
                    }
                    if (isset($foreignChildValues['idfield']))
                    {
                        $thatidfield = $foreignChildValues['idfield'];
                    }
                    else
                    {
                        $thatidfield = $this->idField ;
                    }
                    if (isset($foreignChildValues["where"]))
                    {
                        $where = "where ".$foreignChildValues["where"];
                    }
                    else
                    {
                        $where ="";
                    }
                    
                    $fields['additionalSql'][] = "select $foreignChild.$thatidfield, ".$foreignChildValues['field']." as ChooserField from ".$this->tablePrefix."$foreignChild as $foreignChild $leftjoin $where $orderby";
                    $N2MValues["children"][] = $foreignChild;
                }
                //left joins we need in the master sql
                $fields["n2mLeftJoins"][$child] = " left join ".$this->tablePrefix."$child as $child on $child.".$N2MValues['thisfield']." = Master.".$thisidfield."  ";
                // add the n2m-id to the list of fields which go into the master sql, type = none means, no equivalent field in the db
                
                $fields["fields"][$child.".".$thatidfield]["type"]="none";
                if (isset($N2MValues['objectfield']))
                {
                    $fields["fields"][$child.".".$N2MValues['objectfield'].""]["type"]="none";
                }
                //the tablename is needed for later updates.
                
                // add the tablename in front of thatfield 'cause otherwise we maybe have conflicts
                $child = $child.".".$N2MValues['thatfield'];
                
                // add the values of the n2m tag to the $fields array for later use
                $fields["fields"][$child]["n2m"] = $N2MValues;
                
            }
            elseif ("active" == $childValues["type"] )
            {
                
                if (isset($childValues["children"]))
                {
                    $fromValues = $this->config->getValues( "$path/$child/from" ) ;
                    $tillValues = $this->config->getValues( "$path/$child/till" ) ;
                    if (isset($tillValues["field"]))
                    {
                        $fields["fields"][$tillValues["field"]]["type"] = "datetime";
                    }
                    if (isset($fromValues["field"]))
                    {
                        $fields["fields"][$fromValues["field"]]["type"] = "datetime";
                    }
                    
                }
            }
            
            $fields["fields"][$child]["type"] = $childValues["type"];
            
            if (isset($childValues["subtype"]) && $childValues["subtype"]) {
                $fields["fields"][$child]["subtype"] = $childValues["subtype"];
            }
            if (isset($childValues["info"]) && $childValues["info"]) {
                $fields["fields"][$child]["info"] = $childValues["info"];
            }
            if ("notindb" == $childValues["type"] )
            {
                unset($fields["fields"][$child]);
                
            }
            
        }
        foreach (array_keys($fields["fields"]) as $fieldname)
        {
            
            if (strstr($fieldname,"_notindb")) {
                continue;
            }
            if (!strstr($fieldname,"."))
            {
                $fieldnames[] = "Master.".$fieldname;
            }
            else
            {
                $fieldnames[] = $fieldname;
            }
        }
        
        
        $fields["sqlFields"] = implode (", ",$fieldnames);
        $this->fields = $fields;
        
        return $fields;
    } //end func  getFields
    
    function getDsn ($path = Null )
    {
        return ($GLOBALS["POOL"]->db);
        
    } //end func getD
    
    
    function setDB  ($dsn) {
        $this->db = $GLOBALS['POOL']->db;
    } //end func setDB
    
    function insertUpdateFields()
    {
        
        if (isset($this->http_vars['update']) && !isset($this->http_vars["new"]) && $this->checkSensitive() )
        {
            //if new entry, then IDtemp has a value
            /**
            * here comes the before update/insert stuff.
            */
            foreach ($this->fields["fields"] as $name => $fieldarray)
            {
                //rename uploaded filenames to avoid umlaut troubles
                if ($fieldarray["type"] == "file" && isset($this->files_vars[$name]) && $this->files_vars[$name]["tmp_name"][0]) {
                    $this->files_vars[$name]["name"][0] = str_replace("%","$",urlencode($this->files_vars[$name]["name"][0]));
                }
                //if subtype = image then check for width, size, and type and override the stuff from the fields...
                if ($fieldarray["type"] == "file" && $fieldarray['subtype'] == "image" )
                {
                    
                    $imageValues = $this->config->getValues( $this->configRoot."/fields/$name/imagefields" ) ;
                    if ( !(PEAR::isError($imageValues)) && isset($this->files_vars[$name]) &&  $this->files_vars[$name]["tmp_name"][0] && !($this->files_vars[$name]["tmp_name"][0] == "none" || $this->files_vars[$name]["name"][0] == "none"))
                    {
                        $ImgInfo = @getImageSize($this->files_vars[$name]["tmp_name"][0]);
                        if ($imageValues['width'])
                        {
                            $this->http_vars[$imageValues['width']] = $ImgInfo[0];
                        }
                        if ($imageValues['height'])
                        {
                            $this->http_vars[$imageValues['height']] = $ImgInfo[1];
                        }
                            switch ($ImgInfo[2]) {
                                case 1:
                                $_extCorrect = "gif";
                                break;
                                case 2:
                                $_extCorrect = "jpeg";
                                break;
                                case 3:
                                $_extCorrect = "png";
                                break;
                                case 4:
                                $_extCorrect = "swf";
                                break;
                                default:
                                $_extCorrect = "0";
                                break;
                            }
                        $_extPosition = strrpos($this->files_vars[$name]["name"][0],".");
                        $_extName = substr($this->files_vars[$name]["name"][0],0,$_extPosition);
                        $_ext = substr( $this->files_vars[$name]["name"][0], $_extPosition + 1);
                        if ($_extCorrect != "0" && !($_extCorrect == "jpeg"  && $_ext == "jpg") && $_extCorrect != $_ext ) {
                             $this->files_vars[$name]["name"][0] = $this->files_vars[$name]["name"][0] .'.'.$_extCorrect;
                             $_ext = $_extCorrect;
                        }
                        if ($imageValues['format'])
                        {
                            $this->http_vars[$imageValues['format']] = $_extCorrect;
                        }
                    }
                }
                if ($fieldarray["type"] == "checkbox" )
                {
                    if (isset($this->http_vars[$name]) && ($this->http_vars[$name] == "on" ||  $this->http_vars[$name] == 1))
                    {
                        $this->http_vars[$name] = 1;
                    }
                    else
                    {
                        $this->http_vars[$name] = 0;
                    }
                }
                elseif (! (isset ($this->http_vars[$name]))  && !(isset($this->files_vars[$name]) && is_array($this->files_vars[$name])))
                {
                    //donothing
                }
                elseif ($fieldarray["type"] == "file" && isset($fieldarray["info"]) && $fieldarray["info"] == "fileinfo" )
                {
                    if (($this->files_vars[$name]["tmp_name"][0] && $this->files_vars[$name]["tmp_name"][0] != none))
                    {
                        
                        $infoValues = $this->config->getValues( $this->configRoot."/fields/$name/fileinfofields" ) ;
                        if (isset($infoValues["size"]))
                        {
                            $this->http_vars[$infoValues["size"]] = filesize($this->files_vars[$name]["tmp_name"][0]);
                        }
                        if (isset($infoValues["mimetype"]))
                        {
                            $tmp =  $this->files_vars[$name]["tmp_name"][0];
                            $this->http_vars[$infoValues["mimetype"]] = `file -zbi $tmp`;
                        }
                    }
                }
                
                elseif ($fieldarray["type"] == "date" || $fieldarray["type"] == "datetime")
                {
                    $this->http_vars[$name] = preg_replace("/([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{2,4})/","$3-$2-$1",$this->http_vars[$name]);
                }

                elseif ($fieldarray["type"] == "password")
                {
                    if (strlen($this->http_vars[$name])  == 0) {
                      unset( $this->http_vars[$name]); 
                    }
                    else if ( !(preg_match("/^[0-9a-f]{32}$/",$this->http_vars[$name]) )) {
                        if (isset($fieldarray['info'])) {
                            $salt = $fieldarray['info'];
                        } else {
                            $salt = "";
                        }
                        $this->http_vars[$name] = md5($salt.$this->http_vars[$name]);
                    }
                }
                
                elseif ($fieldarray["type"] == "md5" && !(preg_match("/^[0-9a-f]{32}$/",$this->http_vars[$name]) ))
                {
                    $this->http_vars[$name] = md5($this->http_vars[$name]);
                }
                
                elseif ($fieldarray["type"] == "rang" )
                {
                   
                    $chooserValues = $this->config->getValues( $this->configRoot."/chooser");
                    $rang = $chooserValues["rang"];
                    $fields = $this->config->getValues( $this->configRoot."/fields");
                    $table = $fields['table'];
                    $this->db->loadModule("extended");
                    $res = $this->db->extended->getOne("select $rang from ".$this->tablePrefix."$table where $rang = ".($this->http_vars[$name] ). " and ". $this->idField . " != " .$this->http_vars[$this->idField]);
                    if ($res || $this->http_vars[$name] == 0) {
                        if ($this->http_vars[$this->idField]) {
                            $moveby = 1;
                            $add = " and ". $this->idField . " != " .$this->http_vars[$this->idField];
                        } else {
                            $moveby = 1 ;
                            $add = "";
                        }
                        
                       $GLOBALS['POOL']->dbwrite->query("update ".$this->tablePrefix."$table set $rang = $rang + $moveby where $rang > ". $this->http_vars[$name]. " $add");
                       $this->http_vars[$name]++;
                    }
                }

            }
            
            if (isset($this->http_vars["xmlcheck"]))
            {
                $DoWhat = "XmlCheck";
                $this->XmlCheck();

            }
            else {
            $DoWhat = "insert";
            }
            if ($IDtemp = $this->Query2Db($DoWhat))
            {
                //check if there's a new entry in the db, we need that for later (create table)
                if ($IDtemp != $this->http_vars[$this->idField])
                {
                    $newEntry = True;
                    $this->http_vars[$this->idField] = $IDtemp;
                }
                

            }
            /**
            * here comes the after update/after isnert stuff.
            */
            //move files if there were any...
            foreach ($this->fields["fields"] as $name => $fieldarray)
            {
                if ("file" == $fieldarray["type"])
                {
                    
                    
                    if ((isset($this->files_vars[$name]) && $this->files_vars[$name]["name"][0] == "none")  ||  (isset($this->http_vars[$name]) &&  $this->http_vars[$name][0] == "none") || (isset($this->files_vars[$name]) && $this->files_vars[$name]["tmp_name"][0] && $this->files_vars[$name]["tmp_name"][0] != 'none'))
                    {
                        //if old file was there, delete that first
                        $_old = $name."_old";
                        if ($this->http_vars[$_old]) {
                            @unlink($this->masterValues["uploaddir"].  $this->http_vars[$this->idField].".".$this->http_vars[$_old]);
                        }
                        if ($this->files_vars[$name]["name"][0] != "none")
                        {
                            if (! @move_uploaded_file($this->files_vars[$name]["tmp_name"][0],$this->masterValues["uploaddir"].  $this->http_vars[$this->idField].".".$this->files_vars[$name]["name"][0]))
                            {
                                print "<br /> File ". $this->files_vars[$name]["name"][0]. " couldn't be moved to ". $this->masterValues["uploaddir"]. ". Please check your permissions. <p />";
                            } else {
				chmod($this->masterValues["uploaddir"].  $this->http_vars[$this->idField].".".$this->files_vars[$name]["name"][0],0644);
				}
                        }
                        
                    }
                }
            }
            //run script after update if specified.
            if (isset($this->masterValues['onAfterUpdateCallBack']))
            {
                include($this->masterValues['onAfterUpdateCallBack']);
            }
            
            // if additional tables should be filled
            $createTables = $this->config->getValues("/config/create");
            
            if (!PEAR::isError($createTables) && isset($createTables["children"]) && isset($newEntry) && $newEntry )
            {
                
                require_once("bitlib/xml/xml2db.php");
                foreach ($createTables["children"] as $child)
                {
                    $dontdoit=False;
                    //only do it, if checkbox was checked
                    if(isset($this->http_vars["create"]) && $this->http_vars["create"][$child] == "on")
                    {
                        $n2mInsert=array();
                        $xml = domxml_new_xmldoc("1.0");
                        $bxroot = $xml->add_root("bx");
                        $root = XML_db2xml::newChild($bxroot,$child,"");
                        $fields = $this->config->getValues("/config/create/$child");
                        
                        foreach ($fields["children"] as $field)
                        {
                            
                            $fieldValues = $this->config->getValues("/config/create/$child/$field");
                            if (strstr($fieldValues["value"],":"))
                            {
                                
                                $split = split(":",$fieldValues["value"]);
                                
                                if ($split[0] == "this")
                                {
                                    if (isset($split[2]) )
                                    {
                                        $fieldValues["value"] = "";
                                        if (isset($this->http_vars[$split[1]]) &&  is_array($this->http_vars[$split[1]]))
                                        {
                                            foreach ($this->http_vars[$split[1]] as $objectname => $value)
                                            {
                                                if ($value[0] > 0)
                                                {
                                                    if ($split[2] == 'Value')
                                                    {
                                                        $n2mInsert[$objectname][$fieldValues["name"]] = $value[0];
                                                    }
                                                    else
                                                    {
                                                        $n2mInsert[$objectname][$fieldValues["name"]] = $objectname;
                                                    }
                                                    $fieldValues["value"] = ":n2m:";
                                                }
                                            }
                                        }
                                    }
                                    else
                                    {
                                        
                                        $fieldValues["value"] = $this->http_vars[$split[1]];
                                        
                                    }
                                }
                                else
                                {
                                    $fieldValues["value"] = $insertedFields[$split[0]][$split[1]];
                                }
                            }
                            // if attribue notempty is set in config.xml, then we should only add
                            // this entry, if the fieldValue is not empty... otherwise make nothing
                            if (isset($fieldValues["notempty"]) && empty($fieldValues["value"]))
                            {
                                $dontdoit = True;
                                break;
                            }
                            
                            if (   $fieldValues["value"] != ":n2m:")
                            {
                                
                                XML_db2xml::newChild($root,$fieldValues["name"],$fieldValues["value"]);
                            }
                            
                            
                            
                        }
                        
                        if (! $dontdoit)
                        {
                            if (count($n2mInsert)> 0)
                            {
                                foreach ($n2mInsert as $objectname => $values)
                                {
                                    $xml2db = new  XML_xml2db($this->db);
                                    foreach ($values  as $fieldname => $fieldvalue)
                                    {
                                        if (isset($tags[$fieldname]))
                                        {
                                            $tags[$fieldname]->set_content("$fieldvalue");
                                        }
                                        else
                                        {
                                            $tags[$fieldname] = XML_db2xml::newChild($root,$fieldname,bx_helpers_string::utf2entities($fieldvalue));
                                        }
                                    }
                                    $xml2db->idField = $this->idField;
                                    $xml2db->insertObject($xml,$this->fields);
                                    $insertedFields[$child] = $xml2db->fields;
                                    unset ($xml2db);
                                }
                                
                            }
                            else
                            {
                                $xml2db = new  xml_xml2db($this->db);
                                $xml2db->idField = $this->idField;
                                $xml2db->insertObject($xml,$this->fields);
                                
                                
                                $insertedFields[$child] = $xml2db->fields;
                                unset ($xml2db);
                                if (isset($fields["afterinsertupdate"]))
                                {
                                    $setupdate = preg_replace("/\{this:([a-zA-Z0-9_]*)\}/e","\$this->http_vars[$1]",$fields["afterinsertupdate"]);
                                    $setupdate = preg_replace("/\{([a-zA-Z0-9_]*):([a-zA-Z0-9_]*)\}/e","\$insertedFields[$1][$2];",$setupdate);
                                    $query = "update ".$fields["name"] ." set ".$setupdate;
                                    $GLOBALS['POOL']->dbwrite->query($query);
                                    if (MDB2::isError($res)) {
                                        print "The given query valid in file ".__FILE__." at line ".__LINE__."<br>\n";
                                        print $res->userinfo."<br>";
                                        return new MDB2_Error($res->code,PEAR_ERROR_DIE);
                                    }
                                    
                                }
                                
                            }
                        }
                    }
                    
                }
            }
        }
    } //end func insertUpdateFields
    
    function checkId ($oldID)
    {
        if (! isset($this->http_vars[$this->idField])) {
            $this->http_vars[$this->idField] = $this->getFirstID();
        } else if ($oldID > 0 && $this->http_vars[$this->idField] != $oldID) {
            $this->http_vars[$this->idField] = $oldID;
        }
    } //end func checkId
    
    function setXml ($configfile="config.xml")
    {
        //        $this->xml = new xml_sql2xml_ext($this->db,"iba");
        
        $this->xml = new XML_db2xml($this->db,"bx","Extended");
        $options = array(
            'formatOptions' => array ( 'xml_seperator' => '')
        );
        $this->xml->Format->SetOptions($options);
        $this->xml->add(realpath($configfile));
        
    } //end func setXml
    
    function setSessionVars ()
    {
        $session_vars = array();
        $xml ='<?xml version="1.0" encoding="ISO-8859-1"?><session>';
        if (is_array($_SESSION))
        {
            foreach ($_SESSION as $key => $value)
            {
                $xml .= "<$key>";
                if (is_array($value))
                {
                    $xml .= "\n";
                    foreach ($value as $subkey => $subvalue)
                    {
                        $xml .= "  <$subkey>$subvalue</$subkey>\n";
                    }
                }
                else {
                    $xml .= $value;
                }
                $xml .="</$key>\n";
            }
        }
        $xml .= "<SESSID>".session_id()."</SESSID>\n";
        /*   $xml .= "<usergroups>2</usergroups>\n";
        $xml .= "<usergroups>1</usergroups>\n";*/
        $xml.="</session>";
        //        $this->xml->Format->setResultRootTag("session");
        
        $this->xml->add($xml);
        //       $this->xml->Format->unsetResultRootTag();
    } //end func setXml
    
    function setAuthVars ()
    {
        $this->xml->Format->setResultRootTag("auth");
        if (isset($_SESSION["auth"])) {
            $this->xml->add($_SESSION["auth"]);
        }
        //       $this->xml->Format->unsetResultRootTag();
        $this->xml->Format->unsetResultRootTag();
    } //end func setXml
    
    function setQueryString ()
    {
        $this->xml->Format->setResultRootTag("querystring");
        $this->xml->add($_REQUEST);
        //       $this->xml->Format->unsetResultRootTag();
        $this->xml->Format->unsetResultRootTag();
    } //end func setXml
    
    function setMasterXml ()
    {
        
        $tableInfo['id']['Master']=0;
        $options = array ("user_tableInfo" => $tableInfo,"user_options"=>array('xml_seperator'=>""));
        $this->xml->SetOptions($options);
        
        if (! isset($this->http_vars["new"] ))
        {
            $query = "select Master.$this->idField,";
            
            if ($this->idField != 'id') {
                $query .= " Master.$this->idField as id, ";
            }
            $query .= $this->fields['sqlFields']." from ".$this->tablePrefix.$this->masterValues['table']." as Master ";
            $n2mparents = Null;
            if (isset($this->fields['n2mLeftJoins']) &&  is_array($this->fields['n2mLeftJoins'])) {
                foreach ($this->fields['n2mLeftJoins'] as $table => $leftjoin) {
                    $query .= $leftjoin;
                    $n2mparents["parent_table"][$table] = "Master";
                }
            }
            

            $query .= " where Master.$this->idField = '".$this->http_vars[$this->idField]."'";
            $options = array ("user_tableInfo" => $n2mparents);
//            $formatoptions['formatOptions']['replaceStrings'] = array("&amp;","&amp;amp;");
            $this->xml->SetOptions($options);
            try {
                $this->xml->addWithInput("Sql",$query);
            } catch(PopoonDBException $e) {
                if ($e->getCode() == -19) {
                    $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
                    $lastVersion = $GLOBALS['POOL']->db->queryOne('select value from '.$tablePrefix.'options where name = "lastdbversion"');
                    if (isset($_GET['dbupdate'])) {
                        print "Something went wrong with the dbupdate, please inform the administrator";
                    } else {
                        include_once(BX_LIBS_DIR."/tools/dbupdate/update.php");
                        header("Location: ".bx_helpers_uri::getRequestUri());
                    
                        print "\nDB updated. You should be forwarded to the requested site. If not click <a href='".bx_helpers_uri::getRequestUri()."?dbupdate=1'>here</a>\n";
                    
                        print '<meta http-equiv="refresh" content="0; URL='.bx_helpers_uri::getRequestUri().'?dbupdate=1"/>';
                    }
                    die();
                } else {
                    //rethrow
                    throw $e;
                }
            }
        }
        else
        {
            
            $xslproc = new XSLTProcessor();
            $xsl = new DomDocument();
            $xsl->load(BX_LIBS_DIR."editors/dbform/xsl/config.xsl");
            $xml = new DomDocument();
            $xml->load(getcwd()."/config.xml");
            $xslproc->importStyleSheet($xsl);
            
            $xslproc->setParameter("","isMozilla",popoon_classes_browser::isMozilla() ? "true": "false");
            $configxml = $xslproc->transformToXml($xml);
            $this->xml->add($configxml,array("namespaces"=> array(array("bxco","http://www.flux-cms.org/config/1.0")), "xpath"=>"/bxco:config/bxco:fields/*","root"=>"master/master"));
        }
    } //end func setMasterXml
    
    
    function setAdditionalSql () {
        if (isset($this->fields['additionalSql']) && is_array($this->fields['additionalSql']))
        {
            foreach($this->fields['additionalSql'] as $sql) {
                $tableInfo['id']['Document']=0;
                $options = array ("user_tableInfo" => $tableInfo);
                $this->xml->SetOptions($options);
                $this->xml->add($sql);
            }
        }
    } //end func setAdditionalSql
    
    function setChooser ($path = Null,$tablePrefix = '') {
        
        if (!$path) { $path = $this->configRoot."/chooser";}
        $chooserValues = $this->config->getValues($path);
        //if old chooser format (attributes in fields) was there, translate...
        if (!isset($chooserValues["field"]))
        {
            unset ($chooserValues);
            $chooserValues["field"] = $this->masterValues["chooserfield"];
            $chooserValues["orderby"] = $this->masterValues["chooserorderby"];
            $chooserValues["leftjoin"] = $this->masterValues["leftjoin"];
        }
        
        if (isset($chooserValues["orderby"]))
        {
            $orderby = "order by ".$chooserValues["orderby"];
        }
        if (isset($chooserValues["leftjoin"]))
        {
            $leftjoin = "left join ".$this->replacePrefix($chooserValues["leftjoin"]);
        }
        else
        {
            $leftjoin = "";
        }
        if (isset($chooserValues["where"]))
        {
            $where = "where ".$chooserValues["where"];
        }
        else
        {
            $where = "";
        }
        if (isset($chooserValues["rang"]))
        {
            $rangfield = ", Chooser.".$chooserValues["rang"] ." as rang";
        }
        else
        {
            $rangfield = "";
        }
        $this->xml->add("select Chooser.".$this->idField." as id, ".$chooserValues["field"]."  as ChooserField $rangfield from ".$this->tablePrefix.$this->masterValues['table']." as Chooser $leftjoin $where $orderby");
    } //end func setChooser
    
    function printIt ($xslfile = false)
    {
        
        if (!$xslfile)
        {
            $xslfile = BX_LIBS_DIR."/editors/dbform/xsl/formedit.xsl";
        }
        $xslproc = new XSLTProcessor();
        $xsl = new DomDocument();
        $xsl->load($xslfile);
        $xml = $this->xml->getXmlObject();
        $xslproc->importStylesheet($xsl);
        $xslproc->setParameter("","isMozilla",popoon_classes_browser::isMozilla() ? "true": "false");
        if (isset($_GET['XML']) && $_GET['XML'] == 1) {
            header("Content-type: text/xml");
            print ($xml->saveXML());
            die(); 
        } else {
            $xslproc->setParameter("","actionURL",$_SERVER['REQUEST_URI']);
            print $xslproc->transformToXml ($xml);
        }
        
    } //end func printIt
    
    function getFirstId ()
    {
        // just give back a zero == make new entry
        return 0;
        /*
        old behaviour == first entry
        $path = $this->configRoot."/chooser";
        $chooserValues = $this->config->getValues($path);
        
        if (isset($chooserValues["orderby"]))
        {
            $orderby = "order by ".$chooserValues["orderby"];
        }
        else
        {
            $orderby = "order by $this->idField ";
        }
        if (isset($chooserValues["leftjoin"]))
        {
            $leftjoin = " left join ".$this->replacePrefix($chooserValues["leftjoin"]);
        }
        else
        {
            $leftjoin ="";
        }
        if (isset($chooserValues["where"]))
        {
            $where = "where ".$chooserValues["where"];
        }
        else
        {
            $where = "";
        }
        $this->db->loadModule('extended');
        return $this->db->extended->getOne("select Chooser. $this->idField from ". $this->tablePrefix.$this->masterValues['table']. " as Chooser $leftjoin $where $orderby limit 1");
        */
    } //end func getFirstId
    
    
    function Query2Db ($DoWhat = "insert")
    {
        $xml = new DomDocument();
        $bxroot = $xml->createElement("bx");
        $bxroot = $xml->appendChild($bxroot);
        $root = XML_db2xml::newChild($bxroot,$this->masterValues['table'],"");
        if (isset ($this->masterValues['afterinsertupdate']))
        {
            $root->setAttribute("afterinsertupdate",$this->masterValues['afterinsertupdate']);
            if (isset ($this->masterValues['afterinsertupdateifempty']))
            {
                $root->setAttribute("afterinsertupdateifempty",$this->masterValues['afterinsertupdateifempty']);
            }
            
        }
        
        if (isset($this->http_vars["delete"]) && $this->checkSensitive())
        {
            print "<hr noshade>";
            print "<b>Do you really want to delete ID ".$this->http_vars[$this->idField];
            print "?<br>";
            print '<form method="POST" action=".">';
            print '<input type="submit" name="Yes" value="Yes"/>';
            print '<input type="button" name="No" value="No" onclick="window.location.href = window.location.href"/>';
            
            
            //GAAAAAANZ haesslich....
            print "</b><hr noshade>";
            print "Values of ID ".$this->http_vars[$this->idField];
            
            print ":<table>";
            foreach ($this->http_vars as $key => $value) {
                if ($key != "delete" && $key != "_issensitive_password" && $key != "update" && $key != "MAX_FILE_SIZE")
                {
                    print "<tr><td valign=top>$key</td><td>".htmlentities($value)."</td></tr>";
                }
                
            }
            print '<input type="hidden" name="'.$this->idField.'" value="'.$this->http_vars[$this->idField].'"/>';
            print '<input type="hidden" name="_issensitive_password" value="'.$this->http_vars['_issensitive_password'].'"/>';
            
            print '<input type="hidden" name="reallydelete" value="1"/>';
            print '<input type="hidden" name="update" value="1"/><br/>';
            
            print "</table>";
            print "</form>";
            die;
            //ENDE GAAAAANZ haesslich
        }
        if (isset($this->http_vars["reallydelete"]) &&  $this->http_vars["reallydelete"] == 1)
        {
            $this->objectDelete($this->http_vars[$this->idField],$this->masterValues['table']);
            return true;
        }
        
        if ($this->http_vars[$this->idField]) {
            XML_db2xml::newChild($root,$this->idField,$this->http_vars[$this->idField]);
        }
        foreach ($this->fields["fields"] as $name => $fieldarray) {
            
            
            
            if (! (isset ($this->http_vars[preg_replace("/\..+/","",$name)]))  && ! (isset($this->files_vars[$name]) && is_array($this->files_vars[$name])))
            {
                
                //donothing
            }
            
            //if fieldtype is file, we have to do some checks...
            elseif ("file" == $fieldarray["type"])
            {
                
                // if $this->files_vars is not set, and the value is not none, nothing has
                // changed (no new file upload and no deletion)
                if (isset($this->files_vars[$name]) && $this->files_vars[$name]["name"][0] === "")
                {
                    $_old = $name."_old";
                    XML_db2xml::newChild($root,"$name",bx_helpers_string::utf2entities($this->http_vars[$_old]));
                }
                // if the uploaded file name (on the server side) has not the name "none", a new file was uploaded
                //  (in php 4.1 it seems, that if we don't upload a file, then it's not in _FILES
                //   not sure if this is intended behaviour or not. but php 4.2 behaves the same... check with the php docs..)
                elseif (isset($this->files_vars[$name]) &&  $this->files_vars[$name]["name"][0]!= "none") {
                    XML_db2xml::newChild($root,"$name",bx_helpers_string::utf2entities($this->files_vars[$name]["name"][0]));
                }
                // if the name of the uploaded file is not "none", nothing happend. use the old name
                elseif (isset($this->files_vars[$name]) && $this->files_vars[$name]["name"][0] != "none") {
                    $_old = $name."_old";
                    XML_db2xml::newChild($root,"$name",bx_helpers_string::utf2entities($this->http_vars[$_old]));
                }
                // otherwises delete the entry in the db
                else {
                    XML_db2xml::newChild($root,"$name",bx_helpers_string::utf2entities(""));
                }
            }
            
            elseif ("n2m" == $fieldarray["type"])
            {
                 
                //if (is_array($this->http_vars[$fieldarray["n2m"]["tablename"]])) {
                    
                    if (isset($fieldarray["n2m"]["thisidfield"]))
                    {
                        $thisidfield = $fieldarray["n2m"]["thisidfield"];
                    }
                    else
                    {
                        $thisidfield = $this->idField;
                    }
                // if we have checkboxes subtypes, delete all entries, which are not checked 
                if (isset($fieldarray['subtype']) && $fieldarray['subtype'] == 'checkboxes') {
                    foreach ($fieldarray["n2m"]["children"] as $child) {
                        $_keys = $this->http_vars[$fieldarray["n2m"]["tablename"]][$child];
                        $query = "delete from ". $fieldarray["n2m"]["tablename"] . " where " .$fieldarray["n2m"]['thisfield'] . "= '".$this->http_vars[$thisidfield]."'";
                        if (count($_keys) > 0) {
                            $query .= " and not( ".$fieldarray["n2m"]['thatfield'] ." in ('".implode("','",$_keys)."'))";
                        }
                    
                        $res = $GLOBALS['POOL']->dbwrite->query($query);
                        if (MDB2::isError($res)) {
                            print "The given query valid in file ".__FILE__." at line ".__LINE__."<br>\n";
                            print $res->userinfo."<br>";
                            return new MDB2_Error($res->code,PEAR_ERROR_DIE);
                        }
                        // get all entries already in the DB
                         $query = "select ". $fieldarray["n2m"]['thatfield']. " from ". $fieldarray["n2m"]["tablename"] . " where " .$fieldarray["n2m"]['thisfield'] . "= '".$this->http_vars[$thisidfield]."'";
                        $this->db->loadModule('extended'); 
			$res = $this->db->extended->getAll($query);
                         $_checkboxes = array();
                         foreach($res as $_en) {
                             $_checkboxes[$_en[0]] = 1;
                         }
                         $_checkbox_keys[$child] = array_keys($_checkboxes );
                         
                    }
                }
                    
                if (is_array($this->http_vars[$fieldarray["n2m"]["tablename"]])) {
                   
                    // only do it if we have an id for this record (meaning = not new)
                    if ($this->http_vars[$thisidfield] > 0) {
                        foreach ($fieldarray["n2m"]["children"] as $child)
                        {
                            foreach ($this->http_vars[$fieldarray["n2m"]["tablename"]][$child] as $idkey => $idvalue) {
                                // if both are 0 then nothing happened
                                if ($idkey == "0" && $idvalue == "0") {continue;}
                                
                                // if $idkey still is 0 then it's a new field and we should inform about that..
                                if ($idkey == "0") {$idkey="";}
                                // if checkbox mode and is alread in db...
                                if (isset($fieldarray['subtype']) && $fieldarray['subtype'] == 'checkboxes' && in_array($idkey,$_checkbox_keys[$child])) {
                                    continue;
                                }
                                
                                $n2mXml = XML_db2xml::newChild($bxroot,$fieldarray["n2m"]["tablename"],"");
                                
                                // if idvalue is 0, none was choosen from the drop down and it should be deleted
                                // or if the whole entry is to be delete the entries in the n2m should also be
                                // or the delcheckbox was cheked
                                if ($idvalue == "0" || $idvalue == "on" || isset($this->http_vars["delete"]))
                                {
                                    if (!(isset($this->http_vars[$fieldarray["n2m"]["tablename"]][$child][$idkey - 1]) && $this->http_vars[$fieldarray["n2m"]["tablename"]][$child][$idkey - 1] == "on"))
                                    {
                                        $n2mXml->setAttribute("delete","delete");
                                    }
                                    else
                                    {
                                        $n2mXml->setAttribute("donothing","yes");
                                    }
                                }
                                
                                XML_db2xml::newChild($n2mXml,"id",$idkey);
                                XML_db2xml::newChild($n2mXml,$fieldarray["n2m"]['thatfield'],$idvalue);
                                XML_db2xml::newChild($n2mXml,$fieldarray["n2m"]['thisfield'],$this->http_vars[$thisidfield]);
                                if (isset($fieldarray["n2m"]['objectfield']) &&  $fieldarray["n2m"]['objectfield']) {
                                    if (isset($fieldarray["n2m"]['objectfieldvalue']))
                                    {
                                        XML_db2xml::newChild($n2mXml,$fieldarray["n2m"]['objectfield'],$fieldarray["n2m"]['objectfieldvalue']);
                                        
                                    }
                                    else
                                    {
                                        XML_db2xml::newChild($n2mXml,$fieldarray["n2m"]['objectfield'],$child);
                                    }
                                }
                            }
                        }
                    }
                }
                
                
            }
            elseif ("none" == $fieldarray["type"])
            {
                //do nothing
            }
            else {
                
                /*
                if (function_exists("domxml_dump_node"))
                {
                    $tmpxml = "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?><root>".str_replace('&','&amp;',$this->http_vars[$name])."</root>";
                    $tmpxml = xmldoc($tmpxml);
                    $tmpxml = $tmpxml->root();
                    $tmpchild = XML_db2xml::newChild($root,"$name","");
                    foreach($tmpxml->children() as $tmpfragment)
                    {
                        $tmpchild->add_child($tmpfragment);
                    }
                }
                else*/
                {
                    //replace & with &amp;, otherwise the xml-parser strips away all unknown entities (like auml..)
                    XML_db2xml::newChild($root,"$name",bx_helpers_string::utf2entities(str_replace('&','&amp;',$this->http_vars[$name])));
                }
            }
            
            
        }
        
        if ($DoWhat == "debug") {
            $xmlstring = $xml->dumpmem();
            debug::print_xml($xmlstring);
        }
        elseif ($DoWhat == "insert")
        {
            $xml2db = new  XML_xml2db($this->db,$this->tablePrefix);
            $xml2db->idField = $this->idField;
            return  $xml2db->insertObject($xml,$this->fields);
        }
    } //end func Query2Db
    
    function objectDelete($ID, $table=null)
    {
        $xml = new DomDocument("1.0");
        $root =  XML_db2xml::newChild($xml,"iba");
        $root = XML_db2xml::newChild($root,$table);
        
        $root->setAttribute("delete","delete");
        XML_db2xml::newChild($root,$this->idField,$ID);
       

        $xml2db = new  XML_xml2db($this->db,$this->tablePrefix);
        $xml2db->idField = $this->idField;
        return $xml2db->insertObject($xml);
    }
    
    function replacePrefix($query) {
     return str_replace("{tablePrefix}",$this->tablePrefix,$query);
    }
        
} //end class admin_common
?>
