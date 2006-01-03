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

/**
* This class takes a PEAR::DB-Result Object, a sql-query-string or an array
*  and returns a xml-representation of it.
*
* TODO
*   -encoding etc, options for header
*   -ERROR CHECKING
*
* Usage example
*
* include_once ("DB.php");
* include_once("XML/db2xml.php");
* $db = DB::connect("mysql://root@localhost/xmltest");
* $db2xml = new xml_db2xml();
* //the next one is only needed, if you need others than the default
* $db2xml->setEncoding("ISO-8859-1","UTF-8");
* $result = $db->query("select * from bands");
* $xmlstring = $db2xml->getXML($result);
*
* or
*
* include_once ("DB.php");
* include_once("XML/db2xml.php");
* $db2xml = new xml_db2xml("mysql://root@localhost/xmltest");
* $db2xml->Add("select * from bands");
* $xmlstring = $db2xml->getXML();
*
* More documentation and a tutorial/how-to can be found at
*   http://php.chregu.tv/sql2xml
*
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id$
* @package  XML_db2xml
*/
class XML_db2xml {
    /**
    * If joined-tables should be output nested.
    *  Means, if you have joined two or more queries, the later
    *   specified tables will be nested within the result of the former
    *   table.
    *   Works at the moment only with mysql automagically. For other RDBMS
    *   you have to provide your table-relations by hand (see user_tableinfo)
    *
    * @var  boolean
    * @see  $user_tableinfo, doSql2Xml(), doArray2Xml();
    */
    public $nested = True;

    /**
    * Name of the tag element for resultsets
    *
    * @var  string
    * @see  insertNewResult()
    */
    public $tagNameResult = 'result';

    /**
    *
    * @var   object PEAR::DB
    * @access private
    */
    public $db = Null;


    /**
    * This array is used to give the structure of your database to the class.
    *  It's especially useful, if you don't use mysql, since other RDBMS than
    *  mysql are not able at the moment to provide the right information about
    *  your database structure within the query. And if you have more than 2
    *  tables joined in the sql it's also not possible for mysql to find out
    *  your real relations.
    *  The parameters are the same as in fieldInfo from the PEAR::DB and some
    *   additional ones. Here they come:
    *  From PEAR::DB->fieldinfo:
    *
    *    $tableInfo[$i]['table']    : the table, which field #$i belongs to.
    *           for some rdbms/comples queries and with arrays, it's impossible
    *           to find out to which table the field actually belongs. You can
    *           specify it here more accurate. Or if you want, that one fields
    *           belongs to another table, than it actually says (yes, there's
    *           use for that, see the upcoming tutorial ...)
    *
    *    $tableInfo[$i]['name']     : the name of field #$i. if you want another
    *           name for the tag, than the query or your array provides, assign
    *           it here.
    *
    *   Additional info
    *     $tableInfo['parent_key'][$table]  : index of the parent key for $table.
    *           this is the field, where the programm looks for changes, if this
    *           field changes, it assumes, that we need a new 'rowset' in the
    *           parent table.
    *
    *     $tableInfo['parent_table'][$table]: name of the parent table for $table.
    *
    * @var      array
    * @access    private
    */
    public $user_tableInfo = array();

    /**
    * the encoding type, the input from the db has
    */
    public $encoding_from  = 'ISO-8859-1';

    /**
    * the encoding type, the output in the xml should have
    * (note that domxml at the moment only support UTF-8, or at least it looks like)
    */
    public $encoding_to = 'UTF-8';

    public $use_iconv = false;
    public $tagname = 'tagname';

    public $dsn = Null;

    static private $contentToXmlTranslation = false;
    
