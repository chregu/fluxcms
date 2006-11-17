
<xsl:stylesheet version="1.0" xmlns:i18n="http://apache.org/cocoon/i18n/2.1" 
xmlns:sixcat="http://sixapart.com/atom/category#" 
xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://purl.org/atom/ns#" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:bxf="http://bitflux.org/functions" xmlns:rss="http://purl.org/rss/1.0/" xmlns:blog="http://bitflux.org/doctypes/blog" xmlns:php="http://php.net/xsl" exclude-result-prefixes="rdf dc xhtml rss bxf blog">
    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

    <xsl:include href="../../tabs.xsl"/>
	<xsl:import href="master.xsl"/>
    
    <xsl:param name="webroot"/>
    <xsl:param name="collectionUri" select="''"/>
    <xsl:param name="id" select="''"/>
    <xsl:variable name="opentabs" select="php:function('bx_helpers_config::getOpenTabs')"/>
    <xsl:variable name="openTabType" select="$opentabs/opentabs/tab[@type = 'blog_overview']"/>
    <xsl:variable name="switchtab" select="php:function('bx_helpers_globals::GET', 'st')"/>
    <xsl:variable name="formName" select="'bx[plugins][admin_edit]'"/>
	<xsl:variable name="path" select="/bx/plugin[@name='admin_edit']"/>

    <xsl:template match="/">
        <html>
            <head>
                <link rel="stylesheet" href="{$webroot}themes/standard/admin/css/formedit.css" type="text/css"/>
                <link rel="stylesheet" href="{$webroot}themes/standard/admin/css/blog.css" type="text/css"/>
                <script type="text/javascript" src="{$webroot}webinc/js/livesearch.js"></script>
                <script type="text/javascript" src="{$webroot}webinc/plugins/blog/common.js"></script>
                <script type="text/javascript" src="{$webroot}admin/webinc/js/overview.js"></script>
				
                <script type="text/javascript" language="JavaScript">
                var switchtab = '<xsl:value-of select="$switchtab"/>';
                <![CDATA[
                function initSubTabs() {
                    if(switchtab != '')
                        switchTab(switchtab, false, true);
                }
                ]]></script>
            </head>
            <body onload="initSubTabs();">
			
                    <xsl:call-template name="displayTabs">
                        <xsl:with-param name="name">generalcomments</xsl:with-param>
                        <xsl:with-param name="selected">generalcomments</xsl:with-param>
            
						<xsl:with-param name="title">General Comments</xsl:with-param>
                    </xsl:call-template>
                            
                <xsl:if test="$openTabType/text() = 'posts' or not($openTabType)">
                        <xsl:attribute name="class">tabcontent</xsl:attribute>
                    </xsl:if>
                    
				<div id="tab_comments" class="tabcontentHidden">
                    <xsl:if test="$openTabType/text() = 'comments'">
                        <xsl:attribute name="class">tabcontent</xsl:attribute>
                    </xsl:if>
					<xsl:apply-templates select="bx/plugin[@name='admin_edit']"/>	
                </div>
            </body>
        </html>
    </xsl:template>

	<xsl:template match="comments">
		<form action="" method="POST">
			<h3>
				<i18n:text>Latest Online and Approved Comments</i18n:text>
			</h3>
			<xsl:choose>
				<xsl:when test="/bx/plugin[@name='admin_edit']/comments/comment">
					<table cellpadding="2" class="bigUglyEditTable" id="approved">
					<tr><th></th><th>content</th><th>author</th><th>date</th></tr>
					<xsl:call-template name="comment"/>
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
	</xsl:template>
	
	
	
	<xsl:template match="span[@class='comment_author_email']">
    <xsl:if test="string-length(.) &gt; 5">
            <img class="blog_gravatar" src="{php:functionString('bx_plugins_blog_gravatar::getLink',text(),'40','aaaaaa')}"/>
        </xsl:if>
    </xsl:template>
	
	<xsl:template name="comment">
		<xsl:for-each select="$path/comments/comment">
			<tr>
			<td>
				<input type="checkbox" class="checkbox" name="bx[plugins][admin_edit][deletecomments][{@id}]"/>
			</td>
			<td>
				<a href="{$webroot}{$collectionUri}{$id}?id={@id}">
					<xsl:value-of select="substring(php:functionString('strip_tags',content),1,50)" disable-output-escaping="yes"/>
				</a>
			</td>
			<td><xsl:value-of select="author"/></td>
			<td><xsl:value-of select="date"/></td>
			</tr>
		</xsl:for-each>
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
    
	
	
	<xsl:template match="comment/comment">

        <div id="subeditor">
            <xsl:variable name="comment" select="/bx/plugin/comment/comments[@edit='true']"/>
            <h3><i18n:text>Edit Blog Comment</i18n:text></h3>
            <div id="subeditorleft">
                <form name="categories" method="post" action="?id={comment_id}">
                    <table cellspacing="2" cellpadding="0">

                        <tr>
                            <td width="120"><i18n:text>User</i18n:text></td>
                            <td><input id="name" type="text" name="{$formName}[comment_author]" value="{comment_author}"/></td>
                        </tr>
                        
                          <tr>
                            <td width="120"><i18n:text>URL</i18n:text></td>
                            <td><input id="name" type="text" name="{$formName}[comment_author_url]" value="{comment_author_url}"/></td>
                        </tr>
                        <tr>
                            <td width="120"><i18n:text>Email</i18n:text></td>
                            <td><input id="name" type="text" name="{$formName}[comment_author_email]" value="{comment_author_email}"/></td>
                        </tr>
                        <!--<tr>
                            <td width="120"><i18n:text>Email</i18n:text></td>
                            <td><input id="name" type="text" name="{$formName}[comment_author_email]" value="{$comment/comment_author_email}"/></td>
                        </tr>-->
                        <tr>
                            <td width="120"><i18n:text>Status</i18n:text></td>
                            <td>
                            <select size="1" name="{$formName}[comment_status]">
                                <option value="0"><xsl:if test="comment_status = 0"><xsl:attribute name="selected"/></xsl:if>none</option>
                                <option value="1"><xsl:if test="comment_status = 1"><xsl:attribute name="selected"/></xsl:if>Approved</option>
                                <option value="2"><xsl:if test="comment_status = 2"><xsl:attribute name="selected"/></xsl:if>Moderated</option>
                                <option value="3"><xsl:if test="comment_status = 3"><xsl:attribute name="selected"/></xsl:if>Rejected</option>
                            </select>
                            </td>
                        </tr>
                        <tr>
                            <td width="120"><i18n:text>Text</i18n:text></td>
                            <td><textarea name="{$formName}[comment_content]"><xsl:value-of select="comment_content"/></textarea></td>
                        </tr>
                           <tr>
                            <td width="120"><i18n:text>IP</i18n:text></td>
                            <td><input id="name" type="text" name="{$formName}[comment_author_ip]" value="{comment_author_ip}"/></td>
                        </tr>
                        
                            <tr>
                            <td width="120"><i18n:text>Date</i18n:text></td>
                            <td><input id="name" type="date" name="{$formName}[comment_date]" value="{comment_date}"/></td>
                        </tr>
                        
                         
                    </table>
                    <input id="name" type="hidden" name="{$formName}[comment_id]" value="{comment_id}"/>
                    <p><input type="submit" class="button" name="{$formName}[submit]" value="Update" i18n:attr="value"/></p>
                    
                    <input id="id" type="hidden" name="{$formName}[id]" value="{id}"/><br/>
                </form>
                </div>
        </div>
        
    </xsl:template>
	
</xsl:stylesheet>
