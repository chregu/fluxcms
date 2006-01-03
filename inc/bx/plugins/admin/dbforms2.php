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
// $Id$

class bx_plugins_admin_dbforms2 extends bx_plugins_admin implements bxIplugin {
    
   static private $instance = array();
    
    public static function getInstance($mode) {
        if (!self::$instance) {
            self::$instance = new bx_plugins_admin_dbforms2($mode);
        } 
        return self::$instance;
    }   
    
    protected function __construct($mode) {
        
        $this->mode = $mode;

    }
    
    public function getIdByRequest($path, $name = NULL, $ext  = NULL) {
        return $name;
    }

    public function getContentById($path, $id) {
        
        // get form name and mode from id
        $formName = $this->getFormNameByID($id);
        if($formName == '') 
            throw new Exception('No form specified.');
        
        $mode = $this->getDisplayModeByID($id);
        
        // get config for the form and instanciate a new form object
        $formConfig = new bx_dbforms2_config($formName);
        $form = $formConfig->getForm();;
        
        /*if (!(popoon_classes_browser::isMozilla())) {
            $relocpath = false;
            if (isset($form->attributes['alternate'])) {
                $relocpath = $form->attributes['alternate'];
            } else if (file_exists(BX_PROJECT_DIR.'forms'.$id.'/config.xml')) {
                $relocpath = 'forms'.$id;
            }
            
            if ($relocpath) {
                if ($_GET['path']) {
                    unset($_GET['path']);    
                }
                header("Location: ".BX_WEBROOT. $relocpath.'?'.  bx_helpers_string::array2query($_GET));
                die();
            }
        }*/

        //$form->tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        //bx_helpers_debug::webdump($form);
        if($mode == 'data') {
            //var_dump($form);
            
            if(isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
                //bx_log::log($GLOBALS['HTTP_RAW_POST_DATA']);
                $db = $GLOBALS['POOL']->dbwrite;
                
                // create a new DOM document out of the posted string
                $xmlData = new DOMDocument();
                $xmlData->loadXML($GLOBALS['HTTP_RAW_POST_DATA']);
                
                // get values as an array
                $values = bx_dbforms2_data::getValuesByXML($form, $xmlData);
                $form->setValues($values);
                
                // set current form id
                if(isset($values[$form->idField]) && $form->idField)
                    $form->currentID = $values[$form->idField];

                
                if ($xmlData->documentElement->getAttribute("delete") == "true" && $form->currentID != 0) {
                    // delete an existing entry
                    $query = bx_dbforms2_sql::getDeleteQueryByForm($form);  
                    $type = "delete";
                    $deleteRequest = true;
                
                } else if ($form->currentID == 0) {
                    // create a new entry
                    $form->currentID = $db->nextID($form->tablePrefix.'_sequences');
                    $query = bx_dbforms2_sql::getInsertQueryByForm($form);
                    $type = "insert";
                
                } else {
                    // update an existing entry
                    $query = bx_dbforms2_sql::getUpdateQueryByForm($form);
                    $type = "update";
                }

                // give it a go
                bx_log::log($query);
                $res = $db->query($query);

                if($db->isError($res)) {
                    // 20 means error, this number has been chosen arbitrarily
                    $responseCode = $res->getCode();
                    $responseText = $res->getMessage(). "\n".$res->getUserInfo();
                } else {
                    // 0 means everthing went ok and as expected
                    $responseCode = 0;
                    $responseText = 'id = '.$form->currentID;
                }

                $dataDOM = NULL;

                // run additional field queries and server-side onsave handlers if there was no error 
                if ($responseCode == 0) {
                    bx_dbforms2_data::doAdditionalQueries($type,$form);
                    if (isset($form->attributes['onsavephp'])) {
                        if (strpos($form->attributes['onsavephp'],"::") > 0) {
                            list ($class,$function) = explode ("::",$form->attributes['onsavephp']);
                            call_user_func(array($class,$function),$type,$form);
                        } else {
                            call_user_func($form->attributes['onsavephp'],$type,$form);
                        }
                    }
                    // reload the saved data and return it to the client (on insert or update only)
                    if($type == 'insert' OR $type = 'update') 
                        $dataDOM = $this->getDataByForm($form);
                }
                
                // create response
                $dom = new DomDocument();
                $dom->appendChild($dom->createElement('response'));
                $dom->documentElement->setAttribute('code', $responseCode);
                $dom->documentElement->setAttribute('id', $form->currentID);
                $dom->documentElement->appendChild($dom->createElement('text', $responseText));
                
                // append reloaded data
                if($dataDOM !== NULL) {
                    $dataNode = $dom->createElement('data');
                    $dataNode->appendChild($dom->importNode($dataDOM->documentElement, TRUE));
                    $dom->documentElement->appendChild($dataNode);
                }

                return $dom;
            } 
            
            if(isset($_GET['id']))
                $form->currentID = (int) $_GET['id'];
            return $this->getDataByForm($form);

        } else if($mode == 'form') {
            $dom = $form->serializeTODOM();
            if (isset($_GET['XML']) && $_GET['XML'] == 1.1) {
                return $dom;
            }
            return bx_dbforms2_common::transformFormXML($dom, $form->tablePrefix);

        } else if($mode == 'chooser') {
            
            $chooser = $formConfig->getChooser();

            if(isset($_GET['q'])) {
                $chooser->query = $_GET['q'];
                $chooser->tablePrefix = $form->tablePrefix;
                $query = bx_dbforms2_sql::getSelectQueryByLiveSelect($chooser);
                return bx_dbforms2_data::getXMLByQuery($query);
            }

        } else if($mode == 'liveselect') {
            
            if(isset($_GET['q'])) {
                $parts = explode('/', $id);
                $fieldName = empty($parts[sizeof($parts)-1]) ? $parts[sizeof($parts)-2] : $parts[sizeof($parts)-1];
                $field = $form->getFieldByName($fieldName);
                
                if($field->liveSelect instanceof bx_dbforms2_liveselect) {
                    $field->liveSelect->query = $_GET['q'];
                    $field->liveSelect->tablePrefix = $form->tablePrefix;
                    $query = bx_dbforms2_sql::getSelectQueryByLiveSelect($field->liveSelect);
                    return bx_dbforms2_data::getXMLByQuery($query);
                }
                
            }
        } else if ($mode == 'upload') {
            
            $fObj = $form->fields[$_POST['fieldname']];
            if (isset($_FILES['file']) && $fObj instanceof bx_dbforms2_fields_file) {
                $xml =  $fObj->moveUploadedFile($_FILES['file']);
                $dom = new DomDocument();
                $dom->loadXML($xml);
                 
                return $dom;
            }
        }
        
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    protected function getFormNameByID($id) {
        $elements = explode('/', substr($id, 1));
        return $elements[0];
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    protected function getDisplayModeByID($id) {
        $mode = 'form';
        
        $elements = explode('/', substr($id, 1));
        
        if(!empty($elements[1])) {
            if($elements[1] == 'data') {
                $mode = 'data';
            } else if($elements[1] == 'chooser') {
                $mode = 'chooser';
            } else if($elements[1] == 'liveselect') {
                $mode = 'liveselect';
            }  else if($elements[1] == 'upload') {
                $mode = 'upload';
            }
        }
        return $mode;
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    protected function getDataByForm($form) {
        $query = bx_dbforms2_sql::getSelectQueryByForm($form);
        $dataDOM = bx_dbforms2_data::getXMLByQuery($query,true);
        $dataDOM = bx_dbforms2_data::addAdditionalDataByForm($form, $dataDOM);
        return $dataDOM;
    }
    
    public function handlePOST($path, $id, $data) {
        return FALSE;
    }

    public function getPipelineName($path = NULL, $name = NULL, $ext = NULL) {
        $dm = $this->getDisplayModeByID($this->getIdByRequest($path, $name, $ext));
        if($dm == 'data' OR $dm == 'chooser' OR $dm == 'liveselect') {
            return 'xml';
        }
        
        return 'standard';
    }
    
    public function getStylesheetName($path = NULL, $name = NULL, $ext = NULL) {
        return 'dbforms2.xsl';
    }
    
    public function adminResourceExists($path, $id, $ext=null, $sample=FALSE) {
        return TRUE; 
    }
      
}
?>
