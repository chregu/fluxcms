<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
        xmlns:xhtml="http://www.w3.org/1999/xhtml"
        xmlns="http://www.w3.org/1999/xhtml" 
        xmlns:forms="http://bitflux.org/forms" 
	xmlns:php="http://php.net/xsl"
     xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
	exclude-result-prefixes="xhtml forms php i18n"
	
	>
    <xsl:import href="../standard/mastercommon.xsl" />
    <xsl:output encoding="utf-8" method="xml" 
    doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" 
    doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
    <xsl:param name="webroot"/>
    <xsl:param name="webrootFiles"  select="concat($webroot,'files/')"/>
    <xsl:param name="webrootWebinc"  select="concat($webroot,'webinc/')"/>
    <xsl:param name="webrootThemes" select="concat($webroot,'themes/')"/>
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
    <xsl:variable name="sitedescription" select="php:functionString('bx_helpers_config::getOption','sitedescription')"/>
    
    <xsl:param name="theme" select="php:functionString('bx_helpers_config::getOption','theme')"/>
    <xsl:param name="themeCss" select="php:functionString('bx_helpers_config::getOption','themeCss')"/>
    
  <xsl:variable name="defaultLanguage" select="php:functionString('constant','BX_DEFAULT_LANGUAGE')"/>

    <xsl:variable name="navitreePlugin" select="/bx/plugin[@name='navitree']"/>
    <!-- uncomment this, if you want meta description and keywords from collection properties
           and also adjust the html_head_keywords and html_head_description templates
    -->
    <!--
    <xsl:variable name="selectedCollections" select="$navitreePlugin/collection|$navitreePlugin/collection//items/collection[@selected='selected']"/>
    -->
    
    <xsl:variable name="webrootW" select="substring($webroot,1,string-length($webroot)-1)"/>
    <xsl:variable name="webrootLangW" select="substring($webrootLang,1,string-length($webrootLang)-1)"/>
    <xsl:template match="/">
        <html lang="{$lang}">
            <head>
                <meta name="generator" content="Flux CMS - http://www.flux-cms.org"/>
                <meta name="author">
                    <xsl:attribute name="content">
                        <xsl:value-of select="php:functionString('bx_helpers_uri::getUriPart',$webroot,'host')"/>
                    </xsl:attribute>
                </meta>
                
               <meta http-equiv="Content-Language" content="{$lang}"/>
                <!--
                <meta name="DC.language" content="{$lang}" />
                <meta name="DC.creator">
                    <xsl:attribute name="content">
                        <xsl:value-of select="php:functionString('bx_helpers_uri::getUriPart',$webroot,'host')"/>
                    </xsl:attribute>
                </meta>
                <meta name="DC.title">
                    <xsl:attribute name="content">
                        <xsl:call-template name="html_head_title"/>
                    </xsl:attribute>
                </meta>
                -->

<xsl:text>
</xsl:text>                
                <xsl:call-template name="html_head_keywords"/>
<xsl:text>
</xsl:text>                
                <xsl:call-template name="html_head_description"/>
<xsl:text>
</xsl:text>                
                <title>
                    <xsl:call-template name="html_head_title"/>
                </title>
<xsl:text>
</xsl:text>                
                <meta http-equiv="imagetoolbar" content="no"/>
<xsl:text>
</xsl:text>                
                <link rel="openid.server" href="{$webroot}admin/webinc/openid/" />
<xsl:text>
</xsl:text>                
                <link type="text/css" href="{$webrootThemes}{$theme}/css/{$themeCss}" rel="stylesheet" media="screen"/>
<xsl:text>
</xsl:text>                
                <link type="text/css" href="{$webrootThemes}{$theme}/css/mobile.css" rel="stylesheet" media="handheld"/>
