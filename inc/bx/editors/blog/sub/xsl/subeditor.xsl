<xsl:stylesheet version="1.0" 
    xmlns:sixcat="http://sixapart.com/atom/category#"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:atom="http://purl.org/atom/ns#"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xhtml="http://www.w3.org/1999/xhtml"
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:bxf="http://bitflux.org/functions"
    xmlns:rss="http://purl.org/rss/1.0/"
    xmlns:blog="http://bitflux.org/doctypes/blog"
    xmlns:php="http://php.net/xsl"
    exclude-result-prefixes="rdf dc php xhtml rss bxf blog"
>
    
    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

    <xsl:param name="webroot"/>
    <xsl:param name="collectionUri" select="''"/>
    <xsl:param name="collectionUriOfId" select="''"/>
    <xsl:param name="dataUri" select="''"/>
    <xsl:variable name="formName" select="'bx[plugins][admin_edit]'"/>
    <xsl:variable name="opentabs" select="php:function('bx_helpers_config::getOpenTabs')"/>
    
    <xsl:template match="/">
        <html>
            <head>
                <link rel="stylesheet" href="{$webroot}themes/standard/admin/css/blog.css" type="text/css"/>
                <link rel="stylesheet" href="{$webroot}themes/standard/admin/css/formedit.css" type="text/css"/>
                <script type="text/javascript" src="{$webroot}admin/webinc/js/overview.js"></script>
                <script type="text/javascript" language="JavaScript" src="{$webroot}webinc/js/formedit.js">
                    <xsl:text> </xsl:text>
                </script>
                <xsl:call-template name="head"/>
            </head>
            <body onload="onLoad();">
                <xsl:call-template name="editorContent"/>
            </body>
        </html>
    </xsl:template>
    
    <xsl:template name="head"/>
    
    <xsl:template name="editorContent"/>

</xsl:stylesheet>

