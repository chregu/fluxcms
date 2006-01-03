<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:bxco="http://bitflux.org/config/1.0"
xmlns="http://www.w3.org/1999/xhtml"
xmlns:xhtml="http://www.w3.org/1999/xhtml"
exclude-result-prefixes="bxco"
>
<xsl:output encoding="utf-8" method="xml" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>

    <xsl:param name="screenid" select="0"/>
    <xsl:param name="lang" select="'fr'"/>
    <xsl:param name="requestUri" select="''"/>

    <xsl:variable name="realid">
        <xsl:choose>
            <xsl:when test="$screenid = 0"><xsl:value-of select="/bxco:wizard/bxco:screen/@id" /></xsl:when>
            <xsl:otherwise><xsl:value-of select="$screenid"/></xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    
    <xsl:template match="/">
        <div class="wizard">
        <xsl:for-each select="/bxco:wizard/bxco:screen[@id = $realid]">
            <form name="bx_foo" action="{$requestUri}" method="post">
            <xsl:if test="@enctype">
                <xsl:attribute name="enctype">
                    <xsl:value-of select="@enctype"/>
                </xsl:attribute>
            </xsl:if>
            <input type="hidden" name="nextPage" value="{bxco:nextScreen}"/>
            <input type="hidden" name="thisPage" value="{$realid}"/>
                <xsl:if test="@error and not(@error='')">
                                    </xsl:if>
                <xsl:apply-templates/>
                
                <xsl:if test="bxco:submit">
                    <xsl:call-template name="submit">
                        <xsl:with-param name="submit" select="bxco:submit"/>
                    </xsl:call-template>
                </xsl:if>

            </form>

        </xsl:for-each>
        </div>



<xsl:value-of select="$realid"/>
    </xsl:template>

    <xsl:template match="bxco:section">
        <xsl:apply-templates/>
    </xsl:template>
    
    <xsl:template match="bxco:prevScreen">
    </xsl:template>
    


