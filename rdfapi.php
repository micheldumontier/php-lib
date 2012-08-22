<?php
/**
Copyright (C) 2012 Michel Dumontier

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
 * An RDF API for PHP
 * @version 1.0
 * @author Michel Dumontier
 * @description 
*/
require_once('application.php');
require_once('ns.php');

class RDFFactory extends Application
{
	private $buf = '';
	private $ns = null;
	private $types = null;
	private $read_file=null;
	private $write_file=null;
	private $graph_uri = null;
	private $dataset_uri = null;
	
	function __construct()
	{
		parent::__construct();
		$this->ns = new CNamespace();
	}
	
	function GetNS() {return $this->ns;}
	
	function GetRDF() {return $this->buf;}
	function AddRDF($buf) {$this->buf .= $buf;return TRUE;}
	function DeleteRDF() {$this->buf = '';return TRUE;}

	function SetGraphURI($graph_uri) {$this->graph_uri = $graph_uri;}
	function GetGraphURI() {return $this->graph_uri;}
	
	function SetReadFile($file,$gzcompress=false)
	{
		$this->read_file = new FileFactory($file,$gzcompress);
		return $this->read_file;
	}
	function GetReadFile()
	{	
		return $this->read_file;
	}
	function WriteFileExists()
	{
		if(isset($this->write_file)) return TRUE;
		return FALSE;
	}
	function SetWriteFile($file,$gzcompress=false)
	{
		$this->write_file = new FileFactory($file,$gzcompress);
		return $this->write_file;
	}
	function GetWriteFile()
	{
		return $this->write_file;
	}
	function WriteRDFBufferToWriteFile() 
	{
		if($this->WriteFileExists() === FALSE) {
			trigger_error("Write file not set!");
			return FALSE;
		} 
		$this->GetWriteFile()->Write($this->buf);
		$this->DeleteRDF();
		return TRUE;
	}
	
	function Quad($s_uri, $p_uri, $o_uri, $g_uri = null)
	{
		$graph_uri = '';
		if(isset($g_uri)) $graph_uri = "<$g_uri>";
		elseif(isset($this->graph_uri)) $graph_uri = "<".$this->graph_uri.">";
		
		return "<$s_uri> <$p_uri> <$o_uri> $graph_uri .".PHP_EOL;
	}

	function QuadL($s_uri, $p_uri, $literal, $lang = null, $lt_uri = null, $g_uri = null)
	{
		$graph_uri = '';
		if(isset($g_uri)) $graph_uri = "<$g_uri>";
		elseif(isset($this->graph_uri)) $graph_uri = "<".$this->graph_uri.">";

		if(isset($lang) && isset($lt_uri)) {
			trigger_error("Literal can only hold a language tag *or* datatype", E_USER_ERROR);
			return FALSE;
		}
		return "<$s_uri> <$p_uri> \"$literal\"".(isset($lang)?"@$lang ":'').(isset($lt_uri)?"^^<$lt_uri>":'')." $graph_uri .".PHP_EOL;
	}
	
	function QuadText($s_uri, $p_uri, $text, $lang = null, $g_uri = null)
	{
		$graph_uri = '';
		if(isset($g_uri)) $graph_uri = "<$g_uri>";
		elseif(isset($this->graph_uri)) $graph_uri = "<".$this->graph_uri.">";
		
		if(isset($lang) && isset($lt_uri)) {
			trigger_error("Literal can only hold a language tag *or* datatype", E_USER_ERROR);
			return FALSE;
		}
		return "<$s_uri> <$p_uri> \"\"\"$text\"\"\"".(isset($lang)?"@$lang ":'')." $graph_uri .".PHP_EOL;
	}
	
	
	function QQuad($s,$p,$o,$g = null)
	{
		$s_uri = $this->ns->getFQURI($s);
		$p_uri = $this->ns->getFQURI($p);
		$o_uri = $this->ns->getFQURI($o);
		$g_uri = null;
		if(isset($g)) $g_uri = $this->ns->getFQURI($g);
		
		return $this->Quad($s_uri,$p_uri,$o_uri,$g_uri);
	}
	
	function QQuadL($s,$p,$l,$lang=null,$lt=null,$g=null)
	{
		$s_uri = $this->ns->getFQURI($s);
		$p_uri = $this->ns->getFQURI($p);
		
		$lt_uri = null;
		if(isset($lt)) $lt_uri = $this->ns->getFQURI($lt);		
		$g_uri = null;
		if(isset($g)) $g_uri = $this->ns->getFQURI($g);
		
		return $this->QuadL($s_uri,$p_uri,$l,$lang,$lt_uri,$g_uri);		
	}
	
