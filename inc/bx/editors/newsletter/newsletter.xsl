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
            <script type="text/javascript">
            <![CDATA[
		        dbforms2_fBrowserFieldId = '';
				dbforms2_fBrowserLastLocation = '';

				openFileBrowser = function(fieldId) {
				    var fBrowserUrl = bx_webroot + 'webinc/fck/editor/filemanager/browser/default/browser.html?Type=files&Connector=connectors/php/connector.php';
				
				    var currentFile = document.getElementById(fieldId).value;
				    if (currentFile == '' && dbforms2_fBrowserLastLocation) {
				        currentFile = dbforms2_fBrowserLastLocation;
				    }
				    var filesDir = '/files';
				    sParentFolderPath = currentFile.substring(filesDir.length, currentFile.lastIndexOf('/', currentFile.length - 2) + 1);
				
				    if(sParentFolderPath != '' && (sParentFolderPath.indexOf('/') != -1))
				        fBrowserUrl += '&RootPath=' + escape(sParentFolderPath);
				    
				    if(typeof fBrowserWindow != 'undefined' && !fBrowserWindow.closed) {
				        fBrowserWindow.location.href = fBrowserUrl;
				    } else {
				        fBrowserWindow = window.open(fBrowserUrl, 'fBrowser', 'width=800,height=600,location=no,menubar=no');
				    }
				
				    fBrowserWindow.focus();
				
				    dbforms2_fBrowserFieldId = fieldId;
				    
				    SetUrl = function(url) {
				        if(dbforms2_fBrowserFieldId != '') {
				           document.getElementById(fieldId).value = url;
				            dbforms2_fBrowserLastLocation = url;
				        }
				        dbforms2_fBrowserFieldId = '';
				    }
				}
				]]>
            </script>
            </head>
            <body onload="			dbforms2.form = document.getElementById('bx_news_send');
			alert(dbforms2.form);">
            
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