var title = FCKLang['smsymbol_title'] + ' (1.1.0)';

if (FCKConfig.smsymbol_width == null || FCKConfig.smsymbol_width == '') { FCKConfig.smsymbol_width = 704; }
if (FCKConfig.smsymbol_height == null || FCKConfig.smsymbol_height == '') { FCKConfig.smsymbol_height = 480; }

// Register the related commands.
FCKCommands.RegisterCommand('SMSymbol', new FCKDialogCommand(title, title, FCKConfig.PluginsPath + 'smsymbol/index.html', FCKConfig.smsymbol_width, FCKConfig.smsymbol_height));

// Create the "SMSymbol" toolbar button.
var oSMSymbolItem = new FCKToolbarButton('SMSymbol', FCKLang['smsymbol_desc']);
oSMSymbolItem.IconPath = FCKConfig.PluginsPath + 'smsymbol/img/icon_16x16.gif' ;

 // 'SMSymbol' is the name used in the Toolbar config.
FCKToolbarItems.RegisterItem('SMSymbol', oSMSymbolItem);

// The object used for all HtmlTiles operations.
var FCKSMSymbol = new Object();

// Insert a new html block at the actual selection.
FCKSMSymbol.Insert = function(html) {
	FCK.InsertHtml(html);
};