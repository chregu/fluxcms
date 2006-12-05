
<xsl:stylesheet version="1.0" xmlns:i18n="http://apache.org/cocoon/i18n/2.1" 
xmlns:sixcat="http://sixapart.com/atom/category#" 
xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://purl.org/atom/ns#" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:bxf="http://bitflux.org/functions" xmlns:rss="http://purl.org/rss/1.0/" xmlns:blog="http://bitflux.org/doctypes/blog" xmlns:php="http://php.net/xsl" exclude-result-prefixes="rdf dc xhtml rss bxf blog">
    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

    <xsl:include href="comment.xsl"/>
    <xsl:include href="livesearch.xsl"/>
    <xsl:include href="tabs.xsl"/>

    <xsl:param name="webroot"/>
    <xsl:param name="collectionUri" select="''"/>
    <xsl:param name="id" select="''"/>
    <xsl:variable name="opentabs" select="php:function('bx_helpers_config::getOpenTabs')"/>
    <xsl:variable name="openTabType" select="$opentabs/opentabs/tab[@type = 'blog_overview']"/>
    <xsl:variable name="switchtab" select="php:function('bx_helpers_globals::GET', 'st')"/>
    

    <xsl:template match="/">
        <html>
            <head>
                <link rel="stylesheet" href="{$webroot}themes/standard/admin/css/formedit.css" type="text/css"/>
                <link rel="stylesheet" href="{$webroot}themes/standard/admin/css/blog.css" type="text/css"/>
                <script type="text/javascript" src="{$webroot}webinc/js/livesearch.js"></script>
                <script type="text/javascript" src="{$webroot}webinc/plugins/blog/common.js"></script>
                <script type="text/javascript" src="{$webroot}admin/webinc/js/overview.js"></script>
                <xsl:call-template name="livesearchInit"/>
                <script type="text/javascript" language="JavaScript">
                var switchtab = '<xsl:value-of select="$switchtab"/>';
                <![CDATA[
                function initSubTabs() {
                    if(switchtab != '')
                        switchTab(switchtab, false, true);
                }
                ]]></script>
            </head>
            <body onload="liveSearchInit(); initSubTabs();">
                    <xsl:call-template name="displayTabs">
                        <xsl:with-param name="selected" select="'overview'"/>
                    </xsl:call-template>
                <div class="navitabs" name="blog_overview">
                    <ul>
                        <xsl:call-template name="doSubTab">
                            <xsl:with-param name="name">posts</xsl:with-param>
                            <xsl:with-param name="title">Posts</xsl:with-param>
                            <xsl:with-param name="default">true</xsl:with-param>
                        </xsl:call-template>

                        <xsl:call-template name="doSubTab">
                            <xsl:with-param name="name">comments</xsl:with-param>
                            <xsl:with-param name="title">Comments</xsl:with-param>
                        </xsl:call-template>
                    </ul>
                    <br clear="all"/>
                </div>
                    
                <div id="tab_posts" class="tabcontentHidden">
                            
                <xsl:if test="$openTabType/text() = 'posts' or not($openTabType)">
                        <xsl:attribute name="class">tabcontent</xsl:attribute>
                    </xsl:if>
                <xsl:call-template name="livesearch"/>
                    <h3>
                        <i18n:text>Latest Posts</i18n:text>
                    </h3>
             
                    
