<?php

class bx_helpers_rss {
    
    /* DOCU
    
    
    Put that into your .configxml in your blog collection (are any other)
    
    <plugins inGetChildren="false">
        <extension type="xml"/>
        <file name="merged"/>
        <parameter name="output-mimetype" type="pipeline" value="text/xml"/>
        <parameter type="pipeline" name="xslt" value="mergerss.xsl"/>
        <plugin type="empty">
        </plugin>
    </plugins>
    
    
    Make an mergerss.xsl with following:
    
    
    <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns:php="http://php.net/xsl">
        <xsl:output encoding="utf-8" method="xml" />
        <xsl:template match="/">
            <xsl:copy-of select="php:function('bx_helpers_rss::mergeRSS','der titel','die description','http://der.link.ch','http://blog.bitflux.ch/rss.xml','http://cthulhu.freeflux.net/blog/rss.xml')"/>
        </xsl:template>
    </xsl:stylesheet>
    
   and put it in your themes folder
    
   and now you can call it with http://example.org/blog/merged.xml
   
   * adjust to your needs * :)
    
    */
    static function mergeRSS($title,$description,$link) {
        $numargs = func_num_args();
        $sc = popoon_helpers_simplecache::getInstance();
        $dom = null;
        for($x = 3; $x < $numargs; $x++){
            $arg = func_get_arg($x);
            $rss = $sc->simpleCacheHttpRead($arg,600);
            if (!$dom) {
                $dom = new domDocument();
                $dom->loadXML($rss);
                $domxpath= new DomXPath($dom);
                $res = $domxpath->query("/rss/channel");
                $channel = $res->item(0);
            } else {
                $dom2 = new domDocument();
                $dom2->loadXML($rss);
                $domxpath2 = new DomXPath($dom2);
                $results = $domxpath2->query("/rss/channel/item");
                foreach($results as $result){
                    $channel->appendChild($dom->importNode($result,true));
                }
            }
        }
        
        $xsldom = new domdocument();
        if (!$xsldom->load(dirname(__FILE__)."/xsl/rssmerge.xsl")) {
            return $dom;
        }
        
        $proc = new xsltprocessor();
        $proc->importStylesheet($xsldom);
        $domxpath3 = new DomXPath($dom);
        $proc->setParameter("","title",$title);
        $proc->setParameter("","description",$description);
        $proc->setParameter("","link",$link);
        $out = $proc->transformToDoc($dom);
        return $out;
        
    }
    
}