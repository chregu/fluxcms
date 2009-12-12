<?xml version="1.0" encoding="ISO-8859-1"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:bxco="http://bitflux.org/config/1.0"
    xmlns="http://www.w3.org/1999/xhtml"
	>
   <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"
    
    />
    
   
<!--
relative urls don't work with sablotron 0.90, but do with 0.97
you have to change these values to absolute paths if you can't 
upgrade to 0.97
-->
<xsl:include href="./selectmaker.xsl" />
<xsl:include href="./languages.xsl" />
<xsl:include href="./imageformats.xsl" />

<xsl:param name="actionURL" value="./"/>
 <xsl:variable name="masteridfield">
       <xsl:choose>
       <xsl:when test="/bx/bxco:config/bxco:fields/@idfield"><xsl:value-of select="/bx/bxco:config/bxco:fields/@idfield"/></xsl:when>
       
       <xsl:otherwise>id</xsl:otherwise>
       </xsl:choose>
       </xsl:variable>

<xsl:variable name="setFocus">
    <xsl:choose>
        <xsl:when test="/bx/bxco:config/wysiwyg">setFocus(this);</xsl:when>
        <xsl:otherwise></xsl:otherwise>
        
    </xsl:choose>
    setFocusField(this);
</xsl:variable>


<xsl:variable name="updateWysiwyg">
   
    setFocusField(this);
</xsl:variable>

<!-- template form -->
<xsl:template name="form">
<xsl:param name="textcolor" select="'blackH5'"/>




<script language="JavaScript" type="text/javascript">
<xsl:comment>
<![CDATA[
var isMSIEWin = ((parseInt(navigator.appVersion) >= 4) && (navigator.appName == "Microsoft Internet Explorer") && navigator.platform != "MacPPC");

function openWindow(link,windowname,options,field) {

    window.open(link+'?field='+field,windowname,options);

}

function setFocusField(field) {
    focusOnField = field;
    if (isMSIEWin) {
        focusOnFieldSelection = document.selection.createRange().duplicate();
    }
}
]]>
//</xsl:comment>
</script>

<xsl:variable name="isSensitive">
<xsl:choose>
<xsl:when test="bxco:config/bxco:fields/@issensitive = 'true'">true</xsl:when>
<xsl:otherwise>false</xsl:otherwise>
</xsl:choose>
</xsl:variable>

<form name="Master" action="{$actionURL}" method="post" enctype="multipart/form-data" onsubmit="return bx_onSave({$isSensitive})">
<xsl:call-template name="submitButtons"/>

<div id="formTable">
<table class="bigUglyEditTable">
    <xsl:for-each select="bxco:config/bxco:fields/bxco:field">
    <xsl:variable name="tag" ><xsl:value-of select="@name"/></xsl:variable>
    <xsl:variable name="thisField" select="/bx/master/master/*[name() = $tag]"/>
    <xsl:variable name="value">
        <xsl:choose>
        <!-- das is pseudoxpath zeugs ;) man kann im stil von session/username was angeben. e-->
         <xsl:when test="$thisField/bxco:onnew/@defaultXpath">
            <xsl:value-of select="//*[name() =substring-before($thisField/bxco:onnew/@defaultXpath,'/')]/*[name() = substring-after($thisField/bxco:onnew/@defaultXpath,'/')]"/>
        </xsl:when>
        <xsl:when test="$thisField/@defaultXpath">
            <xsl:value-of select="//*[name() =substring-before($thisField/@defaultXpath,'/')]/*[name() = substring-after($thisField/@defaultXpath,'/')]"/>
        </xsl:when>
        <!-- onnew steht nur im master wenn kein datensatz gewï¿½hlt wurde, da dann das config.xml als master eingefï¿½gt wird -->
        <xsl:when test="$thisField/bxco:onnew/@default">
            <xsl:value-of select="$thisField/bxco:onnew/@default"/>
        </xsl:when>
        <!-- einfache syntax mit <fieldname default="blabal" geht auch -->
        <xsl:when test="$thisField/@default">
            <xsl:value-of select="$thisField/@default"/>
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="$thisField/text()"/>
        </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
   
    <tr>

<xsl:choose>
<xsl:when test="@type = 'descr'">
<td class="{$textcolor}" colspan="2"><hr noshade="noshade"/><xsl:value-of disable-output-escaping="yes" select="@descr"/><hr noshade="noshade"/></td>
</xsl:when>
<xsl:when test="@type = 'hr'">
<td class="{$textcolor}" colspan="2" > <hr noshade="noshade"/></td>
</xsl:when>

