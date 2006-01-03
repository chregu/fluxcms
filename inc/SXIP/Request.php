<?php
/* ====================================================================
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
require_once 'SXIP/Data.php';
require_once 'Net/DNS.php';

/**
 * This class extends the SXIP::Base class.
 */
class SXIP_Request extends SXIP_Data {
    var $_force;
    var $_logout;
    var $_protocol;
    var $_uri;

    var $_formMinimal;
    var $_formAction;
    var $_formMethod;
    var $_formHeader;
    var $_formFooter;
    var $_buttonSrc;
    var $_buttonTitle;

    function SXIP_Request($args = '') {
        $this->SXIP_Data($args);

        $this->protocol($GLOBALS['SXIP_PROTOCOL']);
    }
    
    function getHomesiteCommandUri($fqdn) {
        $dns = new Net_DNS_Resolver();
        $q = $dns->query($fqdn.".command.sxip.net", "TXT");
        if ($q) {
            foreach ($q->answer as $rr) {
                if ("TXT" == $rr->type)
                    $r = str_replace("\"", "", $rr->text);
            }
            if (isset($r)) return $r;
            else {
                $this->warning(
                        "no TXT record found for ".$this->homesite());
                return false;
            }
        }
        else {
            return false;
        }
    }
    
    /**
    * @param	$property  The property to be stored.
    * @param    $context   A unique name for result in XML commands.
    * @param    $value     The value to be stored in '$property'.
    * @return   boolean    Result of method, errors generated as needed.
    *
    * Adds a value to be sent as a 'Store' or 'StoreX' request.
    * This has a slightly different backend from the one provided in the
    * Response module, because Request needs to know the name of the
    * '$property', but Response only has the '$context'.
    */
    function addStore($args) {
        $context = ( array_key_exists("context", $args) ) ?
            $args['context'] :
            "default";
        if (array_key_exists("property", $args)) $property = $args['property'];
        if (array_key_exists("value", $args)) $value = $args['value'];

        if (!$this->_isStore()) {
            $this->warning(
                "store is not possible with the '".
                $this->command()."' command");
            return false;
        }
        if ($property && "/sxip/" != substr($property, 0, 6)) {
            $this->warning(
                "property argument does not start with '/sxip/'");
        }
        if ($this->_isSimple()) {
            $this->_store[$property] = $value;
            return true;
        }
        else if ($this->_isXml()) {
            $this->_store[$context][$property] = $value;
            return true;
        }
    }

    /**
    * @param	$property  The property whose value your Membersite wants
    *					   to store.
    * @param    $context   The unique name, necessary for XML commands.
    * @return   $value     The value to be stored in $property.
    *
    * Gets a value to be sent as a 'Store' or 'StoreX' Request back out of the
    * Request object. This is slightly different from the Response version
    * in that '$context' and '$property' are applicable for the XML version.
    * Note that '$context' is only required for 'StoreX' and does nothing
    * otherwise.
    */
    function getStore($args) {
        $context = ( array_key_exists("context", $args) ) ?
            $args['context'] :
            "default";
        if (array_key_exists("property", $args)) $property = $args['property'];
        if (array_key_exists("default", $args)) $default = $args['default'];

        if (!$this->_isStore()) {
            $this->warning("'getStore' is not possible on non-Store objects");
            return false;
        }

        if ($this->_isSimple() &&
            array_key_exists($property, $this->_store)) {
            $r = $this->_store[$property];
        }
        else if ($this->_isXml() &&
            array_key_exists($context, $this->_store) &&
            array_key_exists($property, $this->_store[$context])) {
            $r = $this->_store[$context][$property];
        }
        if (isset($r)) return $r;
        else if (isset($default)) return $default;
        else return false;
    }

