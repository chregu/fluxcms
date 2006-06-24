
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
            <xsl:when test="/bx/plugin/sendedit">
                <xsl:value-of select="/bx/plugin/sendedit/text()" disable-output-escaping="yes"/>
                
            </xsl:when>
            <xsl:when test="/bx/plugin/edit">
                <xsl:value-of select="/bx/plugin/edit/text()" disable-output-escaping="yes"/>
            </xsl:when>
    <xsl:when test="/bx/plugin/delete">
                <xsl:value-of select="/bx/plugin/delete/text()" disable-output-escaping="yes"/>
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

        <script src="{$webroot}webinc/js/prototype.js" type="text/javascript"></script>
        <script src="{$webroot}webinc/js/scriptaculous/scriptaculous.js?load=effects,dragdrop" type="text/javascript"></script>
        <script src="{$webroot}webinc/plugins/blog/sidebar.js" type="text/javascript"></script>


    </xsl:template>


    <xsl:template name="editorContent">
        <xsl:call-template name="displayTabs">
            <xsl:with-param name="selected" select="'sidebar'"/>
        </xsl:call-template>

        <div id="subeditor">
            <div id="subeditorright"></div>
            <div id="subeditorleft">
              <div id="sidebar_edit" style="display: none;">
<h3 class="blog">Edit entry</h3>
                <form id="editform" name="edit" onsubmit="return sendEdit();">
                    <input id="id" name="bx[plugins][admin_edit][edit][id]" type="hidden" value=""/>
                    <label>Name:</label>
                    <input id="name" name="bx[plugins][admin_edit][edit][name]" type="text" value=""/>
                    <br/>
                    <label>Content:</label>
                    <textarea id="content" name="bx[plugins][admin_edit][edit][content]"/>
                    <br/>
                    <label>is XML:</label> <input style="width: 1em;" type="checkbox" id="isxml" name="bx[plugins][admin_edit][edit][isxml]"/><br/>
                    <input type="submit" style="width: 50px; margin-right: 10px;" value="Save"/>
                    <input type="submit" accesskey="s" onclick="$('sidebar_edit').style.display = 'none'"  style="width: 100px; margin-right: 10px;" value="Save &amp; close"/>
                    <input type="button" style="width: 50px; margin-right: 10px;" value="Close" onclick="$('sidebar_edit').style.display = 'none'"/>
                    <input type="button" style="width: 50px;" value="Delete" onclick="deleteEntry(); $('sidebar_edit').style.display = 'none'"/>
                </form>
            </div>

                <xsl:call-template name="list">
                    <xsl:with-param name="title">Left</xsl:with-param>
                    <xsl:with-param name="id" select="1"/>
                </xsl:call-template>
                

                <xsl:call-template name="list">
                    <xsl:with-param name="title">Not assigned</xsl:with-param>
                    <xsl:with-param name="id" select="0"/>
                </xsl:call-template>


                <xsl:call-template name="list">
                    <xsl:with-param name="title">Right</xsl:with-param>
                    <xsl:with-param name="id" select="2"/>
                </xsl:call-template>


                <br clear="all"/>
                <p id="list-info"></p>
                <p>
                    <a href="#create" onclick="return createNew();">Create new entry</a>
                </p>
            </div>

            
        </div>
    </xsl:template>

    <xsl:template name="list">
        <xsl:param name="title"/>
        <xsl:param name="id"/>

        <div class="sortableContainer">
            <h1>
                <xsl:value-of select="$title"/>
            </h1>


            <ul class="sortable" id="list{$id}">
                <xsl:for-each select="/bx/plugin/data/sidebar[@id = $id]/sidebar">
                    <li id="item_{id}">
                        <a href="#editItem{id}" onclick="return editItem({id})" style="float: right">[...]</a>
                        <xsl:value-of select="name"/>
                    </li>
                </xsl:for-each>
            </ul>



        </div>

    </xsl:template>

</xsl:stylesheet>

