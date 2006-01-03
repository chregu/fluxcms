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
require_once 'SXIP/Base.php';

/**
 * This class extends the SXIP::Base class.
 */
class SXIP_Data extends SXIP_Base {
    var $_fetch;
    var $_store;
    var $_pass;

    var $_command;
    var $_context;
    var $_explanation;
    var $_homesite;
    var $_membersite;
    var $_p3p;
    var $_messageID;
    var $_version;
    var $_xml;
    var $_xmlns;
    var $_xmlns_sp;

    function SXIP_Data($args) {
        $this->SXIP_Base($args);

        // Set some defaults
        $this->membersite($GLOBALS['SXIP_MEMBERSITE']);
        if (isset($SXIP_P3P)) $this->p3p($SXIP_P3P);

        $this->_fetch = array();
        $this->_store = array();
        $this->_pass = array();
    }

    /**
    * @param  none
    * @return $messageID    A 16-bit hexadecimal string.
    *
    * Generates a semi-random number (a 16-bit hexadecimal string) to be used
    * as a verification measure in the XML commands.
    */
    function makeMessageID() {
        $messageID = time().(rand() * 99999);
        return $messageID;
    }

    /**
    * @param  $context      The context for the query.
    * @param  $property     The Sxip property being queried.
    * @param  $value        The value of the property fetch.
    *                       This is mainly used by the Response object.
    * @return bool
    *
    * Adds fetch data to the object. Only the Response will really find
    * '$value' applicable, since the value of the property is returned in the
    * response from the Homesite. In the Simple protocol, the value of
    * '$context' is local name of the requested property (that is, it is the
    * <response-name>in the name-value pairs for property requests and
    * responses in the Sxip commands). In the XML protocol, the value of
    * '$context' is session data that is defined by the Membersite and returned
    * in the corresponding response from the Homesite. The addFetch (%args)
    * method only supports 'Login', 'LoginX', 'Fetch', and 'FetchX'.
    */
    function addFetch($args) {
        $context = ( array_key_exists("context", $args) ) ?
            $args['context'] :
            "default";
        if (array_key_exists("property", $args)) $property = $args['property'];
        if (array_key_exists("value", $args)) $value = $args['value'];

        // Sanity Checks
        if (!$this->_isFetch()) {
            $this->warning(
                "fetch is not possible with the '".
                $this->command()."' command");
            return false;
        }
        if ($property && "/sxip/" != substr($property, 0, 6)) {
            $this->warning(
                "property argument does not start with '/sxip/'");
        }

        if ($this->_isSimple()) {
            if (isset($value)) {
                $this->_fetch[$context] = $value;
                if (isset($property)) {
                    $this->info(
                        "property argument given with value, using value");
                }
            }
            else if (isset($property)) {
                $this->_fetch[$context] = $property;
            }
            else {
                $this->warning(
                    "addFetch requires a property or a value, but ".
                    "received neither for: ".$context);
                return false;
            }
            return true;
        }
        else if ($this->_isXml()) {
            if (!isset($property)) {
                $this->warning("addFetch requires a property for XML objects");
                return false;
            }
            $value = (isset($value)) ? $value : "true";
            $this->_fetch[$context][$property][] = $value;
            return true;
        }
        $this->warning("no command set, cannot addFetch");
        return false;
    }

    /**
    * @param  $name    The name of the passthrough data.
    * @param  $value   The value of the passthrough data.
    * @return bool
    *
    * Adds passthrough data to the object.
    */
    function addPass($args) {
        if (array_key_exists("name", $args)) {
            $name = $args['name'];
        }
        else {
            $this->warning("addPass requires a 'name' but none was given");
            return false;
        }
        if (array_key_exists("value", $args)) {
            $value = $args['value'];
        }
        else {
            $this->warning("addPass requires a 'value' but none was given");
            return false;
        }

        // Warnings
        if ("/sxip/" == substr($name, 0, 6)) {
            $this->warning(
                "addPass 'name' begins with '/sxip/' and may be ".
                "interpreted as store data");
        }
        if ("/sxip/" == substr($value, 0, 6)) {
            $this->warning(
                "addPass 'value' begins with '/sxip/' and may be ".
                "interpreted as fetch data");
        }

        $this->_pass[$name] = $value;
        return true;
    }

