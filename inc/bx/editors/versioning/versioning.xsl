<?xml version="1.0"?>
<xsl:stylesheet version="1.0" 
 xmlns:php="http://php.net/xsl"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml">
    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

    <xsl:param name="url" select="'/'"/>
    <xsl:param name="dataUri" select="'/'"/>
    <xsl:param name="id" select="'/'"/>
    <xsl:param name="webroot" select="'/'"/>
<xsl:param name="returnPostCode" />

    <xsl:template match="/">
        <html>
            <head>
            <title>Edit  <xsl:value-of select="$id"/></title>
             <link rel="stylesheet" href="{$webroot}themes/standard/admin/css/formedit.css" type="text/css" />
             
            <xsl:if test="$returnPostCode = 201">
                <script type="text/javascript">
                    window.parent.navi.Navitree.reload('/<xsl:value-of select="substring($id,1,string-length($id)-1)"/>');
                </script>
            </xsl:if>
            
            </head>
            <body>
                <h1 class="pageTitle">Edit<xsl:value-of select="$id"/></h1>
                <xsl:if test="contains($dataUri,$id) and not(php:functionString('is_writable',$dataUri))">
                <h2 style="color: red;"><xsl:value-of select="$id"/> is not writable!</h2>
                Changes made here will not be saved<br/><br/>
                </xsl:if>
                Boah!
            </body>
        </html>
    </xsl:template>

    <xsl:template name="imageupload">
        <form action="" method="POST" enctype="multipart/form-data">
            <textarea style="width: 95%" name="bx[plugins][admin_edit][fullxml]" cols="80" rows="30">
            <xsl:copy-of select="/text/text()"/>
            </textarea>
            <p><input type="submit" accesskey="s" name="bx[plugins][admin_edit][submit]" value="Submit"/></p>
        </form>
    </xsl:template>
    
</xsl:stylesheet>
