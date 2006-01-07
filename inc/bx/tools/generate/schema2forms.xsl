<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" 
  xmlns:dbform="http://bitflux.org/dbforms2/1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform"  xmlns:exsl="http://exslt.org/common" extension-element-prefixes="exsl">

    <xsl:output encoding="utf-8" method="xml"/>
<xsl:param name="table" select="'bxcms_users'"/>
<xsl:param name="alias" select="'users'"/>
    <xsl:template match="/">
        <xsl:for-each select="/database/table[name/text() = $table]">

            <dbform:form 
  
>
                    <dbform:fields table="{$alias}" >

                        <xsl:apply-templates select="declaration/field"/>

                    </dbform:fields>

                     <dbform:chooser namefield="concat({declaration/field[type = 'text'][1]/name},' (',{declaration/field[name='id' or name ='ID'][1]/name},')')" wherefields="{declaration/field[type = 'text'][1]/name}, {declaration/field[type = 'text'][2]/name}" orderby="{declaration/field[type = 'text'][1]/name}" limit="20"  />
                    
              
            
</dbform:form>
        </xsl:for-each>

    </xsl:template>
    
    <xsl:template match="field[type='text']">
    
    <dbform:field name="{name}" type="text" descr="{name}"><xsl:call-template name="defaultValue"/></dbform:field>
    
    </xsl:template>
   
      <xsl:template match="field[type='text' and not(length)]">
    
    <dbform:field name="{name}" type="text_area" descr="{name}"><xsl:call-template name="defaultValue"/></dbform:field>
    
    </xsl:template>
   
    <xsl:template match="field[name='ID' or name='id']">
     <!-- <dbform:field name="{name}" type="fixed" descr="{name}"><xsl:call-template name="defaultValue"/></dbform:field>-->
    </xsl:template>
     

    
    

    
    
    
     <xsl:template match="field[contains(.,'fileref')]" priority="100">
    
    <dbform:field name="{name}" type="file"  descr="{name}"><xsl:call-template name="defaultValue"/></dbform:field>
    
    </xsl:template>
    
    <xsl:template match="field[type='timestamp' ]">
    
    <dbform:field name="{name}" type="date_time" descr="{name}"><xsl:call-template name="defaultValue"/></dbform:field>
    
    </xsl:template>
    
    
       
    
    
    <xsl:template match="field">
    <dbform:field name="{name}" type="text" descr="{name}"><xsl:call-template name="defaultValue"/></dbform:field>
    </xsl:template>
    
    <xsl:template name="defaultValue">
    <xsl:if test="not(default = 'NULL' or default = '0' or contains(default, '000-00-00 00:00:00'))"><xsl:value-of select="default"></xsl:value-of></xsl:if>
    
    
    
    </xsl:template>
</xsl:stylesheet>

