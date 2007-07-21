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
<xsl:param name="url" select="'/'"/>
<xsl:param name="id" select="'/'"/>
<xsl:param name="dataUri" select="'/'"/>
<xsl:param name="webroot" select="'/'"/>
<xsl:template match="/"> 
		
    
  <script type="text/javascript">

    
    var path = '<xsl:value-of select="/bx/plugin/boxes/path" />';
    var scope = '<xsl:value-of select="/bx/plugin/boxes/scope[@selected = 'true']/@id" />';
    var lang = '<xsl:value-of select="/bx/plugin/boxes/lang[@selected = 'true']/@value" />';
    var setid = '<xsl:value-of select="/bx/plugin/boxes/setId" />';

    function initLists() {
	    initList(0);
	    initList(1);
	    initList(2);
	
    }

    function initList(nr) {
	    Sortable.create('list'+nr, {
		    dropOnEmpty:true,containment:["list0","list1","list2"],constraint:false,
            onUpdate:function() {
                saveList(nr);
	        }
	    });
    }
    
    function saveList(nr){
        var params = serializeList(nr);
        params += '&amp;bx[plugins][admin_edit][boxes][scope]=' + scope;
        params += '&amp;bx[plugins][admin_edit][boxes][lang]=' + lang;
        params += '&amp;bx[plugins][admin_edit][boxes][setid]=' + setid;
        
        var ajax = new Ajax.Request('./box-list-update/',{
           method:'post',
           parameters: params
        });        
        
    }

    function serializeList(nr) {
	    var params =  Sortable.serialize('list'+nr).replace(/list([0-9]+)\[\]=/g,'bx[plugins][admin_edit][boxes][list][$1][]=');
	    if (params.length == 0) {
		    return 	'bx[plugins][admin_edit][boxes][list]['+nr+']=';
	    }
	    return params;
    }
    
    function changePrefs(){
        var selObj = $('boxLang');
        var lang = selObj.options[selObj.selectedIndex].value;
        
        selObj = $('boxScope');
        var scope = selObj.options[selObj.selectedIndex].value;
        
        
        var loc = '/admin/edit' + path + '?editor=boxes&amp;lang=' + lang + '&amp;scope=' + scope;
        document.location.href = loc;
    }
    
  </script>   
  <style type="text/css">
    .sortable {
        min-height: 2.6em;
        border: 1px solid #006486;
        margin-left: 0px;
        padding: 2px;
    }
                    
    .sortableContainer {
        float: left;
        margin-right: 20px;
        min-width: 224px;   
     }
                            
     .sortable li {
        border: 1px solid #006486;
        margin-bottom: 2px;
        min-height: 1.4em;
        padding: 0.2em;
        vertical-align: middle;
        cursor: pointer;
        margin: 0px;
        padding: opx;
        border: 0px;
        width: 220px;
    }    
  </style>
  <html>
	  <head>
	       <title>Boxes Editor</title>
           <link rel="stylesheet" href="{$webroot}themes/standard/admin/css/formedit.css" type="text/css"/>
    	   <link rel="stylesheet" href="{$webroot}webinc/plugins/linklog/admin.css" type="text/css"/>
           
            <script src="/webinc/js/prototype.js" type="text/javascript"></script>
            <script src="/webinc/js/scriptaculous/scriptaculous.js?load=effects,dragdrop" type="text/javascript"></script>
            <script src="/webinc/js/scriptaculous/effects.js" type="text/javascript"></script>
            <script src="/webinc/js/scriptaculous/dragdrop.js" type="text/javascript"></script>
       </head>
       
       <body onload="initLists()">
            <table border="1">
                <tr>
                    <td width="130">
                        <form name="jumpLang">
                            Language: <select id="boxLang" onChange="changePrefs()">
                                <xsl:for-each select="/bx/plugin/boxes/lang">
                                   <option>
                                        <xsl:if test="@selected = 'true'">
                                            <xsl:attribute name="selected">selected</xsl:attribute>
                                        </xsl:if>
                                        <xsl:value-of select="@value" />
                                    </option>   
                                </xsl:for-each>
                            </select>
                        </form> 
                   </td>
                   <td width="180"> 
                        <form name="jumpScope">
                            Scope: <select id="boxScope" onChange="changePrefs()">
                                <xsl:for-each select="/bx/plugin/boxes/scope">
                                    <option>
                                        <xsl:if test="@selected = 'true'">
                                            <xsl:attribute name="selected">selected</xsl:attribute>
                                        </xsl:if>
                                         <xsl:attribute name="value"><xsl:value-of select="@id" /></xsl:attribute>
                                        <xsl:value-of select="@name" />
                                    </option>
                                </xsl:for-each>
                            </select>
                        </form> 
                   </td>
                   <td>
                        <a href="/admin/dbforms2/boxes/">Create new box</a>    
                   </td>
               </tr>
            </table>



                <div class="sortableContainer">
                    <ul id="list1" class="sortable">
                        <xsl:apply-templates select="/bx/plugin/boxes/box_1" />
                    </ul>
                </div>

                <div class="sortableContainer">
                    <ul id="list0" class="sortable">
                       <xsl:apply-templates select="/bx/plugin/boxes/allboxes" />
                    </ul>
                </div>

                <div class="sortableContainer">
                    <ul id="list2" class="sortable">
                        <xsl:apply-templates select="/bx/plugin/boxes/box_2" />
                    </ul>
                </div>    


        </body>
      </html>
    </xsl:template>

    <xsl:template match="box">
        <xsl:for-each select=".">
            <li>
                <xsl:attribute name="id">item_<xsl:value-of select="id" /></xsl:attribute>
                <xsl:value-of select="title" />
            </li>
        </xsl:for-each>    
    </xsl:template>

</xsl:stylesheet>
