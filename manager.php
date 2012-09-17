<?php
$base_dir    = "/sparql";
$instance_dir  = $base_dir."/virtuoso";
$instance_file = $base_dir."/instances.tab";
$apache_config_file   = $base_dir."/virtuoso-apache.conf";
$virtuoso_dir   = "/usr/local/virtuoso-opensource-6.1.6";

if($argc <= 2 || (isset($argv[2]) && !in_array($argv[2],array("create","refresh","start","stop","apacheconfig")))) {
 echo "$argv[0] [all|ns] [create|start|stop|refresh|apacheconfig] [GB memory to use]".PHP_EOL;
 exit;
}
$ns = $argv[1];
$fnx = $argv[2];
$opt = '';
if(isset($argv[3])) $opt = $argv[3];

$instances = GetInstancesFromFile($instance_file);
if($ns != "all") {
  if(!isset($instances[$ns])) {
    echo "Fist add the $ns entry to $instance_file".PHP_EOL;
    exit;
  }
  
  $list[$ns] = $instances[$ns];
} else $list = $instances;


$buf ='';
foreach($list AS $n => $i) 
{
  if($fnx == "create") {
	CreateInstance($i,$instance_dir,$virtuoso_dir);
	CreateVirtuosoINI($i,$instance_dir,$virtuoso_dir);
	StartInstance($i,$instance_dir);
  }
  else if($fnx == "start") {
	if($ns != 'all' || ($ns == 'all' && $i['default_start'] == true)) {
		 StartInstance($i,$instance_dir);
	}
  }
  else if($fnx == "stop") StopInstance($i);
  else if($fnx == "refresh") CreateVirtuosoINI($i,$instance_dir,$virtuoso_dir);
  else if($fnx == "apacheconfig") $buf .= CreateApacheConfig($i);
  if($ns != 'all') break;
}
if($buf) file_put_contents($apache_config_file,$buf);


function CreateInstance($instance,$instance_dir,$virtuoso_dir)
{
 $ns = $instance['ns'];
 $this_instance_dir = $instance_dir."/$ns";
 echo "Creating $ns ...";

 // if exists, delete ; then create
 if(is_dir($this_instance_dir)) {
  system("rm -rf ".$this_instance_dir);
 }
 system("mkdir -p ".$this_instance_dir);
  
 // copy the executable and the config file
 system("cp -f ".$virtuoso_dir."/bin/virtuoso-t ".$this_instance_dir."/p-$ns");
 // copy the database
 $vdb = $virtuoso_dir."/var/lib/virtuoso/db/virtuoso.db";
 if(!file_exists($vdb)) {
   trigger_error("unable to find $vdb. you must create a default virtuoso instance. Remember to change the dba password and add the facet package");
   exit;
 } 
 system("cp ".$vdb." $this_instance_dir/$ns.virtuoso.db");
}

function CreateVirtuosoINI($instance,$instance_dir,$virtuoso_dir)
{
 $ns = $instance['ns'];
 global $opt;
 if(!$opt) $opt = 2;
 $buffers = floor($opt * 85000);
 $dirtybuffers = floor($opt * 62500);
 
 // now read in the virtuoso file and modify the db, www port and isql port
 $inifile = $virtuoso_dir."/var/lib/virtuoso/db/virtuoso.ini"; 
 $this_instance_dir = $instance_dir."/$ns";

 $ini = ReadVirtuosoINI($inifile);
 SetVirtuosoINI($ini, array(
	"Database" => array (
		"DatabaseFile" => $this_instance_dir."/$ns.virtuoso.db",
		"ErrorLogFile" => $this_instance_dir."/virtuoso.log",
		"LockFile"     => $this_instance_dir."/virtuoso.lck",
		"TransactionFile" => $this_instance_dir."/virtuoso.trx",
		"xa_persistent_file" => $this_instance_dir."/virtuoso.pxa",
		"MaxCheckpointRemap" => "125000",
	),
	"TempDatabase" => array(
		"DatabaseFile" =>    $this_instance_dir."/virtuoso-temp.db",
		"TransactionFile" => $this_instance_dir."/virtuoso-temp.trx",
		"MaxCheckpointRemap" => "125000",
	),	
	"Parameters" => array(
		"ServerPort" => $instance['isql_port'],
		"VADInstallDir" => $virtuoso_dir."/share/virtuoso/vad/",
		"DirsAllowed" => "., /usr/local/, /opt, /home, /media, /data ",
		"NumberOfBuffers" => "$buffers",
		"MaxDirtyBuffers" => "$dirtybuffers",
		"TempDBSize" => "100000000"
	),
	"HTTPServer" => array(
		"ServerPort" => $instance['www_port'],
		"ServerRoot" => $virtuoso_dir."/var/lib/virtuoso/vsp"
	),
	"SPARQL" => array(
		"MaxQueryCostEstimationTime" => "20000",
		"MaxQueryExecutionTime" => "10000",
		"DefaultQuery" => "SELECT distinct ?graph ?type (count(?x) AS ?count) WHERE {graph ?graph {?x a ?type} FILTER (?graph != <b3sifp> && ?graph != <http://www.openlinksw.com/schemas/virtrdf#> && ?graph != <http://www.w3.org/2002/07/owl#> && ?graph != <virtrdf-label> && ?graph != <http://localhost:8890/sparql>)} ORDER BY ASC(?graph) ASC(?type) LIMIT 100")  
 ));
unset($ini["Plugins"]);
 WriteVirtuosoINI($ini,"$this_instance_dir/virtuoso.ini");
 echo "done.".PHP_EOL;
 return 0;
}