    /**
    * @param	none
    * @return   $xml    An XML representation of the Request data.
    *
    * Creates an XML representation of the Request data for use in the
    * 'sxip-request-xml' field of the Request form at the Membersite. This
    * method is used by all of the XML commands.
    */
    function toXml() {
        $o = "<".$this->command().
            " xmlns=\"".htmlentities($this->xmlns())."\"".
            " xmlns:sp=\"".htmlentities($this->xmlns_sp())."\"".
            " version=\"".htmlentities($this->version())."\"".
            " messageID=\"".htmlentities($this->messageID())."\"";
        if (strlen($this->explanation()))
            $o .= " explanation=\"".htmlentities($this->explanation())."\"";
        if (strlen($this->force()))
            $o .= " force=\"".htmlentities($this->force())."\"";
        if (strlen($this->context()))
            $o .= " context=\"".htmlentities($this->context())."\"";
        $o .= ">";

        // Begin child elements

        // P3P, if it exists
        if (strlen($this->p3p())) $o .= "<p3p>".htmlentities($this->p3p())."</p3p>";

        // propRequests for the fetches
        foreach ($this->_fetch as $context => $properties) {
            foreach ($properties as $property => $values) {
                foreach ($values as $value) {
                    $o .= "<propRequest".
                        " pathq=\"".htmlentities($property)."\"".
                        " context=\"".htmlentities($context)."\"".
                        " />";
                }
            }
        }

        // propStore for all the stores
        foreach ($this->_store as $context => $properties) {
            $o .= "<propStore".
                " context=\"".htmlentities($context)."\"".
                ">";
            foreach ($properties as $property => $value) {
                $s = split("/", $property);
                $last = array_pop($s);
                $first = array_shift($s);
                $version = date("Y-m-d");
                $grow = "<sp:".$last." value=\"".htmlentities($value)."' />";
                while ($next = array_pop($s)) {
                    $grow = "<sp:".$next.">".$grow."</sp:".$next.">";
                }
                $o .= "<sp:".$first." version=\"".htmlentities($version)."'>";
                $o .= $grow;
                $o .= "</sp:".$first.">";
            }
            $o .= "</propStore>";
        }

        // End child elements

        $o .= "</".$this->command().">";
        return $o;
    }

    function fromAssoc($args) {
        if (array_key_exists("sxip-request-command", $args))
            $this->command($args['sxip-request-command']);
        foreach ($args as $k => $v) {
            if (in_array($k, $GLOBALS['SXIP_REQUEST'])) {
                $temp = split("-", $k);
                $cmd = array_pop($temp);
                if (!$this->$cmd($v)) return false;
            }
            else if ("/sxip/" == substr($v, 0, 6)) {
                if (!$this->addFetch(array("property" => $v, "context" => $k)))
                    return false;
            }
            else if ("/sxip/" == substr($k, 0, 6)) {
                if (!$this->addStore(array("property" => $k, "value" => $v)))
                    return false;
            }
            else {
                if (!$this->addPass(array("name" => $k, "value" => $v)))
                    return false;
            }
        }
        return true;
    }


    /**
    * @param	none
    * @return	$html	The HTML form for submitting Request data.
    *
    * Generates a form, for inclusion in an HTML page, containing the Request
    * data as it should be sent to the Homesite. Please see the form-
    * customization functions in the "Getter-Setters" sections.
    */
    function toForm() {
        $o = "<form".
            " action='".$this->formAction()."'".
            " method='".$this->formMethod()."'>";
        if (strlen($this->formHeader())) $o .= $this->formHeader;
        if (!$this->formMinimal()) {
            if ($this->_isXml()) {
                $this->xml($this->toXml());
            }
            else if ($this->_isSimple()) {
                foreach ($this->_fetch as $context => $property) {
                    $o .= "<input".
                        " type='hidden'".
                        " name='".$context."'".
                        " value='".htmlentities($property, ENT_QUOTES)."' />";
                }
                foreach ($this->_store as $property => $value) {
                    $o .= "<input".
                        " type='hidden'".
                        " name='".$property."'".
                        " value='".htmlentities($value, ENT_QUOTES)."' />";
                }
            }
            foreach ($GLOBALS['SXIP_REQUEST'] as $command) {
                $s = split("-", $command);
                $cmd = array_pop($s);
                if ("sxip-request" == substr($command, 0, 12) &&
                    $this->$cmd()) {
                    $o .= "<input".
                        " type='hidden'".
                        " name='".$command."'".
                        " value='".htmlentities($this->$cmd(), ENT_QUOTES)."' />";
                }
            }
        }
        foreach ($this->_pass as $name => $value) {
            $o .= "<input".
                " type='hidden'".
                " name='".$name."'".
                " value='".$value."' />";
        }
        $o .= "<input".
            " type='image'".
            " name='sxipButton'".
            " src='".$this->buttonSrc()."'";
        if (strlen($this->buttonTitle())) " title='".$this->buttonTitle()."'";
        $o .= " />";
        if (strlen($this->formFooter())) $o .= $this->formFooter();
        $o .= "</form>";
        return $o;
    }

