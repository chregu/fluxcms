<?xml version="1.0"?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns:xhtml="http://www.w3.org/1999/xhtml" 
    xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
    xmlns="http://www.w3.org/1999/xhtml"
    > 
    <xsl:param name="webroot" value="'/'"/>
    <xsl:variable name="pluginName" select="/bx/plugin/@name"/>
     
     <xsl:template match="/">
		<html>
		<head>
			<link type="text/css" href="http://fluxcms/themes/standard/admin/css/formedit.css" rel="stylesheet"/>
		</head>
		<body>
			<xsl:apply-templates mode="xhtml"/>
		</body>
		</html>
     </xsl:template>
     
	 <xsl:template match="/bx/plugin[@name='admin_users']/users" mode="xhtml">
	 	<h2 class="openIdPage">
			Users
		</h2>
		<div id='openIdTrust'>
		<ul>
		<xsl:for-each select="user">
				<li><img style='border:0px;' src='http://fluxcms/admin/webinc/img/icons/delete.gif'/> 
					<a href="edit/?id={id}"><xsl:value-of select="username"/></a>
				</li>
		</xsl:for-each>
		</ul>
		</div>
	 </xsl:template>
	 
	 <xsl:template match="/bx/plugin[@name='admin_users']/user/user" mode="xhtml">
	 <h2 class="openIdPage">
			User | <xsl:value-of select="username"/>
		</h2>
		<div id='openIdTrust'>
		<form name="adminform" action="" method="POST" enctype="multipart/form-data">
		<table>
		<tr>
		<td>
			Username
		</td>
		<td>
			<input type="text" value="{username}" name="bx[plugins][admin_users][username]"/>
		</td>
		</tr>
		<tr>
		<td>
			Fullname
		</td>
		<td>
			<input type="text" value="{fullname}" name="bx[plugins][admin_users][fullname]"/>
		</td>
		</tr>
		<tr>
		<td>
			Mail Adress
		</td>
		<td>
			<input type="text" value="{mail}" name="bx[plugins][admin_users][mail]"/>
		</td>
		</tr>
		<tr>
		<td>
			Guip
		</td>
		<td>
			<input type="text" value="{user_gupi}" name="bx[plugins][admin_users][gupi]"/>
		</td>
		</tr>
		<tr>
		<td>
			Gid
		</td>
		<td>
			<input type="text" value="{user_gid}" name="bx[plugins][admin_users][gid]"/>
		</td>
		</tr>
		<tr>
		<td>
			Sprache
		</td>
		<td>
			<input type="text" value="{user_adminlang}" name="bx[plugins][admin_users][lang]"/>
		</td>
		</tr>
		<tr>
		<td>
			Plazes Username
		</td>
		<td>
			<input type="text" value="{plazes_username}" name="bx[plugins][admin_users][plazes_username]"/>
		</td>
		</tr>
		<tr>
		<td>
			Plazes Password
		</td>
		<td>
			<input type="text" value="{plazes_password}" name="bx[plugins][admin_users][plazes_pwd]"/>
		</td>
		</tr>
		<tr>
		<td colspan="2">
			<input type="submit" value="SEND"/>
		</td>
		</tr>
		</table>
		</form>
		</div>
	 </xsl:template>
	 
</xsl:stylesheet>
