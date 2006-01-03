<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template name="SelectMaker">
    <xsl:param name="id" />
    <xsl:param name="texts"/>
    <xsl:param name="values"/>
    <xsl:param name="static"/>

    <xsl:choose>
        <xsl:when test="$values != ''">
            <xsl:variable name="firstValues" select="substring-before($values,'|')"/>
            <xsl:variable name="restValues" select="substring-after($values,'|')"/>
            <xsl:variable name="firstTexts" select="substring-before($texts,'|')"/>
            <xsl:variable name="restTexts" select="substring-after($texts,'|')"/>
            <xsl:choose>
                <xsl:when test="$static = 1">
                    <xsl:if test="$firstValues = $id">
                        <xsl:value-of select="$firstTexts"/>
                    </xsl:if>
                </xsl:when>
                <xsl:otherwise>
                    <option value="{$firstValues}">
                        <xsl:if test="$firstValues = $id">
                             <xsl:attribute name="selected">selected</xsl:attribute>
                        </xsl:if>   
                        <xsl:value-of select="$firstTexts"/>
                     </option>
        
                </xsl:otherwise>
            </xsl:choose>
                        <xsl:call-template name="SelectMaker">
                        <xsl:with-param name="texts" select="$restTexts"/>
                        <xsl:with-param name="values" select="$restValues"/>
                        <xsl:with-param name="id" select="$id"/>
                        <xsl:with-param name="static" select="$static"/>
                    </xsl:call-template>
        </xsl:when>
    </xsl:choose>
</xsl:template>


<xsl:template name="TargetMaker">


    <xsl:param name="texts"/>
    <xsl:param name="values"/>

    <xsl:choose>
        <xsl:when test="$values != ''">
            <xsl:variable name="firstValues" select="substring-before($values,'|')"/>
            <xsl:variable name="restValues" select="substring-after($values,'|')"/>
            <xsl:variable name="firstTexts" select="substring-before($texts,'|')"/>
            <xsl:variable name="restTexts" select="substring-after($texts,'|')"/>
            <a href="#{$firstValues}">
                <xsl:value-of select="$firstTexts"/>
             </a>
            <xsl:call-template name="TargetMaker">
                <xsl:with-param name="texts" select="$restTexts"/>
                <xsl:with-param name="values" select="$restValues"/>

            </xsl:call-template>
        </xsl:when>
    </xsl:choose>
</xsl:template>



</xsl:stylesheet>

