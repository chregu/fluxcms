<?php

class bx_metadata {

    protected $value;
    
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

}

?>
