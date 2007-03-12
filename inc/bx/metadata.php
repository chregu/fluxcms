<?php

class bx_metadata {

    protected $value;
    
    /**
     * table of db datasource
     * 
     * @var
     * @access protected  
     */
    protected $table = '';
    
    
    /**
     * idfield of db datasource table
     * 
     * @var 
     * @access protected
     */
    protected $idfield = '';
    
    
    /**
     * namefield of db datasource table
     *
     * @var 
     * @access protected
     */
    protected $namefield = '';
    
    
    /**
     * displayfield of db datasource table
     * 
     * @var
     * @access protected
     */
    protected $displayfield = '';
    
    
    /**
     * order of db datasource table
     * 
     * @var 
     * @access protected
     */
    protected $order = '';
    
    
    public function __construct() {
    }

    /**
    * serializes the metadata object to a dom-object which can passed through to xslt
    *
    * @return object DomDocument
    */
    public function serializeToDOM() {
        return FALSE;
    }
    
    /**
    * adds a validator object
    *
    * @return boolean TRUE on success, FALSE otherwise
    */
    public function addValidator($validator) {
        // not implemented at the moment
        return FALSE;
    }

    /**
    * runs all validators and returns a validatorResult object
    * this method should be reviewed later - it is currently only decoration :)
    *
    * @return object validatorResult
    */
    public function validate() {
        // not implemented at the moment
        return TRUE;
    }
    
    public function setValue($value) {
        $this->value = $value;
    }
    
    public function getValue() {
        return $this->value;
    }

    public function isChangeable() {
        return FALSE;
    }
    
    
    public function setProperties($props = array()) {
        if (sizeof($props) > 0 ) {
            foreach($props as $propn => $propv) {
                if (isset($this->$propn)) {
                    $this->$propn = $propv;
                }
            }
        }
        
        return null;
    }
    
}

?>
