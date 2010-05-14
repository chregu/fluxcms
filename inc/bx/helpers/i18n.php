<?php


class bx_helpers_i18n {

    static function translate($key, $src = 'lang/master') {
        $srcfile = BX_PROJECT_DIR.$src;
        $i18n = popoon_classes_i18n::getDriverInstance($srcfile, $GLOBALS['POOL']->config->getOutputLocale());
        return $i18n->translate($key);
    }
     
}