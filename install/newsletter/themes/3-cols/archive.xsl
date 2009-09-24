<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" exclude-result-prefixes="xhtml default bx">
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
				a.top:link { font-weight:regular; color:#C83721; text-decoration:none }
				a.top:visited { font-weight:regular; color:#C83721; text-decoration:none }
				a.top:hover { font-weight:regular; color:#C83721; text-decoration:none }
				a.top:active { font-weight:regular; color:#C83721; text-decoration:none }
				a.top:focus { font-weight:regular; color:C83721; text-decoration:none }
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
	    	<table style="border:1px solid #E4E0DF" width="598" cellspacing="0" cellpadding="0" id="start" name="start">
				<tr>
					<td>
						<img width="598" height="129" alt="Scansystems" src="/files/_galleries/gallery/header.jpg"/>
					</td>
				</tr>
				<tr>
					<td>	
						<table style="font-size: 11px; padding: 5px 20px 5px 20px; color:#FFFFFF" width="598" cellspacing="0" cellpadding="0" border="0" background="/files/_galleries/gallery/footer_background.gif">
							<tr>
								<td align="left" style="font-size: 11px;"><b>Scansystems Newsletter </b></td>
								<td align="right"><img width="8" height="7" alt="" src="/files/_galleries/gallery/arrow_head_footer.gif"/><a href="http://www.scansystems.ch" class="meta" style="font-size: 11px; padding-left:5px">www.scansystems.ch</a></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<div style="width:578px; font-size: 11px; margin: 0px 0px 0px 20px; border-bottom:1px solid #E4E0DF">
							<div style="padding: 16px 20px 20px 0px; margin-left:-24px">
								<p style="margin-top:0px; margin-bottom:6px; line-height:18px; font-size: 12px;"><xsl:copy-of select="xhtml:div/xhtml:div[@id='intro']/xhtml:p/node()" mode="xhtml"/></p>
								<xsl:for-each select="xhtml:div/xhtml:div[@class='item']">
									<p style="margin:0px; padding-top:6px; line-height:18px;"><img width="8" height="7" alt="" src="/files/_galleries/gallery/arrow_right.gif"/><a style="padding-left:5px" href="#item{position()}"><xsl:copy-of select="xhtml:h1/node()" mode="xhtml"/></a></p>
								</xsl:for-each>
							</div>				
						</div>				
					</td>
				</tr>
				<xsl:for-each select="xhtml:div/xhtml:div[@class='item']">
					<tr>
						<td>
							<div style="width:578px; font-size: 11px; margin: 0px 0px 0px 20px; border-bottom:1px solid #E4E0DF" id="item{position()}" name="item{position()}">
								<div style="padding: 16px 20px 20px 0px; margin-left:-24px">
									<xsl:apply-templates mode="xhtml"/>
								</div>
							</div>				
						</td>
					</tr>
				</xsl:for-each>
				<tr>
					<td>
						<div style="width:578px; font-size: 11px; margin: 0px 0px 0px 20px; border-bottom:1px solid #E4E0DF">
							<div style="padding: 20px 20px 16px 0px; margin-left:-24px">
								<h1 style="margin:0px; font-weight: bold; font-size: 12px; color: rgb(0, 0, 0);"><xsl:copy-of select="xhtml:div/xhtml:div[@id='outro']/xhtml:h1/node()" mode="xhtml"/></h1>
								<p style="margin-top:5px; margin-bottom:7px; line-height:18px; font-size: 12px;"><xsl:copy-of select="xhtml:div/xhtml:div[@id='outro']/xhtml:p[position()=1]/node()" mode="xhtml"/></p>
								<p style="margin-top:0px; margin-bottom:0px; line-height:16px"><xsl:apply-templates select="xhtml:div/xhtml:div[@id='outro']/xhtml:p[position()=2]/node()" mode="xhtml"/></p>
								<p style="margin-top:10px; margin-bottom:0px; line-height:16px"><xsl:apply-templates select="xhtml:div/xhtml:div[@id='outro']/xhtml:p[position()=3]/node()" mode="xhtml"/></p>						
							</div>
						</div>				
					</td>
				</tr>
				<tr>
					<td align="right" height="60">
						<img width="234" height="18" alt="Footer" style="margin: 0px 20px 0px 20px;" src="/files/_galleries/gallery/footer_logo.gif"/>		
					</td>
				</tr>
				<tr>
					<td>
						<table style="font-size: 11px; padding: 5px 20px 5px 20px; color:#FFFFFF" width="598" cellspacing="0" cellpadding="0" border="0" background="/files/_galleries/gallery/footer_background.gif">
							<tr>
								<td align="left"></td>
								<td align="right" style="font-size: 11px;">&#169; 2006 Scansystems</td>
							</tr>
						</table>
					</td>
				</tr>
	    	</table>    
	    </body>
	</xsl:template>
    
    <xsl:template match="xhtml:h1" mode="xhtml">
    	<h1 style="margin:0px; font-weight: bold; font-size: 12px; color: rgb(0, 0, 0);"><xsl:copy-of select="node()" mode="xhtml"/></h1>
	</xsl:template>
	
	<xsl:template match="xhtml:p[not(xhtml:img)]" mode="xhtml">
		<p style="margin-top:5px; margin-bottom:0px; line-height:18px; font-size: 12px;"><xsl:copy-of select="node()" mode="xhtml"/></p>
	</xsl:template>
	
	<xsl:template match="xhtml:ul" mode="xhtml">
		<ul class="type1"><xsl:apply-templates mode="xhtml"/></ul>
	</xsl:template>
	
	<xsl:template match="xhtml:a[not(@class)]" mode="xhtml">
		<!-- If the link points to a file it needs a special icon -->
		<xsl:choose>
			<xsl:when test="string-length(substring-before(@href,'.pdf')) &gt; 0">
				<img width="7" height="9" alt="" src="/files/_galleries/gallery/pdf_icon.gif"/>
			</xsl:when>
		   <xsl:otherwise>
		       <img width="8" height="7" alt="" src="/files/_galleries/gallery/arrow_right.gif"/>
		   </xsl:otherwise>
		</xsl:choose>
		
		<span style="padding-left:5px">
			<xsl:element name="{local-name()}">
				<xsl:apply-templates select="@*" mode="xhtml"/>
				<xsl:apply-templates mode="xhtml"/>
			</xsl:element>
		</span>
	</xsl:template>
	
	<xsl:template match="xhtml:p[xhtml:img]" mode="xhtml">
		<table width="550" cellspacing="0" cellpadding="0" border="0" style="margin-top:14px; margin-left:24px">
			<tr>
				<td align="left" style="width:220px; border:1px solid #484A4D"><xsl:apply-templates select="xhtml:img" mode="xhtml"/></td>
				<td align="left" valign="top" style="padding-left:17px; margin-top:0px">
					<p style="margin-left:-24px; margin-top:-4px; margin-bottom:12px; line-height:16px; font-size: 11px; width:322px"><xsl:copy-of select="text()|*[not(local-name()='img') and not(local-name() = 'a' and position() = last())]" mode="xhtml"/></p>
					<table width="100%" cellspacing="0" cellpadding="0" border="0"> 
						<tr>
							<td style="font-size: 11px;"><xsl:apply-templates select="xhtml:a[last()]" mode="xhtml"/></td>
							<td align="right"><img width="7" height="8" alt="Top" src="/files/_galleries/gallery/arrow_top.gif"/><a style="padding-left:5px; font-size: 11px;" class="top" href="#start">Top</a></td>
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
