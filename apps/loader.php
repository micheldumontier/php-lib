<?php
// script to facilitate loading into virtuoso
// instance file consists of entries of the form serverport\thttp-port\tinstance-name

$isql = "/usr/local/virtuoso-opensource/bin/isql";
//$isql = "/virtuoso/bin/isql";

$options = array(
 "file" => "filename",
 "dir" => "dirname",
 "graph" => "graphname",
 "gprefix" => "graphprefix",
 "instance" => "instancename",
 "port" => "1111",
 "user" => "dba",
 "pass" => "dba",
 "flags" => "272",
 "threads" => "4",
 "updatefacet" => "false",
 "deletegraph" => "false",
 "deleteonly" => "false",
 "initialize" => "false",
 "setns" => "false",
 "setpassword" => "",
 "format" => "n3",
 "ignoreerror" => "true",
 "startat" => ""
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
 if(isset($options[$b[0]])) $options[$b[0]] = $b[1];
 else {echo "unknown key $b[0]";exit;}
}

if($options['instance'] != 'instancename') {
 // load the file and get the port
 // 10001   8001    ncbo
 $instance_file = "instances.tab";
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
    if($options['instance'] == $a[2]) {
      $options['port'] = $a[0];
    }
  }
 }
 fclose($fp);
}

$cmd_pre = "$isql -S ".$options['port']." -U ".$options['user']." -P ".$options['pass']." verbose=on banner=off prompt=off echo=ON errors=stdout exec=".'"'; $cmd_post = '"';

// associate a prefix with namespace
// http://docs.openlinksw.com/virtuoso/fn_xml_set_ns_decl.html
if($options['setns'] == 'true') {
 echo "Setting namespaces for facet browser\n";

 include('ns.php');
 $cmd = '';
 foreach($nslist AS $prefix => $base_uri) {
  if($prefix == 'bio2rdf') continue;
  $cmd .= "DB.DBA.XML_SET_NS_DECL ('$prefix', '$base_uri', 2);"; 
 }
  echo $out = shell_exec($cmd_pre.$cmd.$cmd_post);
  exit;
}
if($options['setpassword'] != '') {
  echo "resetting password".PHP_EOL;
  $cmd = 'set password "'.$options['pass'].'" "'.$options['setpassword'].'"; checkpoint;';
  echo $out = shell_exec($cmd_pre.$cmd.$cmd_post); 
  exit;
}

// check for valid file
if($options['dir'] == 'dirname') {
 // must be a file
 if(!file_exists($options['file'])) {
  echo "File ".$options['file']." does not exists. Please specify a *real* file with the file=filename option\n";
  exit;
 }
 $files[] = $options['file'];
} else {
 if(!is_dir($options['dir'])) {
  echo "Directory ".$options['dir']." does not exists. Please specify a *real* directory with the dir=dirname option\n";
  exit;
 } 
 // get the files
 $files = GetFiles($options['dir']);
 $files = getFileR($options['dir']);
}

$deletegraphs = null; // keep a list of deleted graphs

