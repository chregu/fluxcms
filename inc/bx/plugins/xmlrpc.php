<?php

class bx_plugins_xmlrpc extends bx_plugin {
    
    static private $instance = array();
    protected $_dispatchMap = array();
    
    protected function __construct() {
       
    }
    
    public static function getInstance($mode) {
        if (!isset(bx_plugins_xmlrpc::$instance[$mode])) {
            bx_plugins_xmlrpc::$instance[$mode] = new bx_plugins_xmlrpc($mode);
        } 
        return bx_plugins_xmlrpc::$instance[$mode];
    }    
    
    public function getIdByRequest ($path, $name = NULL, $ext = NULL) {
        return "xmlrpc.$name.$ext";
    }
    
    public function getContentById($path, $id) {
         global  $HTTP_RAW_POST_DATA;
         // create a global self-reference for doing callbacks
        //$GLOBALS['_popoon_generator_xmlrpc_server']['foo'] = $this;
        $HTTP_RAW_POST_DATA = file_get_contents('php://input');
        $this->path = $path;
        $this->id = $id;
        $this->registerFunctions();
        $this->_server = new XML_RPC_Server($this->_dispatchMap, FALSE);  
        try {
            $xml = $this->_server->server_payload;
        } catch (Exception $e) {
        
            $r = new XML_RPC_Response(0,$e->getCode(),$e->getMessage());
            $xml = '<?xml version="1.0" ?>'.$r->serialize();
        }
        $dom = new DomDocument();
        
        $dom->loadXML($xml);
        return $dom;
    }
    
    public function resourceExists($path, $name, $ext) {
        return true;
    }
     public function isRealResource($path , $id) {
         
        return true;
    }
        /**
    * adds a method to the dispatch map
    * @param string $methodname name of rpc method
    * @param string $functionName name of function to call
    * @return bool returns true when method has been added to the dispatch map
    */
    function addDispatch($methodName, $functionName) {
        if(method_exists($this, $functionName)) {
            
            $this->_dispatchMap[$methodName] =  array('function' => array($this,$functionName));
            return TRUE;
        }

        return FALSE;
    }
       
    
}

?>