    const CONTENT_APPEND_FRAGMENT = 2;
    const CONTENT_APPEND_DOCUMENT = 1;
    const CONTENT_APPEND_PLAIN = 0;
    /**
    * Constructor
    * The Constructor can take a Pear::DB 'data source name' (eg.
    *  "mysql://user:passwd@localhost/dbname") and will then connect
    *  to the DB, or a PEAR::DB object link, if you already connected
    *  the db before.
    "  If you provide nothing as $dsn, you only can later add stuff with
    *   a pear::db-resultset or as an array. providing sql-strings will
    *   not work.
    * the $root param is used, if you want to provide another name for your
    *  root-tag than "root". if you give an empty string (""), there will be no
    *  root element created here, but only when you add a resultset/array/sql-string.
    *  And the first tag of this result is used as the root tag.
    *
    * @param  mixed $dsn    PEAR::DB "data source name" or object DB object
    * @param  string $root  the name of the xml-doc root element.
    * @access   public
    */
    function __construct ($dsn = Null,$root = 'root',$Format=Null,$InputClasses=array('Sql','Dbresult','Array','Http','File','XmlObject','String')) {

        if (is_null ($Format))
        {
            include_once('XML/db2xml/Format.php');
            $FormatClass = 'XML_db2xml_Format';
        }
        else {
            include_once("XML/db2xml/Format/$Format.php");
            $FormatClass = "XML_db2xml_Format_$Format";
        }
        $this->dsn = $dsn;

        $this->Format = new $FormatClass($this);
        $this->InputClasses = $InputClasses;

        $this->Format->setXmlDoc( new DomDocument('1.0'));


        if ($root) {
            
            
            $this->Format->setXmlRoot( $this->Format->xmldoc->appendChild($this->Format->xmldoc->createElement($root)));
        }

    }

    /**
    * General method for adding new resultsets to the xml-object
    *  Give a sql-query-string, a pear::db_result object or an array as
    *  input parameter, and the method calls the appropriate method for this
    *  input and adds this to $this->xmldoc
    *
    * @param    string sql-string, or object db_result, or array
    * @param    mixed additional parameters for the following functions
    * @access   public
    * @see      addResult(), addSql(), addArray(), addXmlFile()
    */
    function add($resultset, $params = Null)
    {
        if (is_array($this->InputClasses ))
        {
            foreach($this->InputClasses as $TestClass)
            {
               include_once("XML/db2xml/Input/$TestClass.php");
               if (call_user_func("addTestBefore_$TestClass",$resultset))
               {

                 return $this->addWithInput($TestClass,$resultset,$params);

                }
            }
        }

    }

    function addWithInput($container,$resultset, $params = Null)
    {

       $class = "XML_db2xml_Input_$container";
       if (!isset($this->Input) || get_class($this->Input) != strtolower($class))
       {
        include_once("XML/db2xml/Input/$container.php");
        $this->Input= new $class ($this);
       }
       $this->Input->add($resultset,$params);
    }


    /**
    * Returns an xml-string with a xml-representation of the resultsets.
    *
    * The resultset can be directly provided here, or if you need more than one
    * in your xml, then you have to provide each of them with add() before you
    * call getXML, but the last one can also be provided here.
    *
    * @param    mixed  $result result Object from a DB-query
    * @return   string  xml
    * @access   public
    */
    function getXML($result = Null)
    {
        $xmldoc = $this->getXMLObject($result);
        return $xmldoc->saveXML();
    }

    /**
    * Returns an xml DomDocument Object with a xml-representation of the resultsets.
    *
    * The resultset can be directly provided here, or if you need more than one
    * in your xml, then you have to provide each of them with add() before you
    * call getXMLObject, but the last one can also be provided here.
    *
    * @param    mixed $result result Object from a DB-query
    * @return   Object DomDocument
    * @access   public
    */
    function getXMLObject($result = Null)
    {
        if ($result) {
            $this->add ($result);
        }
        return $this->Format->xmldoc;
    }


    /**
    * This method sets the options for the class
    *  One can only set variables, which are defined at the top of
    *  of this class.
    *
    * @param    array   options to be passed to the class
    * @param    boolean   if the old suboptions should be deleted
    * @access   public
    * @see      $nested,$user_options,$user_tableInfo
    */

