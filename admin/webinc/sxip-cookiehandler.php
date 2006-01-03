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



// The name of the cookie to be checked for by the demos
$SXIP_HOMESITE_COOKIE = "sxip-homesite";



include_once("../../conf/config.inc.php");
//require_once 'demo-config.php';
require_once 'SXIP/Request.php';
require_once 'SXIP/Response.php';

// The variable $action defines which process
// to go through in the script.
// The action can be set manually to attempt to force a certain
// type of processing.
if ( array_key_exists('action', $_REQUEST) ) {
    $action = $_REQUEST['action'];
}
// If response data is found in the GET or POST variables
// the script will process a response. In
// this demo this functionality does not really do much
// but in practice this script could be used to perform
// specific tasks upon receiving responses.
else if ( array_key_exists('sxip-response-command', $_REQUEST) ) {
    $action = "processResponse";
}
// Likewise, if request data is found in the GET or POST variables
// the script will process a request. In this case
// that means it will attempt to retrieve the Homesite from the
// cookie, re-generate the request from the data provided, and then
// output an auto-submitting form to post the data to the Homesite,
// in effect forwarding the request.
else if ( array_key_exists('sxip-request-command', $_REQUEST) ) {
    $action = "processRequest";
}
// If no SXIP data is sent to the script (as happens when using
// the form_minimal(1) setting), the script will attempt to
// retrieve the Homesite from the cookie and return it to a page
// specified by $returnPage and $returnQuery.
else {
    $action = "getHomesite";
}

// The default action is to read the cookie and return the
// value. If 'force' is set and no cookie is present, it will present the user
// with a form asking them to enter the URL to their Homesite.
if ("getHomesite" == $action) {
    $homesite = (array_key_exists($SXIP_HOMESITE_COOKIE, $_COOKIE))
        ? $_COOKIE[$SXIP_HOMESITE_COOKIE] : "";

    $force = (array_key_exists('force', $_REQUEST)) ? $_REQUEST["force"] : 0;
    $returnPage = (array_key_exists('returnPage', $_REQUEST))
        ? $_REQUEST["returnPage"] : "";
    $returnQuery = (array_key_exists('returnQuery', $_REQUEST))
        ? $_REQUEST['returnQuery'] : "";
    $valid_homesite = false;
    if ($homesite) {
        $valid_homesite = SXIP_Request::getHomesiteCommandUri($homesite);
    }
    // If 'force' isn't set, or a Homesite was found in the cookie
    // a redirect back to the page specified by $returnPage
    // and $returnQuery (usually the calling page) occurs, sending the
    // value found (blank if no homesite cookie was present), and a
    // 'checked' flag to declare that the script has beeen called
    // to check for a Homesite cookie.
    if (!$force || ($homesite && $valid_homesite)) {
        if ($returnQuery) {
            $returnQuery .= "&";
        }
        $returnQuery .= $SXIP_HOMESITE_COOKIE."=".urlencode($homesite);
        $returnQuery .= "&checked=1";
        header("Location: ".$returnPage."?".$returnQuery);
    }
    // If 'force' was set and no Homesite was found, a form will be presented
    // that asks the user to enter their Homesite.
    else
    {
        $html =  "<html><head><title>Enter your Homesite</title></head>";
        if ($homesite && !$valid_homesite) {
            $html .= "<body><p>No Homesite cookie was found on your browser";
        }
        else {
            $html .= "<body><p>The Homesite cookie found on your browser";
            $html .= " was not valid";
        }
        $html .= ". Please enter the address of your Homesite below.";
        $html .= "<form method='GET' action='".htmlentities($returnPage);
        $html .= "'><input type='text' size='32' maxlength='80'";
        $html .= " name='".$SXIP_HOMESITE_COOKIE."' />";
        foreach ($_REQUEST as $key => $value) {
            $html .= "<input type='hidden' name='".$key."'";
            $html .= " value='".htmlentities($value)."'>";
        }
        $html .= "<input type='submit' name='Submit'";
        $html .= " value='Continue' /></form></body></html>";
        echo $html;
    }
}
// This is not an action that will be accessed under default
// operation, but it's provided as an example of how a post-click
// scenario might work with logic applied at the intermediate step.
// If this action is called, the request will effectively be forwarded
// on to the Homesite by taking the values present in the GET and POST
// headers and reforming them into a new auto-submitting form.
else if ("processRequest" == $action) {
    $homesite = (array_key_exists($SXIP_HOMESITE_COOKIE, $_REQUEST)) ?
        $_REQUEST[$SXIP_HOMESITE_COOKIE] : "";
    $valid_homesite = false;
    if ($homesite) {
        $valid_homesite = SXIP_Request::getHomesiteCommandUri($homesite);
    }
    
    if (!$homesite || !$valid_homesite) {
        // Display the form for user to enter Homesite (from above)

        $html =  "<html><head><title>Enter your Homesite</title></head>";
        if ($homesite && !$valid_homesite) {
            $html .= "<body><p>No Homesite cookie was found on your browser";
        }
        else {
            $html .= "<body><p>The Homesite cookie found on your browser";
            $html .= " was not valid";
        }
        $html .= ". Please enter the address of your Homesite below.";
        $html .= "<form method='GET' action='";
        $html .= htmlentities($_SERVER['PHP_SELF']);
        $html .= "'><input type='text' size='32' maxlength='80'";
        $html .= " name='".$SXIP_HOMESITE_COOKIE."' />";
        foreach ($_REQUEST as $key => $value) {
            $html .= "<input type='hidden' name='".$key."'";
            $html .= " value='".htmlentities($value)."'>";
        }
        $html .= "<input type='submit' name='Submit'";
        $html .= " value='Continue' /></form></body></html>";
        echo $html;
        // Pass all other variables along as hidden variables.
        // We could also do this via JavaScript and
        // set the form action on submit.
    }
    else {
        # The easy way, should auto-generate from the given GET and POST vars
        $req = new SXIP_Request;
        $req->fromAssoc($_REQUEST);
        $req->homesite($homesite);
        $form = $req->toForm();

        $html = "<html><head><title>SXIP Request Processor</title></head>";
        $html .= "<body onLoad='javascript:document.forms[0].submit()'>";
        $html .= $form."</body></html>";
        echo $html;
    }
}
// This action does not really do much in this demo,
// but it could be used to perform
// specific tasks upon receiving responses.
else if ("processResponse" == $action) {
    $html = "<html><head><title>SXIP Response Processor</title></head>";
    $html .= "<body>";
    $res = new SXIP_Response();
    $res->fromAssoc($_REQUEST);

    // If you wanted to do all the response processing in one script,
    // this is where you would put it.

    $html .= "</body></html>";
    echo $html;
}
// See: demo-logout.php
else if ("logout" == $action) {
    // Perform logout actions such as unsetting session data on the server
    // Pass on the checkmark image
}
