<?xml version="1.0"?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns:xhtml="http://www.w3.org/1999/xhtml" 
    xmlns:php="http://php.net/xsl" 
    exclude-result-prefixes="php xhtml"
>

    <xsl:import href="form.xsl"/>
    <xsl:import href="fields.xsl"/>
    <xsl:import href="common.xsl"/>

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
    <xsl:variable name="DBFORMS2_IMG_NULLIMG" select="concat($webroot, 'themes/standard/admin/images/dbforms2/null.gif')"/>
    
    <xsl:template match="/">
        <html>
            <head>
                <link href="{$webroot}themes/admin/css/dbforms2.css" rel="stylesheet" media="screen" type="text/css"/>

                <xsl:call-template name="importJs"><xsl:with-param name="href" select="'webinc/js/sarissa.js'"/></xsl:call-template>

                <xsl:if test="/form/fields//textarea[@type='text_wysiwyg']">
                    <xsl:call-template name="importJs"><xsl:with-param name="href" select="'webinc/fck/fckeditor.js'"/></xsl:call-template>
                    <script type="text/javascript">
                        var fckBasePath	= "<xsl:value-of select="$webroot"/>webinc/fck/"; 
                    </script>
                </xsl:if>

                <xsl:if test="/form/fields//input[@type='date']">
                    <xsl:call-template name="importJs"><xsl:with-param name="href" select="'webinc/js/CalendarPopup.js'"/></xsl:call-template>
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
                    <xsl:call-template name="importJs"><xsl:with-param name="href" select="'webinc/js/colorpicker.js'"/></xsl:call-template>
                </xsl:if>                

                <xsl:call-template name="importJs"><xsl:with-param name="href" select="'webinc/js/bx/tooltip.js'"/></xsl:call-template>
                <xsl:call-template name="importJs"><xsl:with-param name="href" select="'webinc/js/bx/helpers.js'"/></xsl:call-template>
                <xsl:call-template name="importJs"><xsl:with-param name="href" select="'webinc/js/bx/string.js'"/></xsl:call-template>
                
                <xsl:call-template name="importDBF2Js"><xsl:with-param name="href" select="'common.js'"/></xsl:call-template>
                <xsl:call-template name="importDBF2Js"><xsl:with-param name="href" select="'dbforms2.js'"/></xsl:call-template>
                <xsl:call-template name="importDBF2Js"><xsl:with-param name="href" select="'toolbar.js'"/></xsl:call-template>
                <xsl:call-template name="importDBF2Js"><xsl:with-param name="href" select="'log.js'"/></xsl:call-template>
                <xsl:call-template name="importDBF2Js"><xsl:with-param name="href" select="'form.js'"/></xsl:call-template>
                <xsl:call-template name="importDBF2Js"><xsl:with-param name="href" select="'formdata.js'"/></xsl:call-template>
                <xsl:call-template name="importDBF2Js"><xsl:with-param name="href" select="'transport.js'"/></xsl:call-template>
                <xsl:call-template name="importDBF2Js"><xsl:with-param name="href" select="'fields.js'"/></xsl:call-template>
                <xsl:call-template name="importDBF2Js"><xsl:with-param name="href" select="'groups.js'"/></xsl:call-template>
                <xsl:call-template name="importDBF2Js"><xsl:with-param name="href" select="'liveselect.js'"/></xsl:call-template>
                <xsl:call-template name="importDBF2Js"><xsl:with-param name="href" select="'listview.js'"/></xsl:call-template>
                <xsl:call-template name="importDBF2Js"><xsl:with-param name="href" select="'helpers.js'"/></xsl:call-template>

                <xsl:for-each select="$form/script">
                    <script type="text/javascript" src="{$webroot}{@src}">
                        <xsl:text> </xsl:text>
                    </script>
                </xsl:for-each>

                <script type="text/javascript">
                
                    var bx_webroot = "<xsl:value-of select="$webroot"/>";
                    var BX_WEBROOT = bx_webroot;
                    var DBFORMS2_IMG_PREVIEW_SMALL_DIR = BX_WEBROOT + 'dynimages/0,30,scale/';
                    var DBFORMS2_IMG_PREVIEW_LARGE_DIR = BX_WEBROOT + 'dynimages/200/';
                    var DBFORMS2_IMG_NULLIMG = '<xsl:value-of select="$DBFORMS2_IMG_NULLIMG"/>';

                    var _configStack = new Array();
                    var formConfig = new Array();
                    formConfig['fields'] = new Array();
                    <xsl:apply-templates select="/form" mode="jsconfig"/>
                    var dbforms2_formConfig = formConfig;
                    
                </script>

            </head>
            <body onload="dbforms2.init(dbforms2_formConfig); bx_tooltip.init();">
                <div id="controls">
                    <div class="toolbar">
                        <span class="buttons">
                            <input type="button" id="tb_{$formName}_save" accesskey="s" value="save"/>
                            &#160;
                            <input type="button" id="tb_{$formName}_new" accesskey="n" value="new"/>
                            <input type="button" id="tb_{$formName}_saveasnew" value="save as new"/>
                            <input type="button" id="tb_{$formName}_delete" accesskey="d" value="delete"/>
                            <input type="button" id="tb_{$formName}_reload" accesskey="r" value="reload"/>
                        </span>
                        <div style="position:absolute; top:3px; left: 350px">
                            <div class="liveselect">
                                <input type="text" id="chooserQueryField" size="40" accesskey="c"/>
                                <img id="chooserImg" src="{$webroot}themes/admin/images/dbforms2/liveselect_arrowd.gif" border="0"/>
                                <div class="liveselectResultsShadow">
                                    <div class="liveselectResults" id="chooserResults">
                                        <ul><li>Loading...</li></ul>
                                        <div id="chooserPagerDisplay" class="liveselectPager">...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="statusline">
                        <span id="statustext">Initializing...</span>
                    </div>
                </div>

                <div class="mainformcontainer">
                    <xsl:apply-templates select="/form" mode="form"/>
                </div>
                
            </body>
        </html>
    </xsl:template>

    <xsl:template name="importDBF2Js">
        <xsl:param name="href"/>
        <script type="text/javascript" src="{$webroot}webinc/plugins/dbforms2/{$href}">
            <xsl:text> </xsl:text>
        </script>
    </xsl:template>

    <xsl:template name="importJs">
        <xsl:param name="href"/>
        <script type="text/javascript" src="{$webroot}{$href}">
            <xsl:text> </xsl:text>
        </script>
    </xsl:template>
    
    <xsl:template match="group" mode="fields">
        <xsl:apply-templates select="fields/*" mode="xhtml"/>
    </xsl:template>
    
    <xsl:template match="nofield" mode="fields">
        <tr class="formRow">
            <td class="formHeader">
                &#160;
            </td>
            <td class="formInput">
                <strong><xsl:value-of select="@descr"/></strong>
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
