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
// $Id: Writer.php,v 1.18 2005/10/10 08:36:28 lsmith Exp $
//

/**
 * Writes an XML schema file
 *
 * @package MDB2_Schema
 * @category Database
 * @access protected
 * @author  Lukas Smith <smith@pooteeweet.org>
 */
class MDB2_Schema_Writer
{
    // }}}
    // {{{ raiseError()

    /**
     * This method is used to communicate an error and invoke error
     * callbacks etc.  Basically a wrapper for PEAR::raiseError
     * without the message string.
     *
     * @param mixed $code integer error code, or a PEAR error object (all
     *      other parameters are ignored if this parameter is an object
     * @param int $mode error mode, see PEAR_Error docs
     * @param mixed $options If error mode is PEAR_ERROR_TRIGGER, this is the
     *      error level (E_USER_NOTICE etc).  If error mode is
     *      PEAR_ERROR_CALLBACK, this is the callback function, either as a
     *      function name, or as an array of an object and method name. For
     *      other error modes this parameter is ignored.
     * @param string $userinfo Extra debug information.  Defaults to the last
     *      query and native error code.
     * @param mixed $nativecode Native error code, integer or string depending
     *      the backend.
     * @return object a PEAR error object
     * @access public
     * @see PEAR_Error
     */
    function &raiseError($code = null, $mode = null, $options = null, $userinfo = null)
    {
        $error =& MDB2_Schema::raiseError($code, $mode, $options, $userinfo);
        return $error;
    }

    // }}}
    // {{{ _escapeSpecialChars()

    /**
     * add escapecharacters to all special characters in a string
     *
     * @param string $string string that should be escaped
     * @return string escaped string
     * @access protected
     */
    function _escapeSpecialChars($string)
    {
        if (!is_string($string)) {
            $string = strval($string);
        }

        $escaped = '';
        for ($char = 0, $count = strlen($string); $char < $count; $char++) {
            switch ($string[$char]) {
            case '&':
                $escaped .= '&amp;';
                break;
            case '>':
                $escaped .= '&gt;';
                break;
            case '<':
                $escaped .= '&lt;';
                break;
            case '"':
                $escaped .= '&quot;';
                break;
            case '\'':
                $escaped .= '&apos;';
                break;
            default:
                $code = ord($string[$char]);
                if ($code < 32 || $code > 127) {
                    $escaped .= "&#$code;";
                } else {
                    $escaped .= $string[$char];
                }
                break;
            }
        }
        return $escaped;
    }

    // }}}
    // {{{ dumpSequence()