    ########
    ## Getter-Setters
    ##
    ## These are the functions used to manually get or set the internal
    ## Request options.

    /**
    * @param	$force	'true' is currently the only valid
    *                                    value for this.
    * @return	$force	When called with no options, it
    *					returns the existing or default value.
    *
    * This variable is an optional flag for the 'LoginX' command that indicates
    * that the Homesite MUST authenticate the browser and not use cached
    * authorization for determining the identity behind the browser.
    */
    function force($value = '') {
        if (strlen($value)) {
            if (('true' || '1' || 'false' || '0') != $value) {
                $this->warning(
                    "force can only be set to 'true', 'false', '1', or '0'");
                return false;
            }
            $this->_force = $value;
            return true;
        }
        else return $this->_force;
    }

    /**
    * @param	$logout	The URI to the logout script.
    * @return	$logout	The URI to the logout script with an
    *                   an additional 'messageID' to prevent
    *                   caching.
    *
    * This variable holds the URI to the logout script on the Membersite and,
    * when outputted, will be fitted with a new 'messageID' to prevent the
    * user's browser from caching the image.
    */
    function logout($value = '') {
        if (strlen($value)) {
            if (!preg_match('/^\w*:*\/\//', $value)) {
                $this->warning(
                    "logout must begin with a protocol, eg. 'http://'");
                return false;
            }
            $this->_logout = $value;
            return true;
        }
        else {
            if (false !== strpos($this->_logout, "?")) {
                return $this->_logout."&messageID=".
                    urlencode($this->makeMessageID());
            }
            else return $this->_logout."?messageID=".
                urlencode($this->makeMessageID());
        }
    }

    /**
    * @param	$protocol	The protocol for the return query.
    * @return	$protocol	When called with no options it
    *						returns the existing or default value.
    *
    * This variable holds the protocol ('http' or 'https') that the Homesite
    * will use to return the data.
    */
    function protocol($value = '') {
        if (strlen($value)) {
            if (('http' || 'https') != $value) {
                $this->warning(
                    "protocol can only be set to 'http' or 'https'");
                return false;
            }
            $this->_protocol = $value;
            return true;
        }
        else return $this->_protocol;
    }

    /**
    * @param	$uri	The return URI that the Homesite MUST
    *					send the Response data to.
    * @return	$uri	When called with no options it
    *					returns the existing or default value.
    *
    * This variable holds the URI that the Homesite MUST send the Response data
    * to.
    */
    function uri($value = '') {
        if (strlen($value)) {
            if (!preg_match('/^\w*:*\/\//', $value)) {
                $this->warning(
                    "uri must begin with a protocol, eg. 'http://'");
                return false;
            }
            $this->_uri = $value;
            return true;
        }
        else return $this->_uri;
    }

    ######
    ## FORM CONFIGURATION
    ##
    ##The following methods are form configuration options only.
    ##


    /**
    * @param	$form_minimal	Boolean, see below
    * @return	$form_minimal	Boolean
    *
    * When true, if no Homesite is set, this will output a form with no
    * data except return information that submits to the shadow domain
    * ('sxip.net') to get a Homesite in return.
    * By adding a passthrough variable named 'force' with a true value, this
    * informs the cookiehandler script that gets the Homesite that, if no
    * Homesite cookie is found, it should present a form to the user,
    * requesting that they enter a Homesite.
    *
    * Note that 'form_minimal' should be set to true in any situation where
    * you do not want to send any data other than passthrough data (for
    * example, in a post-click cookie retrieval scenario).
    */
    function formMinimal($value = '') {
        if (strlen($value)) {
            $this->_formMinimal = $value;
            return true;
        }
        else return $this->_formMinimal;
    }

