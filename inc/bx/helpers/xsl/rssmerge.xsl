<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dc="http://purl.org/dc/elements/1.1/">
   
<xsl:param name="title"/>
<xsl:param name="description"/>
<xsl:param name="link"/>
<xsl:template match="/">
        <xsl:apply-templates/>
    </xsl:template>

    <xsl:template match="item">
        <xsl:copy-of select="."/>
    </xsl:template>
    
    <xsl:template match="title">
    <title><xsl:value-of select="$title"/></title>
    </xsl:template>
    
    <xsl:template match="description">
    <description><xsl:value-of select="$description"/></description>
    </xsl:template>
    
    <xsl:template match="link">
    <link><xsl:value-of select="$link"/></link>
    </xsl:template>
    
    <xsl:template match="channel">
        <xsl:copy>
            <xsl:apply-templates select="*[local-name() != 'item']"/>
            <xsl:apply-templates select="item">
                <xsl:sort select="dc:date" order="descending" data-type="text"/>
            </xsl:apply-templates>
        </xsl:copy>
    </xsl:template>

    <xsl:template match="*">
        <xsl:copy>
            <xsl:copy-of select="@*"/>
            <xsl:apply-templates/>
        </xsl:copy>
    </xsl:template>
</xsl:stylesheet>
