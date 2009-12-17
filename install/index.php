<?php
session_start();
?>
<html>
<head>
<title>Flux CMS Webinstaller</title>
<!-- <link rel="stylesheet" href="../_hosts/live/themes/standard/admin/css/formedit.css" type="text/css" /> -->

 <link rel="stylesheet" href="../themes/standard/admin/css/formedit.css" type="text/css" />
</head>

<body>
<h1 class="page">
Flux CMS Webinstaller
</h1>



<?php
if (file_exists("../conf/config.inc.php")) {
    print "<font color='red'>It looks like you upgraded Flux CMS, but didn't convert your
    config.inc.php to the new config.xml format.<p/>
    
    You can either reinstall Flux CMS (OVERWRITEs all your data) <br/>or copy config.xml from 
    install/dist/conf/config.xml to conf/config.xml and adjust it. <br/>
    You should also delete the old conf/config.inc.php file.
    </font>";
    die();  
} else if (file_exists("../conf/config.xml")) {
    print "<font color='red'>config.xml exists, therefore the installation routine is locked. <br/>
    Please delete the file 'conf/config.xml', if you want to reinstall the CMS.</font>";
    die();
} 

if (file_exists("locked") ) {
 print "<font color='red'>The installation routine is locked. Please delete the file 'locked' in the install directory.</font>";
die();
}

if (!isset($_SESSION["step"]) || $_SESSION["step"] < 2) {
    ?>
    Welcome to the Flux CMS Installation Routine<p/>
    First, we do some checks, if your system has, what Flux CMS needs<br/>
    <h2 class="page">
    Prerequisites Checks
    </h2>
    <font color='blue'>
    <?php
    if (true !== $err = prereq()) {
        print "Flux CMS cannot be installed due to:<br/> $err";
        die();
    } else {
        print "Prechecks OK<br/>\n";
        print "</font>";
        print "<br/>";
        print '<input type="button" value="Ok. Go to the next Step." onclick="window.location = \'./\'"/>';
        $_SESSION["step"] = 2;
        exit();
    }

}
if (count($_POST) == 0 || checkRequired()) {

    $xsl = new DomDocument();
    $xsl->load("extractProperties.xsl");
    $xml = new DomDocument();
    $xml->load("properties.xml");


    $proc = new XsltProcessor();
    $proc->registerPhpFunctions();
    $proc->importStylesheet($xsl);

    print $proc->transformToXML($xml);

}
else {
    ?>

    <h1 class="pageTitle">
    Running Phing
    </h1>
    <?php
    startPhing();
}

function startPhing() {
    /**
    * This is the Phing command line launcher. It starts up the system evironment
    * tests for all important paths and properties and kicks of the main command-
    * line entry point of phing located in phing.Phing
    * @version $Revision: 1.7 $
    */

    // Set any INI options for PHP
    // ---------------------------

    ini_set('track_errors', 1);

    /* set classpath */

    define('PHP_CLASSPATH', getcwd(). "/phing/classes/" . PATH_SEPARATOR . getcwd()."/../inc/". PATH_SEPARATOR . get_include_path());
    ini_set('include_path', PHP_CLASSPATH);

    require_once 'phing/Phing.php';

    /* Setup Phing environment */
    Phing::startup();

    /*
    find phing home directory
    -- if Phing is installed from PEAR this will probably be null,
    which is fine (I think).  Nothing uses phing.home right now.
    */
    Phing::setProperty('phing.home',getcwd());
    $props = readProperties();
    // defining of sub_dir needs some special checks...
        $_POST["dir_sub"] = trim($_POST["dir_sub"] ,"/");
        if ($_POST["dir_sub"] != "") {
            $_POST["dir_sub"] .= "/";
            $_POST["dir_sub_htaccess"] = $_POST["dir_sub"] . "*";

        } else {
            $_POST["dir_sub_htaccess"] = "";
            $props["dir.sub.htaccess"]["canBeEmpty"] = true;
        }

    foreach($_POST as $key => $value) {
        $key = str_replace("_",".",$key);

        if ($value || (isset($props[$key]["canBeEmpty"]) && $props[$key]["canBeEmpty"] == "true")) {
            print $key ." -> " .$value."<br/>";
            Phing::setProperty($key,$value);
        }
    }
    
     
    Phing::setProperty("BxRootDir",rootDir()."/");
    if (extension_loaded("mysqli")) {
        Phing::setProperty("database.type","mysqli");
    } else {
        Phing::setProperty("database.type","mysql");
    }
    
    //Phing::setProperty("database.prefix", $_POST['database_prefix'].'live__');
    Phing::setProperty("replacePhpInHtaccess", function_exists("apache_get_modules") ? "php_" : "#php_");
    
    $mysql_version = getMysqlVersion();
        
    if ( version_compare($mysql_version,"4.1",">=")) {
        Phing::setProperty("DbHasUTF8","true");
    } else {
        Phing::setProperty("DbHasNoUTF8","true");
    }
    /* polish CLI arguments */

    $args = array("-logger","phing.listener.HtmlLogger","-buildfile",getcwd().DIRECTORY_SEPARATOR."build.xml");
    /* fire main application */
    chdir("..");

    Phing::fire($args);

    /*
    exit OO system if not already called by Phing
    -- basically we should not need this due to register_shutdown_function in Phing
    */

    Phing::halt(0);

}
function rootDir() {

    return dirname(getcwd()."..");
}

function isAllowOverrideAll() {
	$paths = explode(PATH_SEPARATOR, get_include_path());
	if (in_array("/dummy/", $paths)) {
		return true;
	}
	return false;	
}

