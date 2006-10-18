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

    <xsl:variable name="id" select="php:functionString('bx_helpers_globals::GET','id')"/>

    <xsl:variable name="imgSmallWidth" select="'100'"/>
    <xsl:variable name="imgLargeWidth" select="'320'"/>
    <xsl:variable name="virtualRoot" select="/bx/plugin/images/@virtualRoot"/>
    <xsl:variable name="baseCollection" select="/bx/plugin/images/@baseCollection"/>
    <xsl:variable name="galleryId" select="/bx/plugin/images/@galleryId"/>
    <xsl:variable name="currentImageId" select="/bx/plugin/images/@currentImageId"/>
    <xsl:variable name="openTabType" select="$opentabs/opentabs/tab[@type = 'subtabs_gallery']"/>

    <xsl:template name="head">
        <script type="text/javascript" language="JavaScript">
        var galleryRoot = '<xsl:value-of select="concat($webroot,'dynimages/',$imgLargeWidth,'/',$virtualRoot)"/>';
        var currentImageId = '<xsl:value-of select="/bx/plugin/images/@currentImageId"/>';
        var switchToTab ='<xsl:value-of select="/bx/plugin/images/@switchToTab"/>';

        <![CDATA[
            function onLoad() {
            
                    
                if(currentImageId == '') {
                    switchTab('addimage', false, true);
                } else if(switchToTab != '') {
                    switchTab(switchToTab, false, true);
                }
            }
            
            function confirmDelete(id) {
                if(confirm('Do you really want to delete the image "{0}"?', [id]) == true) 
                    window.location.href = '.?del=' + id;
            }
            
            function confirmGalleryDelete(id) {
                if(confirm('Do you really want to delete the gallery "{0}"?', [id]) == true) 
                    window.location.href = '.?gdel=' + id;
            }
            
        ]]></script>
		<script type="text/javascript" src="{$webroot}admin/webinc/js/showhidelayers.js"/>
    </xsl:template>
    
    <xsl:template name="editorContent">
        <xsl:call-template name="displayTabs">
            <xsl:with-param name="selected" select="'gallery'"/>
        </xsl:call-template>

        <div class="navitabs" name="subtabs_gallery">
            <ul>
                <xsl:call-template name="doSubTab">
                    <xsl:with-param name="name">properties</xsl:with-param>
                    <xsl:with-param name="title">Image</xsl:with-param>
                    <xsl:with-param name="default">true</xsl:with-param>
                </xsl:call-template>
                <xsl:call-template name="doSubTab">
                    <xsl:with-param name="name">addimage</xsl:with-param>
                    <xsl:with-param name="title">Add Image</xsl:with-param>
                </xsl:call-template>
                <xsl:call-template name="doSubTab">
                    <xsl:with-param name="name">creategallery</xsl:with-param>
                    <xsl:with-param name="title">Subgallery</xsl:with-param>
                </xsl:call-template>
            </ul>
            <br clear="all"/>
        </div>
        
        <div id="subeditor">
            <div id="subeditorright">
                <h3>/<xsl:value-of select="$galleryId"/></h3>
                <ul class="gallerylist">
                    <xsl:if test="$galleryId !=''">
                        <li class="first"><a href=".."><img border="0" src="{$webroot}admin/webinc/img/up.gif"/></a></li>
                    </xsl:if>
                    <xsl:for-each select="/bx/plugin/images/collection">
                        <xsl:variable name="collectionId" select="substring-after(@id, concat($baseCollection, $galleryId))"/>
                        <li>
                            <a href="#" onclick="confirmGalleryDelete('{@id}');"><img border="0" alt="delete" src="{$webroot}admin/webinc/img/icons/delete.gif"/></a><a href="{$collectionId}"><img border="0" src="{$webroot}admin/webinc/img/icons/fileicon_folder.gif"/><xsl:value-of select="$collectionId"/></a><br/>
                        </li>
                    </xsl:for-each>
                </ul>
                <ul class="imagelist">
                    <xsl:for-each select="/bx/plugin/images/image">
                        <li>
                            &#160;
                            <a href=".?id={@id}"><img border="0" src="{$webroot}dynimages/{$imgSmallWidth}/{@id}" alt="{@description}"/></a><br/>
                            <a href="#" onclick="confirmDelete('{@id}');"><img border="0" alt="delete" src="{$webroot}admin/webinc/img/icons/delete.gif"/><i18n:text>Delete this image</i18n:text></a>
                        </li>
                    </xsl:for-each>
                </ul>
            </div>
            <div id="subeditorleft">
                <div id="tab_properties" class="tabcontentHidden"><xsl:if test="$openTabType/text() = 'properties' or not($openTabType)"><xsl:attribute name="class">tabcontent</xsl:attribute></xsl:if>
                    <h3><i18n:text>Edit Image Properties</i18n:text></h3>
                    <xsl:variable name="imageNode" select="/bx/plugin/images/image[@id = $currentImageId]"/>
                    <xsl:if test="$imageNode">
                        <a href="{$webroot}{$currentImageId}" target="_blank"><img width="{$imgLargeWidth}" id="galleryLargeImage" border="0" src="{concat($webroot,'dynimages/',$imgLargeWidth,'/',$imageNode/@id)}"/></a>
                        <form name="imgDescription" method="post" action=".">
                            <table>
                                <xsl:for-each select="/bx/plugin/images/outputLanguages/language">
                                    <xsl:variable name="langCode">
                                        <xsl:choose>
                                            <xsl:when test="count(../language) &gt; 1"><xsl:value-of select="concat('(',.,')')"/></xsl:when>
                                            <xsl:otherwise/>
                                        </xsl:choose>
                                    </xsl:variable>
                                    <tr><td><i18n:text>Title</i18n:text><xsl:value-of select="$langCode"/></td><td><input id="title-{.}" type="text" name="{$formName}[title][{.}]" value="{$imageNode/title/*[local-name() = current()/@language]/text()}"/></td></tr>
                                    <tr><td><i18n:text>Description</i18n:text><xsl:value-of select="$langCode"/></td><td><input id="description-{.}" type="text" name="{$formName}[description][{.}]" value="{$imageNode/description/*[local-name() = current()/@language]/text()}"/></td></tr>
                                    <tr><td colspan="2">&#160;</td></tr>
                                </xsl:for-each>
								
								<xsl:variable name="preview_name" select="/bx/plugin/images/image[@preview = 'true']"/>
								<tr>
									<td>Preview</td>
									<td><input type="checkbox" style="width:15px;" name="{$formName}[preview]">
									<xsl:if test="$preview_name = $imageNode">
										<xsl:attribute name="checked">checked</xsl:attribute>
									</xsl:if>
									</input>
									</td>
								</tr>
								
								<tr><td width="120"></td><td><input name="{$formName}[submit]" type="submit" class="button" value="Submit" i18n:attr="value"/></td></tr>
                                <tr><td colspan="2">&#160;</td></tr>
                            </table>
                            <input id="id" type="hidden" name="{$formName}[id]" value="{$imageNode/@id}"/><br/>
                        </form>
                    </xsl:if>
                </div>

                <div id="tab_addimage" class="tabcontentHidden"><xsl:if test="$openTabType/text() = 'addimage'"><xsl:attribute name="class">tabcontent</xsl:attribute></xsl:if>
                    <h3><i18n:text>Add a New Image</i18n:text></h3>
                    <form name="imgUpload" method="post" action="." enctype="multipart/form-data" onsubmit="MM_showHideLayers('wait_layer','','show');">
                        <table>
                            <tr><td><i18n:text>Add a New Image</i18n:text></td><td><input name="bx[plugins][admin_addresource][file]" type="file" value=""/></td></tr>
                        </table>
                        <p><input name="{$formName}[addImage]" type="submit" class="button" value="Add" onclick="this.disabled;this.value='wait...';" i18n:attr="value"/></p>
                    </form>
					<div id="wait_layer" style="background-color: #ffffff; text-align:center; border:#000000 solid 1px; position:absolute; width:300px; height:115px; z-index:1; left: 200px; top: 200px; visibility: hidden">
						<h3>Upload in progress</h3>
						<p><img src="{$webroot}themes/standard/admin/images/wait_bar.gif" /><br />
						Image is uploading, please wait. This window will be closed after upload.<br />
						</p>
					</div>
                </div>
                
                <div id="tab_creategallery" class="tabcontentHidden"><xsl:if test="$openTabType/text() = 'creategallery'"><xsl:attribute name="class">tabcontent</xsl:attribute></xsl:if>
                    <h3><i18n:text>Create a New Subgallery</i18n:text></h3>
                    <form name="createGallery" method="post" action=".">
                        <table>
                            <tr><td><i18n:text>Create a New Subgallery</i18n:text></td><td><input name="{$formName}[gallery]" type="text" value=""/></td></tr>
                        </table>
                        <p><input name="{$formName}[addGallery]" type="submit" class="button" value="Create" i18n:attr="value"/></p>
                    </form>
                </div>
                
            </div>
        </div>
    </xsl:template>

</xsl:stylesheet>

