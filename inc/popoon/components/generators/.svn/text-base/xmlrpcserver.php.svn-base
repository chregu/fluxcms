<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Philipp Stucki <philipp@bitflux.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id$

include_once("XML/RPC.php");
include_once("XML/RPC/Server.php");

/**
* generator for handling xmlrpc requests
*
* this generator enables you to act as a xmlrpc server. it's usage is very 
* simple and straightforward.
*
* what it does:
*   just answer requests coming from xmlrpc clients and map them to class 
*   methods
*
* what it needs:
*   XML_RPC from pear
*
* how to implement your own server:
*   you need to extend the base xmlrp_server generator class and add all 
*   the methods you want to expose to the public to the extended class.
*   finally you have to tell xmlrpcserver about which rpc methods match 
*   which class methods. - that's all, your application now speaks xmlrpc. 
*
* here is an example of a very simple xmlrpc generator which implements the
* obligatory hello world example :)

*   class generator_xmlrpcserver_simple extends generator_xmlrpcserver {
*   
*       function generator_xmlrpcserver_simple(&$sitemap) {
*           parent::generator_xmlrpcserver($sitemap);
*           
*           $this->addDispatch('moblog.helloWorld', '_helloWorld');
*       }
*   
*       function _helloWorld($params) {
*           return new XML_RPC_Value('hello world');
*       }
*   }
*
*
* @author   Philipp Stucki <philipp@bitflux.ch>
* @version  $Id$
* @package  popoon
*/

class popoon_components_generators_xmlrpcserver extends popoon_components_generator {

    /**
    * array containing dispatch map
    * @var array
    * @access private
    */
    var $_dispatchMap;
    
    /**
    * xmlrpc server object
    * @var object
    * @access private
    */
    var $_server;
    
    function __construct($sitemap) {
        parent::__construct($sitemap);
    }
    
    function init($attribs) {
        // call parent method
        parent::init($attribs);

        // create a new xmlrpc server
        $this->_server = new XML_RPC_Server($this->_dispatchMap, FALSE);
    }    
    
    function DomStart(&$xml) {

        // and serialize the result - that's it.
        $xml = $this->_server->server_payload;
        $this->sitemap->setHeader('Content-length', strlen($xml));
    }
    
    /**
    * adds a method to the dispatch map
    * @param string $methodname name of rpc method
    * @param string $functionName name of function to call
    * @return bool returns true when method has been added to the dispatch map
    */
    function addDispatch($methodName, $functionName) {

        if(method_exists($this, $functionName)) {
            $this->_dispatchMap[$methodName] =  array('function' => array($this, $functionName));
            return TRUE;
        }

        return FALSE;
    }
}

?>
