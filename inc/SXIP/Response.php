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
require_once 'SXIP/Data.php';

/**
 * This class extends the SXIP::Data class.
 */
class SXIP_Response extends SXIP_Data {

    function SXIP_Response($args = '') {
        $this->SXIP_Data($args);
    }

    /**
    * @param	$context	The name of the result.
    * @param	$value		The result value.
    * @return	boolean
    *
    * This differs from the 'addStore' method for Request in that the Response
    * only needs to know about '$context' and not '$property', because no
    * property data is returned.
    */
    function addStore($args) {
        if (!$this->_isStore()) {
            $this->warning("store is not possible with the '".
                $this->command()."'");
            return false;
        }
        $context = ( array_key_exists("context", $args) ) ?
            $args['context'] :
            "default";
        if (array_key_exists("value", $args)) $value = $args['value'];

        if (!$this->_isXml()) {
            $this->warning("store data is only available in XML responses");
            return false;
        }
        $this->_store[$context] = $value;
        return true;
    }

    /**
    * @param	$context	The name of the result.
    * @param	$default	The value to return if no result was found.
    * @return	$value		The value of the store result or $default.
    *
    * This differs from Request in that it does not need to know about
    * '$property'. It only indexes on '$context' because it is returned by the
    * 'Store' Response.
    */
    function getStore() {
        $context = ( array_key_exists("context", $args) ) ?
            $args['context'] :
            "default";
        if (array_key_exists("default", $args)) $value = $args['default'];

        if (!$this->_isXml()) {
            $this->warning("store data is only available in XML responses");
            return false;
        }
        if (array_key_exists($context, $this->_store))
            $r = $this->_store[$context];

        if (isset($r)) return $r;
        else if (isset($default)) return $default;
        else return false;
    }

    function fromAssoc($args) {
        if (array_key_exists("sxip-response-command", $args))
            $this->command($args['sxip-response-command']);
        if ($this->_isXml()) {
            $xml = (array_key_exists("sxip-response-xml", $args)) ?
                $args['sxip-response-xml'] : false;
            if ($xml) unset($args['sxip-response-xml']);
        }
        foreach ($args as $k => $v) {
            if (in_array($k, $GLOBALS['SXIP_RESPONSE'])) {
                $temp = split("-", $k);
                $cmd = array_pop($temp);
                if (!$this->$cmd($v)) return false;
            }
            else {
                if ($this->_isSimple())
                    if (!$this->addFetch(
                        array("value" => $v, "context" => $k)))
                        return false;
                if (!$this->addPass(array("name" => $k, "value" => $v)))
                    return false;
            }
        }
        if ($this->_isXml()) {
            if ($xml && !$this->fromXml($xml)) {
                $this->error("XML could not be parsed by fromAssoc");
                return 0;
            }
        }
        return true;
    }

    /**
    * @param	$xml	The XML passed in the 'sxip-response-xml'.
    * @return	none
    *
    * This method parses the XML using the C API and then brings that data into
    * the Response object.
    */
    function fromXml($xml) {
        require_once 'SXIP/XMLResponse.php';
        $xml = stripcslashes($xml);
        switch ($this->command()) {
            case "loginx":
                $r = SXIPXMLResponse_Loginx($xml);
                break;
            case "fetchx":
                $r = SXIPXMLResponse_Fetchx($xml);
                break;
            case "storex":
                $r = SXIPXMLResponse_Storex($xml);
                break;
            default:
                $this->warning("cannot fromXml for a non-XML object");
                return false;
        }
        if (!is_array($r)) {
            $this->warning("Could not parse XML");
            return false;
        }

        foreach ($GLOBALS['SXIP_RESPONSE'] as $key) {
            $s = split("-", $key);
            $cmd = array_pop($s);
            if (array_key_exists($cmd, $r)) $this->$cmd($r[$cmd]);
        }
        if (array_key_exists("fetch", $r)) {
            foreach ($r['fetch'] as $context => $properties) {
                foreach ($properties as $property => $values) {
                    foreach ($values as $value) {
                        if (!$this->addFetch(
                            array("context" => $context,
                                  "property" => $property,
                                  "value" => $value))) {
                            return false;
                        }
                    }
                }
            }
        }
        if (array_key_exists("store", $r)) {
            foreach ($r['store'] as $context => $value) {
                $this->addStore(
                    array("context" => $context, "value" => $value));
            }
        }
        return true;
    }


