<?xml version="1.0"?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns:xhtml="http://www.w3.org/1999/xhtml" 
    xmlns:php="http://php.net/xsl" 
    exclude-result-prefixes="php xhtml"
>

    <!--
        explanation of the different modes used here:
            
        'form':     is used to display a form and generate xhtml
        'fields':   is used to display all fields
        'jsconfig': is used to generate the javascript configuration of a form
    -->

    <xsl:template match="form" mode="form">
        <div id="formdiv_{@name}" class="form">
        
            <xsl:if test="@title != ''">
                <xsl:choose>
                    <xsl:when test="local-name(parent::node()) = 'fields'"><h3><a id="_form_{@name}_toggleLink" onclick="dbforms2_helpers.toggleSubForm(this, '_form_{@name}');">-</a><xsl:value-of select="@title"/></h3></xsl:when>
                    <xsl:otherwise><h1><xsl:value-of select="@title"/></h1></xsl:otherwise>
                </xsl:choose>
            </xsl:if>
            
            <!-- display the relation controls in the form header so they don't collapse -->
            <xsl:if test="fields/relation[@type='relation_n21']">
                <xsl:call-template name="doLiveSelect">
                    <xsl:with-param name="ls" select="fields/relation[@type='relation_n21'][1]"/>
                </xsl:call-template>
            </xsl:if>
            <xsl:if test="fields/listview[@type='listview_12n']">
                <xsl:call-template name="doListview">
                    <xsl:with-param name="lv" select="fields/listview[@type='listview_12n'][1]"/>
                </xsl:call-template>
            </xsl:if>
            
            <div id="_form_{@name}" style="display:block;">
                <form name="dbforms2_{@name}">
                    <!-- apply all hidden fields -->
                    <xsl:apply-templates select="fields/input[@type='hidden']" mode="hidden"/>
    
                    <!-- generate a toolbar when this is a subform -->
                    <xsl:if test="local-name(parent::node()) = 'fields'">
                        <div class="toolbar">
                            <span class="buttons">
                                <input type="button" id="tb_{@name}_save" value="save"/>
                                &#160;
                                <input type="button" id="tb_{@name}_new" value="new"/>
                                <input type="button" id="tb_{@name}_saveasnew" value="save as new"/>
                                <input type="button" id="tb_{@name}_delete" value="delete"/>
                                <input type="button" id="tb_{@name}_reload" value="reload"/>
                                
                            </span>
                        </div>
                    </xsl:if>
                    
                    <!-- apply all other fields -->
                    <table cellpadding="0" cellspacing="0">
                        <xsl:attribute name="width">
                            <xsl:choose>
                                <xsl:when test="local-name(parent::node()) = 'fields'">100%</xsl:when>
                                <xsl:otherwise>700</xsl:otherwise>
                            </xsl:choose>
                        </xsl:attribute>
                        <xsl:apply-templates select="*" mode="fields"/>
                    </table>
                
                </form>
            </div>
        </div>
    </xsl:template>
    
    <xsl:template match="form" mode="fields">
        <tr class="formRow">
            <td colspan="2">
                <div class="subformcontainer">
                    <xsl:apply-templates select="." mode="form"/>
                </div>
            </td>
        </tr>
    </xsl:template>
    
    <xsl:template match="form" mode="jsconfig">
    
        <xsl:variable name="baseURI">
            <xsl:choose>
                <xsl:when test="local-name(parent::node()) = 'fields'"><xsl:value-of select="concat($webroot,'admin/dbforms2/',../../@name,'/subform/',@name)"/></xsl:when>
                <xsl:otherwise><xsl:value-of select="concat($webroot,'admin/dbforms2/',@name)"/></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
        formConfig['name']                  = '<xsl:value-of select="@name"/>';
        formConfig['dataURI']               = '<xsl:value-of select="concat($baseURI,'/data')"/>';
        formConfig['chooserDataURI']        = '<xsl:value-of select="concat($baseURI,'/chooser')"/>';
        formConfig['liveSelectRootURI']     = '<xsl:value-of select="concat($baseURI,'/liveselect')"/>';
        formConfig['listViewRootURI']       = '<xsl:value-of select="concat($baseURI,'/listview')"/>';
        formConfig['tablePrefix']           = '<xsl:value-of select="$tablePrefix"/>';

        formConfig['onSaveJS']              = '<xsl:value-of select="php:functionString('addslashes', @onsavejs)"/>';
        formConfig['onLoadJS']              = '<xsl:value-of select="php:functionString('addslashes', @onloadjs)"/>';
        
        formConfig['thisidfield']           = '<xsl:value-of select="php:functionString('addslashes', @thisidfield)"/>';
        formConfig['thatidfield']           = '<xsl:value-of select="php:functionString('addslashes', @thatidfield)"/>';
        
        <xsl:for-each select="fields/*">
            <xsl:choose>
            
                <xsl:when test="local-name() = 'group'">
                    var group = new Array();
                    group['fields'] = new Array();
                    group['isGroup'] = true;
                    group['type'] = '<xsl:value-of select="@type"/>';
                    <xsl:for-each select="fields/*[local-name() != 'nofield']">
                        <xsl:apply-templates select="." mode="jsconfig"/>
                        group['fields']['<xsl:value-of select="@name"/>'] = field;
                    </xsl:for-each>
                    formConfig['fields']['<xsl:value-of select="@name"/>'] = group;
                </xsl:when>
                
                <xsl:when test="local-name() = 'nofield'">
                </xsl:when>
                
                <xsl:when test="local-name() = 'form'">
                    // save current form to the stack
                    _configStack.push(formConfig);
                    var formConfig = new Array();
                    formConfig['fields'] = new Array();

                    <xsl:apply-templates select="." mode="jsconfig"/>

                    // pop the old form back and save the child form 
                    var form = new Array();
                    form['type'] = 'form';
                    form['isForm'] = true;
                    form['config'] = formConfig;
                    
                    formConfig = _configStack.pop();
                    formConfig['fields']['<xsl:value-of select="@name"/>'] = form;
                    
                </xsl:when>
                
                <xsl:otherwise>
                    var field = new Array();
                    field['type'] ='<xsl:value-of select="@type"/>';
                    field['default'] ='<xsl:value-of select="php:functionString('addslashes', default)"/>';
                    formConfig['fields']['<xsl:value-of select="@name"/>'] = field;
                </xsl:otherwise>
            </xsl:choose>
        </xsl:for-each>
        
    </xsl:template>
    

</xsl:stylesheet>
