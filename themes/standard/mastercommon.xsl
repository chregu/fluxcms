<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
        xmlns:xhtml="http://www.w3.org/1999/xhtml"
        xmlns="http://www.w3.org/1999/xhtml" 
        xmlns:forms="http://bitflux.org/forms" 
	xmlns:php="http://php.net/xsl"
     xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
	exclude-result-prefixes="xhtml forms php i18n"
	
	>
    <xsl:param name="webroot"/>
    
    
    <xsl:template name="html_head_scripts">
        <xsl:call-template name="html_head_scripts_default"/>
    </xsl:template>
    
    <xsl:template name="html_head_scripts_default">
    <xsl:text>
            </xsl:text>
        <xsl:for-each select="/bx/javascripts/src">
            <script type="text/javascript" src="{$webroot}{text()}"></script>
                    <xsl:text>
            </xsl:text>
        </xsl:for-each>
    </xsl:template>
    

<xsl:template name="html_head_custom">                                     
</xsl:template>    
    
    
    
    </xsl:stylesheet>
