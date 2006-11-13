<?php

class bx_dbreports_report {

    protected $xml;
    public $name = '';
    
    public function __construct($name) {
        $this->xml = simplexml_load_file(BX_PROJECT_DIR."dbreports/{$name}.xml");
    }

    public function getSections() {
        return $this->xml->section;
    }

    public function getQueryBySection($section) {
        
        $tp = $GLOBALS['POOL']->config->getTablePrefix();
        
        $leftjoins = array();
        $fields = array();

        // main table
        $fields[(string)$section->datasource->table['name']] = explode(',', $section->datasource->table['fields']);
        
        foreach($section->datasource->table->leftjoin as $lj) {
            $fields[(string) $lj['name']] = explode(',', $lj['fields']);
            $leftjoins[(string)$lj['name']] = (string)$lj['on'];
        }
        
        $qleftjoins = '';
        foreach($leftjoins as $tn => $lj) {
            $qleftjoins.= " LEFT JOIN $tp$tn ON (".$this->expandSqlNames($lj).")";
        }
        
        $gtable = (string)$section->group['table'];
        $gfield = (string)$section->group['field'];
        $qgroup = "GROUP BY {$tp}{$gtable}.{$gfield}";
        $fields[$gtable][] = $gfield;
        if((string)$section->group['additional'] != '') {
            $qgroup .= ",$tp".(string)$section->group['additional'];
        }

        if(isset($section->group->aggregate)) {
            foreach($section->group->aggregate as $aggregate) {
                $table = (string)$aggregate['table'];
                $field = (string)$aggregate['field'];
                switch((string)$aggregate['type']) {
                    case 'sum': 
                        $fields['_aggregate'][] = "SUM({$tp}{$table}.{$field}) AS {$table}_{$field}";
                    break;
                }
            }
        }

        $qfields = '';
        foreach($fields as $tn => $table) {
            foreach($table as $field) {
                if($tn == '_aggregate') {
                    $qfields .= "{$field},";
                }else if($tn != '' && $field != '') {
                    $qfields .= "{$tp}{$tn}.{$field} AS {$tn}_{$field},";
                }
            }
        }
        $qfields = substr($qfields, 0, -1);
        

        $query = "SELECT {$qfields} ";
        $query .= "FROM $tp".(string)$section->datasource->table['name']."";
        $query .= $qleftjoins;
        $query .= "$qgroup";

        
        return $query;
        
    }
    
    protected function expandSqlNames($sql) {
        $tp = $GLOBALS['POOL']->config->getTablePrefix();
        return preg_replace('/(\w+)\.(\w+)/i', $tp.'$1.$2', $sql);
    }
    
    protected function sanitizeSqlNames($sql) {
        return preg_replace('/(\w+)\.(\w+)/i', '$1_$2', $sql);
    }

}


