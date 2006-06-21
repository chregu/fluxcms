<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
 xmlns:php="http://php.net/xsl" 
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
 xmlns:xhtml="http://www.w3.org/1999/xhtml" 
 xmlns="http://www.w3.org/1999/xhtml">

<xsl:output
 encoding="utf-8"
 method="html"
 doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
 doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

<xsl:param name="webroot"/>
<xsl:variable name="assetpath" select="/bx/plugin/assets/@path"/>

<xsl:template match="/">
    <html>
    	<head>
    	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    	<link rel="stylesheet" type="text/css" media="screen" href="{$webroot}/themes/admin/css/assets.css"/>
    	</head>
    	<body>
    		<h2>Edit Assets for <xsl:value-of select="$assetpath"/></h2>
    		
    		<div id="form">
    			<form name="assetsform" id="assetsform">
    				
    			</form>
    		</div>
    	</body>
    </html>
</xsl:template>

</xsl:stylesheet>
