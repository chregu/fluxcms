<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" 
xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml"
xmlns="http://www.w3.org/1999/xhtml" 
>
    <xsl:import href="{{blog.xsl}}"/>
    
    <xsl:output encoding="utf-8" method="xml"/>
    
    
    <xsl:template match="/" priority="100">
    
    
    <xsl:apply-templates select="/bx/plugin/xhtml:html/xhtml:body/xhtml:div/xhtml:div/xhtml:div/*" mode="xhtml" />


    </xsl:template>
  
</xsl:stylesheet>