<xsl:template match="bxco:nextScreen">
    </xsl:template>
   
    <xsl:template name="submit">
        <xsl:param name="submit"/>
        <xsl:choose>
            <xsl:when test="$submit/@type = 'href'">
                <a href="#" onclick="javascript:document.{$submit/@formid}.submit();">
                    <xsl:call-template name="lookup">
                        <xsl:with-param name="ID" select="$submit/@name"/>
                    </xsl:call-template>
                </a>
            </xsl:when>
            <xsl:otherwise>
                <input type="submit">
                    <xsl:attribute name="name">
                        <xsl:value-of select="$submit/@name"/>
                    </xsl:attribute>
                    <xsl:attribute name="value">
                        <xsl:call-template name="lookup" >  
                            <xsl:with-param name="ID" select="$submit/@name"/>
                        </xsl:call-template>
                    </xsl:attribute>
                    <xsl:if test="@cssClass != ''">
                        <xsl:attribute name="class">
                            <xsl:value-of select="@cssClass"/>
                        </xsl:attribute>
                    </xsl:if>
                </input>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <xsl:template match="bxco:group">
        <div class="wizardGroup">
            <div class="wizardGroupTitle">
                <xsl:call-template name="lookup">
                    <xsl:with-param name="ID" select="@name"/>
                </xsl:call-template>
            </div>
            <xsl:apply-templates/>
        </div>
    </xsl:template>

    <xsl:template match="bxco:fields">
       <!-- Check whether screen has an error and juggle display-order of 
            intromsg followed by errormsg. and the rest --> 
        <xsl:choose>
            <xsl:when test="ancestor::*[local-name()='screen']/@error">
                <xsl:apply-templates select="*[@name='intro']"/>
            
                <p class="wizardError">
                    <xsl:call-template name="lookup">
                        <xsl:with-param name="ID" select="ancestor::*[local-name()='screen']/@error"/>
                    </xsl:call-template>
                </p>
            
                <xsl:apply-templates select="*[not(@name='intro')]"/> 
        
        </xsl:when>
        <xsl:otherwise>
            <xsl:apply-templates/>
        </xsl:otherwise>
        </xsl:choose>
    
    
    </xsl:template>
    

    <xsl:template match="bxco:section/bxco:teaser">
        <div class="wizardTeaser">
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID" select="@name"/>
            </xsl:call-template>
        </div>
    </xsl:template>
    <xsl:template match="bxco:fields//bxco:field[@type='formerrors']">
        
        <xsl:if test="count(//bxco:fields//bxco:field[@error]) > 0">
                <p class="wizardError">
                    <xsl:call-template name="lookup">
                        <xsl:with-param name="ID" select="@name"/>
                    </xsl:call-template>
                </p>
        </xsl:if>
    </xsl:template>
    
    
    <xsl:template match="bxco:fields//bxco:field[@type='text']">
        <p>  <xsl:if test="@error">
        <div class="wizardError">
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID" select="@error"/>
            </xsl:call-template>
            </div>
            </xsl:if>
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID">
                    <xsl:value-of select="@name"/>
                </xsl:with-param>
            </xsl:call-template>
            <input type="text" name="bx_fw[{@name}]" value="{@value}"/>
        </p>
    </xsl:template>
    
    <xsl:template name="file">
            <input type="file" name="bx_fw[{@name}]"/>
    </xsl:template>
    
    <xsl:template match="bxco:fields//bxco:field[@type='hidden']">
            <input type="hidden" name="bx_fw[{@name}]" value="{@value}"/>
    </xsl:template>
    
    <xsl:template match="bxco:fields//bxco:field[@type='requestVar']">
            <input type="hidden" name="bx_fw[{@name}]" value="{@value}"/>
    </xsl:template>
    
    <xsl:template match="bxco:fields//bxco:group[@type='table']">
           <div class="wizardGroup">
            <div class="wizardGroupTitle">
                <xsl:call-template name="lookup">
                    <xsl:with-param name="ID" select="@name"/>
                </xsl:call-template>
            </div>
          
        </div>
        <table width="{@width}" border="0" cellspacing="0" cellpadding="0">
        <xsl:if test="@cssClass != ''">
            <xsl:attribute name="class"><xsl:value-of select="@cssClass"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@align != ''">
            <xsl:attribute name="align"><xsl:value-of select="@align"/></xsl:attribute>
        </xsl:if>
        <xsl:apply-templates/>
        </table>
    </xsl:template>
    
    <xsl:template match="bxco:fields//bxco:group[@type='table']//bxco:field[@type='text']">
       <tr><td valign="top" width="40%">
            <xsl:call-template name="fieldTitleAttributes"><xsl:with-param name="fieldNode" select="."/></xsl:call-template>
            <xsl:call-template name="genericFieldTitle"><xsl:with-param name="fieldNode" select="."/></xsl:call-template></td>
            <td valign="middle"><xsl:if test="@tdCssClass"><xsl:attribute name="class"><xsl:value-of select="@tdCssClass"/></xsl:attribute></xsl:if> <input type="text" name="bx_fw[{@name}]" value="{@value}" class="textfield"><xsl:call-template name="fieldAttributes"/></input></td></tr>
    </xsl:template>

    <xsl:template name="textarea">
        <xsl:variable name="cols">
            <xsl:choose>
                <xsl:when test="@cols != ''"><xsl:value-of select="@cols"/></xsl:when>
                <xsl:otherwise>40</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:variable name="rows">
            <xsl:choose>
                <xsl:when test="@rows != ''"><xsl:value-of select="@rows"/></xsl:when>
                <xsl:otherwise>8</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <textarea name="bx_fw[{@name}]" cols="{$cols}" rows="{$rows}"><xsl:if test="@cssClass != ''">
            <xsl:attribute name="class"><xsl:value-of select="@cssClass"/></xsl:attribute>
        </xsl:if><xsl:value-of select="@value" /></textarea>
    </xsl:template>
    
    <xsl:template match="bxco:fields//bxco:field[@type='longtext']">
        <p><xsl:if test="@error">
        <div class="wizardError">
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID" select="@error"/>
            </xsl:call-template>
            </div>
            </xsl:if>
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID">
                    <xsl:value-of select="@name"/>
                </xsl:with-param>
            </xsl:call-template><br/>
            <xsl:value-of select="@cssClass"/>
            <textarea name="bx_fw[{@name}]" cols="40" rows="8">
            </textarea>
        </p>
    </xsl:template>
   
    <xsl:template match="bxco:fields//bxco:field[@type='space']">
        <tr>
            <td colspan="2" valign="top">&#160;</td>
        </tr>
    </xsl:template>
    
    <xsl:template match="bxco:fields//bxco:group[@type='table']//bxco:field[@type='longtext']">
       <tr><td valign="top">
            <xsl:call-template name="fieldTitleAttributes"><xsl:with-param name="fieldNode" select="."/></xsl:call-template>
            <xsl:call-template name="genericFieldTitle"><xsl:with-param name="fieldNode" select="."/></xsl:call-template></td>
            <td valign="top">
                <xsl:call-template name="textarea"/>
            </td></tr>
    </xsl:template>

    <xsl:template match="bxco:fields//bxco:field[@type='checkbox']">
        <p>
            <input type="checkbox" name="bx_fw[{@name}]"  value="1">
            <xsl:if test="@value=1">
            <xsl:attribute name="checked">checked</xsl:attribute>
            </xsl:if>
            <xsl:if test="@cssClass">
            <xsl:attribute name="class"><xsl:value-of select="@cssClass"/></xsl:attribute>
            </xsl:if>
            </input>
            
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID" select="@name"/>
            </xsl:call-template>
            
            <if test="@subtitle">
               <div class="wizardKlein">
                    <xsl:call-template name="lookup">
                    <xsl:with-param name="ID" select="@subtitle"/>
                </xsl:call-template>
                </div>
            </if>
            <xsl:if test="@error">
            <div class="wizardError">
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID" select="@error"/>
            </xsl:call-template>
            </div>
            </xsl:if>
        </p>
    </xsl:template>
    
    <xsl:template match="bxco:fields//bxco:group[@type='table']//bxco:field[@type='checkbox']">
       <tr><td valign="top"  colspan="2">
            <xsl:call-template name="fieldTitleAttributes"><xsl:with-param name="fieldNode" select="."/></xsl:call-template>
            <input type="checkbox" name="bx_fw[{@name}]" value="1">
            <xsl:if test="@value=1">
            <xsl:attribute name="checked">checked</xsl:attribute>
            </xsl:if>
            <xsl:if test="@cssClass">
            <xsl:attribute name="class"><xsl:value-of select="@cssClass"/></xsl:attribute>
            </xsl:if>
            </input><xsl:text> </xsl:text><xsl:call-template name="genericFieldTitle"><xsl:with-param name="fieldNode" select="."/></xsl:call-template></td></tr>
    </xsl:template>
    
    <xsl:template match="bxco:fields//bxco:group[@type='table']//bxco:field[@type='checkbox' and @columns='2']" priority="10">
       <tr><td valign="top" align="right">
            <xsl:call-template name="fieldTitleAttributes"><xsl:with-param name="fieldNode" select="."/></xsl:call-template>
            <input type="checkbox" name="bx_fw[{@name}]" value="1">
            <xsl:if test="@value=1">
            <xsl:attribute name="checked">checked</xsl:attribute>
            </xsl:if>
            </input></td><td><xsl:text> </xsl:text><xsl:call-template name="genericFieldTitle"><xsl:with-param name="fieldNode" select="."/></xsl:call-template></td></tr>
    </xsl:template>
    
     <xsl:template match="bxco:fields//bxco:field[@type='checkboxtext']">
        <p>
            <input type="checkbox" name="bx_fw[{@name}]" value="1">
            <xsl:if test="@value=1">
            <xsl:attribute name="checked">checked</xsl:attribute>
            </xsl:if>
            </input>
            
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID" select="@name"/>
            </xsl:call-template>&#160;
            <input type="text" name="bx_fw[{@name}_text]" />
             <if test="@subtitle">
               <div class="wizardKlein">
                    <xsl:call-template name="lookup">
                    <xsl:with-param name="ID" select="@subtitle"/>
                </xsl:call-template>
                </div>
            </if>
            
            <xsl:if test="@error">
            <div class="wizardError">
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID" select="@error"/>
            </xsl:call-template>
            </div>
            </xsl:if>
        </p>
    </xsl:template>
    
    <xsl:template match="bxco:field[@type='msg']">
        <p>
          <xsl:if test="@blockCssClass != ''">
          <xsl:attribute name="class">
            <xsl:value-of select="@blockCssClass"/>
        </xsl:attribute>
        </xsl:if>
            <span>
            <xsl:call-template name="fieldTitleAttributes"><xsl:with-param name="fieldNode" select="."/></xsl:call-template>
            <xsl:call-template name="genericFieldTitle"><xsl:with-param name="fieldNode" select="."/></xsl:call-template>
            </span><br/>
            <xsl:call-template name="lookup">
            <xsl:with-param name="ID" select="@msg"/>
            </xsl:call-template>
        </p>
    </xsl:template>

    <xsl:template name="radio">
        <xsl:param name="fieldNode"/>
            <xsl:for-each select="$fieldNode/bxco:option">

                <input type="radio" name="bx_fw[{../@name}]" value="{@value}">
                <xsl:if test="../@value = @value">
                <xsl:attribute name="checked">checked</xsl:attribute>
                </xsl:if>
                </input>
                
                <xsl:choose>
                    <xsl:when test="@cssClass != ''">
                        <span class="{@cssClass}">
                            <xsl:call-template name="lookup">
                                <xsl:with-param name="ID" select="@name"/>
                            </xsl:call-template>
                        </span>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:call-template name="lookup">
                            <xsl:with-param name="ID" select="@name"/>
                        </xsl:call-template>
                    </xsl:otherwise>
                </xsl:choose>
                
                <xsl:if test="@type = 'text' and not(@valueBelowSubtitle != '')">
                    <xsl:call-template name="radioText"/>
                </xsl:if>

                <xsl:if test="../@orientation != 'horizontal'">
                    <br/>
                </xsl:if>

                <xsl:if test="@subtitle">
                <div class="wizardKlein">
                    <xsl:call-template name="lookup">
                    <xsl:with-param name="ID" select="@subtitle"/>
                </xsl:call-template>
                <xsl:if test="not( @valueBelowSubtitle != '')"><br/><br/></xsl:if>
                </div>
                </xsl:if>

                <xsl:if test="@type = 'text' and @valueBelowSubtitle != ''">
                    <div class="wizardKlein">
                    <xsl:call-template name="radioText"/>
                    </div>
                </xsl:if>

                
            </xsl:for-each>
    </xsl:template>
    
    <xsl:template name="radioText">
        <xsl:call-template name="lookup">
            <xsl:with-param name="ID" select="@textHeader"/>
        </xsl:call-template>
        <xsl:if test="@textHeader != ''"><xsl:text> </xsl:text></xsl:if>
        <input type="text" name="bx_fw[{@name}_text]" size="{@size}" maxlength="{@length}">
        <xsl:attribute name="value">
            <xsl:choose>
                <xsl:when test="@textvalue"><xsl:value-of select="@textvalue"/></xsl:when>
                <xsl:otherwise><xsl:value-of select="@value"/></xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
        <xsl:if test="@size != ''">
            <xsl:attribute name="size"><xsl:value-of select="@size"/></xsl:attribute>
        </xsl:if>
        </input><xsl:text> </xsl:text>
        <xsl:call-template name="lookup">
            <xsl:with-param name="ID" select="@textComment"/>
        </xsl:call-template>
    </xsl:template>
    
    <xsl:template match="bxco:fields//bxco:field[@type='radio']">
        <div class="wizardGroup">
          <xsl:if test="@error">
           
            </xsl:if>
            <div class="wizardGroupTitle">
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID" select="@name"/>
            </xsl:call-template>
            </div>
            
            <div class="wizardError">
            <xsl:call-template name="lookup">
               <xsl:with-param name="ID" select="@error"/>
            </xsl:call-template>
            </div>
            <xsl:call-template name="radio">
                <xsl:with-param name="fieldNode" select="."/>
            </xsl:call-template>
        </div>
    </xsl:template>

    <xsl:template match="bxco:fields//bxco:group[@type='table']//bxco:field[@type='radio']">
        <tr>
            <td valign="top">
            <xsl:call-template name="fieldTitleAttributes"><xsl:with-param name="fieldNode" select="."/></xsl:call-template>
            <xsl:call-template name="genericFieldTitle"><xsl:with-param name="fieldNode" select="."/></xsl:call-template></td>
            <td>
                <xsl:call-template name="radio">
                    <xsl:with-param name="fieldNode" select="."/>
                </xsl:call-template>
            </td>
        </tr>
    </xsl:template>


    <xsl:template name="select">
        <xsl:param name="fieldNode"/>
        <select name="bx_fw[{$fieldNode/@name}]" size="{$fieldNode/@size}">
            <xsl:if test="$fieldNode/@multiple != ''">
                <xsl:attribute name="multiple" value="yes"/>
            </xsl:if>
            <xsl:if test="$fieldNode/@cssClass != ''">
                <xsl:attribute name="class"><xsl:value-of select="@cssClass"/></xsl:attribute>
            </xsl:if>
            <xsl:for-each select="$fieldNode/bxco:option">
                <option value="{@value}">
                    <xsl:if test="../@value = @value">
                        <xsl:attribute name="selected" value="yes"/>
                    </xsl:if>
                    <xsl:call-template name="lookup">
                        <xsl:with-param name="ID" select="@name"/>
                    </xsl:call-template>
                </option>
            </xsl:for-each>
        </select>
    </xsl:template>
    
    <xsl:template match="bxco:fields//bxco:field[@type='select']">
        <p>
            <xsl:if test="@error">
                <div class="wizardError">
                    <xsl:call-template name="lookup">
                        <xsl:with-param name="ID" select="@error"/>
                    </xsl:call-template>
                </div>
            </xsl:if>
            <xsl:call-template name="select">
                <xsl:with-param name="fieldNode" select="."/>
            </xsl:call-template>
            <xsl:call-template name="lookup">
                <xsl:with-param name="ID"><xsl:value-of select="@name"/></xsl:with-param>
            </xsl:call-template>
        </p>
    </xsl:template>

    <xsl:template match="bxco:fields//bxco:group[@type='table']//bxco:field[@type='select']">
        <tr>
            <td class="wizardText"><xsl:call-template name="genericFieldTitle"><xsl:with-param name="fieldNode" select="."/></xsl:call-template></td>
            <td>
                <xsl:call-template name="select">
                    <xsl:with-param name="fieldNode" select="."/>
                </xsl:call-template>
            </td>
        </tr>
    </xsl:template>
    
    <xsl:template match="bxco:fields//bxco:group[@type='table']//bxco:field[@type='file']">
        <tr>
            <td class="wizardText"><xsl:call-template name="genericFieldTitle"><xsl:with-param name="fieldNode" select="."/></xsl:call-template></td>
            <td>
                <xsl:call-template name="file">
                    <xsl:with-param name="fieldNode" select="."/>
                </xsl:call-template>
            </td>
        </tr>
    </xsl:template>
    
    <xsl:template match="bxco:fields//bxco:field[@type='submit']">
    
      <p>
      <xsl:call-template name="fieldTitleAttributes"><xsl:with-param name="fieldNode" select="."/></xsl:call-template><br/>
