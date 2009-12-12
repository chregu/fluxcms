<?php

class bx_filtermanager {
    
    protected $filters = array();
    
    //static protected $instance = null;
    
   /* public static function getInstance() {
        
        if (!isset(self::$instance)) {
            self::$instance = new bx_filtermanager($mode);
        } 
        return self::$instance;
    }
    */
    
    public function __construct() {
        
    }
    
    
    public function addFilter($filter,$name) {
        $this->filters[$name] = $filter;
    }
    
    public function getFilters() {
        return $this->filters;   
    }
}