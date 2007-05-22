
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
    
    <xsl:variable name="langsAvail" select="php:functionString('bx_helpers_config::getLangsAvailXML')"/>
    <xsl:variable name="showAdvancedView" select="php:functionString('bx_helpers_globals::COOKIE','blogAdvancedView')"/> 
    <xsl:variable name="editor" select="php:functionString('bx_helpers_config::getOption','blogDefaultEditor')"/> 
    <xsl:variable name="doFck" select="php:functionString('popoon_classes_browser::hasContentEditable') and $editor != 'source'"/>
    
   
    <xsl:template match="/">
        <html>

            <head>
            
                <link rel="stylesheet" href="{$webroot}themes/standard/admin/css/blog.css" type="text/css"/>
                <link rel="stylesheet" href="{$webroot}themes/standard/admin/css/formedit.css" type="text/css"/>
                <xsl:choose>
            <xsl:when test="$doFck">
              <script type="text/javascript" src="{$webroot}webinc/fck/fckeditor.js">
            <xsl:text> </xsl:text>
        </script>
        <script type="text/javascript" src="{$webroot}webinc/plugins/blog/fck.js">
            <xsl:text> </xsl:text>
        </script>
		<script type="text/javascript" src="{$webroot}webinc/js/prototype.lite.js">
		<xsl:text> </xsl:text>
		</script>
            <script type="text/javascript" src="{$webroot}webinc/js/moo.ajax.js">
		<xsl:text> </xsl:text>
		</script>
        <script type="text/javascript">
            var fckBasePath	= "<xsl:value-of select="$webroot"/>webinc/fck/";
            var bx_webroot = "<xsl:value-of select="$webroot"/>";
            var contentURI = null;
        </script>
        </xsl:when>
        <xsl:otherwise>
        <script type="text/javascript" src="{$webroot}webinc/plugins/blog/quicktags.js">
                </script>
        <script type="text/javascript" src="{$webroot}webinc/js/sarissa_dbform.js">
                </script>
        </xsl:otherwise>
        </xsl:choose>
               
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
            liveSearchParams = "blogadmin=1";
        </script>
            </head>
            <body>
            <xsl:choose>
            <xsl:when test="$doFck">
                <xsl:attribute name="onload">initFck();</xsl:attribute>
            </xsl:when>
            <xsl:otherwise>
       <!--<xsl:attribute name="onload">liveSearchInit(); </xsl:attribute>-->
            </xsl:otherwise>
            </xsl:choose>
          
                    <xsl:call-template name="displayTabs">
                        <xsl:with-param name="selected" select="'post'"/>
                    </xsl:call-template>
                
            <xsl:if test="not($doFck) and $editor != 'source'"><br/>
                <font color="red"><i18n:text>This page would have a nice and easy to use WYISYWIG editor on Mozilla/Firefox or Windows IE.
               
                <br /> Please consider using Mozilla/Firefox to get the best experiance.</i18n:text></font>
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
        <form onsubmit="return formCheck(this);"  method="post" name="entry" action=".">
         <xsl:call-template name="buttons">
		 
		 <xsl:with-param name="accesskeys" select="'true'"/>
         </xsl:call-template>
         <xsl:if test="/atom:entry/@hasStorage = 'true'">
		 <br/><a href="./storage.xml">You have a unsaved entry 
		 (Titel: <xsl:value-of select="/atom:entry/@storageTitle"/> on <xsl:value-of select="/atom:entry/@storageDate"/>), 
		 do you want to load it? 
		 </a>
		 </xsl:if>
          
		 <input type="hidden" id="id" name="bx[plugins][admin_edit][id]" value="{atom:id}"/>
            <input type="hidden" id="delete" name="bx[plugins][admin_edit][delete]" value="0"/>
            <div class="formTable">
                    <xsl:variable name="cats" select="/atom:entry/atom:categories"/>



            
                <table class="bigUglyBorderedEditTable">
                    <tr>
                        <td><i18n:text>Title</i18n:text>:</td>
                        <td >
                            <input id="title" onkeyup="updateUriField('title',document.getElementById('uri'));" name="bx[plugins][admin_edit][title]" size="40" value="{atom:title}"/>
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
                                <p><i18n:text>New Category</i18n:text></p>
                                <input id="newcategory" type="text" name="bx[plugins][admin_edit][newcategory]" value=""/>
                                <br/>
                            </div>
            
