<?xml version="1.0"?>
<xsl:stylesheet version="1.0" 
xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" xmlns:php="http://php.net/xsl" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:rss="http://purl.org/rss/1.0/" xmlns:dc="http://purl.org/dc/elements/1.1/" exclude-result-prefixes="xhtml">

    <xsl:template match="plugin[@name='vote']" mode="vote">

        
            <xsl:choose>
                <xsl:when test="vote/@results = 'true'">
        

                    
                        <div class="votesubdiv">
                            <xsl:variable name="width" select="'110'"/>
                            <xsl:variable name="total" select="sum(/bx/plugin[@name='vote']/vote/answer/@count)"/>
                            <b><xsl:value-of select="/bx/plugin[@name='vote']/vote/response"/></b><br/><br/>
                            <xsl:value-of select="/bx/plugin[@name='vote']/vote/question"/><br/>
                            <xsl:for-each select="/bx/plugin[@name='vote']/vote/answer">
                                <xsl:variable name="balkenwidth">
                                    <xsl:choose>
                                        <xsl:when test="not(@count)">0</xsl:when>
                                        <xsl:otherwise>
                                            <xsl:value-of select="floor($width * (number(@count) div $total))"/>
                                            <br/>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </xsl:variable>

                                <xsl:choose>
                                    <xsl:when test="number(@count)">
                                        <xsl:value-of select="number(@count)"/> % 
                                            <xsl:value-of select="text()"/>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        0 % <xsl:value-of select="text()"/>
                                    </xsl:otherwise>
                                </xsl:choose>

                                <div class="speicher">

                                    <div class="balken" style="position: relative; width: {($width - $balkenwidth)}px; left:{$balkenwidth}px;">&#160;</div>
                                </div>
                            </xsl:for-each>
                        </div>
                    
                </xsl:when>
                <xsl:otherwise>
                  
                  
                    <div class="votesubdiv">
                        <form onsubmit="return voteSubmit();" name="voteform" method="post" action="{vote/@collectionUri}index.html">
                        <xsl:value-of select="vote/question"/>
                        <br/>
                        <br/>
                        <table>
                        <xsl:for-each select="vote/answer">
                                <tr>
                                    <td>
                                        <xsl:value-of select="text()"/>
                                    </td>
                                    <td>
                                        <input type="radio" name="selection" value="{@key}"/>
                                    </td>
                                </tr>
                        </xsl:for-each>
                        </table>
                            <input type="submit" class="votesubmit" value="votesubmit" name="votesubmit" />
                        </form>
                        <a href="#" onclick="voteSubmit();">View results</a>
                    </div>
                    
                </xsl:otherwise>

            </xsl:choose>
        
    </xsl:template>
    
    <xsl:template match="/">
    <xsl:apply-templates select="/bx/plugin[@name='vote']" mode="vote"/>
    
    </xsl:template>



</xsl:stylesheet>
