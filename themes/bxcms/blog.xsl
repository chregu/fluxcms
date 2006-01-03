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

    <xsl:template name="content">

        <xsl:choose>
            <xsl:when test="$singlePost = 'true'">
                <xsl:call-template name="blogSinglePost"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:call-template name="blogOverview"/>
            </xsl:otherwise>
        </xsl:choose>

    </xsl:template>
    <xsl:template name="contentRight">
        <h3 class="blog">LiveSearch</h3>
        <form onsubmit="return liveSearchSubmit()" style="margin:0px;" name="searchform" method="get" action="./">

            <input type="text" id="livesearch" name="q" size="15"  onkeypress="liveSearchStart()"/>
            <div id="LSResult" style="display: none;">
                <ul id="LSShadow">
                    <li>&#160;</li>
                </ul>
            </div>
        </form>

        <xsl:call-template name="delicious"/>

        <xsl:call-template name="bloglinks"/>
        <xsl:call-template name="archive"/>
 <xsl:call-template name="buttons"/>
    </xsl:template>

    
    <xsl:template name="buttons">
 <h3 class="blog">Buttons</h3>
    <div id="buttons">
    <ul>
    <li><a href="{$blogroot}rss.xml"><img src="{$webroot}/themes/{$theme}/buttons/rss.png" alt="RSS 2.0 Feed" border="0"/></a></li>
    <li><a href="{$blogroot}latestcomments.xml"><img src="{$webroot}themes/{$theme}/buttons/comments.png" border="0" alt="Latest comments" /></a></li>
    <li><a href="http://validator.w3.org/check?uri=referer"><img src="{$webroot}/themes/{$theme}/buttons/xhtml10.png" alt="XHTML 1.0 compliant" border="0"/></a></li>
    <li><a href="http://www.flux-cms.org"><img src="{$webroot}/themes/{$theme}/buttons/fluxcms.png" alt="Powered by Flux CMS" border="0"/></a></li>
    <li><a href="http://www.popoon.org"><img src="{$webroot}/themes/{$theme}/buttons/popoon.png" alt="Powered by Popoon" border="0"/></a></li>
    </ul>
    
    
    </div>
    </xsl:template>
    <xsl:template name="blogSinglePost">
        <xsl:for-each select="/bx/plugin[@name = 'blog']/xhtml:html/xhtml:body/xhtml:div[@class='entry']">
            <xsl:apply-templates select="." mode="xhtml"/>
                 <xsl:comment>

&lt;rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns:dc="http://purl.org/dc/elements/1.1/"
         xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/">
&lt;rdf:Description
    rdf:about="http://www.foo.com/archive.html#foo"
    dc:identifier="<xsl:value-of select="concat($webrootW,$collectionUri,@blog:post_uri,'.html')"/>"
    dc:title="<xsl:value-of select="xhtml:h2"/>"
    trackback:ping="<xsl:value-of select="concat($webrootW,$collectionUri,'plugin=trackback(',substring-after(@id,'entry'),').xml')"/>" />
&lt;/rdf:RDF>
</xsl:comment>  
        </xsl:for-each>
    </xsl:template>

    <xsl:template name="blogOverview">


        <xsl:for-each select="/bx/plugin[@name = 'blog']/xhtml:html/xhtml:body/xhtml:div[@class='entry']">
            <xsl:apply-templates select="." mode="xhtml"/>
        </xsl:for-each>

        <xsl:apply-templates select="/bx/plugin[@name = 'blog']/xhtml:html/xhtml:body/xhtml:div[@class='blog_pager']" mode="xhtml"/>
    </xsl:template>

    <xsl:template name="leftnavi">

        <xsl:apply-templates select="document(concat('portlet://',$collectionUri,'plugin=categories(',$filename,').xml'))/bx/plugin/collection"/>
    </xsl:template>

    <xsl:template match="xhtml:div[@class = 'comments']" mode="xhtml">
        <h3 class="blog">comments</h3>
        <xsl:apply-templates mode="xhtml"/>
        <xsl:if test="not(../xhtml:div[@class='comments_not'])">
        <h3 class="blog">add a comment</h3>

        <p> The Trackback URL to this comment is:<br/> <xsl:value-of