<xsl:otherwise>
 <td>
    
           <xsl:if test="@type != 'hidden' and @type != 'notindb' ">
            <xsl:choose>
            <xsl:when test="@descr">
                   <div class="{$textcolor}"> <xsl:value-of select="@descr"/> </div>
                </xsl:when>
               <xsl:otherwise>
                 <div class="{$textcolor}"> <xsl:value-of select="$tag"/> </div>
              </xsl:otherwise>
             </xsl:choose>
           </xsl:if >
	</td><td class="{$textcolor}">

            <xsl:choose>
        	<xsl:when test="deniedgroups = /bx/session/usergroups">

                     <xsl:call-template name="fixed">
                        <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
                        <xsl:with-param name="value" select="$value"/>
                        <xsl:with-param name="nohidden" select="1"/>
                    <xsl:with-param name="noaccess" select="1"/>
                </xsl:call-template>

           </xsl:when>
         		<xsl:when test=" readonlygroups = /bx/session/usergroups">
                    <xsl:call-template name="fixed">
                    <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
                    <xsl:with-param name="value" select="$value"/>
                    <xsl:with-param name="nohidden" select="1"/>
                </xsl:call-template>
           </xsl:when>

			<xsl:when test=" ($tag = 'lang' and @type = 'text') or (@type = 'lang')">

               <xsl:call-template name="languages">
                <xsl:with-param name="id"><xsl:value-of select="$value"/></xsl:with-param>
			   <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
		   </xsl:call-template>
			</xsl:when>

			<xsl:when test=" @type = 'select'">
				<xsl:variable name="texts">
					<xsl:choose>
				   	   <xsl:when test="langinfo"><xsl:value-of select="langinfo/@texts"/></xsl:when>
					   <xsl:otherwise><xsl:value-of select="bxco:options/@texts"/></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				<xsl:variable name="values">
					<xsl:choose>
				   		<xsl:when test="langinfo"><xsl:value-of select="langinfo/@values"/></xsl:when>
					   <xsl:otherwise><xsl:value-of select="bxco:options/@values"/></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
            
			   <xsl:call-template name="select">
               <xsl:with-param name="id"><xsl:value-of select="$value"/></xsl:with-param>
			   <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
			   <xsl:with-param name="texts" select="$texts"/>
           	   <xsl:with-param name="values" select="$values"/>

		   </xsl:call-template>
			</xsl:when>

		   <xsl:when test="@type = 'imageformats'">
                <xsl:call-template name="imageformats">
                <xsl:with-param name="id"><xsl:value-of select="$value"/></xsl:with-param>
                <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
                </xsl:call-template>
           </xsl:when>



			<xsl:when test="@type = 'text' or @type = 'int' or @type = 'md5'">
                <xsl:call-template name="inputtext">
                <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
                <xsl:with-param name="value" select="$value"/>
                </xsl:call-template>
           </xsl:when>
           
           <xsl:when test=" @type = 'password'">
                <xsl:call-template name="inputtext">
                <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
                </xsl:call-template>
           </xsl:when>

           <xsl:when test="@type = 'date'">
			   <xsl:call-template name="date">
				   <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
				   <xsl:with-param name="value" select="$value"/>
			   </xsl:call-template>
           </xsl:when>

           <xsl:when test="@type = 'time'">
			   <xsl:call-template name="time">
				   <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
				   <xsl:with-param name="value" select="$value"/>
			   </xsl:call-template>
           </xsl:when>

			<xsl:when test="@type = 'datetime'">
			   <xsl:call-template name="datetime">
				   <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
				   <xsl:with-param name="value" select="$value"/>
			   </xsl:call-template>
           </xsl:when>
		   
		   <xsl:when test="@type = 'smalltextarea'">
                <xsl:call-template name="textarea">
                <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
                <xsl:with-param name="value" select="$value"/>
                <xsl:with-param name="rows" select="3"/>				
                </xsl:call-template>
           </xsl:when>

		   <xsl:when test="@type = 'textarea'">
                <xsl:call-template name="textarea">
                <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
                <xsl:with-param name="value" select="$value"/>
                </xsl:call-template>
           </xsl:when>

		   <xsl:when test="contains($tag,'abstract')  or @type = 'abstract'">
                <xsl:call-template name="textareaabstract">
                <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
                <xsl:with-param name="value" select="$value"/>
                </xsl:call-template>
           </xsl:when>

      

		   <xsl:when test="@type = 'hidden' or @type = 'notindb'">
                <xsl:call-template name="hidden">
                <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
                <xsl:with-param name="value" select="$value"/>
                </xsl:call-template>
           </xsl:when>
    	   <xsl:when test="@type = 'fixed'">
                <xsl:call-template name="fixed">
                <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
                <xsl:with-param name="value" select="$value"/>
                </xsl:call-template>
           </xsl:when>
           <xsl:when test="@type = 'descr'">
            
           </xsl:when>
		   <xsl:when test="@type = 'file'">
                <xsl:call-template name="file">
                <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
                </xsl:call-template>
           </xsl:when>

		<xsl:when test="@type = 'checkbox'">
        <xsl:call-template name="checkbox">
                <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
                 <xsl:with-param name="value" select="$value"/>
       </xsl:call-template>
       </xsl:when>
       
       <xsl:when test="@type = 'active'">
            <xsl:call-template name="active">
                <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
                 <xsl:with-param name="value" select="$value"/>
                 <xsl:with-param name="node" select="."/>
            </xsl:call-template>
       </xsl:when>
        
       <xsl:when test="@type = 'archive'">
           <xsl:call-template name="select">

               <xsl:with-param name="id"><xsl:value-of select="$value"/></xsl:with-param>
			   <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
               <xsl:with-param name="texts" select="'active|archived|'"/>
               <xsl:with-param name="values" select="'1|4|'"/>
		   </xsl:call-template>

       </xsl:when>

        <!-- the rang type does not work yet -->        
        <xsl:when test="@type = 'rang'">

        (<xsl:value-of select="$value"/>) <xsl:text> </xsl:text>
        Show After: <select onfocus="{$setFocus}"  style="width: 300px;"
