<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml">
    <xsl:output encoding="utf-8" method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
    <xsl:param name="url" select="'/'"/>
    <xsl:param name="dataUri" select="'/'"/>
     <xsl:param name="lang" select="''"/>
    <xsl:param name="webroot" select="'/'"/>
    <xsl:param name="template" select="'default.xhtml'"/>
    <xsl:template match="/">

        <xsl:apply-templates/>
    </xsl:template>


    <xsl:template match="*">
        <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
            <xsl:apply-templates/>
        </xsl:copy>
    </xsl:template>
    
    
 
    <xsl:template match="/xhtml:html/xhtml:head">
    <meta name="bxeNS" content="xhtml=http://www.w3.org/1999/xhtml"/>
   <script src="{$webroot}webinc/bxe/bxeLoader.js" >
   </script>
   <link href="{$webroot}webinc/bxe/css/editor.css" rel="stylesheet" media="screen" type="text/css"/>
  
   <script>
   <xsl:variable name="dtemplate">
        <xsl:choose>
            <xsl:when test="not($template='')"><xsl:value-of select="$template"/></xsl:when>
            <xsl:otherwise><xsl:text>default.xhtml</xsl:text></xsl:otherwise>
        </xsl:choose>
   </xsl:variable>
   var template ='<xsl:value-of select="$dtemplate"/>' 
   var params = new Array();
   params["xmlfile"] = '<xsl:value-of select="$webroot"/>admin/content/<xsl:value-of select="$url"/>?template=<xsl:value-of select="$dtemplate"/>';
   params["xhtmlfile"] = '<xsl:value-of select="$webroot"/><xsl:value-of select="substring-before(substring-before($url,'.xhtml'),'.')"/>.html?admin=1&amp;template=<xsl:value-of select="$dtemplate"/>';
   params["exit"] = '<xsl:value-of select="$webroot"/>admin/overview/<xsl:value-of select="$url"/>';
  
   </script>
    <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="xhtml:body">

     <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
            <xsl:attribute name="onload">bxe_start('<xsl:value-of select="$webroot"/>admin/bxe/config.xml', false, params);</xsl:attribute>
            <xsl:apply-templates/>
       </xsl:copy>
        
    
    
    </xsl:template>
    

</xsl:stylesheet>
