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

class bx_dbforms2 {
    
    const QUERYMODE_SELECT  = 1;
    const QUERYMODE_UPDATE  = 2;
    const QUERYMODE_INSERT  = 3;
    const QUERYMODE_DELETE  = 4;

    // events
    const EVENT_SELECT_PRE  = 1;
    const EVENT_SELECT_POST = 2;

    const EVENT_INSERT_PRE  = 3;
    const EVENT_INSERT_POST = 4;

    const EVENT_UPDATE_PRE  = 5;
    const EVENT_UPDATE_POST = 6;

    const EVENT_DELETE_PRE  = 7;
    const EVENT_DELETE_POST = 8;

    
}