onchange="{$updateWysiwyg}" id="{$tag}" class="{$textcolor}" name="{$tag}">
    <option value="0">On Top</option>
    <xsl:variable name="masterId" select="number(/bx/master/master/id)"/>
            <xsl:for-each  select="/bx/chooser/chooser">
             <xsl:sort data-type="number" select="rang"/>
                <option value="{rang}">
                    <xsl:choose>
                    <xsl:when test="id = $masterId">
                        <xsl:attribute name="selected">selected</xsl:attribute>
                        -- Choose to move --
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="rang"/> - <xsl:value-of select="chooserfield"/>
                    </xsl:otherwise>
                    </xsl:choose>
                    
                    
                </option>
            </xsl:for-each>
        </select>

        </xsl:when>
        
       <xsl:when test="@type = 'foreign'">

                <xsl:call-template name="foreign">
                <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
                <xsl:with-param name="id"><xsl:value-of select="$value"/></xsl:with-param>
                </xsl:call-template>
           </xsl:when>
<xsl:when test="@type = '12m'">


<div class="blackH5">
                <xsl:variable name="table"><xsl:value-of select="bxco:foreign/@table"/></xsl:variable>
                   <xsl:call-template name="one2m">
                            <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
                            <xsl:with-param name="id"></xsl:with-param>
                            <xsl:with-param name="table"><xsl:value-of select="$table"/></xsl:with-param>
                            <xsl:with-param name="thisfieldvalue"><xsl:value-of select="/bx/master/master/*[name() = current()/@thisfield]"/></xsl:with-param>
                            <xsl:with-param name="thatfield"><xsl:value-of select="@thatfield"/></xsl:with-param>
                         </xsl:call-template>
 
 </div>
 </xsl:when>


           <xsl:when test="@type = 'n2m'">
                 <xsl:variable name="thatfield"><xsl:value-of select="bxco:n2m/@thatfield"/></xsl:variable>
				<xsl:variable name="subtype"><xsl:value-of select="@subtype"/></xsl:variable>
                <div class="blackH5">
                 <xsl:choose>
                 <xsl:when test="$subtype = 'checkboxes'">
          
                       <xsl:variable name="table"><xsl:value-of select="bxco:foreign/bxco:table/@name"/></xsl:variable>
        	        <xsl:variable name="field"><xsl:value-of select="bxco:foreign/bxco:table/@field"/></xsl:variable>
                   <xsl:call-template name="n2mAllInCheckboxes">
                            <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
                            <xsl:with-param name="id"></xsl:with-param>
                            <xsl:with-param name="table"><xsl:value-of select="$table"/></xsl:with-param>
                            <xsl:with-param name="field"><xsl:value-of select="$field"/></xsl:with-param>
                            <xsl:with-param name="thatfield"><xsl:value-of select="$thatfield"/></xsl:with-param>
                         </xsl:call-template>
 
      
                 </xsl:when>
                 <xsl:otherwise>
                <xsl:for-each select="bxco:foreign/bxco:table">
	                <xsl:if test="count(../*) &gt; 1">
    		            <xsl:value-of select="@name"/><br/>
	                </xsl:if>

    	            <xsl:variable name="table"><xsl:value-of select="@name"/></xsl:variable>
        	        <xsl:variable name="field"><xsl:value-of select="./@field"/></xsl:variable>


                <xsl:if test="/bx/master/master/*[name() = $tag]/id/text()">
                <table cellspacing="0" cellpadding="0">
                    <xsl:for-each select="/bx/master/master/*[name() = $tag]">

					<xsl:choose>
						<xsl:when test="$subtype='full'">
							<xsl:call-template name="n2m">
								<xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
								<xsl:with-param name="id"><xsl:value-of select="id/text()"/></xsl:with-param>
								<xsl:with-param name="table"><xsl:value-of select="$table"/></xsl:with-param>
								<xsl:with-param name="field"><xsl:value-of select="$field"/></xsl:with-param>
								<xsl:with-param name="thatfieldid"><xsl:value-of select="*[name() = $thatfield]"/></xsl:with-param>
								<xsl:with-param name="objectname"><xsl:value-of select="objectname/text()"/></xsl:with-param>
							</xsl:call-template>
                       	</xsl:when>

                       	<xsl:otherwise>

							<xsl:call-template name="n2mCheckbox">
								<xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
								<xsl:with-param name="id"><xsl:value-of select="id/text()"/></xsl:with-param>
								<xsl:with-param name="table"><xsl:value-of select="$table"/></xsl:with-param>
								<xsl:with-param name="field"><xsl:value-of select="$field"/></xsl:with-param>
								<xsl:with-param name="thatfieldid"><xsl:value-of select="*[name() = $thatfield]"/></xsl:with-param>
								<xsl:with-param name="objectname"><xsl:value-of select="objectname/text()"/></xsl:with-param>
							</xsl:call-template>

						</xsl:otherwise>
					</xsl:choose>

                    </xsl:for-each>
                    </table>
                </xsl:if>

                <!-- call the same thing again, for inserting stuff but only if it's not a new entry,
                    then we can't insert n2m stuff right now-->

               <xsl:choose>

                    <xsl:when test="/bx/master/master/id or /bx/bxco:config/bxco:create/bxco:table[@name = $tag]">
                         <xsl:call-template name="n2m">
                            <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
                            <xsl:with-param name="id"></xsl:with-param>
                            <xsl:with-param name="table"><xsl:value-of select="$table"/></xsl:with-param>
                            <xsl:with-param name="field"><xsl:value-of select="$field"/></xsl:with-param>
                            <xsl:with-param name="thatfieldid"><xsl:value-of select="$value"/></xsl:with-param>
                         </xsl:call-template>
                    </xsl:when>
                    <xsl:otherwise>
                        Not yet possible, you have to first insert the main entry.
                    </xsl:otherwise>
                </xsl:choose>
                </xsl:for-each>
                </xsl:otherwise>
                </xsl:choose>
                </div>

           </xsl:when>


            <xsl:otherwise>
                Type not yet defined
            </xsl:otherwise>
        </xsl:choose>
        <xsl:if test="@popup">
        <a href="javascript:openWindow({@popup},'{name()}')">PopUp</a>
        </xsl:if>
    </td>

