<?php
class testhelper_coverage_HTML extends PHPUnit2_Util_CodeCoverage_Renderer_HTML {
    
     public function factory($rendererName, $codeCoverageInformation) {
        return new self($codeCoverageInformation);
    }
    
    protected function getSummary() {
        foreach ($this->codeCoverageInformation as $testCaseName => $sourceFiles) {
            foreach ($sourceFiles as $sourceFile => $executedLines) {
                
                $good = true;
                //only include files from inc/bx
                if (strpos($sourceFile,"inc/bx/") === false) {
                    $good = false;
                // but not from tests/
                } else if (strpos($sourceFile,"inc/bx/tests") !== false) {
                    $good = false;
                // and also not autoload
                } else if (strpos($sourceFile,"inc/bx/autoload.php") !== false) {
                    $good = false;
                }
                
                if (!$good) {
                    unset($this->codeCoverageInformation[$testCaseName][$sourceFile]);
                }
                
            }
         
        }
       return  parent::getSummary();
    }
}



?>
