<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
    xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" exclude-result-prefixes="xhtml">
    <xsl:import href="static.xsl"/>
    
    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

    <xsl:template name="content">
        <h1><i18n:text>##pname##</i18n:text></h1>
        <xsl:apply-templates select="/bx/plugin[@name = '##pname##']/node()" mode="xhtml"/>
        
    </xsl:template>
    

</xsl:stylesheet>
