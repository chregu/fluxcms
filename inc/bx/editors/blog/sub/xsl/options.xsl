<xsl:stylesheet version="1.0" 
    xmlns:sixcat="http://sixapart.com/atom/category#"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:atom="http://purl.org/atom/ns#"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xhtml="http://www.w3.org/1999/xhtml"
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:bxf="http://bitflux.org/functions"
    xmlns:rss="http://purl.org/rss/1.0/"
    xmlns:blog="http://bitflux.org/doctypes/blog"
    xmlns:php="http://php.net/xsl"
    xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
    exclude-result-prefixes="rdf dc php xhtml rss bxf blog"
>
    <xsl:import href="subeditor.xsl"/>
    <xsl:import href="../../../../../../themes/standard/admin/adminfields.xsl"/>
 
    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
    <xsl:include href="../../tabs.xsl"/>
    <xsl:variable name="openTabType" select="$opentabs/opentabs/tab[@type = 'subtabs_files']"/>
    <xsl:variable name="pluginName" select="'admin_edit][options'"/>
    
    <xsl:template name="head">
        <script type="text/javascript" language="JavaScript">
        <![CDATA[
            function onLoad() {
            }
            
        ]]></script>
    </xsl:template>
    
    
    <xsl:template name="editorContent">
        <xsl:call-template name="displayTabs">
            <xsl:with-param name="selected" select="'options'"/>
        </xsl:call-template>

        <div id="subeditor">
            <div id="subeditorright">
            </div>
            <div id="subeditorleft">
            
            
                <form name="fileUpload" method="post" action="." enctype="multipart/form-data">
            
                <xsl:apply-templates select="/bx/plugin/options/field"/>
             <!--   <h3><i18n:text>Site Name</i18n:text></h3>
                    <p><input name="{$formName}[options][sitename]" size="40" type="text" value="{/bx/plugin/options/option[@name='sitename']/@value}"/></p>
                    
                -->
                <p><input name="{$formName}[saveOptions]" type="submit" class="button" value="Submit" i18n:attr="value"/></p>
                </form>
            </div>
        </div>
    </xsl:template>

</xsl:stylesheet>

