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
 * Namespaces
 * @version 1.0
 * @author Michel Dumontier
 * @description 
*/

class CNamespace
{
  const bio2rdf_uri = "http://bio2rdf.org/";
  private $all_ns = '';
  
  private $base_ns = array(
	'xsd'  => array('name'=>'XML Schema', 'uri' => 'http://www.w3.org/2001/XMLSchema#'),
	'rdf'  => array('name'=>'RDF','uri'=>'http://www.w3.org/1999/02/22-rdf-syntax-ns#'),
	'rdfs' => array('name'=>'RDF Schema','uri'=>'http://www.w3.org/2000/01/rdf-schema#'),
	'owl'  => array('name'=>'Web Ontology Language','uri' => 'http://www.w3.org/2002/07/owl#'),
	'dc'   => array('name'=>'Dublin Core','uri' =>'http://purl.org/dc/terms/'),
	'skos' => array('name'=>'SKOS','uri'=>'http://www.w3.org/2004/02/skos/core#'),
	'foaf' => array('name'=>'FOAF','uri'=>'http://xmlns.com/foaf/0.1/'),
	'sio'  => array('name'=>'Semanticscience Integrated Ontology','uri'=>'http://semanticscience.org/resource/'),
  );

  private $ext_ns = array(
  //'id' => array('name' => '','description' => '','uri' => '', 'synonyms' => array(), 'type' => 'data','classification','part-of' => 'ns'),
    'bio2rdf'   => array('name' => 'Bio2RDF'),
	'ahfs'		=> array('name' => 'Pharmacologic-Therapeutic Classification System'),
	'afcs'      => array('name' => 'UCSD Signaling Gateway'),
	'apo'       => array('name' => 'Ascomycetes Phenotype Ontology', ),
	'atc'       => array('name' => 'Anatomical Therapeutic Chemical Classification', 'type' => 'classification'),
	'bind'      => array('name' => 'Biomolecular Interaction Database'),
	'bindingdb' => array('name' => 'BindingDB'),
	'biogrid'   => array('name' => 'BioGrid Interaction Database'),
	'blastprodom' => array('name' => ''),
	'candida'   => array('name' => ''),
	'cas'       => array('name' => 'Chemical Abstracts Service'),
	'chebi'     => array('name' => 'Chemical Entities of Biological Interest','part-of'=>'ebi'),
	'chemspider' => array('name' => 'ChemSpider'),
	'coil'      => array('name' => ''),
	'corum'     => array('name' => ''),
	'ctd'       => array('name' => 'Comparative Toxicogenomics Database'),
	'cygd'      => array('name' => ''),
	'dbsnp'     => array('name' => 'dbSNP','part-of'=>'ncbi'),
	'dip'       => array('name' => 'Database of Interacting Proteins'),
	'ddbj'      => array('name' => 'DDBJ sequence database'),
	'dpd'		=> array('name' => 'Drugs Product Database'),
	'drugbank'  => array('name' => 'DrugBank'),
	'ec'        => array('name' => 'Enzyme Classiciation'),
	'embl'      => array('name' => 'EMBL sequence database'),
	'ensembl'   => array('name' => 'EnsEMBL genomic database'),
	'eco'       => array('name' => 'Evidence Code Ontology'),
	'euroscarf' => array('name' => ''),
	'flybase'   => array('name' => 'FlyBase'),
	'fprintscan' => array('name' => ''),
	'kegg'      => array('name' => 'KEGG','synonyms' => array('KEGG Compound','KEGG Drug')),
	'genatlas'	=> array('name' => 'GenAtlas'),
	'genbank'	=> array('name' => 'GenBank'),
	'genecards'	=> array('name' => 'GeneCards'),
	'gene3d'    => array('name' => ''),
	'geneid'    => array('name' => '', 'synonyms' => array('geneid','entrez gene')),
	'germonline' => array('name' => ''),
	'go'        => array('name' => 'Gene Ontology'),
	'gp'        => array('name' => 'NCBI Genome database','part-of'=>'ncbi'),
	'gtp'		=> array('name' => 'Guide to Pharmacology'),
	'hprd'      => array('name' => 'Human Protein Reference Database'),
	'hgnc'		=> array('name' => 'HUGO Gene Nomenclature Committee (HGNC)'),
	'innatedb'  => array('name' => ''),
	'intact'    => array('name' => 'Intact Interaction Database'),
	'ipi'       => array('name' => 'International Protein Index'),
	'irefindex'         => array('name' => 'iRefIndex','part-of' => 'irefindex'),
	'irefindex_rogid'   => array('name' => 'iRefIndex','part-of' => 'irefindex'),
	'irefindex_rigid'   => array('name' => 'iRefIndex','part-of' => 'irefindex'),
	'irefindex_irigid'  => array('name' => 'iRefIndex','part-of' => 'irefindex'),
	'irefindex_crigid'  => array('name' => 'iRefIndex','part-of' => 'irefindex'),
	'irefindex_crogid'  => array('name' => 'iRefIndex','part-of' => 'irefindex'),
	'irefindex_icrogid' => array('name' => 'iRefIndex','part-of' => 'irefindex'),
	'irefindex_icrigid' => array('name' => 'iRefIndex','part-of' => 'irefindex'),
	'iuphar'		=> array('name' => 'iuphar'),
	'iupharligand' => array('name' => '','part-of'=>'iuphar'),
	'matrixdb'  => array('name' => ''),
	'mesh' => array('name' => ''),
	'metacyc' => array('name' => ''),
	'mi' => array('name' => ''),
	'mint' => array('name' => ''),
	'mips' => array('name' => ''),
	'mpact' => array('name' => ''),
	'mpi' => array('name' => ''),
	'ncbi' => array('name' => ''),
	'ndc' => array('name' => 'National Drug Code Directory'),
	'refseq' => array('name' => '','part-of' => 'ncbi'),
	'obo' => array('name' => ''),
	'omim' => array('name' => ''),
	'ophid' => array('name' => ''),
	'patternscan' => array('name' => ''),
	'pato' => array('name' => ''),
	'panther' => array('name' => ''),
	'pfam' => array('name' => 'Protein Families'),
	'pharmgkb' => array('name' => 'PharmGKB knowledge base'),
	'pir'=> array('name' => 'Protein Information Resource'),
	'prf'=> array('name' => ''),
	'prodom'=> array('name' => ''),
	'profilescan'=> array('name' => ''),
	'pdb'=> array('name' => 'Protein Databank'),
	'pubmed'=> array('name' => 'PubMed'),
	'pmc'=>array('name'=>'PubMed Central'),
	'pubchemcompound'=> array('name' => '', 'synonyms' => array('PubChem Compound')),
	'pubchemsubstance'=> array('name' => '', 'synonyms' => array('PubChem Substance')),
	'reactome'=> array('name' => ''),
	'registry'=> array('name' => ''),
	'registry_dataset'=> array('name' => ''),
	'seg'=> array('name' => ''),
	'sgd'=> array('name' => ''),
	'smart'=> array('name' => 'SMART'),
	'snomed'=> array('name' => ''),
	'so'=> array('name' => 'Sequence Ontology'),
	'superfamily'=> array('name' => ''),
	'swissprot'=> array('name' => 'SwissProt'),
	'taxon'=> array('name' => '','synonyms'=>array('taxon','ncbitaxon'),'part-of' => 'irefindex'),
	'tcdb'=> array('name' => ''),
	'tigr'=> array('name' => 'TIGR'),
	'tpg'=> array('name' => ''),
	'trembl'=> array('name' => 'TrEMBL'),
	'umls'=> array('name' => 'UMLS'),
	'uniparc'=> array('name' => 'UniParc','part-of' => 'uniprot'),
	'uniprot'=> array('name' => 'UniProt','part-of' => 'uniprot'),
	'uniref'=> array('name' => 'UniRef','part-of' => 'uniprot'),
	'uo'=> array('name' => 'Unit Ontology'));
	
