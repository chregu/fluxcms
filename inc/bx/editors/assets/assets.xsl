<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
 xmlns:xhtml="http://www.w3.org/1999/xhtml" 
 xmlns="http://www.w3.org/1999/xhtml"
 xmlns:php="http://php.net/xsl"
 xmlns:i18n="http://apache.org/cocoon/i18n/2.1">

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
    	<link rel="stylesheet" type="text/css" href="http://berggebiete/themes/standard/admin/css/formedit.css" />
    	<link rel="stylesheet" type="text/css" href="http://berggebiete/themes/standard/admin/css/admin.css" />
    	<link rel="stylesheet" type="text/css" media="screen" href="{$webroot}/themes/standard/admin/css/assets.css"/>
    	<script type="text/javascript" language="javascript">
    		var bx_webroot = '<xsl:value-of select="$webroot"/>';
    		var langs  = new Array(<xsl:for-each select="/bx/plugin/assets/langs/entry">'<xsl:value-of select="."/>'<xsl:if test="not(position()=last())">,</xsl:if></xsl:for-each>);
    	</script>
    	<script type="text/javascript" language="javascript"><![CDATA[
    	
    	function assetsPopup(id) {
    		valuef = document.getElementById('value['+id+']');
    		if (valuef) {
    			 var fBrowserUrl = bx_webroot + 'webinc/fck/editor/filemanager/browser/default/browser.html?Type=files&Connector=connectors/php/connector.php';
    			 fBrowserUrl+= "&RootPath=/files";
    		     fBrowserWin = window.open(fBrowserUrl, 'fBrowser', 'width=800,height=600,location=no,menubar=no');
    		}
    		
    		SetUrl = function(url)  {
    			valuef.value = url;	
    		}
    		
    	}
    	
    	function newAsset() {
    		ps = document.getElementsByTagName('p');
    		nextpos = 0;
    		for(i=0; i<ps.length;i++) {
    			if(ps[i].getAttribute('class') == 'assetp') {
    				nextpos++;
    			}
    		}
    		
    		assetP = addAssetP(nextpos, nextpos);
    		document.getElementById('assetsform').appendChild(assetP);
    	}
    	
    	function addAssetP(pos, id) {
    		
    		p = document.createElement('p');
    		p.setAttribute('id', 'assetp['+id+']');
    		p.setAttribute('class', 'assetp');
    		p.appendChild(document.createTextNode(pos+") "));
    		p.appendChild(document.createTextNode('  '));
    		
    		checkb = document.createElement('input');
    		checkb.setAttribute('type','checkbox');
    		checkb.setAttribute('name', 'bx[plugins][admin_edit][delete]['+id+']');
    		checkb.setAttribute('value', 1);
    		p.appendChild(checkb);
    		p.appendChild(document.createTextNode('  '));
    		
    		namef = document.createElement('input');
    		namef.setAttribute('type','text');
    		namef.setAttribute('name','bx[plugins][admin_edit][name]['+id+']');
    		namef.setAttribute('value','');
    		p.appendChild(namef);
    		p.appendChild(document.createTextNode('  '));
    		
    		valuef= document.createElement('input');
    		valuef.setAttribute('type','text');
    		valuef.setAttribute('name','bx[plugins][admin_edit][value]['+id+']');
    		valuef.setAttribute('id', 'value['+id+']');
    		valuef.setAttribute('value','');
    		p.appendChild(valuef);
    		p.appendChild(document.createTextNode('  '));
    		
    		button = document.createElement('input');
    		button.setAttribute('type','button');
    		button.setAttribute('name', '');
    		button.setAttribute('value','Select');
    		button.setAttribute('onclick','assetsPopup('+id+')');
    		p.appendChild(button);
    		p.appendChild(document.createTextNode('  '));
    		
    		select = document.createElement('select');
    		select.setAttribute('name','bx[plugins][admin_edit][type]['+id+']');
    		select.appendChild(getOption('Link','link'));
    		select.appendChild(getOption('Download','download'));
    		p.appendChild(select);
    		p.appendChild(document.createTextNode('  '));
    		
    		lang = document.createElement('select');
    		lang.setAttribute('name','bx[plugins][admin_edit][lang]['+id+']');
    		for(l=0; l<langs.length; l++) {
    			lang.appendChild(getOption(langs[l], langs[l]));
    		}
    		p.appendChild(lang);
    		p.appendChild(document.createTextNode('  '));
    		
    		target = document.createElement('select');
    		target.setAttribute('name','bx[plugins][admin_edit][target]['+id+']');
    		target.appendChild(getOption('----------------',''));
    		target.appendChild(getOption('_blank','_blank'));
    		p.appendChild(target);
    		p.appendChild(document.createTextNode('  '));
    		
    		return p
    	}
    	
    	function getOption(name, value) {
    		opt = document.createElement('option');
    		opt.setAttribute('value', value);
    		opt.appendChild(document.createTextNode(name));
			return opt;
    	}
    	
    	]]>
    	</script>
    	</head>
    	<body>
    		<div id="admincontent">
    		<h2><i18n:text>Edit Assets for</i18n:text> <xsl:value-of select="$assetpath"/></h2>
    		
    		 <div id="form">
    			
    			<form name="assetsform" action="" method="post" id="assetsform">
    				<xsl:call-template name="buttons"/>
    				<xsl:apply-templates select="/bx/plugin/assets/entry"/>
    			</form>
    		
    			
    		</div>
    		
    		
    		
    		
    		</div>
    	</body>
    </html>
</xsl:template>

<xsl:template name="buttons">
	
    <input type="submit" name="submit" value="Save"></input>&#160;
    <input type="button" name="add" value="Add New" onclick="newAsset()"/>&#160;
    
	<p class="uline">&#160;</p>
</xsl:template>

<xsl:template match="entry">
	<p id="assetp[{id}]" class="assetp"><xsl:value-of select="position()"/>)
	<input type="checkbox" name="bx[plugins][admin_edit][delete][{id}]" value="1"/><xsl:text> </xsl:text>
	<input type="text" name="bx[plugins][admin_edit][name][{id}]" value="{name}"/><xsl:text> </xsl:text>
	<input type="text" name="bx[plugins][admin_edit][value][{id}]" id="value[{id}]" value="{value}"/><xsl:text> </xsl:text>
	<input type="button" name="" value="Select" onclick="assetsPopup({id})"/><xsl:text> </xsl:text>
	<select name="bx[plugins][admin_edit][type][{id}]">
		<option value="link">
			<xsl:if test="type='link'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
		Link
		</option>
		<option value="download">
			<xsl:if test="type='download'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
		Download
		</option>
	</select><xsl:text> </xsl:text>
	<xsl:variable name="l" select="lang"/>
	<select name="bx[plugins][admin_edit][lang][{id}]">
		<xsl:for-each select="/bx/plugin/assets/langs/entry">
			<option value="{.}">
			<xsl:if test="$l=.">
				<xsl:attribute name="selected"><xsl:text>selected</xsl:text></xsl:attribute>
			</xsl:if>
			<xsl:value-of select="."/></option>
		</xsl:for-each>
	</select>
	<xsl:text> </xsl:text>
	<select name="bx[plugins][admin_edit][target][{id}]">
		<option value="">----------------</option>
		<option value="_blank">
			<xsl:if test="target='_blank'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
		_blank</option>
	</select>
	
	</p>
</xsl:template>

</xsl:stylesheet>
