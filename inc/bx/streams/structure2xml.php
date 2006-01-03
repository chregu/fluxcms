<?php

class bx_streams_structure2xml extends bx_streams_buffer {
    public $db = null;
    public $defaultExpires = 3600;
    public $st2xmlCaching = "true";

    function __construct() {
        parent::__construct();

    }

    function contentOnRead($path) {
        $this->db = $GLOBALS['POOL']->db;
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
        $st2xml = new popoon_classes_structure2xml($this,$GLOBALS['POOL']->config->getTablePrefix().$this->getParameter('secondtableprefix'));
        $xml = $st2xml->showPage($table);
        return $xml->saveXML();
    }

    function contentOnWrite($content) {
    }

    function getAttrib($name) {

        return $this->getParameter($name);
    }


}

