
<xsl:stylesheet version="1.0" 
    xmlns:sixcat="http://sixapart.com/atom/category#"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:atom="http://purl.org/atom/ns#"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xhtml="http://www.w3.org/1999/xhtml"
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:bxf="http://bitflux.org/functions"
    xmlns:rss="http://purl.org/rss/1.0"
    xmlns:blog="http://bitflux.org/doctypes/blog"
    xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
    exclude-result-prefixes="rdf dc xhtml rss bxf blog"
>

    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

    <xsl:template name="displayTabs">
        <xsl:param name="selected" select="'overview'"/>
        <div class="navitabs" id="navitabs">
            <ul>
            	<xsl:if test="php:functionString('bx_editors_blog::isTabAllowed','post') = 'true'">
	                <xsl:call-template name="doTab">
	                    <xsl:with-param name="name" select="'Overview'"/> 
	                    <xsl:with-param name="uri" select="'.'"/> 
	                    <xsl:with-param name="selected" select="$selected"/>
	                </xsl:call-template>
                </xsl:if>
                <xsl:if test="php:functionString('bx_editors_blog::isTabAllowed','post') = 'true'">
	                <xsl:call-template name="doTab">
	                    <xsl:with-param name="name" select="'Post'"/> 
	                    <xsl:with-param name="uri" select="'newpost.xml'"/> 
	                    <xsl:with-param name="selected" select="$selected"/>
	                </xsl:call-template>
	            </xsl:if>
                <xsl:if test="php:functionString('bx_editors_blog::isTabAllowed','categories') = 'true'">
	                <xsl:call-template name="doTab">
	                    <xsl:with-param name="name" select="'Categories'"/> 
	                    <xsl:with-param name="uri" select="'sub/categories/'"/> 
	                    <xsl:with-param name="selected" select="$selected"/>
	                </xsl:call-template>
	            </xsl:if>
                <xsl:if test="php:functionString('bx_editors_blog::isTabAllowed','blogroll') = 'true'">
	                <xsl:call-template name="doTab">
	                    <xsl:with-param name="name" select="'Links'"/> 
	                    <xsl:with-param name="uri" select="'sub/blogroll/'"/> 
	                    <xsl:with-param name="selected" select="$selected"/>
	                </xsl:call-template>
	            </xsl:if>
                <xsl:if test="php:functionString('bx_editors_blog::isTabAllowed','gallery') = 'true'">     
	                <xsl:call-template name="doTab">
	                    <xsl:with-param name="name" select="'Gallery'"/> 
	                    <xsl:with-param name="uri" select="'sub/gallery/'"/> 
	                    <xsl:with-param name="selected" select="$selected"/>
	                </xsl:call-template>
	            </xsl:if>
                <xsl:if test="php:functionString('bx_editors_blog::isTabAllowed','files') = 'true'">    
	                <xsl:call-template name="doTab">
	                    <xsl:with-param name="name" select="'Files'"/> 
	                    <xsl:with-param name="uri" select="'sub/files/'"/> 
	                    <xsl:with-param name="selected" select="$selected"/>
	                </xsl:call-template>
	            </xsl:if>
                
                   <xsl:if test="php:functionString('bx_editors_blog::isTabAllowed','sidebar') = 'true'">    
	                <xsl:call-template name="doTab">
	                    <xsl:with-param name="name" select="'Sidebar'"/> 
	                    <xsl:with-param name="uri" select="'sub/sidebar/'"/> 
	                    <xsl:with-param name="selected" select="$selected"/>
	                </xsl:call-template>
	            </xsl:if>   
                
                <xsl:if test="php:functionString('bx_editors_blog::isTabAllowed','options') = 'true'">    
	                <xsl:call-template name="doTab">
	                    <xsl:with-param name="name" select="'Blog Options'"/> 
	                    <xsl:with-param name="uri" select="'sub/options/'"/> 
	                    <xsl:with-param name="selected" select="$selected"/>
	                </xsl:call-template>
	            </xsl:if>   
            </ul>
              <br clear="all"/>
        </div>
      
    </xsl:template>
    
    <xsl:template name="doTab">
        <xsl:param name="name"/>
        <xsl:param name="uri"/>
        <xsl:param name="selected"/>
				<xsl:variable name="collectionRelUri" select="substring-after($collectionUri,'/')"/>
        <li> 
            <a href="{concat($webroot,$collectionRelUri, $collectionUriOfId, $uri)}"><xsl:if test="contains($uri, $selected) or ($selected = 'overview' and $uri = '.') or ($selected = 'post' and $uri='newpost.xml')"><xsl:attribute name="class">selected</xsl:attribute></xsl:if><i18n:text><xsl:value-of select="$name"/></i18n:text></a>
        </li>
    </xsl:template>
    
    <xsl:template name="doSubTab">
    <xsl:param name="name"/>
    <xsl:param name="title"/>
    
    <xsl:param name="default" select="'false'"/>
                       
     <li id="li_{$name}" >
      
                                                    
     
     <a href="#" onclick="switchTab('{$name}',false,true)">
      <xsl:if test="$name = $openTabType/text() or ($default = 'true' and not($openTabType)) ">
               <xsl:attribute name="class">selected</xsl:attribute>
       </xsl:if>
     
     <i18n:text><xsl:value-of select="$title"/></i18n:text></a></li>
     </xsl:template>

    
</xsl:stylesheet>

