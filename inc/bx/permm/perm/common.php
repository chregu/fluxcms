<?php

abstract class bx_permm_perm_common {

    
    /**
     * db dsn
     *
     * @access   protected
     * @var      array
     */
    protected $dsn = array();
    
    /**
     * auth table
     *
     * @access  protected
     * @var     string
     */
    protected $perm_table = '';
    
    /**
     * auth table username column
     *
     * @access  protected
     * @var     string
     */
    protected $perm_useridcol = '';
    
    /**
     * auth table password tolumn
     *
     * @access  protected
     * @var     string
     */
    protected $perm_propertiesidcol = '';
    

    public function isEditable()
    {
    	return false;	
    }    
    
    protected function __construct($options) {
        
        if (is_array($options)) {
            
            foreach ($options as $name => $value) {
                if (isset($this->$name)) {
                    $this->$name = $value;
                }
            }
        }
        
    }
    
    

}



?>