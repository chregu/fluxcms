<?php
/*
====================================================================
The Sxip Networks Software License, Version 1

Copyright (c) 2004 Sxip Networks Inc. All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions
are met:

1. Redistributions of source code must retain the above copyright
   notice, this list of conditions and the following disclaimer.

2. Redistributions in binary form must reproduce the above copyright
   notice, this list of conditions and the following disclaimer in
   the documentation and/or other materials provided with the
   distribution.

3. The end-user documentation included with the redistribution,
   if any, must include the following acknowledgment:
      "This product includes software developed by
       Sxip Networks Inc. (https://sxip.org)."
   Alternately, this acknowledgment may appear in the software itself,
   if and wherever such third-party acknowledgments normally appear.

4. The names "Sxip" and "Sxip Networks" must not be used to endorse
   or promote products derived from this software without prior
   written permission. For written permission, please contact
   bizdev@sxip.org.

5. Products derived from this software may not be called "Sxip",
   nor may "Sxip" appear in their name, without prior written
   permission of Sxip Networks Inc.

THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESSED OR IMPLIED
WARRANTIES OR CONDITIONS, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OR CONDITIONS OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL SXIP NETWORKS OR ITS
CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
====================================================================

*/

## The default values for many of the SXIP objects.
## Some of these should be edited to match the corresponding values
## at your website.

/**
* MEMBERSITE
* This is the domain name of your website that is registered as a Sxip
* Membersite. If you do not change this variable to reflect your
* domain, you will need to manually specify a membersite at every
*instantiation of a SXIP object.
*/
$GLOBALS['SXIP_MEMBERSITE'] = "example.com";

/**
* COOKIE HANDLER PATH
* This is the path, relative to your web root, that you need to point
* to under the sxip.net shadow domain to retrieve the 'sxip.homesite'
* cookie.
*/
$GLOBALS['SXIP_COOKIEHANDLER_PATH'] = "examples/demo-cookiehandler.php";

/**
* PROTOCOL
* This is the return protocol that the Homesite should use to post
* the data back to your Membersite, via the user's browser.
* The default is http, but https is more secure.
*/
$GLOBALS['SXIP_PROTOCOL'] = "http";

/**
* XMLNS and XMLNS_SP
* These are the two XML namespaces for the SxipML Schema and the Sxip
* Properties Schema. It is unlikely that you will need to change these
* values.
*/
$GLOBALS['SXIP_XMLNS'] = "http://www.sxip.net/ns/sxip";

$GLOBALS['SXIP_XMLNS_SP'] = "http://www.sxip.net/ns/property";

/**
* VERSION
* This is the schema version in use. This shouldn't need to be changed
* because the MDK it was written for has only been tested with this
* version.
*/
$GLOBALS['SXIP_VERSION']= "1.0";

// $GLOBALS['SXIP_P3P'] = "http://example.com/p3p.html";

/**
* BUTTON_SRC
* This hash sets the default image location based on the command that
* is being used. In most situations, there should only be five images,
* as shown below.
*/
$GLOBALS['SXIP_BUTTON_SRC'] = array(
    "login" => "images/sxip_in.gif",
    "loginx" => "images/sxip_in.gif",
    "store" => "images/sxip_back.gif",
    "storex" => "images/sxip_back.gif",
    "fetch" => "images/sxip_this.gif",
    "fetchx" => "images/sxip_this.gif",
    "logout" => "images/sxip_out.gif",
    "checkmark" => "images/sxip_checkmark.gif" );

/**
* BUTTON_TITLE
* Much like button_src, this hash represents the default captions that
* are placed in the title attribute of the Sxip button. These captions
* are rendered by most browser as info boxes on mouseOver.
*/
$GLOBALS['SXIP_BUTTON_TITLE'] = array(
    "login" => "Sign on using your Sxip Identity",
    "loginx" => "Sign on using your Sxip Identity",
    "fetch" => "Fill this form using your Sxip Identity",
    "fetchx" => "Fill this form using your Sxip Identity",
    "store" => "Store this to your Sxip Identity",
    "storex" => "Store this to your Sxip Identity",
    "logout" => "Sign out using your Sxip Identity" );

/**
* These are the default arrays of the command types, placing them in
* the appropriate categories.
*/
$GLOBALS['SXIP_FETCH'] = array('login', 'loginx', 'fetch', 'fetchx');
$GLOBALS['SXIP_STORE'] = array('store', 'storex');
$GLOBALS['SXIP_SIMPLE'] = array('login', 'fetch', 'store', 'logout');
$GLOBALS['SXIP_XML'] = array('loginx', 'fetchx', 'storex');
$GLOBALS['SXIP_COMMAND'] = array(
                    'login',
                    'loginx',
                    'fetch',
                    'fetchx',
                    'store',
                    'storex',
                    'logout');



define('DEBUG_INFO',      E_USER_NOTICE);
define('DEBUG_WARNING',   E_USER_WARNING);
define('DEBUG_ERROR',     E_USER_ERROR);
define('DEBUG_NONE',      0);

/**
* REQUEST
* These are all the commands that a Request object will
* recognize in fromHash.
*/
$GLOBALS['SXIP_REQUEST'] = array( 'sxip-request-command',
                    'sxip-request-explanation',
                    'sxip-request-logout',
                    'sxip-request-membersite',
                    'sxip-request-p3p',
                    'sxip-request-protocol',
                    'sxip-request-uri',
                    'sxip-request-xml',
                    'sxip-context',
                    'sxip-force',
                    'sxip-homesite',
                    'sxip-messageID',
                    'sxip-version',
                    'sxip-xmlns',
                    'sxip-xmlns_sp' );

/**
* RESPONSE
* These are all the commands that a Response object will
* recognize in fromHash.
*/
$GLOBALS['SXIP_RESPONSE'] = array( 'sxip-response-code',
                    'sxip-response-command',
                    'sxip-response-message',
                    'sxip-response-xml',
                    'sxip-gupi',
                    'sxip-context',
                    'sxip-explanation',
                    'sxip-instant',
                    'sxip-membersite',
                    'sxip-method',
                    'sxip-messageID',
                    'sxip-p3p',
                    'sxip-responseID',
                    'sxip-version' );

?>
