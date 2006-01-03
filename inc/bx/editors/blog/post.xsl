
<xsl:stylesheet version="1.0" xmlns:sixcat="http://sixapart.com/atom/category#"
 xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://purl.org/atom/ns#" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:bxf="http://bitflux.org/functions" xmlns:rss="http://purl.org/rss/1.0/" xmlns:blog="http://bitflux.org/doctypes/blog" 
 xmlns:php="http://php.net/xsl"
 xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
 exclude-result-prefixes="rdf dc php xhtml rss bxf blog">
<xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
    
    <xsl:include href="comment.xsl"/>
    <xsl:include href="tabs.xsl"/>

    <xsl:param name="webroot"/>
    <xsl:param name="collectionUri" select="''"/>
    <xsl:param name="collectionUriOfId" select="''"/>
    <xsl:param name="dataUri" select="''"/>
    
    <xsl:variable name="showAdvancedView" select="php:functionString('bx_helpers_globals::COOKIE','blogAdvancedView')"/> 
    <xsl:variable name="doKupu" select="php:functionString('popoon_classes_browser::isMozillaAndHasMidas')"/>
    <xsl:variable name="enableNewBlogEditor" select="php:functionString('bx_helpers_config::getOption','enableNewBlogEditor')"/>
    
    <xsl:template match="/">
        <html>

            <head>
            
            <xsl:if test="$doKupu">
                <xsl:call-template name="kupuhead"/>
                </xsl:if>
                <link rel="stylesheet" href="{$webroot}/themes/standard/admin/css/blog.css" type="text/css"/>
                <link rel="stylesheet" href="{$webroot}/themes/standard/admin/css/formedit.css" type="text/css"/>
                <script type="text/javascript" src="{$webroot}webinc/js/livesearch.js">
                </script>
                <script type="text/javascript" src="{$webroot}webinc/plugins/blog/common.js">
                </script>
               <!-- <xsl:call-template name="livesearchInit"/>-->
                <xsl:call-template name="themeInit"/>
                <script type="text/javascript" language="JavaScript" src="{$webroot}webinc/js/formedit.js"><xsl:text> </xsl:text> </script>
                <script type="text/javascript" language="JavaScript" src="{$webroot}webinc/js/CalendarPopup.js"><xsl:text> </xsl:text></script>
                <script language="JavaScript" type="text/javascript">
                    document.write(getCalendarStyles());
      
                    var cal = new CalendarPopup('caldiv');
                    cal.showYearNavigation();
                </script>
                
                  <script type="text/javascript" language="JavaScript">
            liveSearchRoot = "<xsl:value-of select="$webroot"/>";
            liveSearchRootSubDir = "<xsl:value-of select="concat($collectionUri,$id)"/>";
            liveSearchParams = "&amp;blogadmin=1";
        </script>
            </head>
            <body>
            
            <xsl:choose>
            <xsl:when test="$doKupu">
                <xsl:attribute name="onload"> initBxContent(); kupu =  startKupu(this);</xsl:attribute>
            </xsl:when>
            <xsl:otherwise>
            <!--<xsl:attribute name="onload">liveSearchInit(); </xsl:attribute>-->
            </xsl:otherwise>
            </xsl:choose>
          
    
                <xsl:if test="$enableNewBlogEditor = 'true'">
                    <xsl:call-template name="displayTabs">
                        <xsl:with-param name="selected" select="'post'"/>
                    </xsl:call-template>
                </xsl:if>
                
            <xsl:if test="not($doKupu)">
                <font color="red"><i18n:text>This page would have a WYISYWIG editor on Mozilla/Firefox ;)</i18n:text></font>
            </xsl:if>  
                <!--<xsl:call-template name="livesearch"/>-->
                <xsl:if test="$doKupu">
                    <xsl:call-template name="kupubody"/>
                 </xsl:if>
                <xsl:apply-templates select="atom:entry"/>
            </body>
        </html>


    </xsl:template>
    
    <xsl:template name="themeInit">
    <script type="text/javascript" language="JavaScript">
    var theme = '<xsl:value-of select="php:functionString('bx_helpers_config::getProperty','theme')"/>';
    var themeCss = '<xsl:value-of select="php:functionString('bx_helpers_config::getProperty','themeCss')"/>';
    </script>
    
    </xsl:template>

    <xsl:template match="atom:entry">
        <form method="post" name="entry" action="{php:functionString('bx_helpers_uri::getRequestUri')}">
         <xsl:call-template name="buttons">
         <xsl:with-param name="accesskeys" select="'true'"/>
         </xsl:call-template>
            <input type="hidden" id="id" name="bx[plugins][admin_edit][id]" value="{atom:id}"/>
            <input type="hidden" id="delete" name="bx[plugins][admin_edit][delete]" value="0"/>
            <div class="formTable">
                    <xsl:variable name="cats" select="/atom:entry/atom:categories"/>



            
                <table class="bigUglyBorderedEditTable">
                    <tr>
                        <td><i18n:text>Title</i18n:text>:</td>
                        <td width="10%">
                            <input id="title" onkeyup="updateUriField('title',document.getElementById('uri'));" name="bx[plugins][admin_edit][title]" size="60" value="{atom:title}"/>
                        </td>
                        <td></td>
                        
                        <td rowspan="11" width="18%" valign="top" nowrap="nowrap">
                                                    <div style="white-space: nowrap" class="scrollbox" id="categories">
                                                    <i18n:text>Categories:</i18n:text><br/>
                            <xsl:variable name="allcats" select="document(concat('blog://',$collectionUriOfId,'categories.xml'))"/>
                            
                                <xsl:for-each select="$allcats/*/dc:subject[@xml:id = $cats/atom:category/@xml:id]">
                                    <input class="checkbox" type="checkbox" name="bx[plugins][admin_edit][categories][{.}]">
                                        <xsl:attribute name="checked">checked</xsl:attribute>
                                        <xsl:value-of select="."/>
                                    </input>
                                    <br/>

                                </xsl:for-each>
                                <hr noshade="noshade"/>
                                <xsl:for-each select="$allcats/*/dc:subject">
                                    <xsl:if test="not($cats/atom:category[current()/@xml:id = @xml:id ])">
                                        <input class="checkbox" type="checkbox" name="bx[plugins][admin_edit][categories][{.}]">
     


                                            <xsl:value-of select="."/>
                                        </input>
                                        <br/>
                                    </xsl:if>
                                </xsl:for-each>
                            </div>
            
