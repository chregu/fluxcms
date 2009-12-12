<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:php="http://php.net/xsl" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml">
    <xsl:output encoding="utf-8" method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

    <xsl:param name="url" select="'/'"/>
    <xsl:param name="id" select="'/'"/>
    <xsl:param name="dataUri" select="'/'"/>
    <xsl:param name="webroot" select="'/'"/>
    <xsl:template match="/">
        <xsl:variable name="selectedID" select="bx/plugin/links/editlink/editlink/id/text()"/>
        <html>
            <head>
                <title>Edit Link</title>
                <link rel="stylesheet" href="{$webroot}themes/standard/admin/css/formedit.css" type="text/css"/>
                <link rel="stylesheet" href="{$webroot}webinc/plugins/dbform/css/admin.css" type="text/css"/>
                 <script type="text/javascript" language="JavaScript" src="{$webroot}webinc/js/CalendarPopup.js"></script>
                   <script language="JavaScript" type="text/javascript">

        document.write(getCalendarStyles());
      
        var cal = new CalendarPopup('caldiv');
        cal.showYearNavigation();
</script>
                 
            </head>
            <body>
                <table cellspacing="0" width="700" border="0" id="topbar">
                    <tr>
                        <td class="bgDarkGreen">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" class="bgDarkGreen" id="subtopbar">
                                <tr class="chooser bgDarkGreen">

                                    <td width="5%"></td>
                                    <td width="5%">

                                    </td>
                                    <td>Links:</td>
                                    <td>
                                        <form name="chooser" action="/forms/bloglinks/" style="margin: 0pt;" id="chooser">
                                            <select class="chooser" name="id" onChange="document.location.href='./' + this.options[this.selectedIndex].value + '.links'">

                                                <option value="0">None</option>

                                                <xsl:for-each select="/bx/plugin/links/links/links">

                                                    <option value="{id}">
                                                        <xsl:if test="id = $selectedID">
                                                            <xsl:attribute name="selected">selected</xsl:attribute>
                                                        </xsl:if>
                                                        <xsl:value-of select="text"/>
                                                    </option>
                                                </xsl:for-each>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
                <br/>
                <div id="formedit">
                    <xsl:call-template name="buttons"/>

                    <xsl:for-each select="/bx/plugin/links/*/editlink">

                        <form name="Master" action="./{id}.links" method="post" enctype="multipart/form-data" id="Master">
                            <div id="formTable">
                                <table class="bigUglyEditTable">
                                    <tr>
                                        <td>
                                            <div class="blackH5"> Title </div>
                                        </td>
                                        <td class="blackH5">
                                            <input id="text" class="blackH5" name="bx[plugins][admin_edit][text]" size="80" value="{text}" type="text"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>

                                            <div class="blackH5">Link </div>
                                        </td>
                                        <td class="blackH5">
                                            <input id="link" class="blackH5" name="bx[plugins][admin_edit][link]" size="80" value="{link}" type="text"/>
                                        </td>
                                    </tr>
                                    

                                    <tr>
                                        <td>

                                            <div class="blackH5">Tags </div>
                                        </td>
                                        <td class="blackH5">
                                            <input id="tags" class="blackH5" name="bx[plugins][admin_edit][tags]" size="80" value="{/bx/plugin/links/result/tags}" type="text"/>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <div class="blackH5"> Description </div>
                                        </td>
                                        <td class="blackH5">
                                            <textarea id="description" class="blackH5" wrap="virtual" name="bx[plugins][admin_edit][description]" rows="3" cols="80">
                                                <xsl:value-of select="description"/>
                                            </textarea>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="blackH5">Date</div>
                                        </td>
                                        <td class="blackH5">
                                            <input name="bx[plugins][admin_edit][date]" type="text" id="date" size="20" value="{date}"/>
                                            <a href="#" onClick="cal.select(document.getElementById('date'),'anchor_date','yyyy-MM-dd 00:00:00'); return false;" name="anchor_date" id="anchor_date">select</a>
                                            <div id="caldiv" style="position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="blackH5">
                  Category
                </div>
                                        </td>
                                        <td class="blackH5">
                                            <select class="blackH5" name="bx[plugins][admin_edit][bloglinkscategories]">

                                                <option value="0">
                    None
                  </option>
                                                <xsl:variable name="catID" select="bloglinkscategories"/>
                                                <xsl:for-each select="/bx/plugin/links/categories/categories">
                                                    <option value="{id}">
                                                        <xsl:if test="$catID = id">
                                                            <xsl:attribute name="selected">selected</xsl:attribute>
                                                        </xsl:if>

                                                        <xsl:value-of select="name"/>
                                                    </option>
                                                </xsl:for-each>
                                            </select>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="blackH5"> Rang </div>
                                        </td>
                                        <td class="blackH5"> (<xsl:value-of select="rang"/>) Show After: 
                                        
                                        <select style="width: 300px;" id="rang" class="blackH5" name="bx[plugins][admin_edit][rang]">
                  <option value="0">
                    On Top
                  </option>
                                                <xsl:variable name="rang" select="rang"/>
                                                <xsl:for-each select="/bx/plugin/links/links/links">
                                                    <xsl:sort select="rang"/>
                                                    <option value="{rang}">
                                                        <xsl:choose>
                                                            <xsl:when test="rang = $rang">
                                                                <xsl:attribute name="selected">selected</xsl:attribute>
                                                            -- Choose to move --
                                                        </xsl:when>
                                                            <xsl:otherwise>
                                                                <xsl:value-of select="rang"/> - <xsl:value-of select="text"/>
                                                            </xsl:otherwise>
                                                        </xsl:choose>
                                                    </option>
                                                </xsl:for-each>
                                            </select>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="blackH5">Changed </div>
                                        </td>
                                        <td class="blackH5">


                                            <div class="blackH5">
                                                <xsl:value-of select="changed"/>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <br/>
                            <input type="hidden" id="id" name="bx[plugins][admin_edit][id]" value="{id}"/>
                            <xsl:call-template name="buttons"/>

                        </form>&#160;
</xsl:for-each>
                </div>


            </body>
        </html>
    </xsl:template>

    <xsl:template name="buttons">
&#160;<input accesskey="s" type="submit" value="Save Entry"/>&#160;
 <input type="reset" value="Reset"/>&#160; 
 <input type="button" name="_notindb" value="New Entry" onclick="javascript:window.location.href='./?new=1';"/>&#160; 
 <input type="button" name="_notindb" value="Delete Entry" onclick="javascript:document.forms.Master.action += '?delete=1'; document.forms.Master.submit();"/>


    </xsl:template>




</xsl:stylesheet>