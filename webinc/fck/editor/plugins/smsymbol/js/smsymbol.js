symbol_typo = new Array('&#8216;','&#8217;','&#8218;','&#8220;','&#8221;','&#8222;','&#38;','&#166;','&#169;','&#170;','&#171;','&#174;','&#182;','&#186;','&#187;','&#191;','&#8211;','&#8212;','&#8226;','&#8230;','&#8242;','&#8243;','&#8249;','&#8250;','&#8254;','&#9001;','&#9002;');
symbol_umlaut = new Array('&#168;','&#180;','&#184;','&#193;','&#196;','&#214;','&#220;','&#228;','&#246;','&#252;','&#352;','&#710;','&#732;');
symbol_currency = new Array('&#162;','&#163;','&#164;','&#165;','&#8364;');
symbol_alphabet = new Array('&#192;','&#194;','&#195;','&#197;','&#199;','&#200;','&#201;','&#202;','&#203;','&#204;','&#205;','&#206;','&#207;','&#208;','&#209;','&#210;','&#211;','&#212;','&#213;','&#216;','&#217;','&#218;','&#219;','&#221;','&#222;','&#223;','&#224;','&#225;','&#226;','&#227;','&#229;','&#231;','&#232;','&#233;','&#234;','&#235;','&#236;','&#237;','&#238;','&#239;','&#240;','&#241;','&#242;','&#243;','&#244;','&#245;','&#248;','&#249;','&#250;','&#251;','&#253;','&#254;','&#255;','&#353;','&#376;','&#402;');
symbol_math = new Array('&#60;','&#62;','&#172;','&#175;','&#176;','&#177;','&#178;','&#179;','&#183;','&#185;','&#188;','&#189;','&#190;','&#215;','&#247;','&#8240;','&#8260;','&#8465;','&#8472;','&#8476;','&#8501;','&#8704;','&#8706;','&#8707;','&#8709;','&#8711;','&#8712;','&#8713;','&#8715;','&#8719;','&#8721;','&#8722;','&#8727;','&#8730;','&#8733;','&#8734;','&#8736;','&#8743;','&#8744;','&#8745;','&#8746;','&#8747;','&#8756;','&#8764;','&#8773;','&#8776;','&#8800;','&#8801;','&#8804;','&#8805;','&#8834;','&#8835;','&#8836;','&#8838;','&#8839;','&#8853;','&#8855;','&#8869;','&#8901;');
symbol_greek = new Array('&#913;','&#914;','&#915;','&#916;','&#917;','&#918;','&#919;','&#920;','&#921;','&#922;','&#923;','&#924;','&#925;','&#926;','&#927;','&#928;','&#929;','&#931;','&#932;','&#933;','&#934;','&#935;','&#936;','&#937;','&#945;','&#946;','&#947;','&#948;','&#949;','&#950;','&#951;','&#952;','&#953;','&#954;','&#955;','&#956;','&#957;','&#958;','&#959;','&#960;','&#961;','&#962;','&#963;','&#964;','&#965;','&#966;','&#967;','&#968;','&#969;','&#977;','&#978;','&#982;');
symbol_dingbats = new Array();
symbol_grafik = new Array('&#34;','&#8968;','&#8969;','&#8970;','&#8971;','&#9674;','&#9824;','&#9827;','&#9829;','&#9830;');
symbol_else = new Array('&#161;','&#167;','&#181;','&#198;','&#230;','&#338;','&#339;','&#8224;','&#8225;','&#8482;','&#8592;','&#8593;','&#8594;','&#8595;','&#8596;','&#8629;','&#8656;','&#8657;','&#8658;','&#8659;','&#8660;');


// Initialisierung: Array "symbol_dingbats"
var m, n = 0;
for (m = 9985; m < 10175; m++) { symbol_dingbats[n] = '&#' + m + ';'; n++; }


// Symbol einfügen
function InsertSymbol(s) {
	FCKSMSymbol.Insert(s);
	window.parent.Cancel();
}

// Style festlegen
function SetStyle(id, bc, c){
	document.getElementById(id).style.backgroundColor = bc;
	document.getElementById(id).style.color = c;
}

// Vertikalen Abstand einfügen
function SetVerticalSpacing() {
	document.write('<div style="clear:left; height:10px; font-size:0px;"></div>');
}

