
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
        xmlns:xhtml="http://www.w3.org/1999/xhtml"
        xmlns="http://www.w3.org/1999/xhtml" 
        xmlns:forms="http://bitflux.org/forms" 
	xmlns:php="http://php.net/xsl"
	exclude-result-prefixes="xhtml forms php"
	>
    <xsl:output encoding="utf-8" method="xml" 
    doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" 
    doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
    <xsl:param name="webroot"/>
    <xsl:param name="webrootLang"/>
    <xsl:param name="requestUri"/>
    <xsl:param name="mode"/>
    <xsl:param name="admin" select="'false'"/>
    <xsl:param name="lang" select="'de'"/>
    <xsl:param name="collectionUri"/>
    <xsl:param name="filename"/>
    <xsl:param name="fileNumber"/>
    <xsl:variable name="langsAvail" select="php:functionString('bx_helpers_config::getLangsAvailXML')"/>
    <xsl:variable name="sitename" select="php:functionString('bx_helpers_config::getOption','sitename')"/>
    <xsl:param name="theme" select="php:functionString('bx_helpers_config::getOption','theme')"/>
    <xsl:param name="themeCss" select="php:functionString('bx_helpers_config::getOption','themeCss')"/>
      
  <xsl:variable name="defaultLanguage" select="php:functionString('constant','BX_DEFAULT_LANGUAGE')"/>

    <xsl:variable name="navitreePlugin" select="/bx/plugin[@name='navitree']"/>
    <xsl:variable name="webrootW" select="substring($webroot,1,string-length($webroot)-1)"/>
    <xsl:variable name="webrootLangW" select="substring($webrootLang,1,string-length($webrootLang)-1)"/>
    <xsl:template match="/">
        <html>
            <head>
                <meta name="generator" content="Flux CMS - http://www.flux-cms.org"/>
                <link type="text/css" href="{$webroot}themes/{$theme}/css/{$themeCss}" rel="stylesheet" media="screen"/>

                <title>
                    <xsl:call-template name="html_head_title"/>
                </title>
                <link rel="shortcut icon" href="{$webroot}favicon.ico" type="image/x-icon"/>
                <xsl:call-template name="html_head"/>
            </head>

            <body id="ng_bitflux_org">
                <xsl:call-template name="body_attributes"/>
                <div id="container">
                    <div id="banner">

                        <div id="metanavi">
                                <xsl:for-each select="$langsAvail/langs/entry[not(.=$lang)]">
                                <xsl:choose>
                                    <xsl:when test="text() = $defaultLanguage">
                                    <a href="{concat($webrootW,$collectionUri)}">
                                        <xsl:value-of select="."/>&#160;
                                    </a>
                                    </xsl:when>
                                    <xsl:otherwise>
                                    <a href="{concat($webroot,.,$collectionUri)}">
[                                        <xsl:value-of select="."/> ]
                                    </a>
                                    </xsl:otherwise>
                                    </xsl:choose>
                                </xsl:for-each>
                          
 	                       </div>
                        <h1>
                            <a href="{$webrootLang}">
                                <xsl:value-of select="$sitename"/>
                            </a>
                        </h1>

                    </div>

                    <div id="topnavi">
                        <xsl:call-template name="topnavi"/>
                    </div>

                    <div id="left">
                        <xsl:call-template name="leftnavi"/>
                    </div>
                    <div id="right">
                        <xsl:call-template name="contentRight"/>
                    </div>


                    <div id="content" bxe_xpath="/xhtml:html/xhtml:body">
                        <xsl:call-template name="content"/>
                    </div>

                    <div id="footer">
    Flux CMS - <a href="http://www.flux-cms.org/">http://www.flux-cms.org</a>
                    </div>

                </div>
            </body>
        </html>
    </xsl:template>
    <xsl:template name="leftnavi">
        <xsl:apply-templates select="$navitreePlugin/collection/items/collection[@selected = 'selected']"/>
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
                    </li>

                    <xsl:if test="local-name()='collection' and ( @selected = 'selected')">
                        <xsl:apply-templates select="."/>
                    </xsl:if>

                </xsl:for-each>
            </ul>
        </xsl:if>

    </xsl:template>

    <xsl:template name="topnavi">

        <xsl:for-each select="$navitreePlugin/collection/items/collection[display-order != 0]">
            <xsl:sort select="display-order" order="ascending" data-type="number"/>
            <xsl:choose>
                <xsl:when test="@relink">
                    <a href="{@relink}">
                        <xsl:value-of select="title"/>
                    </a>
                </xsl:when>
                <xsl:otherwise>
                    <a href="{$webrootLang}{substring-after(uri,'/')}">
                        <xsl:if test="@selected='selected'">
                            <xsl:attribute name="class">selected</xsl:attribute>
                        </xsl:if>
                        <xsl:value-of select="title"/>
                    </a>
                </xsl:otherwise>
            </xsl:choose>


               
                <!--<xsl:if test="position() != last()">|</xsl:if>-->
        </xsl:for-each>

    </xsl:template>
    <xsl:template name="contentRight">
    <!-- we should add the rss feed here... for later-->
        <h3>Latest Blog Posts</h3>
        <ul>
            <xsl:for-each select="document('portlet://blog/rss.xml')/bx/plugin/xhtml:html/xhtml:body/xhtml:div[@class='entry']">
                <li>
                    <a href="{xhtml:div[@class='post_links']/xhtml:span[@class='post_uri']/xhtml:a/@href}">
                        <xsl:value-of select="xhtml:h2"/>
                    </a>

                </li>
            </xsl:for-each>
        </ul>
<p>
        <a href="{$webroot}blog/rss.xml">
            <img border="0" src="{$webroot}files/images/xml.png" alt="RSS 2.0 feed"/>
        </a>
</p>
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