<xsl:call-template name="submit"><xsl:with-param name="submit" select="."/></xsl:call-template>
      </p>
    </xsl:template>
        
    <xsl:template match="bxco:fields//bxco:group[@type='table']//bxco:field[@type='submit']">
       <tr><td valign="top">
            <xsl:call-template name="fieldTitleAttributes"><xsl:with-param name="fieldNode" select="."/></xsl:call-template>
            </td>
            <td valign="top"><br/><xsl:call-template name="submit"><xsl:with-param name="submit" select="."/></xsl:call-template></td></tr>
    </xsl:template>

    
    <xsl:template name="lookup">
        <xsl:param name="ID"/>
        
        <xsl:choose>
            <xsl:when test="../@nolookup = 'yes'">
                <xsl:value-of select="$ID"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:variable name="entry" select="/bxco:wizard/bxco:lang/bxco:entry[@ID=$ID]"/>
                <xsl:choose>
                    <xsl:when test="$entry/bxco:text[@lang = $lang]">
                        <xsl:apply-templates  mode="lookup" select="$entry/bxco:text[@lang = $lang]/*|$entry/bxco:text[@lang=$lang]/text()"/>
                    </xsl:when>
                    <xsl:otherwise>
                         <xsl:apply-templates mode="lookup" select="$entry/bxco:text[@lang = 'de']/*|$entry/bxco:text[@lang = 'de']/text()"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <xsl:template match="*" mode="lookup">
        <xsl:element name="{local-name()}">
        <xsl:copy-of select="@*"/>
        <xsl:apply-templates/>
        </xsl:element>
    </xsl:template>
    
 
    
    <xsl:template name="genericFieldTitle">
        <xsl:param name="fieldNode"/>
        <xsl:choose>
            <xsl:when test="$fieldNode/@error">
                <span class="wizardError">
                    <xsl:call-template name="lookup">
                        <xsl:with-param name="ID"><xsl:value-of select="$fieldNode/@name"/></xsl:with-param>
                    </xsl:call-template>
        <xsl:if test="$fieldNode/@required != ''"><xsl:value-of select="'*'"/></xsl:if>
                </span>
            </xsl:when>
            <xsl:otherwise>
                <xsl:call-template name="lookup">
                    <xsl:with-param name="ID"><xsl:value-of select="$fieldNode/@name"/></xsl:with-param>
                </xsl:call-template>
        <xsl:if test="$fieldNode/@required != ''"><xsl:value-of select="'*'"/></xsl:if>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <xsl:template name="fieldTitleAttributes">
        <xsl:param name="fieldNode"/>
        
        <xsl:if test="$fieldNode/@titleCssClass != ''">
        <xsl:attribute name="class">
            <xsl:choose>
                <xsl:when test="$fieldNode/@titleCssClass != ''"><xsl:value-of select="$fieldNode/@titleCssClass"/></xsl:when>
                <xsl:otherwise><xsl:value-of select="'wizardText'"/></xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
        </xsl:if>
        <xsl:choose>
<xsl:when test="@titleWidth != ''">
            <xsl:attribute name="width">
                <xsl:value-of select="@titleWidth"/>
            </xsl:attribute>

</xsl:when>
        <xsl:when test="../@titleWidth != ''">
            <xsl:attribute name="width">
                <xsl:value-of select="../@titleWidth"/>
            </xsl:attribute>
        </xsl:when>
</xsl:choose>        
    </xsl:template>

    <xsl:template name="fieldAttributes">
        <xsl:if test="@cssClass != ''">
            <xsl:attribute name="class"><xsl:value-of select="@cssClass"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@size != ''">
            <xsl:attribute name="size"><xsl:value-of select="@size"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@style != ''">
            <xsl:attribute name="style"><xsl:value-of select="@style"/></xsl:attribute>
        </xsl:if>
    </xsl:template>
    
</xsl:stylesheet>