// Symbole anzeigen
function ShowSymbol_1() {
	var i;

	// "Typografie"-Symbole anzeigen
	for (i = 0; i < symbol_typo.length; i++ ) {
		document.write('<div class="textbox" id="symbol_typo_' + i + '" onclick="InsertSymbol(\'' + symbol_typo[i]+ '\')" onmouseover="SetStyle(\'symbol_typo_' + i + '\', \'#616a74\', \'#ffffff\');" onmouseout="SetStyle(\'symbol_typo_' + i + '\', \'#ffffff\', \'#000000\');">' + symbol_typo[i] + '</div>');
		SetStyle('symbol_typo_' + i, '#ffffff', '#000000');
	}

	SetVerticalSpacing();

	// "Umlaut"-Symbole anzeigen
	for (i = 0; i < symbol_umlaut.length; i++ ) {
		document.write('<div class="textbox" id="symbol_umlaut_' + i + '" onclick="InsertSymbol(\'' + symbol_umlaut[i]+ '\')" onmouseover="SetStyle(\'symbol_umlaut_' + i + '\', \'#616a74\', \'#ffffff\');" onmouseout="SetStyle(\'symbol_umlaut_' + i + '\', \'#ffffff\', \'#000000\');">' + symbol_umlaut[i] + '</div>');
		SetStyle('symbol_umlaut_' + i, '#ffffff', '#000000');
	}

	SetVerticalSpacing();

	// "Währung"-Symbole anzeigen
	for (i = 0; i < symbol_currency.length; i++ ) {
		document.write('<div class="textbox" id="symbol_currency_' + i + '" onclick="InsertSymbol(\'' + symbol_currency[i]+ '\')" onmouseover="SetStyle(\'symbol_currency_' + i + '\', \'#616a74\', \'#ffffff\');" onmouseout="SetStyle(\'symbol_currency_' + i + '\', \'#ffffff\', \'#000000\');">' + symbol_currency[i] + '</div>');
		SetStyle('symbol_currency_' + i, '#ffffff', '#000000');
	}

	SetVerticalSpacing();

	// "Alphabet"-Symbole anzeigen
	for (i = 0; i < symbol_alphabet.length; i++ ) {
		document.write('<div class="textbox" id="symbol_alphabet_' + i + '" onclick="InsertSymbol(\'' + symbol_alphabet[i]+ '\')" onmouseover="SetStyle(\'symbol_alphabet_' + i + '\', \'#616a74\', \'#ffffff\');" onmouseout="SetStyle(\'symbol_alphabet_' + i + '\', \'#ffffff\', \'#000000\');">' + symbol_alphabet[i] + '</div>');
		SetStyle('symbol_alphabet_' + i, '#ffffff', '#000000');
	}

	SetVerticalSpacing();

	// Sonstige Symbole anzeigen
	for (i = 0; i < symbol_else.length; i++ ) {
		document.write('<div class="textbox" id="symbol_else_' + i + '" onclick="InsertSymbol(\'' + symbol_else[i]+ '\')" onmouseover="SetStyle(\'symbol_else_' + i + '\', \'#616a74\', \'#ffffff\');" onmouseout="SetStyle(\'symbol_else_' + i + '\', \'#ffffff\', \'#000000\');">' + symbol_else[i] + '</div>');
		SetStyle('symbol_else_' + i, '#ffffff', '#000000');
	}

	SetVerticalSpacing();

	// "Grafik"-Symbole anzeigen
	for (i = 0; i < symbol_grafik.length; i++ ) {
		document.write('<div class="textbox" id="symbol_grafik_' + i + '" onclick="InsertSymbol(\'' + symbol_grafik[i]+ '\')" onmouseover="SetStyle(\'symbol_grafik_' + i + '\', \'#616a74\', \'#ffffff\');" onmouseout="SetStyle(\'symbol_grafik_' + i + '\', \'#ffffff\', \'#000000\');">' + symbol_grafik[i] + '</div>');
		SetStyle('symbol_grafik_' + i, '#ffffff', '#000000');
	}
}

// Symbole anzeigen
function ShowSymbol_2() {

	// "Mathematik"-Symbole anzeigen
	for (i = 0; i < symbol_math.length; i++ ) {
		document.write('<div class="textbox" id="symbol_math_' + i + '" onclick="InsertSymbol(\'' + symbol_math[i]+ '\')" onmouseover="SetStyle(\'symbol_math_' + i + '\', \'#616a74\', \'#ffffff\');" onmouseout="SetStyle(\'symbol_math_' + i + '\', \'#ffffff\', \'#000000\');">' + symbol_math[i] + '</div>');
		SetStyle('symbol_math_' + i, '#ffffff', '#000000');
	}
}

// Symbole anzeigen
function ShowSymbol_3() {

	// "Griechisches Alphabet"-Symbole anzeigen
	for (i = 0; i < symbol_greek.length; i++ ) {
		document.write('<div class="textbox" id="symbol_greek_' + i + '" onclick="InsertSymbol(\'' + symbol_greek[i]+ '\')" onmouseover="SetStyle(\'symbol_greek_' + i + '\', \'#616a74\', \'#ffffff\');" onmouseout="SetStyle(\'symbol_greek_' + i + '\', \'#ffffff\', \'#000000\');">' + symbol_greek[i] + '</div>');
		SetStyle('symbol_greek_' + i, '#ffffff', '#000000');
	}
}

// Symbole anzeigen
function ShowSymbol_4() {

	// "Dingbats"-Symbole anzeigen
	for (i = 0; i < symbol_dingbats.length; i++ ) {
		document.write('<div class="textbox" id="symbol_dingbats_' + i + '" onclick="InsertSymbol(\'' + symbol_dingbats[i]+ '\')" onmouseover="SetStyle(\'symbol_dingbats_' + i + '\', \'#616a74\', \'#ffffff\');" onmouseout="SetStyle(\'symbol_dingbats_' + i + '\', \'#ffffff\', \'#000000\');">' + symbol_dingbats[i] + '</div>');
		SetStyle('symbol_dingbats_' + i, '#ffffff', '#000000');
	}
}

// Oberfläche initialisieren
var dialog = window.parent;

// Tabs hinzufügen
dialog.AddTab('t_1', FCKLang.smsymbol_tab_1);
dialog.AddTab('t_2', FCKLang.smsymbol_tab_2);
dialog.AddTab('t_3', FCKLang.smsymbol_tab_3);
dialog.AddTab('t_4', FCKLang.smsymbol_tab_4);

// Aktiven Tab anzeigen
function OnDialogTabChange(tabCode) {
	ShowE('tab_1', (tabCode == 't_1'));
	ShowE('tab_2', (tabCode == 't_2'));
	ShowE('tab_3', (tabCode == 't_3'));
	ShowE('tab_4', (tabCode == 't_4'));
}
