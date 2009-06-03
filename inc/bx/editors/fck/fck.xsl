<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns:xhtml="http://www.w3.org/1999/xhtml"
        xmlns:php="http://php.net/xsl">

    <xsl:output encoding="utf-8" method="html"
            doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
            doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" />

    <xsl:param name="url" select="'/'"/>
    <xsl:param name="dataUri" select="$dataUri"/>
    <xsl:param name="webroot" select="'/j/'"/>
    <xsl:param name="requestUri" select="$requestUri"/>
    <xsl:param name="template" select="'default.xhtml'"/>

    <xsl:variable name="contentUri" select="concat($webroot, 'admin/content', $requestUri)"/>

    <xsl:template match="/">
        <xsl:apply-templates/>
    </xsl:template>
    
    <xsl:template match="*">
        <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
            <xsl:apply-templates/>
        </xsl:copy>
    </xsl:template>

    <xsl:template name="themeInit">
        <script type="text/javascript" language="JavaScript">
            var theme = '<xsl:value-of select="php:functionString('bx_helpers_config::getProperty','theme')"/>';
            var themeCss = '<xsl:value-of select="php:functionString('bx_helpers_config::getProperty','themeCss')"/>';
        </script>
    </xsl:template>

    <xsl:template match="xhtml:head">
        <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
            <xsl:apply-templates/>
        </xsl:copy>
    
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <xsl:call-template name="themeInit"/>

        <link rel="stylesheet" type="text/css" href="{$webroot}themes/standard/admin/css/admin.css"/>
        <script type="text/javascript" src="{$webroot}webinc/fck/fckeditor.js">
            <xsl:text> </xsl:text>
        </script>
        <script type="text/javascript" src="{$webroot}webinc/editors/fck/fck.js">
            <xsl:text> </xsl:text>
        </script>
        <script type="text/javascript" src="{$webroot}webinc/js/sarissa_0.9.9.4.js">
            <xsl:text> </xsl:text>
        </script>
        <script type="text/javascript" src="{$webroot}webinc/js/bx/helpers.js">
            <xsl:text> </xsl:text>
        </script>
        <script type="text/javascript">
            var fckBasePath	= "<xsl:value-of select="$webroot"/>webinc/fck/";
            var bx_webroot = "<xsl:value-of select="$webroot"/>";
            var contentURI = '<xsl:value-of select="$contentUri"/>?editor=fck&amp;template=<xsl:value-of select="$template"/>'; ;
        </script>
    </xsl:template>
 
    <xsl:template match="xhtml:body">
        <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
         
            <xsl:apply-templates/>
        </xsl:copy>
        <form>
            <!--<textarea id="editcontent" name="editcontent" rows="20"></textarea>-->
            <script type="text/javascript">
            <![CDATA[
            <!--
            startFCK();
            //-->
            ]]>
            </script>
            <input type="button" onclick="javascript:saveContent();" accesskey="s" value="Save" id="save"/>
             <span id="LSResult">Document saved ...</span>
        </form>
    </xsl:template>

</xsl:stylesheet>
