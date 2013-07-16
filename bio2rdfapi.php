<?php
/**
Copyright (C) 2013 Michel Dumontier

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

require('rdfapi.php');

/**
 * Bio2RDF API
 *
 * @author Michel Dumontier 
 * @version 1.0
*/

class Bio2RDFizer extends RDFFactory
{
	/** the application arguments */
	private $argv = null;
	/** the Bio2RDF release number */
	private $bio2rdf_version = null;
	/** the dataset version number */
	private $dataset_version = null;
	
	/** the dataset prefix name */
	private $prefix = null;
	/** the default namespace */
	private $default_namespace = null;
	/** the dataset's vocabulary prefix */
	private $vocabulary_namespace = null;
	/** the dataset's resource prefix */
	private $resource_namespace = null;
	
	/** whether to expand a statement */
	private $rdf_model = false;
	/** release filename */
	private $release_filename = null;
	/** download url */
	private $bio2rdf_download_url = null;
	/** source downloaded files */
	private $source_download_files = null;
	/** follow Bio2RDF guidelines */
	private $bio2rdf_guidelines = true;
	
	/** 
	 * the constructor 
	 * 
	 * @input $namespace the dataset prefix
	*/
	function __construct($argv, $prefix)
	{
		parent::__construct();
		
		// make sure argv is not null
		if(!isset($argv)) {
			trigger_error("Invalid application arguments!",E_USER_ERROR);
			return null;
		}
		$this->argv = $argv;
		$this->setPrefix($prefix);
		$this->setNamespaces($prefix);
	}
	
	/**
	 * the default descructor
	 * currently generates a list of non-matching namespaces
	 * 
	 */
	function __destruct()
	{
		// get the list of unmapped terms
		$this->getRegistry()->printNoMatchList();
	}
	