select="concat($webrootW,$collectionUri,'plugin=trackback(',substring-after(../@id,'entry'),').xml')"/> </p>

        <p>  This blog is <a href="http://www.gravatar.com/">gravatar</a> enabled.<br/>
            Your email adress will never be published.<br/>
            Comment spam will be deleted!</p>
        </xsl:if>
    </xsl:template>

    <xsl:template match="xhtml:span[@class='comment_author_email']" mode="xhtml">
        <xsl:if test="string-length(.) &gt; 5">
            <img  class="blog_gravatar" src="{php:functionString('bx_plugins_blog_gravatar::getLink',text(),'80','aaaaaa')}" />
        </xsl:if>
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

    <xsl:template name="delicious">
        <h3 class="blog"><a href="http://del.icio.us/tag/bitflux">del.icio.us/tag/bitflux</a></h3>
        <ul>
           <xsl:for-each select="document(concat('portlet://',$collectionUri,'plugin=deliciousrdf(tag/bitflux).xml'))/bx/plugin/rdf:RDF/rss:item[position() &lt; 11]">
           <li><a title="{rss:description}   - Categories: {dc:subject}" class="blogLinkPad" href="{rss:link}"><xsl:value-of select="rss:title"/></a></li>
        </xsl:for-each>
        
            
        </ul>
    </xsl:template>

     <xsl:template name="archive">
        <h3 class="blog">Archive</h3>
        <ul>
            <xsl:for-each select="document(concat('portlet://',$collectionUri,'plugin=montharchive.xml'))/bx/plugin/archive/link">
                <li>
                    <a title="{@count} entries" class="blogLinkPad" href="{$collectionUri}archive/{@href}">
                        <xsl:copy-of select="node()"/> 
                    </a>   [<xsl:value-of select="@count"/>]  
                </li>
            </xsl:for-each>
            

        </ul>
    </xsl:template>
    
    <xsl:template name="html_head_title">
        <xsl:value-of select="$blogname"/>
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
<xsl:text>
</xsl:text>
<link rel="alternate" type="application/rss+xml" title="RSS 2.0 Feed" href="{$blogroot}rss.xml" />
<xsl:text>
</xsl:text>
<link rel="alternate" type="application/rss+xml" title="RSS 2.0 Comments Feed" href="{$blogroot}latestcomments.xml" />
<xsl:text>
</xsl:text>
<link rel="EditURI" type="application/rsd+xml" title="RSD" href="{$blogroot}xmlrpc.rsd" />
<xsl:text>
</xsl:text>
<script type="text/javascript" src="{$webroot}webinc/js/livesearch.js">
</script>
<xsl:text>
    </xsl:text>
</xsl:template>

    <xsl:template match="xhtml:span[@class='post_author']" mode="xhtml">
    by <xsl:value-of select="."/>
    </xsl:template>
    
    <xsl:template match="xhtml:span[@class='post_date']" mode="xhtml">
    @ <xsl:value-of select="."/>
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
          
    <xsl:template match="xhtml:span[@class='post_category']" mode="xhtml">
    <xsl:text> </xsl:text>
    <xsl:text> </xsl:text><xsl:apply-templates mode="xhtml"/> 
    </xsl:template>

    <xsl:template match="xhtml:span[@class='post_uri']" mode="xhtml">
    &#160;<xsl:apply-templates mode="xhtml"/>
    </xsl:template>

    <xsl:template match="xhtml:span[@class='blog_pager_counter']" mode="xhtml">
    <xsl:value-of select="."/>
    </xsl:template>

    <xsl:template match="xhtml:span[@class='blog_pager_prevnext' or @class='post_categories']" mode="xhtml">
    <span class="right">
    <xsl:apply-templates mode="xhtml"/>
    </span>
    </xsl:template>

    <!--de google-page ranker -->
    <xsl:template match="xhtml:div[@class='comment_content']//xhtml:a" mode="xhtml">
    <xsl:apply-templates mode="xhtml"/> [<xsl:value-of select="@href"/>]
    </xsl:template>
          
    <xsl:template match="xhtml:span[@class = 'post_comments_count']" mode="xhtml">
    <xsl:variable name="entry" select="../.."/>
        <xsl:if test="$entry[@blog:post_comment_allowed = 1  or @blog:comment_count &gt; 0]">
                <a href="{xhtml:a/@href}">
                Comments(<xsl:value-of select="."/>)
                </a>
        </xsl:if>
    </xsl:template>

<xsl:template match="@blog:*" mode="xhtml">

</xsl:template>

<xsl:template name="body_attributes">
<xsl:attribute name="onload">liveSearchInit();</xsl:attribute>

</xsl:template>
</xsl:stylesheet>
