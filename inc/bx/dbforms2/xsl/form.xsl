<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:php="http://php.net/xsl" exclude-result-prefixes="php xhtml">
<xsl:output encoding="utf-8" method="xml"
   doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
   doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"
/>

    <xsl:param name="webroot" value="'/'"/>
    <xsl:param name="tablePrefix" value="''"/>

    <xsl:variable name="form" select="/form"/>
    <xsl:variable name="formName" select="$form/@name"/>

    <xsl:variable name="dataURI" select="concat($webroot,'admin/dbforms2/',$formName,'/data')"/>
    <xsl:variable name="chooserDataURI" select="concat($webroot,'admin/dbforms2/',$formName,'/chooser')"/>
    <xsl:variable name="liveSelectRootURI" select="concat($webroot,'admin/dbforms2/',$formName,'/liveselect')"/>

    <xsl:attribute-set name="standardInputElement">
        <xsl:attribute name="onfocus">if(dbforms2_globalObj[this.id]) dbforms2_globalObj[this.id].e_onFocus();</xsl:attribute>
        <xsl:attribute name="onblur">if(dbforms2_globalObj[this.id]) dbforms2_globalObj[this.id].e_onBlur();</xsl:attribute>
        <xsl:attribute name="onmouseover">if(dbforms2_globalObj[this.id]) dbforms2_globalObj[this.id].e_onMouseOver();</xsl:attribute>
        <xsl:attribute name="onmouseout">if(dbforms2_globalObj[this.id]) dbforms2_globalObj[this.id].e_onMouseOut();</xsl:attribute>

        <xsl:attribute name="class"></xsl:attribute>
    </xsl:attribute-set>
    
    <xsl:template name="genJSConfig">
        <xsl:param name="field"/>
        var field = new Array();
        field['type'] ='<xsl:value-of select="@fieldType"/>';
        field['default'] ='<xsl:value-of select="php:functionString('addslashes', default)"/>';
    </xsl:template>

    <xsl:template match="/">
        <html>
            <head>
                <link href="{$webroot}themes/admin/css/dbforms2.css" rel="stylesheet" media="screen" type="text/css"/>

                <script type="text/javascript" src="{$webroot}/webinc/js/sarissa.js">
                    <xsl:text> </xsl:text>
                </script>

                <xsl:if test="/form/fields//textarea[@type='text_wysiwyg']">
                    <script type="text/javascript" src="{$webroot}webinc/fck/fckeditor.js">
                        <xsl:text> </xsl:text>
                    </script>
                    <script type="text/javascript">
                        var fckBasePath	= "<xsl:value-of select="$webroot"/>webinc/fck/"; 
                    </script>
                </xsl:if>

                <xsl:if test="/form/fields//input[@type='date']">
                    <script type="text/javascript" src="{$webroot}webinc/js/CalendarPopup.js">
                        <xsl:text> </xsl:text>
                    </script>
                    <script type="text/javascript">
                        document.write(getCalendarStyles());
                        var cal = new CalendarPopup('caldiv');
                        cal.showYearNavigation();
                    </script>
                </xsl:if>
                
                <xsl:if test="/form/fields//input[@type='color']">
                    <script type="text/javascript">
                        colorPicker_spacerImage = "<xsl:value-of select="$webroot"/>webinc/js/spacer.gif";
                    </script>
                    <script type="text/javascript" src="{$webroot}webinc/js/colorpicker.js">
                        <xsl:text> </xsl:text>
                    </script>
                </xsl:if>                

                <script type="text/javascript" src="{$webroot}webinc/js/bx/helpers.js">
                    <xsl:text> </xsl:text>
                </script>
                <script type="text/javascript" src="{$webroot}webinc/plugins/dbforms2/common.js">
                    <xsl:text> </xsl:text>
                </script>
                <script type="text/javascript" src="{$webroot}webinc/plugins/dbforms2/dbforms2.js">
                    <xsl:text> </xsl:text>
                </script>
                <script type="text/javascript" src="{$webroot}webinc/plugins/dbforms2/toolbar.js">
                    <xsl:text> </xsl:text>
                </script>
                <script type="text/javascript" src="{$webroot}webinc/plugins/dbforms2/log.js">
                    <xsl:text> </xsl:text>
                </script>
                <script type="text/javascript" src="{$webroot}webinc/plugins/dbforms2/form.js">
                    <xsl:text> </xsl:text>
                </script>
                <script type="text/javascript" src="{$webroot}webinc/plugins/dbforms2/formdata.js">
                    <xsl:text> </xsl:text>
                </script>
                <script type="text/javascript" src="{$webroot}webinc/plugins/dbforms2/transport.js">
                    <xsl:text> </xsl:text>
                </script>
                <script type="text/javascript" src="{$webroot}webinc/plugins/dbforms2/fields.js">
                    <xsl:text> </xsl:text>
                </script>
                <script type="text/javascript" src="{$webroot}webinc/plugins/dbforms2/groups.js">
                    <xsl:text> </xsl:text>
                </script>
                <script type="text/javascript" src="{$webroot}webinc/plugins/dbforms2/liveselect.js">
                    <xsl:text> </xsl:text>
                </script>
                <script type="text/javascript" src="{$webroot}webinc/plugins/dbforms2/helpers.js">
                    <xsl:text> </xsl:text>
                </script>

                <xsl:for-each select="$form/script">
                    <script type="text/javascript" src="{$webroot}{@src}">
                        <xsl:text> </xsl:text>
                    </script>
                </xsl:for-each>

                <script type="text/javascript">
                
                    var bx_webroot = "<xsl:value-of select="$webroot"/>"; 
                    var dbforms2_formConfig = new Array();
                    dbforms2_formConfig['fields'] = new Array();

                    <xsl:for-each select="$form/fields/*">
                        <xsl:choose>
                            <xsl:when test="local-name() = 'group'">
                                var group = new Array();
                                group['fields'] = new Array();
                                group['isGroup'] = true;
                                group['type'] = '<xsl:value-of select="@type"/>';
                                <xsl:for-each select="fields/*[local-name() != 'nofield']">
                                    <xsl:call-template name="genJSConfig">
                                        <xsl:with-param name="field" select="."/>
                                    </xsl:call-template>
                                    group['fields']['<xsl:value-of select="@name"/>'] = field;
                                </xsl:for-each>
                                dbforms2_formConfig['fields']['<xsl:value-of select="@name"/>'] = group;
                            </xsl:when>
                            <xsl:when test="local-name() = 'nofield'">
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:call-template name="genJSConfig">
                                    <xsl:with-param name="field" select="."/>
                                </xsl:call-template>
                                dbforms2_formConfig['fields']['<xsl:value-of select="@name"/>'] = field;
                            </xsl:otherwise>
                        </xsl:choose>
                    </xsl:for-each>
                    
                    dbforms2_formConfig['name']             = '<xsl:value-of select="$formName"/>';
                    dbforms2_formConfig['dataURI']          = '<xsl:value-of select="$dataURI"/>';
                    dbforms2_formConfig['chooserDataURI']   = '<xsl:value-of select="$chooserDataURI"/>';
                    dbforms2_formConfig['liveSelectRootURI']   = '<xsl:value-of select="$liveSelectRootURI"/>';
                    dbforms2_formConfig['tablePrefix']      = '<xsl:value-of select="$tablePrefix"/>';

                    dbforms2_formConfig['onSaveJS']         = '<xsl:value-of select="php:functionString('addslashes', $form/@onsavejs)"/>';
                    dbforms2_formConfig['onLoadJS']         = '<xsl:value-of select="php:functionString('addslashes', $form/@onload)"/>';
                    
                </script>

            </head>
            <body onload="dbforms2.init(dbforms2_formConfig);">
                <div id="controls">
                    <div id="toolbar">
                        <span id="buttons">
                            <input type="button" name="button_save" value="save" accesskey="s" onclick="dbforms2.form.saveFormData()"/>
                            &#160;
                            <input type="button" name="button_new" value="new" accesskey="n" onclick="dbforms2.form.createNewEntry()"/>
                            <input type="button" name="button_saveasnew" value="save as new" onclick="dbforms2.form.saveFormDataAsNew()"/>
                            <input type="button" name="button_delete" value="delete" accesskey="d" onclick="dbforms2.form.deleteEntry();"/>
                            <input type="button" name="button_reload" value="reload" accesskey="r" onclick="dbforms2.form.loadFormDataByID(dbforms2.form.currentID)"/>
                        </span>
                        <div style="position:absolute; top:3px; left: 350px">
                            <div class="liveselect">
                                <input type="text" id="chooserQueryField" size="40" accesskey="c"/>
                                <img id="chooserImg" src="{$webroot}themes/admin/images/dbforms2/liveselect_arrowd.gif" border="0"/>
                                <div class="liveselectResultsShadow">
                                    <div class="liveselectResults" id="chooserResults">
                                        <ul><li>Loading...</li></ul>
                                        <!--<div class="pager">bla</div>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="statusline">
                        <span id="statustext">Initializing...</span>
                    </div>
                </div>

                <div id="form">
                    <form name="bxform">
                        <xsl:apply-templates select="$form/fields/input[@type='hidden']" mode="hidden"/>
                        <table id="maintable" cellpadding="0" cellspacing="0" width="700">
                            <xsl:apply-templates select="$form/fields/*" mode="xhtml"/>
                        </table>
                    </form>
                </div>
                
            </body>
        </html>
    </xsl:template>
    
    <xsl:template match="group" mode="xhtml">
        <xsl:apply-templates select="fields/*" mode="xhtml"/>
    </xsl:template>
    
    <xsl:template match="nofield" mode="xhtml">
        <tr class="formRow">
            <td class="formHeader">
                &#160;
            </td>
            <td class="formInput">
                <strong><xsl:value-of select="@descr"/></strong>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="input" mode="xhtml">
        <tr class="formRow">
            <td class="formHeader">
                <label for="field_{@name}">
                    <xsl:value-of select="@descr"/>
                </label>
            </td>
            <td class="formInput">
                <input xsl:use-attribute-sets="standardInputElement" id="field_{@name}">
                    <xsl:apply-templates select="@*[name() != 'descr' and name() != 'fieldType']" mode="xhtml"/>
                    <xsl:apply-templates mode="xhtml"/>
                </input>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="input[@type='hidden']" mode="xhtml">
        <!-- nix ;) -->
    </xsl:template>

    <xsl:template match="input[@type='hidden']" mode="hidden">
        <input xsl:use-attribute-sets="standardInputElement" id="field_{@name}">
             <xsl:apply-templates select="@*[name() != 'descr' and name() != 'fieldType']" mode="xhtml"/>
             <xsl:apply-templates mode="xhtml"/>
        </input>
    </xsl:template>

    <xsl:template match="textarea" mode="xhtml">
        <tr class="formRow">
            <td class="formHeader">
                <label for="field_{@name}">
                    <xsl:value-of select="@descr"/>
                </label>
            </td>
            <td class="formInput">
                <textarea xsl:use-attribute-sets="standardInputElement" id="field_{@name}" name="field_{@name}">
                    <xsl:apply-templates select="@*[name() != 'descr' and name() != 'fieldType']" mode="xhtml"/>
                    <xsl:apply-templates mode="xhtml"/>
                </textarea>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="remark" mode="xhtml">
        <tr class="formRow">
            <td class="formHeader">
                <label for="field_{@name}">
                    <xsl:value-of select="@descr"/>
                </label>
            </td>
            <td class="formInput">
                <label for="field_{@name}">
                    <xsl:value-of select="@descr"/>
                </label>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="select" mode="xhtml">
        <tr class="formRow">
            <td class="formHeader">
                <label for="field_{@name}">
                    <xsl:value-of select="@descr"/>
                </label>
            </td>
            <td class="formInput">
                <select xsl:use-attribute-sets="standardInputElement" id="field_{@name}">
                    <xsl:apply-templates select="@*[name() != 'descr' and name() != 'fieldType']" mode="xhtml"/>
                    <xsl:apply-templates mode="xhtml"/>
                </select>
            </td>
        </tr>
    </xsl:template>
    

    <xsl:template match="select[@type='relation_n2m']" mode="xhtml">
        <tr class="formRow">
            <td class="formHeader">
                <label for="field_{@name}">
                    <xsl:value-of select="@descr"/>
                </label>
            </td>
            <td class="formInput">
                <div class="liveselectcontainer" style="z-index: {1000-position()} !important;">
                    <div class="liveselect">
                        <input type="text" id="{@name}_lsqueryfield" size="40"/>
                        <div class="liveselectResultsShadow">
                            <div class="liveselectResults" id="{@name}_lsresults">
                                <ul></ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" id="field_{@name}"/>
                <div class="n2mvalues" id="field_{@name}_values"></div>

            </td>
        </tr>
    </xsl:template>

    <xsl:template match="input[@type='date']" mode="xhtml">
        <tr class="formRow">
            <td class="formHeader">
                <label for="field_{@name}">
                    <xsl:value-of select="@descr"/>
                </label>
            </td>
            <td class="formInput">
                <input xsl:use-attribute-sets="standardInputElement" id="field_{@name}">
                    <xsl:apply-templates select="@*[name() != 'descr' and name() != 'fieldType']" mode="xhtml"/>
                    <xsl:apply-templates mode="xhtml"/>
                </input>
                <xsl:text> </xsl:text>
                <input type="button" value="..." onclick="cal.select(document.getElementById('field_{@name}'),'anchor_field_{@name}','dd.MM.yyyy 00:00:00'); return false;" name="anchor_field_{@name}" id="anchor_field_{@name}" />
                <div id="caldiv" style="position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></div>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="input[@type='color']" mode="xhtml">
        <tr class="formRow">
            <td class="formHeader">
                <label for="field_{@name}">
                    <xsl:value-of select="@descr"/>
                </label>
            </td>
            <td class="formInput">
                <input xsl:use-attribute-sets="standardInputElement" id="field_{@name}" onchange="colorPicker_preview('{@name}');">
                    <xsl:apply-templates select="@*[name() != 'descr' and name() != 'fieldType']" mode="xhtml"/>
                    <xsl:apply-templates mode="xhtml"/>
                </input>
                <xsl:text> </xsl:text>
                <input type="button" value="..." onclick="colorPicker_show('{@name}'); return false;" name="anchor_field_{@name}" id="anchor_field_{@name}" style="background-color: #ffffff; color: #000000;"/>
            </td>
        </tr>
    </xsl:template>    

    <xsl:template match="input[@type='upload']" mode="xhtml">
        <tr class="formRow">
            <td class="formHeader">
                <label for="field_{@name}">
                    <xsl:value-of select="@descr"/>
                </label>
            </td>
            <td class="formInput">
                <input xsl:use-attribute-sets="standardInputElement" disabled="true" id="field_{@name}">
                    <xsl:apply-templates select="@*[name() != 'descr' and name() != 'fieldType']" mode="xhtml"/>
                    <xsl:apply-templates mode="xhtml"/>
                </input>
                <xsl:text> </xsl:text>

                <input type="button" onclick="openUploadIframe('{@name}')" value="..."/>
                <iframe id="field_{@name}_iframe" width="400" height="50" style="display: none"></iframe>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="input[@type='file_browser']" mode="xhtml">
        <tr class="formRow">
            <td class="formHeader">
                <label for="field_{@name}">
                    <xsl:value-of select="@descr"/>
                </label>
            </td>
            <td class="formInput">
                <input xsl:use-attribute-sets="standardInputElement" disabled="true" id="field_{@name}">
                    <xsl:apply-templates select="@*[name() != 'descr' and name() != 'fieldType']" mode="xhtml"/>
                    <xsl:apply-templates mode="xhtml"/>
                </input>
                <xsl:text> </xsl:text>

                <input type="button" onclick="dbforms2_common.openFileBrowser('{@name}')" value="..."/>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="default|script" mode="xhtml"></xsl:template>

    <xsl:template match="*" mode="xhtml">
        <xsl:element name="{local-name()}">
            <xsl:apply-templates select="@*" mode="xhtml"/>
            <xsl:apply-templates mode="xhtml"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="@*" mode="xhtml">
        <xsl:copy-of select="."/>
    </xsl:template>

</xsl:stylesheet>
