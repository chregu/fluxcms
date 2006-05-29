<?xml version="1.0"?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns:xhtml="http://www.w3.org/1999/xhtml" 
    xmlns="http://www.w3.org/1999/xhtml" 
    xmlns:php="http://php.net/xsl" 
    xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
    xmlns:blog="http://bitflux.org/doctypes/blog"
    exclude-result-prefixes="xhtml php blog i18n"
    >
    
    <xsl:output encoding="utf-8" method="xml" 
        />
        

<xsl:template match="/|comment()|processing-instruction()" mode="xhtml">
    <xsl:copy>
        <xsl:apply-templates mode="xhtml"/>
    </xsl:copy>
</xsl:template>

<!-- translate links from filename.lang.ext to filename.html -->
<xsl:template match="*[local-name()='a']" mode="xhtml">
     
     <xsl:element name="a">
        <xsl:if test="@href">
<!-- FIXME: do we really need that? -->        
            <xsl:attribute name="href">
                <xsl:value-of select="php:functionString('bx_helpers_uri::translateUri' , @href)"/>
             </xsl:attribute>
        </xsl:if>
        <xsl:apply-templates select="@*[not(local-name()='href')]" mode="xhtml"/>
        <xsl:apply-templates select="node()" mode="xhtml"/>
     </xsl:element>
</xsl:template>

<xsl:template match="*" mode="xhtml">
<xsl:element name="{local-name()}">
    <xsl:apply-templates select="@*" mode="xhtml"/>
    <xsl:apply-templates mode="xhtml"/>
</xsl:element>
</xsl:template>

<xsl:template match="blog:*" mode="xhtml">
</xsl:template>

<xsl:template match="*[namespace-uri() = 'http://apache.org/cocoon/i18n/2.1']" mode="xhtml">
    <xsl:copy>
        <xsl:apply-templates select="@*" mode="xhtml"/>
        <xsl:apply-templates mode="xhtml"/>
    </xsl:copy>
</xsl:template>

<xsl:template match="@*" mode="xhtml">
    <xsl:copy-of select="."/>
</xsl:template>

<!-- 
    add empty alt attribute when not set - 
     xhtml validity issue ... 
-->
<xsl:template match="*[local-name()='img']" mode="xhtml">
     <xsl:element name="img">
        <xsl:apply-templates select="@*" mode="xhtml"/>
        <xsl:if test="not(@alt)">
            <xsl:attribute name="alt">
                <xsl:value-of select="@src"/>
            </xsl:attribute>
        </xsl:if>
        <xsl:apply-templates select="node()" mode="xhtml"/>
    </xsl:element>
</xsl:template>

<!-- kupu bockmist ;) -->
<xsl:template match="xhtml:br/@type" mode="xhtml">
</xsl:template>

 <xsl:template name="littleLogin">
    <xsl:variable name="username" select="php:functionString('bx_helpers_perm::getUsername')"/>
    <xsl:choose>
    <xsl:when test="$username = ''">
    <h3><a onclick="document.getElementById('littleLogin').style.display = 'block'; return false; " href="{$webroot}admin/">Login</a></h3>
    
 <form style="display: none;" method="post" action="{php:functionString('bx_helpers_uri::getRequestUri')}" id="littleLogin">
    <label>User:</label><input size="10" name="username" type="text" class="input"/><br/>
    <label>Pwd:</label><input size="10"  name="password" type="password" class="input"/><br/>

    <label>&#160;</label>  <input type="submit" value="Submit"  />
  </form>
  </xsl:when>
  <xsl:otherwise>
  <p>Hello <xsl:value-of select="$username"/>.
  <a href="{$webroot}admin/?logout&amp;back={php:functionString('bx_helpers_uri::getRequestUri')}">Logout</a>.
  <xsl:if test="php:functionString('bx_helpers_perm::isAdmin')  = 'true'">
  <br/>
  <a href="{$webroot}admin/">Go to Admin</a>
  </xsl:if>
  </p>
  
  </xsl:otherwise>
  </xsl:choose>
    </xsl:template>


</xsl:stylesheet>
