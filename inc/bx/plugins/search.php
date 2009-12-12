<?php


class bx_plugins_search extends bx_plugin implements bxIplugin {
    
    static public $instance = array();
    
    private $searchObj = null;
    private $driver    = 'MnogoSearch';
    private $searchString = '';
    private $currPage = 0; 
    private $numRows = 10;
    
    /*** magic methods and functions ***/
    
    public static function getInstance($mode) {
        
        if (!isset(bx_plugins_search::$instance[$mode])) {
            bx_plugins_search::$instance[$mode] = new bx_plugins_search($mode);
        } 
        return bx_plugins_search::$instance[$mode];
    }
    
    protected function __construct($mode) {
        $this->mode = $mode;
        $this->searchString = @$_GET['q']; 
        $this->currPage = (isset($_GET['p'])) ? @$_GET['p']:$this->currPage;
     
    }
    
    public function getContentById($path, $id) {
         
        $dom = new domDocument();
        
        if (!empty($this->searchString)) {
            
            
            
            $opts = array('dsn' => $GLOBALS['POOL']->config->getConfProperty('search_dsn'),
                          'NumRows' => $this->numRows,
                          'CurrPage'=> $this->currPage
                          );
                          
            /* populate all params from .configxml / overrides defaults */
            if ($_p = $this->getParameterAll($path)) {
                $opts = array_merge($opts, $_p);
            }

            if ($this->loadSearchModule($this->driver, $opts)) {
                $resnode = $dom->createElement('results');
                
                $querynode = $dom->createElement('query');
                $querynode_txt = $dom->createTextNode($this->searchString);
                $querynode->appendChild($querynode_txt);    
                $resnode->appendChild($querynode);
               
                $query = $this->searchObj->doQuery($this->searchString);
                if (!isset($query['error'])) {
                    $results = $this->searchObj->getResult();
                    
                    if (is_array($results) && sizeof($results) > 0) {
                        
                        $resultParams = $this->searchObj->getResultParams();
                        
                        if (is_array($resultParams)) {
                            foreach($resultParams as $name => $value) {
                                
                                $attrnode = $dom->createElement($name);
                                $attrtxt  = $dom->createTextNode($value);
                                $attrnode->appendChild($attrtxt);
                                $resnode->appendChild($attrnode);
                            
                            }
                        }

                        $this->array2dom($results, $dom, $resnode);
                    }
                }


                $dom->appendChild($resnode); 
            }     
         
             

        } 
        
        return $dom;
    }
    
    
    public function getIdByRequest($path, $name = NULL, $ext  = NULL) {
        return $path; 
    } 
    
    
    public function isRealResource($path , $id) {
        return true;
    }
    

    private function array2dom($array, &$domdoc, &$domnode) {
        if (is_array($array)) {
        
            foreach ($array as $n => $node) {
                
                $n = (is_numeric($n)) ? "entry":$n;
                $elem = $domdoc->createElement($n);
                
                if (is_array($node)) {
                    $this->array2dom($node, $domdoc, $elem);
                } else {
                    $txtnode = $domdoc->createTextNode($node);
                    $elem->appendChild($txtnode);
                }

                if ($domnode !== NULL) {
                    $domnode->appendChild($elem);
                } else {
                    $domdoc->appendChild($elem);
                }
            }
        
        }
    }
    
    private function loadSearchModule($driver, $params) {
        
        $incfile = sprintf("%scomponents/generators/search/%s.php", BX_POPOON_DIR, $driver);
        if (file_exists($incfile)) {
            
            if (!get_class($this->searchObj)) {
                
                include_once $incfile;
            
                if (class_exists($driver)) {
                    $this->searchObj = new $driver($params); 
                    if ($this->searchObj instanceof $driver) {
                        return true;
                    }
                }
            
            }
        }
    
        return false;
    }  
    
    
}
?>
