<?xml version="1.0"?>
<xsl:stylesheet version="1.0" 
    xmlns:php="http://php.net/xsl" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns:xhtml="http://www.w3.org/1999/xhtml" 
    xmlns="http://www.w3.org/1999/xhtml"
>
    <xsl:output method="xml" omit-xml-declaration="yes"/>
    <xsl:param name="url" select="'/'"/>
    <xsl:param name="dataUri" select="''"/>
    <xsl:param name="webroot" select="''"/>
    <xsl:variable name="theme" select="php:functionString('bx_helpers_config::getProperty','theme')"/>
    <xsl:variable name="themeCss" select="php:functionString('bx_helpers_config::getProperty','themeCss')"/>
    <xsl:variable name="lang" select="php:functionString('bx_helpers_config::getProperty','adminLanguage')"/>
    
    <xsl:template match="/">
<xsl:text disable-output-escaping = "yes"
>FCKConfig.CustomConfigurationsPath = '' ;

FCKConfig.DocType = '&lt;!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"&gt;' ;

FCKConfig.BaseHref = '' ;

FCKConfig.FullPage = false ;

FCKConfig.Debug = false ;

FCKConfig.SkinPath = FCKConfig.BasePath + 'skins/default/' ;

FCKConfig.PluginsPath = FCKConfig.BasePath + 'plugins/' ;

// FCKConfig.Plugins.Add( 'placeholder', 'en,it' ) ;

FCKConfig.AutoDetectLanguage	= false ;
FCKConfig.ContentLangDirection	= 'ltr' ;

FCKConfig.EnableXHTML		= true ;
FCKConfig.EnableSourceXHTML	= true ;

FCKConfig.ProcessHTMLEntities	= false ;
FCKConfig.IncludeLatinEntities	= true ;
FCKConfig.IncludeGreekEntities	= true ;

FCKConfig.FillEmptyBlocks	= false ;

FCKConfig.FormatSource		= true ;
FCKConfig.FormatOutput		= true ;
FCKConfig.FormatIndentator	= '    ' ;

FCKConfig.GeckoUseSPAN	= false ;
FCKConfig.StartupFocus	= false ;
FCKConfig.ForcePasteAsPlainText	= false ;
FCKConfig.ForceSimpleAmpersand	= false ;
FCKConfig.TabSpaces		= 0 ;
FCKConfig.ShowBorders	= true ;
FCKConfig.UseBROnCarriageReturn	= false ;
FCKConfig.ToolbarStartExpanded	= true ;
FCKConfig.ToolbarCanCollapse	= true ;
FCKConfig.IgnoreEmptyParagraphValue = true ;
FCKConfig.AutoDetectPasteFromWord = true ;