</xsl:otherwise>
</xsl:choose>



    </tr>

    </xsl:for-each>
  
    
    
</table>


  <xsl:if test="$isSensitive = 'true'">
  <br/>
  <table class="bigUglyEditTable">  
 <tr><td>
 This form contains sensitive information, please provide your password to make changes (The one you used for logging in to  the admin):<br/> 
 Your Password: <input type="password" name="_issensitive_password" value=""/>
 </td>
 </tr>
</table>
    </xsl:if>
</div>
<br/>
<input type="hidden" id="id" name="{$masteridfield}" value="{master/master/id}" />
<input type="hidden" name="update" value="1" />

<xsl:call-template name="submitButtons"/>
<!--<input type="button" onclick="xmlcheck();" value="XML Check"/>-->
<p/>

</form>
<!--<h3>Choose available <xsl:value-of select="bxco:config/fields/@table"/>: </h3>
-->

<p/>

&#160;



</xsl:template>


<!-- template checkbox -->
<xsl:template name="checkbox">
        <xsl:param name="tag" />
        <xsl:param name="value" />
        <input name="{$tag}" type="checkbox" class="blackH5checkBox" >
          <xsl:if test="$value > 0">
               <xsl:attribute name="checked">checked</xsl:attribute>
        </xsl:if>
          </input>

</xsl:template>

<!-- template active -->
<xsl:template name="active">

        <xsl:param name="tag" />
        <xsl:param name="value" />
        <xsl:param name="node" />        
          <xsl:call-template name="select">

               <xsl:with-param name="id"><xsl:value-of select="$value"/></xsl:with-param>
			   <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
               <xsl:with-param name="texts" select="'always active|active from till|'"/>
               <xsl:with-param name="values" select="'1|2|'"/>
               <xsl:with-param name="class" select="'length100'"/>
              
               <xsl:with-param name="nonetext" select="'not active'"/>               
               <xsl:with-param name="onchange">javascript:ShowHideFromTill('<xsl:value-of select="$tag"/>');</xsl:with-param>
		   </xsl:call-template>

        <xsl:if test="$node/bxco:from/@field">
         <xsl:variable name="blastyle">display:  <xsl:choose>
                    <xsl:when test="$value=2">block;</xsl:when>
                    <xsl:otherwise>none;</xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
        <div id="{$tag}fields" style="{$blastyle}">
        From:
            <xsl:call-template name="datetime">
                <xsl:with-param name="tag" select="$node/bxco:from/@field"/>
                <xsl:with-param name="value" select="/bx/master/master/*[name() = $node/bxco:from/@field]"/>
                <xsl:with-param name="size" select="'20'"/>
                <xsl:with-param name="class" select="'blackH5short'"/>
               
            </xsl:call-template>
        Till:

            <xsl:call-template name="datetime">
                <xsl:with-param name="tag" select="$node/bxco:till/@field"/>
                 <xsl:with-param name="value" select="/bx/master/master/*[name() = $node/bxco:till/@field]"/>                
                <xsl:with-param name="size" select="'20'"/>
                <xsl:with-param name="class" select="'blackH5short'"/>
                
            </xsl:call-template>
        </div>
           <script language="JavaScript">
          
           </script>
        </xsl:if>

</xsl:template>

<xsl:template name="archive">

        <xsl:param name="tag" />
        <xsl:param name="value" />
        <xsl:param name="node" />        
          <xsl:call-template name="select">

               <xsl:with-param name="id"><xsl:value-of select="$value"/></xsl:with-param>
			   <xsl:with-param name="tag"><xsl:value-of select="$tag"/></xsl:with-param>
               <xsl:with-param name="texts" select="'active|archived|'"/>
               <xsl:with-param name="values" select="'1|4|'"/>
               <xsl:with-param name="class" select="'length100'"/>
              
               <xsl:with-param name="nonetext" select="'not active'"/>               
		   </xsl:call-template>

</xsl:template>



