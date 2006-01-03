<?php

class bx_streams_db2xml extends bx_streams_buffer {
    
    function contentOnRead($path) {
        $db2xml = new XML_db2xml(NULL, NULL, 'Extended');
        $xml = '';
        
        $options = array(
            'formatOptions' => array (
                'xml_seperator' => '',
                'element_id' => 'id'
            )
        );
        $db2xml->Format->SetOptions($options);

        if(preg_match('/\/(.*)[\/]/', $path, $matches)) {
            $table = $matches[1];
        }
       
        $where = $this->getParameter('where');
        if(!empty($table)) {
            $query = "select * from $table";
            if(!empty($where)) {
                $query .= " where $where";
            }
            $res = $GLOBALS['POOL']->db->query($query);
            if (PEAR::isError($res) || $res->numRows() == 0) {
                 $xml = "<nothingFound/>";    
            } else {
                $xml = $db2xml->getXML($res);
            }
        } 
        return $xml;
    }

    function contentOnWrite($content) {
    }
    
}

