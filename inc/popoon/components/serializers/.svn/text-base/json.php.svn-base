<?php
/**
* Outputs the XML-Data as JSON object.
*
* Note: it is data-centric versus document centric,
* mixed content (markup) will get lost, "text<e>text</e>text"
* as well as mixed order "<a/><b/><a/><b/>"
*
* Rules:
* 1 <e/> ........................... "e": null
* 2 <e>text</e> .................... "e": "text"
* 3 <e name="value"/> .............. "e": { "@name": "value"}
* 4 <e name="value">text</e> ....... "e": { "@name": "value", "#text": "text" }
* 5 <e><a>text</a><b>text</b></e> .. "e": { "a": "text", "b": "text" }
* 6 <e><a>text</a><a>text</a></e> .. "e": { "a": ["text", "text"] }
* 7 <e>text<a>text</a></e> ......... "e": { "#text": "text", "a": "text" }
*
* see http://www.xml.com/pub/a/2006/05/31/converting-between-xml-and-json.html
*/
class popoon_components_serializers_json extends popoon_components_serializer {
    public $XmlFormat = "Own";
    protected $contentType = "text/plain;";

    function __construct (&$sitemap) {
        $this->sitemap = &$sitemap;
    }

    function init($attribs) {
        parent::init($attribs);
    }

    function DomStart(&$dom)
    {
        $root = $dom->documentElement;
        print "{'".$root->nodeName."':".$this->serializeNode($root)."}";
    }

    function serializeNode($node) {
        $name = $node->nodeName;
        error_log("parseNode: $name");
        $children = $node->hasChildNodes() ? $node->childNodes : null;
        $attributes = $node->hasAttributes() ? $node->attributes : null;

        if( ! $attributes && ! $children ) {
            return "null";
            }

        if( ! $attributes && $children->length==1 ) {
            $value = $this->normalizeSpace($children->item(0)->nodeValue);
            return "'$value'";
            }

        // child_name = array( parsed_values )
        $values = array();
        if($children) {
            foreach($children as $child) {
                switch($child->nodeType) {
                    case XML_TEXT_NODE:
                        $value = $this->normalizeSpace($child->nodeValue);
                        if( $value != '') {
                            if( !isset($values['#text']) ) { $values[$child->nodeName] = array(); }
                            array_push($values['#text'], $value );
                            }
                        break;
                    case XML_ELEMENT_NODE:
                        if( !isset($values[$child->nodeName]) ) { $values[$child->nodeName] = array(); }
                        array_push($values[$child->nodeName], $this->serializeNode($child) );
                        break;
                    }
                }
            }

        $servalues = array();

        if( $attributes ) {
            foreach($attributes as $attr) {
                array_push($servalues,"'@".$attr->nodeName."':'".$attr->nodeValue."'");
                }
            }

        foreach($values as $c_name=>$c_val) {
            if( sizeof($c_val) == 1 ) {
                array_push($servalues, "'".$c_name."':".$c_val[0]);
                }
            else {
                array_push($servalues, "'".$c_name."':[".join(',',$c_val)."]");
                }
            }

        return '{'.join(',',$servalues).'}';
    }

    function normalizeSpace($string) {
        $string = preg_replace('/^\s*/','',$string);
        $string = preg_replace('/\s*$/','',$string);
        $string = preg_replace('/\s+$/',' ',$string);
        return $string;
        }
}


?>
