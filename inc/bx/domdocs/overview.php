<?php 

class bx_domdocs_overview extends domdocument {
    
    function __construct() {
        
       parent::__construct();
       $this->appendChild($this->createElement("section"));
    }
    
    function setTitle($title, $tabtitle = "Main") {
        $this->documentElement->appendChild($this->createElement("title","$title"));
            
        
         $this->addTab($tabtitle);
         
    }
    
    
    function addLink($title, $href = null, $helptext = null, $style = null ) {
        $link = $this->links->appendChild($this->createElement("link"));
        $link->setAttribute("href",$href);
        $link->appendChild($this->createElement("title",$title));
        if (is_array($helptext)) {
            if (isset($helptext['help'])) {
                $link->appendChild($this->createElement("helptext",$helptext['help']));
                unset($helptext['help']);
            }
            foreach ($helptext as $key=>$value) {
                $link->setAttribute($key,$value);   
            }
        }
        else if ($helptext) {
            $link->appendChild($this->createElement("helptext",$helptext));
        }
	if ($style) {
		$link->setAttribute("style",$style);
	}
    }
    
    function addTab($title) {
        $this->tab = $this->documentElement->appendChild($this->createElement("tab"));
        $this->tab->setAttribute("title",$title);
        $this->tab->setAttribute("id",bx_helpers_string::makeUri($title));
        
        $this->links = $this->tab->appendChild($this->createElement("links"));

    }
    
    
    function setIcon($title) {
         $this->documentElement->setAttribute("icon",$title);
    }
    
     
    function setType($type) {
         $this->documentElement->setAttribute("type",$type);
    }

 
    function setPath($title) {
         $this->documentElement->setAttribute("path",$title);
        
    }    
    function addSeperator($title = null) {
       $this->links = $this->tab->appendChild($this->createElement("links"));
       
    }
    
    
}
    