foreach($files AS $file) {
	if($options['startat'] != '') {
		if($options['startat'] == $file) { $options['startat'] = ''; }
		else continue;
	}

	echo 'Processing '.$file."\n";

	// if the graph has not been set, then create a graph name from the file, minus path and extension
	$graph = $options['graph'];
	if($graph == "graphname") {
		$pos = strrpos($file,"/");
		if($pos !== FALSE) {
			$graph = substr($file,$pos+1);
		}
		$pos = strpos($graph,".");
		if($pos !== FALSE) {
			$graph = substr($graph,0,$pos);
		}
		if($options['gprefix'] != 'graphprefix') {
			$graph = "http://bio2rdf.org/".$options['gprefix']."_".$graph;
		} else { 
			$graph = "http://bio2rdf.org/".$graph;
		}
	}
 
	// do delete graph option, but only once 
	if($options['deletegraph'] == "true" && !isset($deletedgraphs[$graph])) {
		$deletedgraphs[$graph] = true;
		$cmd = "sparql clear graph <$graph>";
		echo "Deleting $graph".PHP_EOL;
		echo $out = shell_exec($cmd_pre.$cmd.$cmd_post);
		if($options['deleteonly'] == "true") exit;
	}


	$path = '';
	$pos = strrpos($file,"/");
	if($pos !== FALSE) {
		$path = substr($file,0, $pos+1);
	}

	// unzip if necessary
	$f = str_replace('\\','/',$file);;
	$fcmd = 'file_to_string_output';
	if(strstr($file,".gz")) {
		$gzfile = $file;
		$un = substr($file,0,-3);
   
		$out = fopen($un,"w");
		$in = gzopen($file,"r");
		while($l = gzgets($in)) {
			fwrite($out,$l);
		}
		fclose($out);
		gzclose($in);
   
		$f = $un;
	} elseif(strstr($file,".zip")) {
		$zfile = $file;
		// get the unzipped file name
		$un = $t = substr($file,0,-4);
		$pos = strrpos($file,"/");
		if($pos !== FALSE) {
			$t = substr($file,strrpos($file,"/")+1,-4);
		}

		// open the file and put the contents in a file   
		$out = fopen($un,"w");
		$zin = new ZipArchive();
		if($zin->open($zfile) === FALSE) {
			trigger_error("Unable to open $zfile");
    			exit;
		}
		$data = '';
		$fpin = $zin->getStream($t);
		while($l = fgets($fpin)) {
			fwrite($out,$l);
		}
		fclose($fpin);
		fclose($out);
		$zin->Close();

		$f = $un;	
	} elseif(strstr($file,".bz")) {
		$bzfile = $file;
		$un = substr($file,0,-3);
		$out = fopen($un,"w");
		$in = bzopen($file,"r");
		while($l = bzread($in)) {
			fwrite($out,$l);
		}
		fclose($out);
		bzclose($in);
		$f = $un;	
	}
 
	// guess the loader
	$fp = fopen($f,'r');
	$l = fgets($fp);
	$xml = false;
	if(strstr($l,'<?xml')) $xml = true;
	if($xml == true) $options['format'] = 'xml';
	if($xml == false && $options['format'] == 'xml') {
		// there's a problem
		echo "$f is not an xml file... skipping";
 		continue;
	}
  	// guess the loader
	fclose($fp);

	// http://docs.openlinksw.com/virtuoso/fn_ttlp_mt.html
	if($options['format'] == 'n3') {
		$program = "DB.DBA.TTLP_MT"; 
		// $program = "DB.DBA.TTLP_MT_LOCAL_FILE";
	} else {
		// http://docs.openlinksw.com/virtuoso/fn_rdf_load_rdfxml_mt.html
		$program = 'DB.DBA.RDF_LOAD_RDFXML_MT';
	}

	$t1 = $path."t1.txt"; // the source
	$t2 = $path."t2.txt"; // the destination
	if(file_exists($t1)) unlink($t1);
	if(file_exists($t2)) unlink($t2);

$tries = 10;
 do { 
  echo "Loading $file into $graph ...".PHP_EOL; 

  $cmd = $program."($fcmd ('$f'), '', '".$graph."', ".$options['flags'].", ".$options['threads']."); checkpoint;";
//   echo $cmd_pre.$cmd.$cmd_post;
  $out = shell_exec($cmd_pre.$cmd.$cmd_post);
 
  if(strstr($out,"Error")) {
    // *** Error 37000: [Virtuoso Driver][Virtuoso Server]SP029: TURTLE RDF loader, line 43: syntax error
    preg_match("/Error ([0-9]+)\:/",$out,$m);
    if(!isset($m[1]) || (isset($m[1]) && $m[1] != '37000')) {
	// some other error
	echo $out;
	break;
    } 

    preg_match("/line\s([0-9]+)\:/",$out,$m);
    if(!isset($m[1])) {
	// some problem here
	exit;
    }

    $line = $m[1]; 
   // write to log?
    echo "Skipping line:$line ... ";

   // we need find find the line number, and slice the file  
    if(!file_exists($t1)) {
      // first use
      echo "making copy of $f\n";
      copy($f,$t1);
    }
    if(file_exists($t2)) {
	unlink($t1);
 	rename($t2,$t1);
    } 
    $fp_in = fopen($t1,"r");
    $fp_out = fopen($t2,"w");
    $i = 0;
    while($l = fgets($fp_in,4096)) {
      $i++;
      if($i == $line) {
	echo "Problem in: $l\n";
	if(--$tries == 0) {echo "quitting after 10 tries";break;}

      } else {
	if($l[0] == '@' || $i > $line) {
		fwrite($fp_out,$l);
	}
      }
    }
    fclose($fp_in);
    fclose($fp_out);
    $f=$t2;
  } else {
	if(file_exists($t1)) unlink($t1);
	if(file_exists($t2)) unlink($t2);
	echo "Done!\n";
	break;
  }
 } while (true);

 if(strstr($file,".gz") || strstr($file,".bz")) {
  if(file_exists($f)) unlink($f);
 }
 echo PHP_EOL;

}


