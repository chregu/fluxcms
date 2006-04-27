<?xml version="1.0"?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output encoding="utf-8" method="xml"/>

    
<xsl:param name="webroot" select="webroot"/>
<xsl:variable name="adminPath" select="'/admin/navi/kupu'"/>
<xsl:variable name="drawer" select="/navitree/params/param[@name='drawer']/@value"/>

<!-- Transform to kupus library xml -->
<xsl:template match="/">
<xsl:choose>
	<xsl:when test="/navitree/path = '/' and count(/navitree/params/param[@name='nolibrary'] ) = 0">
	    
        <libraries>
	        <library id="home">
                <title>Home</title>
                <src><xsl:value-of select="concat($webroot, 'admin/navi/kupu/', '?nolibrary=true&amp;drawer=', $drawer)"/></src>
                <icon><xsl:value-of select="concat($webroot, 'admin/webinc/img/icons/fileicon_folder.gif')"/></icon>
            </library>
            <library id="images">
            <title>Images</title>
            <src><xsl:value-of select="concat($webroot, 'admin/navi/kupu/files/images/', '?drawer=', $drawer)"/></src>
            <icon><xsl:value-of select="concat($webroot, 'admin/webinc/img/icons/fileicon_folder.gif')"/></icon>
            </library>
            
            <library id="themes">
            <title>Themes</title>
            <src><xsl:value-of select="concat($webroot, 'admin/navi/kupu/themes/', '?drawer=', $drawer)"/></src>
            <icon><xsl:value-of select="concat($webroot, 'admin/webinc/img/icons/fileicon_folder.gif')"/></icon>
            </library>
            
            <library id="data">
                <title>Files</title>
                <src><xsl:value-of select="concat($webroot, 'admin/navi/kupu/files/', '?drawer=', $drawer)"/></src>
                <icon><xsl:value-of select="concat($webroot, 'admin/webinc/img/icons/fileicon_folder.gif')"/></icon>
            </library>
            
            <library id="download">
                <title>Downloads</title>
                <src><xsl:value-of select="concat($webroot, 'admin/navi/kupu/files/downloads/', '?drawer=', $drawer)"/></src>
                <icon><xsl:value-of select="concat($webroot, 'admin/webinc/img/icons/fileicon_folder.gif')"/></icon>
            </library>
            
        </libraries>
    </xsl:when>
  
    <xsl:otherwise>
        <collection>
            <uri><xsl:value-of select="concat($webroot, 'admin/navi/kupu', /navitree/path)"/></uri>
            <items>
                        <xsl:element name="collection">
                             <xsl:attribute name="id"><xsl:text>dirup</xsl:text>
                                <xsl:value-of select="translate(/navitree/path, '/', '_')"/>
                             </xsl:attribute>
                             <uri><xsl:value-of select="concat($webroot, 'admin/navi/kupu', /navitree/path, '../')"/></uri>
                             <title><xsl:text>..</xsl:text></title>
                             <src><xsl:value-of select="concat($webroot, 'admin/navi/kupu', /navitree/path, '../')"/></src>
                        </xsl:element>
                 
               <xsl:choose>
                 <xsl:when test="$drawer = 'image'">
                    <xsl:apply-templates select="/navitree/item[contains(@mimetype, 'image/') or (@mimetype='httpd/unix-directory')]"/>
                 </xsl:when>
                 
                 <xsl:when test="$drawer = 'asset'">
                    <xsl:apply-templates select="/navitree/item"/> 
                 </xsl:when>
                 
                 <xsl:when test="$drawer = 'library'">
                    <xsl:apply-templates select="/navitree/item[1 or (@mimetype='httpd/unix-directory')]"/>
                 </xsl:when>

                 <xsl:otherwise>
                    <xsl:apply-templates select="/navitree/item[@mimetype='text/html' or (@mimetype='httpd/unix-directory')]"/>
                 </xsl:otherwise>
              </xsl:choose>
            </items>
        </collection>
    </xsl:otherwise>
</xsl:choose>
</xsl:template>


<xsl:template match="item">
    <xsl:variable name="fileurl" select="concat($webroot, /navitree/path, @uri)"/>
    <resource id="res{@title}">
        <uri><xsl:value-of select="$fileurl"/></uri>
        <title><xsl:value-of disable-output-escaping="yes" select="@title"/></title>
        <icon><xsl:value-of select="@icon"/></icon>
        <src><xsl:value-of select="$fileurl"/></src>
        <description/>
    </resource>
</xsl:template>

<xsl:template match="item[contains(@mimetype, 'image/')]">
    
    <xsl:variable name="fileurl" select="concat($webroot, /navitree/path, @uri)"/>
    <xsl:variable name="thumburl" select="concat($webroot, 'dynimages/60', /navitree/path, @uri)"/>
    <resource id="res_{@title}">
        <uri><xsl:value-of select="substring-after($fileurl,$webroot)"/></uri>
        <title><xsl:value-of select="@title"/></title>
        <icon><xsl:value-of select="@icon"/></icon>
        <preview><xsl:value-of select="$thumburl"/></preview>
        <src><xsl:value-of select="substring-after($fileurl,$webroot)"/></src>
        <description/>
    </resource>

</xsl:template>


<xsl:template match="item[@mimetype='text/html']">

    <xsl:variable name="fileurl" select="concat($webroot, substring-after(/navitree/path, '/data/'), @uri)"/>
    
    <resource id="res{concat(translate(/navitree/path, '/', '_'), translate(@uri, '.', '_'))}">
        <uri><xsl:value-of select="concat($webroot, substring-after(/navitree/path, '/data/'), @uri)"/></uri>
        <title><xsl:value-of disable-output-escaping="yes"  select="@title"/></title>
        <icon><xsl:value-of select="@icon"/></icon>
        <src><xsl:value-of select="$fileurl"/></src>
        <description/>
    </resource>

</xsl:template>

<xsl:template match="item[@mimetype='httpd/unix-directory']">
    <xsl:variable name="fileUrl">
        <xsl:value-of select="@uri"/>
    </xsl:variable>
    
    <!-- FIXME: data/ path is mapped to / in admintree generator, therefore we must
         add it to the filepath here -->
    <xsl:variable name="resourceUrl">
        <xsl:choose>
            <xsl:when test="contains(/navitree/path, '/data/')">
                <xsl:value-of select="concat($webroot, $adminPath, '/data', $fileUrl, '?drawer=', $drawer)"/>
            </xsl:when>
            <xsl:when test="starts-with($fileUrl,'/')">
                <xsl:value-of select="concat($webroot, $adminPath, $fileUrl, '?drawer=', $drawer)"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="concat($webroot, $adminPath, /navitree/path, $fileUrl, '?drawer=', $drawer)"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    
    <collection id="coll_{@uri}">
        <uri><xsl:value-of select="$resourceUrl"/></uri>
        <icon><xsl:value-of select="@icon"/></icon>
        <title><xsl:value-of disable-output-escaping="yes"  select="@title "/></title>
        <description/>
        <src><xsl:value-of select="$resourceUrl"/></src>
    </collection>
</xsl:template>

<xsl:template name="dirup">
    

</xsl:template>

</xsl:stylesheet>
