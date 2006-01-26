<?php
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2004 Manuel Lemos, Tomas V.V.Cox,                 |
// | Stig. S. Bakken, Lukas Smith                                         |
// | All rights reserved.                                                 |
// +----------------------------------------------------------------------+
// | MDB2 is a merge of PEAR DB and Metabases that provides a unified DB  |
// | API as well as database abstraction for PHP applications.            |
// | This LICENSE is in the BSD license style.                            |
// |                                                                      |
// | Redistribution and use in source and binary forms, with or without   |
// | modification, are permitted provided that the following conditions   |
// | are met:                                                             |
// |                                                                      |
// | Redistributions of source code must retain the above copyright       |
// | notice, this list of conditions and the following disclaimer.        |
// |                                                                      |
// | Redistributions in binary form must reproduce the above copyright    |
// | notice, this list of conditions and the following disclaimer in the  |
// | documentation and/or other materials provided with the distribution. |
// |                                                                      |
// | Neither the name of Manuel Lemos, Tomas V.V.Cox, Stig. S. Bakken,    |
// | Lukas Smith nor the names of his contributors may be used to endorse |
// | or promote products derived from this software without specific prior|
// | written permission.                                                  |
// |                                                                      |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS  |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT    |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS    |
// | FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE      |
// | REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,          |
// | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, |
// | BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS|
// |  OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED  |
// | AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT          |
// | LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY|
// | WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE          |
// | POSSIBILITY OF SUCH DAMAGE.                                          |
// +----------------------------------------------------------------------+
// | Author: Lukas Smith <smith@pooteeweet.org>                           |
// +----------------------------------------------------------------------+
//
// $Id: Schema.php,v 1.54 2006/01/13 09:58:37 lsmith Exp $
//

require_once 'MDB2.php';

define('MDB2_SCHEMA_DUMP_ALL',          0);
define('MDB2_SCHEMA_DUMP_STRUCTURE',    1);
define('MDB2_SCHEMA_DUMP_CONTENT',      2);

/**
 * The method mapErrorCode in each MDB2_Schema_dbtype implementation maps
 * native error codes to one of these.
 *
 * If you add an error code here, make sure you also add a textual
 * version of it in MDB2_Schema::errorMessage().
 */

define('MDB2_SCHEMA_ERROR',              -1);
define('MDB2_SCHEMA_ERROR_PARSE',        -2);
define('MDB2_SCHEMA_ERROR_NOT_CAPABLE',  -3);
define('MDB2_SCHEMA_ERROR_UNSUPPORTED',  -4);    // Driver does not support this function
define('MDB2_SCHEMA_ERROR_INVALID',      -5);    // Invalid attribute value
define('MDB2_SCHEMA_ERROR_NODBSELECTED', -6);

/**
 * The database manager is a class that provides a set of database
 * management services like installing, altering and dumping the data
 * structures of databases.
 *
 * @package MDB2_Schema
 * @category Database
 * @author  Lukas Smith <smith@pooteeweet.org>
 */
class MDB2_Schema extends PEAR
{
    // {{{ properties

    var $db;

    var $warnings = array();

    var $options = array(
        'fail_on_invalid_names' => true,
        'dtd_file' => false,
    );

    var $database_definition = array(
        'name' => '',
        'create' => false,
        'tables' => array()
    );

    // }}}
    // {{{ apiVersion()

    /**
     * Return the MDB2 API version
     *
     * @return string     the MDB2 API version number
     * @access public
     */
    function apiVersion()
    {
        return '0.4.0';
    }

    // }}}
    // {{{ resetWarnings()

    /**
     * reset the warning array
     *
     * @access public
     */
    function resetWarnings()
    {
        $this->warnings = array();
    }

    // }}}
    // {{{ getWarnings()

    /**
     * get all warnings in reverse order.
     * This means that the last warning is the first element in the array
     *
     * @return array with warnings
     * @access public
     * @see resetWarnings()
     */
    function getWarnings()
    {
        return array_reverse($this->warnings);
    }

    // }}}
    // {{{ setOption()

    /**
     * set the option for the db class
     *
     * @param string $option option name
     * @param mixed $value value for the option
     * @return mixed MDB2_OK or MDB2 Error Object
     * @access public
     */
    function setOption($option, $value)
    {
        if (isset($this->options[$option])) {
            if (is_null($value)) {
                return $this->raiseError(MDB2_SCHEMA_ERROR, null, null,
                    'may not set an option to value null');
            }
            $this->options[$option] = $value;
            return MDB2_OK;
        }
        return $this->raiseError(MDB2_SCHEMA_ERROR_UNSUPPORTED, null, null,
            "unknown option $option");
    }

    // }}}
    // {{{ getOption()

    /**
     * returns the value of an option
     *
     * @param string $option option name
     * @return mixed the option value or error object
     * @access public
     */
    function getOption($option)
    {
        if (isset($this->options[$option])) {
            return $this->options[$option];
        }
        return $this->raiseError(MDB2_SCHEMA_ERROR_UNSUPPORTED,
            null, null, "unknown option $option");
    }

    // }}}
    // {{{ factory()

    /**
     * Create a new MDB2 object for the specified database type
     * type
     *
     * @param   mixed   $db       'data source name', see the MDB2::parseDSN
     *                            method for a description of the dsn format.
     *                            Can also be specified as an array of the
     *                            format returned by MDB2::parseDSN.
     *                            Finally you can also pass an existing db
     *                            object to be used.
     * @param   mixed   $options  An associative array of option names and
     *                            their values.
     * @return  mixed MDB2_OK on success, or a MDB2 error object
     * @access  public
     * @see     MDB2::parseDSN
     */
    function &factory(&$db, $options = array())
    {
        $obj =& new MDB2_Schema();
        $err = $obj->connect($db, $options);
        if (PEAR::isError($err)) {
            return $err;
        }
        return $obj;
    }

    // }}}
    // {{{ connect()

    /**
     * Create a new MDB2 connection object and connect to the specified
     * database
     *
     * @param   mixed   $db       'data source name', see the MDB2::parseDSN
     *                            method for a description of the dsn format.
     *                            Can also be specified as an array of the
     *                            format returned by MDB2::parseDSN.
     *                            Finally you can also pass an existing db
     *                            object to be used.
     * @param   mixed   $options  An associative array of option names and
     *                            their values.
     * @return  mixed MDB2_OK on success, or a MDB2 error object
     * @access  public
     * @see     MDB2::parseDSN
     */
    function connect(&$db, $options = array())
    {
        $db_options = array();
        if (is_array($options) && !empty($options)) {
            foreach ($options as $option => $value) {
                if (array_key_exists($option, $this->options)) {
                    $err = $this->setOption($option, $value);
                    if (PEAR::isError($err)) {
                        return $err;
                    }
                } else {
                    $db_options[$option] = $value;
                }
            }
        }
        $this->disconnect();
        if (!MDB2::isConnection($db)) {
            $db =& MDB2::factory($db, $db_options);
        }
        if (PEAR::isError($db)) {
            return $db;
        }

        $this->db =& $db;
        $this->db->loadModule('Manager');
        $this->db->loadModule('Reverse');
        return MDB2_OK;
    }

    // }}}
    // {{{ disconnect()

    /**
     * Log out and disconnect from the database.
     *
     * @access public
     */
    function disconnect()
    {
        if (MDB2::isConnection($this->db)) {
            $this->db->disconnect();
            unset($this->db);
        }
    }

    // }}}
    // {{{ parseDatabaseDefinitionFile()

