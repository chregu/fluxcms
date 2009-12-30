<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the 'License');      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an 'AS IS' BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Philipp Stucki <philipp@bitflux.ch>                          |
// +----------------------------------------------------------------------+
//
// $Id$

/**
* @author   Philipp Stucki <philipp@bitflux.ch>
* @version  $Id$
* @package  popoon
*/

class popoon_components_transformers_i18n_driver {
    
    /**
     *  Translates the given string and replaces all occurrences of the
     *  passed parameters.
     *
     *  @param  string $text Text to translate
     *  @param  array $params Parameters to replace
     *  @access public
     *  @return string Translated string with replaced parameters
     */
    
    public function translate($text, $params = NULL) {
        $ret = $this->getText($text);
        if($ret === FALSE OR $ret == '')
            $ret = $text;
        return $this->substitute($ret, $params);
        
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function translate2($text, $params = NULL) {
        $t = $this->getText($this->substitute($text, $params));
        if($t === FALSE OR $t == '') {
            return $this->translate($text, $params);
        }
        return $t;
    }
    
    /**
     *  Substitutes all references in $t with the given parameters.
     *
     *  @param  string  $t Text to process
     *  @param  array  $params Parameters to substitute in $t
     *  @access protected
     *  @return string String with substituted references
     */
    protected function substitute($t, $params) {
        return preg_replace("/\{([a-zA-Z0-9_]*)\}/e", "\$params['$1']", $t);
    }
    
}

