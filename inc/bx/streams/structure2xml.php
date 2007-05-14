<?php

class bx_streams_structure2xml extends bx_streams_buffer {
    public $db = null;
    public $config = null;
    public $defaultExpires = 3600;
    public $st2xmlCaching = "true";

    function __construct() {
        parent::__construct();

    }


     function checkForMysqlUtf8($dsn,$db) {
        if ($this->config->dbIsUtf8 === null) {
            if (popoon_pool::isMysqlFourOne($dsn,$db)) {
                $this->config->dbIsFourOne = true;
                if (popoon_pool::isMysqlUtf8($dsn,$db)) {
                    $this->config->dbIsUtf8 = true;
                }   
            }
        }
        
        if ($this->config->dbIsUtf8) {
            $this->config->dbIsUtf8 = true;
            $db->isUtf8 = true;
        } else {
            $db->isUtf8 = false;
            $this->config->dbIsUtf8 = false;
        }
        
        if ($this->config->dbIsFourOne) {
            $db->query("set names 'utf8'");
        }    
     }

    function contentOnRead($path) {

        $dsn = $this->getParameter('dsn');
        //if no dsn given -> use default
        if($dsn == ''){
            $this->db = $GLOBALS['POOL']->db;
        } else {
            //check config
            if(isset($GLOBALS['POOL']->config->$dsn)){
                $dsn = $GLOBALS['POOL']->config->$dsn;
                $this->config = $GLOBALS['POOL']->config;
                require_once("MDB2.php");
                $this->db = @MDB2::connect($dsn);
                $this->checkForMysqlUtf8($dsn,$this->db);
            }
        }
        
        $parts = parse_url($path);
        if(preg_match('#^[\/]*(.*)\/$#', $parts['path'], $matches)) {
            $table = $matches[1];
        }

        //make no xml_seperator by default
        if (is_null($this->getParameter("xml_seperator"))) {
            $this->setParameter("xml_seperator","");
        }
         $this->setParameter("contentIsXml",true);
        if ($this->getParameter("st2xmlCaching")) {
            $this->st2xmlCaching = $this->getParameter("st2xmlCaching");
        }
	$ptP = $this->getParameter('tableprefix');
	if ($ptP) {
		$prefix = $ptP;	
	} else {
		$prefix = $GLOBALS['POOL']->config->getTablePrefix().$this->getParameter('secondtableprefix');
	}
	
        $st2xml = new popoon_classes_structure2xml($this,$prefix);
        $xml = $st2xml->showPage($table);
        return $xml->saveXML();
    }

    function contentOnWrite($content) {
    }

    function getAttrib($name) {

        return $this->getParameter($name);
    }


}

