<?php

class bx_filters_patforms_formhandler {

    protected $i18n = NULL;
    protected $configParams = array();
    public $returnsDOM = FALSE;

    public function setI18nDriver($configParams, $driver) {
        $this->configParams = $configParams;
        $this->i18n = $driver;
    }

    public function getText($key) {
        if(!($text = $this->i18n->getText($key))) {
            return $key;
        }
        return $text;
    }

}

?>
