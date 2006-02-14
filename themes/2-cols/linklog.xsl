<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xhtml="http://www.w3.org/1999/xhtml"
	xmlns:php="http://php.net/xsl"
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:rss="http://purl.org/rss/1.0/"	
	xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:blog="http://bitflux.org/doctypes/blog"
	xmlns:bxf="http://bitflux.org/functions"
	xmlns="http://www.w3.org/1999/xhtml" exclude-result-prefixes="xhtml rdf rss dc php blog">
	<xsl:import href="master.xsl" />
	<xsl:import href="../standard/common.xsl" />
	<xsl:output encoding="utf-8" method="xml" />
	<!-- get the username 
	add this to namespace:
	xmlns:php="http://php.net/xsl"
    <xsl:variable name="username"><xsl:value-of select="php:functionString('bx_helpers_perm::getUsername')"/></xsl:variable>	
    
    call as
     <xsl:value-of select="$username"/>
	 -->
<xsl:variable name="currenttag"><xsl:value-of select="/bx/plugin[@name='linklog']/links/meta/title"/></xsl:variable>	



	<!-- <title>-tag in head: -->
	<xsl:template name="html_head_title">
		<xsl:apply-templates
			select="/bx/plugin[@name='linklog']/links/meta/title" />
		| sequenz/linklog
	</xsl:template>



	<xsl:template name="content">
		<!-- Titel: -->
		<xsl:apply-templates
			select="/bx/plugin[@name='linklog']/links/meta" mode="meta" />

		<!-- Effective Content: -->
		<xsl:apply-templates
			select="/bx/plugin[@name='linklog']/links/link" mode="links" />

	</xsl:template>

	<!-- Construct the Heading of the title: -->
	<xsl:template match="meta" mode="meta">
		<h1>
			<xsl:value-of select="title" />
		</h1>
		<p>
			<xsl:value-of select="description" />
		</p>
	</xsl:template>

	<xsl:template match="link" mode="links">

		<div class="linkmanager_single_link">
			<h3>
				<a href="{url}">
					<xsl:value-of select="title" />
				</a>
				
			<xsl:choose>
			    <xsl:when test="edituri != ''">
			     &#160;(&#160;<a href="{edituri}"><img src="/files/images/edit.gif" border="0" alt="edit" height="13px" width="13px" title="edit {title}" /></a>
			    </xsl:when>
			</xsl:choose>

			<xsl:choose>
			    <xsl:when test="deleteuri != ''">
			    		<!-- here i shoud add a javascript warning -->
				    &#160; <a href="{deleteuri}"><img src="/files/images/delete.gif" border="0" alt="delete" height="13px" width="13px" title="delete {title}" /></a> )
			    </xsl:when>
			</xsl:choose>
						
				
			</h3>
			<p>
				<xsl:value-of select="description" />
				<br />
				Tags:
				<xsl:for-each select="tags/tag">
					&#160;
					<a href="{fulluri}">
						<xsl:value-of select="name" />
					</a>
				</xsl:for-each>
				<br />
				<xsl:value-of select="time" />
			</p>
			
			
			
		</div>
	</xsl:template>

	<!-- This is the navigation, calls it's own plugin -->
	<xsl:template name="leftnavi">
		<div id="left">
		
			<xsl:apply-templates
				select="document(concat('portlet://',$collectionUri,'plugin=tags(',$filename,').xml'))/bx/plugin/collection" />

		<!-- this is not done yet - want an image only for current page, too -->
		<p><a href="{$collectionUri}rss.xml" title="linklog as rss"><img src="http://www.sequenz.ch/files/img/xml.gif"></img></a></p>

<!--   	     <xsl:call-template name="delicious" />    -->


		</div>
	</xsl:template>

    <xsl:template name="delicious">

        <ul>
            <xsl:for-each select="document(concat('portlet://blog/plugin=deliciousrdf(/tag/freeflux).xml'))/bx/plugin/rdf:RDF/rss:item[position() &lt; 11]">
                <li>
                    <a title="{rss:description} - Categories: {dc:subject}" class="blogLinkPad" href="{rss:link}">
                      <xsl:value-of select="rss:title"/> 
                    </a>
                </li>
            </xsl:for-each>
        </ul>
    </xsl:template>	
<!--  
    <xsl:template name="delicious">
        <h3 class="blog">
            <a href="http://del.icio.us/tag/freeflux">del.icio.us/tag/<xsl:value-of select="$currenttag"/></a>
        </h3>
        <ul>
            <xsl:for-each select="document(concat('portlet://blog/plugin=deliciousrdf(tag/bitflux).xml'))/bx/plugin/rdf:RDF/rss:item[position() &lt; 11]">
                <li>
                    <a title="{rss:description} - Categories: {dc:subject}" class="blogLinkPad" href="{rss:link}">
                        <xsl:value-of select="rss:title"/>
                    </a>
                </li>
            </xsl:for-each>
        </ul>
    </xsl:template>
  -->


</xsl:stylesheet>
