<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" exclude-result-prefixes="xhtml">

	<xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

	<xsl:template match="/">
		<xsl:apply-templates mode="xhtml"/>
	</xsl:template>
    
	<!-- red font-color for bold text -->
	<xsl:template match="*[local-name()='body']" mode="xhtml">
		<xsl:element name="{local-name()}">
					<xsl:apply-templates select="@*" mode="xhtml"/>
					
					<xsl:element name="style">
		
<![CDATA[
/* ... IN VALID CODE WE TRUST ...http://www.intensivstation.ch */
/* css created by miss monorom 2005 http://www.monorom.to */
/* This css is made specialy for BX-CMS  */
/* css released under Creative Commons License - http://creativecommons.org/licenses/by/2.0/deed.en  */

/* @import url(bx-additions.css); */

/*body element, you can overwrite that with a custom stylesheet for example in mozilla*/

#ng_bitflux_org, body {
background-color: #ffffff;
background:url("/themes/scansystems/images/background/bgstripes.jpg");
background-repeat:repeat-y;
font-size: 11px;
font-family: Verdana, Arial, Helvetica, SunSans-Regular, sans-serif;
color:#000000;
line-height:16px;
padding:0px;
margin: 0px;
}               


#bgcontainer {
position:absolute;
margin:0px;
padding:0px;
top:0px;
left:0px;
width:469px;
height:690px;
}

div.bgportrait {
background: url("/themes/scansystems/images/background/portrait.jpg");
background-repeat:no-repeat;
}

div.bgproducts {
background: url("/themes/scansystems/images/background/produkte.jpg");
background-repeat:no-repeat;
}

div.bgservices {
background: url("/themes/scansystems/images/background/service.jpg");
background-repeat:no-repeat;
}

div.bgreferences {
background-image:url(/themes/scansystems/images/background/referenzen.jpg);
background-repeat:no-repeat;
}

div.bgcontact {
background: url("/themes/scansystems/images/background/kontakt.jpg");
background-repeat:no-repeat;

}

div.bgdefault {
background: url(/themes/scansystems/images/bg4.jpg);
background-repeat:no-repeat;

}


/* for iePC */ td{
font-size: 12px;
}

a {color: #000000; text-decoration: underline;}
a:visited {color: #000000; text-decoration:underline;}
a:hover {color: #000000; text-decoration:underline;}
a:active { color: #000000; text-decoration:underline;}

h1{
font-size:18px;
}
h2{
font-size:16px;
}
h3{
font-size:14px;
}

/*h4{
font-size:10px;
}*/

form{
padding: 0px;
margin: 0px;
}

.form{
padding: 0px;
margin: 5px 0px 15px 0px;
}

textarea, .formgenerell input, input.formgenerell {
width: 335px;
border: 1px solid #aaaaaa; 
margin-top: 5px;
padding: 2px;
}

.formbutton {
width:100px;
border: 1px solid #aaaaaa; 
}

/* patforms filter */
.formErrors {
    margin: 5px 25px 25px 25px;
    padding: 5px 5px 5px 5px;
    border: 1px solid ;
    background-color: #FFBBBB;
}

.formlabelerror {
    color: #BB1111;
    font-weight: bold;
}

/* container to center the layout
-------------------------------------- */
#container {
position:absolute;
margin: 0px;
top:0px;
left: 95px;
width:780px;
}

/* a container around right/content */
#content_container {
background-color:#ffffff;
width:778px;
margin:0px;
padding:0px;
height:auto;
height:expression(document.body.clientHeight > 600 ? "auto":"600px" );
min-height:530px;
border-left:1px solid #e7dfde;
}


/* head container for logo and metanavi
-------------------------------------- */
#banner {
padding: 0px;
margin: 0px;
height:141px;
background-color: transparent;
}
#banner a{
color: #dde7e9;
text-decoration:none; }
#banner  a:hover {color: #ffffff;}

#banner h1 {
font-size: 30px;
padding: 0px 0px 0px 50px;
margin: 0px;
}
#banner h2{
color: #ffffff;
font-size: 14px;
padding: 5px 0px 25px 50px;
margin: 0px;
}

#banner img.logo {
margin-left:595px;
margin-top:65px;
}

#metanavi {
float: right;
padding: 0px 0px 0px 0px;
margin: 0px;
font-weight:normal;
font-size:10px;
}

#metanavi a {
font-weight:normal;
font-size:10px;
}

/*main-navi
-------------------------------------- */
#topnavi {
background-color: #484A4D;
background-image:url(/themes/scansystems/images/verlauf-navi-top.jpg);
background-position:right;
background-repeat:repeat-y;
line-height:25px;
margin: 0px;
padding: 0px 0px 1px 0px;
}
#topnavi a { 
color: #ffffff;
text-decoration: none; 
font-weight:bold;
margin: 0px;
padding: 0px 24px;
}
#topnavi a:visited {color:#ffffff;}
#topnavi a:hover {color: #ffffff;}
#mainnavi a:active { color:#ffffff;}

