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

        return $psuite->run($preporter);
    }
}
?>
