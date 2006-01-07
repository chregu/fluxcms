<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
        xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" exclude-result-prefixes="xhtml">
    <xsl:import href="static.xsl"/>

    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
    <xsl:variable name="overview" select="boolean((count(/bx/plugin[@name = '##pname##']/xhtml:div/xhtml:table/xhtml:tr) &gt; 2) or not(bx/plugin[@name = '##pname##']/xhtml:div/xhtml:table/xhtml:tr)) "/>
    <xsl:template name="content">
        <h1>
            <i18n:text>##pname##</i18n:text>
        </h1>
        
        <xsl:choose>
            
            <xsl:when test="$overview">
                <xsl:apply-templates select="/bx/plugin[@name = '##pname##']/xhtml:div/node()" mode="xhtml"/>
            
                </xsl:when>
            <xsl:otherwise>
                <xsl:variable name="datarow" select="/bx/plugin[@name = '##pname##']/xhtml:div/xhtml:table/xhtml:tr[position() = 2]"/>
               <table>
                    <xsl:for-each select="/bx/plugin[@name = '##pname##']/xhtml:div/xhtml:table/xhtml:tr/xhtml:th">
                        <tr>
                            <td>
                                <xsl:value-of select="."/>
                            </td>
                            <td>
                                <xsl:variable name="pos" select="position()"/>
                                <xsl:value-of select="$datarow/xhtml:td[position() = $pos]" />
                            </td>
                        </tr>
                    </xsl:for-each>
                </table>
            
            <p>
            <a href="./"><i18n:text>Back to Overview</i18n:text></a>
            </p>
            </xsl:otherwise>
            
        </xsl:choose>

    </xsl:template>

    <xsl:template match="xhtml:td[position() = 1]" mode="xhtml">

        <xsl:choose>
            <xsl:when test="$overview">
                <td>
                    <a href="./id{.}.html">
                        <xsl:apply-templates mode="xhtml"/>
                    </a>
                </td>
            </xsl:when>
        </xsl:choose>
    </xsl:template>


</xsl:stylesheet>
