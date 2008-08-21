<?php
/**
* Simple Basket Plugin
* 
* <plugin type="basket">
*    <parameter name="basketname" value="kursbestellung"/>
*    <parameter name="baskethandlerclass" value="bx_helpers_novabasket"/>
* </plugin>
* 
*
*/

class bx_plugins_basket extends bx_plugin {

    static private $instance = array();
    private $basketname='';
    private $baskethandler='';
    private $storage = null;    
    private $idprefx = '';
    
    protected function __construct($mode) { 

        @session_start();
        if (!isset($_SESSION['basket'])) {
            $_SESSION['basket'] = array();
            
        }
        $this->idprefx = get_class($this)."_";
        $this->storage =& $_SESSION['basket'];
        
    }
    
    public static function getInstance($mode='') {
        if (!isset(bx_plugins_basket::$instance[$mode])) {
            bx_plugins_basket::$instance[$mode] = new bx_plugins_basket($mode);
        }
        return bx_plugins_basket::$instance[$mode];
    }
    

    public function getIdByRequest($path, $name = NULL, $ext =NULL) {
        return $this->idprefx.$name.$ext;
    }
    
    
    public function isRealResource($path, $id) {
        return true;
    }


    public function getContentById($path, $id) {
        $this->path = $path;
        $this->id = $id;
        $id = str_replace($this->idprefx, "", $id);
        $this->basketname = $this->getParameter($path, 'basketname');
        $handlerClassName = $this->getParameter($path, 'baskethandlerclass');
        $this->baskethandler = new $handlerClassName();
	    //post
        if ($_SERVER["REQUEST_METHOD"] == 'POST' && is_object($this->baskethandler) && method_exists($this->baskethandler, 'postRequest')) {
            $this->baskethandler->postRequest($this->basketname, $this, $this->storage);
        }

        $command = $this->getCommand($path, $id); 
        if ($command && !empty($command) && $command!=".") {
            $id = str_replace("$command/", "", $id);
            $handler = $command."Handler";
            if (method_exists($this, $handler)) {
                $this->$handler($path, $id);
            }
        }
	    

        $domdoc= new DomDocument();
        if (isset($this->storage[$this->basketname])) {
        
            $db2xml = new XML_db2xml(null, $this->basketname);
            $domdoc->loadXML('<basket/>');
            
            if (isset($this->storage[$this->basketname]['basket'])) { 
                ksort($this->storage[$this->basketname]['basket']);
                
                foreach($this->storage[$this->basketname]['basket'] as $idfield => $opts) {
                  //  var_export($idfield.":   ".$opts."  <br/>");
                    $e = $domdoc->createElement('entry');
                    if ($e) {
                        $e->setAttribute('idfield', $idfield);
                        
                        if ($opts && sizeof($opts) > 0) {
                            bx_helpers_xml::array2Dom($opts, $domdoc, $e);
                        } 
                        
                        if (method_exists($this->baskethandler, 'getItemInfo')) {
                            $dbprms = $this->baskethandler->getItemInfo($idfield, $this->basketname);
                            bx_helpers_xml::array2Dom($dbprms, $domdoc, $e);
                        }
                    }
                    
                    $domdoc->documentElement->appendChild($e);
                }
		

		foreach($this->storage[$this->basketname]['user'] as $idfield => $opts) {
			 //var_export($idfield.":   ".$opts."<br/>");
                    $u = $domdoc->createElement('user');
                    if ($u) {
                        $u->setAttribute('idfield', $idfield);
                        
                        if ($opts && sizeof($opts) > 0) {
			 //var_export($idfield.":   ".$opts."  <br/>");

                            bx_helpers_xml::array2Dom($opts, $domdoc, $u);
                        } 
                        
                       
                    }
                    
                    $domdoc->documentElement->appendChild($u);
                }
		
		//Shippingadresse
		foreach($this->storage[$this->basketname]['shipping'] as $idfield => $opts) {
			 //var_export($idfield.":   ".$opts."<br/>");
                    $u = $domdoc->createElement('shipping');
                    if ($u) {
                        $u->setAttribute('idfield', $idfield);
                        
                        if ($opts && sizeof($opts) > 0) {
			 //var_export($idfield.":   ".$opts."  <br/>");

                            bx_helpers_xml::array2Dom($opts, $domdoc, $u);
                        } 
                       
                    }
                    
                    $domdoc->documentElement->appendChild($u);
                }
		
		
		//Billsadresse
		foreach($this->storage[$this->basketname]['bill'] as $idfield => $opts) {
			 //var_export($idfield.":   ".$opts."<br/>");
                    $u = $domdoc->createElement('bill');
                    if ($u) {
                        $u->setAttribute('idfield', $idfield);
                        
                        if ($opts && sizeof($opts) > 0) {
			 //var_export($idfield.":   ".$opts."  <br/>");

                            bx_helpers_xml::array2Dom($opts, $domdoc, $u);
                        } 
              
                    }
                    
                    $domdoc->documentElement->appendChild($u);
                }
		
//		
		foreach($this->storage[$this->basketname]['costs'] as $idfield => $opts) {
                    $u = $domdoc->createElement('costs');
                    if ($u) {
                        $u->setAttribute('idfield', $idfield);
                        
                        if ($opts && sizeof($opts) > 0) {
			 //var_export($idfield.":   ".$opts."  <br/>");

                            bx_helpers_xml::array2Dom($opts, $domdoc, $u);
                        } 
                       
                    }
                    
                    $domdoc->documentElement->appendChild($u);
                }
            }
            
            /*FIXME: Not really what we want ;) */
            if (isset($this->storage[$this->basketname]['checkout'])) {
                $c = $domdoc->createElement('checkout');
                if ($c) {
                    bx_helpers_xml::array2Dom($this->storage[$this->basketname]['checkout'], $domdoc, $c);
                    $domdoc->documentElement->appendChild($c);
                }
            }
        }
        return $domdoc;
    }
    

