<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:php="http://php.net/xsl" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml">
    <xsl:output encoding="utf-8" method="xml" />
    <xsl:param name="url" select="'/'"/>
    <xsl:param name="dataUri" select="''"/>
    <xsl:param name="webroot" select="''"/>
    <xsl:variable name="theme" select="php:functionString('bx_helpers_config::getProperty','theme')"/>
    <xsl:variable name="themeCss" select="php:functionString('bx_helpers_config::getProperty','themeCss')"/>
    
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
    
    <xsl:template match="webroot">
    <xsl:value-of select="$webroot"/>
    </xsl:template>
    
 <xsl:template match="theme">
 
    <xsl:value-of select="$theme"/>
    </xsl:template>
    
     <xsl:template match="themeCss">
    <xsl:value-of select="$themeCss"/>
    </xsl:template>

</xsl:stylesheet>
