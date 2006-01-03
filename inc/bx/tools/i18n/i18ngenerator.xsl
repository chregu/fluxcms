<?xml version="1.0" encoding="utf8" ?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
    
>
<xsl:output indent="yes"/>
<xsl:template match="/">
<catalogue xml:lang="de">
<xsl:for-each select="//i18n:text">
    <message key="{.}"><xsl:value-of select="."/></message>
</xsl:for-each>
</catalogue>
</xsl:template>


</xsl:stylesheet>