    public function &getBasket($basketname) {
        $bn = ($basketname!=null) ? $basketname : $this->basketname;
        if (isset($this->storage[$bn])) {
            return $this->storage[$bn];
        }
    }
    
    
    public function clearBasket($basketname) {
        if (isset($this->storage[$basketname])) {
            unset($this->storage[$basketname]);
        }    
    }
    
    
    private function addHandler($path, $id) {
        $key = dirname($id);
        $prms = array();                                   
        if (!isset($this->storage[$this->basketname])) {
            $this->storage[$this->basketname] = array();
            if (!isset($this->storage[$this->basketname]['basket'])) {
                $this->storage[$this->basketname]['basket'] = array();
            }
        }
        
        if (isset($_REQUEST[$this->basketname])) {
            $prms = $_REQUEST[$this->basketname];
        }
         
        if (method_exists($this->baskethandler, 'addHandler')) {
            $this->baskethandler->addHandler($path, $id, $this->basketname, $this->storage, $prms);
        } else {
            $this->storage[$this->basketname]['basket'][$key] = $prms;
        }
    }
    
    
    private function deleteHandler($path, $id) {
	        $key = dirname($id);
		unset($this->storage[$this->basketname]['basket'][$key]);
    }
    
    private function removeHandler($path, $id) {
        if (isset($_REQUEST[$this->basketname]['remove'])) {
            foreach($_REQUEST[$this->basketname]['remove'] as $idfield=>$value) {
                if (isset($this->storage[$this->basketname]['basket'][$idfield])) {
                    unset($this->storage[$this->basketname]['basket'][$idfield]);
                }
            }
        }
    }
    
    private function userInfoHandler($path, $id) {
        $prms=array();
        if (isset($_REQUEST[$this->basketname])) {
            $prms = $_REQUEST[$this->basketname];
        }

        if (is_object($this->baskethandler) && method_exists($this->baskethandler, 'userInfoHandler')) {
            $this->baskethandler->userInfoHandler($path, $id, $this->basketname, $this->storage, $prms);
        }
        
    }
    
    private function overviewHandler($path, $id) {
        $prms=array();
        if (isset($_REQUEST[$this->basketname])) {
            $prms = $_REQUEST[$this->basketname];
        }

        if (is_object($this->baskethandler) && method_exists($this->baskethandler, 'overviewHandler')) {
            $this->baskethandler->overviewHandler($path, $id, $this->basketname, $this->storage, $this->getParameterAll($path,"helper"));
        }
        
    }
    
      private function checkloginHandler($path, $id) {
        $prms=array();
        if (isset($_REQUEST[$this->basketname])) {
            $prms = $_REQUEST[$this->basketname];
        }

        if (is_object($this->baskethandler) && method_exists($this->baskethandler, 'checkloginHandler')) {
            $this->baskethandler->checkloginHandler($path, $id, $this->basketname, $this->storage, $prms);
        }
        
    }
    
    
    private function checkoutHandler($path, $id) {
        $this->storage[$this->basketname]['checkout'] = $this->getPayload();
        if (is_object($this->baskethandler) && method_exists($this->baskethandler, 'checkoutHandler')) {
            $this->baskethandler->checkoutHandler($path, $id, $this->getBasket($this->basketname));
        }
    }
    
    private function orderHandler($path, $id) {
           if (is_object($this->baskethandler) && method_exists($this->baskethandler, 'orderHandler')) {
		   $prms =
               $this->baskethandler->orderHandler($path, $id, $this->basketname, $this->storage, $this->getParameterAll($path,"helper"));
           }
       }
       
     private function newloginHandler($path, $id) {
           if (is_object($this->baskethandler) && method_exists($this->baskethandler, 'newloginHandler')) {
               $this->baskethandler->newloginHandler($this->basketname, $this->storage);
           }
       }
       
    private function finishHandler($path, $id) {
           if (is_object($this->baskethandler) && method_exists($this->baskethandler, 'finishHandler')) {
               $this->baskethandler->finishHandler($path, $id, $this->getBasket($this->basketname));
           }
       }
    
    
    private function getCommand($path, $id) {
        $cmdArr = explode("/", dirname($id));
        $command = array_shift($cmdArr);
        if ($command == "." or empty($command)) {
            if (isset($_REQUEST[$this->basketname]['command'])) {
                return $_REQUEST[$this->basketname]['command'];
            } elseif ($this->checkBasketRequest() && isset($_REQUEST['command'])) {
                return $_REQUEST['command'];
            } 
        }
        return $command;
    }
    
    
    private function getPayload() {
        if (isset($_REQUEST[$this->basketname])) {
            return $_REQUEST[$this->basketname];
        } elseif ($this->checkBasketRequest()) {
            return $GLOBALS["_".$_SERVER['REQUEST_METHOD']];
        }

        return null;
    }
    

    private function checkBasketRequest() {
        return (isset($_REQUEST['basketname']) && $_REQUEST['basketname'] == $this->basketname);
    }
    
    public function getLastModifiedById($path, $id) {
                return time();       
    
    }
    

    
}


?>
