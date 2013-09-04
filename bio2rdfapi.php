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


/**
 * Bio2RDF API
 *
 * @author Michel Dumontier 
 * @version 1.0
*/
require_once(__DIR__.'/rdfapi.php');
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
	
	/** dataset file  */
	private $dataset_file = null;
	
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
        $this->setTimeZone();
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
     * Set the default timezone to work in
     */
    public function setTimeZone($tz = 'America/New_York'){
        date_default_timezone_set($tz); 
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
	
	/**
	 * Get the assigned namespace e.g. 'bind:'
	 *
	 * @output string the namespace
	 */
	public function getNamespace()
	{
		return $this->default_namespace;
	}
	
	/**
	 * Get the resource namespace e.g. 'bind_resource:'
	 * 
	 * @output string the resource namespace for the dataset
	 */
	public function getRes() {
		return $this->resource_namespace;
	}
	
	/**
	 * Get the vocabulary namespace e.g. 'bind_vocabulary:'
	 * 
	 * @output string the vocabulary namespace for the dataset
	 */
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
	
	public function writeToReleaseFile($buf)
	{
		if(!isset($this->dataset_file)) {
			$this->dataset_file = new FileFactory(parent::getParameterValue("outdir").$this->getBio2RDFReleaseFile());
		}
		$this->dataset_file->write($buf);
	}
	public function closeReleaseFile()
	{
		if(isset($this->dataset_file)) {
			$this->dataset_file->close();
		}
	}
	
	public function initialize() 
	{	
		parent::addParameter('indir',false,null,'/data/download/'.$this->getPrefix().'/','directory to download into and/or parse from');
		parent::addParameter('outdir',false,null,'/data/rdf/'.$this->getPrefix().'/','directory to place output files');
		parent::addParameter('download',false,'true|false','false','set true to download files or false to use local');
		parent::addParameter('process',false,'true|false','true','set true to process local files');
		parent::addParameter('graph_uri',false,null,null,'provide the graph uri to generate n-quads instead of n-triples');
		parent::addParameter('id_list',false,null,null,'provide a comma-separated list of URIs to process (parser may not support)');
		
		parent::addParameter('parser',false,$this->getPrefix(),$this->getPrefix(),'this Bio2RDF parser');
		parent::addParameter('registry_dir',false,null,'/data/download/registry/','directory for the local version of the regisry');
		parent::addParameter('registry_cache_time',false,null,'1','in days; 0 to force download');
		parent::addParameter('bio2rdf_release',false,null,'3','Bio2RDF release number');
		parent::addParameter('dataset_graph',false,'true|false','true','use the date versioned dataset graph uri to generate an nquad dataset description file');
		parent::addParameter('uri_scheme',false,'provider-uri|bio2rdf-uri|identifiers.org-uri','bio2rdf-uri','uri scheme preference');
		parent::addParameter('guidelines',false,'true|false','true','follow Bio2RDF guidelines');
		parent::addParameter('model',false,'simple|sio|ovopub|nanopub','simple','format to selected rdf data model');
		parent::addParameter('output_level',false,'dataset|file|record|triple','dataset','level at which to generate output files');
		parent::addParameter('output_format',false,'nt|nt.gz|nq|nq.gz','nq.gz','output format');
		parent::addParameter('log_level',false,'error|warning|notice','warning','level at which to print log messages');
		parent::addParameter('unregistered_ns',false,'die|skip|continue','continue','what to do if the namespace is not found in registry');
		
		if(parent::setParameters($this->argv) == false) {
			parent::printParameters($this->argv);
			exit;
		}
		setLogLevelFromString($this->getParameterValue('log_level'));

		//make sure in and out directories end with slash
		if(substr(parent::getParameterValue('indir'), -1) !== "/"){
			parent::setParameterValue('indir', parent::getParameterValue('indir')."/");
		}
		
		if(substr(parent::getParameterValue('outdir'), -1) !== "/"){
			parent::setParameterValue('outdir', parent::getParameterValue('outdir')."/"); 
		}
		
		if(parent::createDirectory(parent::getParameterValue('indir')) === false) {trigger_error("Could not create directory 'indir' !",E_USER_ERROR); exit;}
		if(parent::createDirectory(parent::getParameterValue('outdir')) === false) {trigger_error("Could not create directory 'outdir' !",E_USER_ERROR); exit;}
		if(parent::createDirectory(parent::getParameterValue('registry_dir')) === false) {trigger_error("Could not create directory 'registry_dir' !",E_USER_ERROR); exit;}
		
		if(parent::getParameterValue('graph_uri')) {
			parent::setGraphURI(parent::getParameterValue('graph_uri'));	
		}
		if(strstr(parent::getParameterValue('output_format'),".nt")) {parent::setParameterValue('dataset_graph',false);}
		
		$this->getRegistry()->setLocalRegistry(parent::getParameterValue('registry_dir'));
		$this->getRegistry()->setCacheTime(parent::getParameterValue('registry_cache_time'));
		$this->getRegistry()->setUnregisteredNSAction(parent::getParameterValue('unregistered_ns'));
		$this->getRegistry()->setURISchemePriority(explode(",", parent::getParameterValue('uri_scheme')));
		$this->setRDFModel(parent::getParameterValue('model'));
		$this->setFollowBio2RDFGuidelines( (parent::getParameterValue('guidelines')=="true"?true:false) );
		
		// check namespace validity against registry
		if(!$this->getRegistry()->isPrefix($this->getPrefix())) {
			trigger_error("Invalid namespace ".$this->getPrefix(),E_USER_WARNING);
		}
	
		$bio2rdf_release_file = "bio2rdf-".$this->getPrefix().".nq";
		$bio2rdf_dataset_uri  = "bio2rdf_dataset:bio2rdf-".$this->getPrefix()."-".date("Ymd");
		$this->setDatasetURI($bio2rdf_dataset_uri);
		$this->setBio2RDFReleaseFile($bio2rdf_release_file);
		
		// setup the default dataset graph
		if(parent::getParameterValue('dataset_graph') == true && parent::getGraphURI() == '') {
			parent::setGraphURI($this->getDatasetURI());
		} 
		
		if(parent::getParameterValue('dataset_graph') == false && parent::getGraphURI() == '') {
			$gz = (strstr(parent::getParameterValue('output_format'),"gz")?".gz":"");
			parent::setParameterValue('output_format','nt'.$gz);
		}		
	}
	
	public function clear()
	{
		$this->declared = null;
		$this->getRegistry()->clearNoMatchList();
	}

	/** this function generates the basic entity metadata only once unless the cache is cleared */
	public function declareEntity($qname)
	{
		$buf = '';
		if(!isset($this->declared[$qname])
			&& !strstr($qname,"://")
			&& $this->getFollowBio2RDFGuidelines() == true) {

			$my_qname = $this->getRegistry()->mapQName($qname);
			$this->declared[$my_qname] = true;
			$this->getRegistry()->parseQName($my_qname,$ns,$id);
			
			$buf .= $this->QQuadL($my_qname,"dc:identifier",$my_qname,null,"xsd:string");
			$buf .= $this->QQuadL($my_qname,"bio2rdf_vocabulary:namespace",$ns,null,"xsd:string");
			$buf .= $this->QQuadL($my_qname,"bio2rdf_vocabulary:identifier",$id,null,"xsd:string");
			if( (($pos = strpos($ns,"_resource")) !== FALSE)
				|| (($pos = strpos($ns,"_vocabulary")) !== FALSE)) {
				$type = substr($ns,0,$pos)."_vocabulary";
			} else {
				$type = $ns."_vocabulary";
			}
			// identifiers.org
			// @todo replace with identifiers.org types e.g http://identifiers.org/clinicaltrials/resource
			$buf .= $this->QQuad($my_qname,"rdf:type","$type:Resource");
			$buf .= $this->QQuad($my_qname,"void:inDataset",$this->getDatasetURI());
		}
		return $buf;
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
			$buf .= $this->declareEntity($qname);
		}
		if(!isset($this->declared['d'.$qname])) {
			$this->declared['d'.$qname] = true;

			if(strstr($qname, '://')){
				$my_qname = $qname;
			} else {
				$my_qname = $this->getRegistry()->mapQName($qname);
				$this->getRegistry()->parseQName($my_qname,$ns,$id);
			}
			
			$buf  .= $this->QQuadL($my_qname,"rdfs:label",$label." [$my_qname]",$lang);
			if(isset($title) && $title != '') $buf .= $this->QQuadL($my_qname,"dc:title",$title,$lang);
			if(isset($description) && $description != '') {
				$buf .= $this->QQuadL($my_qname,"dc:description",$description,$lang);
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

	/**
	 * function to simply generate annotated triples
	 * @param string $s the subject qname (ns:id)
	 * @param string $p the predicate qname (ns:id)
	 * @param string $o the object qname (ns:id)
	 * @param string $o_parent optional set parent uri of the object
	 * @param string $class assert that the object is a subclass (true) or instance (false; default)
	 */
	public function triplify($s,$p,$o,$o_parent=null,$class = false)
	{
		$buf = '';
		// see if we can get the fast description of the predicate and type removing a dash
		if(strstr($p,"_vocabulary")) {
			$a = explode(":",$p,2);
			$p_label = str_replace("-"," ",$a[1]);
			$buf .= $this->describeObjectProperty($p,$p_label);
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
		$buf .= $this->declareEntity($s).$this->declareEntity($p).$this->declareEntity($o);
		return $this->QQuad($s,$p,$o).$buf;
	}
	
	
	public function triplifyString($s,$p,$l,$dt=null,$lang=null,$o=null,$o_type=null)
	{
		$buf = $this->declareEntity($s).$this->declareEntity($p);
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
				$o = $s_ns."_resource:".md5($p.$l);
				$buf .= $this->describeIndividual($o,$l,$o_type);
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
				$buf .= $this->describeDatatypeProperty($p,$p_label);
			}
			
			if(isset($lang) && $lang != '') {
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
		// @todo complete this functionality
		return true;
		
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
		if($level == parent::getParameterValue('output_level')
		  && in_array(parent::getParameterValue('output_format'), array('nq','nq.gz')))
		{
			// generate the graph uri
			if(parent::getGraphURI() == '') {
				// generate one
				$uuid  = uniqid('',true);
				$uri = $this->getRes().$uuid;
				parent::setGraphURI($uri);
			}

			// @todo open new files
		}
	}
}
