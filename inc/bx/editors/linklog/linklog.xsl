<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:php="http://php.net/xsl" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml">
<xsl:output encoding="utf-8" method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
<xsl:param name="webroot"/>
<!-- 
	stylesheet for the linklog-editor
 -->
    <xsl:param name="url" select="'/'"/>
    <xsl:param name="id" select="'/'"/>
    <xsl:param name="dataUri" select="'/'"/>
    <xsl:param name="webroot" select="'/'"/>
    <xsl:template match="/"> 
		
<!-- of any use? -->	
    <xsl:variable name="selectedID" select="/bx/plugin/linklog/linklog/id/text()"/>
    
    <!-- a bit javascript to add tags to formfield: -->
<script type="text/javascript">
 function addTag (tag) {
	document.forms.Master.elements["bx[plugins][admin_edit][tags]"].value = trim(document.forms.Master.elements["bx[plugins][admin_edit][tags]"].value + " " + tag);
 }
 
function trim(sString){
	while (sString.substring(0,1) == ' '){
		sString = sString.substring(1, sString.length);
	}
	while (sString.substring(sString.length-1, sString.length) == ' '){
		sString = sString.substring(0,sString.length-1);
	}
	return sString;
} 
 
 </script>   
    
     <html>
            <head>
                <title>linklog Editor</title>
                <link rel="stylesheet" href="{$webroot}themes/standard/admin/css/formedit.css" type="text/css"/>
                <link rel="stylesheet" href="{$webroot}webinc/plugins/linklog/admin.css" type="text/css"/>
            </head>
            <body>
            
	<h1>linklog editor</h1>

<!-- we can only use one document because we return an emtpy xml-tree when entering a new link -->
<xsl:for-each select="/bx/plugin/linklog/link">

			<!-- insert a new link -->
			<form name="Master" action="" method="post" enctype="multipart/form-data" id="Master">
			
			<div class="linklog_editor_data">
				<p>URL:<br /> <input id="url" class="blackH5" name="bx[plugins][admin_edit][url]" size="40" value="{url}" type="text"/></p>
				<p>Title: <br /><input id="title" class="blackH5" name="bx[plugins][admin_edit][title]" size="40" value="{title}" type="text"/></p>
				<p>Description: <br />
				<textarea id="description" class="blackH5" wrap="virtual" name="bx[plugins][admin_edit][description]" rows="2" cols="38">
					<xsl:value-of select="description"/>
				</textarea>
				</p>
				<!-- hier muss foreach rein -->
				<p>Tags: <br />
				<input id="tags" class="blackH5" name="bx[plugins][admin_edit][tags]" size="40" value="{tags}" type="text"/>
					<xsl:call-template name="buttons"/>			
				
				<!-- 
				<textarea id="tags" name="bx[plugins][admin_edit][tags]" cols="38" rows="1">
					<xsl:for-each select="/bx/plugin/linklog/link/categories/category">
                     		<xsl:value-of select="title"/> &#160; 
					</xsl:for-each>
				</textarea>
				 -->
				
				</p>

				

			</div>
			
		    <div class="linklog_editor_tags">
			<xsl:call-template name="tags"/>
			
				
				
				</div>		
			
			
				<br class="clear" />
	             <input type="hidden" id="id" name="bx[plugins][admin_edit][id]" value="{id}"/>				

			</form>
</xsl:for-each>

            </body>
        </html>
    </xsl:template>

    <xsl:template name="buttons">
    <p>
&#160;<input accesskey="s" type="submit" value="Save Entry"/>&#160;
 <input type="button" name="_notindb" value="New Entry" onclick="javascript:window.location.href='./?new=1';"/>&#160; 

<!--  only display delete-button when a link contains data -->
	<xsl:choose>
      <xsl:when test='/bx/plugin/linklog/link/id[.!=""]'>
		 <input type="button" name="_notindb" value="Delete Entry" onclick="javascript:document.forms.Master.action += '../delete/{/bx/plugin/linklog/link/id/.}'; document.forms.Master.submit();"/>

      </xsl:when> 
 	</xsl:choose>
</p>
<h3>Additional Juice:</h3>
<p>
<!--  <a href="javascript:h=location.href;t=document.title;e=%22%22 +(window.getSelection ? window.getSelection() : document.getSelection ?document.getSelection() : document.selection.createRange().text); if(!e) e = prompt(%22You didn'tselect any text.  Enter a description(or not):%22, %22%22); location=%22{$webroot}/admin/edit/linklog/?&url=%22 + escape(h) + %22&name=%22 + escape(t) + %22&description=%22+ escape(e).replace(/ /g, %22+%22); void 0">linklog it</a> - pull this to your bookmark toolbarfolder  -->
<a href="javascript:%20var%20baseUrl%20=%20'{$webroot}admin/edit/linklog/?';%20var%20url=baseUrl;var%20title=document.title;%20url=url%20+%20'name='%20+%20encodeURIComponent(title);%20var%20currentUrl=document.location.href;%20url=url%20+%20'&amp;url='%20+%20encodeURIComponent(currentUrl);%20var%20selectedText;%20selectedText=getSelection();%20if%20(selectedText%20!=%20'')%20url=url%20+%20'&amp;description='%20+%20encodeURIComponent(selectedText);var win = window.open(null, '', 'width=700,height=500,scrollbars,resizable,location,toolbar');win.location.href=url;win.focus();">linkit</a>

</p>

 <p>
 <a>
	 <xsl:attribute name="href">
		 http://www.sequenz.ch
	 </xsl:attribute>
	 sequenz
</a>
 
 
 </p>
    </xsl:template>

    <xsl:template name="tags">
    <h3>Used Tags:</h3>
		<ul>
			<xsl:for-each select="/bx/plugin/linklog/tags/tag">
				<li><a href="#">
				   <xsl:attribute name="onClick">addTag('<xsl:value-of select="name"/>')</xsl:attribute>
				   <xsl:value-of select="name"/>
				</a> (<xsl:value-of select="numberentries"/>)</li>
			</xsl:for-each>
		</ul>
    </xsl:template>





</xsl:stylesheet>