#topnavi h4 {
margin:0px;
padding:0px;
line-height:2px;
height:2px;
font-size:2px;
background:url(/themes/scansystems/images/verlauf-streifen-oben.jpg);
}

/* extra div for js-generated navi */
#topnavi_menu {
margin:0px;
padding:0px;
background-color:transparent;
}

#container  #topnavi a.selected {
background-color: #c83721;
}

/* content elements
-------------------------------------- */
#content {
padding: 25px  0px;
margin:0px;
margin-right:0px;
margin-left:204px;
background-color: #ffffff;
width:507px;
}

#content h1, #content h2, #content h3, #content h4 {
padding: 0px 27px 0px 27px;
margin:0px 0px 21px 0px;
color:#4d6c6c;
}

#content h1 {
color:#c83721;
font-size:13px;
font-weight:bold;
}

#content h4 {
color:#000;
font-size:11px;
font-weight:bold;
line-height:16px;
}

#content #downloads, #content #links, #content #leftcontent {
padding: 0px 27px 0px 27px;
margin:10px 0px;
border:1px solid red;
}

#content a {
    color: #C83721;
}

#content p{
line-height: 16px;
padding: 0px 27px 0px 27px;
margin:0px 0px 15px 0px;
}
#content ul{
padding: 0px 27px 0px 50px;
margin:0px 0px 15px 0px;
}
#content p.center{
text-align:center;
}

#content p img {
margin-bottom:20px;
display: block;
}

#content p.breadcrumb {
line-height: 16px;
padding: 0px 27px 0px 27px;
margin:0px 0px 15px 0px;
}

#content table {
margin:9px 7px 24px 27px;
padding:0px;
border:0px;
}

#content table td {
margin:0px;
padding:0px;
}

#content table td h4 {
padding-left:0px;
margin-bottom: 5px;
}

#content h4.produktbeschrieb {
margin-bottom: 5px;
}

#breadcrumb {
padding: 0px;
margin:0px;
margin-left:204px;
padding-top:24px;
background-color: #ffffff;
width:507px;
font-size:10px;
}

#breadcrumb p {
line-height: 16px;
padding: 0px 27px 0px 27px;
margin:0px;
}

#breadcrumb a { text-decoration:none;padding-right:15px;}
#breadcrumb a:hover {text-decoration:none; color:#c83721;}
#breadcrumb a:active {text-decoration:none; color:#c83721;}
#breadcrumb a:visited {text-decoration:none; color:#000000;}

#breadcrumb a.selected {text-decoration:none; color:#c83721;}

/* elements for the gallery
-------------------------------------- */
#content #gallerie{
float:left;
padding: 0px 0px 10px 50px;
margin:0px 0px 20px 0px;
}
 
#content .thumbnail{
float:left;
width:100px;
margin: 15px 15px 0px 0px;
padding: 0px;
}
#content .thumbnail img{
border: 1px solid #778899;
padding: 1px;
}

#content  br.antileft {
clear: left;
}
.antifloat {
clear: both;
visibility: hidden;
}

/* elements for the gallery_preview-plugin
--------------------------------------------*/
gallerie_preview {

padding: 0px 10px 10px 25px;
margin:0px 20px 20px 20px;
border: 1px solid #778899;
}

#gallerie_preview .thumbnail{
float:left;
width:100px;
margin: 10px 10px 0px 0px;
padding: 0px;
}

#gallerie_preview .thumbnail img{
border: 1px solid #778899;
padding: 1px;
}

#gallerie_preview_navi {margin-top: 10px;}
#gallerie_preview_info {margin-top: 10px; visibility: visible;}

/* elements for left and right navigation
-------------------------------------- */
#left {
margin: 0px;
padding: 25px 0px;
background-color: #ffffff;
}

#right {
float: left;
width: 204px;
margin: 0px;
padding: 0px;
}

#right p{
padding: 0px 15px 15px 25px;
margin:0px;
}
#left a, #right a {
text-decoration: none;
font-size:11px;
color:#000000;
}
#left .selected, #right .selected {
text-decoration: none;
color:#c83721;
font-weight:bold;
}

#left ul, #right ul {
list-style-type: none;
margin: 0px 0px;
padding: 0px;
} 
#left li, #right li {
margin-bottom: 0px;
padding: 2px 26px;
line-height:20px;
height:16px;
}

#left li img {
visibility:hidden;
}

#left li.selected_list, #right li.selected_list {
background:url('/themes/scansystems/images/grau-raster.gif');
line-height:20px;
}

#left li.selected_list_downloads, #right li.selected_list_downloads {
background:url('/themes/scansystems/images/grau-raster.gif');
font-weight:bold;
line-height:15px;
}

