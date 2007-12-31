<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
       
 
xmlns:php="http://php.net/xsl" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" exclude-result-prefixes="xhtml">
    <xsl:import href="master.xsl"/>
    <xsl:import href="../standard/common.xsl"/>

    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

    <xsl:template name="content">
        <h1>Suche</h1>
<xsl:variable name="results" select="/bx/plugin[@name='search2']/search2/results"/>
        <p>        <form action="./" method="get">
                    <input type="text" name="q" value="{php:functionString('bx_helpers_globals::GET','q','')}"/>
                    <xsl:text> </xsl:text>
                   
                    <input type="submit" value="Suche"/>
                    
                </form>
            
           </p> 
            
        
   
        <xsl:if test="$results">
            <xsl:if test="$results[@type='fulltext']/entry">
   <p>

                    <xsl:apply-templates select="$results[@type='fulltext']/entry"/>

      </p>
            </xsl:if>
            
          
            </xsl:if>

    </xsl:template>
<xsl:template match="entry">

<div class="personen">
    <xsl:if test="position() mod 2 = 0">
    <xsl:attribute name="class">personen greybg</xsl:attribute>
    </xsl:if>
    <h4>
    <a href="{url}"><xsl:value-of select="title"/><xsl:text> </xsl:text> <xsl:value-of select="name"/></a>
    </h4>
    
    <p><xsl:value-of select="text" disable-output-escaping="yes"/></p>
    </div>


</xsl:template>


</xsl:stylesheet>