	/**
	 * set the default dataset prefix
	 *
	 * @input $prefix The short name for the dataset
	 */
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
		return $this;
	}
	
	/** 
	 * get the default dataset prefix
	 * 
	 * @output the dataset prefix
	 */
	 public function getPrefix()
	{
		return $this->prefix;
	}
	
    /** 
	 * set the Bio2RDF version
	 * 
	 * @input $version the Bio2RDF version
	 */
	public function setBio2RDFVersion($bio2rdf_version)
	{
		$this->bio2rdf_version = $bio2rdf_version;
	}
	
	/**
	 * get the Bio2RDF version 
	 * 
	 * @output 	the Bio2RDF version 
	 */
	public function getBio2RDFVersion()
	{
		return $this->bio2rdf_version;
	}
	
	/** 
	 * set the dataset version number
	 * 
	 * @input $version the dataset version number
	 */
	public function setDatasetVersion($dataset_version)
	{
		$this->dataset_version = $dataset_version;
	}
	
	/**
	 * get the dataset version number
	 * 
	 * @output 	the dataset version number
	 */
	public function getDatasetVersion()
	{
		return $this->dataset_version;
	}
	
	public function setNamespaces($prefix)
	{
		$this->default_namespace    = $prefix.":";
		$this->vocabulary_namespace = $prefix."_vocabulary:";
		$this->resource_namespace   = $prefix."_resource:";
	}
	
	public function getNamespace()
	{
		return $this->default_namespace;
	}
	
	public function getRes() {
		return $this->resource_namespace;
	}
		
	public function getVoc() {
		return $this->vocabulary_namespace;
	}
	
	public function setFollowBio2RDFGuidelines($value)
	{
		$this->bio2rdf_guidelines = $value;
	}
	public function getFollowBio2RDFGuidelines()
	{
		return $this->bio2rdf_guidelines;
	}
	
	public function initialize() 
	{	
		parent::addParameter('indir',false,null,'/data/download/'.$this->getPrefix().'/','directory to download into and/or parse from');
		parent::addParameter('outdir',false,null,'/data/rdf/'.$this->getPrefix().'/','directory to place output files');
		parent::addParameter('download',false,'true|false','false','set true to download files');
		parent::addParameter('graph_uri',false,null,null,'provide the graph uri to generate n-quads instead of n-triples');
		
		parent::addParameter('parser',false,$this->getPrefix(),$this->getPrefix(),'this Bio2RDF parser');
		parent::addParameter('registry_dir',false,null,'/data/download/registry/','directory for the local version of the regisry');
		parent::addParameter('registry_cache_time',false,null,'1','in days; 0 to force download');
		parent::addParameter('uri_scheme',false,'provider-uri|bio2rdf-uri|identifiers.org-uri','bio2rdf-uri','uri scheme preference');
		parent::addParameter('guidelines',false,'true|false','true','follow Bio2RDF guidelines');
		parent::addParameter('model',false,'simple|sio|ovopub|nanopub','simple','format to selected rdf data model');
		parent::addParameter('output_level',false,'dataset|file|record|triple','file','level at which to generate output files');
		parent::addParameter('output_format',false,'nt|nt.gz|nquad|nquad.gz','nt.gz','output format');
		parent::addParameter('file_type',false,'nt|nt.gz|nquad|nquad.gz','nt.gz','output format');
		parent::addParameter('log_level',false,'error|warning|notice','warning','level at which to print log messages');
		parent::addParameter('unregistered_ns',false,'die|skip|continue','continue','what to do if the namespace is not found in registry');
		
		if(parent::setParameters($this->argv) == false) {
			parent::printParameters($this->argv);
			exit;
		}
		setLogLevelFromString($this->getParameterValue('log_level'));
		if(parent::createDirectory(parent::getParameterValue('indir')) === false) exit;
		if(parent::createDirectory(parent::getParameterValue('outdir')) === false) exit;
		if(strstr(parent::getParameterValue('output_format'),".gz")) $this->gz_compress = true;
		if(strstr(parent::getParameterValue('output_format'),"nquad")) $this->output_format = "nquad";
		if(parent::createDirectory(parent::getParameterValue('registry_dir')) === false) exit;
		if(parent::getParameterValue('graph_uri')) parent::setGraphURI(parent::getParameterValue('graph_uri'));	
		
		$this->getRegistry()->setLocalRegistry(parent::getParameterValue('registry_dir'));
		$this->getRegistry()->setCacheTime(parent::getParameterValue('registry_cache_time'));
		$this->getRegistry()->setUnregisteredNSAction(parent::getParameterValue('unregistered_ns'));
		$schemes = explode(",", parent::getParameterValue('uri_scheme'));
		$this->getRegistry()->setURISchemePriority($schemes);
		$this->setRDFModel(parent::getParameterValue('model'));
		$this->setFollowBio2RDFGuidelines( (parent::getParameterValue('guidelines')=="true"?true:false) );
		
		// check namespace validity against registry
		if(!$this->getRegistry()->isPrefix($this->getPrefix())) {
			trigger_error("Invalid namespace ".$this->getPrefix(),E_USER_ERROR);
			return null;
		}
	
		$bio2rdf_dataset_version = "bio2rdf-".$this->getPrefix()."-".date("Ymd");
		$this->setDatasetURI("bio2rdf_dataset:".$bio2rdf_dataset_version);
		$this->setBio2RDFReleaseFile($bio2rdf_dataset_version.".nt");
	}
	
	public function clear()
	{
		$this->declared = null;
		$this->getRegistry()->clearNoMatchList();
	}
	
	/** 
	 * Describe a resource in terms of label, title and description 
	 * 
	 * @param string $qname The qname for the resource
	 * @param string $label The label to assign to the resource
	 * @param string $title The source provided title for the resource
	 * @param string $description The source provided description for the resource
	 * @param string $lang The language tag to use for the above literals
	 * @return string The entity description in RDF
	 */
	public function describe($qname,$label,$title=null,$description=null,$lang="en")
	{
		$buf = '';
		if(!isset($this->declared[$qname])) {
			$this->declared[$qname] = '';
			
			$s = $this->getRegistry()->mapQName($qname);
			$this->getRegistry()->parseQName($s,$ns,$id);

			$buf  = $this->QQuadL($s,"rdfs:label",$label." [$s]",$lang);
			if(isset($title) && $title != '') $buf .= $this->QQuadL($s,"dc:title",$title,$lang);
			if(isset($description) && $description != '') {
				$buf .= $this->QQuadL($s,"dc:description",$description,$lang);
			}
			if($this->getFollowBio2RDFGuidelines() == true) {
				$buf .= $this->QQuadL($s,"dc:identifier",$s,null,"xsd:string");
				$buf .= $this->QQuadL($s,"bio2rdf_vocabulary:namespace",$ns,null,"xsd:string");
				$buf .= $this->QQuadL($s,"bio2rdf_vocabulary:identifier",$id,null,"xsd:string");
				if( (($pos = strpos($ns,"_resource")) != FALSE)
					|| (($pos = strpos($ns,"_vocabulary")) != FALSE)) {
					$type = substr($ns,0,$pos)."_vocabulary";
				} else {
					$type = $ns."_vocabulary";
				}
				// identifiers.org
				// rdf:type <http://identifiers.org/clinicaltrials/resource>
				$buf .= $this->QQuad($s,"rdf:type",$type.":Resource");
				$buf .= $this->QQuad($s,"void:inDataset",$this->GetDatasetURI());
			}
		}

		return $buf;
	}
	
	/** 
	 * Describe an individual in terms of label, title, description and parent type
	 * 
	 * @param string $qname The qname for the resource
	 * @param string $label The label to assign to the resource
	 * @param string $parent The class that this individual instantiates
	 * @param string $title The source provided title for the resource
	 * @param string $description The source provided description for the resource
	 * @param string $lang The language tag to use for the above literals
	 * @return string The entity description in RDF
	 */
	 
	function describeIndividual($qname,$label,$parent,$title=null,$description=null,$lang="en") 
	{
		$d = $this->describe($qname,$label,$title,$description, $lang);
		if($d && ($this->getFollowBio2RDFGuidelines() == true) ) {
			$d .= $this->QQuad($qname,"rdf:type","owl:NamedIndividual");
			if(isset($parent) && $parent != null) {
				$d .= $this->QQuad($qname,"rdf:type",$parent);
			}
			return $d;
		}
		return '';
	}
	
	public function describeClass($qname,$label,$parent=null,$title=null,$description=null,$lang="en") 
	{
		$d = $this->describe($qname,$label,$title,$description,$lang);
		if($d && ($this->getFollowBio2RDFGuidelines() == true)) {
			$d .= $this->QQuad($qname,"rdf:type","owl:Class");
			if(isset($parent) && $parent != null) {
				$d .= $this->QQuad($qname,"rdfs:subClassOf",$parent);
			}
			return $d;
		}
		return '';
	}
	
	public function describeProperty($qname,$label,$parent=null,$title=null,$description=null,$lang="en")
	{
		$d = $this->describe($qname,$label,$title,$description,$lang);
		if($d && ($this->getFollowBio2RDFGuidelines() == true)) {
			$d .= $this->QQuad($qname,"rdf:type","rdf:Property");
			if(isset($parent) && $parent != null) {
				$d .= $this->QQuad($qname,"rdfs:subPropertyOf",$parent);
			}
			return $d;
		}
		return '';
	}
	
	protected function describeObjectProperty($qname,$label,$parent=null,$title=null,$description=null,$lang="en") 
	{
		$d = $this->describe($qname,$label,$title,$description,$lang);
		if($d && ($this->getFollowBio2RDFGuidelines() == true)) {
			$d .= $this->QQuad($qname,"rdf:type","owl:ObjectProperty");
			if(isset($parent) && $parent != null) {
				$d .= $this->QQuad($qname,"rdfs:subPropertyOf",$parent);
			}
			return $d;
			
		}
		return '';
	}
	
	protected function describeDatatypeProperty($qname,$label,$parent=null,$title=null,$description=null,$lang="en") 
	{
		$d = $this->describe($qname,$label,$title,$description,$lang);
		if($d && ($this->getFollowBio2RDFGuidelines() == true)) {
			$d .= $this->QQuad($qname,"rdf:type","owl:DatatypeProperty");
			if(isset($parent) && $parent != null) {
				$d .= $this->QQuad($qname,"rdfs:subPropertyOf",$parent);
			}
			return $d;
		}
		return '';
	}

	
	public function triplify($s,$p,$o,$o_parent=null,$class = false)
	{
		$buf = '';
		// see if we can get the fast description of the predicate and type removing a dash
		if(strstr($p,"_vocabulary")) {
			$a = explode(":",$p,2);
			$p_label = str_replace("-"," ",$a[1]);
			if($a[1] != $p_label) $buf .= $this->describeObjectProperty($p,$p_label);
		}
		// now generate a type description
		if($o_parent != null) {
			$a = explode(":",$o_parent,2);
			$o_label = str_replace("-"," ",$a[1]);
			if($a[1] != $o_label) $buf .= $this->describeClass($otype,$o_label);
			
			if($class === false) {
				$buf .= parent::QQuad($o,"rdf:type",$o_parent);
			} else {
				$buf .= parent::QQuad($o,"rdfs:subClassOf",$o_parent);
			}
		}
		
		return $this->QQuad($s,$p,$o).$buf;
	}
	
	
	public function triplifyString($s,$p,$l,$dt=null,$lang=null,$o=null,$o_type=null)
	{
		$buf = '';
		if(!isset($dt)) {
			$dt = $this->guessDatatype($l);
		}
		if($this->getRDFModel() == "sio") {
			$this->getRegistry()->parseQName($s,$s_ns,$s_id);
			$this->getRegistry()->parseQName($p,$p_ns,$p_id);
			$s_ns= str_replace("_resource","",$s_ns);
			if(!isset($o_type)) { // make the o-type from the predicate
				$o_type = $s_ns."_vocabulary:".ucfirst($p_id);
				$buf .= $this->describeClass($o_type,str_replace("-"," ",$p_id));
			}
			if(!isset($o)) {
				$o = $s_ns."_resource:".md5($s.$p.$l);
				$buf .= $this->describeIndividual($o,str_replace("-"," ",$p_id),$o_type);
			}
			
			return $buf.$this->triplify($s,$p,$o).$this->QQuadL($o,"rdf:value",$l,$lang,$dt);
		} else if($this->getRDFModel() == "ovopub") {
			// @todo
		} else if ($this->getRDFModel() == "nanopub") {
			// @todo
		} else {
			// if $dt is set, make $o a literal
			if(strstr($p,"_vocabulary")) {
				$a = explode(":",$p,2);
				$p_label = str_replace("-"," ",$a[1]);
				if($a[1] != $p_label) $buf .= $this->describeDatatypeProperty($p,$p_label);
			}
			
			if(isset($lang)) {
				return $buf.$this->QQuadL($s,$p,$l,$lang);
			} else if(isset($dt)) {
				return $buf.$this->QQuadL($s,$p,$l,null,$dt);
			} else {
				return $buf.$this->QQuadL($s,$p,$l,null,"xsd:string");
			}
		}
	
	}

	public static function guessDatatype($l)
	{
		preg_match("/^([0-9]+\.[0-9]+)$/",$l,$m);
		if(isset($m[1])) return "xsd:float";
		preg_match("/^([0-9]+)$/",$l,$m);
		if(isset($m[1])) return "xsd:integer";
		preg_match("/^([0-9]{4}-[0-9]{2}-[0-9]{2})$/",$l,$m);
		if(isset($m[1])) return "xsd:date";
		preg_match("/^([0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}\:[0-9]{2}\:[0-9]{2})/",$l,$m); 
		if(isset($m[1])) return "xsd:dateTime";
		
		return "xsd:string";
	}
	
	public function getDatasetResourceFromQName($qname,&$ns)
	{
		$qname = $this->getRegistry()->mapQName($qname);
		$a = explode(":",$qname,2);
		if(count($a) !== 2) {
			trigger_error("Improper qname - $qname",E_USER_ERROR);
			return false;
		}
		$ns = $a[0];
		if( (($pos = strpos($ns,"_resource")) != FALSE)
		    || (($pos = strpos($ns,"_vocabulary")) != FALSE)) {
		
			$type = substr($ns,0,$pos)."_vocabulary:";
		} else {
			$type = $ns."_vocabulary:";
		}
		
		return $type."Resource";
	}
	
	/**
	 *  
	 * @param $serialization_format the serialization format : 
	 */
	public function setRDFModel($rdf_model)
	{
		$this->rdf_model = $rdf_model;
		return $this;
	}
	public function getRDFModel()
	{
		return $this->rdf_model;
	}
	
	
	public function setDatasetURI($dataset_uri)
	{		
		$this->dataset_uri = $dataset_uri;
	}
	public function getDatasetURI()
	{
		return $this->dataset_uri;
	}
	
	public function getDatasetDescription(
		$dataset_name, 
		$dataset_uri, 
		$creator_uri, 
		$publisher_name, 
		$publisher_uri, 
		$data_urls = null, 
		$sparql_endpoint = null, 
		$source_homepage, 
		$source_rights, 
		$source_license = null, 
		$source_location = null, 
		$source_version = null)
	{
		$rdf = '';
		$date = date("Y-m-d");
		$datetime = date("Y-m-d\TG:i:s\Z");

		$rdf .= $this->QQuad ($dataset_uri,"rdf:type","void:Dataset");
		$rdf .= $this->QQuadL($dataset_uri,"rdfs:label","$dataset_name RDF dataset generated by $publisher_name at $datetime [$dataset_uri]");
		$rdf .= $this->QQuadL($dataset_uri,"dc:created",$datetime,null,"xsd:dateTime");
		$rdf .= $this->QQuadO_URL($dataset_uri,"dc:creator",$creator_uri);
		$rdf .= $this->QQuadO_URL($dataset_uri,"dc:publisher",$publisher_uri);
		if($this->getBio2RDFVersion()) $rdf .= $this->QQuadL($dataset_uri,"pav:version",$this->getBio2RDFVersion());
		
		$rights = array("use-share-modify","attribution","restricted-by-source-license");
		foreach($rights AS $right) {
			$rdf .= $this->QQuadL($dataset_uri,"dc:rights",$right);
		}
		if(isset($source_license)) $rdf .= $this->QQuadO_URL($dataset_uri,"dc:license","http://creativecommons.org/licenses/by/3.0/"); // @todo check
		if(isset($data_urls)) {
			if(!is_array($data_urls)) $data_urls = array($data_urls);
			foreach($data_urls AS $u) {
				$rdf .= $this->QQuadO_URL($dataset_uri,"void:dataDump",$u);
			}
		}
		if(isset($sparql_endpoint)) $rdf .= $this->QQuadO_URL($dataset_uri,"void:sparqlEndpoint",$sparql_endpoint);
		
		// source description
		$source_uri = "bio2rdf_dataset:$dataset_name";
		
		// link dataset to source
		// $rdf .= $this->QQuad($dataset_uri,"dc:source",$source_uri);
		$rdf .= $this->QQuad($dataset_uri,"prov:wasDerivedFrom",$source_uri);
		
		$rdf .= $this->QQuadL($source_uri,"rdfs:label","$dataset_name dataset [$source_uri]");
		$rdf .= $this->QQuad($source_uri,"rdf:type","dc:Dataset");
		$rdf .= $this->QQuadO_URL($source_uri,"foaf:homepage",$source_homepage);
		$rights = $source_rights;
		if(!is_array($rights)) $rights = array($source_rights);
		foreach($rights AS $right) {
			$rdf .= $this->QQuadL($source_uri,"dc:rights",$this->GetRightsDescription($right));
		}
		if(isset($source_license))  $rdf .= $this->QQuadO_URL($source_uri,"dc:license",$source_license);
		if(isset($source_location)) $rdf .= $this->QQuadO_URL($source_uri,"rdfs:seeAlso",$source_location);
		if(isset($source_version))  $rdf .= $this->QQuadL($source_uri,"pav:version",$source_version);

		return $rdf;
	}
	
	public function setBio2RDFReleaseFile($filename)
	{
		$this->release_filename = $filename;
		return $this;
	}
	public function getBio2RDFReleaseFile() 
	{
		return $this->release_filename;
	}
	
	public function setBio2RDFDownloadURL($namespace)
	{
		$bio2rdf_version = $this->getBio2RDFVersion();
		$dataset = $this->getNamespace();
		$this->bio2rdf_download_url = "http://download.bio2rdf.org/release/$bio2rdf_version/$dataset/rdf/";
		return $this;
	}
	
	public function getBio2RDFDownloadURL()
	{
		return $this->bio2rdf_download_url;
	}
	
	public function getBio2RDFDatasetDescription(
		$namespace, 
		$script_url, 
		$download_files,
		$source_homepage, 
		$source_rights, 
		$source_license = null, 
		$source_location = null, 
		$source_version = null)
	{
		return $this->GetDatasetDescription(				
			$namespace, 
			$this->GetDatasetURI(), 
			$script_url, 
			"Bio2RDF", 
			"http://bio2rdf.org", 
			$download_files,
			"http://$namespace.bio2rdf.org/sparql", 
			$source_homepage, 
			$source_rights, 
			$source_license, 
			$source_location, 
			$source_version);
	}
	
	public function getRightsDescription($right)
	{
		$rights = array(
			"use" => "free to use",
			"use-share" => "free to use and share as is",
			"use-share-modify" => "free to use, share, modify",			
			"no-commercial" => "commercial use requires licensing",
			"no-derivative" => "no derivatives allowed without permission",
			"attribution" => "requires attribution",
			"restricted-by-source-license" => "check source for further restrictions"
		);
		if(!isset($rights[$right])) {
			trigger_error("Unable to find $right in ".implode(",",array_keys($rights))." of rights");
			return FALSE;
		}
		return $rights[$right];
	}	
	
	
	/**
	 * Write the RDF to the file, taking into account whether it is a record or dataset
	 */
	public function setCheckPoint($level, $finalize = false) 
	{
		// if rdf present, the generate file if not available and write to it
		if($this->hasRDF() === TRUE) {
			// check if there is an active file pointer
			if($this->writeFileExists() === FALSE) {
				// create the file from the file pattern
				$file = $this->getWriteFilePath().parent::getParameterValue('output_level').$this->gz_compress;
				$this->setWriteFile($file, $this->getOutputCompression());
			}
			parent::writeRDFBufferToWriteFile();
			if($level == parent::getParameterValue('output_level') && $finalize == true) {
				$this->closeWriteFile();
			}
		}

		// if the level is record, then generate a uuid and set it as the graph
		if($level == parent::getParameterValue('output_level'))
		{
			// generate the graph uri
			if(!isset($uri)) {
				// generate one
				$uuid  = uniqid('',true);
				$uri = $this->getRes().$uuid;
			}
			parent::setGraphURI($uri);
			// open the file pointer
		}
	}
}