    /**
    * @param	$xml	The XML whose signature needs to be
    *					verified.
    * @return	bool
    *
    * Checks the validity of the XML document's signature. Returns 1 on
    * success and raises an error while returning 0 on failure. The actual
    * XMLResponse method it calls returns 0 on success and an error code and
    * message, as defined in MResponse.h, on failure. This is switched around
    * here for semantic purposes.
    */
    function verifyXml($xml) {
        require_once 'SXIP/XMLResponse.php';
        $xml = stripcslashes($xml);
        if ('loginx' == $this->command()) {
            if (!SXIPXMLResponse_verifyLoginx($xml)) {
                return true;
            }
        }
        else if ('fetchx' == $this->command()) {
            if (!SXIPXMLResponse_verifyFetchx($xml)) {
                return true;
            }
        }
        else if ('storex' == $this->command()) {
            if (!SXIPXMLResponse_verifyStorex($xml)) {
                return true;
            }
        }
        else {
            $this->warning("Could not verify XML");
            return false;
        }
    }

    ########
    ## GETTER-SETTERS
    ##
    ## These are the functions used to manually get or set the internal
    ## Response options.

    /**
    * @param	$code	The status code.
    * @return	$code	When called with no arguments, it
    * 	                returns the existing or default value.
    *
    * This variable holds the status code sent back from the Homesite.
    */
    function code($value = '') {
        if (strlen($value)) {
            $this->_code = $value;
            return true;
        }
        else return $this->_code;
    }

    /**
    * @param	$gupi	The GUPI in a 'Login' or 'LoginX'
    *                   Response.
    * @return	$gupi	When called with no arguments, it
    *                   returns the existing or default value.
    *
    * This variable holds the Globally Unique Persona Identifier (GUPI)
    * returned by a 'Login' or 'LoginX' Response.
    */
    function gupi($value = '') {
        if (strlen($value)) {
            if ("login" != substr($this->command(), 0, 5)) {
                $this->warning("gupi can only be set for login or loginx");
                return false;
            }
            $this->_gupi = $value;
            return true;
        }
        else return $this->_gupi;
    }

    /**
    * @param	$time	The time instant in UTC format.
    * @return   $time	When called with no arguments, it
    * 	                returns the existing or default value.
    *
    * This variable holds the time instant of issue in UTC form.
    */
    function instant($value = '') {
        if (strlen($value)) {
            $this->_instant = $value;
            return true;
        }
        else return $this->_instant;
    }

    /**
    * @param	$message	The status message.
    * @return	$message	When called with no arguments, it
    *                       returns the existing or default value.
    *
    * This variable holds the status message sent back from the Homesite.
    */
    function message($value = '') {
        if (strlen($value)) {
            $this->_message = $value;
            return true;
        }
        else return $this->_message;
    }

    /**
    * @param	$value	The method of authentication.
    * @return	$value	When called with no arguments, it
    *                   returns the existing or default value.
    *
    * This variable holds the method of authentication from the
    * 'authentication' element in a 'LoginX' Response.
    */
    function method($value = '') {
        if (strlen($value)) {
            if ("login" != substr($value, 0, 5)) {
                $this->warning("method can only be set for login or loginx");
                return false;
            }
            $this->_method = $value;
            return true;
        }
        else return $this->_method;
    }

    /**
    * @param	$responseID		A unique identifier for the Response.
    * @return	$responseID		When called with no arguments, it
    *                           returns the existing or default value.
    *
    * This variable holds a token that uniquely identifies the Response.
    */
    function responseID($value = '') {
        if (strlen($value)) {
            $this->_responseID = $value;
            return true;
        }
        else return $this->_responseID;
    }

    /**
    * @param	$xml	The XML to be parsed into the object.
    * @return	$xml	When called with no options, it
    *                   returns the existing or default value.
    *
    * This method calls 'fromXml()' when used as a setter. It also stores the
    * XML.
    */
    function xml($value = '') {
        if (strlen($value)) {
            if (!$this->_isXml()) {
                $this->warning("xml cannot be set on non-XML objects");
                return false;
            }
            $this->fromXml($value);
            $this->_xml = $value;
            return true;
        }
        else return $this->_xml;
    }
}
?>
