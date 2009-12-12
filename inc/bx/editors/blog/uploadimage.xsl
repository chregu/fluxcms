<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
        xmlns:php="http://php.net/xsl"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
        xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml"
        xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
        >
    <xsl:output encoding="utf-8" method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

    <xsl:param name="url" select="'/'"/>
    <xsl:param name="id" select="'/'"/>
    <xsl:param name="dataUri" select="'/'"/>
    <xsl:param name="webroot" select="'/'"/>
    <xsl:param name="updateTree"/>
    <xsl:template match="/">
        <html>
            <head>
            <link rel="stylesheet" type="text/css" href="{$webroot}themes/standard/admin/css/admin.css" />
            <link rel="stylesheet" type="text/css" href="{$webroot}themes/standard/admin/css/formedit.css" />
            <script type="text/javascript" src="{$webroot}admin/webinc/js/showhidelayers.js"/>
            </head>
            <body id="upload">
            <xsl:if test="php:functionString('bx_helpers_globals::GET','fileuri') ">
            <xsl:attribute name="onload">parent.insertImage("<xsl:value-of select="php:functionString('bx_helpers_globals::GET','fileuri') "/>");</xsl:attribute>
            </xsl:if>
                <form action="{$webroot}admin/addresource/files/images/blog/?type=file"
                enctype="multipart/form-data"   
                method="post"
				onsubmit="MM_showHideLayers('wait_layer','','show');">
                <input type="hidden" name="bx[plugins][admin_addresource][name]" value=""/>
                      
                <input type="hidden" name="bx[plugins][admin_addresource][redirect]" value="{$webroot}admin/edit/{$url}"/>                      
                <input type="file" name="bx[plugins][admin_addresource][file]" value=""/>
               &#160; <input type="submit" i18n:attr="value" value="Upload" onclick="this.disabled;this.value ='Uploading Image ...'"/>
                </form>
				<div id="wait_layer" style="background-color: #ffffff; text-align:center; border:#000000 solid 1px; position:absolute; width:300px; height:115px; z-index:1; left: 200px; top: 200px; visibility: hidden">
					<h3>Upload in progress</h3>
					<p><img src="{$webroot}themes/standard/admin/images/wait_bar.gif" /><br />
					File is uploading, please wait. This window will be closed after upload.<br />
					</p>
				</div>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
