
<xsl:stylesheet
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  version="1.0">
  
<xsl:template match="/">
<xsl:apply-templates select="/bx/plugin">

</xsl:apply-templates>
</xsl:template>

<xsl:template match="/bx/plugin">

<xsl:copy-of select="*"/>

</xsl:template>  
  
  
  </xsl:stylesheet>