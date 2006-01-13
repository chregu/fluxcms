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
    
    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

    <xsl:include href="../../tabs.xsl"/>
    
    <xsl:variable name="filesRoot" select="/bx/plugin/files/@filesRoot"/>
    <xsl:variable name="collectionId" select="/bx/plugin/files/@collectionId"/>
    <xsl:variable name="openTabType" select="$opentabs/opentabs/tab[@type = 'subtabs_files']"/>

    <xsl:template name="head">
        <script type="text/javascript" language="JavaScript">
        <![CDATA[
            function onLoad() {
                if(typeof i18n == 'undefined')
                    i18n = parent.i18n;
            }
            
            function confirmResourceDelete(id, title) {
                if(confirm(i18n.translate('Do you really want to delete the file "{0}"?', [title])) == true) 
                    window.location.href = '.?del=' + id;
            }
            
            function confirmCollectionDelete(id, title) {
                if(confirm(i18n.translate('Do you really want to delete "{0}"?', [title])) == true) 
                    window.location.href = '.?del=' + id;
            }
            
        ]]></script>
		<script type="text/javascript" src="{$webroot}admin/webinc/js/showhidelayers.js"/>
		
    </xsl:template>
    
    
    <xsl:template name="editorContent">
        <xsl:call-template name="displayTabs">
            <xsl:with-param name="selected" select="'files'"/>
        </xsl:call-template>
        <div class="navitabs" name="subtabs_files">
            <ul>
                <xsl:call-template name="doSubTab">
                    <xsl:with-param name="name">addfile</xsl:with-param>
                    <xsl:with-param name="title">Add File</xsl:with-param>
                    <xsl:with-param name="default">true</xsl:with-param>
                </xsl:call-template>
                <xsl:call-template name="doSubTab">
                    <xsl:with-param name="name">createcollection</xsl:with-param>
                    <xsl:with-param name="title">Create Subcollection</xsl:with-param>
                </xsl:call-template>
            </ul>
            <br clear="all"/>
        </div>
        
        <div id="subeditor">
            <div id="subeditorright">
                <h3>/<xsl:value-of select="$collectionId"/></h3>
                <ul class="collectionlist">
                    <xsl:if test="$collectionId != ''"><li class="first"><a href=".."><img border="0" src="{$webroot}admin/webinc/img/up.gif"/></a></li></xsl:if>
                    <xsl:apply-templates select="/bx/plugin/files/resource[@mimeType = 'httpd/unix-directory']" mode="collection"/>
                </ul>
                <ul>
                    <xsl:apply-templates select="/bx/plugin/files/resource[@mimeType != 'httpd/unix-directory']"/>
                </ul>
            </div>
            <div id="subeditorleft">
                <div id="tab_addfile" class="tabcontentHidden"><xsl:if test="$openTabType/text() = 'addfile' or not($openTabType)"><xsl:attribute name="class">tabcontent</xsl:attribute></xsl:if>
                    <h3><i18n:text>Add a New File</i18n:text></h3>
                    <p>
                        <form name="fileUpload" method="post" action="." enctype="multipart/form-data" onsubmit="MM_showHideLayers('wait_layer','','show');">
                            <input name="bx[plugins][admin_addresource][file]" type="file" value=""/><br/>
                            <input name="{$formName}[addFile]" type="submit" class="button" value="Add" onclick="this.disabled;this.value='wait...';" i18n:attr="value"/>
                            <input type="hidden" name="{$formName}[parentCollection]" value="{$collectionId}"/>
                        </form>
                    </p>
					<div id="wait_layer" style="background-color: #ffffff; text-align:center; border:#000000 solid 1px; position:absolute; width:300px; height:115px; z-index:1; left: 200px; top: 200px; visibility: hidden">
						<h3>Upload in progress</h3>
						<p><img src="/themes/standard/admin/images/wait_bar.gif" /><br />
						File is uploading, please wait. This window will be closed after upload.<br />
						</p>
					</div>
                </div>

                <div id="tab_createcollection" class="tabcontentHidden"><xsl:if test="$openTabType/text() = 'createcollection'"><xsl:attribute name="class">tabcontent</xsl:attribute></xsl:if>
                    <h3><i18n:text>Create a New Subcollection</i18n:text></h3>
                    <p>
                        <form name="createCollection" method="post" action=".">
                           <input name="{$formName}[collectionUri]" type="text" value=""/><br/>
                           <input name="{$formName}[addCollection]" type="submit" class="button" value="Create" i18n:attr="value"/>
                            <input type="hidden" name="{$formName}[parentCollection]" value="{$collectionId}"/>
                        </form>
                    </p>
                </div>
            </div>
        </div>
    </xsl:template>
    
    <xsl:template match="resource">
        <xsl:variable name="displayName" select="substring-after(@id, concat($filesRoot, $collectionId))"/>
        <li><a href="#" onclick="confirmResourceDelete('{@id}', '{$displayName}');"><img border="0" alt="delete" src="{$webroot}admin/webinc/img/icons/delete.gif"/></a><a href="{@id}" target="_blank"><xsl:value-of select="$displayName"/></a></li>
    </xsl:template>
    
    <xsl:template match="resource" mode="collection">
        <xsl:variable name="displayName" select="substring-after(@id, concat($filesRoot, $collectionId))"/>
        <li><a href="#" onclick="confirmCollectionDelete('{@id}', '{$displayName}');"><img border="0" alt="delete" src="{$webroot}admin/webinc/img/icons/delete.gif"/></a><a href="{$displayName}"><img border="0" src="{$webroot}admin/webinc/img/icons/fileicon_folder.gif"/><xsl:value-of select="@displayName"/></a></li>
    </xsl:template>
    

</xsl:stylesheet>

