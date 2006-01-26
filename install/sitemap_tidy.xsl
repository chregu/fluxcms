<?xml version="1.0"?>
<xsl:stylesheet version="1.0" 


xmlns:map="http://apache.org/cocoon/sitemap/1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
 >
    <xsl:output encoding="utf-8" method="xml" />
    <xsl:param name="tidy" value="false"/>
    <xsl:template match="/">
    <xsl:comment>
     <xsl:value-of select="$tidy"/>
     </xsl:comment>
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

<xsl:template match="comment()">
<xsl:copy/>
</xsl:template>

</xsl:stylesheet>
