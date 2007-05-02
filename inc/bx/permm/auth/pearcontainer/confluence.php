<?php

    
class Auth_Container_confluence extends Auth_Container_MDB2
{

    var $wsdlurl = null;
    var $allowedGroup = null;
    var $client = null;
    var $token = null;
    var $groups = null;
    /**
     * Constructor
     *
     * Currently does nothing
     * 
     * @return void
     */
    function __construct($dsn) {
        parent::__construct($dsn);
        $this->wsdlurl = $dsn['wsdlurl'];
        $this->allowedGroup = $dsn['allowedGroup'];
        
    }

    // }}}
    // {{{ fetchData()
    
    /**
     * Get user information from pear.php.net
     *
     * This function uses the given username and password to authenticate
     * against the pear.php.net website
     *
     * @param string    Username
     * @param string    Password
     * @return mixed    Error object or boolean
     */
    function verifyPassword( $password,$password2, $cryptType = "md5", $username = '')
    {
        try {
            if (!$this->token && class_exists('SoapClient')) {
                $this->client = new  SoapClient($this->wsdlurl);
                $this->token  = $this->client->login($username,$password);
            }
            if ($this->token) {
                if (!$this->groups) {
                    $this->groups = $this->client->getUserGroups($this->token,$username);
                }
                if (in_array($this->allowedGroup,$this->groups)) {
                    return true;
                }
            }
        } catch (SoapFault $e) {
            
        }
        //fall back to internal DB check
        return parent::verifyPassword($password,$password2,$cryptType, $username );
    }

    function fetchData($username, $password) {
        //if fetchData doesn't work, do checks if user exists
        if (!parent::fetchData($username,$password)) {
            //if not, check if user does not exists
            /* copied from Auth_Container_MDB2 */
            if (strstr($this->options['db_fields'], '*')) {
                $sql_from = '*';
            } else {
                $sql_from = $this->options['idcol'] . ', ' . $this->options['usernamecol'] . ', '. $this->options['passwordcol'] . $this->options['db_fields'];
            }
            
            $query = sprintf("SELECT %s FROM %s WHERE %s = %s",
                $sql_from,
                $this->options['table'],
                $this->options['usernamecol'],
                $this->db->quote($username, 'text')
            );
            $res = $this->db->queryRow($query, null, MDB2_FETCHMODE_ASSOC);
            /* end copy from MDB2 */
            //user does not exist
            if (!is_array($res)) {
                //verify if it exists in confluence
                if ($this->verifyPassword($password,null,null,$username)) {
                    //create user
                    $user = $this->client->getUser($this->token,$username);
                    $this->addUser($username,$password,array("user_fullname" => $user->fullname, "user_email" => $user->email));
                    // do the whole fetchData thing again
                    return parent::fetchData($username,$password);
                }
                
            }
            return false;
            
        }
        return true;
    }
    
    
    // }}}
    
}
?>