    function setOptions($options,$delete = False) {

        //this first if is for compatibility reasons
        // better use setUserOptions or $this->Format->setOptions
        if (isset($options['user_options']))
        {
            $this->Format->SetOptions($options);
        }


        if (is_array($options))
        {
            foreach ($options as $option => $value)
            {
               if (isset($this->{$option}))
                {
                    if (is_array($value) && ! $delete)
                    {
                        foreach ($value as $suboption => $subvalue)
                        {
                            $this->{$option}["$suboption"] = $subvalue;
                        }
                    }
                    else
                    {
                          $this->$option = $value;
                    }
                }
            }
        }
    }

    function setFormatOptions($options,$delete = False) {
        $FormatOptions['user_options'] = $options;
        $this->Format->SetOptions($FormatOptions);
    }

    // here come some helper functions...

    /**
    * make utf8 out of the input data and escape & with &amp; and "< " with "&lt; "
    * (we assume that when there's no space after < it's a tag, which we need in the xml)
    *  I'm not sure, if this is the standard way, but it works for me.
    *
    * @param    string text to be utfed.
    * @access private
    */
    function xml_encode ($text, $stringReplace = null)
    {
        if ($this->use_iconv)
        {
//			 Notice: iconv(): Unknown error (0) in /home/bitlib2/php/XML/db2xml.php on line 327
// strange messaages as in 4.3.0-dev, turn them off
             $text = @iconv($this->encoding_from,$this->encoding_to,preg_replace('/\&amp;([#a-z0-9A-Z]+);/','&$1;',str_replace('&','&amp;',$text)));
             if ($stringReplace) {
                $text = str_replace($stringReplace[0],$stringReplace[1],$text);
            }
             if (! isset($text) )
             {
                if (isset($php_errormsg))
                {
                    $errormsg = "error: $php_errormsg";
                }
                else
                {
                    $errormsg = 'undefined iconv error, turn on track_errors in php.ini to get more details';
                }
                return PEAR::raiseError($errormsg,Null,PEAR_ERROR_DIE);
             }
             else {
                return $text;
             }
        }
        else
        {
            $ret = utf8_encode(preg_replace('/\&amp;([#a-z0-9A-Z]+);/','&$1;',str_replace('&','&amp;',$text)));
            if ($stringReplace) {
                $ret = str_replace($stringReplace[0],$stringReplace[1],$ret);
            }
            return $ret;
        }
        return $text;
    }

    //taken from kc@hireability.com at http://www.php.net/manual/en/function.array-merge-recursive.php
    /**
    * There seemed to be no built in function that would merge two arrays recursively and clobber
    *   any existing key/value pairs. Array_Merge() is not recursive, and array_merge_recursive
    *   seemed to give unsatisfactory results... it would append duplicate key/values.
    *
    *   So here's a cross between array_merge and array_merge_recursive
    **/
    /**
    *
    * @param    array first array to be merged
    * @param    array second array to be merged
    * @return   array merged array
    * @access private
    */
    function array_merge_clobber($a1,$a2)
    {
        if(!is_array($a1) || !is_array($a2)) return false;
        $newarray = $a1;
        while (list($key, $val) = each($a2))
        {
            if (is_array($val) && is_array($newarray[$key]))
            {
                $newarray[$key] = $this->array_merge_clobber($newarray[$key], $val);
            }
            else
            {
                $newarray[$key] = $val;
            }
        }
        return $newarray;
    }


    /**
    * sets the encoding for the db2xml transformation
    * @param    string $encoding_from encoding to transform from
    * @param    string $encoding_to encoding to transform to
    * @access public
    */
    function setEncoding ($encoding_from = 'ISO-8859-1', $encoding_to ='UTF-8')
    {
        if ($encoding_from == 'ISO-8859-1' && $encoding_to ='UTF-8') {
            $this->use_iconv = false;
        } else if (function_exists('iconv') && isset($this->encoding_from) && isset($this->encoding_to)) {
            $this->use_iconv = true;
            ini_set('track_errors',1);
        } 
        $this->encoding_from = $encoding_from;
        $this->encoding_to = $encoding_to;
    }
    /**
    * @param array $parentTables parent to child relation
    * @access public
    */