    /**
    * @param  $context  	The context of the property fetch
    *						data your Membersite wants.
    * @param  $property 	The Sxip property name of the data
    *						your Membersite wants.
    * @param  $default  	A default value to return if empty.
    * @return $fetchData	The fetch data stored in the object.
    *
    * Returns the Sxip property fetch data stored in the object.
    * The '$property' argument is not used for the Simple protocol, but is
    * required for the XML protocol. This method is typically only used by the
    * Response object for retrieving data requested in an 'addFetch' method. In
    * the case of multiple responses for the same item in an XML response, only
    * the first is returned. For example, if a user has more than one fax
    * number, only the first fax number is returned with this method. Use
    * 'getFetchALL' for multiple responses.
    */
    function getFetch($args) {
        $context = (array_key_exists("context", $args)) ?
                    $args['context'] :
                    "default";
        if (array_key_exists("property", $args)) $property = $args['property'];
        if (array_key_exists("default", $args)) $default = $args['default'];

        if (!$this->_isFetch()) {
            $this->warning("cannot use getFetch on a non-Fetch object");
            return false;
        }
        if ($this->_isSimple()) {
            if (isset($property)) $this->info(
                "'property' is not a required argument of getFetch ".
                "in a Simple object");
            if (array_key_exists($context, $this->_fetch))
                $r = $this->_fetch[$context];
        }
        else if ($this->_isXml()) {
            if (!isset($property)) {
                $this->warning(
                    "'property' is a required argument of getFetch ".
                    "in an XML object");
                return false;
            }
            if (array_key_exists($context, $this->_fetch) &&
                array_key_exists($property, $this->_fetch[$context]))
                $r = $this->_fetch[$context][$property][0];
        }
        if (isset($r)) return $r;
        else if (isset($default)) return $default;
        else return false;
    }

    /**
    * @param  $context        The context of the property fetch
    *		  			  	  data your Membersite wants.
    * @param  $property       The Sxip property name of the data
    *					  	  your Membersite wants.
    * @param  @default        An array of the fetch data stored
    *					  	  in the object.
    * @return @dfetchData     Array  of the fetch data.
    *
    * This method is only used by the XML protocol. It is similar to
    * 'getFetch()', except it returns the entire array of data for a
    * context-property pair. This is useful if there are multiple
    * returns for one query. For example, if a user has more than one
    * fax number, both fax numbers can be returned with this method.
    */
    function getFetchAll($args) {
        $context = (array_key_exists("context", $args)) ?
                    $args['context'] :
                    "default";
        if (array_key_exists("property", $args)) $property = $args['property'];
        if (array_key_exists("default", $args)) $default = $args['default'];


        if (!$this->_isFetch()) {
            $this->warning("cannot use getFetchAll on a non-Fetch object");
            return false;
        }
        if (!$this->_isXml()) {
            $this->warning("cannot use getFetchAll on a non-XML object");
            return false;
        }
        if (!isset($property)) {
            $this->warning(
                "'property' is a required argument of getFetchAll");
            return false;
        }
        if (array_key_exists($context, $this->_fetch) &&
            array_key_exists($property, $this->_fetch[$context]))
            $r = $this->_fetch[$context][$property];

        if (isset($r)) return $r;
        else if (isset($default)) return $default;
        else return false;
    }

    /**
    * @param    $name       The name of the passthrough data.
    * @return   $default    A default value if empty.
    * @return   $value      The value of the pass data
    *
    * Returns the value of the passthrough data or a default value if no
    * value is available.
    */
    function getPass($args) {
        if (array_key_exists("name", $args)) {
            $name = $args['name'];
        }
        else {
            $this->warning("'name' is required argument of getPass");
            return false;
        }
        if (array_key_exists("default", $args)) $default = $args['default'];

        if (array_key_exists($name, $this->_pass)) return $this->_pass[$name];
        else if (isset($default)) return $default;
        else return false;
    }
    /*
    * Sanity Checks
    *
    * These functions make sure that everything is running smoothly
    * and try to prevent you from doing things you are not allowed to do.
    */
    /**
    * @param  none
    * @return bool      True if the object has a fetch command, else false.
    *
    * Checks to see if the command type for the object exists in the list
    * of fetch commands. For example, it is a 'fetch' or 'fetchx' command.
    */
    function _isFetch() {
        if (in_array($this->command(), $GLOBALS['SXIP_FETCH'])) return true;
        return false;
    }

