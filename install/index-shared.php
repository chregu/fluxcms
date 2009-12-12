<?php
    
    $id = $argv[1];
  
    if (!$id ) {
        die ("Please provide an id \n");
    }
    
    
    
    setClassPath();
    
    $props = readProperties();
    
    $dsn = 'mysql://'.$props['database.user']['value'].':'.$props['database.password']['value'].'@'.$props['database.host']['value'].'/'.$props['database.name']['value'];
    include_once("MDB2.php");
    $db = MDB2::connect($dsn);
    
    
    $vals = getUserValues($db,$id);
    
     $password =  password();
        if ($argv[2]) {
$body = 'Hi 

Your Freeflux account is now ready.

Go to http://'.$vals['host'].'/ to see it in action.
You can login into the admin at 
http://'.$vals['host'].'/admin/ 

Your username is:     '.$vals['user'] .'
and your password is: '.$password .'

(You can change the password and your username in
Quicklinks -> Users).

You can also blog via email or mms, just send it 
(incl. pictures, if you want) to:
'.$vals['user'].'+'.$password.'@'.$vals['host'].'

If you are new to Freeflux, resp. Flux CMS, read the
Getting Started with Flux CMS guide at:
http://docs.bitflux.org/en/start/

If you have any questions, problems or other feedback, 
please use our Freeflux Forum at
http://forum.freeflux.net/

More help resources are also listed at:
http://freeflux.net/help/

Enjoy and please remember, it is Beta ;)

The Bitflux Team

';

$subject = "Your Freeflux account ". $vals['host'];

$headers = "From: Freeflux.net <flux@bitflux.ch>\n";
//$headers .= "Bcc: flux@bitflux.ch\n";
mail($vals['email'],$subject,$body,$headers);
}


    
    
    if ($vals) {
        regenerateMap($db);
       
        startPhing($vals['host'],$vals['prefix'], $vals['user'], $vals['email'],$password,$vals['db']);
    }

    
    

function setClassPath() {
    define('PHP_CLASSPATH', getcwd(). "/phing/classes/" . PATH_SEPARATOR . getcwd()."/../inc/". PATH_SEPARATOR . get_include_path());
    ini_set('include_path', PHP_CLASSPATH);
}

function getUserValues($db,$id) {
    $query = "select * from freeflux_master.master where id = $id";
    $res = $db->query($query);
    $row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
    if ($row) {
    $arr = array(
        'user' => $row['user'],
        'email' => $row['email'],
        'prefix' => $row['prefix'],
        'db' => $row['db'],
         'host' => $row['host']);
        
        
        $query = "update freeflux_master.master set active = 1, installed = now() where id = $id";
        $res = $db->query($query);
        return $arr;
    } else {
        return false;
    }
    
}

    
    

function startPhing($host,$prefix,$user,$email,$password, $db) {
    global $argv;

    $defaultLogger = false;
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
    //$props["
    // defining of sub_dir needs some special checks...
    
    foreach($props as $key => $value) {
        
        if ($value ) {
            print $value['name'] ." -> ". $value['value'] ."\n";
            Phing::setProperty($value['name'],$value['value']);
        }
    }
    Phing::setProperty('database.name',$db);
    Phing::setProperty('database.prefix',$prefix . '_');
    Phing::setProperty("BxRootDir",rootDir()."/" );
    Phing::setProperty("BxHostDir",rootDir()."/hosts/".$prefix);
    Phing::setProperty("cms.email",$email);
    Phing::setProperty("cms.password",$password);
    Phing::setProperty("cms.user",$user);
    Phing::setProperty("cms.domainname",$host);
    
    /* polish CLI arguments */
    if ($defaultLogger) {
        $args = array("-logger","phing.listener.DefaultLogger","-buildfile",getcwd().DIRECTORY_SEPARATOR."build-shared.xml");
    } else {
       $args = array("-logger","phing.listener.AnsiColorLogger","-buildfile",getcwd().DIRECTORY_SEPARATOR."build-shared.xml");
    }
    
    /* fire main application */
    
    chdir("..");
    
    Phing::fire($args);
    
    
    /*
    exit OO system if not already called by Phing
    -- basically we should not need this due to register_shutdown_function in Phing
    */
    
    Phing::halt(0);
    
}

function regenerateMap($db) {
    
    $query = "select * from freeflux_master.master";
    $res = $db->query($query);
    $fd = fopen('../tmp/rewrite_hosts.map',"w");
    while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
        if ($row['hostsdir']) {
            fwrite ($fd, $row['host']. "\t" . $row['hostsdir'] ."\n");
        }
    }
    fclose($fd);
    rename('../conf/rewrite_hosts.map','../conf/rewrite_hosts.map.old');
    rename('../tmp/rewrite_hosts.map','../conf/rewrite_hosts.map');
    
}
function rootDir() {
    
    return dirname(getcwd()."..");
}

function readProperties($dom = null) {
    
    
    
    $dom = new DomDocument();
    $dom->load("properties-shared.xml");
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
    $dir = substr(str_replace($_SERVER['DOCUMENT_ROOT'],"",str_replace("\\","/",getcwd())),0,-7);
    if ($dir == "/") { $dir = "";}
    return $dir;
}
// by gassi
function password(){
    
    $password = substr(preg_replace( "/[^0-9A-Za-z]/",  "", crypt(rand())),1, 8);
    //keine I,l,1,O oder 0's (wegen dummen fragen ;))
    $password = str_replace('I','3',$password);
    $password = str_replace('l','5',$password);
    $password = str_replace('1','9',$password);
    $password = str_replace('O','s',$password);
    $password = str_replace('0','x',$password);
    return $password;
}
function makeSymlink($target, $link) {
    $dir = getcwd();
    chdir("..");
    symlink($target,$link);
    chdir($dir);
}
    

?>


</body>
</html>
