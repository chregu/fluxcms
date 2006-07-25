/*
ColorPicker - Copyright (c) 2004, 2005 Norman Timmler (inlet media e.K., Hamburg, Germany)
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions
are met:
1. Redistributions of source code must retain the above copyright
   notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright
   notice, this list of conditions and the following disclaimer in the
   documentation and/or other materials provided with the distribution.
3. The name of the author may not be used to endorse or promote products
   derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/



colorPicker_HexValues = Array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
colorPicker_currentColor = '';
colorPicker_parentInputElementName = null;
colorPicker_colorpickerbox_x = 0;
colorPicker_colorpickerbox_y = 0;

document.onmousemove = function(evt) {
  colorPicker_colorpickerbox_x = document.all ? event.clientX : document.layers ? evt.x : evt.clientX;
  colorPicker_colorpickerbox_y = document.all ? event.clientY : document.layers ? evt.y : evt.clientY;
}

document.writeln("<style type=\"text/css\">");
document.writeln("#colorPicker_colorpickerbox {position: absolute; top: 0px; left: 0px; visibility: hidden; border: 1px solid #000000;padding: 10px; width: 291px; height: 165px; background-color: #FFFFFF;  z-index: 99; }");
document.writeln("#colorPicker_colorbox {position: absolute; border: 1px solid #000000; top: 35px; left: 160px; font-size: 40px;}");
document.writeln("#colorPicker_colorbox img {border: 0px; width: 128px; height: 15px;}");
document.writeln("#colorPicker_spectrumbox {position: absolute; border: 1px solid #000000; font-size: 40px;}");
document.writeln("#colorPicker_spectrumbox img {border: 0px; width: 3px; height: 15px;}");
document.writeln("#colorPicker_satbrightbox {position: absolute; border: 1px solid #000000; top: 35px; left: 10px;}");
document.writeln("#colorPicker_satbrightbox img {border: 0px; width: 8px; height: 8px;}");
document.writeln("#colorPicker_hexvaluebox {position: absolute; border: 0px solid #000000; top: 70px; left: 160px; font-size: 14px;}");
document.writeln("#colorPicker_hexvaluebox input {border: 1px solid #000000; width: 100px; font-family: font-size: 14px;}");
document.writeln("#colorPicker_controlbox {position: absolute; border: 0px solid #000000; top: 120px;left: 210px;}");
document.writeln("#colorPicker_controlbox input {border: 1px solid #000000; width: 78px; height: 16px; font-family: font-size: 10px; background-color: #FFFFFF;}");
document.writeln("</style>");

document.writeln("<div id=\"colorPicker_colorpickerbox\">");
document.writeln("    <div id=\"colorPicker_spectrumbox\">&nbsp;</div>");
document.writeln("    <div id=\"colorPicker_satbrightbox\">&nbsp;</div>");
document.writeln("    <div id=\"colorPicker_colorbox\"><img src=\"" + colorPicker_spacerImage + "\" /></div>");
document.writeln("    <div id=\"colorPicker_hexvaluebox\">#<input type=\"text\" value=\"\" /></div>");
document.writeln("    <div id=\"colorPicker_controlbox\"><input type=\"submit\" value=\"OK\" onclick=\"colorPicker_ok();\" /><br /><br /><input type=\"submit\" value=\"Cancel\" onclick=\"colorPicker_hide();\" /></div>");
document.writeln("</div>");

function colorPicker_showSatBrightBox(col) {
  element = colorPicker_getElementById('colorPicker_satbrightbox');
  html = '';
  
  s = 16; // steps
  colEnd = Array();
  
  col[0] = 256 - col[0];
  col[1] = 256 - col[1];
  col[2] = 256 - col[2];
  
  
  // calculating row end points
  for (j = 0; j < 3; j++) {
    colEnd[j] = Array();
    for (i = s; i > -1; i--) {
      colEnd[j][i] =  i * Math.round(col[j] / s);
    }
  }
  
  hexStr = '';
  for (k = s; k > -1; k--) {
    for (i = s; i > -1; i--) {
      for (j = 0; j < 3; j++) {
        dif = 256 - colEnd[j][k];
        quot = (dif != 0) ? Math.round(dif / s) : 0;
        hexStr += colorPicker_toHex(i * quot);
      }
      html += "<span style=\"background-color:#" +
        hexStr + ";\"><a href=\"#\" onclick=\"colorPicker_ShowColorBox('" +
        hexStr + "');\"><img src=\"" + colorPicker_spacerImage + "\"/></a></span>";
      hexStr = '';
    }
    html += "<br />";
  }
  
  element.innerHTML = html;
}

function colorPicker_showSpectrumBox() {
  element = colorPicker_getElementById('colorPicker_spectrumbox');
  html = '';
  
  d = 1; // direction
  c = 0; // count
  v = 0; // value
  s = 16; // steps
  col = Array(256, 0, 0); // color array [0] red, [1] green, [2] blue
  ind = 1; // index
  cel = 256; //ceiling
  
  while (c < (6 * 256)) {
    html += "<span style=\"background-color:#" + colorPicker_toHex(col[0]) + colorPicker_toHex(col[1]) + colorPicker_toHex(col[2]) +
      "\"><a href=\"#\" onclick=\"colorPicker_showSatBrightBox(Array(" +
      col[0] + "," + col[1] + "," + col[2] + "));\"><img src=\"" + colorPicker_spacerImage + "\" /></a></span>";
    
    c += s;
    v += (s * d);
    col[ind] = v;
    
    if (v == cel) {
      ind -= 1;
      if (ind == -1) ind = 2;
      d = d * -1;
    }
    
    if (v == 0) {
      ind += 2;
      if (ind == 3) ind = 0;
      d = d * -1;
    }
  }
  element.innerHTML = html;
  
  colorPicker_showSatBrightBox(col);
}


function colorPicker_toHex(num) {
  if (num > 0) num -= 1;
  base = num / 16;
  rem = num % 16;
  base = base - (rem / 16);
  return colorPicker_HexValues[base] + colorPicker_HexValues[rem];
}

function colorPicker_ShowColorBox(hexStr){
  colorbox = colorPicker_getElementById('colorPicker_colorbox');
  colorboxhtml = "<span style=\"background-color:#" + hexStr + "\"><img src=\"" + colorPicker_spacerImage + "\" /></span>";
  colorbox.innerHTML = colorboxhtml;
  
  hexvaluebox = colorPicker_getElementById('colorPicker_hexvaluebox');
  hexvalueboxhtml = "#<input type=\"text\" value=\"" + hexStr + "\" />";
  hexvaluebox.innerHTML = hexvalueboxhtml;
  
  colorPicker_currentColor = hexStr;
}

function colorPicker_show(elementName) {
  colorPicker_showSpectrumBox();
  
  element = colorPicker_getElementById('colorPicker_colorpickerbox');
  element.style.left = colorPicker_colorpickerbox_x + 'px';
  element.style.top = colorPicker_colorpickerbox_y + 'px';
  element.style.visibility = 'visible';
  
  colorPicker_parentInputElementName = elementName;
}

function colorPicker_ok() {
  console.log(colorPicker_parentInputElementName);
  obj = colorPicker_getElementById(colorPicker_parentInputElementName);
  obj.value = colorPicker_currentColor;
  colorPicker_preview(colorPicker_parentInputElementName);
  colorPicker_hide();
}


function colorPicker_preview(elementName){
  element = colorPicker_getElementById(elementName);
  color = element.value;
  element = colorPicker_getElementById('anchor_' + elementName);
  element.style.backgroundColor = '#' + color;
}

function colorPicker_hide() {
  element = colorPicker_getElementById('colorPicker_colorpickerbox');
  element.style.visibility = 'hidden';
}

function colorPicker_getElementById(e, f) {
  if (document.getElementById) {
    return document.getElementById(e);
  }
  if(document.all) {
     return document.all[e];
  }
}