<xsl:text>
</xsl:text>                
                <link rel="shortcut icon" href="{$webroot}favicon.ico" type="image/x-icon"/>
                <xsl:call-template name="html_head"/>
                <xsl:call-template name="html_head_scripts"/>
                <xsl:call-template name="html_head_custom"/>

            </head>

            <body id="ng_bitflux_org">
                <xsl:call-template name="body_attributes"/>

                
                    <div id="banner">

                        <div id="metanavi">
                            <xsl:for-each select="$langsAvail/langs/entry[not(.=$lang)]">
                                <a href="{concat($webroot,.,$collectionUri)}">
                                    [ <xsl:value-of select="." /> ]
                                </a>
                            </xsl:for-each>
                        </div>
                        
                        <h1>
                            <a href="{$webrootLang}">
                                <xsl:value-of select="$sitename"/>
                            </a>
                        </h1>
                        <h2><xsl:value-of select="$sitedescription"/>&#160;</h2>


                    </div>
		   <div id="mobile" style="display: none">
                    	<a href="{$webroot}mo{$requestUri}">Mobile Mode</a>
                    </div>
                    <div id="topnavi">
                        <xsl:call-template name="topnavi"/>
                    </div>

                <div id="container">

                    <div id="right">
                        <xsl:call-template name="leftnavi"/>

                       <xsl:call-template name="contentRight"/>
                    </div>


                    <div id="content" bxe_xpath="/xhtml:html/xhtml:body">
                        <xsl:call-template name="content"/>
                    </div>

                    <div id="footer">
<a href="http://freeflux.net/">Freeflux.net</a> - Powered by 
    <a href="http://www.flux-cms.org/">Flux CMS</a>
                    </div>

                </div>
            </body>
        </html>
    </xsl:template>
    

    <xsl:template name="leftnavi">
        <xsl:if test="($navitreePlugin/collection/items/collection[@selected = 'selected']/items[*[not(filename) or filename != 'index']/display-order &gt; 0 ])">
            <xsl:apply-templates select="$navitreePlugin/collection/items/collection[@selected = 'selected']"/>
        </xsl:if>
    </xsl:template>

    <xsl:template match="items/collection| plugin/collection">
        <xsl:variable name="items" select="items/*[(not(@lang) or @lang=$lang) and (not(filename) or filename!='index') and display-order > 0]"/>
        <xsl:if test="$items">
            <ul id="subnav">
            <xsl:call-template name="doCollection">
                <xsl:with-param name="items" select="$items"/>
            </xsl:call-template>
            </ul>
        </xsl:if>
    </xsl:template>

    <xsl:template name="doCollection">
        <xsl:param name="items"/>
        
            <xsl:for-each select="$items">
                <xsl:sort select="display-order" order="ascending" data-type="number"/>
                <xsl:variable name="link">
                    <xsl:choose>
                        <xsl:when test="@relink">
                            <xsl:value-of select="@relink"/>
                        </xsl:when>
                        <xsl:when test="local-name()='collection'">
                            <xsl:if test="not(starts-with(uri,'http://') or starts-with(uri,'https://') )">
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
                    <xsl:if test="local-name()='collection' and ( @selected = 'selected')">
                        <xsl:variable name="items" select="items/*[(not(@lang) or @lang=$lang) and (not(filename) or filename!='index') and display-order > 0]"/>
                        <xsl:if test="$items">
                            <ul>    
                            <xsl:call-template name="doCollection">
                                <xsl:with-param name="items" select="$items"/>
                            </xsl:call-template>
                            </ul>
                        </xsl:if>
                    </xsl:if>
                </li>
            </xsl:for-each>
    </xsl:template>
    
    <xsl:template name="topnavi">
        <ul>
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
                    <a href="{$webrootLang}{substring-after(uri,'/')}">
                        <xsl:if test="@selected='selected'">
                            <xsl:attribute name="class">selected</xsl:attribute>
                        </xsl:if>
                        <xsl:value-of select="title"/>
                    </a>
                </xsl:otherwise>
            </xsl:choose>
            </li>
                <!--<xsl:if test="position() != last()">|</xsl:if>-->
        </xsl:for-each>
        </ul>
    </xsl:template>
    <xsl:template name="contentRight">
    <!-- we should add the rss feed here... for later-->
        <h3>Latest Blog Posts</h3>
        <ul>
            <xsl:for-each select="document('portlet://blog/rss.xml')/bx/plugin/xhtml:html/xhtml:body/xhtml:div[@class='entry' and position() &lt; 6]">
                <li>
                    <a href="{xhtml:div[@class='post_links']/xhtml:span[@class='post_uri']/xhtml:a/@href}">
                        <xsl:value-of select="xhtml:h2"/>
                    </a>

                </li>
            </xsl:for-each>
        </ul>
