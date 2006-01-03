<xsl:stylesheet 
    version="1.0" 
    xmlns:sixcat="http://sixapart.com/atom/category#"
    xmlns:dc="http://purl.org/dc/elements/1.1/" 
    xmlns:atom="http://purl.org/atom/ns#" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns:xhtml="http://www.w3.org/1999/xhtml" 
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
    xmlns:bxf="http://bitflux.org/functions" 
    xmlns:rss="http://purl.org/rss/1.0/" 
    xmlns:blog="http://bitflux.org/doctypes/blog"
    exclude-result-prefixes="rdf dc xhtml rss bxf blog"
>

   <xsl:template name="livesearch">
        <p><h3 class="blog">Blogpost LiveSearch</h3>
        <form onsubmit="return liveSearchSubmit()" style="margin:0px;" name="searchform" method="get" action="./">
            <input type="text" id="livesearch" name="q" size="15"  onkeypress="liveSearchStart()"/>
            <div id="LSResult" style="display: none;">
                <ul id="LSShadow">
                    <li>&#160;</li>
                </ul>
            </div>
        </form></p>
   </xsl:template>
   
   <xsl:template name="livesearchInit">
        <script type="text/javascript" language="JavaScript">
            liveSearchRoot = "<xsl:value-of select="$webroot"/>";
            liveSearchRootSubDir = "<xsl:value-of select="concat($collectionUri,$id)"/>";
            liveSearchParams = "&amp;blogadmin=1";
        </script>
    </xsl:template>
  

</xsl:stylesheet>
