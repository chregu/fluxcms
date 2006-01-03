<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:bxco="http://bitflux.org/config/1.0" xmlns:exsl="http://exslt.org/common" extension-element-prefixes="exsl">

    <xsl:output encoding="utf-8" method="xml"/>

    <xsl:template match="/">
        <xsl:for-each select="/database/table">

            <exsl:document href="config.{name}.xml" indent="yes">

                <bxco:config>
                    <bxco:fields table="{name}" downloaddir="/files/{name}/">

                        <xsl:apply-templates select="declaration/field"/>

                    </bxco:fields>

                     <bxco:chooser field="{declaration/field[type = 'text'][1]/name}" orderby="{declaration/field[type = 'text'][1]/name}" descr="Choose {name}"/>
                    
                </bxco:config>
            </exsl:document>

        </xsl:for-each>

    </xsl:template>
    
    <xsl:template match="field[type='text']">
    
    <bxco:field name="{name}" type="text" descr="{name}"><xsl:call-template name="defaultValue"/></bxco:field>
    
    </xsl:template>
   
      <xsl:template match="field[type='text' and not(length)]">
    
    <bxco:field name="{name}" type="textarea" descr="{name}"><xsl:call-template name="defaultValue"/></bxco:field>
    
    </xsl:template>
   
    <xsl:template match="field[name='ID' or name='id']">
      <bxco:field name="{name}" type="fixed" descr="{name}"><xsl:call-template name="defaultValue"/></bxco:field>
    </xsl:template>
     
    <xsl:template match="field[name='changed'][type='timestamp']" priority="100">
    
     <bxco:field name="changed" descr="Changed" type="fixed" subtype="datetime">
  <bxco:onnew default="sql:now()" /> 
  <bxco:onchange default="sql:now()" /> 
 </bxco:field>
    </xsl:template>
    
     <xsl:template match="field[name='created'][type='timestamp']" priority="100">
    
     <bxco:field name="created" descr="Created" type="fixed" subtype="datetime">
        <bxco:onnew default="sql:now()" /> 
      </bxco:field>
    </xsl:template>
    
    
    <xsl:template match="field[name='md5']" priority="100">
    
    <bxco:field name="{name}" type="fixed"  descr="{name}"><xsl:call-template name="defaultValue"/></bxco:field>
    
    </xsl:template>
    
     <xsl:template match="field[contains(.,'fileref')]" priority="100">
    
    <bxco:field name="{name}" type="file"  descr="{name}"><xsl:call-template name="defaultValue"/></bxco:field>
    
    </xsl:template>
    
    <xsl:template match="field[type='timestamp' ]">
    
    <bxco:field name="{name}" type="datetime" descr="{name}"><xsl:call-template name="defaultValue"/></bxco:field>
    
    </xsl:template>
    
    
       
    
    
    <xsl:template match="field">
    <bxco:field name="{name}" type="text" descr="{name}"><xsl:call-template name="defaultValue"/></bxco:field>
    </xsl:template>
    
    <xsl:template name="defaultValue">
    <xsl:if test="not(default = 'NULL' or default = '0' or contains(default, '000-00-00 00:00:00'))"><xsl:value-of select="default"></xsl:value-of></xsl:if>
    
    
    
    </xsl:template>
</xsl:stylesheet>