</td>
                    </tr>
                    
                    
                    
                    <tr>
                        <td>Uri:</td>
                        <td>
                            <input id="uri" onkeyup="this.edited=true; " name="bx[plugins][admin_edit][uri]" size="60" value="{atom:uri}"/>
                        </td>
                        <td><i18n:text>No special chars.</i18n:text></td>
                    </tr>
                     <tr>
                        <td>Tags: <a target="_blank" href="http://www.technorati.com/help/tags.html">?</a> </td>
                        <td>
                            <input id="tags"  name="bx[plugins][admin_edit][tags]" size="60" value="{atom:tags}"/>
                        </td>
                        <td><i18n:text>Space or comma separated.</i18n:text></td>
                    </tr>
                    <tr id="foo">
                        <td valign="top" colspan="2">
                            <img onclick="toggleAdvanced();" id="advanced_triangle" src="{$webroot}/admin/webinc/img/closed_klein.gif">
                             <xsl:attribute name="src">
                                   <xsl:choose>
                                       <xsl:when test="$showAdvancedView = 'true'"><xsl:value-of select="$webroot"/>/admin/webinc/img/open_klein.gif</xsl:when>
                                        <xsl:otherwise><xsl:value-of select="$webroot"/>/admin/webinc/img/closed_klein.gif</xsl:otherwise>
                                     </xsl:choose>
                             </xsl:attribute>
                             </img> <span onclick="toggleAdvanced();"><i18n:text>More options (click to expand)</i18n:text></span>
                        </td>
                    </tr>
                    <tr id="advanced1">
                   <xsl:call-template name="displayOrNot"/>
                        <td>
                             Status:
                        </td>
                        <td colspan = "2">   
                            <select id="status" name="bx[plugins][admin_edit][status]">
                                <option value="1" >
                                <xsl:if test="atom:status = 1">
                                    <xsl:attribute name="selected">selected</xsl:attribute>
                                </xsl:if>
                                <i18n:text>Public</i18n:text></option>
                                <option value="2">
                                 <xsl:if test="atom:status = 2">
                                    <xsl:attribute name="selected">selected</xsl:attribute>
                                </xsl:if>
                                <i18n:text>Private</i18n:text></option>
                                <option value="4">
                                 <xsl:if test="atom:status = 4">
                                    <xsl:attribute name="selected">selected</xsl:attribute>
                                </xsl:if>
                                <i18n:text>Draft</i18n:text></option>
                            </select>
                        </td>
                    </tr>
                    <tr id="advanced2">
                        <xsl:call-template name="displayOrNot"/>
                        <td>
                            <i18n:text>Comment Mode</i18n:text>
                        </td>
                        <td>
                            <select id="comment_mode" name="bx[plugins][admin_edit][comment_mode]">
                            <option value="99" >
                            <xsl:if test="atom:comment_mode = 99">
                                <xsl:attribute name="selected">selected</xsl:attribute>
                            </xsl:if> 
                            <i18n:text>Default  (currently:</i18n:text>
                            
                            <xsl:variable name="mode" select="php:functionString('bx_helpers_config::getOption','blogDefaultPostCommentMode')"/>
                            <xsl:choose>
                                <xsl:when test="$mode = 1">
                                    <i18n:text>Allow comments for 1 month</i18n:text>
                                </xsl:when>
                                <xsl:when test="$mode = 2">
                                    <i18n:text>Always allow comments</i18n:text>
                                </xsl:when>
                                 <xsl:when test="$mode = 3">
                                    <i18n:text>No comments allowed</i18n:text>
                                </xsl:when>
                            </xsl:choose>)
                            
                            
                            </option>
                            
                            <option value="1" >
                            <xsl:if test="atom:comment_mode = 1">
                                <xsl:attribute name="selected">selected</xsl:attribute>
                            </xsl:if>
                            <i18n:text>Allow comments for 1 month</i18n:text></option>
                            <option value="2">
                            <xsl:if test="atom:comment_mode = 2">
                                <xsl:attribute name="selected">selected</xsl:attribute>
                            </xsl:if>
                            <i18n:text>Always allow comments</i18n:text></option>
                            <option value="3">
                            <xsl:if test="atom:comment_mode = 3">
                                <xsl:attribute name="selected">selected</xsl:attribute>
                            </xsl:if>
                            <i18n:text>No comments allowed</i18n:text></option>
                            </select>
                        </td>
                    </tr>
                    <tr id="advanced3">
                        <xsl:call-template name="displayOrNot"/>
                        <td><i18n:text>Author</i18n:text>:</td>
                        <td>
                            <xsl:value-of select="atom:author/atom:name"/>
                        </td>
                        <td></td>
                    </tr>
                    <tr id="advanced4">
                    <xsl:call-template name="displayOrNot"/>
                        <td><i18n:text>Date</i18n:text>:</td>
                        <td>
                            <input id="created"  name="bx[plugins][admin_edit][created]" size="60" value="{atom:created}"/>
                            
                            
                        </td>
                        <td>
                        
                            <a href="#" onClick="cal.select(document.forms.entry.created,'anchor_post_date','yyyy-MM-ddT00:00'); return false;" name="anchor_post_date" id="anchor_post_date">select</a><div id="caldiv" style="position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></div>
                            <br/>
                        </td>
                    </tr>
                    <tr id="advanced5">
                        <xsl:call-template name="displayOrNot"/>
                        <td>Trackback URLs</td>
                        <td>
                            <input id="trackback"  name="bx[plugins][admin_edit][trackback]" size="60" value="{atom:trackback}"/>
                        </td>
                        <td valign="middle"><i18n:text>Auto Discovery</i18n:text><input class="checkbox" type="checkbox" id="autodiscovery"  name="bx[plugins][admin_edit][autodiscovery]"/> </td>
                    </tr>
                  <tr id="advanced6" >
                    <xsl:call-template name="displayOrNot"/>
                    <td><i18n:text>Upload Picture</i18n:text></td>
                    <td colspan="2 ">
                    
                    <iframe id="upload_iframe" src="./uploadimage.xml" frameborder="0" scrolling="no"/>
                    </td>
                  </tr>
                  <tr>
                     
                        <td colspan="3">
                          
                             <xsl:choose>
                                <xsl:when test="$doKupu">
                                    <textarea style="display: none;" id="content" cols="200" rows="20" name="bx[plugins][admin_edit][content]">
                                        <xsl:apply-templates select="atom:content/node()"  mode="escape"/>
                                    </textarea>
                                    <xsl:call-template name="kupueditor"/>
                                </xsl:when>
                                <xsl:otherwise>
                                  
                                  <div id="oneform" style="margin:0px;text-align:left;height:100%;">
                                    <textarea id="content" cols="100" style="width: 100%; height: 300px;" rows="50" name="bx[plugins][admin_edit][content]">
                                    <xsl:apply-templates select="atom:content/node()" mode="escape"/>
                                    
                                    </textarea>
                                    
                                  </div>
                                </xsl:otherwise>
                            </xsl:choose>
                        
                        </td>
                        
                    </tr>
                    <xsl:if test="not($doKupu)">
                    <tr><td>nl2br</td>
                    <td><input class="checkbox" type="checkbox" name="bx[plugins][admin_edit][nl2br]" checked="checked" value="1"/>
                    </td>
                     <td></td>
                    </tr>
                    </xsl:if>
                    		      
                 
                

                </table>

            </div>
            <xsl:call-template name="buttons"/>
            
            </form>            
            <xsl:call-template name="postcomments"/>
    </xsl:template>

    <xsl:template name="postcomments">
        <xsl:if test="count(atom:comments/atom:comment) > 0">
        <h2><i18n:text>Post Comments</i18n:text></h2>
        <form method="post">
        <table cellpadding="2" class="bigUglyBorderedEditTable">
            <xsl:apply-templates select="atom:comments/atom:comment"/>
            <tr><td colspan="5"><input type="submit" name="" value="Delete Selected Comments"/></td></tr>
        </table>
        </form>
        </xsl:if>
    </xsl:template>
    
    <xsl:template name="displayOrNot">
     <xsl:attribute name="style">
           <xsl:choose>
               <xsl:when test="$showAdvancedView = 'true'">
                </xsl:when>
                <xsl:otherwise>display: none;</xsl:otherwise>
             </xsl:choose>
     </xsl:attribute>
    </xsl:template>

    <xsl:template match="atom:content">
        <xsl:apply-templates select="*|text()" mode="xhtml"/>
    </xsl:template>

    <xsl:template match="*" mode="xhtml">&lt;<xsl:value-of select="local-name()"/>
        <xsl:apply-templates select="@*" mode="xhtml"/>&gt;<xsl:apply-templates mode="xhtml"/>&lt;/<xsl:value-of select="local-name()"/>&gt;</xsl:template>

    <xsl:template match="*[not(node())]" mode="xhtml">&lt;<xsl:value-of select="local-name()"/>
        <xsl:apply-templates select="@*" mode="xhtml"/>/&gt;</xsl:template>

    <xsl:template match="@*" mode="xhtml">
        <xsl:text> </xsl:text>
        <xsl:value-of select="local-name()"/>="<xsl:value-of select="."/>"</xsl:template>
