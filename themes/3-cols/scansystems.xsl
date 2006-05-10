<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" exclude-result-prefixes="xhtml">

	<xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

	<xsl:template match="/">
		<xsl:apply-templates mode="xhtml"/>
	</xsl:template>
	
	<xsl:template match="xhtml:body" mode="xhtml">
		<xsl:element name="{local-name()}">
			<xsl:attribute name="style">font-size: 11px; font-family: Verdana, Arial, Helvetica, SunSans-Regular, sans-serif;</xsl:attribute>
			<xsl:apply-templates select="@*" mode="xhtml"/>
			<xsl:apply-templates mode="xhtml"/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="xhtml:a" mode="xhtml">
		<xsl:element name="{local-name()}">
			<xsl:attribute name="style">color:#C83721;</xsl:attribute>
			<xsl:apply-templates select="@*" mode="xhtml"/>
			<xsl:apply-templates mode="xhtml"/>
		</xsl:element>
	</xsl:template>
    
	<xsl:template match="xhtml:h1" mode="xhtml">
		<xsl:element name="{local-name()}">
			<xsl:attribute name="style">font-size:18px;</xsl:attribute>
			<xsl:apply-templates select="@*" mode="xhtml"/>
			<xsl:apply-templates mode="xhtml"/>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="xhtml:h2" mode="xhtml">
		<xsl:element name="{local-name()}">
			<xsl:attribute name="style">font-size:16px;</xsl:attribute>
			<xsl:apply-templates select="@*" mode="xhtml"/>
			<xsl:apply-templates mode="xhtml"/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="xhtml:h3" mode="xhtml">
		<xsl:element name="{local-name()}">
			<xsl:attribute name="style">font-size:14px;</xsl:attribute>
			<xsl:apply-templates select="@*" mode="xhtml"/>
			<xsl:apply-templates mode="xhtml"/>
		</xsl:element>
	</xsl:template>
   
    
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