function prereq() {
    print "Prechecks:<br/>\n";
    print "Checking for >= PHP 5.0.0 ... \n";
    print "Found PHP ".phpversion().".\n";
    if (version_compare('5.0.0',phpversion() ,"<=" )) {
        print "OK.<br/> \n";
    }  else {
        print "<font color='red'>Not Ok!<br/>";
        return ("Wrong PHP Version");
    }
	/*
    has to be discussed, doesn't work this way :)
    print "Checking for Apache Config (AllowOverride is not None) ...\n";
	if (isAllowOverrideAll()) {
	        print "OK.<br/> \n";
    }  else {
        print "<font color='red'>Not Ok!<br/>";
        return ("Check your Apache Config if AllowOverride is not set to none");
	}*/	
	print "Checking for Apache Module ...\n";
    if (stripos(PHP_OS,"Win") === 0) {
    	print "<br/>You are using Windows. We can't reliably check for installed apache modules here (some systems crash)<br/>
    	      <font color='red'>Please make sure, you have enabled mod_rewrite</font><br/>
    	      See <a href='http://wiki.bitflux.org/Installation_on_Windows'>http://wiki.bitflux.org/Installation_on_Windows</a> for details
    	      <br/>(You may have installed, we just don't know ;) )<br/><br/>";
  	} else  {
/*If you're system crashes, please comment out the whole following if/else block and try it again
*/
    if (!function_exists("apache_get_modules")) {
         print "<font color='red'>Not Found!<br/>";
         print "You're either not running PHP as Apache Module (maybe as CGI) or you're not running Apache at all.<br>";
         print "This is not really tested by us. But you can still try, it *should* work.<br>";
         print "</font>";
    } else {
        print "OK.<br/> \n";
        print "Checking for mod_rewrite ...\n";
        if (in_array("mod_rewrite",apache_get_modules())) {
            print "OK.<br/> \n";
        } else {
            print "<font color='red'>Not Found<br/>";
            return ("mod_rewrite not found. Please enable it in your httpd.conf");
        }
    }
    }
    //


    print "Checking for DOM Support ...\n";
    if (class_exists("domdocument")) {
        print "OK.<br/> \n";
    }  else {
        print "<font color='red'>Not found!<br/>";
        return "No DOM Support Found";
    }
    print "Checking for XSLT Support ...\n";
    if (class_exists("xsltprocessor")) {
        print "OK.<br/> \n";
    }  else {
        print "<font color='red'>Not found!<br/>";
        return "No XSLT Support Found";
    }
    
    print "Checking for MySQL Support ...\n";
    if (extension_loaded("mysql") || extension_loaded("mysqli")) {
        $mysql_version = getMysqlVersion();
        if (! version_compare($mysql_version,"4.0",">=")) {
            print "<font color='red'>Wrong version.<br/>";
            return "MySQL too old. Should be >= 4.0, is $mysql_version";   
        }
        print "Found $mysql_version. OK.<br/> \n";
        
        if (! version_compare($mysql_version,"4.1",">=")) {
            print "<font color='red'>We highly recommend MySQL >= 4.1 for better UTF-8 support.</font><br/>";
        }
        
    }  else {
        print "<font color='red'>Not Found!</font><br/>";
        print "<font color='red'>Flux CMS is based on MDB2, so it may work with another DB than MySQL, but this is not tested and the installer most presumably won't work.</font><br/>";
    }
    

    print "Check if Root (".rootDir().") directory is writable ...\n";
    if(is_writeable(rootDir())) {
        print "OK.<br/>\n";
    } else {
        print "<font color='red'>";
        print "Not writeable<br/>";
        return "Root Dir (".rootDir().") is not writeable";
    }

    
    /* print "Check if live dir (".rootDir()."/_hosts/live/) directory is writable ...\n";
    if(is_writeable(rootDir()."/_hosts/live/")) {
        print "OK.<br/>\n";
    } else {
        print "<font color='red'>";
        print "Not writeable<br/>";
        return "Root Dir (".rootDir()."/_hosts/live/) is not writeable";
    }*/
    
    return true;
}

function getMysqlVersion() {
    if (extension_loaded("mysqli")) {
        $mysql_version = @mysqli_get_server_info();
        if (!$mysql_version) {
            $mysql_version = @mysqli_get_client_info();
        }
    } else {
        $mysql_version = @mysql_get_server_info();
        if (!$mysql_version) {
            $mysql_version = @mysql_get_client_info();
        }
    }

    if (strpos($mysql_version, "mysqlnd") === 0) {
        $arr = explode(" ", $mysql_version);
        $mysql_version = $arr[1];
    }

    return $mysql_version;
}

function readProperties($dom = null) {
    $dom = new DomDocument();
    $dom->load("properties.xml");
    $xp = new DomXPath($dom);
    $res = $xp->query("/properties/property");
    $props = array();
    foreach($res as $node) {
        $prop = array();

        foreach($node->attributes as $attr) {
            $prop[$attr->nodeName] = $attr->value;

        }
        $props[$node->getAttribute("name")] = $prop;
    }
    return $props;

}

function checkRequired() {
    $props = readProperties();
    $error = false;
    foreach($props as $name => $value) {
        if (isset($value["required"]) && $value["required"] == true) {
            $pname = str_replace(".","_",$name);
            if (!(isset($_POST[$pname]) && $_POST[$pname])) {
                print "<font color='red'>$name is required</font><br/>";
                $error = true;
            }

        }
    }
    return $error;


}

function subdir() {
    $dir = substr(str_replace(realpath($_SERVER['DOCUMENT_ROOT']),"",str_replace("\\","/",realpath(getcwd()))),0,-7);
    if ($dir == "/") { $dir = "";}
    return $dir;
}
?>


</body>
</html>