<xsl:template name="buttons">
<xsl:param name="accesskeys" select="'false'"/>
    <xsl:choose>
               <xsl:when test="$doKupu">
                 <input class="button" type="button" i18n:attr="value" value="Save"  onclick="  if(updateTextAreas()) this.form.submit();" >
                    <xsl:if test="$accesskeys = 'true'">
                        <xsl:attribute name="accesskey">s</xsl:attribute>
                    </xsl:if>
                 </input>
                </xsl:when>
                <xsl:otherwise>
                 <input class="button" type="button" i18n:attr="value" value="Save"  onclick="if(formCheck()) this.form.submit();" >
                    <xsl:if test="$accesskeys = 'true'">
                       <xsl:attribute name="accesskey">s</xsl:attribute>
                    </xsl:if>
                </input>
                
                
                </xsl:otherwise>
                </xsl:choose>
       
         <input type="button" class="button" i18n:attr="value" value="New"  onclick="  reallyNew()" />
       <xsl:if test="atom:id ">
            
       <input type="button" value="Delete" i18n:attr="value" class="button" onclick=" if (reallyDelete()) this.form.submit();" />
         </xsl:if>
       <input type="button" class="button" i18n:attr="value" value="Preview">
    <xsl:choose>
               <xsl:when test="$doKupu">
               <xsl:attribute name="onclick">if(updateTextAreas()) { 
               kupu._initialized = true;
               startPreview(this.form);}</xsl:attribute>
               </xsl:when>
               <xsl:otherwise>
               <xsl:attribute name="onclick">if(formCheck()) { startPreview(this.form);}</xsl:attribute>
               
               </xsl:otherwise>
               </xsl:choose>
       </input>
       
