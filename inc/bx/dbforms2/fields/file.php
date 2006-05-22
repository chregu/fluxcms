<?php
/**
*
* @package bx_dbforms2
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
            var field  = parent.dbforms2.form.getFieldByID("'. $this->name .'");';
            if ($file['tmp_name']) {
                $xml .= 'field.setValue("'.$filename.'");';
            }
            $xml .= '    field.closeIframe();
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