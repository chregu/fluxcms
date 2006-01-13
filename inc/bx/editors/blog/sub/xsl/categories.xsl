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

    <xsl:variable name="id" select="/bx/plugin/data/@currentCategoryId"/>
    
    <xsl:template name="head">
        <script type="text/javascript" language="JavaScript">
        var id = '<xsl:value-of select="$id"/>';
        var rootCategory = '<xsl:value-of select="//blogcategories/blogcategories[parentid=0]/id"/>';
        <![CDATA[
            function onLoad() {
                if(typeof i18n == 'undefined')
                    i18n = parent.i18n;
                    
                if(id == '')
                    emptyForm();
            }
            
            function emptyForm() {
                document.getElementById('name').value = '';
                document.getElementById('uri').value = '';
                document.getElementById('parentid').value = rootCategory;
                document.getElementById('id').value = '';
            }
            
            function confirmDelete(id, title) {
                if(confirm(i18n.translate('Do you really want to delete the category "{0}"?', [title])) == true) 
                    window.location.href = '.?del=' + id;
            
            }
            
        ]]></script>
    </xsl:template>
    
    <xsl:template name="editorContent">
        <xsl:call-template name="displayTabs">
            <xsl:with-param name="selected" select="'categories'"/>
        </xsl:call-template>
        <div id="subeditor">
            <xsl:variable name="categoryNode" select="//blogcategories/blogcategories[@id=concat('blogcategories', $id)]"/>
            <h3><i18n:text>Blog Categories</i18n:text></h3>
            <div id="subeditorright">
                <!--<h3>Edit an Existing Category</h3>-->
                <ul>
                <xsl:for-each select="//blogcategories/blogcategories">
                    <li>
                        <xsl:if test="parentid != 0"><a href="#" onclick="confirmDelete({id},'{fullname}');"><img border="0" alt="delete" src="{$webroot}admin/webinc/img/icons/delete.gif"/></a></xsl:if>
                        <a href="?id={id}"><xsl:value-of select="fullname"/></a>
                    </li>
                </xsl:for-each>
                </ul>
            </div>
            <div id="subeditorleft">
                <form name="categories" method="post" action=".">
                    <table cellspacing="2" cellpadding="0">
                    <tr>
                        <td width="120"><i18n:text>Name</i18n:text></td>
                        <td><input id="name" type="text" name="{$formName}[name]" value="{$categoryNode/name}" onkeyup="updateUriField('name',document.getElementById('uri'));"/></td>
                    </tr><tr>
                        <td><i18n:text>Uri</i18n:text></td>
                        <td>
                            <input id="uri" type="text" name="{$formName}[uri]" value="{$categoryNode/uri}">
                                <xsl:if test="$categoryNode/parentid = 0"><xsl:attribute name="disabled" value="true"/></xsl:if>
                            </input>
                        </td>
                    </tr><tr>
                        <td><i18n:text>Parent Category</i18n:text></td>
                        <td><select id="parentid" name="{$formName}[parentid]"><xsl:if test="$categoryNode/parentid = 0"><xsl:attribute name="disabled" value="true"/></xsl:if>
                            <xsl:for-each select="//blogcategories/blogcategories">
                                <option value="{id}"><xsl:if test="id = $categoryNode/parentid"><xsl:attribute name="selected" value="selected"/></xsl:if>
                                <xsl:value-of select="fullname"/></option>
                            </xsl:for-each>
                            </select>
                        </td>
                    </tr>
					<tr>
                        <td>&#160;</td>
                        <td>
							<input type="submit" class="button" name="{$formName}[submit]" value="Submit" i18n:attr="value"/>&#160;
							<input type="button" class="button" name="new" value="New" onclick="emptyForm();" i18n:attr="value"/>
						</td>
                    </tr>
                    </table>                  
                    <input id="id" type="hidden" name="{$formName}[id]" value="{$categoryNode/id}"/><br/>
                    <input type="hidden" name="{$formName}[parentidold]" value="{$categoryNode/parentid}"/><br/>
                </form>
            </div>
        </div>
        
    </xsl:template>

</xsl:stylesheet>




