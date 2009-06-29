<?php

class bx_helpers_shorturl {

    /*
     * var MDB2
     */
    protected $db = null;

    public function __construct() {
        $this->db = $GLOBALS['POOL']->db;
        $this->tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
    }

    public function codeExists($code) {
        $query = "SELECT path from ".$this->tablePrefix."properties where ns = 'to:' and value = BINARY " . $this->db->quote($code);
        if ($this->db->query($query)->numRows() > 0) {
            return true;
        }
        return false;
    }

   public function getUrlFromCode($code) {

        $query = "SELECT path from ".$this->tablePrefix."properties where ns = 'to:' and  value = BINARY :code";
        $stm = $this->db->prepare($query);
        $f = $stm->execute(array(
                ":code" => $code
        ));
        return $f->fetchOne();
    }

    protected function getNextCode() {
       $code = $this->id2url($this->nextId());
        if ($this->codeExists($code)) {
            $code = $this->getNextCode();
        }

        return $code;
    }

    protected function id2url($val) {
        if (0 == $val) {
            return 0;
        }
        $base = 63;
        $symbols = 'JVPAGYRKBWLUTHXCDSZNFOQMEIef02nwy1mdtx7p89653cbaoj4igkvrsqz_hul';
        $result = '';
        $exp = $oldpow = 1;
        while ($val > 0 && $exp < 10) {
            $pow = pow($base, $exp++);
            $mod = ($val % $pow);
            $result = substr($symbols, $mod / $oldpow, 1) . $result;
            $val -= $mod;
            $oldpow = $pow;
        }
        return $result;
    }


    public static function getCode($url,$usercode = null) {
        $sh = new bx_helpers_shorturl();
        return $sh->getShortCode($url,$usercode);
    }

    public function getShortCode($url, $usercode = null) {

        //$url = $this->normalizeUrl($url);

        //check if a code exists
        $code = $this->getCodeFromDB($url);
        //if not create one
        if (!$code) {
            // insert url
            $this->insertUrl($url, $usercode);
            // get code again (if another code with the same url was inserted in the meantime...)
            $code = $this->getCodeFromDB($url);

        }
        return $code;

    }


    protected function getCodeFromDB($url) {
        $query = "SELECT value FROM ".$this->tablePrefix."properties where ns = 'to:' and path = :url";
        $stm = $this->db->prepare($query);

        $res = $stm->execute(array(
                ':url' => $url
        ));
        return $res->fetchOne();
    }


    protected  function insertUrl($url, $code = null) {
        if (!$code) {
            $code = $this->getNextCode();
        }

        $query = 'INSERT INTO '.$this->tablePrefix.'properties (path,ns,name,value) VALUES (:path, :ns,"auto",:code)';

        $stm = $GLOBALS['POOL']->dbwrite->prepare($query);
        if (!$stm->execute(array(
                ':path' => $url,
                ':ns' => 'to:',
                ':code' => $code

        ))) {
            die("DB Error");
        }

        return $code;
    }

    protected function nextId() {
        return $GLOBALS['POOL']->dbwrite->nextID($this->tablePrefix."_shorturl");
    }
}
