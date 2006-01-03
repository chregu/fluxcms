
<xsl:stylesheet version="1.0" xmlns="http://purl.org/atom/ns#" xmlns:atom="http://purl.org/atom/ns#" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dc="http://www.purl.org/dc" xmlns:bxf="http://bitflux.org/functions" xmlns:rss="http://purl.org/rss/1.0/" xmlns:blog="http://bitflux.org/doctypes/blog" exclude-result-prefixes="rdf dc xhtml rss bxf blog">
    <xsl:template match="/">
        <entry xmlns="http://purl.org/atom/ns#">
            <xsl:apply-templates/>
        </entry>
    </xsl:template>

    <xsl:template match="xhtml:div[@class='post_content']">
        <atom:content type="application/xhtml+xml" xmlns="http://www.w3.org/1999/xhtml" >
            <xsl:apply-templates mode="xhtml"/>
        </atom:content>
    </xsl:template>
        <xsl:template match="xhtml:div[@class='post_content_extended']">
        <atom:content_extended type="application/xhtml+xml" xmlns="http://www.w3.org/1999/xhtml" >
            <xsl:apply-templates mode="xhtml"/>
        </atom:content_extended>
    </xsl:template>

    <xsl:template match="xhtml:span[@class='post_categories']">
        <xsl:text>
</xsl:text>
        <categories>
            <xsl:text>
</xsl:text>
            <xsl:for-each select="xhtml:span/xhtml:a">
                <category><xsl:attribute name="xml:id"><xsl:value-of select="../@id"/></xsl:attribute>
                    <xsl:value-of select="."/>
                </category>
                <xsl:text>
</xsl:text>
            </xsl:for-each>
        </categories>
        
        <xsl:text>
</xsl:text>
</xsl:template>
 <xsl:template match="xhtml:div[@class='post_tags']">
        <xsl:text>
</xsl:text>
        <tags><xsl:for-each select="xhtml:span/xhtml:a">
                    <xsl:choose>
                        <xsl:when test="contains(.,' ')">"<xsl:value-of select="."/>"</xsl:when>
                        <xsl:otherwise><xsl:value-of select="."/></xsl:otherwise>
                    </xsl:choose>
                    <xsl:if test="position() != last()">
                    <xsl:text> </xsl:text>
                    </xsl:if>
            </xsl:for-each>
        </tags>
        <xsl:text>
</xsl:text>

    </xsl:template>

    <xsl:template match="xhtml:div[@class='entry']">
        <created>
            <xsl:value-of select="@blog:post_date_iso"/>
        </created>
        <id>
            <xsl:value-of select="substring-after(@id,'entry')"/>
        </id>
        <uri>
            <xsl:value-of select="@blog:post_uri"/>
        </uri>
        <status>
            <xsl:value-of select="@blog:post_status"/>
        </status>
          <comment_mode>
            <xsl:value-of select="@blog:post_comment_mode"/>
        </comment_mode>
        <xsl:apply-templates/>
    </xsl:template>

    <xsl:template match="xhtml:h2[@class='post_title']">
        <title>
            <xsl:value-of select="."/>
        </title>
    </xsl:template>

    <xsl:template match="xhtml:span[@class='post_author']">
        <author><name>
            <xsl:value-of select="."/>
        </name></author>
    </xsl:template>

    <xsl:template match="xhtml:span[@class='post_date']">
        
    </xsl:template>

    <xsl:template match="xhtml:span[@class='post_comments_count']">
        <commentcount>
            <xsl:value-of select="xhtml:a"/>
        </commentcount>
    </xsl:template>

    <xsl:template match="xhtml:span[@class='post_uri']">
        <link>
            <xsl:value-of select="xhtml:a/@href"/>
        </link>
    </xsl:template>

    <xsl:template match="@*" mode="xhtml">
        <xsl:copy/>
    </xsl:template>


    <xsl:template match="@blog:*" mode="xhtml">

    </xsl:template>

    <xsl:template match="*" mode="xhtml">
        <xsl:copy>
            <xsl:apply-templates select="@*" mode="xhtml"/>
            <xsl:apply-templates mode="xhtml"/>

        </xsl:copy>
    </xsl:template>

</xsl:stylesheet>