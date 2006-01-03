<?xml version="1.0" encoding="ISO-8859-1"?>
<!-- $Id $ -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
xmlns="http://www.w3.org/1999/xhtml"
xmlns:xlink="http://www.w3.org/1999/xlink"
xmlns:bxco="http://bitflux.org/config/1.0"
>

<xsl:include href="./form.xsl" />
<xsl:param name="isMozilla" select="'false'"/>
<xsl:output method="xml" indent="yes" encoding="UTF-8"  />
  <xsl:variable name="textcolor">
  		<xsl:text>blackH5</xsl:text>
  </xsl:variable>

<xsl:template match="/bx">

<xsl:variable name="backgroundcolor">
	<xsl:choose>
		<xsl:when test="bxco:config/bxco:fields/@table='Section'">
  		<xsl:text>#EEEEEE</xsl:text>
		</xsl:when>
		<xsl:when test="bxco:config/bxco:fields/@table='Document'">
  		<xsl:text>#DDDDDD</xsl:text>
		</xsl:when>
		
		<xsl:when test="bxco:config/bxco:fields/@table='Imageobject' or bxco:config/bxco:fields/@table='Videorealobject'">
  		<xsl:text>#BBBBBB</xsl:text>
		</xsl:when>
		<xsl:otherwise><xsl:text>#CCCCCC</xsl:text>
		</xsl:otherwise>
  	</xsl:choose>
  </xsl:variable>
  

<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>Bitflux CMS Admin</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	 <!-- If we link to a script, it would come here
	 <script language="JavaScript" src="/clients/client_name/scripts/dw.js"></script> -->
	<link rel="stylesheet" href="../../webinc/plugins/dbform/css/admin.css" type="text/css" />
    
<script type="text/javascript" language="JavaScript" src="../../webinc/plugins/dbform/js/formxmlcheck.js"/>
<script type="text/javascript" language="JavaScript" src="../../webinc/js/sarissa_dbform.js"/>
<script type="text/javascript" language="JavaScript" src="../../webinc/js/formedit.js"/>
<xsl:if test="count(//bxco:field[@type='date' or @type='active' or @type='datetime']) > 0">
   <script type="text/javascript" language="JavaScript" src="../../webinc/js/CalendarPopup.js"/>

<script language="JavaScript" type="text/javascript">
<xsl:comment>

        document.write(getCalendarStyles());
      
        var cal = new CalendarPopup('caldiv');
        cal.showYearNavigation();
</xsl:comment>
</script>


</xsl:if>

<xsl:if test=" $isMozilla = 'true' and count(//bxco:field[@subtype='mozile'] ) > 0">
<script language="JavaScript" src="../../webinc/mozile/mozileLoader.js" type="text/javascript"/>
</xsl:if>
<!--<script language="JavaScript" src="../wysiwyg/common.js"></script>-->





 </head>
<!-- some table things should be changed and put into the css -->
  
  
  
  <body  >
  <!--  onload="changeToWysiwygLayer()"-->

  <!-- chooser table -->
<table cellspacing="0" width="700" border="0" id="topbar">
  <tr><td class="bgDarkGreen" >
   <table  border="0" cellpadding="0" cellspacing="0" width="100%" class="bgDarkGreen" id="subtopbar">
   <!-- platzhalter oben -->
    <tr class="chooser bgDarkGreen"><td  width="5%" ></td>
        <td  width="5%" >
<!--        <img src="../img/space.gif" height="2" width="1"/><br/>-->
		<img src="../../admin/webinc/img/icons/application/dbform.gif"/>

        </td>
        <td >
        <xsl:choose>
        <xsl:when test="bxco:config/bxco:fields/@showTableName">
             <xsl:value-of select="bxco:config/bxco:fields/@showTableName"/>
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="bxco:config/bxco:fields/@table"/>
            </xsl:otherwise>
           </xsl:choose>
         : &#160;
        </td>
        <td >  
<!--        <img src="../img/space.gif" height="2" width="1"/><br/>-->
            <xsl:call-template name="chooser" >
                <xsl:with-param name="textcolor" select="$textcolor"/>
                    <xsl:with-param name="id"><xsl:value-of select="master/master/id"/></xsl:with-param>
            </xsl:call-template>

        </td>
    </tr>
   <!-- platzhalter unten -->
<!--    <tr class="chooser"><td colspan="4"><img src="../img/space.gif" height="5" width="1"/><br/></td></tr>-->

    </table>
    </td></tr></table>
    <p/>
<!-- fields table -->    

<div id="formedit">

            <xsl:call-template name="form">
                <xsl:with-param name="textcolor" select="$textcolor"/>
            </xsl:call-template>

</div>



</body>
</html>





</xsl:template>



</xsl:stylesheet>