</xsl:template>
    <xsl:template name="kupubody">
        <div style="display: none;">
            <xml id="kupuconfig">
                <kupuconfig>

                    <dst><xsl:value-of select="$webroot"/>webinc/plugins/blog/empty.html</dst>
                    <src><xsl:value-of select="$webroot"/>webinc/plugins/blog/empty.html</src>
                    <use_css>1</use_css>
                    <reload_after_save>0</reload_after_save>
                    <strict_output>1</strict_output>
                    <content_type>application/xhtml+xml</content_type>
                    <compatible_singletons>1</compatible_singletons>
            

                    <image_xsl_uri>
                        <xsl:value-of select="$webroot"/>/webinc/kupu/common/kupudrawers/drawer.xsl</image_xsl_uri>
                    <link_xsl_uri>
                        <xsl:value-of select="$webroot"/>/webinc/kupu/common/kupudrawers/drawer.xsl</link_xsl_uri>
                    <image_libraries_uri>
                        <xsl:value-of select="$webroot"/>admin/navi/kupu/?drawer=image</image_libraries_uri>
                    <link_libraries_uri>
                        <xsl:value-of select="$webroot"/>admin/navi/kupu/?drawer=library</link_libraries_uri>
                    <search_images_uri></search_images_uri>
                    <search_links_uri></search_links_uri>
           

                </kupuconfig>
            </xml>
        </div>
    </xsl:template>

    <xsl:template name="kupueditor">
        <div class="kupu-fulleditor">
    

            <div class="kupu-tb" id="toolbar">
    

                <span id="kupu-tb-buttons">
    