	function QQuadText($s,$p,$l,$lang=null,$g=null)
	{
		$s_uri = $this->ns->getFQURI($s);
		$p_uri = $this->ns->getFQURI($p);
		
		$lt_uri = null;
		if(isset($lt)) $lt_uri = $this->ns->getFQURI($lt);		
		$g_uri = null;
		if(isset($g)) $g_uri = $this->ns->getFQURI($g);
		
		return $this->QuadText($s_uri,$p_uri,$l,$lang,$g_uri);		
	}
	
	function QQuadO_URL($s,$p,$o_uri,$g=null) 
	{
		$s_uri = $this->ns->getFQURI($s);
		$p_uri = $this->ns->getFQURI($p);
		$g_uri = null;
		if(isset($g)) $g_uri = $this->ns->getFQURI($g);
		
		return $this->Quad($s_uri,$p_uri,$o_uri,$g_uri);
	}
	
	function SafeLiteral($s)
	{
		return str_replace(array("\r","\n",'"'),array('','\n','\"'), $s);
	}
	
	function GetBio2RDFDatasetFile($dataset)
	{
		$date = date("Ymd");
		$name = "bio2rdf-$dataset-$date";
		$this->SetDatasetURI("bio2rdf_dataset:$name");
		return $name.".ttl";
	}
	
	function SetDatasetURI($dataset_uri)
	{		
		$this->dataset_uri = $dataset_uri;
	}
	function GetDatasetURI()
	{
		return $this->dataset_uri;
	}
	
	function GetDatasetDescription($dataset_uri, $dataset_name, $publisher_name, $creator_uri, 
				$publisher_uri, $data_url = null, $sparql_endpoint = null, 
				$source_homepage, $source_rights, $source_license = null)
	{
		$rdf = '';
		$date = date("Y-m-d");
		//$datetime = date("D M j G:i:s T Y");

		$rdf .= $this->QQuadL($dataset_uri,"rdfs:label","$dataset_name dataset by $publisher_name [$dataset_uri]");
		$rdf .= $this->QQuad($dataset_uri,"rdf:type","void:Dataset");
		$rdf .= $this->QQuadL($dataset_uri,"dc:created",$date,null,"xsd:date");
		$rdf .= $this->QQuadO_URL($dataset_uri,"dc:creator",$creator_uri);
		$rdf .= $this->QQuadO_URL($dataset_uri,"dc:publisher",$publisher_uri);
		if(isset($data_url)) $rdf .= $this->QQuadO_URL($dataset_uri,"void:dataDump",$data_url);
		if(isset($sparql_endpoint)) $rdf .= $this->QQuadO_URL($dataset_uri,"void:sparqlEndpoint",$sparql_endpoint);
  
		// source description
		$source_uri = "bio2rdf_dataset:$dataset_name";
		$rdf .= $this->QQuad($dataset_uri,"dc:source",$source_uri);
		$rdf .= $this->QQuadL($source_uri,"rdfs:label","$dataset_name dataset [$source_uri]");
		$rdf .= $this->QQuad($source_uri,"rdf:type","void:Dataset");
		$rdf .= $this->QQuadO_URL($source_uri,"foaf:homepage",$source_homepage);
		$rights = $source_rights;
		if(!is_array($rights)) $rights = array($source_rights);
		foreach($rights AS $right) {
			$rdf .= $this->QQuadL($source_uri,"dc:rights",$this->GetRightsDescription($right));
		}
		if(isset($source_license)) $rdf .= $this->QQuadO_URL($source_uri,"dc:license",$source_license);

		return $rdf;
	}
	
	function GetBio2RDFDatasetDescription($namespace, $script_url, $filename, $source_homepage, $source_rights, $source_license = null)
	{
		return $this->GetDatasetDescription(				
				$this->GetDatasetURI(), $namespace, "Bio2RDF.org", $script_url, 
				"http://bio2rdf.org", "http://download.bio2rdf.org/rdf/$namespace/$filename", "http://$namespace.bio2rdf.org/sparql", 
				$source_homepage, $source_rights, $source_license);
	}
	
	function GetRightsDescription($right)
	{
		$rights = array(
			"use" => "free to use"
			"use-share" => "free to use and share, no derivatives",
			"use-share-modify" => "free to use, share, modify",			
			"no-commercial" => "commercial use requires licensing",
		);
		if(!isset($rights[$right])) {
			trigger_error("Unable to find $right in ".implode(",",keys($rights))." of rights");
			return FALSE;
		}
		return $rights[$right];
	}
}

