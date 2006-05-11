<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" exclude-result-prefixes="xhtml,dc,content">

	<xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

	<!-- Timestamp of the last RSS entry that was already processed -->
	<xsl:param name="lastdate"/>

	<xsl:template match="/">
		<html>
			<body>
				<xsl:apply-templates select="/rss/channel" mode="xhtml"/>
			</body>	
		</html>		
	</xsl:template>
	
	<xsl:template match="/rss/channel" mode="xhtml">
		<xsl:for-each select="item">
			<!-- Make sure to not send an entry twice -->
			<xsl:if test="$lastdate &lt; dc:date">
				<!-- Format RSS 2.0 Feed Entry -->
				<h1><a href="{link}"><xsl:value-of select="title"/></a></h1>
				<div class="content"><xsl:copy-of select="content:encoded/node()" mode="xhtml"/></div>
			</xsl:if>
		</xsl:for-each>
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