<!--
                    <span class="kupu-tb-buttongroup" style="float: right">
                        <button type="button" class="kupu-logo" title="Kupu 1.1" accesskey="k" onclick="window.open('http://kupu.oscom.org');">&#xA0;</button>
                    </span>
    -->

                    <select id="kupu-tb-styles">
                        <option value="P">
        Normal
      </option>
                        <option value="H1">
                            <span>Heading</span> 1
      </option>
                        <option value="H2">
                            <span>Heading</span> 2
      </option>
                        <option value="H3">
                            <span>Heading</span> 3
      </option>
                        <option value="H4">
                            <span>Heading</span> 4
      </option>
                        <option value="H5">
                            <span>Heading</span> 5
      </option>
                        <option value="H6">
                            <span>Heading</span> 6
      </option>
                        <option value="PRE">
        Formatted
      </option>
                    </select>
    

                    <span style="display: none;" class="kupu-tb-buttongroup">

                        <button type="button" class="kupu-save" id="kupu-save-button" title="save: alt-s" accesskey="s">&#xA0;</button>
                    </span>
    

                    <span class="kupu-tb-buttongroup">
                        <button type="button" class="kupu-bold" id="kupu-bold-button" title="bold: alt-b" accesskey="b">&#xA0;</button>
                        <button type="button" class="kupu-italic" id="kupu-italic-button" title="italic: alt-i" accesskey="i">&#xA0;</button>
                        <button type="button" class="kupu-underline" id="kupu-underline-button" title="underline: alt-u" accesskey="u">&#xA0;</button>
                    </span>
    

                    <span class="kupu-tb-buttongroup">
                        <button type="button" class="kupu-subscript" id="kupu-subscript-button" title="subscript: alt--" accesskey="-">&#xA0;</button>
                        <button type="button" class="kupu-superscript" id="kupu-superscript-button" title="superscript: alt-+" accesskey="+">&#xA0;</button>
                    </span>
    

                    <span style="display: none;" class="kupu-tb-buttongroup">

                        <button type="button" class="kupu-forecolor" id="kupu-forecolor-button" title="text color: alt-f" accesskey="f">&#xA0;</button>
                        <button type="button" class="kupu-hilitecolor" id="kupu-hilitecolor-button" title="background color: alt-h" accesskey="h">&#xA0;</button>
                    </span>
    

                    <span class="kupu-tb-buttongroup">
                        <button type="button" class="kupu-justifyleft" id="kupu-justifyleft-button" title="left justify: alt-l" accesskey="l">&#xA0;</button>
                        <button type="button" class="kupu-justifycenter" id="kupu-justifycenter-button" title="center justify: alt-c" accesskey="c">&#xA0;</button>
                        <button type="button" class="kupu-justifyright" id="kupu-justifyright-button" title="right justify: alt-r" accesskey="r">&#xA0;</button>
                    </span>
    

                    <span class="kupu-tb-buttongroup">

                        <button type="button" class="kupu-insertorderedlist" title="numbered list: alt-#" id="kupu-list-ol-addbutton" accesskey="#">&#xA0;</button>
                        <button type="button" class="kupu-insertunorderedlist" title="unordered list: alt-*" id="kupu-list-ul-addbutton" accesskey="*">&#xA0;</button>
                    </span>
    
