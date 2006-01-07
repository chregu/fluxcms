<?php

function db2forms($table = "") {
    if (!$table) {
        return ;
    }
    $dsn = $GLOBALS['POOL']->config->dsn;
    require_once 'MDB2.php';
    MDB2::loadFile('Tools/Manager');
    $manager = new MDB2_Tools_Manager();
    $err = $manager->connect($dsn, array('debug' => false, 'log_line_break' => '\n'));
    if (MDB2::isError($err)) {
        $error = $err->getMessage();
        
    } else {
        $GLOBALS['db2forms_output'] = "";
        $dump_what = MDB2_MANAGER_DUMP_STRUCTURE;
        $dump_config = array(
        'output' => 'db2forms_output'
        );
         $manager->dumpDatabase($dump_config, $dump_what);
    }
    
    $xsl = new xsltprocessor();
    
    $xsl->importStylesheet(domdocument::load(BX_PROJECT_DIR."inc/bx/tools/generate/schema2forms.xsl"));
    
    $xsl->setParameter("","table",$GLOBALS['POOL']->config->getTablePrefix().$table);
    $xsl->setParameter("","alias",$table);
    $dom = $xsl->transformToDoc(domdocument::loadXML( $GLOBALS['db2forms_output']));
    
    $dom->formatOutput = 1;
    
    $dom->save(BX_PROJECT_DIR."/dbforms2/$table.xml");
    
}

function db2forms_output($s) {
    $GLOBALS['db2forms_output'] .= $s;
}