    function SetParentTables($parentTables)
    {
        foreach ($parentTables as $table => $parent)
        {
            $table_info['parent_table'][$table]=$parent;
        }
        $this->SetOptions(array('user_tableInfo'=>$table_info));
    }


    /**
    * returns the content of the first match of the xpath expression
    *
    * @param    string $expr xpath expression
    * @return   mixed content of the evaluated xpath expression
    * @access   public
    */

    function getXpathValue ($expr)
    {

        $xpth = new DOMXPath($this->Format->xmldoc);
        $xnode = $xpth->query($expr);

        if ($xnode->item(0))
        {
            $firstnode = $xnode->item(0);

            $children = $firstnode->firstChild;
            $value = $children->nodeValue;
            return $value;
        }

        else
        {
            return Null;
        }
    }

    /**
    * get the values as an array from the childtags from the first match of the xpath expression
    *
    * @param    string xpath expression
    * @return   array with key->value of subtags
    * @access   public
    */

    function getXpathChildValues ($expr)
    {
        $xpth = new DOMXPath($this->Format->xmldoc);
        $xnode = $xpth->query($expr);

        if ($xnode->item(0))
        {
            foreach ($xnode->item(0)->childNodes as $child)
            {
                $children = $child->childNodes;
                if($children) {
                    $value[$child->nodeName] = $children->item(0)->nodeValue;
                }
            }
            return $value;
        }
        else
        {
            return Null;
        }
    }

    function setNested ($nested)
    {
        $this->nested = $nested;
    }
    
    public function setContentIsXml($bool) {
        if ($bool) {
            // the CONTENT_APPEND_FRAGMENT should be faster than the one with DOCUMENT
            // please apply the patch  from
            // http://svn.bitflux.ch/repos/public/misc/dompatches/documentfragment_appendXML.patch
            // if you're using sth around PHP 5.0.0 
            if ( is_callable(array('domdocumentfragment','appendXML'))) {
                self::$contentToXmlTranslation =  XML_db2xml::CONTENT_APPEND_FRAGMENT;
            } else {
                self::$contentToXmlTranslation =  XML_db2xml::CONTENT_APPEND_DOCUMENT;
            }
        } else {
                self::$contentToXmlTranslation =  XML_db2xml::CONTENT_APPEND_PLAIN;
        }
    }
        
    
    static function newChild($parent, $newChild, $content) {
        if ($parent->nodeType == XML_DOCUMENT_NODE) {
            $doc = $parent;
        } else {
            $doc = $parent->ownerDocument;
        }
        if ( self::$contentToXmlTranslation && (strpos($content,"<") !== false || preg_match("/\&\S;/", $content) )) {
            if (self::$contentToXmlTranslation == XML_db2xml::CONTENT_APPEND_FRAGMENT) {
                $f = $doc->createDocumentFragment();
                $f->appendXML("<$newChild>".$content."</$newChild>");
                return $parent->appendChild($f);
                
            } else { //self::$contentToXmlTranslation == CONTENT_APPEND_DOCUMENT in this case, no need to check
                //insert that code here
                $dom = new DomDocument();
                $dom->loadXML("<$newChild>".$content."</$newChild>");
                $newnode = $parent->appendChild($doc->importNode($dom->documentElement,true));
                unset ($dom);
                return $newnode;
            }
        }
        return $parent->appendChild( $doc->createElement($newChild,$content));
        
    }
    
    static function addRoot($doc,$root) {
        
        if ($doc->documentElement && $doc->documentElement->nodeName == $root) {
            return $doc->documentElement;
        }
        $root = $doc->createElement($root);
        if ($doc->documentElement) {   
            return $doc->replaceChild($root,$doc->documentElement);
        } else {
            return $doc->appendChild($root);
        }
    }
}
?>
