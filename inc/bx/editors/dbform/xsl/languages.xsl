<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">


<!-- 
template languages : 
ISO 639-1 the Alpha-2 code according to 
http://lcweb.loc.gov/standards/iso639-2/langhome.html 
-->

<xsl:template name="languages">
    <xsl:param name="id" />
    <xsl:param name="tag" />
     <xsl:param name="texts">English (en)|Deutsch (de)|Francais (fr)|Italiano (it)|Espanol (es)|</xsl:param>
    <xsl:param name="values">en|de|fr|it|es|</xsl:param>

    <select size="1" name="{$tag}">
    <option>none</option>
    <xsl:call-template name="SelectMaker">
                    <xsl:with-param name="texts" select="$texts"/>
                    <xsl:with-param name="values" select="$values"/>
                    <xsl:with-param name="id" select="$id"/>
     </xsl:call-template>

    </select>
</xsl:template>



</xsl:stylesheet>
     