<p>
        <a href="{$webroot}blog/rss.xml">
            <img border="0"  src="{$webrootThemes}{$theme}/buttons/rss.png"  alt="RSS 2.0 feed"/>
        </a>
</p>
    </xsl:template>
    <xsl:template name="html_head">
        <link rel="alternate" type="application/rss+xml" title="RSS 2.0 Feed" href="{$webroot}blog/rss.xml"/>
    </xsl:template>
    
    <xsl:template name="html_head_title">
    
        <xsl:variable name="matcher">
            <xsl:value-of select="$collectionUri" />
            <xsl:value-of select="$filename" />
            <xsl:value-of select="'.'" />
            <xsl:value-of select="$lang" />
            <xsl:value-of select="'.xhtml'" />
        </xsl:variable>
        
        <xsl:variable name="pagetitle">
            <xsl:value-of select="$navitreePlugin/collection/items//*[uri = $matcher]/pagetitle" />
        </xsl:variable>
        
        <xsl:choose>
            <xsl:when test="$pagetitle != ''">
            
                <xsl:value-of select="$pagetitle" />
           
            </xsl:when>       
            <xsl:otherwise>
               
                <xsl:value-of select="$sitename" />
                <xsl:for-each
                    select="$navitreePlugin/collection/items//*[@selected='selected']">
                    ::
                    <xsl:value-of select="title" />
                    <!-- resource do not have selected -> search them with the filename -->
                    <xsl:if test="position() = last() and $filename != 'index'">
                        ::
                        <xsl:value-of select="items/*[filename=$filename]/title" />
                    </xsl:if>
                </xsl:for-each>
                
            </xsl:otherwise>
        </xsl:choose>

        <xsl:call-template name="html_head_title_end"/>
    </xsl:template>
    
    <xsl:template name="body_attributes"/>
    
    <xsl:template name="html_head_title_end"/>

    <xsl:template name="html_head_keywords">
      <!-- uncomment this, if you want meta description and keywords from collection properties
           and also adjust the html_head_keywords and html_head_description templates
    -->
    <!-- with inheritance 
    <xsl:variable name="k" select="$selectedCollections/properties/property[@name='subject']"/>
    <xsl:variable name="last" select="$k[position() = last()]/@value"/>
    -->
    <!-- without inheritance 
    <xsl:variable name="k" select="$selectedCollections[position() = last()]"/>
    <xsl:variable name="last" select="$k/properties/property[@name='subject']/@value"/>
    -->
    <!--
    <xsl:if test="$last">
        <meta name="keywords">
            <xsl:attribute name="content"><xsl:value-of select="$last"/></xsl:attribute>
        </meta>
    </xsl:if>
    -->
    </xsl:template>
    
    <xsl:template name="html_head_description">
    <!-- uncomment this, if you want meta description and keywords from collection properties
           and also adjust the html_head_keywords and html_head_description templates
    -->
    <!-- with inheritance
        <xsl:variable name="k" select="$selectedCollections/properties/property[@name='description']"/>
        <xsl:variable name="last" select="$k[position() = last()]/@value"/>
     -->
    <!-- without inheritance
        <xsl:variable name="k" select="$selectedCollections[position() = last()]"/>
        <xsl:variable name="last" select="$k/properties/property[@name='description']/@value"/>
     -->
    <!--
      <xsl:choose>
            <xsl:when test="$last != ''">
                <meta name="description" content="{$last}"/>
            </xsl:when>
            <xsl:otherwise>
                <meta name="description" content="{$sitedescription}"/>
            </xsl:otherwise>
        </xsl:choose>
     -->
    <meta name="description" content="{$sitedescription}"/>
    </xsl:template>
    
    <xsl:template name="html_head_custom"/>
     
</xsl:stylesheet>