#left li.listsmall {
height:15px;
line-height:15px;
}

#left li.listsmall a { font-size:10px;}

#left #downloads li img, #left #links li img {
visibility:visible;
}

#left h3, #right h3{
margin: 0px 0px 10px 0px;
padding: 25px 0px 0px 25px;
}

#left #leftcontent h1 {
background:url('/themes/scansystems/images/grau-raster.gif');
font-weight:bold;
font-size: 10px;
padding-left: 27px;
}

#left #leftcontent a.emaillink {
    color: #C83721;
    text-decoration:underline;
}

/*footer
-------------------------------------- */
#footer {
clear: both;
padding: 3px 0px 3px 26px;
font-size:10px;
margin: 0px;
}

#footer p {
margin:0px;
padding:5px 0px;
border-top:1px solid #e7dfde;
font-size:10px;
color:#9c9c9c;
}

#footer a {color: #9c9c9c; text-decoration:none;}
#footer a:hover {color:#c83721; text-decoration:none;}

/* just used vor the blog
-------------------------------------- */
#content .post_title {
color: #333;
border-bottom: 1px solid #aaa;
margin: 0px 50px 3px 50px;
padding: 0px 0px 5px 0px;
}

.post_meta_data {
font-size: 11px;
padding-top: 0px;
margin: 5px 50px 15px 50px;
}
.right {
float: right;
}
.post_content {
line-height: 18px;
padding-top: 0px;
margin: 5px 50px 15px 50px;
}
#content .post_content p{
padding: 0px 0px 15px 0px;
margin: 0px;
}

h3.blog {
color: #333;
margin: 0px;
margin-bottom: 5px;
}
#right  h3.blog {
background-image: none;
color: #333;
margin: 0px;
margin-bottom: 5px;
}
.post_links, .post_tags, .post_related_entries  {
text-align: right;
font-family: Verdana, Geneva, Arial, Helvetica, SunSans-Regular, sans-serif;
font-size: 10px;
padding: 0px 50px 20px 50px;
margin: 0px;
}

.post_tags, .post_related_entries {
    padding-bottom: 10px;
}

.blog_pager{
padding-top: 0px;
margin: 5px 50px 15px
}

#right .blog li {
line-height:  15px;
padding: 0px 15px 3px 25px;
margin: 0px;
text-indent: -1em;
}
#livesearch {
margin: 0px 15px 15px 25px;
padding:0px;
width: 140px;
display: block;
}
#right input#livesearch  {
padding:1px;
width: 120px;
border: 1px solid #aaaaaa; 
}

.formurl {display: none;}

.cired { color:#C83721; margin-left:13px; margin-right:2px;}

h3.overview {
    line-height: 16px;
    font-weight: bold;
    font-size: 11px;
    padding: 0px !important;
    margin: 0px !important;
    color: #000 !important;
}

p.overview {
    line-height: 18px;
    padding: 0px !important;
}

a.overview {
    color: #C83721;
    text-decoration:underline;
}

.portraittable b, .portraittitel {
    line-height: 16px;
    font-weight: bold;
    font-size: 11px;
    padding: 0px !important;
    margin: 0px !important;
    color: #000 !important;
}

.portraittable p, p.portraittext {
    line-height: 18px;
    padding: 0px !important;
    margin: 0px !important;
}


.portraittable a, p.portraittext a {
    color: #C83721;
    text-decoration:underline;
}

.portraittable td {
border-bottom:18px white solid;
}

#sitemap a {
    color: #000 !important;
}

#sitemap {
}

#sitemap ul {
    padding-left: 27px;
}

#sitemap ul li ul {
    padding-left: 0px;
    margin-bottom: 12px;
}

/* ;) */
#sitemap ul li ul li ul {
    padding: 0px;
    margin-bottom: 3px;
}

#sitemap ul li {
    list-style-type: none;
}

#sitemap a {
    text-decoration: none;
}

#sitemap a span.listlevel1 {
    font-weight: bold;
    padding-bottom: 10px;
}

#sitemap a span.listlevel2, #sitemap a span.listlevel3 {
}

#sitemap span.listitem1 {
    color: #000;
}

#sitemap span.listitem2 {
    color: #C83721;
}

img.blackborder {
    border: 1px solid #000;
}

#downloads a, #links a {
    color: #9c9c9c;
}

#downloads a:hover, #links a:hover {
    color: #c83721;
}
]]>
		
					</xsl:element>
					
					<xsl:apply-templates mode="xhtml"/>		
		</xsl:element>
	</xsl:template>
    
	<xsl:template match="*" mode="xhtml">
		<xsl:element name="{local-name()}">
			<xsl:apply-templates select="@*" mode="xhtml"/>
			<xsl:apply-templates mode="xhtml"/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="@*" mode="xhtml">
		<xsl:copy-of select="."/>
	</xsl:template>

</xsl:stylesheet>
