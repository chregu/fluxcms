<xsl:stylesheet version="1.0" 
 xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
xmlns:blog="http://bitflux.org/doctypes/blog" xmlns:bxf="http://bitflux.org/functions" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" xmlns:php="http://php.net/xsl" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:rss="http://purl.org/rss/1.0/" xmlns:dc="http://purl.org/dc/elements/1.1/" exclude-result-prefixes="php blog bxf xhtml rdf rss dc i18n">
    <xsl:import href="master.xsl"/>
    <xsl:import href="../standard/common.xsl"/>
    <xsl:import href="../standard/plugins/blog.xsl"/>

    <xsl:param name="ICBM" select="php:functionString('bx_helpers_config::getOption','ICBM')"/>
    
    <xsl:variable name="blogname" select="php:functionString('bx_helpers_config::getOption','blogname')"/>
    <xsl:variable name="blogroot" select="concat(substring($webroot,1,string-length($webroot)-1),$collectionUri)"/>
    <xsl:output encoding="utf-8" method="xml"/>
    <xsl:variable name="singlePost">
        <xsl:choose>
            <xsl:when test="count(/bx/plugin[@name = 'blog']/xhtml:html/xhtml:body/xhtml:div[@class='entry']) &lt;=1">true</xsl:when>
            <xsl:otherwise>false</xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
<xsl:variable name="dctitle">
    <xsl:choose>
            <xsl:when test="string-length($blogname) &gt; 0">
                <xsl:value-of select="$blogname"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$sitename"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>

    
    <xsl:template name="contentRight">
         <xsl:apply-templates select="/bx/plugin[@name = 'blog']/xhtml:html/sidebar[@sidebar=2]"/>
    </xsl:template>
    
    <xsl:template name="leftnavi">
        
        <xsl:apply-templates select="/bx/plugin[@name = 'blog']/xhtml:html/sidebar[@sidebar=1]"/>
        
    </xsl:template>
    
  
</xsl:stylesheet>
