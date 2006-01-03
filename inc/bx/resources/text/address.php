<?php

class bx_resources_text_address extends bx_resource {

    protected $fulluri = "";
    
    protected $id = "";
    
    public function __construct($id, $new = false ) {
        
        $uri = bx_collections::sanitizeUrl(dirname($id));
        $name = basename($id);
        $this->fulluri = $id;
        $this->id = $id;
        
        $this->props['mimetype'] = "text/address";
        
        if ($name== "index") {
            
            $this->isValid = true;
            $this->address['address_address.name'] = "overview";
            $this->address['address_address.city'] = "nada";
            return;
        }

            
        $query = "select * from address_address left join address_collection on address_address.parenturi = address_collection.uri where address_address.name = '".str_replace("_"," ",$name)."' and address_address.parenturi = '$uri'";
        $res = $GLOBALS['POOL']->db->query($query);
        $res = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
        if (!$res) {
            $this->isValid = false;
        } else {
            $this->isValid = true;
        }
        $this->address = $res;

        if ($new) {
           $this->init();
        }
    }

    public function getInputContentUri() {
        return BX_DATA_DIR.$this->fulluri;
    }
    public function getDisplayName() {
       return $this->address['address_address.name'];
    }
    
    public function getLocalName() {
         return str_replace(" ","_",$this->address['address_address.name']).".xml";
    }
    
    public function getProperty($name, $namespace = BX_PROPERTY_DEFAULT_NAMESPACE) {
        if ($name == "output-mimetype") {
            return "text/html";
        }
    }

    
    public function getContent() {
        $xml = '<address>';
        $xml .= '<name>'.$this->address['address_address.name'].'</name>';
         $xml .= '<city>'.$this->address['address_address.city'].'</city>';
        $xml .= '</address>';
        return $xml;
    }
    
    protected function init() {
        $this->setProperty("mimetype",$this->mimetype);
        $this->setProperty("output-mimetype","text/html");
        $this->setProperty("parent-uri",bx_collections::getCollectionUri($this->id));
    }
    
}