	function __construct()
	{
		foreach($this->ext_ns AS $ns => $obj) {
			$this->ext_ns[$ns]['uri'] =  self::bio2rdf_uri.$ns.':';
			$this->ext_ns[$ns.'_vocabulary']['uri'] =  self::bio2rdf_uri.$ns.'_vocabulary:';
			$this->ext_ns[$ns.'_resource']['uri'] =  self::bio2rdf_uri.$ns.'_resource:';
		}
		$this->all_ns = array_merge($this->base_ns,$this->ext_ns);		
   }
   
	function isNS($ns)
	{
		if(!isset($this->all_ns[$ns])) return false;
		return TRUE;
	}
	
	function getNSURI($ns)
	{
		if($this->isNS($ns)) {
			if($this->all_ns[$ns]['uri']) return $this->all_ns[$ns]['uri'];
		}
		return FALSE;
	}
	
	function ParsePrefixedName($prefixed_name,&$ns,&$id)
	{
		$a = explode(":",$prefixed_name,2);
		if(count($a) == 1) {
			// there is no prefix
			$ns = null;
			$id = $prefixed_name;
		} else {
			$ns = strtolower($a[0]);
			$id = $a[1];
		}
		return TRUE;
	}
	
	function getFQURI($prefixed_name) 
	{
		$this->ParsePrefixedName($prefixed_name,$ns,$id);		
		if(!$this->isNS($ns)) {trigger_error("Invalid qname ".$ns. " for $prefixed_name", E_USER_ERROR); exit;}
		return $this->getNSURI($ns).$id;
	}
	
	function GetTTLPrefix($ns)
	{
		 return '@prefix '.$ns.': <'.$this->getNSURI($ns).'> .'.PHP_EOL;
	}
	
	function GenerateTTLHeader()
	{
		$buf = '';
		foreach($this->all_ns AS $ns => $obj) {
			$buf .= $this->GetTTLPrefix($ns);
		}
		return $buf;
	}
}