</td>
                    </tr>
                    
                    
					
                    <tr>
                        <td>Uri:
                        <span onmouseout="document.getElementById('uri_help').style.display = 'none';"
                        onmouseover="document.getElementById('uri_help').style.display = 'block';">(?)</span>
                        <div id="uri_help" class="helptip"><i18n:text>No special chars.</i18n:text></div>
                        
                        </td>
                        <td >
                            <input id="uri" onkeyup="this.edited=true; " name="bx[plugins][admin_edit][uri]" size="40" value="{atom:uri}"/>
                        </td>
                        <td></td>
                    </tr>
                     <tr>
                        <td>Tags:  <span class="helpmark"
                        onmouseout="document.getElementById('tags_help').style.display = 'none';"
                        onmouseover="document.getElementById('tags_help').style.display = 'block';">(?)</span> 
                        <div id="tags_help" class="helptip"><i18n:text>Space or comma separated.</i18n:text></div>
                         </td>
                        <td >
                            <input id="tags"  name="bx[plugins][admin_edit][tags]" size="40" value="{atom:tags}"/>
                        </td>
                       <td></td>
                    </tr>
                    <tr id="foo">
                        <td valign="top" colspan="2">
                            <img onclick="toggleAdvanced();" id="advanced_triangle" src="{$webroot}admin/webinc/img/closed_klein.gif">
                             <xsl:attribute name="src">
                                   <xsl:choose>
                                       <xsl:when test="$showAdvancedView = 'true'"><xsl:value-of select="$webroot"/>admin/webinc/img/open_klein.gif</xsl:when>
                                        <xsl:otherwise><xsl:value-of select="$webroot"/>admin/webinc/img/closed_klein.gif</xsl:otherwise>
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
                            <input id="created"  name="bx[plugins][admin_edit][created]" size="40" value="{atom:created}"/>
                            
                            
                        </td>
                        <td>
                        
                            <a href="#" onClick="cal.select(document.forms.entry.created,'anchor_post_date','yyyy-MM-ddT00:00'); return false;" name="anchor_post_date" id="anchor_post_date">select</a><div id="caldiv" style="position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></div>
                            <br/>
                        </td>
                    </tr>
                    
                    <tr id="advanced5">
                        <xsl:call-template name="displayOrNot"/>
                        <td>Lang:
                        <xsl:value-of select="$langsAvail/langs"/>
                        </td>
                        <td >
                        <xsl:variable name="postLang" select="/atom:entry/atom:lang"/>
                        <select id="lang" name="bx[plugins][admin_edit][lang]">
                            <option value="">
                                None
                            </option>
                        <xsl:for-each select="$langsAvail/langs/entry">
                            <xsl:variable name="lang" select="."/>
                            <option value="{.}">
                                <xsl:if test="$lang = $postLang">
                                    <xsl:attribute name="selected">selected</xsl:attribute>
                                </xsl:if>
                                <xsl:value-of select="."/>
                            </option>
                            
                        </xsl:for-each>    
                            </select>
                        </td>
                        <td></td>
                    </tr>
                    
                    <tr id="advanced7">
                    <xsl:call-template name="displayOrNot"/>
                        <td><i18n:text>Expiration Date</i18n:text>:</td>
                        <td>
                            <input id="expires"  name="bx[plugins][admin_edit][expires]" size="40" value="{atom:expires}"/>
                        </td>
                        <td>
                            <a href="#" onClick="cal.select(document.forms.entry.expires,'anchor_post_date','yyyy-MM-ddT00:00'); return false;" name="anchor_post_date" id="anchor_post_date">select</a><div id="caldiv" style="position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></div>
                            <br/>
                        </td>
                    </tr>

                    
                    <tr id="advanced5">
                        <xsl:call-template name="displayOrNot"/>
                        <td>Trackback URLs</td>
                        <td>
                            <input id="trackback"  name="bx[plugins][admin_edit][trackback]" size="40" value="{atom:trackback}"/>
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
                                 <div id="oneform" style="margin:0px;text-align:left;height:100%;">
                   
                                 <xsl:if test="not($doFck)">
                                 <script type="text/javascript">edToolbar();</script>
                                </xsl:if>
                                 
                                <textarea id="bx[plugins][admin_edit][content]" cols="100" style="width: 100%; height: 300px;" rows="50" name="bx[plugins][admin_edit][content]">
                                <xsl:apply-templates select="atom:content/node()" mode="escape"/>
                                </textarea>
                                 <xsl:if test="not($doFck)">
                                
                                <script type="text/javascript">var edCanvas = document.getElementById('bx[plugins][admin_edit][content]');</script>
                                </xsl:if>
                                </div>
                        </td>
                    </tr>
                    <xsl:if test="not(string-length(atom:content_extended) &gt; 0)" >
                    <tr id="toggleExtended">
                        <td valign="top" colspan="2">
                            <img onclick="toggleExtendedPost();" id="advanced_triangle" src="{$webroot}admin/webinc/img/closed_klein.gif">
                             <xsl:attribute name="src">
                                   <xsl:choose>
                                       <xsl:when test="$showAdvancedView = 'true'"><xsl:value-of select="$webroot"/>admin/webinc/img/open_klein.gif</xsl:when>
                                        <xsl:otherwise><xsl:value-of select="$webroot"/>admin/webinc/img/closed_klein.gif</xsl:otherwise>
                                     </xsl:choose>
                             </xsl:attribute>
                             </img> <span onclick="toggleExtendedPost();"><i18n:text>Make an Extended Post</i18n:text></span>
                        </td>
                    </tr>
                    </xsl:if>
                    <tr id="postExtended">
                        <xsl:if test="not(string-length(atom:content_extended) &gt; 0)" >
                            <xsl:attribute name="style">display: none;</xsl:attribute>
                        </xsl:if>
                        <td colspan="3">
                            <div id="oneform_extended" style="margin:0px;text-align:left;height:100%;">
                              <!--  <xsl:if test="not($doFck)">
                                 <script type="text/javascript">edToolbar();</script>
                                </xsl:if>-->
                            <textarea id="bx[plugins][admin_edit][content_extended]" cols="100" style="width: 100%; height: 300px;" rows="50" name="bx[plugins][admin_edit][content_extended]">
                            <xsl:apply-templates select="atom:content_extended/node()" mode="escape"/>
                            </textarea>
                            <!--
                            <xsl:if test="not($doFck)">
                                
                                <script type="text/javascript">var edCanvas2 = document.getElementById('bx[plugins][admin_edit][content_extended]');</script>
                                </xsl:if>-->
                          </div>
                        </td>    
                    </tr>
            
                    <xsl:if test="not($doFck)">
                    <tr><td>nl2br</td>
                    <td><input class="checkbox" type="checkbox" name="bx[plugins][admin_edit][nl2br]" checked="checked" value="1"/>
                    </td>
                     <td></td>
                    </tr>
                    </xsl:if>
                    		      
                 
                

                </table>

            </div>
            <xsl:call-template name="buttonsBottom"/>
            
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
    
                 <input class="button" type="submit" i18n:attr="value" value="Save" id="Save" name="bx[plugins][admin_edit][save]">
				 <xsl:if test="$accesskeys = 'true'">
                       <xsl:attribute name="accesskey">s</xsl:attribute>
                    </xsl:if>
                </input>
                
                
              
       
