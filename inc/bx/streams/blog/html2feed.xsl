<xsl:stylesheet version="1.0" xmlns:i18n="http://apache.org/cocoon/i18n/2.1" xmlns="http://purl.org/atom/ns#" xmlns:atom="http://purl.org/atom/ns#" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dc="http://www.purl.org/dc" xmlns:bxf="http://bitflux.org/functions" xmlns:rss="http://purl.org/rss/1.0/" xmlns:blog="http://bitflux.org/doctypes/blog" exclude-result-prefixes="rdf dc xhtml rss bxf blog">
   <xsl:param name="webroot">/</xsl:param>
   <xsl:param name="break"><xsl:text>
</xsl:text></xsl:param>
   <xsl:template match="/">
       <feed version="0.3" xmlns="http://purl.org/atom/ns#">
            <xsl:apply-templates/>
       </feed>
    </xsl:template>

    <xsl:template match="xhtml:div[@class='post_content']">
        <!--<atom:content type="application/xhtml+xml" xmlns="http://www.w3.org/1999/xhtml" >
            <xsl:apply-templates mode="xhtml"/>
        </atom:content>-->
    </xsl:template>

    <xsl:template match="xhtml:span[@class='post_categories']">
        <xsl:text>
</xsl:text>
        <categories>
            <xsl:text>
</xsl:text>
            <xsl:for-each select="xhtml:span/xhtml:a">
                <category><xsl:attribute name="id"><xsl:value-of select="../@id"/></xsl:attribute>
                    <xsl:value-of select="."/>
                </category>
                <xsl:text>
</xsl:text>
            </xsl:for-each>
        </categories>
        <xsl:text>
</xsl:text>

    </xsl:template>

    <xsl:template match="xhtml:div[@class='entry']">
    <entry>
        <xsl:value-of select="$break"/>
        <created localtime="{xhtml:div[@class='post_meta_data']/xhtml:span[@class='post_date']}">
            <xsl:value-of select="@blog:post_date_iso"/>
        </created><xsl:value-of select="$break"/>
        <id>
            <xsl:value-of select="substring-after(@id,'entry')"/>
        </id>
        <xsl:value-of select="$break"/>
        <uri>
            <xsl:value-of select="@blog:post_uri"/>
        </uri>
        <xsl:value-of select="$break"/>
        <status>
            <xsl:value-of select="@blog:post_status"/>
        </status>
        <link rel="service.edit">
            <xsl:value-of select="$webroot"/>admin/content/<xsl:value-of select="@blog:post_uri"/>.html
        </link>
		<xsl:value-of select="$break"/>
		<lang>
            <xsl:value-of select="@blog:blog_lang"/>
        </lang>
        <xsl:value-of select="$break"/>
        
        <xsl:apply-templates/>
        <xsl:value-of select="$break"/>
        </entry>
    </xsl:template>

    <xsl:template match="xhtml:h2[@class='post_title']">
        <title>
            <xsl:value-of select="."/>
        </title>
        <xsl:value-of select="$break"/>
    </xsl:template>

    <xsl:template match="xhtml:span[@class='post_author']">
        <author><name>
            <xsl:value-of select="."/>
        </name></author>
        <xsl:value-of select="$break"/>
    </xsl:template>

    <xsl:template match="xhtml:span[@class='post_date']">
        
    </xsl:template>
    
    <xsl:template match="xhtml:span[@class='post_comments_count']">
        <commentcount>
            <xsl:value-of select="xhtml:a"/>
        </commentcount>
        <xsl:value-of select="$break"/>
    </xsl:template>

    <xsl:template match="xhtml:span[@class='post_uri']">
        <link>
            <xsl:value-of select="xhtml:a/@href"/>
        </link>
    </xsl:template>
    
       <xsl:template match="xhtml:div[@class='blog_pager']">
        <xsl:copy-of select="."/>
        <!--
       <div class="blog_pager" blog:start="{@blog:start}" blog:end="{@blog:end}" blog:total="{@blog:total}">
            <span class="blog_pager_prevnext">
            <a href="{xhtml:span/xhtml:a/@href}">
                <i18n:text>
                    <xsl:value-of select="xhtml:span/xhtml:a/i18n:text"/>
                </i18n:text>
            </a>
            </span>
            <span class="blog_pager_counter">
                <xsl:value-of select="xhtml:span[@class='blog_pager_counter']"/>
            </span>
        </div>-->
       <!-- here should come the following
        
        <link rel="next" type='application/x.atom+xml'
     title="Next 20 Entries" href="http://.."/>
  <link rel="prev" type='application/x.atom+xml'
     title="Previous 20 Entries" href="http://.."/>
  <link rel="comments" type='application/x.atom+xml'
     title="Last 20 Comments" href="http://.."/>
  <link rel='service.post' type='application/x.atom+xml" 
     title="Create a new post on intertwingly.net" href=".."/>
from http://bitworking.org/news/AtomAPI_Quick_Reference
        
        -->
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
