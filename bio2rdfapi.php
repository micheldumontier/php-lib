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
	/** the dataset's preferred prefix */
	private $namespace = null;
	/** the dataset's vocabulary prefix */
	private $namespace_vocabulary = null;
	/** the dataset's resource prefix */
	private $namespace_resource = null;
	/** whether to expand a statement */
	private $rdf_model = false;
	/** release filename */
	private $release_filename = null;
	/** download url */
	private $bio2rdf_download_url = null;
	/** registry location */
	private $registry_file = null;
	
	/** 
	 * the constructor 
	 * 
	 * @input $namespace the dataset preferred prefix
	*/
	function __construct($argv, $namespace)
	{
		parent::__construct();
		// check namespace validity against registry
		$this->argv = $argv;
		$this->setNamespace($namespace);
	}
	
	function __destruct()
	{
		// get the list of unmapped terms
		$this->getRegistry()->printNoMatchList();
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
	
	public function setNamespace($ns)
	{
		$this->namespace = $ns;
		$this->namespace_vocabulary = $this->getNamespace()."_vocabulary:";
		$this->namespace_resource = $this->getNamespace()."_resource:";
	}
	public function getNamespace()
	{
		return $this->namespace;
	}
	
	public function getRes() {
		return $this->namespace_resource;
	}
		
	public function getVoc() {
		return $this->namespace_vocabulary;
	}
	
	public function initialize() 
	{	
		parent::AddParameter('indir',false,null,'/data/download/'.$this->getNamespace().'/','directory to download into and/or parse from');
		parent::AddParameter('outdir',false,null,'/data/rdf/'.$this->getNamespace().'/','directory to place output files');
		parent::AddParameter('download',false,'true|false','false','set true to download files');
		parent::AddParameter('graph_uri',false,null,null,'provide the graph uri to generate n-quads instead of n-triples');
		
		parent::AddParameter('registry_dir',false,null,'/data/download/registry/','directory for the local version of the regisry');
		parent::AddParameter('registry_cache_time',false,null,'1','in days; 0 to force download');
		parent::AddParameter('uri_scheme',false,'provider-uri|bio2rdf-uri|identifiers.org-uri','provider-uri,bio2rdf-uri','uri scheme preference');
		parent::AddParameter('rdf_model',false,'simple|sio|ovopub|nanopub','simple','format to selected rdf data model');
//		parent::AddParameter('file_granularity',false,'dataset|record|triple','dataset','set granularity of file generation');
		parent::AddParameter('output_format',false,'nt|nt.gz','nt.gz','output format');
		parent::AddParameter('log_level',false,'error|warning|notice','warning','level at which to print log messages');
		parent::AddParameter('unregistered_ns',false,'die|skip|continue','continue','what to do if the namespace is not found in registry');
		
		if(parent::SetParameters($this->argv) == false) {
			parent::PrintParameters($this->argv);
			exit;
		}
		setLogLevelFromString($this->GetParameterValue('log_level'));
		if(parent::CreateDirectory($this->GetParameterValue('indir')) === false) exit;
		if(parent::CreateDirectory($this->GetParameterValue('outdir')) === false) exit;
		if(parent::CreateDirectory($this->GetParameterValue('registry_dir')) === false) exit;
		if(parent::GetParameterValue('graph_uri')) $this->SetGraphURI($this->GetParameterValue('graph_uri'));	
		
		$bio2rdf_dataset_version = "bio2rdf-".$this->getNamespace()."-".date("Ymd");
		$this->setDatasetURI("bio2rdf_dataset:".$bio2rdf_dataset_version);
		$this->setBio2RDFReleaseFile($bio2rdf_dataset_version.".nt");
		$this->getRegistry()->setCacheTime(parent::GetParameterValue('registry_cache_time'));
		$this->getRegistry()->setUnregisteredNSAction(parent::GetParameterValue('unregistered_ns'));
		$schemes = explode(",", parent::GetParameterValue('uri_scheme'));
		$this->getRegistry()->setURISchemePriority($schemes);
		$this->setRDFModel(parent::GetParameterValue('rdf_model'));
	}
	
	public function clear()
	{
		$this->declared = null;
		$this->getRegistry()->clearNoMatchList();
	}
	
	public function describe($s,$label,$title = null,$description = null, $lang = "en")
	{
		$buf = '';
		if(!isset($this->declared[$s])) {
			$this->declared[$s] = '';
			
			$qname = $this->getRegistry()->MapQName($s);
			$this->getRegistry()->parseQName($qname,$ns,$id);

			$buf  = $this->QQuadL($qname,"rdfs:label",$this->SafeLiteral($label)." [$qname]",$lang);
			$buf .= $this->QQuadL($qname,"dc:identifier",$qname,null,"xsd:string");
			if(isset($title) && $title != '') $buf .= $this->QQuadL($qname,"dc:title",$this->SafeLiteral($title),$lang);
			if(isset($description) && $description != '') {
				$buf .= $this->QQuadL($qname,"dc:description",$this->SafeLiteral($description),$lang);
			}
			$buf .= $this->QQuadL($qname,"bio2rdf_vocabulary:namespace",$ns,null,"xsd:string");
			$buf .= $this->QQuadL($qname,"bio2rdf_vocabulary:identifier",$this->SafeLiteral($id),null,"xsd:string");
			if( (($pos = strpos($ns,"_resource")) != FALSE)
			    || (($pos = strpos($ns,"_vocabulary")) != FALSE)) {
				
				$type = substr($ns,0,$pos)."_vocabulary";
			} else {
				$type = $ns."_vocabulary";
			}
			$buf .= $this->QQuad($qname,"rdf:type",$type.":Resource");
			$buf .= $this->QQuad($qname,"void:inDataset",$this->GetDatasetURI());
		}
		return $buf;
	}
	
	function describeIndividual($qname,$label,$title=null,$description=null,$parent=null) 
	{
		$d = $this->describe($qname,$label,$title,$description);
		if($d) {
			$d .= $this->QQuad($qname,"rdf:type","owl:NamedIndividual");
			if(isset($parent) && $parent != null) {
				$d .= $this->QQuad($qname,"rdf:type",$parent);
			}
			return $d;
		}
		return '';
	}
	
	public function describeClass($qname,$label,$title=null,$description=null,$parent=null) 
	{
		$d = $this->describe($qname,$label,$title,$description);
		if($d) {
			$d .= $this->QQuad($qname,"rdf:type","owl:Class");
			if(isset($parent) && $parent != null) {
				$d .= $this->QQuad($qname,"rdfs:subClassOf",$parent);
			}
			return $d;
		}
		return '';
	}
	
	public function describeProperty($qname,$label,$title=null,$description=null,$parent=null)
	{
		$d = $this->describe($qname,$label,$title,$description);
		if($d) {
			$d .= $this->QQuad($qname,"rdf:type","rdf:Property");
			if(isset($parent) && $parent != null) {
				$d .= $this->QQuad($qname,"rdfs:subPropertyOf",$parent);
			}
			return $d;
		}
		return '';
	}
	
	protected function describeObjectProperty($qname,$label,$title=null,$description=null,$parent=null) 
	{
		$d = $this->describe($qname,$label,$description);
		if($d) {
			$d .= $this->QQuad($qname,"rdf:type","owl:ObjectProperty");
			if(isset($parent) && $parent != null) {
				$d .= $this->QQuad($qname,"rdfs:subPropertyOf",$parent);
			}
			return $d;
			
		}
		return '';
	}
	
	protected function describeDatatypeProperty($qname,$label,$title=null,$description=null,$parent=null) 
	{
		$d = $this->describe($qname,$label,$title,$description);
		if($d) {
			$d .= $this->QQuad($qname,"rdf:type","owl:DatatypeProperty");
			if(isset($parent) && $parent != null) {
				$d .= $this->QQuad($qname,"rdfs:subPropertyOf",$parent);
			}
			return $d;
		}
		return '';
	}

	
	public function triplify($s,$p,$o,$o_type = null)
	{
		$o_desc = '';
		if($o_type == null) {
			$o_type = $this->getDatasetResourceFromQName($o,$ns);
			$o_desc = $this->describe($o_type,"$ns resource");
		}
		return $this->QQuad($s,$p,$o).$this->QQuad($o,"rdf:type",$o_type).$o_desc;
	}
	
	public function triplifyString($s,$p,$l,$dt = null,$lang=null, $o = null, $o_type = null)
	{
		if(!isset($dt)) {
			$dt = $this->guessDatatype($l);
		}
		if($this->getRDFModel() == "sio") {
			$buf = '';
			$this->getRegistry()->parseQName($s,$s_ns,$s_id);
			$this->getRegistry()->parseQName($p,$p_ns,$p_id);
			if(!isset($o_type)) { // make the o-type from the predicate
				$o_type = $s_ns."_vocabulary:".ucfirst($p_id);
				$buf .= $this->describeClass($o_type,str_replace("-"," ",$p_id));
			}
			if(!isset($o)) {
				$o = $s_ns."_resource:".md5($s.$p.$l);
				$buf .= $this->describeIndividual($o,str_replace("-"," ",$p_id),null,null,$o_type);
			}
			return $buf.$this->triplify($s,$p,$o).$this->QQuadL($o,"rdf:value",$l,$lang,$dt);
		} else if($this->getRDFModel() == "ovopub") {
			// @todo
		} else if ($this->getRDFModel() == "nanopub") {
			// @todo
		} else {
			// if $dt is set, make $o a literal
			if(isset($lang)) {
				return $this->QQuadL($s,$p,$l,$lang);
			} else if(isset($dt)) {
				return $this->QQuadL($s,$p,$l,null,$dt);
			} else {
				return $this->QQuadL($s,$p,$l,null,"xsd:string");
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
		return "xsd:string";
	}
	
	public function getDatasetResourceFromQName($qname,&$ns)
	{
		$qname = $this->getRegistry()->MapQName($qname);
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
		//$datetime = date("D M j G:i:s T Y");

		$rdf .= $this->QQuadL($dataset_uri,"rdfs:label","$dataset_name dataset by $publisher_name on $date [$dataset_uri]");
		$rdf .= $this->QQuad($dataset_uri,"rdf:type","void:Dataset");
		$rdf .= $this->QQuadL($dataset_uri,"dc:created",$date,null,"xsd:date");
		$rdf .= $this->QQuadO_URL($dataset_uri,"dc:creator",$creator_uri);
		$rdf .= $this->QQuadO_URL($dataset_uri,"dc:publisher",$publisher_uri);
		$rights = array("use-share-modify","attribution","restricted-by-source-license");
		foreach($rights AS $right) {
			$rdf .= $this->QQuadL($dataset_uri,"dc:rights",$right);
		}
		if(isset($source_license)) $rdf .= $this->QQuadO_URL($dataset_uri,"dc:license","http://creativecommons.org/by-attribution"); // @todo check
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
		$rdf .= $this->QQuad($source_uri,"rdf:type","void:Dataset");
		$rdf .= $this->QQuadO_URL($source_uri,"foaf:homepage",$source_homepage);
		$rights = $source_rights;
		if(!is_array($rights)) $rights = array($source_rights);
		foreach($rights AS $right) {
			$rdf .= $this->QQuadL($source_uri,"dc:rights",$this->GetRightsDescription($right));
		}
		if(isset($source_license)) $rdf .= $this->QQuadO_URL($source_uri,"dc:license",$source_license);
		if(isset($source_location)) $rdf .= $this->QQuadO_URL($source_uri,"rdfs:seeAlso",$source_location);
		if(isset($source_version)) $rdf .= $this->QQuadL($source_uri,"pav:version",$source_version);

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
	
		
	
	
}
