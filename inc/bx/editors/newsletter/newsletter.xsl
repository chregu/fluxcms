<?xml version="1.0"?>
<xsl:stylesheet version="1.0" 
  xmlns:php="http://php.net/xsl"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml">
 

  <xsl:output encoding="utf-8" method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

    <xsl:param name="url" select="'/'"/>
    <xsl:param name="id" select="'/'"/>
    <xsl:param name="dataUri" select="'/'"/>
    <xsl:param name="webroot" select="'/'"/>
    <xsl:param name="updateTree"/>
    <xsl:variable name="BX_OPEN_BASEDIR" select="php:functionString('constant','BX_OPEN_BASEDIR')"/>
	<xsl:variable name="mimetype" select="php:functionString('popoon_helpers_mimetypes::getFromFileLocation',$dataUri)"/>
    <xsl:template match="/">
        <html>
            <head>
            <title>Edit Newsletter <xsl:value-of select="substring-after($dataUri,$BX_OPEN_BASEDIR)"/></title>
                         <link rel="stylesheet" href="{$webroot}themes/standard/admin/css/formedit.css" type="text/css" />

             <xsl:if test="$updateTree">
            <script type="text/javascript">
            window.parent.navi.Navitree.reload('/<xsl:value-of select="substring($dataUri,1,string-length($dataUri)-1)"/>');
            </script>
            </xsl:if>
            
			
            </head>
            <body>
            
            
            <!-- xsl:value-of select="/bx/plugin/newsletter"/ -->
            
            <xsl:copy>
    			<xsl:apply-templates select="/bx/plugin/newsletter" mode="xhtml"/>
			</xsl:copy>
            
            </body>
		</html>
		</xsl:template>
         
		<xsl:template match="*" mode="xhtml">
		<xsl:element name="{local-name()}">
			<xsl:apply-templates select="@*" mode="xhtml"/>
			<xsl:apply-templates mode="xhtml"/>
		</xsl:element>
		</xsl:template>


		<xsl:template match="@*" mode="xhtml">
 			<xsl:copy-of select="."/>
		</xsl:template>
            
</xsl:stylesheet>