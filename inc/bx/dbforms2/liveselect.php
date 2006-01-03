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
class bx_dbforms2_liveselect {

    /**
     *  DOCUMENT_ME
     *  @var var
     */
    public $nameField = '';
    /**
     *  DOCUMENT_ME
     *  @var var
     */
    public $whereFields = '';
    /**
     *  DOCUMENT_ME
     *  @var var
     */
    public $limit = 10;

    /**
     *  DOCUMENT_ME
     *  @var var
     */
    public $idField = 'id';

    /**
     *  DOCUMENT_ME
     *  @var var
     */
    public $tableName = '';

    /**
     *  DOCUMENT_ME
     *  @var var
     */
    public $tablePrefix = '';

    /**
     *  DOCUMENT_ME
     *  @var var
     */
    public $query = '';

    /**
     *  DOCUMENT_ME
     *  @var var
     */
    public $leftJoin = '';

    /**
     *  DOCUMENT_ME
     *  @var var
     */
    public $orderBy = 'id';

    /**
     *  DOCUMENT_ME
     *  @var var
     */
	public $getMatcher = '';
	
    /**
     *  DOCUMENT_ME
     *  @var var
     */
	public $notNullFields = '';
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function getNormalizedQuery() {
        // strip ws
        return trim($this->query);
    }

    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function setLeftJoin($leftJoin) {

        if ($leftJoin) {
            $leftJoin = str_replace('{tablePrefix}', $this->tablePrefix,$leftJoin);
            $this->leftJoin = "left join " . $leftJoin;
        }
    }
}

?>
