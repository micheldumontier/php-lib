<?php
$isql_linux = '/usr/local/virtuoso-opensource/bin/isql';
$isql_windows = "/software/virtuoso-opensource/bin/isql.exe";
$instance_file = 'instances.tab';

$options = array(
 "d" => "directory",
 "f" => "filepattern",
 "i" => "instance",
 "u" => "dba",
 "p" => "dba",
 "o" => "1111",
 "g" => "graph",
 "t" => "4" // threads
);


// show options
if($argc == 1) {
 echo "Usage: php $argv[0] ".PHP_EOL;
 foreach($options AS $key => $value) {
  echo " $key=$value ". PHP_EOL;
 }
}

// set options from user input
foreach($argv AS $i=> $arg) {
 if($i==0) continue;
 $b = explode("=",$arg);
 if(isset($options[$b[0]])) $options[$b[0]] = trim($b[1]);
 else {echo "unknown key $b[0]";exit;}
}

if($options['i'] == 'instance' && $options['d'] == 'directory') {
	echo "Please specify an instance or a directory".PHP_EOL;
	exit;
}

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
 $isql = $isql_windows;
} else $isql = $isql_linux;

// specifies instance
if($options['i'] != 'instance') {
 $instances_file="instances.tab";
 if(!file_exists($instance_file)) {
   trigger_error("Please create the requisite instance file; tab delimited - server port\twww port\tname\n");
   exit;
 }
 $fp = fopen($instance_file,"r");
 if(!isset($fp)) {
        trigger_error("Unable to open $instance_file");
        exit;
 }
 while($l=fgets($fp)) {
  $a=explode("\t",trim($l));
  if(isset($a[2])) {
    if($options['i'] == $a[2]) {
      $options['o'] = $a[0];
    }
  }
 }
 fclose($fp);

 if($options['d']=='directory') $options['d'] = '/data/rdf/'.$options['i'].'/';
}
$options['d'] = str_replace('\\','/',$options['d']);
if($options['g'] == 'graph') $options['g'] = 'test';

$len = strlen($options['d']);
if($options['d'][$len-1] != '/') $options['d'] .= '/';
if($options['f']=='filepattern') $options['f'] = '*';
if($options['f']=='filepattern') $options['f'] = '*';

$cmd_pre = "$isql -S ".$options['o']." -U ".$options['u']." -P ".$options['p']." verbose=on banner=off prompt=off echo=ON errors=stdout exec=".'"'; 
$cmd_post = '"';

$cmd = "DELETE from DB.DBA.load_list";
$exec = $cmd_pre.$cmd.$cmd_post;
$out = shell_exec($exec);
echo $out;

$cmd = "ld_dir('".$options['d']."','".$options['f']."','".$options['g']."');rdf_loader_run();checkpoint;";
$exec = $cmd_pre.$cmd.$cmd_post;
echo $cmd.PHP_EOL;
$out = shell_exec($exec);
echo $out;