<!--
                    <span class="kupu-tb-buttongroup">

                        <button type="button" class="kupu-insertdefinitionlist" title="definition list: alt-=" id="kupu-list-dl-addbutton" accesskey="=">&#xA0;</button>
                    </span>
    -->

                    <span class="kupu-tb-buttongroup">
                        <button type="button" class="kupu-outdent" id="kupu-outdent-button" title="outdent: alt-&lt;" accesskey="&lt;">&#xA0;</button>
                        <button type="button" class="kupu-indent" id="kupu-indent-button" title="indent: alt-&gt;" accesskey="&gt;">&#xA0;</button>
                    </span>

                    <span class="kupu-tb-buttongroup">
      <button  type="button" class="kupu-image" id="kupu-imagelibdrawer-button" title="image" i18n:attributes="title"> </button>
      <button  type="button" class="kupu-inthyperlink" id="kupu-linklibdrawer-button" title="internal link" i18n:attributes="title"> </button>
      <button  type="button" class="kupu-exthyperlink" id="kupu-linkdrawer-button" title="external link" i18n:attributes="title"> </button>
      <button  type="button" class="kupu-table" id="kupu-tabledrawer-button" title="table" i18n:attributes="title"> </button>
    </span>
  
    
    <span class="kupu-tb-buttongroup" id="kupu-bg-remove">
      <button  type="button" class="kupu-removeimage invisible" id="kupu-removeimage-button" title="Remove image" i18n:attributes="title"> </button>
      <button  type="button" class="kupu-removelink invisible" id="kupu-removelink-button" title="Remove link" i18n:attributes="title"> </button>
    </span>
  
    
    <span class="kupu-tb-buttongroup" id="kupu-bg-undo">
      <button  type="button" class="kupu-undo" id="kupu-undo-button" title="undo: alt-z" i18n:attributes="title" accesskey="z"> </button>
      <button  type="button" class="kupu-redo" id="kupu-redo-button" title="redo: alt-y" i18n:attributes="title" accesskey="y"> </button>
    </span>
  
    
    <span class="kupu-tb-buttongroup" id="kupu-source">
      <button  type="button" class="kupu-source" id="kupu-source-button" title="edit HTML code" i18n:attributes="title"> </button>
    </span>
  
   
    </span>
  
    
    <select id="kupu-ulstyles">
      <option  value="disc" i18n:translate="list-disc">&#x25CF;</option>
      <option  value="square" i18n:translate="list-square">&#x25A0;</option>
      <option  value="circle" i18n:translate="list-circle">&#x25CB;</option>
      <option  value="none" i18n:translate="list-nobullet">no bullet</option>
    </select>
    <select id="kupu-olstyles">
      <option  value="decimal" i18n:translate="list-decimal">1</option>
      <option  value="upper-roman" i18n:translate="list-upperroman">I</option>
      <option  value="lower-roman" i18n:translate="list-lowerroman">i</option>
      <option  value="upper-alpha" i18n:translate="list-upperalpha">A</option>
      <option  value="lower-alpha" i18n:translate="list-loweralpha">a</option>
    </select>
  
    
    
    <!-- drawers -->
              <div style="display:block;">
      <div id="kupu-librarydrawer" class="kupu-drawer">
      </div>
    </div>
  
    
    <div id="kupu-linkdrawer" class="kupu-drawer">
      <h1  i18n:translate="">External Link</h1>

      <div id="kupu-linkdrawer-addlink" class="kupu-panels">
        <table>
        <tr><td><div class="kupu-toolbox-label">
          
          <span  i18n:translate="items-matching-keyword">
            Link the highlighted text to this URL
          </span>:
        </div>

        <input id="kupu-linkdrawer-input" class="kupu-toolbox-st" type="text"/>
        </td>
       
        <td class="kupu-preview-button" style="display:none;">
        <button type="button" onclick="drawertool.current_drawer.preview()" >Preview</button>
        </td></tr>
        <tr><td colspan="2"  style="display:none;" align="center">
        <iframe frameborder="1" scrolling="auto" width="440" height="198" id="kupu-linkdrawer-preview" src="{$webroot}webinc/plugins/blog/empty.html">
        </iframe>
        </td></tr>
        </table>

        <div class="kupu-dialogbuttons">
          <button type="button" onclick="drawertool.current_drawer.save()">Ok</button>
          <button type="button" onclick="drawertool.closeDrawer()">Cancel</button>
        </div>

      </div>
    </div>
  
    
    <div id="kupu-tabledrawer" class="kupu-drawer">
    <h1>Table</h1>
    <div class="kupu-panels">
      <table width="99%">
        <tr class="kupu-panelsrow">
          <td class="kupu-panel">
            <table width="100%">
              <tbody>
                <tr>
                  <td class="kupu-toolbox-label">Table Class</td>
                  <td width="50%">
                    <select id="kupu-tabledrawer-classchooser" onchange="drawertool.current_drawer.tool.setTableClass(this.options[this.selectedIndex].value)">
                      <option value="plain">Plain</option>
                      <option value="listing">Listing</option>
                      <option value="grid">Grid</option>
                      <option value="data">Data</option>
                    </select>
                  </td>
                </tr>

                <tr>
                  <td colspan="2" class="">

                  
                  <div id="kupu-tabledrawer-addtable">
                    <table width="100%">
                      <tr>
                        <td class="kupu-toolbox-label" width="50%">Rows</td>
                        <td><input type="text" id="kupu-tabledrawer-newrows"/></td>
                      </tr>
                      <tr>
                        <td class="kupu-toolbox-label">Columns</td>
                        <td><input type="text" id="kupu-tabledrawer-newcols"/></td>
                      </tr>
                      <tr>
                        <td class="kupu-toolbox-label">Headings</td>
                        <td class="kupu-toolbox-label">
                          <input name="kupu-tabledrawer-makeheader" id="kupu-tabledrawer-makeheader" type="checkbox"/>
                          <label for="kupu-tabledrawer-makeheader">Create</label>
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2" style="text-align: center">
                            <button type="button" onclick="drawertool.current_drawer.createTable()">Add Table</button>
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2" style="text-align: center">
                            <button type="button" onclick="drawertool.current_drawer.tool.fixAllTables()">Fix All Tables</button>
                        </td>
                      </tr>
                    </table>
                  </div>

                  
                  <div id="kupu-tabledrawer-edittable">
                    <table width="100%">
                      <tr>
                        <td width="50%">Current column alignment</td>
                        <td>
                          <select id="kupu-tabledrawer-alignchooser" onchange="drawertool.current_drawer.tool.setColumnAlign(this.options[this.selectedIndex].value)">
                            <option value="left">Left</option>
                            <option value="center">Center</option>
                            <option value="right">Right</option>
                            </select>
                        </td>
                      </tr>
                      <tr>
                        <td>Column</td>
                        <td>
                          <button type="button" id="kupu-tabledrawer-addcolumn-button" onclick="drawertool.current_drawer.tool.addTableColumn()">Add</button>
                          <button type="button" id="kupu-tabledrawer-delcolumn-button" onclick="drawertool.current_drawer.tool.delTableColumn()">Remove</button>
                        </td>
                      </tr>
                      <tr>
                        <td>Row</td>
                        <td>
                          <button type="button" id="kupu-tabledrawer-addrow-button" onclick="drawertool.current_drawer.tool.addTableRow()">Add</button> 
                          <button type="button" id="kupu-tabledrawer-delrow-button" onclick="drawertool.current_drawer.tool.delTableRow()">Remove</button>
                        </td>
                      </tr>
                      <tr>
                        <td>Fix Table</td>
                        <td>
                          <button type="button" id="kupu-tabledrawer-addrow-button" onclick="drawertool.current_drawer.tool.fixTable()">Fix</button> 
                        </td>
                      </tr>
                    </table>
                  </div>

                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </table>
      <div class="kupu-dialogbuttons">
        <button type="button" onclick="drawertool.closeDrawer()">Close</button>
      </div>
    </div>
    </div>
    
    <!-- end toolbar div -->
            </div>
     

            <div class="kupu-toolboxes" style="display: none;">
                <div class="kupu-toolbox" id="kupu-toolbox-properties">
                    <h1>Properties</h1>

                    <table id="kupu-properties">
                        <tbody>
                            <tr>
                                <td class="kupu-toolbox-label">Title</td>
                                <td>
                                    <input id="kupu-properties-title"/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="kupu-toolbox-label">Description</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <textarea id="kupu-properties-description">lala </textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
    

                <div class="kupu-toolbox" id="kupu-toolbox-links">
                    <h1>Links</h1>

                    <div id="kupu-toolbox-addlink">
                        <div class="kupu-toolbox-label">

                            <span>
            Link the highlighted text to this URL
          </span>:
        </div>

                        <input id="kupu-link-input" class="kupu-toolbox-st" type="text" size="14"/>
                        <div style="text-align: center">
                            <button type="button" id="kupu-link-button" class="kupu-toolbox-action">Make Link</button>
                        </div>

                    </div>
                </div>
                <div class="kupu-toolbox" id="kupu-toolbox-images">
                    <h1>Images</h1>

                    <div class="kupu-toolbox-label">
                        <span>
                Insert image at the following URL
              </span>:
            </div>

                    <div style="text-align: center">
                        <button type="button" id="kupu-image-addbutton" class="kupu-toolbox-action">Insert Image</button>
                    </div>
                </div>
  

                <div class="kupu-toolbox" id="kupu-toolbox-tables">
                    <h1>Tables</h1>

                    <table width="100%">
                        <tbody>
                            <tr>
                                <td class="kupu-toolbox-label">Table Class</td>
                                <td>
                                    <select id="kupu-table-classchooser">
                                        <option value="plain">Plain</option>
                                        <option value="listing">Listing</option>
                                        <option value="grid">Grid</option>
                                        <option value="data">Data</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div id="kupu-toolbox-addtable">
                                        <table width="100%">
                                            <tr>
                                                <td class="kupu-toolbox-label">Rows</td>
                                                <td>
                                                    <input id="kupu-table-newrows"/>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="kupu-toolbox-label">Columns</td>
                                                <td>
                                                    <input id="kupu-table-newcols"/>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="kupu-toolbox-label">Headings</td>
                                                <td class="kupu-toolbox-label">
                                                    <input name="kupu-table-makeheader" id="kupu-table-makeheader" type="checkbox"/>
                                                    <label for="kupu-table-makeheader">Create</label>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="text-align: center">
                                                    <button type="button" id="kupu-table-addtable-button">Add Table</button>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div id="kupu-toolbox-edittable">
                                        <table width="100%">
                                            <tr>
                                                <td>Col Align</td>
                                                <td>
                                                    <select id="kupu-table-alignchooser">
                                                        <option value="left">Left</option>
                                                        <option value="center">Center</option>
                                                        <option value="right">Right</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Column</td>
                                                <td>
                                                    <button type="button" id="kupu-table-addcolumn-button">Add</button>
                                                    <button type="button" id="kupu-table-delcolumn-button">Remove</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Row</td>
                                                <td>
                                                    <button type="button" id="kupu-table-addrow-button">Add</button>
                                                    <button type="button" id="kupu-table-delrow-button">Remove</button>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="kupu-toolbox" id="kupu-toolbox-inspector">

                    <h1>Inspector</h1>

                    <table id="kupu-inspector" width="100%">
                        <tbody>
                            <tr>
                                <td align="center">
                                    <form method="post" action="#" onsubmit="return false;" id="kupu-inspector-form">
             &#160; </form>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>
 

                <div class="kupu-toolbox" style="" id="kupu-toolbox-debug">
                    <h1>Debug Log</h1>
                    <div id="kupu-toolbox-debuglog" class="kupu-toolbox-label" style="height: 150px;">
                        <xsl:text> </xsl:text>
                    </div>
                </div>

            </div>
            <table id="kupu-colorchooser" cellpadding="0" cellspacing="0" style="position: fixed; border-style: solid; border-color: black; border-width: 1px;">
                <tr>
                    <td>&#160;</td>
                </tr>
            </table>
    

            <div class="kupu-editorframe">

                <iframe id="kupu-editor" frameborder="0" src="{$webroot}webinc/plugins/blog/empty.html" dst="{$webroot}webinc/plugins/blog/empty.html" reloadsrc="0" usecss="1" strict_output="1" content_type="application/xhtml+xml" scrolling="auto">
          </iframe>

                <textarea id="kupu-editor-textarea" style="display: none">
                    <xsl:text> </xsl:text>
                </textarea>

            </div>
  

        </div>
    </xsl:template>
    <xsl:template name="kupuhead">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <link href="{$webroot}/webinc/kupu/common/kupustyles.css" rel="stylesheet" type="text/css"/>
  

        <link href="{$webroot}/webinc/kupu/common/kupucustom.css" rel="stylesheet" type="text/css"/>
        <link href="{$webroot}/webinc/kupu/common/kupudrawerstyles.css" rel="stylesheet" type="text/css"/>

        <link rel="stylesheet" type="text/css" href="{$webroot}/themes/standard/admin/css/admin.css"/>

        <link rel="stylesheet" type="text/css" href="{$webroot}/themes/standard/admin/css/admin.css"/>

        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/sarissa.js"><xsl:text> </xsl:text></script>
        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/kupuhelpers.js"><xsl:text> </xsl:text></script>
        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/kupueditor.js"><xsl:text> </xsl:text></script>
        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/kupubasetools.js"><xsl:text> </xsl:text></script>
        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/kupuloggers.js"><xsl:text> </xsl:text></script>
        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/kupucontentfilters.js"><xsl:text> </xsl:text></script>
        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/kupucontextmenu.js"><xsl:text> </xsl:text></script> 
       <!-- <script type="text/javascript" src="{$webroot}/webinc/kupu/common/kupuinspector.js"/> -->
        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/kupuinit_experimental.js"><xsl:text> </xsl:text></script>
        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/kupusaveonpart.js"><xsl:text> </xsl:text></script>
        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/kupusourceedit.js"><xsl:text> </xsl:text></script>
        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/kupudrawers.js"><xsl:text> </xsl:text></script>
        <script type="text/javascript" src="{$webroot}/webinc/plugins/blog/kupu.js"><xsl:text> </xsl:text></script>

    </xsl:template>

    
<xsl:template match="xhtml:*" mode="escape">&lt;<xsl:value-of select="local-name()"/> <xsl:for-each select="@*"><xsl:text> </xsl:text><xsl:value-of select="local-name()"/>="<xsl:value-of select="."/>"</xsl:for-each>&gt;<xsl:apply-templates mode="escape"/>&lt;/<xsl:value-of select="local-name()"/>&gt;</xsl:template>

<xsl:template match="xhtml:*[not(node())]" mode="escape">&lt;<xsl:value-of select="local-name()"/> <xsl:for-each select="@*"><xsl:text> </xsl:text><xsl:value-of select="local-name()"/>="<xsl:value-of select="."/>"</xsl:for-each> /&gt;</xsl:template>

<xsl:template match="xhtml:br|xhtml:br[not(node())]" mode="escape">
  <xsl:if test="$doKupu">
  <br/>
  </xsl:if>
</xsl:template>

  <xsl:template match="text()" mode="escape">
     <xsl:value-of select="php:functionString('htmlspecialchars',.,0,'UTF-8')"  />
    </xsl:template>
</xsl:stylesheet>
