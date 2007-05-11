<?php

class bx_metadatas_text_select extends bx_metadata {

    protected $size = 45;
    protected $maxLength = 0;
    protected $node = ""; 
    
    public function __construct() {
        parent::__construct();
    }
    
    public function setSize($size) {
        $this->size = $size;
    }

    public function setMaxLength($maxLength) {
        $this->maxLength = $maxLength;
    }
    
    public function serializeToDOM() {
        $dom = new domDocument();
        
        $textField = $dom->createElement('metadata');
        $textField->setAttribute('type', 'select');
        $textField->setAttribute('size', $this->size);
        if ($this->maxLength > 0) {
            $textField->setAttribute('maxLength', $this->maxLength);
        }
        $s = $textField->appendChild($dom->createElement("select"));
        foreach($this->getXpathNodes("bxcms:metadata/bxcms:option",$this->node) as $node) {
            $o = $dom->createElement("option");
            $o->setAttribute("value",$node->getAttribute("value"));
            $o->appendChild($dom->createTextNode($node->textContent));
            $s->appendChild($o);
        }

        return $textField;
    }

    public function isChangeable() {
        return TRUE;
    }
    
    protected function getXPathNodes($xpath, $ctxt ) {
        $xp = new Domxpath($ctxt->ownerDocument);
        $xp->registerNamespace("bxcms","http://www.flux-cms.org/propertyconfig");
        if ($ctxt) {
            return  $xp->query($xpath, $ctxt);    
        } else {
            return  $xp->query($xpath);
        }
    }
    
}

?>
