<?php
// +----------------------------------------------------------------------+
// | Flux CMS                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Liip AG      <contact@liip.ch>                               |
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
    
    public function __construct($mode) {
        $this->mode = $mode;
    }

    /*
    public function getPermissionList() {
    	return array(	"admin_dbforms2-back-edit");	
    }*/
    
    public function getIdByRequest($path, $name = NULL, $ext  = NULL) {
        return $name;
    }

    public function getContentById($path, $id) {
        
        $perm = bx_permm::getInstance();
        $urlParts = explode('/', $id);
        if (!$perm->isAllowed('/dbforms2/', array('admin_dbforms2-back-'.$urlParts[1]))) {
            throw new BxPageNotAllowedException();
        }
        
        // get form name and mode from id
        $formName = $this->getMainFormNameById($id);
        if($formName == '') 
            throw new Exception('No form specified.');
        
        // get config for the form and create a new form instance
        $formConfig = new bx_dbforms2_config($formName);
        $mainForm = $formConfig->getMainForm();
        $form = $mainForm;
        
        // check if the request belongs to a subform
        $subFormName = $this->getSubFormNameById($id);
        if(!empty($subFormName)) {
            $form = $mainForm->getSubFormByName($subFormName);
        }

        $mode = $this->getDisplayModeByID($id);
        
        if($mode == 'data') {
            $rawpost = file_get_contents('php://input');
            if(!empty($rawpost)) {
                $db = $GLOBALS['POOL']->dbwrite;
                
                // create a new DOM document out of the posted string
                $xmlData = new DOMDocument();
                $xmlData->loadXML($rawpost);
                
                // get values as an array
                $values = bx_dbforms2_data::getValuesByXML($form, $xmlData);
                $form->setValues($values);
                
                // set current form id
                if(isset($values[$form->idField]) && $form->idField)
                    $form->currentID = $values[$form->idField];

                
                if($xmlData->documentElement->getAttribute('getnewid') == 'true') {
                    $newId = $db->nextID($form->tablePrefix.'_sequences');
                    // return the newly created id
                    $dom = $this->createResponse(0, $newId);
                    $dom->documentElement->setAttribute('id', $newId);
                    //bx_log::log($dom->saveXML());
                    return $dom;
                    
                } else if($xmlData->documentElement->getAttribute("delete") == "true" && $form->currentID != 0) {
                    // delete an existing entry
                    $form->queryMode = bx_dbforms2::QUERYMODE_DELETE;
                    $query = bx_dbforms2_sql::getDeleteQueryByForm($form);  
                    $form->callEventHandlers(bx_dbforms2::EVENT_DELETE_PRE);
                    $form->callEventHandlers(bx_dbforms2::EVENT_DELETE_PRE);
                
                } else if($form->currentID == 0) {
                    // create a new entry
                    $form->queryMode = bx_dbforms2::QUERYMODE_INSERT;
                    
                    $insertid = $xmlData->documentElement->getAttribute('insertid');
                    if(!empty($insertid)) {
                        $form->currentID = $insertid;
                    } else {
                        $form->currentID = $db->nextID($form->tablePrefix.'_sequences');
                    }
                    
                    $form->callEventHandlers(bx_dbforms2::EVENT_INSERT_PRE);
                    $query = bx_dbforms2_sql::getInsertQueryByForm($form);
                
                } else {
                    // update an existing entry
                    $form->queryMode = bx_dbforms2::QUERYMODE_UPDATE;
                    $form->callEventHandlers(bx_dbforms2::EVENT_UPDATE_PRE);
                    $query = bx_dbforms2_sql::getUpdateQueryByForm($form);
                }
                
                // give it a go
                $res = $db->query($query);

                if(MDB2::isError($res)) {
                    // return the db's error code on error
                    $responseCode = $res->getCode();
                    $responseText = $res->getMessage(). "\n".$res->getUserInfo();

                } else {
                    // 0 means everthing went ok and as expected
                    $responseCode = 0;
                    $responseText = 'id = '.$form->currentID;
                }

                $dataDOM = NULL;
                
                switch($form->queryMode) {
                    case bx_dbforms2::QUERYMODE_INSERT:
                        $form->callEventHandlers(bx_dbforms2::EVENT_INSERT_POST);
                    break;
                    case bx_dbforms2::QUERYMODE_UPDATE:
                        $form->callEventHandlers(bx_dbforms2::EVENT_UPDATE_POST);
                    break;
                    case bx_dbforms2::QUERYMODE_DELETE:
                        $form->callEventHandlers(bx_dbforms2::EVENT_DELETE_POST);
                    break;
                }

                // run additional field queries and server-side onsave handlers if there was no error 
                if ($responseCode == 0) {
                    bx_dbforms2_data::doAdditionalQueries($form);
                    
                    // reload the saved data and return it to the client (on insert or update only)
                    if($form->queryMode == bx_dbforms2::QUERYMODE_INSERT || $form->queryMode == bx_dbforms2::QUERYMODE_UPDATE) { 
                        $dataDOM = $this->getDataByForm($form);
                    }
                }
                
                // create response
                $dom = $this->createResponse($responseCode, $responseText);
                $dom->documentElement->setAttribute('id', $form->currentID);
                
                // append reloaded data
                if($dataDOM !== NULL) {
                    $dataNode = $dom->createElement('data');
                    $dataNode->appendChild($dom->importNode($dataDOM->documentElement, TRUE));
                    $dom->documentElement->appendChild($dataNode);
                }
                return $dom;
            } 
            
            if(isset($_GET['id'])) {
                $form->currentID = (int) $_GET['id'];
            }
                
            return $this->getDataByForm($form);

        } else if($mode == 'form') {
            
            $dom = $form->serializeToDOMObject();
            if (isset($_GET['XML']) && $_GET['XML'] == 1.1) {
                return $dom;
            }
            
            // default form-xsl
            $xslfile = BX_LIBS_DIR.'dbforms2/xsl/dbforms2.xsl';
            
            // check for userspace form-xsl in local include dir
            if (isset($form->attributes['xsl']) && !empty($form->attributes['xsl'])) {
                $userxslf = BX_LOCAL_INCLUDE_DIR."dbforms2/xsl/".$form->attributes['xsl'];
                if(file_exists($userxslf)) {
                    $xslfile = $userxslf;    
                }
            }
             
            return bx_dbforms2_common::transformFormXML($dom, $form->tablePrefix, $xslfile);

        } else if($mode == 'chooser') {
            
            if(!$form->chooser instanceof bx_dbforms2_liveselect) {
                throw new Exception('No chooser has been defined for this form.');
            }
            
            if(isset($_GET['q'])) {
                $form->chooser->query = $_GET['q'];
                
                if(isset($_GET['p'])) {
                    $form->chooser->currentPage = $_GET['p'];
                    $xml = bx_helpers_db2xml::getXMLByQuery($form->chooser->getSelectQuery());
                    $form->chooser->appendPagerNode($xml);
                } else {
                    $xml = bx_helpers_db2xml::getXMLByQuery($form->chooser->getSelectQuery());
                }
                
                return $xml;
            }

        } else if($mode == 'listview') {
            $thisid = '';
            $thatid = '';
            if(isset($_GET['thisid'])) {
                $thisid = $_GET['thisid'];
            }
            if(isset($_GET['thatid'])) {
                $thatid = $_GET['thatid'];
            }
            
            $parts = explode('/', $id);
            $fieldName = $parts[sizeof($parts)-1];
            $field = $form->getFieldByName($fieldName);
            
            if($field instanceof bx_dbforms2_fields_listview_12n) {
                $query = $field->getSelectQuery(array('thatid' => $thatid));
            } else if($field instanceof bx_dbforms2_fields_listview_n21) {
                $query = $field->getSelectQuery(array('thisid' => $thisid));
            } else if($field instanceof bx_dbforms2_fields_listview_n2m) {
                $query = $field->getSelectQuery(array('thatid' => $thatid, 'thisid' => $thisid));
            } else if($field instanceof bx_dbforms2_fields_listview) {
                $query = $field->getSelectQuery();
            } 

            if($field instanceof bx_dbforms2_fields_listview) {
                return bx_helpers_db2xml::getXMLByQuery($query);
            }
            
        } else if($mode == 'liveselect') {
            if(isset($_GET['q'])) {
                $parts = explode('/', $id);
                $fieldName = empty($parts[sizeof($parts)-1]) ? $parts[sizeof($parts)-2] : $parts[sizeof($parts)-1];
                $field = $form->getFieldByName($fieldName);
                if($field->liveSelect instanceof bx_dbforms2_liveselect) {
                    $field->liveSelect->query = $_GET['q'];
                    $field->liveSelect->tablePrefix = $form->tablePrefix;
                    if(isset($_GET['p'])) {
                        $field->liveSelect->currentPage = $_GET['p'];
                    }
                    
                    $xml = bx_helpers_db2xml::getXMLByQuery($field->liveSelect->getSelectQuery());
                    $field->liveSelect->appendPagerNode($xml);
                    return $xml;
                }
                
            }
            
        } else if($mode == 'upload') {
            
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
     *  Creates a new DOM document which can be sent to the client as a response.
     *
     *  @param  string $code The error code of the response
     *  @param  string $text The message of the response
     *  @access protected
     *  @return DOMDocument The created response
     */
    protected function createResponse($code, $text) {
        $dom = new DomDocument();
        $dom->appendChild($dom->createElement('response'));
        $dom->documentElement->setAttribute('code', $code);
        $dom->documentElement->appendChild($dom->createElement('text', $text));
        return $dom;
    }
    
    /**
     *  Returns the name of the main form.
     *
     *  @param  string $id 
     *  @access protected
     *  @return type descr
     */
    protected function getMainFormNameById($id) {
        $elements = explode('/', substr($id, 1));
        return $elements[0];
    }
    
    /**
     *  Returns the name of a sub form.
     *
     *  @param  string $id 
     *  @access protected
     *  @return type descr
     */
    protected function getSubFormNameById($id) {
        $elements = explode('/', substr($id, 1));
        if(isset($elements[1]) && $elements[1] === 'subform' && !empty($elements[2])) {
            return $elements[2];
        }
    }
    
    /**
     *  Returns the display mode by id.
     *
     *  @param  string $id Request id
     *  @access protected
     *  @return string Display mode
     */
    protected function getDisplayModeByID($id) {
        $mode = 'form';
        $elements = explode('/', substr($id, 1));
        if(!empty($elements[1])) {
            if($elements[1] == 'subform' && sizeof($elements > 3)) {
                $elements = array_slice($elements, 2, 2);
            }
            
            if(in_array($elements[1], array(
                'data', 
                'chooser', 
                'listview', 
                'liveselect', 
                'upload', 
                ))) 
            {
                $mode = $elements[1];
            }
            
        }
        return $mode;
    }
    
    /**
     *  Takes a form and creates the correspondig DOMObject. 
     *
     *  @param  object $form Form object
     *  @access protected
     *  @return object DOMObject containing the data of the form.
     */
    protected function getDataByForm($form) {
        $form->queryMode = bx_dbforms2::QUERYMODE_SELECT;
        $form->callEventHandlers(bx_dbforms2::EVENT_SELECT_PRE);        
        $query = bx_dbforms2_sql::getSelectQueryByForm($form);
        $dataDOM = bx_helpers_db2xml::getXMLByQuery($query,true);
        $dataDOM = bx_dbforms2_data::addAdditionalDataByForm($form, $dataDOM);
        $form->callEventHandlers(bx_dbforms2::EVENT_SELECT_POST);        
        return $dataDOM;
    }
    
    public function handlePOST($path, $id, $data) {
        return FALSE;
    }

    public function getPipelineParametersById($path, $id) {
        $params = array();
        $dm = $this->getDisplayModeByID($id);
        
        if($dm == 'data' || $dm == 'chooser' || $dm == 'liveselect' || $dm == 'listview') {
            $params['pipelineName'] = 'xml';
        }
        
        return $params;
    }
    
    public function adminResourceExists($path, $id, $ext=null, $sample=FALSE) {
        return TRUE; 
    }
      
}
?>
