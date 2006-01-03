<xsl:stylesheet version="1.0" 
xmlns:blog="http://bitflux.org/doctypes/blog" 
xmlns:bxf="http://bitflux.org/functions" 
xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
xmlns:xhtml="http://www.w3.org/1999/xhtml" 
xmlns="http://www.w3.org/1999/xhtml"
xmlns:php="http://php.net/xsl"
xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
xmlns:rss="http://purl.org/rss/1.0/"
xmlns:dc="http://purl.org/dc/elements/1.1/" 
 
exclude-result-prefixes="php blog bxf xhtml rdf rss dc"
>
<xsl:import href="master.xsl"/>
    <xsl:import href="../standard/common.xsl"/>
    
<xsl:variable name="blogname" select="php:functionString('bx_helpers_config::getOption','blogname')"/>
   <xsl:variable name="blogroot" select="concat(substring($webroot,1,string-length($webroot)-1),$collectionUri)"/>
    
    <xsl:output encoding="utf-8" method="xml"/>
    <xsl:variable name="singlePost">
        <xsl:choose>
            <xsl:when test="count(/bx/plugin[@name = 'blog']/xhtml:html/xhtml:body/xhtml:div[@class='entry']) &lt;=1">true</xsl:when>
            <xsl:otherwise>false</xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
<xsl:variable name="dctitle">
    <xsl:choose>
            <xsl:when test="string-length($blogname) &gt; 0">
                <xsl:value-of select="$blogname"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$sitename"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>

	<xsl:template match="xhtml:div[@class='post_tags' or @class='post_related_entries']" mode="xhtml">
<xsl:if test="$singlePost = 'true'">
<xsl:copy>
<xsl:apply-templates select="@*" mode="xhtml"/>
<xsl:apply-templates mode="xhtml"/>
</xsl:copy>
</xsl:if>
</xsl:template>
    <xsl:template name="content">

        <xsl:choose>
            <xsl:when test="$singlePost = 'true'">
                <xsl:call-template name="blogSinglePost"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:call-template name="blogOverview"/>
            </xsl:otherwise>
        </xsl:choose>

        <h3 class="blog">Search</h3>
        <form  id="searchform" method="get" action="./">
<p>
            <input type="text" id="livesearch" name="q" size="10"  />
            <input type="submit" value="go" size="2" />
</p>
        </form>
    </xsl:template>


    <xsl:template name="blogSinglePost">
        <xsl:for-each select="/bx/plugin[@name = 'blog']/xhtml:html/xhtml:body/xhtml:div[@class='entry']">
            <xsl:apply-templates select="." mode="xhtml"/>
              
        </xsl:for-each>
    </xsl:template>

    <xsl:template name="blogOverview">


        <xsl:for-each select="/bx/plugin[@name = 'blog']/xhtml:html/xhtml:body/xhtml:div[@class='entry']">
            <xsl:apply-templates select="." mode="xhtml"/>
        </xsl:for-each>

        <xsl:apply-templates select="/bx/plugin[@name = 'blog']/xhtml:html/xhtml:body/xhtml:div[@class='blog_pager']" mode="xhtml"/>
    </xsl:template>

    <xsl:template match="xhtml:div[@class = 'comments']" mode="xhtml">
        <h3 class="blog">comments</h3>
        <xsl:apply-templates mode="xhtml"/>
