<?php
if (strpos($argv[0], basename(__FILE__)) > 0 && $argv[1]) {

require_once("inc/bx/init.php");
bx_init::start('conf/config.xml', "");

db2forms($argv[1]);


}
function db2forms($table = "") {
    if (!$table) {
        return ;
    }
    $dsn = $GLOBALS['POOL']->config->dsn;
    require_once 'MDB2.php';
    MDB2::loadFile('Schema');
    $manager = new MDB2_Schema();
    
    $err = $manager->connect($dsn, array('debug' => false, 'log_line_break' => '\n'));
    if (MDB2::isError($err)) {
        print $err->getMessage();
        print "\n";
    } else {
        $GLOBALS['db2forms_output'] = "";
        $dump_what = MDB2_SCHEMA_DUMP_STRUCTURE;
        $dump_config = array(
        'output' => 'db2forms_output'
        );
        $err = $manager->dumpDatabase($dump_config, $dump_what);
        if (MDB2::isError($err)) {
            //bx_helpers_debug::webdump($err);
            print  $err->getMessage();;
            print "\n";
            print  $err->getUserInfo();;
            print "\n";
        }
    }
    if ($GLOBALS['db2forms_output']) {
    $xsl = new xsltprocessor();
    
    $xsl->importStylesheet(domdocument::load(BX_PROJECT_DIR."inc/bx/tools/generate/schema2forms.xsl"));
    
    $xsl->setParameter("","table",$GLOBALS['POOL']->config->getTablePrefix().$table);
    $xsl->setParameter("","alias",$table);
    $dom = $xsl->transformToDoc(domdocument::loadXML( $GLOBALS['db2forms_output']));
    
    $dom->formatOutput = 1;
    
    $dom->save(BX_PROJECT_DIR."/dbforms2/$table.xml");
    print "done in ".BX_PROJECT_DIR."/dbforms2/$table.xml\n";
    } else {
        print "No Output produced by MDB2\n";
    }
    
}

function db2forms_output($s) {
    $GLOBALS['db2forms_output'] .= $s;
}
