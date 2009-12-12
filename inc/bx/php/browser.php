<?php
include("../conf/config.inc.php");
print "Agent : ";
print popoon_classes_browser::getAgent();
print "<br/>";


print "BrowserName : ";
print popoon_classes_browser::getName();
print "<br/>";
print "BrowserSubName : ";
print popoon_classes_browser::getSubName();
print "<br/>";

print "Platform : ";
print popoon_classes_browser::getPlatform();
print "<br/>";

print "Version : ";
print popoon_classes_browser::getVersion();
print "<br/>";

print "isMozilla: ";
var_dump( popoon_classes_browser::isMozilla());
print "<br/>";


print "isMSIEWin: ";
var_dump( popoon_classes_browser::isMSIEWin());
print "<br/>";

print "isSafari: ";
var_dump( popoon_classes_browser::isSafari());
print "<br/>";