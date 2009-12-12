<?php
class testhelper_scanner {

    /**
    * comma sep. list of rec. scanned directories
    * root dir is detected thru BLUE_LIBS_DIR
    */
    private $_ascandirs = '/bx/';

    /**
    * reserved filenames not to take into consideration
    */
    private $_areservedfns = array('..','.','.svn');

    private $_sbasedir = '';
    private $_serr = '';
    private $_atestcaselist = array();

    function __construct() {

        //include('./test-config.php');

        $this->_sbasedir = substr(BX_LIBS_DIR,0,-4);
        
        if(!$this->_scanForTests())
            die('Scanning failed with Error: '.$this->getError());
    }

    private function _setError($slocation,$serr) {

        if(!$serr) $serr = 'Unknown Error Occurred.';
        if(!$slocation) $slocation = 'unkown_class::unknown_method';
        $this->_serr = sprintf('%s:&nbsp;<b>%s</b>',$slocation,htmlentities($serr));
        return false;
    }

    /**
    * collect testcases
    */
    private function _scanForTests() {

        $abasedirs = explode(',',$this->_ascandirs);
        if(!is_array($abasedirs))
            return $this->_setError(__METHOD__,'no scandirs found');

        for($i=0;$i<count($abasedirs);$i++) {

            if(!is_dir($this->_sbasedir.$abasedirs[$i])) // only stop if we're totally wrong as there might be more in the bush ;-)
                return $this->_setError(__METHOD__,$this->_sbasedir.$abasedirs[$i].' is not a valid directory.');

            if(!$this->_findTests($this->_sbasedir.$abasedirs[$i]))
                return $this->_setError(__METHOD__,'Could not search for tests: '.$this->getError());
        }

        if(count($this->_atestcaselist)==0)
            return $this->_setError(__METHOD__,'No testcases found.');

        return true;
    }

    /**
    * loops rec through dirs, to find the ones called 'test'.
    * all files w/extension .php will be added, they contain the
    * test classes to be required_once and added to the suite.
    *
    * important: call files foo_bar_some (ie: blue_test_helper) - so classes can be loaded through __autoload
    */
    private function _findTests($sdir,$bistestloc = false) {

        $pDir = dir($sdir);

        while (false !== ($sentry = $pDir->read())) {

            if(is_dir($sdir.'/'.$sentry) && !in_array($sentry,$this->_areservedfns)) {

                if(!$this->_findTests($sdir.'/'.$sentry,($sentry=='test')))
                    return $this->_setError(__METHOD__,'Could not search for tests: '.$this->getError());
            }
            if(substr($sentry,-4)=='.php' && $bistestloc)
                $this->_atestcaselist[] = $sdir.'/'.$sentry;
        }

        $pDir->close();
        return true;
    }

    public function getError() {

        return $this->_serr;
    }

    public function getTestCaseList() {

        return $this->_atestcaselist;
    }
}