// Facet update : http://virtuoso.openlinksw.com/dataspace/dav/wiki/Main/VirtFacetBrowserInstallConfig
if($options['updatefacet'] == "true") {
	UpdateFacet($cmd_pre,$cmd_post);
}

function UpdateFacet($cmd_pre,$cmd_post)
{
	$cmd = "RDF_OBJ_FT_RULE_ADD (null, null, 'All');VT_INC_INDEX_DB_DBA_RDF_OBJ ();urilbl_ac_init_db();s_rank();";
	echo "Updating facet";
	echo $out = shell_exec($cmd_pre.$cmd.$cmd_post);
}


if($options['initialize'] == 'true') {
  $cmd = "drop index RDF_QUAD_OPGS;drop index RDF_QUAD_POGS;drop index RDF_QUAD_GPOS;drop index RDF_QUAD_OGPS;checkpoint;
  create table R2 (G iri_id_8, S iri_id_8, P iri_id_8, O any, primary key (S, P, O, G)); alter index R2 on R2 partition (S int (0hexffff00));
  log_enable (2); insert into R2 (G, S, P, O) select G, S, P, O from RDF_QUAD;
  drop table RDF_QUAD; alter table R2 rename RDF_QUAD; checkpoint;
  create bitmap index RDF_QUAD_OPGS on RDF_QUAD (O, P, G, S) partition (O varchar (-1, 0hexffff));
  create bitmap index RDF_QUAD_POGS on RDF_QUAD (P, O, G, S) partition (O varchar (-1, 0hexffff));
  create bitmap index RDF_QUAD_GPOS on RDF_QUAD (G, P, O, S) partition (O varchar (-1, 0hexffff));
  checkpoint;";
  
}

function GetFiles($dirname)
{
 $d = dir($dirname);
 while (false !== ($e = $d->read())) {
   if($e == '.' || $e == '..') continue;
   $files[] = $dirname.$e;
 }
 sort($files);
 $d->close();
 return $files;
}

function getFileR($directory, $recursive=true) {
	//This function generates an array of paths to the files of extension $extension
   $array_items = array();
	if ($handle = opendir($directory)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				if (is_dir($directory. "/" . $file)) {
					if($recursive) {
							$array_items = array_merge($array_items, getFileR($directory. "/" . $file, $recursive));
					}//if
					$file = $directory . "/" . $file;
					if(is_file($file)){
							$array_items[] = preg_replace("/\/\//si", "/", $file);
					}
					
				} else {
					$file = $directory . "/" . $file;
					if(is_file($file)){
						$array_items[] = preg_replace("/\/\//si", "/", $file);
					}
				}//else
			}//if
		}//while
		closedir($handle);
	}//if
	return $array_items;
}//getFileR
?>
