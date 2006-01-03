<xsl:stylesheet version="1.0" 
 xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
xmlns:blog="http://bitflux.org/doctypes/blog" xmlns:bxf="http://bitflux.org/functions" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" xmlns:php="http://php.net/xsl" exclude-result-prefixes="php blog bxf xhtml i18n">


    <xsl:template name="plazeDiv">
        <xsl:param name="blogInfo"/>
        <xsl:if test="$blogInfo/blog:plazes">
        
            <div class="post_tags">
                <xsl:call-template name="plaze">
                    <xsl:with-param name="blogInfo" select="$blogInfo"/>
                </xsl:call-template>
            </div>
        </xsl:if>
    </xsl:template>


    <xsl:template name="plaze">
        <xsl:param name="blogInfo"/>
        <xsl:if test="$blogInfo/blog:plazes">
            <i18n:text>Location</i18n:text>:
            <xsl:variable name="plazes" select="$blogInfo/blog:plazes"/>
            <!-- call location part -->
            <xsl:call-template name="plazeLocation">
                <xsl:with-param name="plazes" select="$plazes"/>
            </xsl:call-template>
            <!-- call long lat part -->
            <xsl:call-template name="plazeLongLat">
                <xsl:with-param name="plazes" select="$plazes"/>
            </xsl:call-template>
        </xsl:if>
    </xsl:template>
    
    <xsl:template name="plazeLocation">
      <xsl:param name="plazes"/>
            <xsl:if test="$plazes/blog:plazename">
                <xsl:choose>
                    <xsl:when test="$plazes/blog:plazeurl">
                        <a href="{$plazes/blog:plazeurl}"><xsl:value-of select="$plazes/blog:plazename"/></a>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="$plazes/blog:plazename"/>
                    </xsl:otherwise>
                </xsl:choose>
                            <!--
            / <xsl:value-of select="$plazes/blog:plazecity"/>/
            <xsl:value-of select="$plazes/blog:plazecountry"/>/
            -->

            </xsl:if>
    </xsl:template>
    
    <xsl:template name="plazeLongLat">
        <xsl:param name="plazes"/>
        (<xsl:value-of select="format-number($plazes/blog:plazelat,'#.000')"/>,
         <xsl:value-of select="format-number($plazes/blog:plazelon,'#.000')"/>)
    </xsl:template>
</xsl:stylesheet>