    /**
     * dump the structure of a sequence
     *
     * @param string  $sequence_name
     * @param string  $eol
     * @return mixed string with xml seqeunce definition on success, or a MDB2 error object
     * @access public
     */
    function dumpSequence($sequence_definition, $sequence_name, $eol, $dump = MDB2_SCHEMA_DUMP_ALL)
    {
        $buffer = "$eol <sequence>$eol  <name>$sequence_name</name>$eol";
        if ($dump == MDB2_SCHEMA_DUMP_ALL || $dump == MDB2_SCHEMA_DUMP_CONTENT) {
            if (array_key_exists('start', $sequence_definition)) {
                $start = $sequence_definition['start'];
                $buffer .= "  <start>$start</start>$eol";
            }
        }

        if (array_key_exists('on', $sequence_definition)) {
            $buffer .= "  <on>$eol";
            $buffer .= "   <table>".$sequence_definition['on']['table'].
                "</table>$eol   <field>".$sequence_definition['on']['field'].
                "</field>$eol  </on>$eol";
        }
        $buffer .= " </sequence>$eol";

        return $buffer;
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
    function dumpDatabase($database_definition, $arguments, $dump = MDB2_SCHEMA_DUMP_ALL)
    {
        if (array_key_exists('definition', $arguments) && $arguments['definition']) {
            $database_definition = $arguments['definition'];
        }

        if (array_key_exists('output', $arguments)) {
            if (array_key_exists('output_mode', $arguments) && $arguments['output_mode'] == 'file') {
                $fp = fopen($arguments['output'], 'w');
                $output = false;
            } elseif (function_exists($arguments['output'])) {
                $output = $arguments['output'];
            } else {
                return $this->raiseError(MDB2_SCHEMA_ERROR, null, null,
                    'no valid output function specified');
            }
        } else {
            return $this->raiseError(MDB2_SCHEMA_ERROR, null, null,
                'no output method specified');
        }

        if (array_key_exists('end_of_line', $arguments)) {
            $eol = $arguments['end_of_line'];
        } else {
            $eol = "\n";
        }

        $sequences = array();
        if (array_key_exists('sequences', $database_definition)
            && is_array($database_definition['sequences'])
        ) {
            foreach ($database_definition['sequences'] as $sequence_name => $sequence) {
                if (array_key_exists('on', $sequence)) {
                    $table = $sequence['on']['table'];
                } else {
                    $table = '';
                }
                $sequences[$table][] = $sequence_name;
            }
        }

        $buffer = ('<?xml version="1.0" encoding="ISO-8859-1" ?>'.$eol);
        $buffer .= ("<database>$eol$eol <name>".$database_definition['name']."</name>$eol <create>"
            .$database_definition['create']."</create>$eol");

        if ($output) {
            $output($buffer);
        } else {
            fwrite($fp, $buffer);
        }

        $buffer = '';
        if (array_key_exists('tables', $database_definition) && is_array($database_definition['tables'])) {
            foreach ($database_definition['tables'] as $table_name => $table) {
                $buffer = ("$eol <table>$eol$eol  <name>$table_name</name>$eol");
                if ($dump == MDB2_SCHEMA_DUMP_ALL || $dump == MDB2_SCHEMA_DUMP_STRUCTURE) {
                    $buffer .= ("$eol  <declaration>$eol");
                    if (array_key_exists('fields', $table) && is_array($table['fields'])) {
                        foreach ($table['fields'] as $field_name => $field) {
                            if (!array_key_exists('type', $field)) {
                                return $this->raiseError(MDB2_SCHEMA_ERROR, null, null,
                                    'it was not specified the type of the field "'.
                                    $field_name.'" of the table "'.$table_name);
                            }
                            $buffer .=("$eol   <field>$eol    <name>$field_name</name>$eol    <type>"
                                .$field['type']."</type>$eol");
                            switch ($field['type']) {
                            case 'integer':
                                if (array_key_exists('unsigned', $field)) {
                                    $buffer .=("    <unsigned>true</unsigned>$eol");
                                }
                                break;
                            case 'text':
                            case 'clob':
                            case 'blob':
                                if (array_key_exists('length', $field)) {
                                    $buffer .=('    <length>'.$field['length']."</length>$eol");
                                }
                                break;
                            case 'boolean':
                            case 'date':
                            case 'timestamp':
                            case 'time':
                            case 'float':
                            case 'decimal':
                                break;
                            default:
                                return $this->raiseError('type "'.$field['type'].
                                    '" is not yet supported');
                            }
                            if (array_key_exists('notnull', $field) && $field['notnull']) {
                                $buffer .=("    <notnull>true</notnull>$eol");
                            } else {
                                $buffer .=("    <notnull>false</notnull>$eol");
                            }
                            if (array_key_exists('default', $field)
                                && $field['type'] != 'clob' && $field['type'] != 'blob'
                            ) {
                                $buffer .=('    <default>'.$this->_escapeSpecialChars($field['default'])
                                    ."</default>$eol");
                            }

                            if (array_key_exists('autoincrement', $field)) {
                                $buffer .= "    <autoincrement>" . $field['autoincrement'] .
                                    "</autoincrement>$eol";
                            }

                            $buffer .=("   </field>$eol");
                        }
                    }

                    if (array_key_exists('indexes', $table) && is_array($table['indexes'])) {
                        foreach ($table['indexes'] as $index_name => $index) {
                            $buffer .=("$eol   <index>$eol    <name>$index_name</name>$eol");
                            if (array_key_exists('unique', $index)) {
                                $buffer .=("    <unique>true</unique>$eol");
                            }

                            if (array_key_exists('primary', $index)) {
                                $buffer .=("    <primary>true</primary>$eol");
                            }

                            foreach ($index['fields'] as $field_name => $field) {
                                $buffer .=("    <field>$eol     <name>$field_name</name>$eol");
                                if (is_array($field) && array_key_exists('sorting', $field)) {
                                    $buffer .=('     <sorting>'.$field['sorting']."</sorting>$eol");
                                }
                                $buffer .=("    </field>$eol");
                            }
                            $buffer .=("   </index>$eol");
                        }
                    }
                    $buffer .= ("$eol  </declaration>$eol");
                }

                if ($output) {
                    $output($buffer);
                } else {
                    fwrite($fp, $buffer);
                }

                $buffer = '';
                if ($dump == MDB2_SCHEMA_DUMP_ALL || $dump == MDB2_SCHEMA_DUMP_CONTENT) {
                    if (array_key_exists('initialization', $table) && !empty($table['initialization']) && is_array($table['initialization'])) {
                        $buffer = ("$eol  <initialization>$eol");
                        foreach ($table['initialization'] as $instruction) {
                            switch ($instruction['type']) {
                            case 'insert':
                                $buffer .= ("$eol   <insert>$eol");
                                foreach ($instruction['fields'] as $field_name => $field) {
                                    $buffer .= ("$eol    <field>$eol     <name>$field_name</name>$eol     <value>"
                                        .$this->_escapeSpecialChars($field)."</value>$eol   </field>$eol");
                                }
                                $buffer .= ("$eol   </insert>$eol");
                                break;
                            }
                        }
                        $buffer .= ("$eol  </initialization>$eol");
                    }
                }
                $buffer .= ("$eol </table>$eol");
                if ($output) {
                    $output($buffer);
                } else {
                    fwrite($fp, $buffer);
                }

                if (isset($sequences[$table_name])) {
                    foreach ($sequences[$table_name] as $sequence) {
                        $result = $this->dumpSequence(
                            $database_definition['sequences'][$sequence],
                            $sequence,
                            $eol,
                            $dump
                        );
                        if (PEAR::isError($result)) {
                            return $result;
                        }

                        if ($output) {
                            $output($result);
                        } else {
                            fwrite($fp, $result);
                        }
                    }
                }
            }
        }

        if (isset($sequences[''])) {
            foreach ($sequences[''] as $sequence) {
                $result = $this->dumpSequence(
                    $database_definition['sequences'][$sequence],
                    $sequence,
                    $eol,
                    $dump
                );
                if (PEAR::isError($result)) {
                    return $result;
                }

                if ($output) {
                    $output($result);
                } else {
                    fwrite($fp, $result);
                }
            }
        }

        $buffer = ("$eol</database>$eol");
        if ($output) {
            $output($buffer);
        } else {
            fwrite($fp, $buffer);
            fclose($fp);
        }

        return MDB2_OK;
    }
}
?>