    /**
    * @param	$form_action	Overrides the default form action.
    * @return	$form_action	Returns a form action based on
    *                           the Homesite, Membersite, or override.
    *
    * This variable controls what action is presented in the form in the
    * 'toForm()' method. If Homesite data is present, it will perform a DNS
    * lookup to find the text record for the Homesite's command script
    * and use that as the form action. If only a Membersite is present, the
    * variable defaults to going to the shadow domain for the Membersite.
    * Both of these defaults can be overriden by manually setting the value.
    */
    function formAction($value = '') {
        if (strlen($value)) {
            $this->_formAction = $value;
            return true;
        }
        else {
            if (strlen($this->_formAction)) return $this->_formAction;
            if (strlen($this->homesite())) {
                $r = $this->getHomesiteCommandUri($this->homesite());
                if (!$r) {
                    $this->warning("homesite lookup failed");
                    $r = "";
                }
                return "https://".$r;
            }
            if (strlen($this->membersite())) {
                return "http://".$this->membersite().".membersite.sxip.net/".
                    $SXIP_COOKIE_HANDLER;
            }
            $this->warning(
                "no form method could be found, please set a homesite, ".
                "membersite, or a formMethod");
            return false;
        }
    }

    /**
    * @param	$form_method	Overrides the default form method.
    * @return	$form_method	Either the default or the
    *							override.
    *
    * This method allows the developer to override the default form method. The
    * default is POST if a Homesite is defined and GET if only a Membersite is
    * defined. The default can be overridden by manually setting the value.
    */
    function formMethod($value = '') {
        if (strlen($value)) {
            if (('POST' || 'GET') != strtoupper($value)) {
                $this->warning(
                    "formMethod can only be set to 'POST' or 'GET'");
                return false;
            }
            $this->_formMethod = strtoupper($value);
            return true;
        }
        else {
            if (strlen($this->_formMethod)) return $this->_formMethod;
            if (strlen($this->homesite())) return 'POST';
            if (strlen($this->membersite())) return 'GET';
            $this->warning(
                "no form method could be found, please set a homesite, ".
                "membersite, or a formMethod");
            return false;
        }
    }

    /**
    * @param	$html	The HTML to include inside the form
    *                                  tag before the button.
    * @return	$html	When called with no arguments it
    *                   returns the existing or default value.
    *
    * This method allows the developer to add custom HTML within the form
    * before the button.
    */
    function formHeader($value = '') {
        if (strlen($value)) {
            $this->_formHeader = $value;
            return true;
        }
        else return $this->_formHeader;
    }

    /**
    * @param	$html	The HTML to include inside the form
    *					tag after the button.
    * @return	$html	When called with no arguments it
    *                   returns the existing or default value.
    *
    * This method allows the developer to add custom HTML inside the form after
    * the button.
    */
    function formFooter($value = '') {
        if (strlen($value)) {
            $this->_formFooter = $value;
            return true;
        }
        else return $this->_formFooter;
    }

    function buttonTitle($value = '') {
        if (strlen($value)) {
            $this->_buttonTitle = $value;
            return true;
        }
        else {
            if (!strlen($this->_buttonTitle)) {
                return $GLOBALS['SXIP_BUTTON_TITTLE'][$this->command()];
            }
            return $this->_buttonTitle;
        }
    }

    /**
    * @param	$src	The location of the image (URI).
    * @return           When called with no arguments  it
    *                   returns the existing or default value.
    *
    * This variable allows the user to set a custom 'src' attribute for each
    * Sxip button that displays a different image from the default. A default
    * set is provided in 'SXIP::Config'.
    */
    function buttonSrc($value = '') {
        if (strlen($value)) {
            $this->_buttonSrc = $value;
            return true;
        }
        else {
            if (!strlen($this->_buttonSrc)) {
                return $GLOBALS['SXIP_BUTTON_SRC'][$this->command()];
            }
            return $this->_buttonSrc;
        }
    }
}
?>