    /**
    * @param  none
    * @return bool    True the object has a store command,	else false.
    *
    * Checks to see if the command type for the object exists in the list
    * of store commands. For example, it is a 'store' or 'storex' command.
    */
    function _isStore() {
        if (in_array($this->command(), $GLOBALS['SXIP_STORE'])) return true;
        return false;
    }

    /**
    * @param  none
    * @return bool    True if the object has Simple	commands, else false.
    *
    * Checks to see if the command type for the object exists in the list
    * of Simple commands. For example, the command is 'login', 'fetch', or
    * 'store.'
    */
    function _isSimple() {
        if (in_array($this->command(), $GLOBALS['SXIP_SIMPLE'])) return true;
        return false;
    }

    /**
    * @param  none
    * @return bool    True if the object has XML commands, else false.
    *
    * Checks to see if the command type for the object exists in the list
    * of XML commands. For example, the command is 'loginx', 'fetchx', or
    * 'storex.
    */
    function _isXml() {
        if (in_array($this->command(), $GLOBALS['SXIP_XML'])) return true;
        return false;
    }

    ## Getter-Setters
    ##
    ## These are the functions used to manually get or set the internal
    ## options of the object. These values of these variables can be edited in
    ## Config.pm.

    /**
    * @param  $value    The object's command.
    * @return $value    When called with no options, it returns
    *                   the existing or default value.
    *
    * This variable holds the command type of the object. For example,
    * 'login'.
    */
    function command($value = '') {
        if (strlen($value)) {
            if (!in_array($value, $GLOBALS['SXIP_COMMAND'])) {
                $this->error("'".$value."' is not a valid command");
                return false;
            }
            $this->_command = $value;
            return true;
        }
        else return $this->_command;
    }

    /**
    * @param  $value    Membersite-defined data that is returned
    *                   in the corresponding response from the
    *                   Homesite. This is an option for the XML
    *                   commands only.
    * @return $value    When called with no options, it returns
    *                   the existing or default value.
    *
    * This variable holds Membersite-defined data that is also returned in the
    * corresponding response from the Homesite. For example, it can be data
    * about the session.
    */
    function context($value = '') {
        if (strlen($value)) {
            if (!$this->_isXml()) {
                $this->warning("cannot set root context on non-XML objects");
                return false;
            }
            $this->_context = $value;
            return true;
        }
        else return $this->_context;
    }

    /**
    * @param  $value    An explanation of the current SXIP
    *				  	transaction.
    * @return $value    When called with no options it returns
    *                   the existing or default value.
    *
    * This variable holds a human-readable explanation, supplied by the
    * Membersite, of why the SXIP transaction is being performed.
    */
    function explanation($value = '') {
        if (strlen($value)) {
            $this->_explanation = $value;
            return true;
        }
        else return $this->_explanation;
    }

    /**
    * @param  $value    The FQDN of the Homesite.
    * @return $value    When called with no options it returns
    *                   the existing or default value.
    *
    * This variable holds the Fully Qualified Domain Name (FQDN) of the
    * Homesite. It cannot include the protocol type (for example, 'http://').
    */
    function homesite($value = '') {
        if (strlen($value)) {
            if (preg_match('/^\w*:*\/\//', $value)) {
                $this->warning(
                    "homesite must be a fully qualified domain name ".
                    "and cannot begin with a protocol, eg. 'http://'");
                return false;
            }
            $this->_homesite = $value;
            return true;
        }
        else return $this->_homesite;
    }

    /**
    * @param  $value      The FQDN of the Membersite.
    * @return $valueWhen  called with no options, it returns
    *                     the existing or default value.
    *
    * This variable holds the Fully Qualified Domain Name (FQDN) of the
    * Membersite. It cannot include the protocol type (for example, 'http://').
    */
    function membersite($value = '') {
        if (strlen($value)) {
            if (preg_match('/^\w*:*\/\//', $value)) {
                $this->warning(
                    "membersite must be a fully qualified domain name ".
                    "and cannot begin with a protocol, eg. 'http://'");
                return false;
            }
            else $this->_membersite = $value;
            return true;
        }
        else return $this->_membersite;
    }

