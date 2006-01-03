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

   <xsl:template match="atom:comment">
        <tr>
         <xsl:choose>
            <xsl:when test="position() mod 2= 0">
            <xsl:attribute name="class">uneven</xsl:attribute>
            </xsl:when>
            </xsl:choose>
            <td valign="top"><input class="checkbox" type="checkbox" name="bx[plugins][admin_edit][deletecomments][{@id}]" value="{@id}"/></td>
            <td valign="top">
                <xsl:variable name="title">
                    <xsl:value-of select="substring(atom:content, 1, 50)"/><xsl:if test="string-length(atom:content) > 50"> ...</xsl:if>
                </xsl:variable>
                <xsl:choose>
                    <xsl:when test="atom:post_uri != ''">
                    
                      <a href="{concat($collectionUri, $collectionUriOfId)}sub/comments/?id={@id}"><xsl:value-of select="$title"
                      
                      disable-output-escaping="yes"/></a>
      
                      <!--  <a href="{concat(atom:post_uri, '.html')}">
                            <xsl:value-of select="$title"/>
                        </a>-->
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="$title"/>
                    </xsl:otherwise>
                </xsl:choose>
            </td>
            <td valign="top"><xsl:value-of select="atom:author"  disable-output-escaping="yes"/></td>
            <td valign="top"><xsl:value-of select="atom:date"/></td>
            <td valign="top"><xsl:copy-of select="atom:rejectreason/node() "/></td>
           
        </tr>
    </xsl:template>

</xsl:stylesheet>
