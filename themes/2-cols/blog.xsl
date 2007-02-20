<xsl:stylesheet version="1.0"

 xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
xmlns:blog="http://bitflux.org/doctypes/blog" xmlns:bxf="http://bitflux.org/functions" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" xmlns:php="http://php.net/xsl" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:rss="http://purl.org/rss/1.0/" xmlns:dc="http://purl.org/dc/elements/1.1/" exclude-result-prefixes="php blog bxf xhtml rdf rss dc i18n">
    <xsl:import href="../standard/common.xsl"/>
    <xsl:import href="master.xsl"/>
    <xsl:import href="../standard/plugins/blog.xsl"/>

    <!-- 
    
    if you want to change some of the blog xsl-templates, look at
    themes/standard/plugins/blog.xsl, copy those you want to change into this file
    and change them.
    
    This will overwrite the standard templates.
    
    We do not advise to change stuff in themes/standard/, but to overwrite/extend them 
    here, as this will make future upgrades much easier. 
    
    -->
    
 
</xsl:stylesheet>