    /**
    * @param  $value      URI to the Membersite's Privacy Policy
    * @return $value      When called with no options, it returns
    *                     the existing or default value
    *
    * This is the full URI to your site's Privacy Policy as defined by the
    * *W3C Platform for Privacy Preferences* (http://www.w3.org/P3P/).
    * All sites SHOULD have this information, however, it is not a
    * required field for SXIP objects and does not need to be commented.
    */
    function p3p($value = '') {
        if (strlen($value)) {
            if (!$this->_isFetch()) {
                $this->warning("p3p cannot be set on non-Fetch objects");
                return false;
            }
            $this->_p3p = $value;
            return true;
        }
        else return $this->_p3p;
    }

    /**
    * @param  $messageID   A pseudo-random, unique string, with a
    *                                  minimum of 16 characters.
    * @return $messageID   When called with no options, it returns
    *                      the existing or default value.
    *
    * This variable holds the Membersite-supplied messageID for use in a
    * 'loginx' command. The value is meant to be serve as an identifier for
    * the request.
    */
    function messageID($value = '') {
        if (strlen($value)) {
            if (!$this->_isXml()) {
                $this->warning("messageID cannot be set on non-XML objects");
                return false;
            }
            if (16 > strlen($value)) {
                $this->warning("messageID must be at least 16 characters");
                return false;
            }
            $this->_messageID = $value;
            return true;
        }
        else {
            if (!$this->_messageID) {
                $this->_messageID = $this->makeMessageID();
                $this->info(
                    "messageID is being auto-generated as none was set");
            }
            return $this->_messageID;
        }
    }

    /**
    * @param  $value     The version of SXIP in use.
    * @return $value     When called with no options, it returns
    *                    the existing or default value.
    *
    * This variable is usually set by default to the version that the
    * MDK was most recently tested against, but it can be modified.
    */
    function version($value = '') {
        if (strlen($value)) {
            if (!$this->_isXml()) {
                $this->warning("version cannot be set on non-XML objects");
                return false;
            }
            $this->_version = $value;
            return true;
        }
        else {
            if (!isset($this->_version)) $this->_version = $SXIP_VERSION;
            return $this->_version;
        }
    }

    /**
    * @param  $value     XML data.
    * @return $value     When called with no options, it returns
    *                    the existing or default value.
    *
    * This variable holds the XML (SxipML) data for the object.
    */
    function xml($value = '') {
	    # This is ideal behavior, but cannot function without a
        # fromXml for the request and a toXml for the response.
        #if (@_) {
        #    my $xml = shift;
        #    $self->fromXml($xml);
        #}
        #else {
        #    return $self->toXml();
        #}
        if (strlen($value)) {
            if (!$this->_isXml()) {
                $this->warning("xml cannot be set on non-XML objects");
                return false;
            }
            $this->_xml = $value;
            return true;
        }
        else return $this->_xml;
    }

    /**
    * @param  $value     The SxipML namespace.
    * @return $value     When called with no options, it returns
    *                    the existing or default value.
    *
    * This variable is usually set by default, but can be overridden.
    */
    function xmlns($value = '') {
        if (strlen($value)) {
            if (!$this->_isXml()) {
                $this->warning("xmlns cannot be set on non-XML objects");
                return false;
            }
            $this->_xmlns = $value;
            return true;
        }
        else {
            if (!isset($this->_xmlns)) $this->_xmlns = $SXIP_XMLNS;
            return $this->_xmlns;
        }
    }

    /**
    * @param  $value       The Sxip Property namespace.
    * @return $value   	   When called with no options, it returns
    *                      the existing or default value.
    *
    * This variable is usually set by default, but can be overriden.
    */
    function xmlns_sp($value = '') {
        if (strlen($value)) {
            if (!$this->_isXml()) {
                $this->warning("xmlns_sp cannot be set on non-XML objects");
                return false;
            }
            $this->_xmlns_sp = $value;
            return true;
        }
        else {
            if (!isset($this->_xmlns_sp)) $this->_xmlns_sp = $SXIP_XMLNS_SP;
            return $this->_xmlns_sp;
        }
    }
}
?>
