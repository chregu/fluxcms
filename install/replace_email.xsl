<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
xmlns:xhtml="http://www.w3.org/1999/xhtml"
xmlns="http://www.w3.org/1999/xhtml"
xmlns:forms="http://bitflux.org/forms"
exclude-result-prefixes="forms xhtml"
>
    <xsl:output encoding="utf-8" method="xml"/>
    <xsl:param name="email" value="''"/>
     <xsl:param name="domain" value="''"/>
    <xsl:template match="/">

        <xsl:apply-templates/>


    </xsl:template>
    
    <xsl:template match="forms:parameter[@name='emailTo']">
        <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
            <xsl:attribute name="value"><xsl:value-of select="$email"/></xsl:attribute>
            <xsl:apply-templates/>
        </xsl:copy>
    </xsl:template>
    
       <xsl:template match="forms:parameter[@name='subjectTemplateKey']">
        <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
            <xsl:attribute name="value">Contact form from <xsl:value-of select="$domain"/></xsl:attribute>
            <xsl:apply-templates/>
        </xsl:copy>
    </xsl:template>
    <xsl:template match="xhtml:td[not(node())]">
    <td><xsl:text> </xsl:text></td>
    </xsl:template>
 
    <xsl:template match="*">
        <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
            <xsl:apply-templates/>
        </xsl:copy>
    </xsl:template>


</xsl:stylesheet>
