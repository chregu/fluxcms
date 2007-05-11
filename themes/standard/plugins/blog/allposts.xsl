<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xhtml="http://www.w3.org/1999/xhtml"
	xmlns:php="http://php.net/xsl"
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:rss="http://purl.org/rss/1.0/"	
	xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:blog="http://bitflux.org/doctypes/blog"
	xmlns:bxf="http://bitflux.org/functions"
	xmlns="http://www.w3.org/1999/xhtml" exclude-result-prefixes="rdf rss dc php blog xhtml">

	<xsl:import href="master.xsl" />
	<xsl:import href="../standard/common.xsl" />
	<xsl:output encoding="utf-8" method="xml" />

	<xsl:template name="content">
	
		<h2>Blog Sitemap</h2>
		<xsl:copy-of disable-output-escaping="yes" select="/bx/plugin[@name = 'blogpostsall']/html/body" />
		
	</xsl:template>
</xsl:stylesheet>
