
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
        xmlns:xhtml="http://www.w3.org/1999/xhtml"
        xmlns:forms="http://bitflux.org/forms" 
        xmlns="http://www.w3.org/1999/xhtml" 
	xmlns:php="http://php.net/xsl"
	exclude-result-prefixes="xhtml forms php"
	>
    <xsl:output encoding="utf-8" method="xml" 
    doctype-system="http://www.wapforum.org/DTD/xhtml-mobile10.dtd"         
    doctype-public="-//WAPFORUM//DTD XHTML Mobile 1.0//EN"/> 
    <xsl:param name="webroot"/>
    <xsl:param name="webrootLang"/>
    <xsl:param name="requestUri"/>
    <xsl:param name="mode"/>
    <xsl:param name="admin" select="'false'"/>
    <xsl:param name="lang" select="'de'"/>
    <xsl:param name="collectionUri"/>
    <xsl:param name="filename"/>
    <xsl:param name="fileNumber"/>
    <xsl:param name="noNavigation" select="'false'"/>
    <xsl:variable name="langsAvail" select="php:functionString('bx_helpers_config::getLangsAvailXML')"/>
    <xsl:variable name="sitename" select="php:functionString('bx_helpers_config::getOption','sitename')"/>
    <xsl:param name="theme" select="php:functionString('bx_helpers_config::getOption','theme')"/>
    <xsl:param name="themeCss" select="php:functionString('bx_helpers_config::getOption','themeCss')"/>
      
  <xsl:variable name="defaultLanguage" select="php:functionString('constant','BX_DEFAULT_LANGUAGE')"/>

    <xsl:variable name="navitreePlugin" select="/bx/plugin[@name='navitree']"/>
    <xsl:variable name="webrootW" select="substring($webroot,1,string-length($webroot)-1)"/>
    <xsl:variable name="webrootLangW" select="substring($webrootLang,1,string-length($webrootLang)-1)"/>
<xsl:variable name="dctitle">
                <xsl:value-of select="$sitename"/>
    </xsl:variable>

    <xsl:template match="/">
        <html>
            <head>
                <meta name="generator" content="Flux CMS - http://www.flux-cms.org"/>
                <link type="text/css" href="{$webroot}themes/{$theme}/css/main.css" rel="stylesheet" media="handheld"/>
                <link type="text/css" href="{$webroot}themes/{$theme}/css/main.css" rel="stylesheet" media="screen"/>  
                <title>
                    <xsl:call-template name="html_head_title"/>
                </title>
                <xsl:call-template name="html_head"/>
            </head>

            <body id="ng_bitflux_org">
                <xsl:call-template name="body_attributes"/>
       
                    <div id="banner">
                        <h1>
                            <a href="{$webrootLang}">
                                <xsl:value-of select="$dctitle"/>
                            </a>
                        </h1>

                    </div>

                    <div id="content" bxe_xpath="/xhtml:html/xhtml:body">
                        <xsl:call-template name="content"/>
                    </div>

                    <div id="topnavi">
                        <xsl:call-template name="topnavi"/>
                    </div>
                    <div id="footer">
    Flux CMS - <a href="http://www.flux-cms.org/">http://www.flux-cms.org</a>
                    </div>

       
            </body>
        </html>
    </xsl:template>
    
  <xsl:template match="items/collection| plugin/collection">
        <xsl:variable name="items" select="items/*[(not(@lang) or @lang=$lang) and (not(filename) or filename!='index') and display-order > 0]"/>
        <xsl:if test="$items">

            <ul>

                <xsl:for-each select="$items">
                    <xsl:sort select="display-order" order="ascending" data-type="number"/>
                    <xsl:variable name="link">
                        <xsl:choose>
                            <xsl:when test="@relink">
                                <xsl:value-of select="@relink"/>
                            </xsl:when>
                            <xsl:when test="local-name()='collection'">
                                <xsl:if test="not(starts-with(uri,'http://'))">
                                    <xsl:value-of select="$webrootLangW"/>
                                </xsl:if>
                                <xsl:value-of select="uri"/>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:value-of select="concat($webrootLangW,../../uri,filename)"/>.html</xsl:otherwise>
                        </xsl:choose>
                    </xsl:variable>

                    <li>
                    
                        <a href="{$link}">
                            <xsl:if test="filename=$filename or @selected='selected'">
                                <xsl:attribute name="class">selected</xsl:attribute>
                            </xsl:if>
                            <xsl:value-of select="title"/>
                        </a>
                        <!-- uncomment the following, if you want a counter for 
                                the number of posts per category on blog pages 
                             You also have to adjust the blog.xsl.
                             See http://wiki.bitflux.org/How_to_show_number_of_posts_in_a_category
                             for details
                             
                         -->
                         <!--
                        <xsl:if test="@count">
                                [<xsl:value-of select="@count"/>]
                        </xsl:if>
                        -->
                    </li>

                    <xsl:if test="local-name()='collection' and ( @selected = 'selected')">
                        <xsl:apply-templates select="."/>
                    </xsl:if>

                </xsl:for-each>
            </ul>
        </xsl:if>

    </xsl:template>

    <xsl:template name="topnavi">
    <h3>Navigation</h3>
    <ul>    
    <li><a href="{$webrootLangW}/" accesskey="0">HOME</a></li>
<xsl:if test="$noNavigation != 'true'">
        <xsl:for-each select="$navitreePlugin/collection/items/collection[display-order != 0]">
            <xsl:sort select="display-order" order="ascending" data-type="number"/>
            <li>
            <xsl:choose>
                <xsl:when test="@relink">
                    <a href="{@relink}">
                        <xsl:value-of select="title"/>
                    </a>
                </xsl:when>
                <xsl:otherwise>
                    <a href="{$webrootLang}{substring-after(uri,'/')}" accesskey="{position()}">
                        
                    <xsl:if test="@selected='selected'">
                            <xsl:attribute name="class">selected</xsl:attribute>
                        </xsl:if>
                        <xsl:value-of select="title"/>
                        <xsl:if test="@selected='selected'">
                            <xsl:apply-templates select="document(concat('portlet://',$collectionUri,'plugin=categories(',$filename,',count).xml'))/bx/plugin/collection"/>
                        </xsl:if>
                        
                        
                           
                    </a>
                </xsl:otherwise>
            </xsl:choose>
</li>

               
        </xsl:for-each>
</xsl:if>
        <li><a accesskey="*" href="{$webroot}{$requestUri}?isMobile=false">Leave mobile mode</a></li>
</ul>
    </xsl:template>
    
    <xsl:template name="html_head">
        <link rel="alternate" type="application/rss+xml" title="RSS 2.0 Feed" href="{$webroot}blog/rss.xml"/>
    </xsl:template>
    <xsl:template name="html_head_title">
        <xsl:value-of select="$sitename"/>
        <xsl:for-each select="$navitreePlugin/collection/items//*[@selected='selected']">
                :: <xsl:value-of select="title"/>
                <!-- resource do not have selected -> search them with the filename -->
            <xsl:if test="position() = last() and $filename != 'index'">
                :: <xsl:value-of select="items/*[filename=$filename]/title"/>
            </xsl:if>
        </xsl:for-each>
    </xsl:template>
    <xsl:template name="body_attributes"/>
</xsl:stylesheet>