FCKConfig.ToolbarSets["Default2"] = [
	['Source','DocProps','-','Save','NewPage','Preview','-','Templates'],
	['Cut','Copy','Paste','PasteText','PasteWord','-','Print','SpellCheck'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
	['OrderedList','UnorderedList','-','Outdent','Indent'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	['Link','Unlink','Anchor'],
	['Image','Table','SpecialChar','UniversalKey'],
	'/',
	['Style','FontFormat'],
	['TextColor','BGColor'],
	['About']
] ;

FCKConfig.ToolbarSets["Basic"] = [
	['Bold','Italic','-','OrderedList','UnorderedList','-','Link','Unlink','-','About','Source']
] ;

FCKConfig.ToolbarSets["fluxfck"] = [
	['Source'],
	['Cut','Copy','Paste','PasteText','PasteWord','-','Print','SpellCheck'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	['OrderedList','UnorderedList','-','Outdent','Indent'],
	['Link','Unlink','Anchor'],
	['Image','Table','SpecialChar','UniversalKey'],
	'/',
	['Style','FontFormat'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript']
] ;

FCKConfig.ToolbarSets["fluxfckblog"] = [
['FontFormat']
,['Bold','Italic','Underline','StrikeThrough','Subscript','Superscript','RemoveFormat'],
['OrderedList','UnorderedList','Outdent','Indent'],
['Link','Unlink','Image','Table','SpecialChar'],

['Undo','Redo'],
['PasteText','PasteWord'],
['Source']
	
] ;
FCKConfig.ToolbarSets["Default"] = FCKConfig.ToolbarSets["fluxfckblog"];

FCKConfig.ContextMenu = ['Generic','Link','Anchor','Image','Flash','Select','Textarea','Checkbox','Radio','TextField','HiddenField','ImageButton','Button','BulletedList','NumberedList','TableCell','Table','Form'] ;

FCKConfig.FontColors = '000000,993300,333300,003300,003366,000080,333399,333333,800000,FF6600,808000,808080,008080,0000FF,666699,808080,FF0000,FF9900,99CC00,339966,33CCCC,3366FF,800080,999999,FF00FF,FFCC00,FFFF00,00FF00,00FFFF,00CCFF,993366,C0C0C0,FF99CC,FFCC99,FFFF99,CCFFCC,CCFFFF,99CCFF,CC99FF,FFFFFF' ;

FCKConfig.FontNames		= 'Arial;Comic Sans MS;Courier New;Tahoma;Times New Roman;Verdana' ;
FCKConfig.FontSizes		= '1/xx-small;2/x-small;3/small;4/medium;5/large;6/x-large;7/xx-large' ;
FCKConfig.FontFormats	= 'p;div;h1;h2;h3;h4;pre' ;

FCKConfig.TemplatesXmlPath	= FCKConfig.EditorPath + 'fcktemplates.xml' ;

FCKConfig.SpellChecker			= 'ieSpell' ;	// 'ieSpell' | 'SpellerPages'
FCKConfig.IeSpellDownloadUrl	= 'http://www.iespell.com/rel/ieSpellSetup211325.exe' ;

FCKConfig.MaxUndoLevels = 15 ;

FCKConfig.LinkDlgHideTarget		= false ;
FCKConfig.LinkDlgHideAdvanced	= false ;

FCKConfig.ImageDlgHideLink		= false ;
FCKConfig.ImageDlgHideAdvanced	= false ;

FCKConfig.LinkBrowser = true ;
FCKConfig.LinkBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=files&amp;Connector=connectors/php/connector.php' ;

//FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Connector=connectors/asp/connector.asp' ;

//FCKConfig.LinkBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Connector=connectors/asp/connector.asp&amp;ServerPath=/CustomFiles/' ;
// ASP.Net		// FCKConfig.LinkBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Connector=connectors/aspx/connector.aspx' ;
// ColdFusion	// FCKConfig.LinkBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Connector=connectors/cfm/connector.cfm' ;
// Perl			// FCKConfig.LinkBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Connector=connectors/perl/connector.cgi' ;
// PHP			// FCKConfig.LinkBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Connector=connectors/php/connector.php' ;
// PHP - mcpuk	// FCKConfig.LinkBrowserURL = FCKConfig.BasePath + 'filemanager/browser/mcpuk/browser.html?Connector=connectors/php/connector.php' ;
try {
FCKConfig.LinkBrowserWindowWidth	= screen.width * 0.7 ;	// 70%
FCKConfig.LinkBrowserWindowHeight	= screen.height * 0.7 ;	// 70%
} catch(e) {
FCKConfig.LinkBrowserWindowWidth	= 800;	// 70%
FCKConfig.LinkBrowserWindowHeight	= 600 ;	// 70%
}


FCKConfig.ImageBrowser = true ;
FCKConfig.ImageBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=files&amp;Connector=connectors/php/connector.php' ;

// ASP.Net		// FCKConfig.ImageBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Image&amp;Connector=connectors/aspx/connector.aspx' ;
// ColdFusion	// FCKConfig.ImageBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Image&amp;Connector=connectors/cfm/connector.cfm' ;
// Perl			// FCKConfig.ImageBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Image&amp;Connector=connectors/perl/connector.cgi' ;
// PHP			// FCKConfig.ImageBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Image&amp;Connector=connectors/php/connector.php' ;
// PHP - mcpuk	// FCKConfig.ImageBrowserURL = FCKConfig.BasePath + 'filemanager/browser/mcpuk/browser.html?Type=Image&amp;Connector=connectors/php/connector.php' ;
try {
FCKConfig.ImageBrowserWindowWidth  = screen.width * 0.7 ;	// 70% ;
FCKConfig.ImageBrowserWindowHeight = screen.height * 0.7 ;	// 70% ;
} catch (e) {
FCKConfig.ImageBrowserWindowWidth  = 800 ;	// 70% ;
FCKConfig.ImageBrowserWindowHeight = 600 ;	// 70% ;

}
FCKConfig.SmileyPath	= FCKConfig.BasePath + 'images/smiley/msn/' ;
FCKConfig.SmileyImages	= ['regular_smile.gif','sad_smile.gif','wink_smile.gif','teeth_smile.gif','confused_smile.gif','tounge_smile.gif','embaressed_smile.gif','omg_smile.gif','whatchutalkingabout_smile.gif','angry_smile.gif','angel_smile.gif','shades_smile.gif','devil_smile.gif','cry_smile.gif','lightbulb.gif','thumbs_down.gif','thumbs_up.gif','heart.gif','broken_heart.gif','kiss.gif','envelope.gif'] ;
FCKConfig.SmileyColumns = 8 ;
FCKConfig.SmileyWindowWidth		= 320 ;
FCKConfig.SmileyWindowHeight	= 240 ;
</xsl:text>

FCKConfig.EditorAreaCSS = '<xsl:value-of select="concat($webroot,'themes/',$theme,'/css/',$themeCss)"/>';
FCKConfig.StylesXmlPath = '<xsl:value-of select="concat($webroot,'admin/fck/fckstyles.xml')"/>';
FCKConfig.DefaultLanguage = '<xsl:value-of select="$lang"/>';

</xsl:template>

</xsl:stylesheet>
