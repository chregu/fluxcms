<?php
// +----------------------------------------------------------------------+
// | BxCMS                                                                |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <flux@bitflux.ch>                               |
// +----------------------------------------------------------------------+
//
// $Id$

/**
 * DOCUMENT_ME
 *
* @package bx_dbforms2
 * @category 
 * @author Bitflux GmbH <flux@bitflux.ch>
 */
class bx_dbforms2_common {

    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public static function transformFormXML($dom,$tablePrefix,$formxsl) {
        if (file_exists($formxsl)) {
            $xslt = new XSLTProcessor();
            $xsl = new DOMDocument();
            $xsl->load($formxsl);
            $xslt->importStylesheet($xsl);
            $xslt->setParameter('', 'webroot', BX_WEBROOT);
            $xslt->setParameter('', 'tablePrefix', $tablePrefix);
            $xslt->registerPhpFunctions();
            
            return $xslt->transformToDoc($dom);
        }
        
        return false;
    }
    
}

?>
