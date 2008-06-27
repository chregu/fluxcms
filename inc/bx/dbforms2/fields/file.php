<?php
// +----------------------------------------------------------------------+
// | Flux CMS                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2007 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Liip AG      <contact@liip.ch>                               |
// +----------------------------------------------------------------------+
//
// $Id$

/**
 * DOCUMENT_ME
 *
 * @package bx_dbforms2
 * @category 
 * @author Liip AG      <contact@liip.ch>
 */

/* example

<dbform:field name="pdf" uploaddir="/files/pdfs" type="file" descr="PDF Document"/>
        
*/

class bx_dbforms2_fields_file extends bx_dbforms2_field {
    
    public function __construct($name) {
        parent::__construct($name);
        $this->type = 'upload';
        $this->XMLName = 'input';
    }
    
    protected function getXMLAttributes() {
        return array('uploaddir' => $this->attributes['uploaddir']);
    }
    
    
    public function getConfigAttributes() {
        
        $ret =  parent::getConfigAttributes();
        $ret['uploaddir'] = 'string';
        
        return $ret;
    }
    
    public function getFileLocation() {
        return BX_OPEN_BASEDIR.$this->attributes['uploaddir'];
    }
    
    public function moveUploadedFile($file) {
        if ($file['tmp_name']) {
            $filename = bx_helpers_string::makeUri($file['name'],true);
            move_uploaded_file($file['tmp_name'],$this->getFileLocation().'/'.$filename);
        }
        
        $xml = '<html><head><script type="text/javascript">
        function init() {
            var field  = parent.dbforms2.mainform.getFieldByID("'. $this->name .'");';
            if ($file['tmp_name']) {
                $xml .= 'field.setValue("'.$filename.'");';
            }
            $xml .= '    
            field.onChange();
            field.closeIframe();   
        }
        </script>
        </head>
        <body onload="init()">
        </body>
        </html>';
        return $xml;
    }
}

?>