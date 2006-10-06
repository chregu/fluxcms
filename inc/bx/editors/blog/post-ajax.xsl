
<xsl:stylesheet version="1.0" xmlns:sixcat="http://sixapart.com/atom/category#"
 xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://purl.org/atom/ns#" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:bxf="http://bitflux.org/functions" xmlns:rss="http://purl.org/rss/1.0/" xmlns:blog="http://bitflux.org/doctypes/blog" 
 xmlns:php="http://php.net/xsl"
 xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
 exclude-result-prefixes="rdf dc php xhtml rss bxf blog">
<xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
  <xsl:param name="webroot"/>
    <xsl:param name="collectionUri" select="''"/>
    <xsl:param name="collectionUriOfId" select="''"/>
    <xsl:param name="dataUri" select="''"/>
    
  <xsl:template match="/">
  <entry>
 <!-- FIXME: escape values & check for ampersand & < problems -->
  {"id":"<xsl:value-of select="/atom:entry/atom:id"/>","uri":"<xsl:value-of select="/atom:entry/atom:uri"/>"}
  </entry>
  </xsl:template>
</xsl:stylesheet>
