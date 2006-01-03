<?php

@include_once("bitlib/functions/debug.php");

class popoon_components_transformers_bxcmsfilter extends popoon_components_transformer {
    
    public $XmlFormat = 'DomDocument';
    
    /**
    * Constructor, creates xslt_process
    */
    function __construct ($sitemap) {
        parent::__construct($sitemap);
        
    }

    function DomStart(&$dom) {
        $position = $this->getAttrib('position');
        $filters = $this->getParameterDefault('filters');
        $collectionUri = $this->getParameterDefault('collectionUri');
        $filters = $filters->getFilters();
        if(!empty($filters)) {
            foreach($filters as $filter) {
                $filter->setCurrentRequest($collectionUri, '');
                $filter->DOMStart($dom, $position,$collectionUri, $this->getParameterDefault("filename"));
                if($filter->hasHeaders()) {
                    $this->sitemap->setHeaders($filter->getHeaders());
                }
            }
        }
        
    }
    
}


?>
