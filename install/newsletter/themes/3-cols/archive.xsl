<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" exclude-result-prefixes="xhtml">
    <xsl:import href="master-newsletter.xsl"/>
    <xsl:import href="../standard/common.xsl"/>

    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
    
    <xsl:template name="content">
        <xsl:variable name="body" select="/bx/plugin[@name='xhtml']/xhtml:html/xhtml:body"/>
        <xsl:choose>
            <!-- if there is a <div id = 'content'> just take that -->
            <xsl:when test="$body/xhtml:div[@id = 'content']">
                <xsl:apply-templates select="$body" mode="xhtml"/>
            </xsl:when>
            <!-- otherwise take the whole body content -->
            <xsl:otherwise>
                <!-- <xsl:copy-of select="$body/*|$body/text()"/> -->
                <xsl:apply-templates select="$body/node()" mode="xhtml"/>
            </xsl:otherwise>
        </xsl:choose>
        

    </xsl:template>
    
    <!-- add everything from head to the output -->
    <xsl:template name="html_head">
        <xsl:apply-templates select="/bx/plugin[@name='xhtml']/xhtml:html/xhtml:head/node()" mode="xhtml"/>
    </xsl:template>
    
    <!-- except the right content -->
    <xsl:template match="contentRight">
    </xsl:template>
    
    <!-- except the title -->
    <xsl:template match="xhtml:head/xhtml:title" mode="xhtml">
    </xsl:template>

    <!-- except the links -->
    <xsl:template match="xhtml:head/xhtml:link" mode="xhtml">
    </xsl:template>
    
    <!-- do not output meta tags without @content -->
    <xsl:template match="xhtml:head/xhtml:meta[not(@content)]" mode="xhtml">
    </xsl:template>
    
    <xsl:template name="body_attributes">
    <xsl:apply-templates select="/bx/plugin[@name='xhtml']/xhtml:html/xhtml:body/@*" mode="xhtml"/>
    </xsl:template>
    
    <xsl:template match="xhtml:span[@id='status']" mode="xhtml">
    	<xsl:if test="/bx/plugin[@name='newsletter']/newsletter/status">
    		<b><xsl:value-of select="/bx/plugin[@name='newsletter']/newsletter/status"/></b>
    	</xsl:if>
    </xsl:template>
    
    <xsl:template match="xhtml:div[@id='newsletter_groups']" mode="xhtml">
        <xsl:for-each select="/bx/plugin[@name='newsletter']/newsletter/group">
        	<input type="checkbox" name="groups[]" checked="checked" value="{@id}"/>
        	<xsl:value-of select="."/><br/>
        </xsl:for-each>
    </xsl:template>
    
	<xsl:template match="xhtml:body" mode="xhtml">
	    <body style="padding:0px; margin:0px; font-family: Verdana,Arial,Helvetica,sans-serif; font-size: 11px">
			<style type="text/css">
				a:link { font-weight:regular; color:#C83721; text-decoration:underline }
				a:visited { font-weight:regular; color:#C83721; text-decoration:underline }
				a:hover { font-weight:regular; color:#C83721; text-decoration:underline }
				a:active { font-weight:regular; color:#C83721; text-decoration:underline }
				a:focus { font-weight:regular; color:C83721; text-decoration:underline }
				a.address:link { font-weight:regular; color:#000000; text-decoration:none }
				a.address:visited { font-weight:regular; color:#000000; text-decoration:none }
				a.address:hover { font-weight:regular; color:#000000; text-decoration:underline }
				a.address:active { font-weight:regular; color:#000000; text-decoration:none }
				a.address:focus { font-weight:regular; color:#000000; text-decoration:none }
				a.meta:link { font-weight:regular; color:#FFFFFF; text-decoration:none }
				a.meta:visited { font-weight:regular; color:#FFFFFF; text-decoration:none }
				a.meta:hover { font-weight:regular; color:#FFFFFF; text-decoration:none }
				a.meta:active { font-weight:regular; color:#FFFFFF; text-decoration:none }
				a.meta:focus { font-weight:regular; color:#FFFFFF; text-decoration:none }
				a.technical:link { font-weight:regular; color:#000000; text-decoration:underline }
				a.technical:visited { font-weight:regular; color:#000000; text-decoration:underline }
				a.technical:hover { font-weight:regular; color:#000000; text-decoration:underline }
				a.technical:active { font-weight:regular; color:#000000; text-decoration:underline }
				a.technical:focus { font-weight:regular; color:#000000; text-decoration:underline }
			</style>
	    	<table width="598" cellspacing="0" cellpadding="0" border="0" id="start" name="start">
				<tr>
					<td>
						<img src="/files/_galleries/gallery/header.jpg"/>
					</td>
				</tr>
				<tr>
					<td>
						<div style="font-size: 11px; padding: 5px 20px 5px 20px; background-image:url(/files/_galleries/gallery/footer_background.gif); background-color:rgb(69, 73, 76)">
							<table width="100%" cellspacing="0" cellpadding="0" border="0" style="color:#FFFFFF">
								<tr>
									<td align="left" style="font-size: 11px;"><b>Scansystems Newsletter </b>{date}</td>
									<td align="right"><img src="/files/_galleries/gallery/arrow_head_footer.gif" style="padding-right:7px"/><a href="http://www.scansystems.ch" class="meta" style="font-size: 11px;">www.scansystems.ch</a></td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div style="padding: 20px 0px 20px 0px; margin: 0px 20px 0px 20px; font-size:10px; border-bottom:1px solid #E4E0DF">
							Wenn Sie dieses E-Mail nicht lesen k&#246;nnen, <a href="{{publication}}" class="technical">klicken Sie hier</a>.
						</div>				
					</td>
				</tr>
				<tr>
					<td>
						<div style="font-size: 11px; padding: 20px 0px 20px 0px; margin: 0px 20px 0px 20px; border-bottom:1px solid #E4E0DF">
							<h1 style="margin-top:0px; line-height:11px; font-weight: bold; font-size: 12px; color: rgb(0, 0, 0);"><xsl:copy-of select="xhtml:div/xhtml:div[@id='intro']/xhtml:h1/node()" mode="xhtml"/></h1>
							<p style="font-size: 12px; padding-bottom:5px"><xsl:copy-of select="xhtml:div/xhtml:div[@id='intro']/xhtml:p/node()" mode="xhtml"/></p>
							<xsl:for-each select="xhtml:div/xhtml:div[@class='item']">
								<p style="line-height:8px; margin-bottom:6px;"><img src="/files/_galleries/gallery/arrow_right.gif" style="padding-right:7px"/><a href="#item{position()}"><xsl:copy-of select="xhtml:h1/node()" mode="xhtml"/></a></p>
							</xsl:for-each>						
						</div>				
					</td>
				</tr>
				<xsl:for-each select="xhtml:div/xhtml:div[@class='item']">
					<tr>
						<td>
							<div style="font-size: 11px; padding: 20px 0px 20px 0px; margin: 0px 20px 0px 20px; border-bottom:1px solid #E4E0DF" id="item{position()}" name="item{position()}">
								<xsl:apply-templates mode="xhtml"/>
							</div>				
						</td>
					</tr>
				</xsl:for-each>
				<tr>
					<td>
						<div style="font-size: 11px; padding: 20px 0px 20px 0px; margin: 0px 20px 0px 20px; border-bottom:1px solid #E4E0DF">
							<h1 style="margin-top:0px; line-height:11px; font-weight: bold; font-size: 11px; color: rgb(0, 0, 0);"><xsl:copy-of select="xhtml:div/xhtml:div[@id='outro']/xhtml:h1/node()" mode="xhtml"/></h1>
							<p><xsl:copy-of select="xhtml:div/xhtml:div[@id='outro']/xhtml:p[position()=1]/node()" mode="xhtml"/></p>
							<p><xsl:copy-of select="xhtml:div/xhtml:div[@id='outro']/xhtml:p[position()=2]/node()" mode="xhtml"/></p>
							<p style="margin-bottom:0px;"><xsl:copy-of select="xhtml:div/xhtml:div[@id='outro']/xhtml:p[position()=3]/node()" mode="xhtml"/></p>						
						</div>				
					</td>
				</tr>
				<tr>
					<td align="right" height="60">
						<img style="margin: 0px 20px 0px 20px;" src="/files/_galleries/gallery/footer_logo.gif"/>		
					</td>
				</tr>
				<tr>
					<td>
						<div style="font-size: 11px; padding: 5px 20px 5px 20px; background-image:url(/files/_galleries/gallery/footer_background.gif); background-color:rgb(69, 73, 76)">
							<table width="100%" cellspacing="0" cellpadding="0" border="0" style="color:#FFFFFF">
								<tr>
									<td align="left"><img src="/files/_galleries/gallery/arrow_head_footer.gif" style="padding-right:7px"/><a href="{{unsubscribe}}" class="meta" style="font-size: 11px;">abbestellen</a></td>
									<td align="right" style="font-size: 11px;">&#169; 2006 Scansystems</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
	    	</table>    
	    </body>
	</xsl:template>
    
    <xsl:template match="xhtml:h1" mode="xhtml">
    	<h1 style="margin-top:0px; line-height:11px; font-weight: bold; font-size: 11px; color: rgb(0, 0, 0);"><xsl:copy-of select="node()" mode="xhtml"/></h1>
	</xsl:template>
	
	<xsl:template match="xhtml:p[not(xhtml:img)]" mode="xhtml">
		<p style="margin-bottom:0px; font-size: 11px;"><xsl:copy-of select="node()" mode="xhtml"/></p>
	</xsl:template>
	
	<xsl:template match="xhtml:ul" mode="xhtml">
		<ul class="type1"><xsl:apply-templates mode="xhtml"/></ul>
	</xsl:template>
	
	<xsl:template match="xhtml:a" mode="xhtml">
		<img src="/files/_galleries/gallery/arrow_right.gif" style="padding-right:7px"/><xsl:copy-of select="." mode="xhtml"/>
	</xsl:template>
	
	<xsl:template match="xhtml:p[xhtml:img]" mode="xhtml">
		<table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:18px">
			<tr>
				<td align="left" style="padding-right:18px"><xsl:apply-templates select="xhtml:img" mode="xhtml"/></td>
				<td align="left" valign="top">
					<p style="margin-top:0px; font-size: 11px;"><xsl:copy-of select="text()|*[not(local-name()='img') and not(local-name() = 'a' and position() = last())]" mode="xhtml"/></p>
					<table width="100%" cellspacing="0" cellpadding="0" border="0"> 
						<tr>
							<td style="font-size: 11px;"><xsl:apply-templates select="xhtml:a[last()]" mode="xhtml"/></td>
							<td align="right"><img src="/files/_galleries/gallery/arrow_top.gif" style="padding-right:7px"/><a style="font-size: 11px;" href="#start">Top</a></td>
						</tr>
					</table>
				</td>
			</tr>				
		</table>
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