function ReadVirtuosoINI($file)
{
  $section = '';
  $fp = fopen($file,"r");
  while($l = fgets($fp)) {
    if($l[0] == ';' || trim($l) == '') continue;
    $l = trim($l);
    if($l[0] == '[') {
      // section
      $section = substr(trim($l),1,-1);
    } else {
      $l = str_replace("\t","",$l);
      $a = explode("=",$l);
      $items[$section][trim($a[0])] = trim($a[1]);
    }
  }
  fclose($fp);
  return $items;
}

function SetVirtuosoINI(&$items,$values)
{
  foreach($items AS $section => $a) {
	foreach($a AS $k => $v) {
	    if(isset($values[$section][$k])) $items[$section][$k] = $values[$section][$k];
	}
  }
}

function WriteVirtuosoINI($items,$file)
{
 $buf = '';
 foreach($items AS $section => $o) {
   $buf .= "[".$section."]".PHP_EOL;
   foreach($o AS $k => $v) {
     $buf .= $k."=".$v.PHP_EOL;
   }
 }
 file_put_contents($file,$buf);
}

function StopInstance($instance)
{
  $ns = $instance['ns'];
  system("pgrep p-$ns");
  system("pkill p-$ns"); 
}

function StartInstance($instance,$instance_dir)
{
  StopInstance($instance);

  // start it up
  $ns = $instance['ns'];
  $cmd = "cd ".$instance_dir."/$ns/;./p-$ns &"; 
  system($cmd);
  system("pgrep p-$ns");
}


function GetInstancesFromFile($instance_file)
{
 $fp = fopen($instance_file,"r");
 while($l = fgets($fp)) {
  $a = explode("\t",trim($l));
  if(!isset($a[0][0]) || (isset($a[0][0]) && $a[0][0] == '#')) continue;
  unset($i);
  $i = '';
  $i["isql_port"] = $a[0];
  $i["www_port"] = $a[1];
  $i["ns"] = $a[2];
  if(isset($a[3]) && $a[3] == 'default_start') $i['default_start'] = true;
  else $i['default_start'] = false;

  if($a[2] == '') continue;
  $instances[$i['ns']] = $i;
 }
 fclose($fp);
 return $instances;
}


function CreateApacheConfig(&$instance)
{
$ns = $instance['ns'];
$port = $instance['www_port'];

$buf = '<VirtualHost *:80>
  ServerName  cu.'.$ns.'.bio2rdf.org  
  ServerAlias '.$ns.'.bio2rdf.org  
  ProxyRequests Off
  <Proxy *>
    Order deny,allow
    Allow from all
  </Proxy>
  ProxyPass / http://cu.'.$ns.'.bio2rdf.org:'.$port.'/
  ProxyPassReverse / http://cu.'.$ns.'.bio2rdf.org:'.$port.'/
</VirtualHost>
';
 return $buf;
}
?>
