<?xml version="1.0"?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns:xhtml="http://www.w3.org/1999/xhtml" 
    xmlns:php="http://php.net/xsl" 
    exclude-result-prefixes="php xhtml"
>

    <xsl:template name="doLiveSelect">
        <xsl:param name="ls"/>
        <div class="liveselectcontainer" style="z-index: {1000-position()} !important;">
            <div class="liveselect">
                <input type="text" id="{$ls/@id}_lsqueryfield" size="40"/>
                <div class="liveselectResultsShadow">
                    <div class="liveselectResults" id="{$ls/@id}_lsresults">
                        <ul></ul>
                        <div id="{$ls/@id}_pd" class="liveselectPager"></div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="{$ls/@id}"/>
        <div class="n2mvalues"/>
    </xsl:template>

    <xsl:template name="doListview">
        <xsl:param name="lv"/>
        <div id="{$lv/@id}">
            <div class="listview">
                <div class="listviewResults" id="{$lv/@name}_lvresults">
                    <table cellspacing="0" cellpadding="0" width="100%">
                        <thead>
                            <tr noremove="1">
                                <th width="90%">Title</th>
                                <th width="10%"></th>
                            </tr>
                        </thead>
                        <tbody id="{$lv/@name}_lvresultstable">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </xsl:template>

</xsl:stylesheet>
