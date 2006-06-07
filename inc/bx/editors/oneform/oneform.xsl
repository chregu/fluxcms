<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:php="http://php.net/xsl" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml">
    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

    <xsl:param name="url" select="'/'"/>
    <xsl:param name="dataUri" select="'/'"/>
    <xsl:param name="id" select="'/'"/>
    <xsl:param name="webroot" select="'/'"/>
    <xsl:param name="returnPostCode"/>
<xsl:variable name="mimetype" select="php:functionString('popoon_helpers_mimetypes::getFromFileLocation',$dataUri)"/>

    <xsl:template match="/">
        <html>
            <head>
                <title>Edit  <xsl:value-of select="$id"/>
                </title>
                <link rel="stylesheet" href="{$webroot}themes/standard/admin/css/formedit.css" type="text/css"/>
                <script type="text/javascript" src="{$webroot}webinc/editors/oneform/save.js"> </script>
                <script type="text/javascript" src="{$webroot}webinc/js/sarissa_dbform.js"></script>
                <script type="text/javascript">
                <xsl:if test="$returnPostCode = 201">
                    
                    window.parent.navi.Navitree.reload('/<xsl:value-of select="substring($id,1,string-length($id)-1)"/>');
                 
            </xsl:if>

      
           
                  
                     <xsl:choose>
                <xsl:when test=" contains($id,'-txt.')">
                    var noXMLCheck = true;
                </xsl:when>
                <xsl:otherwise>
                    var noXMLCheck = false;
                </xsl:otherwise>
                </xsl:choose>
                </script>
                     
            </head>
            <body>
                <h1 class="pageTitle">Edit  <xsl:value-of select="$id"/>
                </h1>
<xsl:variable name="isNotWritable" select="contains($dataUri,$id) and not(php:functionString('is_writable',$dataUri))"/>
                <xsl:if test="$isNotWritable">
                    <h2 style="color: red;">
                        <xsl:value-of select="$id"/> is not writable!</h2>
                Changes made here will not be saved<br/>
                    <br/>
                </xsl:if>
                
                <form action="{php:functionString('bx_helpers_uri::getRequestUri')}" enctype="multipart/form-data" method="post" >
                 <xsl:if test="not($isNotWritable)">
                 <xsl:attribute name="onsubmit">return liveSave(this,document.getElementById('area'),'<xsl:value-of select="$mimetype"/>');</xsl:attribute>
                 </xsl:if>
                
                    <textarea style="width: 95%" id="area" name="bx[plugins][admin_edit][fullxml]" cols="80" rows="30">
                        <xsl:copy-of select="/text/text()"/>
                    </textarea>
                    <p>
                        <input type="submit" accesskey="s" name="bx[plugins][admin_edit][submit]" value="Submit"/>

                        <span id="LSResult">Document saved ...</span>
                    </p>
                </form>
            </body>
        </html>
    </xsl:template>
    

</xsl:stylesheet>
