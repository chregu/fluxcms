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

    <xsl:variable name="id" select="/bx/plugin/data/@id"/>
    
    <xsl:template name="head">
        <script type="text/javascript" language="JavaScript">
        var id = '<xsl:value-of select="$id"/>';
        var rootCategory = '<xsl:value-of select="//blogcategories/blogcategories[parentid=0]/id"/>';
        <![CDATA[
            function onLoad() {
                    
            }
        ]]></script>
    </xsl:template>
    
    <xsl:template name="editorContent">
        <xsl:call-template name="displayTabs">
            <xsl:with-param name="selected" select="'overview'"/>
        </xsl:call-template>

        <div class="navitabs" name="subtabs_blogroll">
            <ul>
                <li><a href="{concat($collectionUri, $collectionUriOfId)}?st=posts"><i18n:text>Posts</i18n:text></a></li>
                <li><a href="{concat($collectionUri, $collectionUriOfId)}?st=comments" class="selected"><i18n:text>Comments</i18n:text></a></li>
            </ul>
            <br clear="all"/>
        </div>

        <div id="subeditor">
            <xsl:variable name="comment" select="/bx/plugin/data/blogcomments/blogcomments"/>
            <h3><i18n:text>Edit Blog Comment</i18n:text></h3>
            <div id="subeditorleft">
                <form name="categories" method="post" action=".">
                    <table cellspacing="2" cellpadding="0">

                        <tr>
                            <td width="120"><i18n:text>User</i18n:text></td>
                            <td><input id="name" type="text" name="{$formName}[comment_author]" value="{$comment/comment_author}"/></td>
                        </tr>
                        
                          <tr>
                            <td width="120"><i18n:text>URL</i18n:text></td>
                            <td><input id="name" type="text" name="{$formName}[comment_author_url]" value="{$comment/comment_author_url}"/></td>
                        </tr>
                        <tr>
                            <td width="120"><i18n:text>Email</i18n:text></td>
                            <td><input id="name" type="text" name="{$formName}[comment_author_email]" value="{$comment/comment_author_email}"/></td>
                        </tr>
                        <!--<tr>
                            <td width="120"><i18n:text>Email</i18n:text></td>
                            <td><input id="name" type="text" name="{$formName}[comment_author_email]" value="{$comment/comment_author_email}"/></td>
                        </tr>-->
                        <tr>
                            <td width="120"><i18n:text>Status</i18n:text></td>
                            <td>
                            <select size="1" name="{$formName}[comment_status]">
                                <option value="0"><xsl:if test="$comment/comment_status = 0"><xsl:attribute name="selected"/></xsl:if>none</option>
                                <option value="1"><xsl:if test="$comment/comment_status = 1"><xsl:attribute name="selected"/></xsl:if>Approved</option>
                                <option value="2"><xsl:if test="$comment/comment_status = 2"><xsl:attribute name="selected"/></xsl:if>Moderated</option>
                                <option value="3"><xsl:if test="$comment/comment_status = 3"><xsl:attribute name="selected"/></xsl:if>Rejected</option>
                            </select>
                            </td>
                        </tr>
                        <tr>
                            <td width="120"><i18n:text>Text</i18n:text></td>
                            <td><textarea name="{$formName}[comment_content]"><xsl:value-of select="$comment/comment_content"/></textarea></td>
                        </tr>
                           <tr>
                            <td width="120"><i18n:text>IP</i18n:text></td>
                            <td><input id="name" type="text" name="{$formName}[comment_author_ip]" value="{$comment/comment_author_ip}"/></td>
                        </tr>
                        
                            <tr>
                            <td width="120"><i18n:text>Date</i18n:text></td>
                            <td><input id="name" type="date" name="{$formName}[comment_date]" value="{$comment/comment_date}"/></td>
                        </tr>
                        
                         
                    </table>
                    
                    <p><input type="submit" class="button" name="{$formName}[submit]" value="Submit" i18n:attr="value"/></p>
                    
                    <input id="id" type="hidden" name="{$formName}[id]" value="{$comment/id}"/><br/>
                </form>
                </div>
        </div>
        
    </xsl:template>

</xsl:stylesheet>