<form method="post" action="">
                   
                    <table id="posts" cellpadding="5" border="0" class="bigUglyEditTable">
                        <tr>
                        <td colspan="4">    
                               <xsl:call-template name="pager"/>
                    
                    
                    </td>
                    <td colspan="2" style="text-align: right;">
                        <a class="buttonStyle" style="color: white" href="./newpost.xml">
                        <i18n:text>Make new post</i18n:text>
                    </a>
                    </td>
                    </tr>
                    <tr>
                        <th>
                            </th>
                            <th>
                                <i18n:text>title</i18n:text>
                            </th>
                            <th>
                                <i18n:text>author</i18n:text>
                            </th>
                            <th>
                                <i18n:text>date</i18n:text>
                            </th>
                            <th>
                                <i18n:text>status</i18n:text>
                            </th>
                            <th>
                                <i18n:text>comments</i18n:text>
                            </th>

                        </tr>
                        <xsl:apply-templates select="/atom:feed/atom:entry"/>
                        <tr>
                            <td colspan="5">
                                    <input type="checkbox" onclick="toggleCheckboxes(this.checked,'posts')" class="checkbox"/>
                                    <i18n:text>check1</i18n:text>
                         
                                
                            </td>
                        </tr>
                    </table>
                     
                     
                    <input type="submit" name="" i18n:attr="value" value="Delete Selected Posts"/>
                </form>    
                </div>
                
                <div id="tab_comments" class="tabcontentHidden">
                    <xsl:if test="$openTabType/text() = 'comments'">
                        <xsl:attribute name="class">tabcontent</xsl:attribute>
                    </xsl:if>
                    <form action="" method="POST">
                        <h3>
                            <i18n:text>Latest Online and Approved Comments</i18n:text>
                        </h3>
                        <xsl:choose>
                            <xsl:when test="/atom:feed/atom:comments[@status = 1]/atom:comment">
                                <table cellpadding="2" class="bigUglyEditTable" id="approved">
                    <tr><th></th><th>content</th><th>author</th><th>date</th></tr>
                                                <xsl:apply-templates select="/atom:feed/atom:comments[@status = 1]/atom:comment"/>
                                </table>

                                <input type="checkbox" onclick="toggleCheckboxes(this.checked,'approved')" class="checkbox"/>
                                <i18n:text>check1</i18n:text>
                            </xsl:when>
                            <xsl:otherwise>
                        None
                    </xsl:otherwise>
                        </xsl:choose>

                        <h3>
                            <i18n:text>Latest Moderated Comments (Auto-deleted after 14 days)</i18n:text>
                        </h3>
                        <xsl:choose>
                            <xsl:when test="/atom:feed/atom:comments[@status = 2]/atom:comment">
                                <table cellpadding="2" id="moderated" class="bigUglyEditTable">
                            <tr><th></th><th>content</th><th>author</th><th>date</th></tr>
                           <xsl:apply-templates select="/atom:feed/atom:comments[@status = 2]/atom:comment"/>
                                </table>
                                <input type="checkbox" onclick="toggleCheckboxes(this.checked,'moderated')" class="checkbox"/>
                                <i18n:text>check1</i18n:text>
                            </xsl:when>
                            <xsl:otherwise>
                            None <br/>
                            </xsl:otherwise>
                        </xsl:choose>
                        <h3>
                            <i18n:text>Latest Rejected Comments (Auto-deleted after 3 days)</i18n:text>
                        </h3>
                        <xsl:choose>
                            <xsl:when test="/atom:feed/atom:comments[@status = 3]/atom:comment">
                                <table cellpadding="2" id="rejected" class="bigUglyEditTable">
                                    <tr><th></th><th>content</th><th>author</th><th>date</th></tr>
                                    <xsl:apply-templates select="/atom:feed/atom:comments[@status = 3]/atom:comment"/>
                                </table>
                                <input type="checkbox" onclick="toggleCheckboxes(this.checked,'rejected')" class="checkbox"/>
                                <i18n:text>check1</i18n:text>
                            </xsl:when>
                            <xsl:otherwise>
                            None <br/>
                            </xsl:otherwise>
                        </xsl:choose>
                        <br/>
                        <br/>
                        <input type="submit" name="" i18n:attr="value" value="Delete Selected Comments"/>


                    </form>
                </div>
<!--
                <hr/>
                <p>
                <a href="javascript:%20var%20baseUrl%20=%20'{$webroot}admin/edit{$id}newpost.xml?';%20var%20url=baseUrl;var%20title=document.title;%20url=url%20+%20'link_title='%20+%20encodeURIComponent(title);%20var%20currentUrl=document.location.href;%20url=url%20+%20'&amp;link_href='%20+%20encodeURIComponent(currentUrl);%20var%20selectedText;%20selectedText=getSelection();%20if%20(selectedText%20!=%20'')%20url=url%20+%20'&amp;text='%20+%20encodeURIComponent(selectedText);var win = window.open(null, '', 'width=700,height=500,scrollbars,resizable,location,toolbar');win.location.href=url;win.focus();">BxCMS bookmarklet</a>
                <i18n:text> (Drag'n'drop to your bookmarks for immediate posting from your browser)</i18n:text>
                
                </p>
    -->
            </body>
        </html>
    </xsl:template>

    <xsl:template match="atom:entry">
        <xsl:if test="atom:uri != ''">
            <tr>
            <xsl:choose>
            <xsl:when test="position() mod 2= 0">
            <xsl:attribute name="class">uneven</xsl:attribute>
            </xsl:when>
            </xsl:choose>
            
                <td>
                    <input class="checkbox" type="checkbox"  name="bx[plugins][admin_edit][deleteposts][{atom:id}]" value="{atom:id}" />
                </td>
                <td>
                    <a href="./{atom:uri}.html">
                        <xsl:value-of select="atom:title"/> [<xsl:value-of select="atom:lang"/>]
                    </a>
                    <br/>
                </td>
                <td>
                    <xsl:value-of select="atom:author/atom:name"/>
                </td>
                <td>
                    <xsl:value-of select="atom:created/@localtime"/>
                </td>
                <td>
                    <xsl:choose>
                        <xsl:when test="atom:status=1"> 
                        Public
                    </xsl:when>
                        <xsl:otherwise>
                            <xsl:choose>
                                <xsl:when test="atom:status=2">
                                Private
                            </xsl:when>
                                <xsl:otherwise>
                                Draft
                            </xsl:otherwise>
                            </xsl:choose>
                        </xsl:otherwise>
                    </xsl:choose>
                </td>

                <td>
                    <xsl:value-of select="atom:commentcount"/>
                </td>
            </tr>
        </xsl:if>
    </xsl:template>
    
    <xsl:template name="pager" >
    <xsl:for-each select="/atom:feed/xhtml:div[@class='blog_pager']">
        <!--div id="admin_blog_pager_prevnext"-->
            <xsl:for-each select="xhtml:span[@class='blog_pager_prevnext']/xhtml:a">
            &#160;<a href="{@href}">
                <i18n:text>
                    <xsl:value-of select="i18n:text"/>
                </i18n:text>    
            </a>&#160;
            </xsl:for-each>
        <!--/div-->
        <xsl:for-each select="xhtml:span[@class='blog_pager_counter']">
            <xsl:copy-of select="."/>
        </xsl:for-each>
        </xsl:for-each>
    </xsl:template>
    
</xsl:stylesheet>
