<?php

class bx_filters_patforms_hegusearch extends bx_filters_patforms_formhandler {

    public $returnsDOM = TRUE;

    protected $searchFields = array(
        'meta_projektname' => array('hasEntities' => TRUE), 
        //'meta_jahr' => array('hasEntities' => FALSE),
        'meta_architekt' => array('hasEntities' => TRUE),
        'meta_bauherrschaft' => array('hasEntities' => TRUE)
    );
    
    public function __construct() {
        $this->db = $GLOBALS['POOL']->db;
        $this->lang = $GLOBALS['POOL']->config->getOutputLanguage();
        
    }
    
    public function submitFields($params, $fields) {
        $query = "SELECT id, uri, teaser, meta_projektname FROM projekte WHERE online=1 ";
        $lengthOK = FALSE;
        // compose query
        foreach($fields as $key => $value) {
            // at least one of the query string should be larger than 3 characters
            if(!$lengthOK && strlen($value) > 2) {
                $lengthOK = TRUE;
            }
            if(in_array($key, array_keys($this->searchFields)) && !empty($value)) {
                if($this->searchFields[$key]['hasEntities']) {
                    $value = bx_helpers_string::utf2entities($value);
                }
                $query .= "AND ($key like ".$this->db->quote("%%$value%%").") ";
            }
        }
        if(!$lengthOK) {
            $query .= "AND 1=0 ";
        }

        $xml = '<div xmlns:i18n="http://apache.org/cocoon/i18n/2.1">';

        $res = $this->db->query($query);
        if (!MDB2::isError($res)) {
            while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
		$row['meta_projektname'] = str_replace("<right>",", ",$row['meta_projektname'] );
		$row['meta_projektname'] = str_replace("</right>","",$row['meta_projektname'] );
		$row['meta_projektname'] = preg_replace("#\s+,#",",",$row['meta_projektname'] );
                $xml .= "<h3>".$row['meta_projektname']."</h3>";
                $xml .= "<p>".$row['teaser']."<br/>";
                $xml .= '<a href="/'.$this->lang.'/projekte/auswahl/projekt/'.$row['uri'].'.html"><i18n:text>Details</i18n:text></a></p>';
            }
        }
        $xml.= '</div>';
        
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        return $dom;
    }

    
}

?>