<!--         <input type="button" class="button" i18n:attr="value" value="New"  onclick="  reallyNew()" />-->
       <xsl:if test="atom:id ">
            
       <input type="button" value="Delete" i18n:attr="value" class="button" onclick=" if (reallyDelete()) this.form.submit();" />
         </xsl:if>
       <input type="button" class="button" i18n:attr="value" value="Preview">
    <xsl:choose>
               <xsl:when test="$doFck">
               <xsl:attribute name="onclick">if(updateTextAreas(this.form,'1')) { startPreview(this.form);}</xsl:attribute>
               </xsl:when>
               <xsl:otherwise>
               <xsl:attribute name="onclick">if(formCheck(this.form,'1')) { startPreview(this.form);}</xsl:attribute>
               
               </xsl:otherwise>
               </xsl:choose>
       </input>
	   
	   <input type="button" class="button" i18n:attr="value" value="Draft" onclick="formCheck('draft')"/>
       
</xsl:template>
    
<xsl:template name="buttonsBottom">
<xsl:param name="accesskeys" select="'false'"/>
    
                 <input class="button" type="submit" i18n:attr="value" value="Save" id="SaveBottom" name="bx[plugins][admin_edit][saveBottom]">
				 <xsl:if test="$accesskeys = 'true'">
                       <xsl:attribute name="accesskey">s</xsl:attribute>
                    </xsl:if>
                </input>
                
                
              
       
<!--         <input type="button" class="button" i18n:attr="value" value="New"  onclick="  reallyNew()" />-->
       <xsl:if test="atom:id ">
            
       <input type="button" value="Delete" i18n:attr="value" class="button" onclick=" if (reallyDelete()) this.form.submit();" />
         </xsl:if>
       <input type="button" class="button" i18n:attr="value" value="Preview">
    <xsl:choose>
               <xsl:when test="$doFck">
               <xsl:attribute name="onclick">if(updateTextAreas()) { startPreview(this.form);}</xsl:attribute>
               </xsl:when>
               <xsl:otherwise>
               <xsl:attribute name="onclick">if(formCheck()) { startPreview(this.form);}</xsl:attribute>
               
               </xsl:otherwise>
               </xsl:choose>
       </input>
	   
	   <input type="button" class="button" i18n:attr="value" value="Draft" onclick="formCheck('draft')"/>
       
</xsl:template>
   
 
    
<xsl:template match="xhtml:*" mode="escape">&lt;<xsl:value-of select="local-name()"/> <xsl:for-each select="@*"><xsl:text> </xsl:text><xsl:value-of select="local-name()"/>="<xsl:value-of select="."/>"</xsl:for-each>&gt;<xsl:apply-templates mode="escape"/>&lt;/<xsl:value-of select="local-name()"/>&gt;</xsl:template>

<xsl:template match="xhtml:*[not(node())]" mode="escape">&lt;<xsl:value-of select="local-name()"/> <xsl:for-each select="@*"><xsl:text> </xsl:text><xsl:value-of select="local-name()"/>="<xsl:value-of select="."/>"</xsl:for-each> /&gt;</xsl:template>

<xsl:template match="xhtml:br|xhtml:br[not(node())]" mode="escape">
  <xsl:if test="$doFck">
  <br/>
  </xsl:if>
</xsl:template>

  <xsl:template match="text()" mode="escape">
     <xsl:value-of select="php:functionString('htmlspecialchars',.,0,'UTF-8')"  />
    </xsl:template>
</xsl:stylesheet>
