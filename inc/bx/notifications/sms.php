<?php


class bx_notifications_sms extends bx_notification {

    static protected $instance = null;

    private $username = '';
    private $password = '';
    private $sms = null;

    protected function __construct() {
        $this->username = $GLOBALS['POOL']->config->aspsms_username;
        $this->password = $GLOBALS['POOL']->config->aspsms_password;
        $this->sms = new bx_notifications_sms_aspsms($this->username, $this->password);
    }

    static public function getInstance() {
        if (!(self::$instance)) {
            self::$instance = new bx_notifications_sms();
        }
        return self::$instance;
    }

    public function send($to, $subject = null, $message, $fromAdress = false, $fromName = null, $options = array()) {
        if (!$fromAdress) {
            $fromAdress = $GLOBALS['POOL']->config->apsms_originator;
            if( trim($fromAdress) == '') {
                $fromAdress = 'ASPSMS';
            }
        }
        $this->sms->recipients = array();

        //messages ned to be iso-latin
        $message = utf8_decode($message);

        $this->sms->setOriginator($fromAdress);

        $to = $this->cleanNumbers($to);

        if(count($to) == 0){
            die('No valid phonenumbers found');
        }

        foreach($to as $nr) {
            $this->sms->addRecipient($nr);
        }


        $this->sms->setContent($message);

        //check accounting
        //if key is set
        $key = $GLOBALS['POOL']->config->aspsms_accounting_key;
        if(trim($key) != '') {
            //add zero just for testing
            if(!$this->accounting(0)) {
                die ("Error while accounting SMS, could not send");
            }
        }

        if(!$this->sms->sendSMS()) {
          die('Error: ' . $this->sms->getErrorDescription() . "\n");
        }
        else {
            if(trim($key) != '') {
                $c = count($to);
                $nrs = implode(',', $to);
                $this->accounting($c, $nrs, $key);
            }
        }
        return true;
    }




    public function sendByUsername($username, $subject, $message, $fromAdress = null, $fromName = null) {
        die('Not Implemented');
    }



    private function accounting($amount, $numbers = '', $key) {
        $user = $GLOBALS['POOL']->config->aspsms_accounting_username;
        $pw = $GLOBALS['POOL']->config->aspsms_accounting_password;
        $db = $GLOBALS['POOL']->config->aspsms_accounting_database;
        if(trim($user) != '' AND trim($pw) != '' AND trim($db) != '') {
            $dsn = "mysql://$user:$pw@localhost/$db";
            $db = @MDB2::connect($dsn, NULL);

            if (PEAR::isError($db)) {
                return false;
            }

            $sql = "INSERT INTO sent_sms (`date`,`amount`,`key`,`numbers`) VALUES (NOW(),'$amount','$key','$numbers')";
            $res = $db->query($sql);

            if (PEAR::isError($res)) {
                return false;
            }
            return true;
        }
        return false;
    }


    private function encodeSms($message) {
        $newmsg = '';
        //messages ned to be iso-latin
        $message = utf8_decode($message);
        for($x = 0; $x < strlen($message); $x++) {
            if(ord($message[$x]) > 127) {
                $newmsg .= "&#".ord($message[$x]).";";
            }
            else {
                $newmsg .= $message[$x];
            }
        }
        return $newmsg;
    }

    private function getCredits() {
        if(!$credits = $this->sms->showCredits()) {
             die('Error: ' . $this->sms->getErrorDescription() . "\n");
        }
        if(!$credits = $this->sms->getCredits() ) {
          die('Error: ' . $this->sms->getErrorDescription() . "\n");
        }
        return $credits;
    }


    private function cleanNumbers($to) {
        $numbers = array();
        if(is_array($to)){
            foreach($to as $nr) {
                if($ok = $this->checkNumber($nr)) {
                    $numbers[] = $ok;
                }
            }
        }
        else {
            if($ok = $this->checkNumber($to)) {
                $numbers[] = $ok;
            }
        }
        return $numbers;
    }


    private function checkNumber($number) {
        $number = str_replace("+","00",$number);
        $number = preg_replace("/[^0-9]/", "", $number);
        if(strlen($number) != 13) {
            return false;
        }
        return $number;
    }





}
