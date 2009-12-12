
<xsl:stylesheet version="1.0" xmlns:php="http://php.net/xsl" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml">
    <xsl:output encoding="utf-8" method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>


    <xsl:template match="/">
        <form name="bxinstall" action="" method="post">

            <table class="page" width="550px">
                <xsl:apply-templates select="/properties/*"/>

            </table>
            <br/>
            WARNING: It will overwrite all your Flux CMS content (in the database, data/ directory and more) with the default content. So be careful.
            <br/>
            <input type="submit" value="Start Installation"/>
        </form>
    </xsl:template>

    <xsl:template match="text">

        <tr>
            <td colspan="3" style="padding-left: 20px; font-style: italic;">
                <xsl:copy-of select="*|text()"/>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="title">

        <tr>
            <td colspan="3" >
                <h2 style="margin-top: 20px;" class="page">
                    <xsl:value-of select="."/>
                </h2>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="property">
        <tr class="page">

            <td width="50%">
                <xsl:value-of select="."/>


            </td>

            <td>
                <input type="text" name="{@name}">
                    <xsl:attribute name="value">
                        <xsl:choose>
                            <xsl:when test="starts-with(@value,'php:')">
                                <xsl:value-of select="php:functionString(substring-after(@value,'php:'))"/>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:value-of select="@value"/>
                            </xsl:otherwise>
                        </xsl:choose>
                    </xsl:attribute>
                </input>
            </td>
            <td>
            [<xsl:value-of select="@name"/>]  <xsl:if test="@required">
    *
    </xsl:if>
            </td>
        </tr>
    </xsl:template>

</xsl:stylesheet>