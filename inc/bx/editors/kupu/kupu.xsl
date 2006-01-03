<?xml version="1.0"?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns:xhtml="http://www.w3.org/1999/xhtml" 
    xmlns:php="http://php.net/xsl"
    >
    
    <xsl:output encoding="utf-8" method="html" 
        doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" 
        doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"
    />
    
    <xsl:param name="url" select="'/'"/>
    <xsl:param name="dataUri" select="$dataUri"/>
    <xsl:param name="webroot" select="$webroot"/>
    <xsl:param name="requestUri" select="$requestUri"/>
    <xsl:param name="template" select="'default.xhtml'"/>
    
    <xsl:variable name="contentUri" select="concat($webroot, 'admin/content', $requestUri)"/>
    
    <xsl:template match="/">
        <xsl:apply-templates/>
    </xsl:template>


    <xsl:template match="*">
        <xsl:copy>
             
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
            
            <xsl:apply-templates/>
        
        </xsl:copy>
    </xsl:template>
    
    
    <xsl:template name="themeInit">
    <script type="text/javascript" language="JavaScript">
    var theme = '<xsl:value-of select="php:functionString('bx_helpers_config::getProperty','theme')"/>';
    var themeCss = '<xsl:value-of select="php:functionString('bx_helpers_config::getProperty','themeCss')"/>';
    </script>
    
    </xsl:template>

    <xsl:template match="/xhtml:html/xhtml:head">
      
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <xsl:call-template name="themeInit"/>
        <link href="{$webroot}/webinc/kupu/common/kupustyles.css" rel="stylesheet" type="text/css"/>
        <link href="{$webroot}/webinc/kupu/common/kupucustom.css" rel="stylesheet" type="text/css"/>
        <link href="{$webroot}/webinc/kupu/common/kupudrawerstyles.css" rel="stylesheet" type="text/css"/>
        
        <link rel="stylesheet" type="text/css" href="{$webroot}/themes/standard/admin/css/admin.css"/>
        
        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/sarissa.js"/>
        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/kupuhelpers.js"/>
        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/kupueditor.js"/>
        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/kupubasetools.js"/>
        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/kupuloggers.js"/>
        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/kupucontentfilters.js"/> 
        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/kupucontextmenu.js"/> 
        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/kupuinit_experimental.js"/>
        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/kupusaveonpart.js"/>
        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/kupusourceedit.js"/>
        <script type="text/javascript" src="{$webroot}/webinc/kupu/common/kupudrawers.js"/>
       
        
        <script type="text/javascript">
        
        var kupu = null;
        var kupuui = null;
        
       /* function BxContentFilter() {
            this.initialize = function(editor) {
                this.editor = editor;
            };
            
            this.filter = function(ownerdoc, htmlnode) {
                htmlnode = deInitBxContent(ownerdoc,htmlnode);
                return htmlnode;
            }
        }*/
        
        function kupuGetContent() {
                doc=kupu.getInnerDocument();
                xmlserializer=new XMLSerializer();
                alert(xmlserializer.serializeToString(doc));
             
        }
        
        function startKupu() {
            
            var frame = document.getElementById('kupu-editor'); 
            kupu = initKupu(frame); 
            kupuui = kupu.getTool('ui');
            kupu.initialize();
           // kupu.registerFilter(new BxContentFilter());
        }
        
        function initBxContent() {
        
            var css = document.getElementById('mastercss');
            var frame = document.getElementById('kupu-editor');
            var fdoc = frame.contentWindow.document;
            var frameHead = fdoc.getElementsByTagName("head")[0];
            if (_SARISSA_IS_MOZ) { 
                var res = fdoc.evaluate("/html/body//*[@_moz-userdefined]",fdoc,null, 0, null);
                if (res.iterateNext()) {
                    alert("This document contains non-HTML elements, please consider using the oneform or BXE editor.\nSaving in kupu will remove those elements and lead to unexpected results");
                }
            }
            hasCss=0;
            headLinks = frameHead.getElementsByTagName('link');
            
            if (headLinks.length > 0 ) {
                for (var n in headLinks) {
                    if (headLinks.item(n).getAttribute('type') == 'text/css') {
                        hasCss=1;
                        break;
                    }
                }
            } 
            
            if (hasCss==0) {
               
                headCss= fdoc.createElement('link');
                headCss.setAttribute('rel', 'stylesheet');
                headCss.setAttribute('type','text/css');
                headCss.setAttribute('href','/themes/'+theme+ '/css/'+themeCss);
                frameHead.appendChild(headCss);

                headCss= fdoc.createElement('link');
                headCss.setAttribute('rel', 'stylesheet');
                headCss.setAttribute('type','text/css');
                headCss.setAttribute('href','/themes/'+theme+ '/css/kupu-additions.css');
                frameHead.appendChild(headCss);
            }
            
        }
        
        function deInitBxContent(doc,htmlnode) {
            var style = htmlnode.firstChild.firstChild;
            
            while (style) {
                var nextstyle = style.nextSibling;
                if (style.nodeName == "link") {
                    style.parentNode.removeChild(style);
                }
                style = nextstyle;
            }
            var body = htmlnode.firstChild;
            while (body) {
               var nextbody = body.nextSibling; 
               if (body.nodeName == "body") {
               
                   child = body.firstChild.firstChild;
                   while (child) {
                        var nextChild = child.nextSibling;
                        body.appendChild(child);
                        child = nextChild;
                  }
                  
                  body.removeChild(body.firstChild);
               }
               body = nextbody;
            }
            return htmlnode;
        
        }
  
        
    </script>
     
    </xsl:template>
    
    <xsl:template match="xhtml:body">
    
     <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
            <xsl:attribute name="onload">initBxContent(); startKupu()</xsl:attribute>
            
            <xsl:apply-templates/>
        
        </xsl:copy>
        
    </xsl:template>
    
    <xsl:template match="xhtml:xml[@id='kupuconfig']">
       <xml id="kupuconfig">
        <kupuconfig>
            
            <dst><xsl:value-of select="$contentUri"/></dst>
            <use_css>1</use_css>
            <reload_after_save>0</reload_after_save>
            <strict_output>1</strict_output>
            <content_type>application/xhtml+xml</content_type>
            <compatible_singletons>1</compatible_singletons>
            
            
            <image_xsl_uri><xsl:value-of select="$webroot"/>/webinc/kupu/common/kupudrawers/drawer.xsl</image_xsl_uri>
            <link_xsl_uri><xsl:value-of select="$webroot"/>/webinc/kupu/common/kupudrawers/drawer.xsl</link_xsl_uri>
            <image_libraries_uri><xsl:value-of select="$webroot"/>admin/navi/kupu/?drawer=image</image_libraries_uri>
            <link_libraries_uri><xsl:value-of select="$webroot"/>admin/navi/kupu/?drawer=library</link_libraries_uri>
            <search_images_uri> </search_images_uri>
            <search_links_uri> </search_links_uri>
            
            
        </kupuconfig>
      </xml>
    </xsl:template>
    
    <xsl:template match="xhtml:iframe[@id='kupu-editor']">
        
        <iframe id="kupu-editor" 
                  frameborder="0" 
                  src="ufulldoc.html" 
                  dst="fulldoc.html" 
                  reloadsrc="0" 
                  usecss="1" 
                  strict_output="1" 
                  content_type="application/xhtml+xml" 
                  scrolling="auto">
                  <xsl:attribute name="src"><xsl:value-of select="concat($contentUri, '?template=',$template)"/></xsl:attribute>
                  <xsl:attribute name="dst"><xsl:value-of select="$contentUri"/></xsl:attribute>
            
          </iframe>

        
    </xsl:template>
    
    <xsl:template match="*[@bxContent = 'true']">
          
     <xsl:copy>
            <xsl:for-each select="@*">
                <xsl:copy/>
            </xsl:for-each>
            <xsl:attribute name="onload">kupu = startKupu();</xsl:attribute>
            
 
    <div style="display: none;">
      <xml id="kupuconfig">
        <kupuconfig>
            <foo>bar</foo>
            <dst><xsl:value-of select="$contentUri"/></dst>
            <use_css>1</use_css>
            <reload_after_save>0</reload_after_save>
            <strict_output>1</strict_output>
            <content_type>application/xhtml+xml</content_type>
            <compatible_singletons>1</compatible_singletons>
        </kupuconfig>
      </xml>
    </div>
    
    <div class="kupu-fulleditor">
        
        <xsl:apply-templates/>
        <!--
        <xsl:call-template name="kupuToolbars"/>
         -->
       
        <div class="kupu-editorframe">
          
          <iframe id="kupu-editor" 
                  frameborder="0" 
                  src="ufulldoc.html" 
                  dst="fulldoc.html" 
                  reloadsrc="0" 
                  usecss="1" 
                  strict_output="1" 
                  content_type="application/xhtml+xml" 
                  scrolling="auto">
                    <xsl:attribute name="src">/admin/content<xsl:value-of select="$dataUri"/></xsl:attribute>
                    <xsl:attribute name="dst">/admin/content<xsl:value-of select="$dataUri"/></xsl:attribute>
            
                  
                  </iframe>

        </div>
        </div>
        
              </xsl:copy>
    </xsl:template>
    
    

       
</xsl:stylesheet>
