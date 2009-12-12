<?php
/* this module extends the MDB2 class, so that one can authenticate
    against confluence first and then against MDB2
    if confluence login fails, it tries to authentivate against MDB2
    if condluence login succeeds and there's no internal user with that name, it creates it

    config.xml params:

     <authModule>
                <type>confluence</type>
                <wsdlurl>http://wiki.liip.ch:8081/rpc/soap-axis/confluenceservice-v1?wsdl</wsdlurl>
                <!-- which space the user can see to be allowed a login -->
                <allowedSpace>INTERN</allowedSpace>
                <!-- or in which group the user has to be
                (this does not work for non-admin account in at least confluence 2.4.3
                -->
                <allowedGroup>internal-developers</allowedGroup>

     ...

     and the same parameters as for the pearcommon auth

     */

require_once 'Auth/Auth.php';
require_once ("bx/permm/auth/pearcontainer/confluence.php");

class bx_permm_auth_confluence extends bx_permm_auth_pearauth {

    public function __construct($options = array()) {
        parent::__construct();
        $options = $this->initOptions($options);
        $this->MDB2Constructor($options, 'confluence', array(
                'advancedsecurity',
                'wsdlurl',
                'allowedGroup',
                'allowedSpace'
        ));

    }
}

?>
