
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
        exclude-result-prefixes="rdf dc php xhtml rss bxf blog">
    <xsl:import href="subeditor.xsl"/>
    <xsl:import href="../../../../../../themes/standard/admin/adminfields.xsl"/>

    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
    <xsl:include href="../../tabs.xsl"/>
    <xsl:variable name="openTabType" select="$opentabs/opentabs/tab[@type = 'subtabs_files']"/>
    <xsl:variable name="pluginName" select="'admin_edit][options'"/>

    <xsl:template match="/">
        <xsl:choose>
            <xsl:when test="/bx/plugin/ajaxpost">
List updated
</xsl:when>
            <xsl:otherwise>
                <xsl:call-template name="root"/>
            </xsl:otherwise>
        </xsl:choose>



    </xsl:template>

    <xsl:template name="head">
        <script type="text/javascript" language="JavaScript">
<![CDATA[
function onLoad() {
initLists();
}

]]></script>

        <script src="/webinc/js/prototype.js" type="text/javascript"></script>
        <script src="/webinc/js/scriptaculous/scriptaculous.js?load=effects,dragdrop" type="text/javascript"></script>
        <script src="/webinc/plugins/blog/sidebar.js" type="text/javascript"></script>


    </xsl:template>


    <xsl:template name="editorContent">
        <xsl:call-template name="displayTabs">
            <xsl:with-param name="selected" select="'sidebar'"/>
        </xsl:call-template>

        <div id="subeditor">
            <div id="subeditorright"></div>
            <div id="subeditorleft">
            
           
<div class="sortableContainer">
<h1>Left</h1>
                <ul class="sortable" id="list1">
                    <xsl:for-each select="/bx/plugin/data/sidebar[@id = '1']/sidebar">
                        <li id="item_{id}">
                        <a href="#editItem{id}" onclick="return editItem({id})" style="float: right">[+]</a>
                        
                            <xsl:value-of select="name"/>
                        </li>
                    </xsl:for-each>
                </ul>
                </div>
                
                 <div class="sortableContainer">
            <h1>Not assigned</h1>
                <ul class="sortable" id="list0">
                    <xsl:for-each select="/bx/plugin/data/sidebar[@id = '0']/sidebar">
                        <li id="item_{id}">
                        <a href="#editItem{id}" onclick="return editItem({id})" style="float: right">[+]</a>
                        
                            <xsl:value-of select="name"/>
                        </li>
                    </xsl:for-each>
                </ul>
</div>
                <div class="sortableContainer">
<h1>Right</h1>


                <ul class="sortable" id="list2">
                    <xsl:for-each select="/bx/plugin/data/sidebar[@id = '2']/sidebar">
                        <li id="item_{id}">
                        <a href="#editItem{id}" onclick="return editItem({id})"  style="float: right">[+]</a>
                            <xsl:value-of select="name"/>
                        </li>
                    </xsl:for-each>
                </ul>

      

</div>
<br clear="all"/>
          <p id="list-info"></p>
            </div>
        </div>
    </xsl:template>

</xsl:stylesheet>