<!-- template inputtext -->
<xsl:template name="inputtext">
        <xsl:param name="tag" />
        <xsl:param name="value" />
        <xsl:param name="size" select="'80'"/>
        <xsl:param name="class" select="'blackH5'"/>

        <input onfocus="{$setFocus}"  onmouseup="{$updateWysiwyg}" id="{$tag}"  class="{$class}" name="{$tag}"  size="{$size}" value="{$value}">
            <xsl:attribute name="onkeyup">
                <xsl:value-of select="$updateWysiwyg"/>
                <xsl:if test="@updateurifield">updateUriField("<xsl:for-each select="..//*[@updateurifield = current()/@updateurifield]">
                <xsl:value-of select="@name"/><xsl:if test="position() &lt; last()">,</xsl:if></xsl:for-each>",document.getElementById("<xsl:value-of select="@updateurifield"/>"));</xsl:if>
                this.edited=true;
            </xsl:attribute>
            <xsl:attribute name="type">
                <xsl:choose>
                    <xsl:when test="@type='md5' or @type='password'">password</xsl:when>
                    <xsl:otherwise>text</xsl:otherwise>
                </xsl:choose>
          </xsl:attribute>
        </input>
</xsl:template>

<!-- template date -->
<xsl:template name="date">

        <xsl:param name="tag" />
        <xsl:param name="value" />

        <input name="{$tag}" type="text" size="20">
        <xsl:attribute name="value">
			<xsl:choose>
					<xsl:when test="contains($value,'sql')">
						<xsl:value-of select="$value"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:call-template name="getDate">
							<xsl:with-param name="date" select="$value"/>
						</xsl:call-template>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
		</input>
        <a href="#" onClick="cal.select(document.forms.Master.{$tag},'anchor_{$tag}','dd.MM.yyyy'); return false;"  name="anchor_{$tag}" id="anchor_{$tag}">select</a>
<div id="caldiv" style="position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></div>
</xsl:template>

<!-- template time -->
<xsl:template name="time">
        <xsl:param name="tag" />
        <xsl:param name="value" />
        <input name="{$tag}" type="text" size="20">
			<xsl:attribute name="value">
				<xsl:choose>
					<xsl:when test="contains($value,'sql')">
						<xsl:value-of select="$value"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:call-template name="getTime">
							<xsl:with-param name="date" select="$value"/>
						</xsl:call-template>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
		</input>
</xsl:template>

<!-- template datetime -->
<xsl:template name="datetime">
        <xsl:param name="tag" />
        <xsl:param name="value" />
         <xsl:param name="style" />
        
        <xsl:param name="size" select="'20'"/>
        <xsl:param name="class" select="'blackH5short'"/>
                
        <input onfocus="{$setFocus}" onkeyup="{$updateWysiwyg}" onmouseup="{$updateWysiwyg}" id="{$tag}" class="{$class}" name="{$tag}" type="text" style="{$style}" size="{$size}">
        <xsl:attribute name="value">
        	<xsl:call-template name="getDateTime">
				<xsl:with-param name="date" select="$value"/>
			</xsl:call-template>

		</xsl:attribute>
		</input>
         <a href="#" onClick="cal.select(document.forms.Master.{$tag},'anchor_{$tag}','dd.MM.yyyy 00:00:00'); return false;"  name="anchor_{$tag}" 
         id="anchor_{$tag}">select</a>
<div id="caldiv" style="position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></div>
</xsl:template>




<!-- template hidden -->
<xsl:template name="hidden">
        <xsl:param name="tag" />
        <xsl:param name="value" />
        <input name="{$tag}" type="hidden" >
		<!-- checken if onchange is set, onchange macht wirklich nur bei fixed und hidden feldern sinn  -->
			<xsl:attribute name="value">
				<xsl:choose>
					 <xsl:when test="bxco:onchange/@default">
						<xsl:value-of select="onchange/@default"/>
					 </xsl:when>
					<xsl:when test="bxco:onchange/@defaultXpath">
						<xsl:value-of select="/bx/*[name() =substring-before(/bx/bxco:config/bxco:fields/bxco:field[@name = $tag]/bxco:onchange/@defaultXpath,'/')]/*[name() = substring-after(/bx/bxco:config/bxco:fields/bxco:field[@name = $tag]/bxco:onchange/@defaultXpath,'/')]"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="$value"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
        </input>
</xsl:template>

<!-- template fixed -->
<xsl:template name="fixed">

        <xsl:param name="tag" />
        <xsl:param name="value" />
        <xsl:param name="nohidden" />
        <xsl:param name="noaccess" />

        <xsl:if test="not($nohidden > 0) or /bx/master/master/*[name() = $tag]/@type or bxco:onchange">
        <input name="{$tag}" type="hidden" >
        <!-- checken if onchange is set, onchange macht wirklich nur bei fixed und hidden feldern sinn -->
            <xsl:attribute name="value">
                <xsl:choose>
                     <xsl:when test="bxco:onchange/@default">
                        <xsl:value-of select="bxco:onchange/@default"/>
                     </xsl:when>
                    <xsl:when test="bxco:onchange/@defaultXpath">
            			<xsl:value-of select="/bx/*[name() =substring-before(/bx/bxco:config/bxco:fields/bxco:field[@name = $tag]/bxco:onchange/@defaultXpath,'/')]/*[name() = substring-after(/bx/bxco:config/bxco:fields/bxco:field[@name = $tag]/bxco:onchange/@defaultXpath,'/')]"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="$value"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>
        </input>
        </xsl:if>
	<div class="blackH5">
		<xsl:choose>
         <xsl:when test="$noaccess &gt; 0">
                no access
    	    </xsl:when>
			<xsl:when test="@subtype = 'datetime'">
				<xsl:call-template name="getDateTime">
					<xsl:with-param name="date" select="$value"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:when test="@subtype = 'time'">
				<xsl:call-template name="getTime">
					<xsl:with-param name="date" select="$value"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:when test="@subtype = 'date'">
				<xsl:call-template name="getDate">
					<xsl:with-param name="date" select="$value"/>
				</xsl:call-template>
			</xsl:when>
            <xsl:when test="@type = 'foreign'">
                 <xsl:variable name="table"><xsl:value-of select="bxco:foreign/@table"/></xsl:variable>
                 <xsl:variable name="field"><xsl:value-of select="bxco:foreign/@field"/></xsl:variable>
                 <xsl:value-of select="/bx/*[name() = $table]/*[name() = $table]/*[name() = $field and ../id = $value]"/>
			</xsl:when>
            <xsl:when test="@type = 'file' and string-length(normalize-space(/bx/master/master/*[name() = $tag]/text()))>0">

               <a class="blackH5" target="file" href="{/bx/bxco:config/bxco:fields/@downloaddir}{/bx/master/master/id}.{/bx/master/master/*[name() = $tag]}"><xsl:value-of select="/bx/master/master/*[name() = $tag]"  /></a>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$value"/>
			</xsl:otherwise>
		</xsl:choose>
	</div>

</xsl:template>

<!-- template textareaabstract-->
<xsl:template name="textareaabstract">
        <xsl:param name="tag" />
        <xsl:param name="value" />

       
        <textarea  onfocus="{$setFocus}" onkeyup="{$updateWysiwyg}; " onmouseup="{$updateWysiwyg}" id="{$tag}"  class="blackH5"  name="{$tag}" rows="10" cols="80" wrap="virtual"><xsl:value-of select="$value"  /></textarea>

</xsl:template>


<!-- template textarea -->
<xsl:template name="textarea">
        <xsl:param name="tag" />
        <xsl:param name="value" />
       <xsl:param name="rows">20</xsl:param>
       
       <xsl:choose>
       <xsl:when test="@subtype = 'mozile' and $isMozilla = 'true'">
       <a class="buttonStyleActive" id="mozile_wysiwyg_{$tag}" href="javascript:bx_toggleSource('{$tag}')">Wysiwyg</a>
       <a class="buttonStyle" id="mozile_source_{$tag}" href="javascript:bx_toggleSource('{$tag}')">Source</a>

       <div id="mozile_{$tag}"    contentEditable="true" >
<!-- onkeyup does not work -->

       <xsl:attribute name="onmouseup">
<xsl:value-of select="$updateWysiwyg"/>
<xsl:if test="@updateteaserfield">
updateTeaserField(this,document.getElementById("<xsl:value-of select="@updateteaserfield"/>"));
</xsl:if>
</xsl:attribute>
       <xsl:choose>
       <xsl:when test="string-length($value) = 0">
       &#160;
       <xsl:value-of disable-output-escaping="yes" select="$value"/>
       </xsl:when>
       <xsl:otherwise>
       <xsl:value-of disable-output-escaping="yes" select="$value"/>
       </xsl:otherwise>
       </xsl:choose>
       
       </div>
    </xsl:when>
       </xsl:choose>
       
        <textarea onfocus="{$setFocus}" onmouseup="{$updateWysiwyg}" id="{$tag}" class="blackH5" wrap="virtual" name="{$tag}" rows="{$rows}" cols="80">  
        <xsl:if test="@subtype='mozile'  and $isMozilla = 'true'">
        <xsl:attribute name="style">display: none;</xsl:attribute>
        </xsl:if>
        <xsl:attribute name="onkeyup">
<xsl:value-of select="$updateWysiwyg"/>
<xsl:if test="@updateteaserfield">
updateTeaserField(this,document.getElementById("<xsl:value-of select="@updateteaserfield"/>"));
</xsl:if>
this.edited = true;
</xsl:attribute>
          <xsl:value-of select="$value"/></textarea>
</xsl:template>

<!-- template file -->
<xsl:template name="file">
        <xsl:param name="tag" />
<div  style="float:right; ">
        <img onmouseout="document.getElementById('_image{$tag}').style.display = 'none';"  onmouseover="document.getElementById('_image{$tag}').style.display = 'block';"  height="30" src="{/bx/bxco:config/bxco:fields/@downloaddir}{/bx/master/master/id}.{/bx/master/master/*[name() = $tag]}"/>
        <div style="float:right;left: 350px; border: 5px solid white; position: absolute; display: none;" id="_image{$tag}">
        <img src="{/bx/bxco:config/bxco:fields/@downloaddir}{/bx/master/master/id}.{/bx/master/master/*[name() = $tag]}"/>
        </div>

        </div>
<input name="{$tag}[]" TYPE="file" size="35" />
    
        
    
    <br/>
    
    <xsl:if test="string-length(normalize-space(/bx/master/master/*[name() = $tag]/text()))>0">
	<a class="blackH5" target="file" href="{/bx/bxco:config/bxco:fields/@downloaddir}{/bx/master/master/id}.{/bx/master/master/*[name() = $tag]}"><xsl:value-of select="/bx/master/master/*[name() = $tag]" /></a>
	<input name="{$tag}_delete" TYPE="checkbox" /> löschen<br/>

	
    </xsl:if>
	<input name="{$tag}_old" id="{$tag}" type="hidden" value="{/bx/master/master/*[name() = $tag]}" />
</xsl:template>





<!-- template  "foreign" -->
<xsl:template name="foreign">
        <xsl:param name="tag" />
        <xsl:param name="id" />

        <xsl:variable name="table"><xsl:value-of select="bxco:foreign/@table"/></xsl:variable>
        <xsl:variable name="field"><xsl:value-of select="bxco:foreign/@field"/></xsl:variable>
        <xsl:variable name="thatfield">
            <xsl:choose>
                <xsl:when test="bxco:foreign/@thatfield"><xsl:value-of select="bxco:foreign/@thatfield"/></xsl:when>
                <xsl:otherwise>id</xsl:otherwise>       
            </xsl:choose>
         </xsl:variable>
         
        
        <select  class="blackH5" name="{$tag}">
            <option value="0">
             None
            </option>
            <xsl:for-each select="/bx/*[name() = $table]/*[name() = $table]/chooserfield">
                <option value="{../*[name() = $thatfield]}">
                    <xsl:if test="../*[name() = $thatfield] = $id">
                        <xsl:attribute name="selected">selected</xsl:attribute>
                    </xsl:if>
                    <xsl:value-of select="."/>
                </option>
            </xsl:for-each>
        </select>
        <xsl:for-each select="/bx/*[name() = $table]/*[name() = $table]/*[name() = $thatfield and text() = $id ]">
        <xsl:if test = "position() = 1">
            <a href="../{$table}/?id={../id}" class="blackH5">-&gt;</a>
            </xsl:if>
        </xsl:for-each>
</xsl:template>

<!-- template  "n2m" -->
<xsl:template name="n2m">
        <!-- the name of the tag in the config.xml file, used for the name of the select-tag -->
        <xsl:param name="tag" />
        <!-- the id of this entry in the n2m-table -->
        <xsl:param name="id" />
        <!-- the field name of the m-table which should be shown in the options -->
        <xsl:param name="field" />
        <!-- the name of the m-table-->
        <xsl:param name="table" />
        <!-- the name of the m-table-reference-field in the n2m-table -->
        <xsl:param name="thatfieldid" />
        <xsl:param name="objectname" />

        <xsl:if test="string-length($objectname)=0 or $objectname = $table">
        <select class="blackH5" name="{$tag}[{$table}][{$id}]">
            <option value="0">
                -- Choose to add --
            </option>


            <xsl:for-each select="/bx/*[name() = $table]/*[name() = $table]/chooserfield">

                <option value="{../id}">
                    <xsl:if test="../id = $thatfieldid">
                        <xsl:attribute name="selected">selected</xsl:attribute>
                    </xsl:if>

                    <xsl:value-of select="."/>
                </option>
            </xsl:for-each>

        </select><br/>
        </xsl:if>
</xsl:template>

<xsl:template name="one2m">
        <!-- the name of the tag in the config.xml file, used for the name of the select-tag -->
        <xsl:param name="thisfieldvalue" />
        <xsl:param name="table" />
        <!-- the name of the m-table-reference-field in the n2m-table -->
        <xsl:param name="thatfield" />
      

            <xsl:for-each select="/bx/*[name() = $table]/*[name() = $table]/*[name() = $thatfield]">
            <xsl:if test=". = $thisfieldvalue">

                <a href="../{$table}/?id={../id}">
              

                    <xsl:value-of select="../chooserfield"/>
               </a><br/>
               </xsl:if>
            </xsl:for-each>
           -&gt; <a href="../{$table}/"><xsl:value-of select="$table"/> Overview</a><br/>

        
</xsl:template>

<xsl:template name="n2mAllInCheckboxes">

        <!-- the name of the tag in the config.xml file, used for the name of the select-tag -->
        <xsl:param name="tag" />
        <!-- the id of this entry in the n2m-table -->
        <xsl:param name="id" />
        <!-- the field name of the m-table which should be shown in the options -->
        <xsl:param name="field" />
        <!-- the name of the m-table-->
        <xsl:param name="table" />
        <!-- the name of the m-table-reference-field in the n2m-table -->
        <xsl:param name="thatfieldid" />
        <xsl:param name="objectname" />
        <xsl:param name="thatfield"/>
<input type="hidden" name="{$tag}[{$table}][{../id}]" value="0"/>
        <xsl:if test="string-length($objectname)=0 or $objectname = $table">
            <xsl:for-each select="/bx/*[name() = $table]/*[name() = $table]/chooserfield">
                <input type="checkbox" name="{$tag}[{$table}][{../id}]" value="{../id}">
           
                     <xsl:if test="../id = /bx/master/master/*[name() = $tag]/*[name() = $thatfield]">
                        <xsl:attribute name="checked">checked</xsl:attribute>
                    </xsl:if>
                    </input>

                    <a href="../{$table}/?id={../id}"><xsl:value-of select="."/></a>
<br/>
                    </xsl:for-each>

    
        </xsl:if>
</xsl:template>
<!-- template  "n2mCheckbox" -->
<xsl:template name="n2mCheckbox">
        <!-- the name of the tag in the config.xml file, used for the name of the select-tag -->
        <xsl:param name="tag" />
        <!-- the id of this entry in the n2m-table -->
        <xsl:param name="id" />
        <!-- the field name of the m-table which should be shown in the options -->
        <xsl:param name="field" />
        <!-- the name of the m-table-->
        <xsl:param name="table" />
        <!-- the name of the m-table-reference-field in the n2m-table -->
        <xsl:param name="thatfieldid" />
        <xsl:param name="objectname" />
<!-- the preceding sibling check is because it can happen, that the same entries appear more than once, due to bad left join stuff -->
        <xsl:if test="(string-length($objectname)=0 or $objectname = $table or $objectname=/bx/bxco:config/bxco:fields/@table) and not(preceding-sibling::*[name() = $tag and id=$id ] )">
<tr><td valign="bottom" >

	        <a class="blackH5" href="../{$table}/?id={$thatfieldid}"><xsl:value-of select="/bx/*[name() = $table]/*[name() = $table]/chooserfield[../id = $thatfieldid]"/></a>
        </td><td valign="bottom" class="blackH5">&#160;Del: <input type="checkbox" class="blackH5checkBox" name="{$tag}[{$table}][{$id}]"/></td></tr>    
        
        </xsl:if>
</xsl:template>




<xsl:template name="select">
    <xsl:param name="id" />
    <xsl:param name="tag" />
    <xsl:param name="texts" />
    <xsl:param name="values" />
    <xsl:param name="class" select="'blackH5'"/>
    <xsl:param name="onchange" />
   <xsl:param name="nonetext" select="'none'"/>
   
    <select id="{$tag}" class="{$class}" size="1" name="{$tag}">
    <xsl:if test="$onchange">
        <xsl:attribute name="onchange"><xsl:value-of select="$onchange"/></xsl:attribute>
    </xsl:if>

    <option value="0"><xsl:value-of select="$nonetext"/></option>
    <xsl:call-template name="SelectMaker">
                    <xsl:with-param name="texts" select="$texts"/>
                    <xsl:with-param name="values" select="$values"/>
                    <xsl:with-param name="id" select="$id"/>
     </xsl:call-template>

    </select>
</xsl:template>


<!-- template "chooser", form 2-->

<xsl:template name="chooser">
        <xsl:param name="id" />
        <xsl:param name="textcolor" />        
<form name="chooser" action="{$actionURL}" style="margin: 0pt;">

           <!--   <xsl:if test="bxco:config/chooser/@descr">
                  <font class="{$textcolor}">
 <xsl:value-of select="bxco:config/chooser/@descr"/>
 </font>
               </xsl:if>-->
      
       
     <select class="chooser" name="{$masteridfield}"  onChange="document.chooser.submit()" >

            <option value="0">
                None
            </option>
            <xsl:for-each select="/bx/chooser/chooser/chooserfield">
                <option value="{../id}">
                    <xsl:if test="../id = $id">
                        <xsl:attribute name="selected">selected</xsl:attribute>
                    </xsl:if>

                    <xsl:value-of select="."/>
                </option>
            </xsl:for-each>
        </select>

</form>
</xsl:template>

<xsl:template name="getDate">
	<xsl:param name="date"/>
	<xsl:choose>
		<xsl:when test="string-length($date) &lt; 5">
					<xsl:value-of select="$date"/>
		</xsl:when>
		<xsl:when test="contains($date,'-')">
			<xsl:value-of select="substring($date,9,2)"/>.<xsl:value-of select="substring($date,6,2)"/>.<xsl:value-of select="substring($date,1,4)"/>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select="substring($date,7,2)"/>.<xsl:value-of select="substring($date,5,2)"/>.<xsl:value-of select="substring($date,1,4)"/>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template name="getTime">
	<xsl:param name="date"/>
	<xsl:choose>
		<xsl:when test="string-length($date) &lt; 5">
				<xsl:value-of select="$date"/>
		</xsl:when>
		<xsl:when test="contains($date,'-')">
			<xsl:value-of select="substring($date,12,2)"/>:<xsl:value-of select="substring($date,15,2)"/>:<xsl:value-of select="substring($date,18,4)"/>
		</xsl:when>
		<xsl:when test="string-length($date)>8">
			<xsl:value-of select="substring($date,9,2)"/>:<xsl:value-of select="substring($date,11,2)"/>:<xsl:value-of select="substring($date,13,2)"/>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select="substring($date,1,2)"/>:<xsl:value-of select="substring($date,4,2)"/>:<xsl:value-of select="substring($date,7,2)"/>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template name="getDateTime">
	<xsl:param name="date"/>
        	<xsl:choose>
        		<xsl:when test="contains($date,'sql')">
					<xsl:value-of select="$date"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:call-template name="getDate">
						<xsl:with-param name="date" select="$date"/>
					</xsl:call-template>
					<xsl:text> </xsl:text>
					<xsl:call-template name="getTime">
						<xsl:with-param name="date" select="$date"/>
					</xsl:call-template>
				</xsl:otherwise>
			</xsl:choose>
</xsl:template>
<xsl:template name="submitButtons">
&#160;<input accesskey="s" type="submit" value="Save Entry"/>&#160;
<input type="reset" value="Reset"/>&#160;

<xsl:if test="not(bxco:config/bxco:fields/@newEntry and  bxco:config/bxco:fields/@newEntry = 'false') ">
<!--<a href="./?new=1"><span class="blackH5">Make new <xsl:value-of select="bxco:config/bxco:fields/@table"/>-Entry</span></a>-->
<input type="button" name="_notindb" value="New Entry" onclick="javascript:window.location.href='./?new=1'; "/>
</xsl:if>

<xsl:choose>
<!-- if old entry make delete button -->
    <xsl:when test="master/master/id">
        <xsl:if test="not(/bx/bxco:config/bxco:fields/@deleteEntry and /bx/bxco:config/bxco:fields/@deleteEntry = 'false')">
        &#160;<input type="submit" name="delete" value="Delete Entry"/>
        
        
        </xsl:if>
    </xsl:when>
<!-- otherwise it's a new entry, make create other tables if they are available -->
    <xsl:when test="/bx/bxco:config/bxco:create/bxco:table">

    <xsl:if test="count(/bx/bxco:config/bxco:create/bxco:table[not(@hide = 'yes')]) > 0">

        <br/><br/><font class="blackH5">&#160;Create also entries in the following tables:</font>
    </xsl:if>



        <table>
        
        <xsl:for-each select="/bx/bxco:config/bxco:create/bxco:table">

            <xsl:choose>
                <xsl:when test="@hide = 'yes'">
                    <input type="hidden"  name="create[{@name}]" value="on"/>
                </xsl:when>
                <xsl:otherwise>                         
                    <tr><td class="blackH5">
                    <input type="checkbox" class="blackH5checkBox" name="create[{@name}]" checked="checked"/> <xsl:value-of select="@name"/>
                    </td></tr>
                </xsl:otherwise>

            </xsl:choose>

        </xsl:for-each>
        </table>
    </xsl:when>
</xsl:choose>
</xsl:template>
</xsl:stylesheet>

