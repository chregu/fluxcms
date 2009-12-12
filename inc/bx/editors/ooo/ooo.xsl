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
            <title>Edit File <xsl:value-of select="$id"/></title>
                         <link rel="stylesheet" href="{$webroot}themes/standard/admin/css/formedit.css" type="text/css" />

             <xsl:if test="$updateTree">
            <script type="text/javascript">
            window.parent.navi.Navitree.reload('/<xsl:value-of select="substring($dataUri,1,string-length($dataUri)-1)"/>');
            </script>
            </xsl:if>
            
                         
            </head>
            <body>
            
                <h1 class="pageTitle">Edit File <xsl:value-of select="$id"/> with your   OpenOffice.org</h1>
                <xsl:if test="not(php:functionString('is_writable',$dataUri))">
                
                <h2 style="color: red;"><xsl:value-of select="$dataUri"/> is not writable!</h2>
                Changes made here will not be saved<br/><br/>
                </xsl:if>
                <xsl:call-template name="imageupload"/>
                    <a href="{$webroot}admin/edit{$id}?editor=ooo&amp;download=true" >Download <xsl:value-of select="$id"/> as  OpenOffice.org file</a>
                
            </body>
        </html>
    </xsl:template>

    <xsl:template name="imageupload">
        <form action="" method="POST" enctype="multipart/form-data">
            <p>
                <h3>Upload new OpenOffice.org 2 file. (*.odt)</h3>
                <input type="file" name="uploadfile" size="20"/>
                 <br/>(Max upload size: 
             <xsl:value-of select="php:functionString('ini_get','upload_max_filesize')"/>)
            </p>
            <p><input type="submit" name="bx[plugins][admin_edit][submit]" value="Upload file"/>
           
            </p>
        </form>
       
    </xsl:template>
    
</xsl:stylesheet>