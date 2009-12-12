<?php
        
/**
*
* this is a silly test filter which only replaces the documents 
* title tag with some garbage
*
*/

class bx_filters_test extends bx_filter {
    
    static private $instance = array();
    
    public static function getInstance($mode) {
        if (!self::$instance) {
            self::$instance = new bx_filters_test($mode);
        } 
        return self::$instance;
    }   
    
    public function preHTML(&$dom) {
        $ne = $dom->createElement('div');
        $tn = $dom->createTextNode('Testfilter was here');
        $ne->appendChild($tn);
        $dom->documentElement->appendChild($ne);
    }

    public function postHTML(&$dom) {
        $xp = new DomXPath($dom);
        $xp->registerNameSpace('html', 'http://www.w3.org/1999/xhtml');
        $tnl = $xp->query('//html:title');
        
        $tn = $tnl->item(0);
        if(!empty($tn)) {
            $title = $this->getParameter('title');
            $ttn = $tn->firstChild;
            $ntn = $dom->createTextNode($title);
            $tn->replaceChild($ntn, $ttn);
        }
    }

}

?>
