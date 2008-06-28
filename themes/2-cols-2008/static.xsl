<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" exclude-result-prefixes="xhtml">
    <xsl:import href="../standard/common.xsl"/>
    <xsl:import href="master.xsl"/>

    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
    <!-- content --> 
    <xsl:template name="content">
        <xsl:variable name="body" select="/bx/plugin[@name='xhtml']/xhtml:html/xhtml:body"/>
        <xsl:choose>
            <!-- if there is a <div id = 'content'> just take that -->
            <xsl:when test="$body/xhtml:div[@id = 'content']">
                <xsl:apply-templates select="$body/xhtml:div[@id = 'content']/node()" mode="xhtml"/>
            </xsl:when>
            <!-- otherwise take the whole body content -->
            <xsl:otherwise>
                <!-- <xsl:copy-of select="$body/*|$body/text()"/> -->
                <xsl:apply-templates select="$body/node()" mode="xhtml"/>
            </xsl:otherwise>
        </xsl:choose>
        

    </xsl:template>
    
    <!-- add everything from head to the output -->
    <xsl:template name="html_head">
        <xsl:apply-templates select="/bx/plugin[@name='xhtml']/xhtml:html/xhtml:head/node()" mode="xhtml"/>
    </xsl:template>
    
    <!-- except the title -->
    <xsl:template match="xhtml:head/xhtml:title" mode="xhtml">
    </xsl:template>

    <!-- except the links -->
    <xsl:template match="xhtml:head/xhtml:link" mode="xhtml">
    </xsl:template>
    
    <!-- do not output meta tags without @content -->
    <xsl:template match="xhtml:head/xhtml:meta[not(@content)]" mode="xhtml">
    </xsl:template>
    
    <xsl:template name="body_attributes">
    <xsl:apply-templates select="/bx/plugin[@name='xhtml']/xhtml:html/xhtml:body/@*" mode="xhtml"/>
    </xsl:template>
    
    
</xsl:stylesheet>
