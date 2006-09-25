<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" exclude-result-prefixes="xhtml">
    <xsl:import href="master.xsl"/>
    <xsl:import href="../standard/common.xsl"/>

    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

    <xsl:template name="content">
        <form onsubmit="return liveSearchSubmit()" style="margin:0px;" name="searchform" method="get" action="./" id="searchform">
            Fulltext Search: <input type="text" id="livesearch" name="query" size="15" onkeypress="liveSearchStart()"/>
            <!--div id="LSResult" style="display: none;"-->
            <xsl:for-each select="/bx/plugin[@name='metasearch']/result/resource">
                    <h2><a href="{@uri}"><xsl:value-of select="title"/></a></h2>
                    <p><xsl:value-of select="@resourceDescription"/></p>
                    <p><i>Last modified: <xsl:value-of select="@lastmodified"/></i></p>
                    
            </xsl:for-each>
            
            <xsl:choose>
            <xsl:when test="/bx/plugin[@name='metasearch']/result/prevpage and /bx/plugin[@name='metasearch']/result/nextpage/text()">
                <p>Searchresults <xsl:value-of select="/bx/plugin[@name='metasearch']/result/firstdoc"/>
                - <xsl:value-of select="/bx/plugin[@name='metasearch']/result/lastdoc"/>                                          
                /<xsl:value-of select="/bx/plugin[@name='metasearch']/result/found"/>
                </p><p>
                <a href="./?query={/bx/plugin[@name='metasearch']/result/query}&amp;p={/bx/plugin[@name='metasearch']/result/prevpage}">Prev</a> /
                <a href="./?query={/bx/plugin[@name='metasearch']/result/query}&amp;p={/bx/plugin[@name='metasearch']/result/nextpage}">Next</a>
                </p>
            </xsl:when>
            <xsl:when test="/bx/plugin[@name='metasearch']/result/nextpage/text()">
                <p>Searchresults <xsl:value-of select="/bx/plugin[@name='metasearch']/result/firstdoc"/>
                - <xsl:value-of select="/bx/plugin[@name='metasearch']/result/lastdoc"/>
                /<xsl:value-of select="/bx/plugin[@name='metasearch']/result/found"/>
                </p><p>
                <a href="./?query={/bx/plugin[@name='metasearch']/result/query}&amp;p={/bx/plugin[@name='metasearch']/result/nextpage}">Next</a>
                </p>
            </xsl:when>
            <xsl:when test="/bx/plugin[@name='metasearch']/result/prevpage">
                <p>Searchresults <xsl:value-of select="/bx/plugin[@name='metasearch']/result/firstdoc"/>
                - <xsl:value-of select="/bx/plugin[@name='metasearch']/result/lastdoc"/>
                /<xsl:value-of select="/bx/plugin[@name='metasearch']/result/found"/>
                </p><p>
                <a href="./?query={/bx/plugin[@name='metasearch']/result/query}&amp;p={/bx/plugin[@name='metasearch']/result/prevpage}">Prev</a>
                </p>
            </xsl:when>
            </xsl:choose>
            
            <!--/div-->
        </form>
    </xsl:template>
    
    <!-- add everything from head to the output -->
    <xsl:template name="html_head">
    <script type="text/javascript" src="{$webroot}webinc/plugins/metasearch/livesearch.js"> </script>
    <script type="text/javascript">
    var liveSearchRoot = '<xsl:value-of select="concat($webrootW,$collectionUri)"/>';
    </script>
        <xsl:apply-templates select="/bx/plugin[@name='xhtml']/xhtml:html/xhtml:head/node()" mode="xhtml"/>
    </xsl:template>
    
    <xsl:template name="results">
        <xsl:value-of select="/bx/plugin[@name='metasearch']/result/resource/@uri"/>
    </xsl:template>
    
    <!-- except the title -->
    <xsl:template match="xhtml:head/xhtml:title" mode="xhtml"></xsl:template>
    
    <!-- do not output meta tags without @content -->
    <xsl:template match="xhtml:head/xhtml:meta[not(@content)]" mode="xhtml"></xsl:template>

    <xsl:template name="body_attributes">
        <xsl:attribute name="onload">liveSearchInit();</xsl:attribute>
    </xsl:template>
    

</xsl:stylesheet>
