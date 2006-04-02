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
    
      <xsl:template match="xhtml:div[@id = 'captcha']" mode="xhtml">
        <xsl:variable name="date" select="/bx/plugin[@name = 'blog']/xhtml:html/xhtml:body/xhtml:div[@class='entry']/@blog:post_date_iso"/>
        <xsl:variable name="days" select="php:functionString('bx_helpers_config::getBlogCaptchaAfterDays')"/>
        <xsl:variable name="captcha" select="php:functionString('bx_helpers_captcha::isCaptcha', $days, $date)"/>
        <xsl:choose>
        <xsl:when test="$captcha = 1">
            <xsl:apply-templates mode="xhtml"/>
        </xsl:when>
        <xsl:otherwise>
            
        </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="xhtml:div[@id = 'captchaTitle']" mode="xhtml">
        <xsl:variable name="date" select="/bx/plugin[@name = 'blog']/xhtml:html/xhtml:body/xhtml:div[@class='entry']/@blog:post_date_iso"/>
        <xsl:variable name="days" select="php:functionString('bx_helpers_config::getBlogCaptchaAfterDays')"/>
        <xsl:variable name="captcha" select="php:functionString('bx_helpers_captcha::isCaptcha', $days, $date)"/>
        <xsl:choose>
        <xsl:when test="$captcha = 1">
            <xsl:apply-templates mode="xhtml"/>
        </xsl:when>
        <xsl:otherwise>
            
        </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    
    
</xsl:stylesheet>