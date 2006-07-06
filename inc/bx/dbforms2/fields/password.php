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
 * @package
 * @category 
 * @author Bitflux GmbH <flux@bitflux.ch>
 */
class bx_dbforms2_fields_password extends bx_dbforms2_fields_text {
    
    public function __construct($name) {
        parent::__construct($name);
        
        $this->type = 'password';
        $this->XMLName = 'input';
    }
    
}

?>
