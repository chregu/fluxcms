<?php
/**
 * send mail via smtp host
 *
 * @autor Adrian Schlegel <adrian@liip.ch>
 * @package bx_notification
 *
 * FIXME this has to be refactored since it duplicates a lot of code from
 * bx_notifications_mail. Either let it extend bx_notifications_mail or
 * make a common base class which both can extend.
 *
 *
 * To make use of an external smtp you have to add a line to the <options>
 * element in config.xml:
 * <!-- syntax for smtp servers: [user:password@]host[:port] -->
 * <mailSmtp>user:pass@example.com</mailSmtp>
 */

class bx_notifications_mailsmtp extends bx_notification {

    static protected $instance = null;
    static protected $smtpOptions = null;

    protected function __construct() {

    }

    static public function getInstance() {
        if (!(self::$instance)) {
            self::$instance = new bx_notifications_mailsmtp();
        }
        return self::$instance;
    }

    /**
     * process parameters and send mail
     *
     * @param string $to recipient email address
     * @param string $subject the subject of the email
     * @param string $message the message body
     * @param string $fromAdress optional sender email address
     * @param string $fromName optional sender full name
     * @param array $options can be: array('charset', 'bcc', 'content-type')
     *
     * @return bool
     */
    public function send($to, $subject, $message, $fromAdress = null, $fromName= null, $options = array()) {

        if (!$fromAdress) {
            $fromAdress = 'unknown@example.org';
        }

        if ($fromName && PHP_OS != 'WINNT') {
            $from = $fromName . '<'.$fromAdress.'>';
        } else {
            $from = $fromAdress;
        }

        if(strpos($from, "\n") !== FALSE or strpos($from, "\r") !== FALSE) {
            throw new Exception("From: is invalid.");
        }
        if(strpos($to, "\n") !== FALSE or strpos($to, "\r") !== FALSE) {
            throw new Exception("To: is invalid.");
        }

        if(strpos($subject, "\n") !== FALSE or strpos($subject, "\r") !== FALSE) {
            throw new Exception("Subject: is invalid.");
        }

        if (!defined('PHP_EOL')) {
            define('PHP_EOL',"\n");
        }
        $headers['From'] = $from;
        $headers['User-Agent'] = "User-Agent: Flux CMS Mailer (".BXCMS_VERSION."/".BXCMS_BUILD_DATE.")";
        if (!empty($_SERVER['HTTP_HOST'])) {
            $headers["X-Flux-Host"] = $_SERVER['HTTP_HOST'];
        }
        if (empty($options['charset'])) {
            $options['charset'] = 'UTF-8';
        }
        else if ($options['charset'] == "utf8") {
            $options['charset']  = 'UTF-8';
        }

        if (!empty($options['bcc'])) {
            $headers["Bcc"] = $options['bcc'];
        }

        if (isset($options['content-type'])) {
            $headers["Content-Type"] = $options['content-type'] .";";
        } else {
            $headers["Content-Type"] = " text/plain;";
        }
        $headers['Content-Type'] .= " charset=\"".$options['charset']."\"";
        $headers["Content-Transfer-Encoding"] = "8bit";        
        // recode utf8 strings
        if ($options['charset'] != "UTF-8") {
            if (function_exists("iconv")) {
                $subject=iconv("utf8",$options['charset'],$subject);
                $message=iconv("utf8",$options['charset'],$message);
            } else {
                // decode utf8 strings
                $subject = utf8_decode($subject);
                $message = utf8_decode($message);
            }
        }

        $cs = strtoupper($options['charset']);   
        //make correct 7bit header for the subject
        $subject = '=?'.$cs.'?B?'.base64_encode($subject).'?=';
        if ($GLOBALS['POOL']->config->logMails == 'true') {
            foreach($headers as $key=>$row) {
                $logHeaders .= "$key: $row".PHP_EOL;
            }
            file_put_contents(BX_DATA_DIR.'/mail.log',"****\nDate: ". date("c")."\nTo: " . $to . "\n"."Subject: " . $subject . "\n"."Headers:\n" . $logHeaders . "\n"."Message: " . $message . "\n",FILE_APPEND);
        }

        $headers['Subject'] = $subject;
        $headers['To'] = $to;
        return $this->sendSmtp($to, $message, $headers);
    }

    public function sendByUsername($username, $subject, $message, $fromAdress = null, $fromName = null) {

        $prefix = $GLOBALS['POOL']->config->getTablePrefix();

        $query = "select user_email,user_fullname from ".$prefix."users where user_login =".$GLOBALS['POOL']->db->quote($username);

        $row = $GLOBALS['POOL']->db->queryRow($query, null, MDB2_FETCHMODE_ASSOC);
        if (MDB2::isError($row)) {
            throw new PopoonDBException($row);
        }
        if (!$row['user_fullname']) {
            $row['user_fullname'] = $username;
        }

        if (!empty($row['user_email'])) {


            $to = $row['user_fullname'] . ' <' .$row['user_email'].'>';
            if ($to) {
                $this->send($to,$subject,$message,$fromAdress, $fromName);
            }
        }
    }

    /**
     * call PEAR::Mail to actually send the mail via smtp
     *
     * @param string $to recipient email address
     * @param string $message message body
     * @param array $headers email headers to set (ex. array('To'=>'joe@example.com', 'From'=>'jim@example.com'))
     */
    protected function sendSmtp($to, $message, $headers) {

        $options = $this->getSMTPOptions();
        $mail =& Mail::factory('smtp', $options);
        if(PEAR::isError($mail)) {
            bx_log::log($mail->getMessage());
            return false;
        }
        $ret = $mail->send($to, $headers, $message);
        if(PEAR::isError($ret)) {
            bx_log::log($ret->getMessage());
            return false;
        }

        return true;
    }

    /**
     * get smtp options from config file
     *
     * the options must be in a string of the following format:
     * [username:password@]host[:port]
     *
     * @return array array('host'=>'example.com') or null
     */
    protected function getSMTPOptions() {
        if(self::$smtpOptions) {
            return self::$smtpOptions;
        }

        $parsed = null;
        if($GLOBALS['POOL']->config->mailSmtp) {
            $dsn = $GLOBALS['POOL']->config->mailSmtp;
            // find username and password
            if(($at = strrpos($dsn, '@')) !== false) {
                $str = substr($dsn, 0, $at);
                $dsn = substr($dsn, $at + 1);
                if (($pos = strpos($str, ':')) !== false) {
                    $parsed['username'] = rawurldecode(substr($str, 0, $pos));
                    $parsed['password'] = rawurldecode(substr($str, $pos + 1));
                    $parsed['auth'] = true;
                } 
            }

            // find hostname and port
            if(preg_match('#([a-z0-9._-]+)(:([\d]+))?#', $dsn, $match)) {
                $parsed['host'] = $match[1];
                isset($match[3]) ? $parsed['port'] = $match[3] : '';
            }
        }
        self::$smtpOptions = $parsed;
        return self::$smtpOptions;
    }

}