<!--        <h3 class="blog">add a comment</h3>-->
    </xsl:template>
    

    <xsl:template match="xhtml:span[@class='comment_author_email']" mode="xhtml">
    </xsl:template>

    <xsl:template match="xhtml:span[@class='post_uri']" mode="xhtml">
    </xsl:template>
    
    
    <xsl:template match="xhtml:h2[@class='post_title']" mode="xhtml">
        <xsl:choose>
            <xsl:when test="$singlePost = 'true'">
                <h2 class="post_title">
                    <xsl:apply-templates/>
                </h2>
            </xsl:when>
            <xsl:otherwise>
                <h2 class="post_title">
                    <a href="{../xhtml:div[@class='post_links']/xhtml:span[@class='post_uri']/xhtml:a/@href}">
                        <xsl:apply-templates/>
                    </a>
                </h2>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
   
    <xsl:template name="bloglinks">

        <xsl:for-each select="document(concat('portlet://',$collectionUri,'bloglinks.xml'))/bx/plugin/bloglinks/bloglinkscategories">
            <h3 class="blog">
                <xsl:value-of select="name"/>
            </h3>
            <ul>
                <xsl:for-each select="bloglinks">
                    <li>
                        <a href="{link}">
                            <xsl:if test="rel"><xsl:attribute name="rel"><xsl:value-of select="rel"/></xsl:attribute></xsl:if>
                         
                            <xsl:value-of select="text"/>
                        </a>
                    </li>
                </xsl:for-each>
            </ul>

        </xsl:for-each>
    </xsl:template>
    
       <xsl:template name="html_head_title">
        <xsl:value-of select="$dctitle"/>
        <xsl:choose>
            <xsl:when test="$singlePost = 'true'">
         ::   <xsl:value-of select="/bx/plugin[@name = 'blog']/xhtml:html/xhtml:body/xhtml:div[@class='entry']/xhtml:h2"/>
            </xsl:when>

            <xsl:otherwise>
                <xsl:value-of select="/bx/plugin[@name = 'blog']/xhtml:html/xhtml:head/xhtml:title"/>
            </xsl:otherwise>
        </xsl:choose>

    </xsl:template>


    
    <xsl:template name="html_head">
    </xsl:template>

    <xsl:template match="xhtml:span[@class='post_author']" mode="xhtml">
    by <xsl:value-of select="."/>
    </xsl:template>
    
    <xsl:template match="xhtml:span[@class='post_date']" mode="xhtml">
    @ <xsl:value-of select="substring-before(.,' ')"/>
    </xsl:template>
    
    <xsl:template match="xhtml:span[@class='comment_date']" mode="xhtml">
    @ <xsl:value-of select="."/>
    </xsl:template>

    <xsl:template match="xhtml:span[@class='comment_author']" mode="xhtml">
    <xsl:value-of select="."/>
    </xsl:template>

    <xsl:template match="xhtml:div[@class='comment_content' or @class='comments_new']" mode="xhtml">
    <xsl:apply-templates mode="xhtml"/>
    </xsl:template>

    <xsl:template match="xhtml:div[@class='comment_meta_data']" mode="xhtml">
    <strong><xsl:apply-templates mode="xhtml"/></strong><br/>
    </xsl:template>

    <xsl:template match="xhtml:div[@class='comment' or @class='comments_not']" mode="xhtml">
    <div class="post_content">
	<xsl:apply-templates mode="xhtml"/>
    </div>
    </xsl:template>
          
    <xsl:template match="xhtml:div[@class='post_content']" mode="xhtml">
    <xsl:choose>
    <xsl:when test="$singlePost = 'true'">
    <div class="post_content">
	<xsl:apply-templates mode="xhtml"/>
    </div>
    </xsl:when>
    </xsl:choose>
    </xsl:template>
    
    
    <xsl:template match="xhtml:span[@class='post_category']" mode="xhtml">
        <xsl:text> </xsl:text>
        <xsl:text> </xsl:text>  <xsl:apply-templates mode="xhtml"/> 
    </xsl:template>

    <xsl:template match="xhtml:span[@class='post_categories']" mode="xhtml">
    <xsl:if test="$singlePost = 'true'">
           in <xsl:apply-templates mode="xhtml"/>
        </xsl:if>
    </xsl:template>
      
    <xsl:template match="xhtml:span[@class='blog_pager_prevnext']" mode="xhtml">
        
            <xsl:apply-templates mode="xhtml"/>
        
    </xsl:template>
    

    
    <xsl:template match="xhtml:span[@class='blog_pager_counter']" mode="xhtml">
    <xsl:value-of select="."/>
    </xsl:template>


    <xsl:template match="xhtml:span[@class = 'post_comments_count']" mode="xhtml">
        <xsl:if test="$singlePost  = 'false' and xhtml:a/text() &gt; 0">
            <a href="{xhtml:a/@href}">
            Comments (<xsl:value-of select="."/>)
            </a>
        </xsl:if>
    </xsl:template>

<xsl:template match="xhtml:div[@class='wizard']" mode="xhtml">
<!--
Not yet available
-->
</xsl:template>

<xsl:template match="xhtml:h3[@class='wizard']" mode="xhtml">
<!--
Not yet available
-->
</xsl:template>


<xsl:template match="xhtml:img[contains(@src,'/dynimages/')]" mode="xhtml">

<xsl:copy>
<xsl:apply-templates select="@*" mode="xhtml"/>
<xsl:attribute name="src">
<xsl:value-of select="php:functionString('bx_helpers_image::replaceDynimage',@src,160)"/>
</xsl:attribute>
<xsl:if test="not(@alt) or @alt = ''">
<xsl:attribute name="alt">a picture</xsl:attribute>
</xsl:if>
<xsl:apply-templates mode="xhtml"/>
</xsl:copy>

</xsl:template>

<xsl:template match="@blog:*" mode="xhtml">

</xsl:template>

</xsl:stylesheet>
