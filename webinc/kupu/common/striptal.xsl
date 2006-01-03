<?xml version="1.0" encoding="UTF-8" ?>
<!--
##############################################################################
#
# Copyright (c) 2003-2004 Kupu Contributors. All rights reserved.
#
# This software is distributed under the terms of the Kupu
# License. See LICENSE.txt for license text. For a list of Kupu
# Contributors see CREDITS.txt.
#
##############################################################################

This simple XSLT generates a pure XHTML document out of anything that
contains TAL or METAL.

$Id: striptal.xsl 3322 2004-03-23 13:56:23Z philikon $
-->
<xsl:stylesheet version="1.0"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:tal="http://xml.zope.org/namespaces/tal"
  xmlns:metal="http://xml.zope.org/namespaces/metal"
  xmlns:i18n="http://xml.zope.org/namespaces/i18n"
  >

<xsl:output method="html" encoding="UTF-8" />

<!-- strip tal: metal: and i18n: tags -->
<xsl:template match="//tal:*|//metal:*|//i18n:*">
  <xsl:apply-templates />
</xsl:template>

<!-- strip tal: metal: and i18n: attributes -->
<xsl:template match="@tal:*|@metal:*|@i18n:*" />

<!-- strip unnecessary XML namespace declarations -->
<!--xsl:template match="@xmlns:tal|@xmlns:metal|@xmlns:i18n" /-->

<!-- copy everything else verbatim -->
<xsl:template match="@*|node()">
  <xsl:copy>
    <xsl:apply-templates select="@*|node()"/>
  </xsl:copy>
</xsl:template>

</xsl:stylesheet>