    /**
     * Parse a database definition file by creating a Metabase schema format
     * parser object and passing the file contents as parser input data stream.
     *
     * @param string $input_file the path of the database schema file.
     * @param array $variables an associative array that the defines the text
     * string values that are meant to be used to replace the variables that are
     * used in the schema description.
     * @param bool $fail_on_invalid_names (optional) make function fail on invalid
     * names
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function parseDatabaseDefinitionFile($input_file, $variables = array(),
        $fail_on_invalid_names = true, $structure = false)
    {
        $dtd_file = $this->getOption('dtd_file');
        if ($dtd_file) {
            require_once 'XML/DTD/XmlValidator.php';
            $dtd =& new XML_DTD_XmlValidator;
            if (!$dtd->isValid($dtd_file, $input_file)) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_PARSE, null, null, $dtd->getMessage());
            }
        }

        require_once 'MDB2/Schema/Parser.php';
        $parser =& new MDB2_Schema_Parser($variables, $fail_on_invalid_names, $structure);
        $result = $parser->setInputFile($input_file);
        if (PEAR::isError($result)) {
            return $result;
        }

        $result = $parser->parse();
        if (PEAR::isError($result)) {
            return $result;
        }
        if (PEAR::isError($parser->error)) {
            return $parser->error;
        }

        return $parser->database_definition;
    }

    // }}}
    // {{{ getDefinitionFromDatabase()

    /**
     * Attempt to reverse engineer a schema structure from an existing MDB2
     * This method can be used if no xml schema file exists yet.
     * The resulting xml schema file may need some manual adjustments.
     *
     * @return mixed MDB2_OK or array with all ambiguities on success, or a MDB2 error object
     * @access public
     */
    function getDefinitionFromDatabase()
    {
        $database = $this->db->database_name;
        if (empty($database)) {
            return $this->raiseError('it was not specified a valid database name');
        }

        $this->database_definition = array(
            'name' => $database,
            'create' => true,
            'tables' => array(),
            'sequences' => array(),
        );

        $tables = $this->db->manager->listTables();
        if (PEAR::isError($tables)) {
            return $tables;
        }

        foreach ($tables as $table_name) {
            $fields = $this->db->manager->listTableFields($table_name);
            if (PEAR::isError($fields)) {
                return $fields;
            }

            $this->database_definition['tables'][$table_name] = array('fields' => array());
            $table_definition =& $this->database_definition['tables'][$table_name];
            foreach ($fields as $field_name) {
                $definition = $this->db->reverse->getTableFieldDefinition($table_name, $field_name);
                if (PEAR::isError($definition)) {
                    return $definition;
                }

                if (array_key_exists('autoincrement', $definition[0])
                    && $definition[0]['autoincrement']
                ) {
                    $definition[0]['default'] = 0;
                }
                $table_definition['fields'][$field_name] = $definition[0];
                $field_choices = count($definition);
                if ($field_choices > 1) {
                    $warning = "There are $field_choices type choices in the table $table_name field $field_name (#1 is the default): ";
                    $field_choice_cnt = 1;
                    $table_definition['fields'][$field_name]['choices'] = array();
                    foreach ($definition as $field_choice) {
                        $table_definition['fields'][$field_name]['choices'][] = $field_choice;
                        $warning .= 'choice #'.($field_choice_cnt).': '.serialize($field_choice);
                        $field_choice_cnt++;
                    }
                    $this->warnings[] = $warning;
                }
            }
            $index_definitions = array();
            $indexes = $this->db->manager->listTableIndexes($table_name);
            if (PEAR::isError($indexes)) {
                return $indexes;
            }

            if (is_array($indexes) && !empty($indexes)
                && !array_key_exists('indexes', $table_definition)
            ) {
                $table_definition['indexes'] = array();
                foreach ($indexes as $index_name) {
                    $this->db->expectError(MDB2_ERROR_NOT_FOUND);
                    $definition = $this->db->reverse->getTableIndexDefinition($table_name, $index_name);
                    $this->db->popExpect();
                    if (PEAR::isError($definition, MDB2_ERROR_NOT_FOUND)) {
                        continue;
                    }
                    if (PEAR::isError($definition)) {
                        return $definition;
                    }
                   $index_definitions[$index_name] = $definition;
                }
            }
            $constraints = $this->db->manager->listTableConstraints($table_name);

            if (PEAR::isError($constraints)) {
                return $constraints;
            }
            if (is_array($constraints) && !empty($constraints)
                && !array_key_exists('indexes', $table_definition)
            ) {
                $table_definition['indexes'] = array();
                foreach ($constraints as $index_name) {
                    $this->db->expectError(MDB2_ERROR_NOT_FOUND);
                    $definition = $this->db->reverse->getTableConstraintDefinition($table_name, $index_name);
                    $this->db->popExpect();
                    if (PEAR::isError($definition, MDB2_ERROR_NOT_FOUND)) {
                        continue;
                    }
                    if (PEAR::isError($definition)) {
                        return $definition;
                    }
                    $index_definitions[$index_name] = $definition;
                }
            }
            if (!empty($index_definitions)) {
                $table_definition['indexes'] = $index_definitions;
            }
        }

        $sequences = $this->db->manager->listSequences();
        if (PEAR::isError($sequences)) {
            return $sequences;
        }

        if (is_array($sequences) && !empty($sequences)) {
            foreach ($sequences as $sequence_name) {
                $definition = $this->db->reverse->getSequenceDefinition($sequence_name);
                if (PEAR::isError($definition)) {
                    return $definition;
                }
                $this->database_definition['sequences'][$sequence_name] = $definition;
            }
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ createTableIndexes()

    /**
     * create a indexes om a table
     *
     * @param string $table_name  name of the table
     * @param array  $indexes     indexes to be created
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @param boolean $overwrite  determine if the table/index should be
                                  overwritten if it already exists
     * @access public
     */
    function createTableIndexes($table_name, $indexes, $overwrite = false)
    {
        if (!$this->db->supports('indexes')) {
            $this->db->debug('Indexes are not supported');
            return MDB2_OK;
        }

        $supports_primary_key = $this->db->supports('primary_key');
        foreach ($indexes as $index_name => $index) {
            $errorcodes = array(MDB2_ERROR_UNSUPPORTED, MDB2_ERROR_NOT_CAPABLE);
            $this->db->expectError($errorcodes);
            if (array_key_exists('primary', $index) || array_key_exists('unique', $index)) {
                $indexes = $this->db->manager->listTableConstraints($table_name);
            } else {
                $indexes = $this->db->manager->listTableIndexes($table_name);
            }
            $this->db->popExpect();
            if (PEAR::isError($indexes)) {
                if (!MDB2::isError($indexes, $errorcodes)) {
                    return $indexes;
                }
            } elseif (is_array($indexes) && in_array($index_name, $indexes)) {
                if (!$overwrite) {
                    $this->db->debug('Index already exists: '.$index_name);
                    return MDB2_OK;
                }
                if (array_key_exists('primary', $index) || array_key_exists('unique', $index)) {
                    $result = $this->db->manager->dropConstraint($table_name, $index_name);
                } else {
                    $result = $this->db->manager->dropIndex($table_name, $index_name);
                }
                if (PEAR::isError($result)) {
                    return $result;
                }
                $this->db->debug('Overwritting index: '.$index_name);
            }

            // check if primary is being used and if it's supported
            if (array_key_exists('primary', $index) && !$supports_primary_key) {
                /**
                 * Primary not supported so we fallback to UNIQUE
                 * and making the field NOT NULL
                 */
                unset($index['primary']);
                $index['unique'] = true;
                $fields = $index['fields'];

                $changes = array();

                foreach ($fields as $field => $empty) {
                    $field_info = $this->db->reverse->getTableFieldDefinition($table_name, $field);
                    if (PEAR::isError($field_info)) {
                        return $field_info;
                    }

                    $changes['change'][$field] = $field_info[0][0];
                    $changes['change'][$field]['notnull'] = true;
                }
                $this->db->manager->alterTable($table_name, $changes, false);
            }

            if (array_key_exists('primary', $index) || array_key_exists('unique', $index)) {
                $result = $this->db->manager->createConstraint($table_name, $index_name, $index);
            } else {
                $result = $this->db->manager->createIndex($table_name, $index_name, $index);
            }
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ createTable()

    /**
     * create a table and inititialize the table if data is available
     *
     * @param string $table_name  name of the table to be created
     * @param array  $table       multi dimensional array that containts the
     *                            structure and optional data of the table
     * @param boolean $overwrite  determine if the table/index should be
                                  overwritten if it already exists
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function createTable($table_name, $table, $overwrite = false)
    {
        $create = true;
        $errorcodes = array(MDB2_ERROR_UNSUPPORTED, MDB2_ERROR_NOT_CAPABLE);
        $this->db->expectError($errorcodes);
        $tables = $this->db->manager->listTables();
        $this->db->popExpect();
        if (PEAR::isError($tables)) {
            if (!MDB2::isError($tables, $errorcodes)) {
                return $tables;
            }
        } elseif (is_array($tables) && in_array($table_name, $tables)) {
            if (!$overwrite) {
                $create = false;
                $this->db->debug('Table already exists: '.$table_name);
            } else {
                $result = $this->db->manager->dropTable($table_name);
                if (PEAR::isError($result)) {
                    return $result;
                }
                $this->db->debug('Overwritting table: '.$table_name);
            }
        }

        if ($create) {
            $result = $this->db->manager->createTable($table_name, $table['fields']);
            if (PEAR::isError($result)) {
                return $result;
            }
        }

        if (array_key_exists('initialization', $table) && is_array($table['initialization'])) {
            $result = $this->initializeTable($table_name, $table);
            if (PEAR::isError($result)) {
                return $result;
            }
        }

        if (array_key_exists('indexes', $table) && is_array($table['indexes'])) {
            $result = $this->createTableIndexes($table_name, $table['indexes'], $overwrite);
            if (PEAR::isError($result)) {
                return $result;
            }
        }

        return MDB2_OK;
    }

    // }}}
    // {{{ initializeTable()

    /**
     * inititialize the table with data
     *
     * @param string $table_name        name of the table
     * @param array  $table       multi dimensional array that containts the
     *                            structure and optional data of the table
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function initializeTable($table_name, $table)
    {
        foreach ($table['fields'] as $field_name => $field) {
            $placeholders[$field_name] = ':'.$field_name;
            $types[$field_name] = $field['type'];
        }
        $fields = implode(',', array_keys($table['fields']));
        $placeholders = implode(',', $placeholders);
        $query = "INSERT INTO $table_name ($fields) VALUES ($placeholders)";
        $stmt = $this->db->prepare($query, $types, null, true);
        if (PEAR::isError($stmt)) {
            return $stmt;
        }

        foreach ($table['initialization'] as $instruction) {
            switch ($instruction['type']) {
            case 'insert':
                if (array_key_exists('fields', $instruction) && is_array($instruction['fields'])) {
                    $result = $stmt->bindParamArray($instruction['fields']);
                    if (PEAR::isError($result)) {
                        return $result;
                    }

                    $result = $stmt->execute();
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                }
                break;
            }
        }
        return $stmt->free();
    }

    // }}}
    // {{{ createSequence()

    /**
     * create a sequence
     *
     * @param string $sequence_name  name of the sequence to be created
     * @param array  $sequence       multi dimensional array that containts the
     *                               structure and optional data of the table
     * @param boolean $overwrite    determine if the sequence should be overwritten
                                    if it already exists
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function createSequence($sequence_name, $sequence, $overwrite = false)
    {
        if (!$this->db->supports('sequences')) {
            $this->db->debug('Sequences are not supported');
            return MDB2_OK;
        }

        $errorcodes = array(MDB2_ERROR_UNSUPPORTED, MDB2_ERROR_NOT_CAPABLE);
        $this->db->expectError($errorcodes);
        $sequences = $this->db->manager->listSequences();
        $this->db->popExpect();
        if (PEAR::isError($sequences)) {
            if (!MDB2::isError($sequences, $errorcodes)) {
                return $sequences;
            }
        } elseif (is_array($sequence) && in_array($sequence_name, $sequences)) {
            if (!$overwrite) {
                $this->db->debug('Sequence already exists: '.$sequence_name);
                return MDB2_OK;
            }

            $result = $this->db->manager->dropSequence($sequence_name);
            if (PEAR::isError($result)) {
                return $result;
            }
            $this->db->debug('Overwritting sequence: '.$sequence_name);
        }

        $start = 1;
        $field = '';
        if (array_key_exists('on', $sequence)) {
            $table = $sequence['on']['table'];
            $field = $sequence['on']['field'];

            $errorcodes = array(MDB2_ERROR_UNSUPPORTED, MDB2_ERROR_NOT_CAPABLE);
            $this->db->expectError($errorcodes);
            $tables = $this->db->manager->listTables();
            $this->db->popExpect();
            if (PEAR::isError($tables) && !MDB2::isError($tables, $errorcodes)) {
                 return $tables;
            }

            if (!PEAR::isError($tables) &&
                is_array($tables) && in_array($table, $tables)
            ) {
                if ($this->db->supports('summary_functions')) {
                    $query = "SELECT MAX($field) FROM $table";
                } else {
                    $query = "SELECT $field FROM $table ORDER BY $field DESC";
                }
                $start = $this->db->queryOne($query, 'integer');
                if (PEAR::isError($start)) {
                    return $start;
                }
                ++$start;
            } else {
                $this->warnings[] = 'Could not sync sequence: '.$sequence_name;
            }
        } elseif (array_key_exists('start', $sequence) && is_numeric($sequence['start'])) {
            $start = $sequence['start'];
            $table = '';
        }

        $result = $this->db->manager->createSequence($sequence_name, $start);
        if (PEAR::isError($result)) {
            return $result;
        }

        return MDB2_OK;
    }

    // }}}
    // {{{ createDatabase()

    /**
     * Create a database space within which may be created database objects
     * like tables, indexes and sequences. The implementation of this function
     * is highly DBMS specific and may require special permissions to run
     * successfully. Consult the documentation or the DBMS drivers that you
     * use to be aware of eventual configuration requirements.
     *
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function createDatabase()
    {
        if (!isset($this->database_definition['name']) || !$this->database_definition['name']) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_INVALID, null, null,
                'no valid database name specified');
        }
        $create = (isset($this->database_definition['create']) && $this->database_definition['create']);
        $overwrite = (isset($this->database_definition['overwrite']) && $this->database_definition['overwrite']);
        if ($create) {
            $errorcodes = array(MDB2_ERROR_UNSUPPORTED, MDB2_ERROR_NOT_CAPABLE);
            $this->db->expectError($errorcodes);
            $databases = $this->db->manager->listDatabases();

            // Lower / Upper case the db name if the portability deems so.
            if ($this->db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
                $func = $this->db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper';
                $db_name = $func($this->database_definition['name']);
            }

            $this->db->popExpect();
            if (PEAR::isError($databases)) {
                if (!MDB2::isError($databases, $errorcodes)) {
                    return $databases;
                }
            } elseif (is_array($databases) && in_array($db_name, $databases)) {
                if (!$overwrite) {
                    $this->db->debug('Database already exists: ' . $this->database_definition['name']);
                    $create = false;
                } else {
                    $result = $this->db->manager->dropDatabase($this->database_definition['name']);
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                    $this->db->debug('Overwritting database: '.$this->database_definition['name']);
                }
            }
            if ($create) {
                $this->db->expectError(MDB2_ERROR_ALREADY_EXISTS);
                $result = $this->db->manager->createDatabase($this->database_definition['name']);
                $this->db->popExpect();
                if (PEAR::isError($result) && !MDB2::isError($result, MDB2_ERROR_ALREADY_EXISTS)) {
                    return $result;
                }
            }
        }
        $previous_database_name = $this->db->setDatabase($this->database_definition['name']);
        if (($support_transactions = $this->db->supports('transactions'))
            && PEAR::isError($result = $this->db->beginTransaction())
        ) {
            return $result;
        }

        $created_objects = 0;
        if (isset($this->database_definition['tables'])
            && is_array($this->database_definition['tables'])
        ) {
            foreach ($this->database_definition['tables'] as $table_name => $table) {
                $result = $this->createTable($table_name, $table, $overwrite);
                if (PEAR::isError($result)) {
                    break;
                }
                $created_objects++;
            }
        }
        if (!PEAR::isError($result)
            && isset($this->database_definition['sequences'])
            && is_array($this->database_definition['sequences'])
        ) {
            foreach ($this->database_definition['sequences'] as $sequence_name => $sequence) {
                $result = $this->createSequence($sequence_name, $sequence, false, $overwrite);

                if (PEAR::isError($result)) {
                    break;
                }
                $created_objects++;
            }
        }

        if (PEAR::isError($result)) {
            if ($created_objects) {
                if ($support_transactions) {
                    $res = $this->db->rollback();
                    if (PEAR::isError($res))
                        $result = $this->raiseError(MDB2_SCHEMA_ERROR, null, null,
                            'Could not rollback the partially created database alterations ('.
                            $result->getMessage().' ('.$result->getUserinfo().'))');
                } else {
                    $result = $this->raiseError(MDB2_SCHEMA_ERROR, null, null,
                        'the database was only partially created ('.
                        $result->getMessage().' ('.$result->getUserinfo().'))');
                }
            }
        } else {
            if ($support_transactions) {
                $res = $this->db->commit();
                if (PEAR::isError($res))
                    $result = $this->raiseError(MDB2_SCHEMA_ERROR, null, null,
                        'Could not end transaction after successfully created the database ('.
                        $res->getMessage().' ('.$res->getUserinfo().'))');
            }
        }

        $this->db->setDatabase($previous_database_name);

        if (PEAR::isError($result) && $create
            && PEAR::isError($result2 = $this->db->manager->dropDatabase($this->database_definition['name']))
        ) {
            return $this->raiseError(MDB2_SCHEMA_ERROR, null, null,
                'Could not drop the created database after unsuccessful creation attempt ('.
                $result2->getMessage().' ('.$result2->getUserinfo().'))');
        }

        return $result;
    }

    // }}}
    // {{{ compareDefinitions()

    /**
     * compare a previous definition with the currenlty parsed definition
     *
     * @param array multi dimensional array that contains the previous definition
     * @param array multi dimensional array that contains the current definition
     * @return mixed array of changes on success, or a MDB2 error object
     * @access public
     */
    function compareDefinitions($previous_definition, $current_definition = null)
    {
        $current_definition = $current_definition ? $current_definition : $this->database_definition;
        $changes = array();

        if (array_key_exists('tables', $current_definition) && is_array($current_definition['tables'])) {
            $changes['tables'] = $defined_tables = array();
            foreach ($current_definition['tables'] as $table_name => $table) {
                $previous_tables = array();
                if (array_key_exists('tables', $previous_definition) && is_array($previous_definition)) {
                    $previous_tables = $previous_definition['tables'];
                }
                $change = $this->compareTableDefinitions($table_name, $previous_tables, $table, $defined_tables);
                if (PEAR::isError($change)) {
                    return $change;
                }
                if (!empty($change)) {
                    $changes['tables']+= $change;
                }
            }
            if (array_key_exists('tables', $previous_definition) && is_array($previous_definition['tables'])) {
                foreach ($previous_definition['tables'] as $table_name => $table) {
                    if (!array_key_exists($table_name, $defined_tables)) {
                        $changes['remove'][$table_name] = true;
                    }
                }
            }
        }
        if (array_key_exists('sequences', $current_definition) && is_array($current_definition['sequences'])) {
            $changes['sequences'] = $defined_sequences = array();
            foreach ($current_definition['sequences'] as $sequence_name => $sequence) {
                $previous_sequences = array();
                if (array_key_exists('sequences', $previous_definition) && is_array($previous_definition)) {
                    $previous_sequences = $previous_definition['sequences'];
                }
                $change = $this->compareSequenceDefinitions(
                    $sequence_name,
                    $previous_sequences,
                    $sequence,
                    $defined_sequences
                );
                if (PEAR::isError($change)) {
                    return $change;
                }
                if (!empty($change)) {
                    $changes['sequences']+= $change;
                }
            }
            if (array_key_exists('sequences', $previous_definition) && is_array($previous_definition['sequences'])) {
                foreach ($previous_definition['sequences'] as $sequence_name => $sequence) {
                    if (!array_key_exists($sequence_name, $defined_sequences)) {
                        $changes['remove'][$sequence_name] = true;
                    }
                }
            }
        }
        return $changes;
    }

    // }}}
    // {{{ compareTableFieldsDefinitions()

    /**
     * compare a previous definition with the currenlty parsed definition
     *
     * @param string $table_name    name of the table
     * @param array multi dimensional array that contains the previous definition
     * @param array multi dimensional array that contains the current definition
     * @return mixed array of changes on success, or a MDB2 error object
     * @access public
     */
    function compareTableFieldsDefinitions($table_name, $previous_definition,
        $current_definition, &$defined_fields)
    {
        $changes = array();

        if (is_array($current_definition)) {
            foreach ($current_definition as $field_name => $field) {
                $was_field_name = $field['was'];
                if (array_key_exists($field_name, $previous_definition)
                    && isset($previous_definition[$field_name]['was'])
                    && $previous_definition[$field_name]['was'] == $was_field_name
                ) {
                    $was_field_name = $field_name;
                }
                if (array_key_exists($was_field_name, $previous_definition)) {
                    if ($was_field_name != $field_name) {
                        $changes['rename'][$was_field_name] = array('name' => $field_name, 'definition' => $field);
                    }
                    if (array_key_exists($was_field_name, $defined_fields)) {
                        return $this->raiseError(MDB2_SCHEMA_ERROR_INVALID, null, null,
                            'the field "'.$was_field_name.
                            '" was specified as base of more than one field of table');
                    }
                    $defined_fields[$was_field_name] = true;
                    $change = $this->db->compareDefinition($field, $previous_definition[$was_field_name]);
                    if (PEAR::isError($change)) {
                        return $change;
                    }
                    if (!empty($change)) {
                        $change['definition'] = $field;
                        $changes['change'][$field_name] = $change;
                    }
                } else {
                    if ($field_name != $was_field_name) {
                        return $this->raiseError(MDB2_SCHEMA_ERROR_INVALID, null, null,
                            'it was specified a previous field name ("'.
                            $was_field_name.'") for field "'.$field_name.'" of table "'.
                            $table_name.'" that does not exist');
                    }
                    $changes['add'][$field_name] = $field;
                }
            }
        }
        if (isset($previous_definition) && is_array($previous_definition)) {
            foreach ($previous_definition as $field_previous_name => $field_previous) {
                if (!array_key_exists($field_previous_name, $defined_fields)) {
                    $changes['remove'][$field_previous_name] = true;
                }
            }
        }
        return $changes;
    }

    // }}}
    // {{{ compareTableIndexesDefinitions()

    /**
     * compare a previous definition with the currenlty parsed definition
     *
     * @param string $table_name    name of the table
     * @param array multi dimensional array that contains the previous definition
     * @param array multi dimensional array that contains the current definition
     * @return mixed array of changes on success, or a MDB2 error object
     * @access public
     */
    function compareTableIndexesDefinitions($table_name, $previous_definition,
        $current_definition, &$defined_indexes)
    {
        $changes = array();

        if (is_array($current_definition)) {
            foreach ($current_definition as $index_name => $index) {
                $was_index_name = $index['was'];
                if (array_key_exists($index_name, $previous_definition)
                    && isset($previous_definition[$index_name]['was'])
                    && $previous_definition[$index_name]['was'] == $was_index_name
                ) {
                    $was_index_name = $index_name;
                }
                if (array_key_exists($was_index_name, $previous_definition)) {
                    $change = array();
                    if ($was_index_name != $index_name) {
                        $change['name'] = $was_index_name;
                    }
                    if (array_key_exists($was_index_name, $defined_indexes)) {
                        return $this->raiseError(MDB2_SCHEMA_ERROR_INVALID, null, null,
                            'the index "'.$was_index_name.'" was specified as base of'.
                            ' more than one index of table "'.$table_name.'"');
                    }
                    $defined_indexes[$was_index_name] = true;

                    $previous_unique = isset($previous_definition[$was_index_name]['unique']);
                    $unique = array_key_exists('unique', $index);
                    if ($previous_unique != $unique) {
                        $change['unique'] = $unique;
                    }
                    $defined_fields = array();
                    $previous_fields = $previous_definition[$was_index_name]['fields'];
                    if (array_key_exists('fields', $index) && is_array($index['fields'])) {
                        foreach ($index['fields'] as $field_name => $field) {
                            if (array_key_exists($field_name, $previous_fields)) {
                                $defined_fields[$field_name] = true;
                                $sorting = (array_key_exists('sorting', $field) ? $field['sorting'] : '');
                                $previous_sorting = (isset($previous_fields[$field_name]['sorting'])
                                    ? $previous_fields[$field_name]['sorting'] : '');
                                if ($sorting != $previous_sorting) {
                                    $change['change'] = true;
                                }
                            } else {
                                $change['change'] = true;
                            }
                        }
                    }
                    if (isset($previous_fields) && is_array($previous_fields)) {
                        foreach ($previous_fields as $field_name => $field) {
                            if (!array_key_exists($field_name, $defined_fields)) {
                                $change['change'] = true;
                            }
                        }
                    }
                    if (!empty($change)) {
                        $changes['change'][$index_name] = $change;
                    }
                } else {
                    if ($index_name != $was_index_name) {
                        return $this->raiseError(MDB2_SCHEMA_ERROR_INVALID, null, null,
                            'it was specified a previous index name ("'.$was_index_name.
                            ') for index "'.$index_name.'" of table "'.$table_name.'" that does not exist');
                    }
                    $changes['add'][$index_name] = $current_definition[$index_name];
                }
            }
        }
        foreach ($previous_definition as $index_previous_name => $index_previous) {
            if (!array_key_exists($index_previous_name, $defined_indexes)) {
                $changes['remove'][$index_previous_name] = true;
            }
        }
        return $changes;
    }

    // }}}
    // {{{ compareTableDefinitions()

    /**
     * compare a previous definition with the currenlty parsed definition
     *
     * @param string $table_name    name of the table
     * @param array multi dimensional array that contains the previous definition
     * @param array multi dimensional array that contains the current definition
     * @return mixed array of changes on success, or a MDB2 error object
     * @access public
     */
    function compareTableDefinitions($table_name, $previous_definition,
        $current_definition, &$defined_tables)
    {
        $changes = array();

        if (is_array($current_definition)) {
            $was_table_name = $table_name;
            if (array_key_exists('was', $current_definition)) {
                $was_table_name = $current_definition['was'];
            }
            if (array_key_exists($was_table_name, $previous_definition)) {
                $changes['change'][$was_table_name] = array();
                if ($was_table_name != $table_name) {
                    $changes['change'][$was_table_name]+= array('name' => $table_name);
                }
                if (array_key_exists($was_table_name, $defined_tables)) {
                    return $this->raiseError(MDB2_SCHEMA_ERROR_INVALID, null, null,
                        'the table "'.$was_table_name.
                        '" was specified as base of more than of table of the database');
                }
                $defined_tables[$was_table_name] = true;
                if (array_key_exists('fields', $current_definition) && is_array($current_definition['fields'])) {
                    $previous_fields = array();
                    if (isset($previous_definition[$was_table_name]['fields'])
                        && is_array($previous_definition[$was_table_name]['fields'])
                    ) {
                        $previous_fields = $previous_definition[$was_table_name]['fields'];
                    }
                    $defined_fields = array();
                    $change = $this->compareTableFieldsDefinitions(
                        $table_name,
                        $previous_fields,
                        $current_definition['fields'],
                        $defined_fields
                    );
                    if (PEAR::isError($change)) {
                        return $change;
                    }
                    if (!empty($change)) {
                        $changes['change'][$was_table_name]+= $change;
                    }
                }
                if (array_key_exists('indexes', $current_definition) && is_array($current_definition['indexes'])) {
                    $previous_indexes = array();
                    if (isset($previous_definition[$was_table_name]['indexes'])
                        && is_array($previous_definition[$was_table_name]['indexes'])
                    ) {
                        $previous_indexes = $previous_definition[$was_table_name]['indexes'];
                    }
                    $defined_indexes = array();
                    $change = $this->compareTableIndexesDefinitions(
                        $table_name,
                        $previous_indexes,
                        $current_definition['indexes'],
                        $defined_indexes
                    );
                    if (PEAR::isError($change)) {
                        return $change;
                    }
                    if (!empty($change)) {
                        if (isset($changes['change'][$was_table_name]['indexes'])) {
                            $changes['change'][$was_table_name]['indexes']+= $change;
                        } else {
                            $changes['change'][$was_table_name]['indexes'] = $change;
                        }
                    }
                }
                if (empty($changes['change'][$was_table_name])) {
                    unset($changes['change'][$was_table_name]);
                }
                if (empty($changes['change'])) {
                    unset($changes['change']);
                }
            } else {
                if ($table_name != $was_table_name) {
                    return $this->raiseError(MDB2_SCHEMA_ERROR_INVALID, null, null,
                        'it was specified a previous table name ("'.$was_table_name.
                        '") for table "'.$table_name.'" that does not exist');
                }
                $changes['add'][$table_name] = true;
            }
        }

        return $changes;
    }

    // }}}
    // {{{ compareSequenceDefinitions()

    /**
     * compare a previous definition with the currenlty parsed definition
     *
     * @param array multi dimensional array that contains the previous definition
     * @param array multi dimensional array that contains the current definition
     * @return mixed array of changes on success, or a MDB2 error object
     * @access public
     */
    function compareSequenceDefinitions($sequence_name, $previous_definition,
        $current_definition, &$defined_sequences)
    {
        $changes = array();

        if (is_array($current_definition)) {
            $was_sequence_name = $sequence_name;
            if (array_key_exists($sequence_name, $previous_definition)
                && isset($previous_definition[$sequence_name]['was'])
                && $previous_definition[$sequence_name]['was'] == $was_sequence_name
            ) {
                $was_sequence_name = $sequence_name;
            } elseif (array_key_exists('was', $current_definition)) {
                $was_sequence_name = $current_definition['was'];
            }
            if (array_key_exists($was_sequence_name, $previous_definition)) {
                if ($was_sequence_name != $sequence_name) {
                    $changes['change'][$was_sequence_name]['name'] = $sequence_name;
                }
                if (array_key_exists($was_sequence_name, $defined_sequences)) {
                    return $this->raiseError(MDB2_SCHEMA_ERROR_INVALID, null, null,
                        'the sequence "'.$was_sequence_name.'" was specified as base'.
                        ' of more than of sequence of the database');
                }
                $defined_sequences[$was_sequence_name] = true;
                $change = array();
                if (array_key_exists('start', $current_definition)
                    && isset($previous_definition[$was_sequence_name]['start'])
                    && $current_definition['start'] != $previous_definition[$was_sequence_name]['start']
                ) {
                    $change['start'] = $previous_definition[$sequence_name]['start'];
                }
                if (isset($current_definition['on']['table'])
                    && isset($previous_definition[$was_sequence_name]['on']['table'])
                    && $current_definition['on']['table'] != $previous_definition[$was_sequence_name]['on']['table']
                    && isset($current_definition['on']['field'])
                    && isset($previous_definition[$was_sequence_name]['on']['field'])
                    && $current_definition['on']['field'] != $previous_definition[$was_sequence_name]['on']['field']
                ) {
                    $change['on'] = $current_definition['on'];
                }
                if (!empty($change)) {
                    $changes['change'][$was_sequence_name][$sequence_name] = $change;
                }
            } else {
                if ($sequence_name != $was_sequence_name) {
                    return $this->raiseError(MDB2_SCHEMA_ERROR_INVALID, null, null,
                        'it was specified a previous sequence name ("'.$was_sequence_name.
                        '") for sequence "'.$sequence_name.'" that does not exist');
                }
                $changes['add'][$sequence_name] = true;
            }
        }
        return $changes;
    }
    // }}}
    // {{{ verifyAlterDatabase()

    /**
     * verify that the changes requested are supported
     *
     * @param array $changes an associative array that contains the definition of
     * the changes that are meant to be applied to the database structure.
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function verifyAlterDatabase($changes)
    {
        if (array_key_exists('tables', $changes) && is_array($changes['tables'])
            && array_key_exists('change', $changes['tables'])
        ) {
            foreach ($changes['tables']['change'] as $table_name => $table) {
                if (array_key_exists('indexes', $table) && is_array($table['indexes'])) {
                    if (!$this->db->supports('indexes')) {
                        return $this->raiseError(MDB2_SCHEMA_ERROR_UNSUPPORTED, null, null,
                            'indexes are not supported');
                    }
                    $table_changes = count($table['indexes']);
                    if (array_key_exists('add', $table['indexes'])) {
                        $table_changes--;
                    }
                    if (array_key_exists('remove', $table['indexes'])) {
                        $table_changes--;
                    }
                    if (array_key_exists('change', $table['indexes'])) {
                        $table_changes--;
                    }
                    if ($table_changes) {
                        return $this->raiseError(MDB2_SCHEMA_ERROR_UNSUPPORTED, null, null,
                            'index alteration not yet supported: '.implode(', ', array_keys($table['indexes'])));
                    }
                }
                unset($table['indexes']);
                $result = $this->db->manager->alterTable($table_name, $table, true);
                if (PEAR::isError($result)) {
                    return $result;
                }
            }
        }
        if (array_key_exists('sequences', $changes) && is_array($changes['sequences'])) {
            if (!$this->db->supports('sequences')) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_UNSUPPORTED, null, null,
                    'sequences are not supported');
            }
            $sequence_changes = count($changes['sequences']);
            if (array_key_exists('add', $changes['sequences'])) {
                $sequence_changes--;
            }
            if (array_key_exists('remove', $changes['sequences'])) {
                $sequence_changes--;
            }
            if (array_key_exists('change', $changes['sequences'])) {
                $sequence_changes--;
            }
            if ($sequence_changes) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_UNSUPPORTED, null, null,
                    'sequence alteration not yet supported: '.implode(', ', array_keys($changes['sequences'])));
            }
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ alterDatabaseIndexes()

    /**
     * Execute the necessary actions to implement the requested changes
     * in the indexes inside a database structure.
     *
     * @param string name of the table
     * @param array $changes an associative array that contains the definition of
     * the changes that are meant to be applied to the database structure.
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function alterDatabaseIndexes($table_name, $changes)
    {
        $alterations = 0;
        if (empty($changes)) {
            return $alterations;
        }

        if (array_key_exists('change', $changes)) {
            foreach ($changes['change'] as $index_name => $index) {
                if (array_key_exists('primary', $index) || array_key_exists('unique', $index)) {
                    $result = $this->db->manager->createConstraint($table_name, $index_name, $index);
                } else {
                    $result = $this->db->manager->createIndex($table_name, $index_name, $index);
                }
                if (PEAR::isError($result)) {
                    return $result;
                }
                $alterations++;
            }
        }
        if (array_key_exists('add', $changes)) {
            foreach ($changes['add'] as $index_name => $index) {
                if (array_key_exists('primary', $index) || array_key_exists('unique', $index)) {
                    $result = $this->db->manager->createConstraint($table_name, $index_name, $index);
                } else {
                    $result = $this->db->manager->createIndex($table_name, $index_name, $index);
                }
                if (PEAR::isError($result)) {
                    return $result;
                }
                $alterations++;
            }
        }
        if (array_key_exists('remove', $changes)) {
            foreach ($changes['remove'] as $index_name => $index) {
                if (array_key_exists('primary', $index) || array_key_exists('unique', $index)) {
                    $result = $this->db->manager->dropConstraint($table_name, $index_name);
                } else {
                    $result = $this->db->manager->dropIndex($table_name, $index_name);
                }
                if (PEAR::isError($result)) {
                    return $result;
                }
                $alterations++;
            }
        }

        return $alterations;
    }

    // }}}
    // {{{ alterDatabaseTables()

    /**
     * Execute the necessary actions to implement the requested changes
     * in the tables inside a database structure.
     *
     * @param array $changes an associative array that contains the definition of
     * the changes that are meant to be applied to the database structure.
     * @param array multi dimensional array that contains the current definition
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function alterDatabaseTables($changes, $current_definition)
    {
        $alterations = 0;
        if (empty($changes)) {
            return $alterations;
        }

        if (array_key_exists('remove', $changes)) {
            foreach ($changes['remove'] as $table_name => $table) {
                $result = $this->db->manager->dropTable($table_name);
                if (PEAR::isError($result)) {
                    return $result;
                }
                $alterations++;
            }
        }

        if (array_key_exists('add', $changes)) {
            foreach ($changes['add'] as $table_name => $table) {
                $result = $this->createTable($table_name,
                    $this->database_definition['tables'][$table_name]);
                if (PEAR::isError($result)) {
                    return $result;
                }
                $alterations++;
            }
        }

        if (array_key_exists('change', $changes)) {
            foreach ($changes['change'] as $table_name => $table) {
                $indexes = array();
                if (array_key_exists('indexes', $table)) {
                    $indexes = $table['indexes'];
                    unset($table['indexes']);
                }
                if (array_key_exists('remove', $indexes) && isset($current_definition[$table_name]['indexes'])) {
                    $result = $this->alterDatabaseIndexes($table_name, array('remove' => $indexes['remove']));
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                    unset($indexes['remove']);
                    $alterations += $result;
                }
                $result = $this->db->manager->alterTable($table_name, $table, false);
                if (PEAR::isError($result)) {
                    return $result;
                }
                $alterations++;
                if (!empty($indexes) && isset($current_definition[$table_name]['indexes'])) {
                    $result = $this->alterDatabaseIndexes($table_name, $indexes);
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                    $alterations += $result;
                }
            }
        }

        return $alterations;
    }

    // }}}
    // {{{ alterDatabaseSequences()

    /**
     * Execute the necessary actions to implement the requested changes
     * in the sequences inside a database structure.
     *
     * @param array $changes an associative array that contains the definition of
     * the changes that are meant to be applied to the database structure.
     * @param array multi dimensional array that contains the current definition
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function alterDatabaseSequences($changes, $current_definition)
    {
        $alterations = 0;
        if (empty($changes)) {
            return $alterations;
        }

        if (array_key_exists('add', $changes)) {
            foreach ($changes['add'] as $sequence_name => $sequence) {
                $result = $this->createSequence($sequence_name,
                    $this->database_definition['sequences'][$sequence_name]);
                if (PEAR::isError($result)) {
                    return $result;
                }
                $alterations++;
            }
        }

        if (array_key_exists('remove', $changes)) {
            foreach ($changes['remove'] as $sequence_name => $sequence) {
                $result = $this->db->manager->dropSequence($sequence_name);
                if (PEAR::isError($result)) {
                    return $result;
                }
                $alterations++;
            }
        }

        if (array_key_exists('change', $changes)) {
            foreach ($changes['change'] as $sequence_name => $sequence) {
                $result = $this->db->manager->dropSequence($current_definition[$sequence_name]['was']);
                if (PEAR::isError($result)) {
                    return $result;
                }
                $result = $this->createSequence($sequence_name, $current_definition[$sequence_name]);
                if (PEAR::isError($result)) {
                    return $result;
                }
                $alterations++;
            }
        }

        return $alterations;
    }

    // }}}
    // {{{ alterDatabase()

    /**
     * Execute the necessary actions to implement the requested changes
     * in a database structure.
     *
     * @param array $changes an associative array that contains the definition of
     * the changes that are meant to be applied to the database structure.
     * @param array multi dimensional array that contains the current definition
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function alterDatabase($changes, $current_definition = null)
    {
        $alterations = 0;
        if (empty($changes)) {
            return $alterations;
        }

        $current_definition = $current_definition
            ? $current_definition : $this->database_definition;

        $result = $this->verifyAlterDatabase($changes);
        if (PEAR::isError($result)) {
            return $result;
        }

        if (array_key_exists('name', $current_definition)) {
            $previous_database_name = $this->db->setDatabase($current_definition['name']);
        }
        if (($support_transactions = $this->db->supports('transactions'))
            && PEAR::isError($result = $this->db->beginTransaction())
        ) {
            return $result;
        }

        if (array_key_exists('tables', $changes) && array_key_exists('tables', $current_definition)) {
            $result = $this->alterDatabaseTables($changes, $current_definition['tables']);
            if (is_numeric($result)) {
                $alterations += $result;
            }
        }
        if (!PEAR::isError($result) && array_key_exists('sequences', $changes) && array_key_exists('sequences', $current_definition)) {
            $result = $this->alterDatabaseSequences($changes, $current_definition['sequences']);
            if (is_numeric($result)) {
                $alterations += $result;
            }
        }

        if (PEAR::isError($result)) {
            if ($support_transactions) {
                $res = $this->db->rollback();
                if (PEAR::isError($res))
                    $result = $this->raiseError(MDB2_SCHEMA_ERROR, null, null,
                        'Could not rollback the partially created database alterations ('.
                        $result->getMessage().' ('.$result->getUserinfo().'))');
            } else {
                $result = $this->raiseError(MDB2_SCHEMA_ERROR, null, null,
                    'the requested database alterations were only partially implemented ('.
                    $result->getMessage().' ('.$result->getUserinfo().'))');
            }
        }
        if ($support_transactions) {
            $result = $this->db->commit();
            if (PEAR::isError($result)) {
                $result = $this->raiseError(MDB2_SCHEMA_ERROR, null, null,
                    'Could not end transaction after successfully implemented the requested database alterations ('.
                    $result->getMessage().' ('.$result->getUserinfo().'))');
            }
        }
        if (isset($previous_database_name)) {
            $this->db->setDatabase($previous_database_name);
        }
        return $result;
    }

    // }}}
    // {{{ dumpDatabaseChanges()

    /**
     * Dump the changes between two database definitions.
     *
     * @param array $changes an associative array that specifies the list
     * of database definitions changes as returned by the _compareDefinitions
     * manager class function.
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function dumpDatabaseChanges($changes)
    {
        if (array_key_exists('tables', $changes)) {
            if (array_key_exists('add', $changes['tables'])) {
                foreach ($changes['tables']['add'] as $table_name => $table) {
                    $this->db->debug("$table_name:");
                    $this->db->debug("\tAdded table '$table_name'");
                }
            }
            if (array_key_exists('remove', $changes['tables'])) {
                foreach ($changes['tables']['remove'] as $table_name => $table) {
                    $this->db->debug("$table_name:");
                    $this->db->debug("\tRemoved table '$table_name'");
                }
            }
            if (array_key_exists('change', $changes['tables'])) {
                foreach ($changes['tables']['change'] as $table_name => $table) {
                    if (array_key_exists('name', $table)) {
                        $this->db->debug("\tRenamed table '$table_name' to '".
                            $table['name']."'");
                    }
                    if (array_key_exists('add', $table)) {
                        foreach ($table['add'] as $field_name => $field) {
                            $this->db->debug("\tAdded field '".$field_name."'");
                        }
                    }
                    if (array_key_exists('remove', $table)) {
                        foreach ($table['remove'] as $field_name => $field) {
                            $this->db->debug("\tRemoved field '".$field_name."'");
                        }
                    }
                    if (array_key_exists('rename', $table)) {
                        foreach ($table['rename'] as $field_name => $field) {
                            $this->db->debug("\tRenamed field '".$field_name."' to '".
                                $field['name']."'");
                        }
                    }
                    if (array_key_exists('change', $table)) {
                        foreach ($table['change'] as $field_name => $field) {
                            if (array_key_exists('type', $field)) {
                                $this->db->debug(
                                    "\tChanged field '$field_name' type to '".
                                        $field['definition']['type']."'");
                            }
                            if (array_key_exists('unsigned', $field)) {
                                $this->db->debug(
                                    "\tChanged field '$field_name' type to '".
                                    (array_key_exists('unsigned', $field['definition']) && $field['definition']['unsigned'] ? '' : 'not ')."unsigned'");
                            }
                            if (array_key_exists('length', $field)) {
                                $this->db->debug(
                                    "\tChanged field '$field_name' length to '".
                                    ((!array_key_exists('length', $field['definition']) || $field['definition']['length'] == 0)
                                        ? 'no length' : $field['definition']['length'])."'");
                            }
                            if (array_key_exists('default', $field)) {
                                $this->db->debug(
                                    "\tChanged field '$field_name' default to ".
                                    (array_key_exists('default', $field['definition']) ? "'".$field['definition']['default']."'" : 'NULL'));
                            }
                            if (array_key_exists('notnull', $field)) {
                                $this->db->debug(
                                   "\tChanged field '$field_name' notnull to ".
                                    (array_key_exists('notnull', $field['definition']) && $field['definition']['notnull'] ? 'true' : 'false')
                                );
                            }
                        }
                    }
                    if (array_key_exists('indexes', $table)) {
                        if (array_key_exists('add', $table['indexes'])) {
                            foreach ($table['indexes']['add'] as $index_name => $index) {
                                $this->db->debug("\tAdded index '".$index_name.
                                    "' of table '$table_name'");
                            }
                        }
                        if (array_key_exists('remove', $table['indexes'])) {
                            foreach ($table['indexes']['remove'] as $index_name => $index) {
                                $this->db->debug("\tRemoved index '".$index_name.
                                    "' of table '$table_name'");
                            }
                        }
                        if (array_key_exists('change', $table['indexes'])) {
                            foreach ($table['indexes']['change'] as $index_name => $index) {
                                if (array_key_exists('name', $index)) {
                                    $this->db->debug(
                                        "\tRenamed index '".$index_name."' to '".$index['name'].
                                        "' on table '$table_name'");
                                }
                                if (array_key_exists('unique', $index)) {
                                    $this->db->debug(
                                        "\tChanged index '".$index_name."' unique to '".
                                        array_key_exists('unique', $index)."' on table '$table_name'");
                                }
                                if (array_key_exists('change', $index)) {
                                    $this->db->debug("\tChanged index '".$index_name.
                                        "' on table '$table_name'");
                                }
                            }
                        }
                    }
                }
            }
        }
        if (array_key_exists('sequences', $changes)) {
            if (array_key_exists('add', $changes['sequences'])) {
                foreach ($changes['sequences']['add'] as $sequence_name => $sequence) {
                    $this->db->debug("$sequence_name:");
                    $this->db->debug("\tAdded sequence '$sequence_name'");
                }
            }
            if (array_key_exists('remove', $changes['sequences'])) {
                foreach ($changes['sequences']['remove'] as $sequence_name => $sequence) {
                    $this->db->debug("$sequence_name:");
                    $this->db->debug("\tAdded sequence '$sequence_name'");
                }
            }
            if (array_key_exists('change', $changes['sequences'])) {
                foreach ($changes['sequences']['change'] as $sequence_name => $sequence) {
                    if (array_key_exists('name', $sequence)) {
                        $this->db->debug(
                            "\tRenamed sequence '$sequence_name' to '".
                            $sequence['name']."'");
                    }
                    if (array_key_exists('change', $sequence)) {
                        foreach ($sequence['change'] as $sequence_name => $sequence) {
                            if (array_key_exists('start', $sequence)) {
                                $this->db->debug(
                                    "\tChanged sequence '$sequence_name' start to '".
                                    $sequence['start']."'");
                            }
                        }
                    }
                }
            }
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ dumpDatabase()

    /**
     * Dump a previously parsed database structure in the Metabase schema
     * XML based format suitable for the Metabase parser. This function
     * may optionally dump the database definition with initialization
     * commands that specify the data that is currently present in the tables.
     *
     * @param array $arguments an associative array that takes pairs of tag
     * names and values that define dump options.
     *                 array (
     *                     'definition'    =>    Boolean
     *                         true   :  dump currently parsed definition
     *                         default:  dump currently connected database
     *                     'output_mode'    =>    String
     *                         'file' :   dump into a file
     *                         default:   dump using a function
     *                     'output'        =>    String
     *                         depending on the 'Output_Mode'
     *                                  name of the file
     *                                  name of the function
     *                     'end_of_line'        =>    String
     *                         end of line delimiter that should be used
     *                         default: "\n"
     *                 );
     * @param integer $dump constant that determines what data to dump
     *                      MDB2_SCHEMA_DUMP_ALL       : the entire db
     *                      MDB2_SCHEMA_DUMP_STRUCTURE : only the structure of the db
     *                      MDB2_SCHEMA_DUMP_CONTENT   : only the content of the db
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function dumpDatabase($arguments, $dump = MDB2_SCHEMA_DUMP_ALL)
    {
        if (!array_key_exists('definition', $arguments) || !$arguments['definition']) {
            if (!$this->db) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_NODBSELECTED,
                    null, null, 'please connect to a RDBMS first');
            }
            $error = $this->getDefinitionFromDatabase();
            if (PEAR::isError($error)) {
                return $error;
            }

            // get initialization data
            if (isset($this->database_definition['tables']) && is_array($this->database_definition['tables'])
                && $dump == MDB2_SCHEMA_DUMP_ALL || $dump == MDB2_SCHEMA_DUMP_CONTENT
            ) {
                foreach ($this->database_definition['tables'] as $table_name => $table) {
                    $fields = array();
                    $types = array();
                    foreach ($table['fields'] as $field_name => $field) {
                        $fields[$field_name] = $field['type'];
                    }
                    $query = 'SELECT '.implode(', ', array_keys($fields)).' FROM '.$table_name;
                    $data = $this->db->queryAll($query, $types, MDB2_FETCHMODE_ASSOC);
                    if (PEAR::isError($data)) {
                        return $data;
                    }
                    if (!empty($data)) {
                        $initialization = array();
                        foreach ($data as $row) {
                            foreach($row as $key => $lob) {
                                if (is_numeric($lob) && array_key_exists($key, $fields)
                                    && ($fields[$key] == 'clob' || $fields[$key] == 'blob')
                                ) {
                                    $value = '';
                                    while (!$this->db->datatype->endOfLOB($lob)) {
                                        $this->db->datatype->readLOB($lob, $data, 8192);
                                        $value .= $data;
                                    }
                                    $row[$key] = $value;
                                }
                            }
                            $initialization[] = array('type' => 'insert', 'fields' => $row);
                        }
                        $this->database_definition['tables'][$table_name]['initialization'] = $initialization;
                    }
                }
            }
        }

        require_once 'MDB2/Schema/Writer.php';
        $writer =& new MDB2_Schema_Writer();
        return $writer->dumpDatabase($this->database_definition, $arguments, $dump);
    }

    // }}}
    // {{{ writeInitialization()

    /**
     * write initialization and sequences
     *
     * @param string $data_file
     * @param string $structure_file
     * @param array $variables an associative array that is passed to the argument
     * of the same name to the parseDatabaseDefinitionFile function. (there third
     * param)
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function writeInitialization($data_file, $structure_file = false, $variables = array())
    {
        $structure = false;
        if ($structure_file) {
            $structure = $this->parseDatabaseDefinitionFile(
                $structure_file,
                $variables
            );
            if (PEAR::isError($structure)) {
                return $structure;
            }
        }

        $data = $this->parseDatabaseDefinitionFile(
            $data_file,
            $variables,
            false,
            $structure
        );
        if (PEAR::isError($data)) {
            return $data;
        }

        $previous_database_name = null;
        if (array_key_exists('name', $data)) {
            $previous_database_name = $this->db->setDatabase($data['name']);
        } elseif(array_key_exists('name', $structure)) {
            $previous_database_name = $this->db->setDatabase($structure['name']);
        }

        if (array_key_exists('tables', $data) && is_array($data['tables'])) {
            foreach ($data['tables'] as $table_name => $table) {
                if (!array_key_exists('initialization', $table)) {
                    continue;
                }
                $result = $this->initializeTable($table_name, $table);
                if (PEAR::isError($result)) {
                    return $result;
                }
            }
        }

        if (array_key_exists('sequences', $structure) && is_array($structure['sequences'])) {
            foreach ($structure['sequences'] as $sequence_name => $sequence) {
                if (isset($data['sequences'][$sequence_name])
                    || !isset($sequence['on']['table'])
                    || !isset($data['tables'][$sequence['on']['table']])
                ) {
                    continue;
                }
                $result = $this->createSequence($sequence_name, $sequence, true);
                if (PEAR::isError($result)) {
                    return $result;
                }
            }
        }
        if (array_key_exists('sequences', $data) && is_array($data['sequences'])) {
            foreach ($data['sequences'] as $sequence_name => $sequence) {
                $result = $this->createSequence($sequence_name, $sequence, true);
                if (PEAR::isError($result)) {
                    return $result;
                }
            }
        }

        if (isset($previous_database_name)) {
            $this->db->setDatabase($previous_database_name);
        }

        return MDB2_OK;
    }

    // }}}
    // {{{ updateDatabase()

    /**
     * Compare the correspondent files of two versions of a database schema
     * definition: the previously installed and the one that defines the schema
     * that is meant to update the database.
     * If the specified previous definition file does not exist, this function
     * will create the database from the definition specified in the current
     * schema file.
     * If both files exist, the function assumes that the database was previously
     * installed based on the previous schema file and will update it by just
     * applying the changes.
     * If this function succeeds, the contents of the current schema file are
     * copied to replace the previous schema file contents. Any subsequent schema
     * changes should only be done on the file specified by the $current_schema_file
     * to let this function make a consistent evaluation of the exact changes that
     * need to be applied.
     *
     * @param mixed $current_schema filename or array of the updated database
     * schema definition.
     * @param mixed $previous_schema filename or array of the previously installed
     * database schema definition.
     * @param array $variables an associative array that is passed to the argument
     * of the same name to the parseDatabaseDefinitionFile function. (there third
     * param)
     * @param bool $disable_query determines if the disable_query option should
     * be set to true for the alterDatabase() or createDatabase() call
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function updateDatabase($current_schema, $previous_schema = false
        , $variables = array(), $disable_query = false)
    {
        if (is_string($current_schema)) {
            $database_definition = $this->parseDatabaseDefinitionFile(
                $current_schema,
                $variables,
                $this->options['fail_on_invalid_names']
            );
            if (PEAR::isError($database_definition)) {
                return $database_definition;
            }
        } elseif (is_array($current_schema)) {
            $database_definition = $current_schema;
        } else {
            return $this->raiseError(MDB2_SCHEMA_ERROR, null, null,
                'invalid data type of current_schema');
        }

        $this->database_definition = $database_definition;
        if ($previous_schema) {
            $errorcodes = array(MDB2_ERROR_UNSUPPORTED, MDB2_ERROR_NOT_CAPABLE);
            $this->db->expectError($errorcodes);
            $databases = $this->db->manager->listDatabases();
            $this->db->popExpect();
            if (PEAR::isError($databases)) {
                if (!MDB2::isError($databases, $errorcodes)) {
                    return $databases;
                }
            } elseif (!is_array($databases) ||
                !in_array($this->database_definition['name'], $databases)
            ) {
                return $this->raiseError(MDB2_SCHEMA_ERROR, null, null,
                    'database to update does not exist: '.$this->database_definition['name']);
            }

            if (is_string($previous_schema)) {
                $previous_definition = $this->parseDatabaseDefinitionFile(
                    $previous_schema, $variables, false);
                if (PEAR::isError($previous_definition)) {
                    return $previous_definition;
                }
            } elseif (is_array($previous_schema)) {
                $previous_definition = $previous_schema;
            } else {
                return $this->raiseError(MDB2_SCHEMA_ERROR, null, null,
                    'invalid data type of previous_schema');
            }

            $changes = $this->compareDefinitions($previous_definition);
            if (PEAR::isError($changes)) {
                return $changes;
            }

            if (is_array($changes)) {
                $this->db->setOption('disable_query', $disable_query);
                $result = $this->alterDatabase($changes, $previous_definition);
                $this->db->setOption('disable_query', false);
                if (PEAR::isError($result)) {
                    return $result;
                }
                $copy = true;
                if ($this->db->options['debug']) {
                    $result = $this->dumpDatabaseChanges($changes);
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                }
            }
        } else {
            $this->db->setOption('disable_query', $disable_query);
            $result = $this->createDatabase();
            $this->db->setOption('disable_query', false);
            if (PEAR::isError($result)) {
                return $result;
            }
        }

        if (is_string($previous_schema) && is_string($current_schema)
            && !copy($current_schema, $previous_schema)
        ) {
            return $this->raiseError(MDB2_SCHEMA_ERROR, null, null,
                'Could not copy the new database definition file to the current file');
        }

        return MDB2_OK;
    }

    // }}}
    // {{{ errorMessage()

    /**
     * Return a textual error message for a MDB2_Schema error code
     *
     * @param   int     $value error code
     * @return  string  error message, or false if the error code was
     *                  not recognized
     * @access public
     */
    function errorMessage($value)
    {
        static $errorMessages;
        if (!isset($errorMessages)) {
            $errorMessages = array(
                MDB2_SCHEMA_ERROR              => 'unknown error',
                MDB2_SCHEMA_ERROR_PARSE        => 'schema parse error',
                MDB2_SCHEMA_ERROR_INVALID      => 'invalid',
                MDB2_SCHEMA_ERROR_UNSUPPORTED  => 'not supported',
                MDB2_SCHEMA_ERROR_NOT_CAPABLE  => 'not capable',
                MDB2_SCHEMA_ERROR_NODBSELECTED => 'no database selected',
            );
        }

        if (PEAR::isError($value)) {
            $value = $value->getCode();
        }

        return array_key_exists($value, $errorMessages) ?
           $errorMessages[$value] : $errorMessages[MDB2_SCHEMA_ERROR];
    }

    // }}}
    // {{{ raiseError()

    /**
     * This method is used to communicate an error and invoke error
     * callbacks etc.  Basically a wrapper for PEAR::raiseError
     * without the message string.
     *
     * @param mixed    integer error code
     *
     * @param int      error mode, see PEAR_Error docs
     *
     * @param mixed    If error mode is PEAR_ERROR_TRIGGER, this is the
     *                 error level (E_USER_NOTICE etc).  If error mode is
     *                 PEAR_ERROR_CALLBACK, this is the callback function,
     *                 either as a function name, or as an array of an
     *                 object and method name.  For other error modes this
     *                 parameter is ignored.
     *
     * @param string   Extra debug information.  Defaults to the last
     *                 query and native error code.
     *
     * @return object  a PEAR error object
     *
     * @see PEAR_Error
     */
    function &raiseError($code = null, $mode = null, $options = null, $userinfo = null)
    {
        $err =& PEAR::raiseError(null, $code, $mode, $options, $userinfo, 'MDB2_Schema_Error', true);
        return $err;
    }

    // }}}
    // {{{ isError()

    /**
     * Tell whether a value is a MDB2_Schema error.
     *
     * @param   mixed $data   the value to test
     * @param   int   $code   if $data is an error object, return true
     *                        only if $code is a string and
     *                        $db->getMessage() == $code or
     *                        $code is an integer and $db->getCode() == $code
     * @access  public
     * @return  bool    true if parameter is an error
     */
    function isError($data, $code = null)
    {
        if (is_a($data, 'MDB2_Schema_Error')) {
            if (is_null($code)) {
                return true;
            } elseif (is_string($code)) {
                return $data->getMessage() === $code;
            } else {
                $code = (array)$code;
                return in_array($data->getCode(), $code);
            }
        }
        return false;
    }

    // }}}
}

/**
 * MDB2_Schema_Error implements a class for reporting portable database error
 * messages.
 *
 * @package MDB2_Schema
 * @category Database
 * @author  Stig Bakken <ssb@fast.no>
 */
class MDB2_Schema_Error extends PEAR_Error
{
    /**
     * MDB2_Schema_Error constructor.
     *
     * @param mixed   $code      MDB error code, or string with error message.
     * @param integer $mode      what 'error mode' to operate in
     * @param integer $level     what error level to use for
     *                           $mode & PEAR_ERROR_TRIGGER
     * @param smixed  $debuginfo additional debug info, such as the last query
     */
    function MDB2_Schema_Error($code = MDB2_SCHEMA_ERROR, $mode = PEAR_ERROR_RETURN,
              $level = E_USER_NOTICE, $debuginfo = null)
    {
        $this->PEAR_Error('MDB2_Schema Error: ' . MDB2_Schema::errorMessage($code), $code,
            $mode, $level, $debuginfo);
    }
}
?>