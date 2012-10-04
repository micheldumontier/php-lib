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
	private $dataset_uri = null;
	private $declared = null;
	
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
			$this->biopax['comment'] = $this->biopax_ns."COMMENT";
			$this->biopax['unificationXref'] = $this->biopax_ns."UnificationXref";
			$this->biopax['relationshipXref'] = $this->biopax_ns."RelationshipXref";
			$this->biopax['publicationXref'] = $this->biopax_ns."PublicationXref";
			$this->biopax['Xref'] = $this->biopax_ns."Xref";
		} else if($version == 3) {
			// relations
			$this->biopax['db'] = $this->biopax_ns."db";
			$this->biopax['id'] = $this->biopax_ns."id";
			$this->biopax['xref'] = $this->biopax_ns.'xref';
			$this->biopax['name'] = $this->biopax_ns."displayName";
			$this->biopax['term'] = $this->biopax_ns."term";
			$this->biopax['comment'] = $this->biopax_ns."comment";
			// types
			$this->biopax['unificationXref'] = $this->biopax_ns."UnificationXref";
			$this->biopax['relationshipXref'] = $this->biopax_ns."RelationshipXref";
			$this->biopax['publicationXref'] = $this->biopax_ns."PublicationXref";
			$this->biopax['Xref'] = $this->biopax_ns."Xref";
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
	function SetDatasetURI($dataset_uri)
	{
		$this->dataset_uri = $dataset_uri;
		return $this;
	}
	
	function Parse()
	{
		$this->declared = null;
		
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
			$s = str_replace(" ","",$a['s']);
			if($a['s_type'] == 'bnode') {
				// make a uri
				$s = $this->bio2rdf_ns.substr($s,2);
			}
			$o['type'] = $a['o_type'];
			$o['datatype'] = $a['o_datatype'];
			$o['value'] = $a['o'];
			if($a['o_type'] == 'uri') {
				$o['value'] = str_replace(" ","",$a['o']);
			}
			if($a['o_type'] == 'bnode') {
				$o['type'] = 'uri';
				$o['value'] = $this->bio2rdf_ns.substr($a['o'],2);
			}
			$index[$s][$a['p']][] = $o;
		}
		if(!isset($index)) return '';
		
		// generate the bio2rdf / identifiers.org xrefs
		foreach($index AS $s => $p_list) {
			foreach($p_list AS $p => $o_list) {

				// find and reformat xrefs
				if($p == $this->biopax['xref']) {
					foreach($o_list AS $i => $o) {
						$o_uri = $o['value'];
						
						// check if we have the normalized id
						if(isset($xrefs[$o_uri])) {
							$new_uri = $xrefs[$o_uri];
						} else {
							$xref_obj = $index[$o_uri];
						
							// generate the id
							if(!isset($xrefs[$o_uri])) {
								if(!isset($xref_obj[$this->biopax['id']][0]['value'])) {
									continue;
								}
								$id_string = $xref_obj[$this->biopax['id']][0]['value'];
								$nso->ParseQName($id_string,$db,$id);
								if(!$db) {
									if(isset($xref_obj[$this->biopax['db']][0]['value'])) {
										$db =  $xref_obj[$this->biopax['db']][0]['value'];
									} else {
										// badly formed biopax
										continue;
									}
								}
								if($db == "ICD") $db = "icd9";
								$qname = $nso->MapQName("$db:$id");
								$nso->ParseQName($qname,$db,$id);
								if($db == "go") {
									if(!is_numeric($id[0])) {
										echo "skipping non-numeric GO identifier: $id".PHP_EOL;
										continue;
									}
								}
								$new_uri = $nso->getFQURI($qname);
								
								// set the new uri
								$xrefs[$o_uri] = $new_uri;

								// add to the index
								$o = '';
								$o['value'] = $db;
								$o['type'] = 'literal';
								$o['datatype'] = '';
								$index[$new_uri][$this->biopax['db']][] = $o;
								
								$o = '';
								$o['value'] = $id;
								$o['type'] = 'literal';
								$o['datatype'] = 'http://www.w3.org/2001/XMLSchema#string';
								$index[$new_uri][$this->biopax['id']][] = $o;
								
								$o = '';
								$o['value'] = "$db:$id";
								$o['type'] = 'literal';
								$o['datatype'] = 'http://www.w3.org/2001/XMLSchema#string';
								$index[$new_uri][$nso->GetFQURI("dc:identifier")][] = $o;
								
							} else $new_uri = $xrefs[$o_uri];
						
							// now determine the nature of the relation
							$type = $xref_obj[$rdf_type][0]['value'];
							if($type == $this->biopax['unificationXref']) {
								$rel = $nso->GetFQURI("biopax_vocabulary:identical-to");
							} elseif($type == $this->biopax['relationshipXref']) {
								$rel = $nso->GetFQURI("biopax_vocabulary:related-to");
							} elseif($type == $this->biopax['publicationXref']) {
								$rel = $nso->GetFQURI("biopax_vocabulary:publication");
							} elseif($type == $this->biopax['Xref']) {
								$rel = $nso->GetFQURI("biopax_vocabulary:related-to");
							}						
							 
							// unset the old ref and put in the new one
							unset($index[$o_uri]);
							unset($index[$s][$p][$i]);
							if(count($index[$s][$p]) == 0) {
								unset($index[$s][$p]);
							}
							
							$o = '';
							$o['value'] = $new_uri;
							$o['type'] = 'uri';
							$o['datatype'] = '';
							$index[$s][$rel][] = $o;
						} // if
					} // foreach o_list
				} // if
			} // foreach p_list
		} // foreach index

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
				if(!$s_uri) {
					continue;
				}
			}
			if(isset($this->dataset_uri)) {
				$rdf .= $this->Quad($s_uri,$nso->GetFQURI("void:inDataset"),$nso->GetFQURI($this->dataset_uri));
			}
			
			if($s[0] != '_' && $s != $s_uri) $rdf .= $this->Quad($s_uri,$nso->GetFQURI("owl:sameAs"),$s);
			
			// add an rdfs:label
			if(isset($p_list[$this->biopax['name']][0]['value'])) {
				$label = $p_list[$this->biopax['name']][0]['value'];
				if($label) $rdf .= $this->QuadL($s_uri,$nso->GetFQURI("rdfs:label"),$label);
			}
			if(isset($p_list[$this->biopax['term']][0]['value'])) {
				$label = $p_list[$this->biopax['term']][0]['value'];
				if($label) $rdf .= $this->QuadL($s_uri,$nso->GetFQURI("rdfs:label"),$label);
			}
			
			foreach($p_list AS $p => $o_list) {
				
				foreach($o_list AS $o) {
					if($o['type'] == 'uri') {
						$o_uri = str_replace($this->base_ns,$this->bio2rdf_ns,$o['value']);				
						$rdf .= $this->Quad($s_uri,$p,$o_uri);
						if(!isset($this->declared[$p])) {
							$this->declared[$p] = '';
							$rdf .= $this->Quad($p,$nso->GetFQURI("rdf:type"), $nso->GetFQURI("owl:ObjectProperty"));
						}
					} elseif($o['type'] == 'bnode') {
						$rdf .= $this->Quad($s_uri,$p ,$o['value']);
						if(!isset($this->declared[$p])) {
							$this->declared[$p] = '';
							$rdf .= $this->Quad($p,$nso->GetFQURI("rdf:type"), $nso->GetFQURI("owl:ObjectProperty"));
						}
					} else if($o['type'] == 'literal') {
						$literal = $this->SafeLiteral($o['value']);
						if($literal == '') continue;
						
						if(!isset($this->declared[$p])) {
							$this->declared[$p] = '';
							if($p == $this->biopax['comment'] || $p == $nso->GetFQURI("dc:identifier")) {
								$rdf .= $this->Quad($p,$nso->GetFQURI("rdf:type"), $nso->GetFQURI("owl:AnnotationProperty"));
							} else $rdf .= $this->Quad($p,$nso->GetFQURI("rdf:type"), $nso->GetFQURI("owl:DatatypeProperty"));
						}
						
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
