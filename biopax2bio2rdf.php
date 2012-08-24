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

require_once('../../php-lib/rdfapi.php');
require_once('../../arc2/ARC2.php'); // available on git @ https://github.com/semsol/arc2.git
	
/**
 * BioPAX2Bio2RDF RDFizer library
 * @version 1.0
 * @author Michel Dumontier
*/
Class BioPAX2Bio2RDF extends RDFFactory
{
	private $file = null;
	private $buf = null;
	private $biopax = null;
	private $base_ns = null;
	private $bio2rdf_ns = null;
	
	function __construct()
	{
	
	}
	
	function SetFile($file)
	{
		$this->file = $file;
		return $this;
	}
	
	function SetBuffer($buf)
	{
		$this->buf = $buf;
		return $this;
	}
	function SetBioPAXVersion($version)
	{
		if($version < 1 || $version  > 3) {
			trigger_error("Invalid BioPAX version $version");
			return FALSE;
		}
		$this->biopax_ns = "http://www.biopax.org/release/biopax-level".$version.".owl#";
		if($version == 1 || $version == 2) {
			$this->biopax['db'] = $this->biopax_ns."DB";
			$this->biopax['id'] = $this->biopax_ns."ID";
			$this->biopax['xref'] = $this->biopax_ns."XREF";
			$this->biopax['name'] = $this->biopax_ns."DISPLAY-NAME";
			$this->biopax['term'] = $this->biopax_ns."TERM";
			$this->biopax['unificationXref'] = $this->biopax_ns."UnificationXref";
			$this->biopax['relationshipXref'] = $this->biopax_ns."RelationshipXref";
			$this->biopax['publicationXref'] = $this->biopax_ns."PublicationXref";
		} else if($version == 3) {
			$this->biopax['db'] = $this->biopax_ns."db";
			$this->biopax['id'] = $this->biopax_ns."id";
			$this->biopax['xref'] = $this->biopax_ns.'xref';
			$this->biopax['name'] = $this->biopax_ns."displayName";
			$this->biopax['term'] = $this->biopax_ns."term";
			$this->biopax['unificationXref'] = $this->biopax_ns."unificationXref";
			$this->biopax['relationshipXref'] = $this->biopax_ns."relationshipXref";
			$this->biopax['publicationXref'] = $this->biopax_ns."publicationXref";
		}
		return $this;
	}
	
	function SetBaseNamespace($base_ns)
	{
		$this->base_ns = $base_ns;
		return $this;
	}
	function SetBio2RDFNamespace($bio2rdf_ns)
	{
		$this->bio2rdf_ns = $bio2rdf_ns;
		return $this;
	}
	
	function Parse()
	{
		$parser = ARC2::getRDFXMLParser();
		
		if(isset($this->file)) {
			$parser->parse('file://'.$this->file);
		} else if(isset($this->buf)) {
			$parser->parse($this->base_ns, $this->buf);
		} else {
			trigger_error("Set a file or buffer to parse", E_USER_ERROR);
			exit;
		}
		
		$nso = new CNamespace();
		$rdf_type = $nso->GetFQURI("rdf:type");
		$url  = $nso->GetFQURI("bio2rdf_vocabulary:url");
		
		$triples = $parser->getTriples();
		foreach($triples AS $i => $a) {
			$o['value'] = $a['o'];
			$o['type'] = $a['o_type'];
			$o['datatype'] = $a['o_datatype'];
			$index[$a['s']][$a['p']][] = $o;
		}
		
		$rdf = '';
		foreach($index AS $s => $p_list) {
			preg_match("/http\:\/\/identifiers\.org\/([^\/]+)\/(.*)/",$s,$m);
			if(isset($m[1])) {
				$ns = $m[1];
				$id = $m[2];
				if($ns == "biomodels.db") {
					$id = str_replace("/","_",$m[2]);
				}
				$nso->ParseQName($id,$ns2,$id2);
				if($ns2) {
					$id = $id2;
				}
				$s = "http://identifiers.org/$ns/$id";
				$s_uri = $nso->GetFQURI($nso->MapQName("$ns:$id"));
			} else {
				$s_uri = str_replace($this->base_ns,$this->bio2rdf_ns,$s);
			}
			if($s[0] != '_' && $s != $s_uri) $rdf .= $this->Quad($s,$nso->GetFQURI("owl:sameAs"),$s_uri);
		
			if(isset($p_list[$this->biopax['db']]) && isset($p_list[$this->biopax['id']])) {		
				$db = $p_list[$this->biopax['db']][0]['value'];
				$id = $p_list[$this->biopax['id']][0]['value'];
				if(!$db || !$id) continue;
		
				// get rid of additional prefix in the identifiers
				$nso->ParseQName($id,$ns2,$id2);
				if($ns2) $id = $id2;
				
				// map the db to the registry
				$qname = $nso->MapQName("$db:$id");
				$o_uri = $nso->getFQURI($qname);
				
				// $rdf .= $this->Quad($s_uri,$url,$o_uri);				
				if(isset($p_list[$rdf_type][0]['value'])) {
					$type = $p_list[$rdf_type][0]['value'];
					if($type == $this->biopax['unificationXref']) {
						$rdf .= $this->Quad($s_uri,$nso->GetFQURI("biopax_vocabulary:identical-to"),$o_uri);
					} elseif($type == $this->biopax['relationshipXref']) {
						$rdf .= $this->Quad($s_uri,$nso->GetFQURI("biopax_vocabulary:related-to"),$o_uri);
					} elseif($type == $this->biopax['publicationXref']) {
						$rdf .= $this->Quad($s_uri,$nso->GetFQURI("biopax_vocabulary:identical-to"),$o_uri);
					}
				} 
			} // isset
			
			// add an rdfs:label
			if(isset($p_list[$this->biopax['name']])) {
				$rdf .= $this->QuadL($s_uri,$nso->GetFQURI("rdfs:label"),$p_list[$this->biopax['name']][0]['value']);
			}
			if(isset($p_list[$this->biopax['term']])) {
				$rdf .= $this->QuadL($s_uri,$nso->GetFQURI("rdfs:label"),$p_list[$this->biopax['term']][0]['value']);
			}
			
			foreach($p_list AS $p => $o_list) {
				
				foreach($o_list AS $o) {
					if($o['type'] == 'uri') {
						$o_uri = str_replace($this->base_ns,$this->bio2rdf_ns,$o['value']);				
						$rdf .= $this->Quad($s_uri,$p ,$o_uri);
					} else {
						// literal
						$literal = $this->SafeLiteral($o['value']);
						$datatype = null;
						if(isset($o['datatype']) && $o['datatype'] != '') {
							if(strstr($o['datatype'],"http://")) {
								$datatype = $o['datatype'];
							} else {
								$datatype = $nso->GetFQURI($o['datatype']);
							}
						}
						$rdf .= $this->QuadL($s_uri,$p,$literal,null,$datatype);
					}
				} // foreach o_list
			} // foreach p_list
		} // foreach index
		
		return $rdf;
	}
} // class