<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2008 Liip AG                                      |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Christian Stocker <chregu@liip.ch>                           |
// +----------------------------------------------------------------------+
//
// $Id: bxcms.php 1459 2004-05-27 13:29:34Z chregu $


/**
 *
 * @author   Silvan Zurbruegg <silvan@liip.ch>
 * @version  $Id: bxcmsauth.php 1459 2004-05-27 13:29:34Z silvan $
 * @package  popoon
 */

class popoon_components_actions_bxcmsauth extends popoon_components_action {

    /**
     * Constructor
     *
     */
    function __construct(&$sitemap) {
        parent::__construct($sitemap);
    }

    function init($attribs) {
        parent::init($attribs);
    }

    function act() {
        /// echo "doing auth";
        $fulluri = $this->getAttrib('uri');
        $conf = bx_config::getInstance();

        @session_start();
        // var_dump($this->getParameterDefault('uri'));
        $confvars = $conf->getConfProperty('permm');

        // allow override of conf params from sitemap
        // to set auth-, and permmodule
        if (($sitemapConfigs = $this->getParameterDefault('permmconfig')) !== NULL) {
            $confvars = $sitemapConfigs;
        } else {
            $confvars = $conf->getConfProperty('permm');
        }

        $permObj = bx_permm::getInstance($confvars);
        //start auth (without that, it's not started per default if we don't have
        // a special perm obj
        $permObj->getAuth();
        if ($permObj instanceof bx_permm) {

            if (preg_match("#logout#", $fulluri) || isset($_GET['logout'])) {
                $permObj->logout();
                if (isset($_GET['back']) && $_GET['back']) {
                    header('Location: ' . $_GET['back']);
                    die();
                }
            }

            $permObj->start();
            if ($permObj->getAuth() == FALSE) {
                return FALSE;
            } else {
                if (isset($_GET['back']) && $_GET['back']) {
                    header('Location: ' . $_GET['back']);
                    die();
                }
                $actions = $this->getParameter('actions');
                if ($actions) {
                    if (! $permObj->isAllowed($fulluri, array_values($actions))) {

                        return false;
                    }
                }
                return true;
            }
        }

        return false;
    }

}
?>
