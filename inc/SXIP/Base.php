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
require_once 'SXIP/Config.php';


class SXIP_Base {
    var $_printError;
    var $_raiseError;
    var $_debugLevel;
    var $_error;

    /**
     * var    	printError  Controls whether errors are printed.
     * var    	raiseError  Controls whether errors are raised.
     * var    	debugLevel  Sets the default error level.
     * @return  $self       The instantiated object.
     * A very generic constructor that sets some defaults.
    */
    function SXIP_Base($args = '') {
        if (!is_array($args)) $args = array();
        $this->_printError = ( array_key_exists('printError', $args) ) ?
            $args['printError'] :
            0;
        $this->_raiseError = ( array_key_exists('raiseError', $args) ) ?
            $args['raiseError'] :
            1;
        $this->_debugLevel = ( array_key_exists('debugLevel', $args) ) ?
            $args['debugLevel'] :
            DEBUG_NONE;
    }
	/**
    * @param  $shouldprint     Should errors be printed?
    * @return $shouldprint    Flag indicating if errors should be printed.
    *
    * Sets/queries the 'printError' attribute, which controls whether
    * errors should be printed (using 'warn') in addition to returning error
    * codes in the normal way.  By default, 'printError' is on.
    */
    function printError($flag = '') {
        if (strlen($flag)) $this->_printError = $flag;
        return $this->_printError;
    }

    /**
    * @param  $shouldraise  Should errors be raised as exceptions?
    * @return $shouldraise  Flag indicating if errors should be raised.
    *
    * Sets/queries the 'raiseError' attribute, which controls whether or not
    * errors should be raised as exceptions (using 'die') in addition to
    * returning error codes in the normal way.  By default, 'raiseError' is on.
    */
    function raiseError($flag = '') {
        if (strlen($flag)) $this->_raiseError = $flag;
        return $this->_raiseError;
    }

    /**
    * @param   $level     The new debug level (as DEBUG_* constant).
    * @return  $level     The current debug level.
    *
    * Sets/queries the 'debugLevel' attribute, which controls the amount of
    * debugging information which is placed onto STDERR.  '$level' as provided
    * should be one of the "DEBUG_*" constants imported from 'SXIP::Config'.
	*/
    function debugLevel($level = '') {
        if (strlen($level)) $this->_debugLevel = $level;
        return $this->_debugLevel;
    }

    /**
    * @param  $level        The debugging level for this message.
    * @param  $msg    		The message to log.
    * @return $wasoutput   	The flag that indicates if the debug item was output.
    *
	* Logs a debugging '$msg' at the specified debug '$level'. If the
    * specified debug level for this message is more verbose than the current
    * debug level for the object, the message will be printed onto STDERR.
    * This method returns true if the message was output to STDERR. It returns
    * false otherwise.
    */
    function debugPrint($level, $msg) {
        if ($level <= $this->debugLevel()) {
            error_log($msg);
            return true;
        }
        return false;
    }

    /**
     * @return     $error              - The last recorded error message.
     * return the last recorded error message.
    */
    function lastError($msg = '') {
        if (strlen($msg)) $this->_error = $msg;
        return $this->_error;
    }

    /**
    * @param  $msg                - The error message to record.
    * @return     none
    *
    * Records the provided '$msg' at the 'DEBUG_ERROR' debugging level.  If
    * 'raiseError' is set, the error message is thrown as an exception using
    * 'die'.  If 'printError' is set, the error message is also printed onto
    * STDERR, using 'warn'.
    */
    function error($msg) {
        $this->lastError($msg);
        if ($this->printError()) trigger_error($msg, DEBUG_ERROR);
        if ($this->raiseError()) die ($msg);
        $this->debugPrint(DEBUG_ERROR, $msg);
    }

    /**
    * @param  $msg                - The warning message to record.
    * @return     none
    *
    * Records the provided warning '$msg' at the 'DEBUG_WARNING' debugging
    * level. If 'printError' is set, the warning message is also printed onto
    * STDERR, using 'warn'.
    */
    function warning($msg) {
        if ($this->printError()) trigger_error($msg, DEBUG_WARNING);
        $this->debugPrint(DEBUG_WARNING, $msg);
    }

    /**
    * @param  $msg                - The informational message to record.
    * @return     none
    *
    * Records the provided '$msg' at the 'DEBUG_INFO' debugging level.
    */
    function info($msg) {
        if ($this->printError()) trigger_error($msg, DEBUG_INFO);
        $this->debugPrint(DEBUG_INFO, $msg);
    }
}
?>
