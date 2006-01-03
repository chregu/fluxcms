<xsl:stylesheet version="1.0"
    xmlns="http://www.w3.org/1999/xhtml"
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

    <xsl:variable name="category" select="/bx/plugin/data/@currentCategoryId"/>
    <xsl:variable name="link" select="/bx/plugin/data/@currentLinkId"/>
    <xsl:variable name="openTabType" select="$opentabs/opentabs/tab[@type = 'subtabs_blogroll']"/>
    
    <xsl:template name="head">
        <script type="text/javascript" language="JavaScript">
        var link = '<xsl:value-of select="$link"/>';
        var category = '<xsl:value-of select="$category"/>';
        var defaultCategory = '<xsl:value-of select="//bloglinkscategories/bloglinkscategories[1]/id"/>';
        <![CDATA[
            function onLoad() {
                if(typeof i18n == 'undefined')
                    i18n = parent.i18n;
                    
                if(link == '')
                    emptyLinkForm();
                
                if(category == '')
                    emptyCategoryForm();

                if(link != 0) {
                    switchTab('bloglinks', false, true);
                
                } else if(category != 0) {
                    switchTab('categories', false, true);
                }
            }
            
            function emptyCategoryForm() {
                document.getElementById('cname').value = '';
                document.getElementById('crang').value = '';
                document.getElementById('cid').value = '';
            }

            function emptyLinkForm() {
                document.getElementById('lcat').value = defaultCategory;
                document.getElementById('ltext').value = '';
                document.getElementById('llink').value = 'http://';
                document.getElementById('lrang').value = '';
                document.getElementById('lid').value = '';
            }
            
            function confirmLinkDelete(id, title) {
                if(confirm(i18n.translate('Do you really want to delete the link "{0}"?', [title])) == true) 
                    window.location.href = '.?linkdel=' + id;
            }

            function confirmCategoryDelete(id, title) {
                if(confirm(i18n.translate('Do you really want to delete the category "{0}"?', [title])) == true) 
                    window.location.href = '.?catdel=' + id;
            }

            
        ]]></script>
    </xsl:template>
    
    <xsl:template name="editorContent">
        <xsl:call-template name="displayTabs">
            <xsl:with-param name="selected" select="'blogroll'"/>
        </xsl:call-template>
        
        <div class="navitabs" name="subtabs_blogroll">
            <ul>
                <xsl:call-template name="doSubTab">
                    <xsl:with-param name="name">categories</xsl:with-param>
                    <xsl:with-param name="title">Categories</xsl:with-param>
                    <xsl:with-param name="default">true</xsl:with-param>
                </xsl:call-template>
                <xsl:call-template name="doSubTab">
                    <xsl:with-param name="name">bloglinks</xsl:with-param>
                    <xsl:with-param name="title">Links</xsl:with-param>
                </xsl:call-template>
            </ul>
            <br clear="all"/>
        </div>
        <div id="subeditor">
            <xsl:variable name="categoryNode" select="//bloglinkscategories/bloglinkscategories[@id=concat('bloglinkscategories', $category)]"/>
            <div id="subeditorright">
                <ul>
                <xsl:for-each select="//bloglinkscategories/bloglinkscategories">
                    <li><h4><a href="#" onclick="confirmCategoryDelete({id}, '{name}');">x</a>&#160;<a href="?category={id}"><xsl:value-of select="name"/></a></h4></li>
                    <ul class="level2">
                        <xsl:for-each select="bloglinks[id != '']">
                            <li><a href="#" onclick="confirmLinkDelete({id}, '{text}');">x</a>&#160;<a href="?link={id}"><xsl:value-of select="text"/></a></li>
                        </xsl:for-each>
                    </ul>
                </xsl:for-each>
                </ul>
            </div>
            <div id="subeditorleft">
                <div id="tab_categories" class="tabcontentHidden"><xsl:if test="$openTabType/text() = 'categories' or not($openTabType)"><xsl:attribute name="class">tabcontent</xsl:attribute></xsl:if>
                    <h3><i18n:text>Link Category</i18n:text></h3>
                    <form name="category" method="post" action=".">
                        <table cellspacing="2" cellpadding="0">
                        <tr>
                            <td width="120"><i18n:text>Name</i18n:text></td>
                            <td><input id="cname" type="text" name="{$formName}[category][name]" value="{$categoryNode/name}"/></td>
                        </tr>
                        <tr>
                            <td width="120"><i18n:text>Rang</i18n:text></td>
                            <td><input id="crang" type="text" name="{$formName}[category][rang]" value="{$categoryNode/rang}"/></td>
                        </tr>
						<tr>
                            <td width="120">&#160;</td>
                            <td>
								<input type="submit" class="button" name="{$formName}[category][submit]" value="Submit" i18n:attr="value"/>
								&#160;<input type="button" class="button" name="new" value="New" onclick="emptyCategoryForm();" i18n:attr="value"/>
							</td>
                        </tr>
                        </table>
                        <input id="cid" type="hidden" name="{$formName}[category][id]" value="{$categoryNode/id}"/><br/>
                    </form>
                </div>
                
                <xsl:variable name="linkNode" select="//bloglinkscategories/bloglinkscategories/bloglinks[@id=concat('bloglinks', $link)]"/>
                <div id="tab_bloglinks" class="tabcontentHidden"><xsl:if test="$openTabType/text() = 'bloglinks'"><xsl:attribute name="class">tabcontent</xsl:attribute></xsl:if>
                    <h3><i18n:text>Blog Link</i18n:text></h3>
                    <form name="link" method="post" action=".">
                        <table cellspacing="2" cellpadding="0">
                        <tr>
                            <td><i18n:text>Link Category</i18n:text></td>
                            <td><select id="lcat" name="{$formName}[link][bloglinkscategories]">
                                <xsl:for-each select="//bloglinkscategories/bloglinkscategories">
                                    <option value="{id}"><xsl:if test="id = $linkNode/bloglinkscategories"><xsl:attribute name="selected" value="selected"/></xsl:if>
                                    <xsl:value-of select="name"/></option>
                                </xsl:for-each>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td width="120"><i18n:text>Text</i18n:text></td>
                            <td><input id="ltext" type="text" name="{$formName}[link][text]" value="{$linkNode/text}"/></td>
                        </tr>
                        <tr>
                            <td width="120"><i18n:text>Link</i18n:text></td>
                            <td><input id="llink" type="text" name="{$formName}[link][link]" value="{$linkNode/link}"/></td>
                        </tr>
                        <tr>
                            <td width="120"><i18n:text>Rang</i18n:text></td>
                            <td><input id="lrang" type="text" name="{$formName}[link][rang]" value="{$linkNode/rang}"/></td>
                        </tr>
						 <tr>
                            <td width="120">&#160;</td>
                            <td><input type="submit" class="button" name="{$formName}[link][submit]" value="Submit" i18n:attr="value"/>&#160;<input type="button" class="button" name="new" value="New" onclick="emptyLinkForm();" i18n:attr="value"/></td>
                        </tr>
                        </table>
                        <input id="lid" type="hidden" name="{$formName}[link][id]" value="{$linkNode/id}"/><br/>
                    </form>
                </div>
            </div>
        </div>
    </xsl:template>

</xsl:stylesheet>




