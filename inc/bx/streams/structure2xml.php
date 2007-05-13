<?php

class bx_streams_structure2xml extends bx_streams_buffer {
    public $db = null;
    public $defaultExpires = 3600;
    public $st2xmlCaching = "true";

    function __construct() {
        parent::__construct();

    }

    function contentOnRead($path) {

        $dsn = $this->getParameter('dsn');
        //if no dsn given -> use default
        if($dsn == ''){
            $this->db = $GLOBALS['POOL']->db;
        } else {
            //check config
            if(isset($GLOBALS['POOL']->config->$dsn)){
                require_once("MDB2.php");
                $this->db = @MDB2::connect($GLOBALS['POOL']->config->$dsn);
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

