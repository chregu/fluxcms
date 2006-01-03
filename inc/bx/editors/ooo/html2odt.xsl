<xsl:stylesheet version="1.0" xmlns:sixcat="http://sixapart.com/atom/category#"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns:xhtml="http://www.w3.org/1999/xhtml"
        xmlns:php="http://php.net/xsl"
        xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
        xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="http://www.w3.org/2000/svg" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <xsl:output method="xml"/>
        
        
        <xsl:template match="xhtml:html">
             <office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="http://www.w3.org/2000/svg" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" office:version="1.0">
             <office:scripts/>
             <office:automatic-styles>
        <style:style style:name="fr1" style:family="graphic" style:parent-style-name="Graphics">
            <style:graphic-properties style:wrap="none" style:vertical-pos="top" style:vertical-rel="paragraph" style:horizontal-pos="left" style:horizontal-rel="paragraph" style:mirror="none" fo:clip="rect(0cm 0cm 0cm 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
        </style:style>
        
        <style:style style:name="Tsuper" style:family="text">
            <style:text-properties style:text-position="super 58%"/>
        </style:style>
        <style:style style:name="Tsub" style:family="text">
            <style:text-properties style:text-position="sub 58%"/>
        </style:style>
            
        <style:style style:name="Pol" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L1">
            <style:text-properties style:text-position="0% 100%"/>
        </style:style>
        
        <style:style style:name="Table1" style:family="table">
            <style:table-properties style:width="16.999cm" table:align="margins"/>
        </style:style>
        <style:style style:name="Table1.A" style:family="table-column">
            <style:table-column-properties style:column-width="5.666cm" style:rel-column-width="21845*"/>
        </style:style>
        <style:style style:name="Table1.A1" style:family="table-cell">
            <style:table-cell-properties fo:padding="0.097cm" fo:border="0.002cm solid #000000" />
        </style:style>
        
        <style:style style:name="Tbold" style:family="text">
            <style:text-properties fo:font-weight="bold" style:font-weight-asian="bold"  style:font-weight-complex="bold"/>
        </style:style>
        <style:style style:name="Titalics" style:family="text">
            <style:text-properties fo:font-style="italic"  style:font-style-asian="italic" style:font-style-complex="italic"/>
        </style:style>
        <style:style style:name="Punderline" style:family="text">
            <style:text-properties style:text-underline-style="solid" style:text-underline-width="auto" style:text-underline-color="font-color" fo:font-weight="normal" style:font-weight-asian="normal" style:font-weight-complex="normal"/>
        </style:style>
        <text:list-style style:name="LO">
            <text:list-level-style-number text:level="1" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:space-before="0.635cm" text:min-label-width="0.635cm"/>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:space-before="1.27cm" text:min-label-width="0.635cm"/>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:space-before="1.905cm" text:min-label-width="0.635cm"/>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:space-before="2.54cm" text:min-label-width="0.635cm"/>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:space-before="3.175cm" text:min-label-width="0.635cm"/>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:space-before="3.81cm" text:min-label-width="0.635cm"/>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:space-before="4.445cm" text:min-label-width="0.635cm"/>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:space-before="5.08cm" text:min-label-width="0.635cm"/>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:space-before="5.715cm" text:min-label-width="0.635cm"/>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:space-before="6.35cm" text:min-label-width="0.635cm"/>
            </text:list-level-style-number>
        </text:list-style>
        <text:list-style style:name="LU">
            <text:list-level-style-bullet text:level="1" text:style-name="Bullet_20_Symbols" style:num-suffix="." text:bullet-char="●">
                <style:list-level-properties text:space-before="0.635cm" text:min-label-width="0.635cm"/>
                <style:text-properties style:font-name="StarSymbol"/>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="2" text:style-name="Bullet_20_Symbols" style:num-suffix="." text:bullet-char="○">
                <style:list-level-properties text:space-before="1.27cm" text:min-label-width="0.635cm"/>
                <style:text-properties style:font-name="StarSymbol"/>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="3" text:style-name="Bullet_20_Symbols" style:num-suffix="." text:bullet-char="■">
                <style:list-level-properties text:space-before="1.905cm" text:min-label-width="0.635cm"/>
                <style:text-properties style:font-name="StarSymbol"/>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="4" text:style-name="Bullet_20_Symbols" style:num-suffix="." text:bullet-char="●">
                <style:list-level-properties text:space-before="2.54cm" text:min-label-width="0.635cm"/>
                <style:text-properties style:font-name="StarSymbol"/>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="5" text:style-name="Bullet_20_Symbols" style:num-suffix="." text:bullet-char="○">
                <style:list-level-properties text:space-before="3.175cm" text:min-label-width="0.635cm"/>
                <style:text-properties style:font-name="StarSymbol"/>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="6" text:style-name="Bullet_20_Symbols" style:num-suffix="." text:bullet-char="■">
                <style:list-level-properties text:space-before="3.81cm" text:min-label-width="0.635cm"/>
                <style:text-properties style:font-name="StarSymbol"/>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="7" text:style-name="Bullet_20_Symbols" style:num-suffix="." text:bullet-char="●">
                <style:list-level-properties text:space-before="4.445cm" text:min-label-width="0.635cm"/>
                <style:text-properties style:font-name="StarSymbol"/>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="8" text:style-name="Bullet_20_Symbols" style:num-suffix="." text:bullet-char="○">
                <style:list-level-properties text:space-before="5.08cm" text:min-label-width="0.635cm"/>
                <style:text-properties style:font-name="StarSymbol"/>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="9" text:style-name="Bullet_20_Symbols" style:num-suffix="." text:bullet-char="■">
                <style:list-level-properties text:space-before="5.715cm" text:min-label-width="0.635cm"/>
                <style:text-properties style:font-name="StarSymbol"/>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="10" text:style-name="Bullet_20_Symbols" style:num-suffix="." text:bullet-char="●">
                <style:list-level-properties text:space-before="6.35cm" text:min-label-width="0.635cm"/>
                <style:text-properties style:font-name="StarSymbol"/>
            </text:list-level-style-bullet>
        </text:list-style>
        
        </office:automatic-styles>
                <xsl:apply-templates/>
             </office:document-content>
        </xsl:template>
            
        <xsl:template match="xhtml:body">
             <office:body><office:text>
             <xsl:apply-templates/>
             </office:text></office:body>
        </xsl:template>
            
        <xsl:template match="xhtml:b">
            <text:span text:style-name="Tbold"> 
                <xsl:apply-templates/>
            </text:span>
        </xsl:template>
        
        <xsl:template match="xhtml:i">
          <text:span text:style-name="Titalics">
            <xsl:apply-templates/>
          </text:span>
        </xsl:template>
        
        <xsl:template match="xhtml:sub">
          <text:span text:style-name="Tsub">
            <xsl:apply-templates/>
          </text:span>
        </xsl:template>
        
        <xsl:template match="xhtml:sup">
          <text:span text:style-name="Tsuper">
            <xsl:apply-templates/>
          </text:span>
        </xsl:template>
        
        <xsl:template match="xhtml:u">
            <text:span text:style-name="Punderline">
                <xsl:apply-templates/>
            </text:span>
        </xsl:template>        
        
        <xsl:template match="xhtml:p">
            <text:p>
            <xsl:apply-templates/>
            </text:p>
        </xsl:template>
        
        <xsl:template match="xhtml:h1">
            <text:h text:outline-level='1'>
            <xsl:apply-templates/>
            </text:h>
        </xsl:template>                    

        <xsl:template match="xhtml:h2">
            <text:h text:outline-level='2'>
            <xsl:apply-templates/>
            </text:h>
        </xsl:template>
        
        <xsl:template match="xhtml:h3">
            <text:h text:outline-level='3'>
            <xsl:apply-templates/>
            </text:h>
        </xsl:template>
        
        <xsl:template match="xhtml:h4">
            <text:h text:outline-level='4'>
            <xsl:apply-templates/>
            </text:h>
        </xsl:template>
        
        <xsl:template match="xhtml:ul">
            <text:list text:style-name="LU">
            <xsl:apply-templates/>
            </text:list>
        </xsl:template>

        <xsl:template match="xhtml:ol">
          <text:list text:style-name="LO">
            <xsl:apply-templates/>
          </text:list>
        </xsl:template>

        <xsl:template match="xhtml:li">
            <text:list-item><text:p text:style-name="L1">
            <xsl:apply-templates/>
            </text:p></text:list-item>
        </xsl:template>
        
        <xsl:template match="xhtml:table">
            <table:table table:name="Table1" table:style-name="Table1">
            <table:table-column table:style-name="Table1.A" table:number-columns-repeated="{count(tr[position() = 1]/td | tr[position() = 1]/th)}"/>
                <xsl:apply-templates/>
            </table:table>
        </xsl:template>
        
        <xsl:template match="xhtml:tr[xhtml:th]">
            <table:table-header-rows><table:table-row>
                <xsl:apply-templates/>
            </table:table-row></table:table-header-rows>
        </xsl:template>
        
         <xsl:template match="xhtml:th">
            <table:table-cell table:style-name="Table1.A1">
            <xsl:apply-templates/>
            </table:table-cell>
        </xsl:template>
        
        <xsl:template match="xhtml:td">
            <table:table-cell table:style-name="Table1.A1">
            <xsl:apply-templates/>
            </table:table-cell>
        </xsl:template>
        
        <xsl:template match="xhtml:tr">
            <table:table-row>
                <xsl:apply-templates/>
            </table:table-row>
        </xsl:template>
        
        <xsl:template match="xhtml:a">
            <text:a xlink:href="{@href}">
                <xsl:apply-templates/>
            </text:a>
        </xsl:template>
        
        <xsl:template match="xhtml:img">
            <draw:frame draw:style-name="fr1" draw:name="graphics1" text:anchor-type="paragraph" svg:x="{@width}" svg:y="{@height}" svg:width="{@width}" svg:height="{@height}" draw:z-index="0">
            <draw:image xlink:href="{@src}" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad">
                <xsl:apply-templates/>
            </draw:image>
            </draw:frame>
        </xsl:template>
        
</xsl:stylesheet>