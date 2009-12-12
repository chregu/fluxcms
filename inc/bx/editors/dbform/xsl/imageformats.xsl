<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template name="imageformats">
    <xsl:param name="id" />
    <xsl:param name="tag" />
    <xsl:variable name="texts">JPEG|GIF|PNG|</xsl:variable>
    <xsl:variable name="values">jpeg|gif|png|</xsl:variable>

    <select size="1" name="{$tag}">
    <option value="">none</option>
    <xsl:call-template name="SelectMaker">
                    <xsl:with-param name="texts" select="$texts"/>
                    <xsl:with-param name="values" select="$values"/>
                    <xsl:with-param name="id" select="$id"/>
     </xsl:call-template>

    </select>
</xsl:template>


</xsl:stylesheet>

