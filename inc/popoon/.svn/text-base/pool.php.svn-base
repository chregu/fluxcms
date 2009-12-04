<?php

/** Class for storing Popoon Config parameter
 *
 *  Parameter about Caching et al. are also stored here
 *
 * @author   Christian Stocker <chregu@liip.ch>
 * @version  $Id: config.php 838 2004-03-16 19:45:10Z  $
 * @example classes/config_cache.php
 * @package  popoon
 */

class popoon_pool {
    /**
     * the class instance
     *
     * this is a singleton class, therefore we save the instance
     * in this static var
     * @var popoon_pool
     */
    static $instance = null;

    private $configclass;

    /**
     * DB read handler
     *
     * @var MDB2_Driver_mysqli
     */
    public $db;

    /**
     * DB write handler
     *
     * @var MDB2_Driver_mysqli
     */
    public $dbwrite;

    /**
     * Config class
     *
     * @var bx_config (could also be popoon_classes_config in a non Flux CMS environment)
     */

    public $config;

    /**
     * Gets a singleton instance of the this class
     *
     * @return popoon_pool an instance of this class
     */
    public static function getInstance($configclass = "popoon_classes_config") {
        if (! popoon_pool::$instance) {
            popoon_pool::$instance = new popoon_pool($configclass);
            popoon_pool::$instance->configclass = $configclass;
        } else
            if (popoon_pool::$instance->configclass != $configclass) {
                throw new Exception("The Config Class $configclass is not the same as the initially defined one " . popoon_pool::$instance->configclass);
            }
        return popoon_pool::$instance;
    }

    /**
     * The constructor
     *
     * As this is a singleton class, we don't allow the class to be called
     * directly. use getInstance
     *
     * @see getInstance
     */
    private function __construct() {
        // This unsets the class vars, to make the __get work
        unset($this->db);
        unset($this->dbwrite);
        unset($this->config);
    }

    public function __get($name) {
        switch ($name) {
            case "config" :
                $c = $this->configclass;
                $this->config = call_user_func(array($this->configclass, 'getInstance'));
                return $this->config;
            break;
            case "db" :
                require_once ("MDB2.php");

                if (! isset($this->config->dboptions)) {
                    $this->config->dboptions = NULL;
                }

                $this->db = @MDB2::connect($this->config->dsn, $this->config->dboptions);

                if (isset($this->config->portabilityoptions)) {
                    $this->db->options['portability'] = $this->config->portabilityoptions;
                }
                if (@MDB2::isError($this->db)) {
                    throw new PopoonDBException($this->db);
                }
                $this->checkForMysqlUtf8($this->config->dsn, $this->db);
                return $this->db;

            case "dbwrite" :
                if (! isset($this->config->dsnwrite)) {
                    if (! isset($this->db)) {
                        $this->dbwrite = $this->__get("db");
                    } else {
                        $this->dbwrite = $this->db;
                    }
                    return $this->dbwrite;
                }
                require_once ("MDB2.php");

                if (! isset($this->config->dboptionswrite)) {
                    $this->config->dboptionswrite = $this->config->dboptions;
                }

                $this->dbwrite = @MDB2::connect($this->config->dsnwrite, $this->config->dboptionswrite);

                if (isset($this->config->portabilityoptions)) {
                    $this->dbwrite->options['portability'] = $this->config->portabilityoptions;
                }

                if (@MDB2::isError($this->dbwrite)) {
                    throw new PopoonDBException($this->dbwrite);
                }

                $this->checkForMysqlUtf8($this->config->dsnwrite, $this->dbwrite);
                return $this->dbwrite;

            case "i18nadmin" :
                if (! isset($this->config->i18nAdminSrc)) {
                    $this->i18nadmin = NULL;
                } else {
                    $this->i18nadmin = popoon_classes_i18n::getDriverInstance($this->config->i18nAdminSrc, $this->config->getAdminLocale());
                    if (isset($this->config->i18nAdminGenerateKeys))
                        $this->i18nadmin->generateKeys = $this->config->i18nAdminGenerateKeys;
                }
                return $this->i18nadmin;
            case "versioning" :
                if (! isset($this->config->versioning)) {
                    $this->versioning = null;
                    ;
                } else {
                    $this->versioning = bx_versioning::versioning($this->config->versioning);
                }
                return $this->versioning;
        }

    }

    function checkForMysqlUtf8($dsn, $db) {
        if ($this->config->dbIsUtf8 === null) {
            if (self::isMysqlFourOne($dsn, $db)) {
                $this->config->dbIsFourOne = true;
                $this->config->dbIsUtf8 = true;
            }
        }

        if ($this->config->dbIsUtf8) {
            $this->config->dbIsUtf8 = true;
            $db->isUtf8 = true;
        } else {
            $db->isUtf8 = false;
            $this->config->dbIsUtf8 = false;
        }

        if ($this->config->dbIsFourOne) {
            $db->query("set names 'utf8'");
        }
    }

    static function isMysqlUTF8($dsn, $db) {
        return true;
    }

    static function isMysqlFourOne($dsn, $db) {
        if ($dsn['phptype'] == "mysql" || $dsn['phptype'] == "mysqli") {
            if ($dsn['phptype'] == 'mysqli') {
                $isFourOne = version_compare($db->connection->server_info, "4.1", ">=");
            } else {
                $isFourOne = version_compare(@mysql_get_server_info(), "4.1", ">=");
            }
        }
        if ($isFourOne) {
            return true;
        } else {
            return false;
        }
    }

}
