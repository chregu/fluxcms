<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// |                                                                      |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Christian Stocker <chregu@bitflux.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id: MDB2.php 1473 2004-05-28 09:49:40Z chregu $
//

require_once 'Auth/Container.php';
require_once 'MDB2.php';

/**
* Storage driver for fetching login data from a database
*
* This storage driver can use all databases which are supported
* by the PEAR MDB2 abstraction layer to fetch login data.
*
* @author   Lorenzo Alberton <l.alberton@quipo.it>
* @package  Auth
* @version  $Revision: 1.2 $
*/
class Auth_Container_sxip extends Auth_Container_MDB2
{
    
        /**
     * Set some default options
     *
     * @access private
     * @return void
     */
    function _setDefaults()
    {
        parent::_setDefaults();
        $this->options['gupicol'] = "user_gupi";
        $this->options['emailcol'] = "user_email";
    }
    
    // {{{ fetchData()
    
    /**
    * Get user information from database
    *
    * This function uses the given username to fetch
    * the corresponding login data from the database
    * table. If an account that matches the passed username
    * and password is found, the function returns true.
    * Otherwise it returns false.
    *
    * @param   string Username
    * @param   string Password
    * @return  mixed  Error object or boolean
    */
    function fetchData($username, $password)
    {   
        if ($username == "__sxip" && isset($this->options['gupicol']) && $this->options['gupicol']) {
            
            $err = $this->_prepare();
            if ($err !== true) {
                return PEAR::raiseError($err->getMessage(), $err->getCode());
            }
            
            // Find if db_fileds contains a *, i so assume all col are selected
            if (strstr($this->options['db_fields'], '*')) {
                $sql_from = '*';
            } else {
                $sql_from = $this->options['usernamecol'] . ', '. $this->options['passwordcol'] . $this->options['db_fields'];
            }
            // bx_helpers_debug::webdump($GLOBALS);  
            $response_command = array_key_exists('sxip-response-command', $_REQUEST) ? $_REQUEST["sxip-response-command"] : "";
            if ($response_command == "loginx") {
                require_once 'SXIP/Request/LoginX.php';
                require_once 'SXIP/Response.php';
                $sx_res = new SXIP_Response();
                
                $loginx_resp = $_REQUEST;
                
                if ($sx_res->fromAssoc($loginx_resp)) {
                    
                    
                    # Check for a GUPI
                    if ($sx_res->gupi()) {
                        
                        
                        $query = sprintf("SELECT %s FROM %s WHERE %s = %s",
                        $sql_from,
                        $this->options['table'],
                        $this->options['gupicol'],
                        $this->db->quote($sx_res->gupi(), 'text')
                        );
                        $res = $this->db->queryRow($query, null, MDB2_FETCHMODE_ASSOC);
                        if (MDB2::isError($res) || PEAR::isError($res)) {
                            return PEAR::raiseError($res->getMessage(), $res->getCode());
                        }
                    
                        if (!is_array($res) && isset($this->options['emailcol']) && $this->options['emailcol'])  {
                            
                            if (!$res = $this->tryEmail($sx_res, $sql_from)) {
                                $this->activeUser = '';
                                return false;
                            }
                            if (MDB2::isError($res) || PEAR::isError($res)) {
                                return PEAR::raiseError($res->getMessage(), $res->getCode());
                            }
                        }
                        foreach ($res as $key => $value) {
                            if ($key == $this->options['passwordcol'] ) {
                                continue;
                            }
                            elseif ( $key == $this->options['usernamecol']) {
                                continue;
                            }
                            
                            // Use reference to the auth object if exists
                            // This is because the auth session variable can change so a static call to setAuthData does not make sense
                            if (isset($this->_auth_obj) && is_object($this->_auth_obj)) {
                                $this->_auth_obj->setAuthData($key, $value);
                            } else {
                                Auth::setAuthData($key, $value);
                            }
                        }
                        $this->_auth_obj->username = $res[$this->options['usernamecol']];
                        $this->activeUser = $res[$this->options['usernamecol']];
                        return true;
                    }
                    else {
                        $html .= "No GUPI returned";
                    }
                }
            }
        }
        return parent::fetchData($username, $password);
    }
    
    function tryEmail($sx_res,$sql_from) {
        
        $email =  $sx_res->getFetch(array( "context"=>"verifiedEmail","property"=>"/sxip/contact/internetAddresses/verifiedEmail"));   
        $query = sprintf("SELECT %s FROM %s WHERE %s = %s",
            $sql_from. ", ". $this->options['gupicol'],
            $this->options['table'],
            $this->options['emailcol'],
            $this->db->quote($email, 'text')
        );
        
        $res = $this->db->queryRow($query, null, MDB2_FETCHMODE_ASSOC);
        if (MDB2::isError($res) || PEAR::isError($res)) {
            return PEAR::raiseError($res->getMessage(), $res->getCode());
        }
        if (!is_array($res)) {
            return false;
        }
        // we already have a gupi, so we do allow to change it
        if (strlen($res[$this->options['gupicol']]) == 16) {
            return false;
        }
            
        $query = sprintf("update %s set %s = %s where %s = %s",
        $this->options['table'],
        $this->options['gupicol'],
        $this->db->quote($sx_res->gupi(),'text'),
        $this->options['emailcol'],
        $this->db->quote($email,'text'));
        
        $resu = $this->db->query($query);
        if (MDB2::isError($resu) || PEAR::isError($resu)) {
            
            return PEAR::raiseError($resu->getMessage(), $resu->getCode());
        }
        return $res;
           
    }
    // }}}
    
    
}
?>
