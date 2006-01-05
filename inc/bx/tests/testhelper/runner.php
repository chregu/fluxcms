<?php
class testhelper_runner {

    public static function main($preporter, $acaselist = false) {

        if(!$acaselist) {
            $testhelper = new testhelper_scanner();

            $acaselist = $testhelper->getTestCaseList();
        }

        $psuite = &new GroupTest('FluxCMSSuite');

        for($i=0;$i<count($acaselist);$i++)
            $psuite->addTestFile($acaselist[$i]);
            if (function_exists("xdebug_start_code_coverage")) {
                xdebug_start_code_coverage();
            }
            $ret = $psuite->run($preporter);
            if (function_exists("xdebug_start_code_coverage")) {
                $cc =  PHPUnit2_Util_CodeCoverage_Renderer::factory('HTML',array('tests' => xdebug_get_code_coverage()));
                $cc->renderToFile('cov.html');
            }

        return $ret;
    }
}
?>
