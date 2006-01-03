<?xml version="1.0"?>
<xsl:stylesheet version="1.0" 
  xmlns:php="http://php.net/xsl"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml">
    <xsl:output encoding="utf-8" method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

    <xsl:param name="url" select="'/'"/>
    <xsl:param name="id" select="'/'"/>
    <xsl:param name="dataUri" select="'/'"/>
    <xsl:param name="webroot" select="'/'"/>
    <xsl:param name="updateTree"/>
    <xsl:variable name="BX_OPEN_BASEDIR" select="php:functionString('constant','BX_OPEN_BASEDIR')"/>
	<xsl:variable name="mimetype" select="php:functionString('popoon_helpers_mimetypes::getFromFileLocation',$dataUri)"/>
    <xsl:template match="/">
        <html>
            <head>
            <title>Edit Image <xsl:value-of select="substring-after($dataUri,$BX_OPEN_BASEDIR)"/></title>
                         <link rel="stylesheet" href="{$webroot}themes/standard/admin/css/formedit.css" type="text/css" />

             <xsl:if test="$updateTree">
            <script type="text/javascript">
            window.parent.navi.Navitree.reload('/<xsl:value-of select="substring($dataUri,1,string-length($dataUri)-1)"/>');
            </script>
            </xsl:if>
            
			<script type="text/javascript" src="{$webroot}admin/webinc/js/showhidelayers.js"/>    
			
            </head>
            <body>
            
                <h1 class="pageTitle">Edit Image <xsl:value-of select="substring-after($dataUri,$BX_OPEN_BASEDIR)"/></h1>
                <xsl:if test="not(php:functionString('is_writable',$dataUri))">
                <h2 style="color: red;"><xsl:value-of select="$dataUri"/> is not writable!</h2>
                Changes made here will not be saved<br/><br/>
                </xsl:if>
             <!--   <xsl:call-template name="imageupload"/> -->
                <div style="float:left; margin-right: 20px;">
                <xsl:call-template name="rotate"/><br/>
				<xsl:call-template name="resize"/><br />
				<xsl:call-template name="crop"/><br/>
				<xsl:call-template name="progressbar"/>
                </div>
               <!-- <img  src="{$webroot}{$dataUri}?rand{php:functionString('rand')}"/>-->
                <xsl:copy-of select="php:function('bx_helpers_image::loadCropInterface',$dataUri)/html/*/node()"/>
            </body>
        </html>
    </xsl:template>
    
    <xsl:template name="crop">
    <h3>Crop:</h3>
				<form action="" method="POST" name="crop" onsubmit="MM_showHideLayers('wait_layer','','show');my_Submit()">
                <input type="hidden" name="bx[plugins][admin_edit][sx]"  value="0"/>
                <input type="hidden" name="bx[plugins][admin_edit][sy]" value="0"/>
                <input type="hidden" name="bx[plugins][admin_edit][ex]" value="0"/>
                <input type="hidden" name="bx[plugins][admin_edit][ey]" value="0"/>
                <input type="hidden" name="bx[plugins][admin_edit][dataUri]" value="{$dataUri}"/>
                <input type="radio" id="resizeAny" name="resize" onClick="my_SetResizingType(0);" checked="checked"/> 
                <label for="resizeAny">Any Dimensions</label> <br/>
                <input type="radio" name="resize" id="resizeProp" onClick="my_SetResizingType(1);"/> 
                <label for="resizeProp">Proportional</label><br/>
		
                <input type="submit" name="bx[plugins][admin_edit][crop]" value="Crop" /><br/>
                </form>
    </xsl:template>
	<xsl:template name="rotate">
    <script type="text/javascript">
	<![CDATA[	
		function unselectRotateRadioBtns() {
			document.rotateform.rotateradio0.checked = false;
			document.rotateform.rotateradio1.checked = false;
			document.rotateform.rotateradio2.checked = false;
		}
		
		function unselectRotateSelect() {
			document.rotateform.rortateselect.selectedIndex = 180;
		}
	]]>
    </script>
	<h3>Rotate:</h3>
                <form name="rotateform" action="" method="POST" onsubmit="MM_showHideLayers('wait_layer','','show');">
                <input type="hidden" name="bx[plugins][admin_edit][dataUri]" value="{$dataUri}"/>
                <input type="radio" id="rotateradio0" name="bx[plugins][admin_edit][rotate_radio]" value="-90" onchange="unselectRotateSelect()"/> Left<br/>
                <input type="radio" id="rotateradio1" name="bx[plugins][admin_edit][rotate_radio]" value="90" onchange="unselectRotateSelect()"/> Right<br/>
                <input type="radio" id="rotateradio2" name="bx[plugins][admin_edit][rotate_radio]" value="180" onchange="unselectRotateSelect()"/> 180°<br/>
                
				Any value <select id="rortateselect" name="bx[plugins][admin_edit][rotate_value]" onchange="unselectRotateRadioBtns()">
				<script type="text/javascript">
					<![CDATA[
					for($i = 180; $i >= -180; $i--) {
						if ($i == 0) {
							document.write('<option value="" selected="selected"></option>');
						} else {
							document.write('<option value="'+$i+'">'+$i+'°</option>');
						}
					}
					]]>
					</script>
				</select><br/>
                <input type="submit" name="bx[plugins][admin_edit][rotate]" value="Rotate"/><br/>
                </form>
	</xsl:template>

	<xsl:template name="resize">
	<h3>Resize:</h3>
					<form action="" method="POST" onsubmit="MM_showHideLayers('wait_layer','','show');">
					<input type="hidden" name="bx[plugins][admin_edit][dataUri]" value="{$dataUri}"/>
					<table>
						<tr>
							<td>Width: </td><td><input type="text" size="5" name="bx[plugins][admin_edit][resize_width]"/></td>
						</tr>
						<tr>
							<td>Height: </td><td><input type="text" size="5" name="bx[plugins][admin_edit][resize_height]"/></td>
						</tr>
					</table>			   
					<input type="submit" name="bx[plugins][admin_edit][resize]" value="Resize"/><br/>
					</form>
	</xsl:template>

	<xsl:template name="imageupload">
		<form action="" method="POST" enctype="multipart/form-data">
			<p>
				<h3>Replace this file with a new one</h3>
				<input type="file" name="uploadfile" size="20"/>
			</p>
			<p><input type="submit" name="bx[plugins][admin_edit][submit]" value="Upload file"/></p>
		</form>
	</xsl:template>
		
	<xsl:template name="progressbar">
		<div id="wait_layer" style="background-color: #ffffff; text-align:center; border:#000000 solid 1px; position:absolute; width:300px; height:115px; z-index:50; left: 200px; top: 200px; visibility: hidden">
			<h3>Image modification in progress</h3>
			<p><img src="/themes/standard/admin/images/wait_bar.gif" /><br />
			Image will be modified, please wait. This window will be closed after success.<br />
			</p>
		</div>
	</xsl:template>
    
</xsl:stylesheet>